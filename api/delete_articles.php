<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Vérifier la méthode POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Données JSON invalides');
    }

    $deletionType = $input['type'] ?? 'selected';
    $deleted = 0;

    $pdo->beginTransaction();

    try {
        if ($deletionType === 'selected') {
            // Suppression des articles sélectionnés
            $selectedIds = $input['ids'] ?? [];

            if (empty($selectedIds)) {
                throw new Exception('Aucun article sélectionné');
            }

            $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM articles WHERE code_article IN ($placeholders)");
            $stmt->execute($selectedIds);
            $deleted = $stmt->rowCount();
        } else if ($deletionType === 'filtered') {
            // Suppression par filtres
            $filters = $input['filters'] ?? [];

            $whereConditions = [];
            $params = [];
            $joins = "";

            // Si on filtre par source, on doit joindre avec nomenclatures
            if (!empty($filters['source'])) {
                $joins = "INNER JOIN nomenclatures n ON articles.code_article = n.code_article";
                $whereConditions[] = "n.source = ?";
                $params[] = $filters['source'];
            }

            // Construction des conditions WHERE selon les filtres
            if (!empty($filters['search'])) {
                $whereConditions[] = "(articles.code_article LIKE ? OR articles.designation_article LIKE ? OR articles.fabricant LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['fabricant'])) {
                $whereConditions[] = "articles.fabricant = ?";
                $params[] = $filters['fabricant'];
            }

            if (!empty($filters['type_article'])) {
                $whereConditions[] = "articles.type_article = ?";
                $params[] = $filters['type_article'];
            }

            if (!empty($filters['groupe_articles'])) {
                $whereConditions[] = "articles.groupe_articles = ?";
                $params[] = $filters['groupe_articles'];
            }

            if (!empty($filters['uq_base'])) {
                $whereConditions[] = "articles.uq_base = ?";
                $params[] = $filters['uq_base'];
            }

            if (empty($whereConditions)) {
                throw new Exception('Aucun filtre spécifié pour la suppression');
            }

            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

            // Si on filtre par source, on doit d'abord récupérer les IDs distincts à supprimer
            if (!empty($filters['source'])) {
                // Compter d'abord combien d'articles seront supprimés
                $countStmt = $pdo->prepare("SELECT COUNT(DISTINCT articles.id) FROM articles $joins $whereClause");
                $countStmt->execute($params);
                $countToDelete = $countStmt->fetchColumn();

                if ($countToDelete == 0) {
                    throw new Exception('Aucun article ne correspond aux critères de suppression');
                }

                // Confirmer si plus de 100 articles
                if ($countToDelete > 100 && !($input['confirmed'] ?? false)) {
                    $pdo->rollBack();
                    echo json_encode([
                        'success' => false,
                        'needsConfirmation' => true,
                        'count' => $countToDelete,
                        'message' => "Vous êtes sur le point de supprimer $countToDelete articles. Confirmez-vous cette action ?"
                    ]);
                    exit;
                }

                // Récupérer les IDs distincts des articles à supprimer
                $idsStmt = $pdo->prepare("SELECT DISTINCT articles.id FROM articles $joins $whereClause");
                $idsStmt->execute($params);
                $articleIds = $idsStmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($articleIds)) {
                    $placeholders = str_repeat('?,', count($articleIds) - 1) . '?';
                    $deleteStmt = $pdo->prepare("DELETE FROM articles WHERE id IN ($placeholders)");
                    $deleteStmt->execute($articleIds);
                    $deleted = $deleteStmt->rowCount();
                }
            } else {
                // Suppression normale sans jointure
                // Compter d'abord combien d'articles seront supprimés
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM articles $whereClause");
                $countStmt->execute($params);
                $countToDelete = $countStmt->fetchColumn();

                if ($countToDelete == 0) {
                    throw new Exception('Aucun article ne correspond aux critères de suppression');
                }

                // Confirmer si plus de 100 articles
                if ($countToDelete > 100 && !($input['confirmed'] ?? false)) {
                    $pdo->rollBack();
                    echo json_encode([
                        'success' => false,
                        'needsConfirmation' => true,
                        'count' => $countToDelete,
                        'message' => "Vous êtes sur le point de supprimer $countToDelete articles. Confirmez-vous cette action ?"
                    ]);
                    exit;
                }

                // Effectuer la suppression
                $deleteStmt = $pdo->prepare("DELETE FROM articles $whereClause");
                $deleteStmt->execute($params);
                $deleted = $deleteStmt->rowCount();
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'deleted' => $deleted,
            'message' => "$deleted article(s) supprimé(s) avec succès"
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
    ]);
}
