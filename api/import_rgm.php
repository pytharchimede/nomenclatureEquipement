<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../model/Database.php';
require_once '../model/Nomenclature.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Debug: Log des informations de requête
    error_log("DEBUG: Méthode REQUEST: " . $_SERVER['REQUEST_METHOD']);
    error_log("DEBUG: FILES reçus: " . json_encode($_FILES));
    error_log("DEBUG: POST reçu: " . json_encode($_POST));

    // Vérification du fichier uploadé
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'Erreur lors de l\'upload du fichier';
        if (isset($_FILES['file'])) {
            $errorMsg .= ' - Code erreur: ' . $_FILES['file']['error'];
        } else {
            $errorMsg .= ' - Aucun fichier reçu';
        }
        throw new Exception($errorMsg);
    }

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $tmpName = $file['tmp_name'];

    // Vérification de l'extension
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['csv', 'xlsx', 'xls'];

    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Format de fichier non supporté. Utilisez CSV ou Excel.');
    }

    $importedCount = 0;
    $duplicatesCount = 0;
    $errors = [];

    // Traitement selon le type de fichier
    if ($extension === 'csv') {
        $result = importFromCSV($tmpName, $errors);
    } else {
        $result = importFromExcel($tmpName, $errors);
    }

    $importedCount = $result['imported'];
    $duplicatesCount = $result['duplicates'];

    // Réponse de succès
    echo json_encode([
        'success' => true,
        'imported' => $importedCount,
        'duplicates' => $duplicatesCount,
        'errors' => $errors,
        'message' => "Import RGM réussi: {$importedCount} nouveaux éléments, {$duplicatesCount} doublons mis à jour"
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'imported' => 0
    ]);
}

function importFromCSV($filePath, &$errors)
{
    $importedCount = 0;
    $duplicatesCount = 0;

    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headerSkipped = false;
        $lineNumber = 0;

        while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) { // Utilisation de tab comme séparateur
            $lineNumber++;

            // Ignorer la première ligne (en-têtes)
            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            // Vérifier que nous avons assez de colonnes
            if (count($data) < 5) {
                $errors[] = "Ligne {$lineNumber}: Données insuffisantes";
                continue;
            }

            try {
                // Mapping des colonnes selon le format RGM fourni
                $nomenclatureData = [
                    'repere_equipement' => trim($data[0]), // Repère équipement
                    'code_article' => trim($data[1]),      // Code Article  
                    'designation_article' => trim($data[2]), // Designation Article
                    'quantite' => intval($data[3]),        // Quantité installée
                    'unite' => trim($data[4]),             // Unité de quantité
                    'source' => 'RGM',
                    'date_creation' => date('Y-m-d')
                ];

                // Validation des données obligatoires
                if (empty($nomenclatureData['repere_equipement']) || empty($nomenclatureData['code_article'])) {
                    $errors[] = "Ligne {$lineNumber}: Repère équipement et code article obligatoires";
                    continue;
                }

                // Vérifier si l'entrée existe déjà (même repère + code article + source RGM)
                $existingId = checkExistingRgmEntry($nomenclatureData['repere_equipement'], $nomenclatureData['code_article']);

                if ($existingId) {
                    // Mise à jour de l'entrée existante
                    if (updateRgmEntry($existingId, $nomenclatureData)) {
                        $duplicatesCount++;
                    } else {
                        $errors[] = "Ligne {$lineNumber}: Erreur lors de la mise à jour";
                    }
                } else {
                    // Insertion nouvelle entrée
                    if (insertNewRgmEntry($nomenclatureData)) {
                        $importedCount++;
                    } else {
                        $errors[] = "Ligne {$lineNumber}: Erreur lors de l'insertion";
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Ligne {$lineNumber}: " . $e->getMessage();
            }
        }
        fclose($handle);
    } else {
        throw new Exception('Impossible de lire le fichier CSV');
    }

    return ['imported' => $importedCount, 'duplicates' => $duplicatesCount];
}

function importFromExcel($filePath, &$errors)
{
    // Vérification si PhpSpreadsheet est disponible
    if (!file_exists('../vendor/autoload.php')) {
        throw new Exception('PhpSpreadsheet non installé. Utilisez le format CSV.');
    }

    require_once '../vendor/autoload.php';

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $importedCount = 0;
        $duplicatesCount = 0;

        // Commencer à la ligne 2 (ignorer les en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $nomenclatureData = [
                    'repere_equipement' => trim($worksheet->getCell('A' . $row)->getValue()), // Repère équipement
                    'code_article' => trim($worksheet->getCell('B' . $row)->getValue()),      // Code Article
                    'designation_article' => trim($worksheet->getCell('C' . $row)->getValue()), // Designation Article
                    'quantite' => intval($worksheet->getCell('D' . $row)->getValue()),        // Quantité
                    'unite' => trim($worksheet->getCell('E' . $row)->getValue()),             // Unité
                    'source' => 'RGM',
                    'date_creation' => date('Y-m-d')
                ];

                // Validation des données obligatoires
                if (empty($nomenclatureData['repere_equipement']) || empty($nomenclatureData['code_article'])) {
                    $errors[] = "Ligne {$row}: Repère équipement et code article obligatoires";
                    continue;
                }

                // Vérifier si l'entrée existe déjà (même repère + code article + source RGM)
                $existingId = checkExistingRgmEntry($nomenclatureData['repere_equipement'], $nomenclatureData['code_article']);

                if ($existingId) {
                    // Mise à jour de l'entrée existante
                    if (updateRgmEntry($existingId, $nomenclatureData)) {
                        $duplicatesCount++;
                    } else {
                        $errors[] = "Ligne {$row}: Erreur lors de la mise à jour";
                    }
                } else {
                    // Insertion nouvelle entrée
                    if (insertNewRgmEntry($nomenclatureData)) {
                        $importedCount++;
                    } else {
                        $errors[] = "Ligne {$row}: Erreur lors de l'insertion";
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Ligne {$row}: " . $e->getMessage();
            }
        }

        return ['imported' => $importedCount, 'duplicates' => $duplicatesCount];
    } catch (Exception $e) {
        throw new Exception('Erreur lors de la lecture du fichier Excel: ' . $e->getMessage());
    }
}

// Fonctions utilitaires pour gérer les données RGM dans la table nomenclatures

function checkExistingRgmEntry($repereEquipement, $codeArticle)
{
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id FROM rgm_synthese 
            WHERE repere_equipement = ? AND code_article = ?
            LIMIT 1
        ");
        $stmt->execute([$repereEquipement, $codeArticle]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    } catch (Exception $e) {
        error_log("Erreur checkExistingRgmEntry: " . $e->getMessage());
        return null;
    }
}

function updateRgmEntry($id, $data)
{
    try {
        $pdo = Database::getConnection();

        // Mise à jour dans rgm_synthese
        $stmt = $pdo->prepare("
            UPDATE rgm_synthese SET 
                designation_article = ?,
                quantite = ?,
                unite = ?,
                date_import = NOW()
            WHERE id = ?
        ");
        $rgmUpdated = $stmt->execute([
            $data['designation_article'],
            $data['quantite'],
            $data['unite'],
            $id
        ]);

        // Mise à jour également dans nomenclatures (si elle existe)
        $stmt = $pdo->prepare("
            UPDATE nomenclatures SET 
                designation_article = ?,
                quantite = ?,
                unite = ?,
                date_creation = ?
            WHERE repere_equipement = ? AND code_article = ? AND source = 'RGM'
        ");
        $stmt->execute([
            $data['designation_article'],
            $data['quantite'],
            $data['unite'],
            $data['date_creation'],
            $data['repere_equipement'],
            $data['code_article']
        ]);

        return $rgmUpdated;
    } catch (Exception $e) {
        error_log("Erreur updateRgmEntry: " . $e->getMessage());
        return false;
    }
}

function insertNewRgmEntry($data)
{
    try {
        $pdo = Database::getConnection();

        // Commencer une transaction pour s'assurer de la cohérence
        $pdo->beginTransaction();

        // D'abord, vérifier si l'article existe, sinon le créer
        $stmt = $pdo->prepare("SELECT id FROM articles WHERE code_article = ?");
        $stmt->execute([$data['code_article']]);
        $existingArticle = $stmt->fetch();

        if (!$existingArticle) {
            // L'article n'existe pas, on le crée
            $insertArticle = $pdo->prepare("
                INSERT INTO articles (code_article, designation_article, date_creation, cree_par) 
                VALUES (?, ?, ?, ?)
            ");
            $insertArticle->execute([
                $data['code_article'],
                $data['designation_article'],
                $data['date_creation'],
                'RGM_IMPORT'
            ]);
        }

        // 1. Insérer dans rgm_synthese (table dédiée)
        $stmt = $pdo->prepare("
            INSERT INTO rgm_synthese (
                repere_equipement, code_article, designation_article, 
                quantite, unite, source, date_import
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $rgmInserted = $stmt->execute([
            $data['repere_equipement'],
            $data['code_article'],
            $data['designation_article'],
            $data['quantite'],
            $data['unite'],
            'RGM'
        ]);

        // 2. Insérer également dans nomenclatures (pour intégration globale)
        $stmt = $pdo->prepare("
            INSERT INTO nomenclatures (
                repere_equipement, code_article, designation_article, 
                quantite, unite, source, date_creation
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $nomenclatureInserted = $stmt->execute([
            $data['repere_equipement'],
            $data['code_article'],
            $data['designation_article'],
            $data['quantite'],
            $data['unite'],
            'RGM',
            $data['date_creation']
        ]);

        // Valider la transaction seulement si les deux insertions ont réussi
        if ($rgmInserted && $nomenclatureInserted) {
            $pdo->commit();
            return true;
        } else {
            $pdo->rollback();
            return false;
        }
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Erreur insertNewRgmEntry: " . $e->getMessage());
        return false;
    }
}
