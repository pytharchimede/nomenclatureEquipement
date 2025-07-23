<?php
require_once '../model/Database.php';
require_once '../model/Nomenclature.php';

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? 'detect';
    $pdo = Database::getConnection();

    switch ($action) {
        case 'detect':
            // Détection des doublons basée sur repere_equipement + code_article
            $query = "
                SELECT 
                    repere_equipement,
                    code_article,
                    COUNT(*) as count,
                    GROUP_CONCAT(id) as ids,
                    MIN(id) as first_id,
                    MAX(date_creation) as latest_date
                FROM nomenclatures 
                WHERE repere_equipement IS NOT NULL 
                AND repere_equipement != ''
                AND code_article IS NOT NULL 
                AND code_article != ''
                GROUP BY repere_equipement, code_article 
                HAVING count > 1
                ORDER BY count DESC, repere_equipement, code_article
            ";

            $stmt = $pdo->query($query);
            $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupération des détails pour chaque groupe de doublons
            $detailedDuplicates = [];
            foreach ($duplicates as $dup) {
                $ids = explode(',', $dup['ids']);

                $detailQuery = "
                    SELECT 
                        id,
                        code_equipement,
                        code_article,
                        repere_equipement,
                        designation_equipement,
                        fabricant,
                        designation_article,
                        quantite,
                        unite,
                        DATE_FORMAT(date_creation, '%d/%m/%Y %H:%i') as date_creation,
                        source
                    FROM nomenclatures 
                    WHERE id IN (" . implode(',', array_map('intval', $ids)) . ")
                    ORDER BY date_creation ASC, id ASC
                ";

                $stmt = $pdo->query($detailQuery);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $detailedDuplicates[] = [
                    'group_key' => $dup['repere_equipement'] . ' | ' . $dup['code_article'],
                    'repere_equipement' => $dup['repere_equipement'],
                    'code_article' => $dup['code_article'],
                    'count' => $dup['count'],
                    'items' => $items,
                    'recommended_action' => 'keep_first' // Par défaut, garder le plus ancien
                ];
            }

            echo json_encode([
                'success' => true,
                'duplicates' => $detailedDuplicates,
                'total_groups' => count($detailedDuplicates),
                'total_items' => array_sum(array_column($duplicates, 'count'))
            ]);
            break;

        case 'resolve':
            // Résolution des doublons
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['actions']) || !is_array($input['actions'])) {
                throw new Exception('Actions de résolution manquantes');
            }

            $resolved = 0;
            $errors = [];

            $pdo->beginTransaction();

            foreach ($input['actions'] as $action) {
                try {
                    switch ($action['type']) {
                        case 'delete':
                            if (!isset($action['id'])) {
                                throw new Exception('ID manquant pour suppression');
                            }

                            $stmt = $pdo->prepare("DELETE FROM nomenclatures WHERE id = ?");
                            if ($stmt->execute([$action['id']])) {
                                $resolved++;
                            }
                            break;

                        case 'merge':
                            if (!isset($action['keep_id']) || !isset($action['delete_ids'])) {
                                throw new Exception('IDs manquants pour fusion');
                            }

                            // Optionnel: Mettre à jour l'enregistrement conservé avec des données fusionnées
                            if (isset($action['update_data'])) {
                                $updateFields = [];
                                $updateParams = [];

                                foreach ($action['update_data'] as $field => $value) {
                                    $updateFields[] = "$field = ?";
                                    $updateParams[] = $value;
                                }

                                if (!empty($updateFields)) {
                                    $updateParams[] = $action['keep_id'];
                                    $stmt = $pdo->prepare(
                                        "UPDATE nomenclatures SET " . implode(', ', $updateFields) . " WHERE id = ?"
                                    );
                                    $stmt->execute($updateParams);
                                }
                            }

                            // Suppression des doublons
                            $deleteIds = array_map('intval', $action['delete_ids']);
                            $placeholders = str_repeat('?,', count($deleteIds) - 1) . '?';
                            $stmt = $pdo->prepare("DELETE FROM nomenclatures WHERE id IN ($placeholders)");
                            if ($stmt->execute($deleteIds)) {
                                $resolved += count($deleteIds);
                            }
                            break;

                        default:
                            throw new Exception('Type d\'action non reconnu: ' . $action['type']);
                    }
                } catch (Exception $e) {
                    $errors[] = [
                        'action' => $action,
                        'error' => $e->getMessage()
                    ];
                }
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'resolved' => $resolved,
                'errors' => $errors,
                'message' => "$resolved doublons résolus avec succès"
            ]);
            break;

        default:
            throw new Exception('Action non reconnue: ' . $action);
    }
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }

    error_log("Erreur gestion doublons nomenclatures: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
