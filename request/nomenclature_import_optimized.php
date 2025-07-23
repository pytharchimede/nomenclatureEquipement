<?php
set_time_limit(600); // 10 minutes pour traitement des gros fichiers

require_once '../model/Database.php';
require_once '../model/Nomenclature.php';
require_once '../model/Equipement.php';
require_once '../model/Article.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors de l\'upload du fichier Excel');
    }

    $tmpFile = $_FILES['excel_file']['tmp_name'];
    $fileName = $_FILES['excel_file']['name'];

    // Validation du type de fichier
    $allowedExtensions = ['xls', 'xlsx'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception('Format de fichier non supporté. Utilisez .xls ou .xlsx');
    }

    // Chargement du fichier Excel
    $spreadsheet = IOFactory::load($tmpFile);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    if (empty($rows)) {
        throw new Exception('Le fichier Excel est vide');
    }

    // La première ligne contient les entêtes
    $header = array_shift($rows);

    // Mapping des colonnes (adaptable selon votre format Excel)
    $columnMap = [
        'A' => 'code_equipement',
        'B' => 'code_article',
        'C' => 'repere_equipement',
        'D' => 'designation_equipement',
        'E' => 'fabricant',
        'F' => 'type',
        'G' => 'numero_serie_fabricant',
        'H' => 'designation_article',
        'I' => 'numero_poste',
        'J' => 'quantite',
        'K' => 'unite',
        'L' => 'poste_technique',
        'M' => 'metier',
        'N' => 'date_creation',
        'O' => 'source'
    ];

    $stats = [
        'total' => count($rows),
        'imported' => 0,
        'duplicates' => 0,
        'errors' => 0,
        'skipped' => 0
    ];

    $details = [
        'duplicates' => [],
        'errors' => [],
        'skipped' => []
    ];

    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    $lineNumber = 1; // Commence à 1 car on a enlevé l'en-tête

    foreach ($rows as $row) {
        $lineNumber++;

        try {
            // Extraction des données selon le mapping
            $data = [];
            foreach ($columnMap as $col => $field) {
                $value = isset($row[$col]) ? trim($row[$col]) : null;
                $data[$field] = empty($value) ? null : $value;
            }

            // Validation des champs obligatoires
            if (empty($data['repere_equipement']) && empty($data['code_article'])) {
                $details['skipped'][] = [
                    'line' => $lineNumber,
                    'reason' => 'Repère équipement et code article manquants',
                    'data' => $data
                ];
                $stats['skipped']++;
                continue;
            }

            // Validation et formatage de la date
            if (!empty($data['date_creation'])) {
                $date = null;

                // Tentative de parsing de différents formats
                $dateFormats = ['d/m/Y', 'j/n/Y', 'Y-m-d', 'd-m-Y'];
                foreach ($dateFormats as $format) {
                    $date = DateTime::createFromFormat($format, $data['date_creation']);
                    if ($date !== false) break;
                }

                if ($date !== false) {
                    $data['date_creation'] = $date->format('Y-m-d');
                } else {
                    $data['date_creation'] = date('Y-m-d'); // Date actuelle par défaut
                }
            } else {
                $data['date_creation'] = date('Y-m-d');
            }

            // Validation de la quantité
            if (!empty($data['quantite'])) {
                if (!is_numeric($data['quantite'])) {
                    $data['quantite'] = 1; // Valeur par défaut
                }
            } else {
                $data['quantite'] = 1;
            }

            // Vérification des doublons (repere_equipement + code_article)
            if (!empty($data['repere_equipement']) && !empty($data['code_article'])) {
                if (Nomenclature::existsByRepereArticle($data['repere_equipement'], $data['code_article'])) {
                    $details['duplicates'][] = [
                        'line' => $lineNumber,
                        'repere_equipement' => $data['repere_equipement'],
                        'code_article' => $data['code_article'],
                        'data' => $data
                    ];
                    $stats['duplicates']++;
                    continue;
                }
            }

            // Ajout automatique des équipements/articles s'ils n'existent pas
            if (!empty($data['repere_equipement']) && !Equipement::getByRepere($data['repere_equipement'])) {
                $equipementData = [
                    'repere_equipement' => $data['repere_equipement'],
                    'designation_equipement' => $data['designation_equipement'] ?? null,
                    'fabricant' => $data['fabricant'] ?? null,
                    'type' => $data['type'] ?? null,
                    'numero_serie_fabricant' => $data['numero_serie_fabricant'] ?? null
                ];
                Equipement::add($equipementData);
            }

            if (!empty($data['code_article']) && !Article::exists($data['code_article'])) {
                $articleData = [
                    'code_article' => $data['code_article'],
                    'designation_article' => $data['designation_article'] ?? null
                ];
                Article::add($articleData);
            }

            // Ajout de la nomenclature
            if (Nomenclature::add($data)) {
                $stats['imported']++;
            } else {
                $details['errors'][] = [
                    'line' => $lineNumber,
                    'reason' => 'Erreur lors de l\'insertion en base de données',
                    'data' => $data
                ];
                $stats['errors']++;
            }
        } catch (Exception $e) {
            $details['errors'][] = [
                'line' => $lineNumber,
                'reason' => $e->getMessage(),
                'data' => $data ?? []
            ];
            $stats['errors']++;
        }
    }

    $pdo->commit();

    // Nettoyage du fichier temporaire
    unlink($tmpFile);

    echo json_encode([
        'success' => true,
        'message' => 'Importation terminée avec succès',
        'stats' => $stats,
        'details' => $details,
        'filename' => $fileName
    ]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }

    error_log("Erreur importation nomenclatures: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
