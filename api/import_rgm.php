<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../model/Database.php';
require_once '../model/RgmSynthese.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Vérification du fichier uploadé
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors de l\'upload du fichier');
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
    $errors = [];

    // Traitement selon le type de fichier
    if ($extension === 'csv') {
        $importedCount = importFromCSV($tmpName, $errors);
    } else {
        $importedCount = importFromExcel($tmpName, $errors);
    }

    // Réponse de succès
    echo json_encode([
        'success' => true,
        'imported' => $importedCount,
        'errors' => $errors,
        'message' => "Import réussi: {$importedCount} éléments importés"
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

    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headerSkipped = false;
        $lineNumber = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
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
                // Mapping des colonnes (ajustez selon votre format CSV)
                $rgmData = [
                    'repere_equipement' => trim($data[0]),
                    'code_article' => trim($data[1]),
                    'designation_article' => trim($data[2]),
                    'quantite' => floatval($data[3]),
                    'unite' => trim($data[4])
                ];

                // Validation des données obligatoires
                if (empty($rgmData['repere_equipement']) || empty($rgmData['code_article'])) {
                    $errors[] = "Ligne {$lineNumber}: Repère équipement et code article obligatoires";
                    continue;
                }

                // Insertion en base
                if (RgmSynthese::insertOrUpdate($rgmData)) {
                    $importedCount++;
                } else {
                    $errors[] = "Ligne {$lineNumber}: Erreur lors de l'insertion";
                }
            } catch (Exception $e) {
                $errors[] = "Ligne {$lineNumber}: " . $e->getMessage();
            }
        }
        fclose($handle);
    } else {
        throw new Exception('Impossible de lire le fichier CSV');
    }

    return $importedCount;
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

        // Commencer à la ligne 2 (ignorer les en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $rgmData = [
                    'repere_equipement' => trim($worksheet->getCell('A' . $row)->getValue()),
                    'code_article' => trim($worksheet->getCell('B' . $row)->getValue()),
                    'designation_article' => trim($worksheet->getCell('C' . $row)->getValue()),
                    'quantite' => floatval($worksheet->getCell('D' . $row)->getValue()),
                    'unite' => trim($worksheet->getCell('E' . $row)->getValue())
                ];

                // Validation des données obligatoires
                if (empty($rgmData['repere_equipement']) || empty($rgmData['code_article'])) {
                    $errors[] = "Ligne {$row}: Repère équipement et code article obligatoires";
                    continue;
                }

                // Insertion en base
                if (RgmSynthese::insertOrUpdate($rgmData)) {
                    $importedCount++;
                } else {
                    $errors[] = "Ligne {$row}: Erreur lors de l'insertion";
                }
            } catch (Exception $e) {
                $errors[] = "Ligne {$row}: " . $e->getMessage();
            }
        }

        return $importedCount;
    } catch (Exception $e) {
        throw new Exception('Erreur lors de la lecture du fichier Excel: ' . $e->getMessage());
    }
}
