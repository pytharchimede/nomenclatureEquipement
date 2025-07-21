<?php

/**
 * Import optimisé pour les gros volumes (23 000+ lignes)
 * Utilise le repère comme clé primaire métier
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Equipement.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Parse une date depuis différents formats vers le format MySQL
 * @param string $dateString
 * @return string|null
 */
function parseDate($dateString)
{
    if (empty($dateString)) {
        return null;
    }

    // Formats possibles
    $formats = [
        'd/m/Y',     // 23/2/2015
        'j/n/Y',     // 23/2/2015 (sans zéros)
        'd-m-Y',     // 23-02-2015
        'Y-m-d',     // 2015-02-23 (déjà bon format)
        'd.m.Y'      // 23.02.2015
    ];

    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dateString);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
    }

    return null;
}

// Configuration pour gros volumes
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 600);

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    // Mode test si pas de fichier uploadé
    if (isset($_GET['test']) && file_exists(__DIR__ . '/../test_import.csv')) {
        // Créer un fichier temporaire pour le test
        $testFile = __DIR__ . '/../test_import.csv';
        $_FILES['excel_file'] = [
            'tmp_name' => $testFile,
            'error' => UPLOAD_ERR_OK
        ];
        $fileTmpPath = $testFile;
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Aucun fichier reçu ou erreur d'upload."
        ]);
        exit;
    }
} else {
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];
}

try {
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();

    // Lecture de l'entête
    $header = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', null, true, true, true)[1];

    // Nettoyage de l'entête
    $header = array_map('trim', $header);

    // Associe lettre colonne => nom colonne
    $colMap = [];
    foreach ($header as $colLetter => $colName) {
        $colMap[trim($colName)] = $colLetter;
    }

    // Colonnes avec variantes acceptées (basé sur votre fichier type)
    $columnMappings = [
        'code_equipement' => ['Code Equipement', 'Code équipement', 'code_equipement', 'Code'],
        'designation_equipement' => ['Désignation équipement', 'Designation equipement', 'Désignation', 'Designation'],
        'repere_equipement' => ['Repère équipement', 'Repere equipement', 'Repère', 'Repere'],
        'fabricant' => ['Fabricant', 'fabricant'],
        'type_objet' => ['Type', 'Type d\'objet', 'Type d objet', 'Type objet', 'type_objet'],
        'numero_serie_fabricant' => ['N° série fabr.', 'N° série fabricant', 'Numero serie fabricant', 'N serie fabr'],
        'poste_technique' => ['Poste technique', 'poste_technique'],
        'date_creation' => ['Créé le', 'Cree le', 'Date création', 'Date creation'],
        // Colonnes supplémentaires de votre fichier
        'code_article' => ['Code Article', 'code_article'],
        'designation_article' => ['Désignation article', 'Designation article'],
        'numero_poste' => ['N° Poste', 'Numero Poste'],
        'quantite_installee' => ['Quantité installée dans l\'équipement', 'Quantite installee'],
        'unite_quantite' => ['Unité de quantité', 'Unite de quantite'],
        'metier' => ['Métier', 'Metier'],
        'source' => ['Source (SAP-RGM-TEMPLATE)', 'Source']
    ];

    // Trouve la correspondance pour chaque colonne
    $foundColumns = [];
    $debugInfo = [];

    foreach ($columnMappings as $fieldName => $possibleNames) {
        $found = false;
        foreach ($possibleNames as $possibleName) {
            if (isset($colMap[$possibleName])) {
                $foundColumns[$fieldName] = $colMap[$possibleName];
                $debugInfo[] = "$fieldName -> '$possibleName' (colonne {$colMap[$possibleName]})";
                $found = true;
                break;
            }
        }

        if (!$found) {
            $debugInfo[] = "$fieldName -> NON TROUVÉ";
        }

        // Seuls le repère et le code sont obligatoires
        if (!$found && in_array($fieldName, ['repere_equipement', 'code_equipement'])) {
            echo json_encode([
                'success' => false,
                'message' => "Colonne obligatoire manquante pour '$fieldName'.",
                'debug' => $debugInfo,
                'available_columns' => array_keys($colMap)
            ]);
            exit;
        }
    }

    // Variables de traitement
    $totalRows = $sheet->getHighestRow() - 1; // -1 pour exclure l'entête
    $inserted = 0;
    $duplicates = 0;
    $errors = 0;
    $log = [];

    // Traitement ligne par ligne pour éviter les problèmes de mémoire
    $pdo = Database::getConnection();

    for ($rowIndex = 2; $rowIndex <= $totalRows + 1; $rowIndex++) {
        $ligneMsg = "Ligne $rowIndex: ";

        // Lecture de la ligne courante
        $rowData = $sheet->rangeToArray('A' . $rowIndex . ':' . $sheet->getHighestColumn() . $rowIndex, null, true, true, true)[$rowIndex];

        $repere_equipement = trim($rowData[$foundColumns['repere_equipement'] ?? ''] ?? '');

        if ($repere_equipement === '') {
            $log[] = ['message' => $ligneMsg . "Repère équipement vide, ignorée.", 'enCours' => false];
            $errors++;
            continue;
        }

        // Vérification des doublons
        if (Equipement::getByRepere($repere_equipement)) {
            $log[] = ['message' => $ligneMsg . "Doublon détecté ($repere_equipement), ignorée.", 'enCours' => false];
            $duplicates++;
            continue;
        }

        // Préparation des données avec les colonnes trouvées
        $data = [
            'code_equipement' => trim($rowData[$foundColumns['code_equipement'] ?? ''] ?? ''),
            'designation_equipement' => $rowData[$foundColumns['designation_equipement'] ?? ''] ?? '',
            'repere_equipement' => $repere_equipement,
            'fabricant' => $rowData[$foundColumns['fabricant'] ?? ''] ?? '',
            'type_objet' => $rowData[$foundColumns['type_objet'] ?? ''] ?? '',
            'numero_serie_fabricant' => $rowData[$foundColumns['numero_serie_fabricant'] ?? ''] ?? '',
            'poste_technique' => $rowData[$foundColumns['poste_technique'] ?? ''] ?? '',
            'date_creation' => parseDate($rowData[$foundColumns['date_creation'] ?? ''] ?? '') ?: date('Y-m-d')
        ];

        // Auto-génération du code si absent
        if (empty($data['code_equipement'])) {
            $data['code_equipement'] = 'EQP-' . strtoupper(preg_replace('/\W+/', '', $repere_equipement));
        }

        // Insertion
        try {
            if (Equipement::create($data)) {
                $inserted++;
                if ($inserted % 1000 == 0) {
                    $log[] = ['message' => "$inserted équipements traités...", 'enCours' => true];
                }
            } else {
                $log[] = ['message' => $ligneMsg . "Erreur lors de l'ajout ($repere_equipement)", 'enCours' => false];
                $errors++;
            }
        } catch (Exception $e) {
            $log[] = ['message' => $ligneMsg . "Erreur : " . $e->getMessage(), 'enCours' => false];
            $errors++;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Import terminé : $inserted ajout(s), $duplicates doublon(s), $errors erreur(s).",
        'imported' => $inserted,
        'duplicates' => $duplicates,
        'errors' => $errors,
        'log' => array_slice($log, -50) // Limiter les logs pour éviter les réponses trop lourdes
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de la lecture du fichier : " . $e->getMessage()
    ]);
}
