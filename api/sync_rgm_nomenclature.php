<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../model/Database.php';
require_once '../model/RgmSynthese.php';
require_once '../model/Nomenclature.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $syncedCount = 0;
    $errors = [];
    $skippedCount = 0;

    // Récupération de tous les éléments RGM
    $rgmData = RgmSynthese::getAll();

    foreach ($rgmData as $rgmItem) {
        try {
            // Préparation des données pour la nomenclature
            $nomenclatureData = [
                'repere_equipement' => $rgmItem['repere_equipement'],
                'code_article' => $rgmItem['code_article'],
                'designation_article' => $rgmItem['designation_article'],
                'quantite' => $rgmItem['quantite'],
                'unite' => $rgmItem['unite'],
                'source' => 'RGM_SYNC'
            ];

            // Vérification si l'élément existe déjà dans la nomenclature
            $exists = NomenclatureSync::exists(
                $rgmItem['repere_equipement'],
                $rgmItem['code_article']
            );

            if ($exists) {
                // Mise à jour de l'élément existant
                if (NomenclatureSync::updateByRepereAndCode($nomenclatureData)) {
                    $syncedCount++;
                } else {
                    $errors[] = "Erreur mise à jour: {$rgmItem['repere_equipement']} - {$rgmItem['code_article']}";
                }
            } else {
                // Création d'un nouvel élément
                if (NomenclatureSync::create($nomenclatureData)) {
                    $syncedCount++;
                } else {
                    $errors[] = "Erreur création: {$rgmItem['repere_equipement']} - {$rgmItem['code_article']}";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Erreur {$rgmItem['repere_equipement']}: " . $e->getMessage();
        }
    }

    // Réponse de succès
    echo json_encode([
        'success' => true,
        'synced' => $syncedCount,
        'total' => count($rgmData),
        'errors' => $errors,
        'skipped' => $skippedCount,
        'message' => "Synchronisation terminée: {$syncedCount} éléments synchronisés"
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'synced' => 0
    ]);
}

/**
 * Extension de la classe Nomenclature pour les méthodes de synchronisation
 */
class NomenclatureSync
{

    public static function exists($repere_equipement, $code_article)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM nomenclatures WHERE repere_equipement = ? AND code_article = ?");
        $stmt->execute([$repere_equipement, $code_article]);
        return $stmt->fetchColumn() > 0;
    }

    public static function updateByRepereAndCode($data)
    {
        $pdo = Database::getConnection();

        try {
            $sql = "UPDATE nomenclatures SET 
                    designation_article = ?, 
                    quantite = ?, 
                    unite = ?, 
                    source = ?,
                    updated_at = NOW()
                    WHERE repere_equipement = ? AND code_article = ?";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $data['designation_article'],
                $data['quantite'],
                $data['unite'],
                $data['source'],
                $data['repere_equipement'],
                $data['code_article']
            ]);
        } catch (PDOException $e) {
            error_log("Erreur updateByRepereAndCode: " . $e->getMessage());
            return false;
        }
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();

        try {
            $sql = "INSERT INTO nomenclatures (repere_equipement, code_article, designation_article, quantite, unite, source, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $data['repere_equipement'],
                $data['code_article'],
                $data['designation_article'],
                $data['quantite'],
                $data['unite'],
                $data['source']
            ]);
        } catch (PDOException $e) {
            error_log("Erreur create nomenclature: " . $e->getMessage());
            return false;
        }
    }
}

// Utilisation de la classe étendue pour la synchronisation
// Le code de synchronisation est intégré dans la boucle principale ci-dessus
