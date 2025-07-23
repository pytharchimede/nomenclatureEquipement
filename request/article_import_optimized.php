<?php

/**
 * Import optimisé des articles depuis Excel
 * Format: Code Article, Désignation Article, Type d'article, etc.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Article.php';
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
        'd/m/Y',     // 1/10/2014
        'j/n/Y',     // 1/10/2014 (sans zéros)
        'd-m-Y',     // 01-10-2014
        'Y-m-d',     // 2014-10-01 (déjà bon format)
        'd.m.Y'      // 01.10.2014
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
    if (isset($_GET['test']) && file_exists(__DIR__ . '/../test_import_articles.csv')) {
        $testFile = __DIR__ . '/../test_import_articles.csv';
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
    $startTime = microtime(true);

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
        'code_article' => ['Code Article', 'code_article'],
        'designation_article' => ['Désignation Article', 'designation_article'],
        'type_article' => ['Type d\'article', 'Type d article', 'type_article'],
        'temsup_niv_mdt' => ['TémSup.:niv.mdt', 'TémSup', 'temsup_niv_mdt'],
        'ancien_num_article' => ['Anc. n° article', 'Ancien numero article', 'ancien_num_article'],
        'uq_base' => ['UQ base', 'uq_base'],
        'fabricant' => ['Fabricant', 'fabricant'],
        'numero_piece_fabricant' => ['N° pce fabricant', 'numero_piece_fabricant'],
        'groupe_articles' => ['Grpe articles', 'Groupe articles', 'groupe_articles'],
        'groupe_marche_externe' => ['Gpe march.ext.', 'Groupe marche externe', 'groupe_marche_externe'],
        'document' => ['Document', 'document'],
        'description' => ['Description', 'description'],
        'date_creation' => ['Créé le', 'Cree le', 'Date création', 'date_creation'],
        'cree_par' => ['Créé par', 'Cree par', 'cree_par']
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

        // Seul le code article est obligatoire
        if (!$found && $fieldName === 'code_article') {
            echo json_encode([
                'success' => false,
                'message' => "Colonne obligatoire manquante pour 'code_article'.",
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

        $code_article = trim($rowData[$foundColumns['code_article'] ?? ''] ?? '');

        if ($code_article === '') {
            $log[] = ['message' => $ligneMsg . "Code article vide, ignorée.", 'enCours' => false];
            $errors++;
            continue;
        }

        // Vérification des doublons
        if (Article::exists($code_article)) {
            $log[] = ['message' => $ligneMsg . "Doublon détecté ($code_article), ignorée.", 'enCours' => false];
            $duplicates++;
            continue;
        }

        // Préparation des données avec les colonnes trouvées
        $data = [
            'code_article' => $code_article,
            'designation_article' => $rowData[$foundColumns['designation_article'] ?? ''] ?? '',
            'type_article' => $rowData[$foundColumns['type_article'] ?? ''] ?? '',
            'temsup_niv_mdt' => $rowData[$foundColumns['temsup_niv_mdt'] ?? ''] ?? '',
            'ancien_num_article' => $rowData[$foundColumns['ancien_num_article'] ?? ''] ?? '',
            'uq_base' => $rowData[$foundColumns['uq_base'] ?? ''] ?? '',
            'fabricant' => $rowData[$foundColumns['fabricant'] ?? ''] ?? '',
            'numero_piece_fabricant' => $rowData[$foundColumns['numero_piece_fabricant'] ?? ''] ?? '',
            'groupe_articles' => $rowData[$foundColumns['groupe_articles'] ?? ''] ?? '',
            'groupe_marche_externe' => $rowData[$foundColumns['groupe_marche_externe'] ?? ''] ?? '',
            'document' => $rowData[$foundColumns['document'] ?? ''] ?? '',
            'description' => $rowData[$foundColumns['description'] ?? ''] ?? '',
            'date_creation' => parseDate($rowData[$foundColumns['date_creation'] ?? ''] ?? '') ?: date('Y-m-d'),
            'cree_par' => $rowData[$foundColumns['cree_par'] ?? ''] ?? ''
        ];

        // Insertion
        try {
            if (Article::create($data)) {
                $inserted++;
                if ($inserted % 1000 == 0) {
                    $log[] = ['message' => "$inserted articles traités...", 'enCours' => true];
                }
            } else {
                $log[] = ['message' => $ligneMsg . "Erreur lors de l'ajout ($code_article)", 'enCours' => false];
                $errors++;
            }
        } catch (Exception $e) {
            $log[] = ['message' => $ligneMsg . "Erreur : " . $e->getMessage(), 'enCours' => false];
            $errors++;
        }
    }

    $executionTime = microtime(true) - $startTime;

    echo json_encode([
        'success' => true,
        'message' => "Import terminé : $inserted ajout(s), $duplicates doublon(s), $errors erreur(s).",
        'imported' => $inserted,
        'duplicates' => $duplicates,
        'errors' => $errors,
        'execution_time' => round($executionTime, 2) . 's',
        'log' => array_slice($log, -50) // Limiter les logs pour éviter les réponses trop lourdes
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de la lecture du fichier : " . $e->getMessage()
    ]);
}
