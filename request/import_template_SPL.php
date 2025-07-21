<?php
// filepath: c:\wamp\www\nomenclatureequipement\request\import_template_spl.php
header('Content-Type: application/json');

require_once '../model/Database.php';
require_once '../model/TemplateSPL.php';
require_once '../model/Utilisateur.php'; // si besoin pour l'ID utilisateur
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

set_time_limit(300); // 5 minutes

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => "Aucun fichier reçu ou erreur d'upload."
    ]);
    exit;
}

$fileTmpPath = $_FILES['excel_file']['tmp_name'];

try {
    $reader = new Xlsx();
    $reader->setLoadSheetsOnly(['SPL']); // Charge uniquement la feuille SPL
    $spreadsheet = $reader->load($fileTmpPath);
    $sheet = $spreadsheet->getSheetByName('SPL');
    $rows = $sheet->toArray(null, true, true, true);

    // Première ligne = entête
    $header = null;
    $headerIndex = null;
    foreach ($rows as $i => $row) {
        // On force la valeur à string pour éviter les problèmes de null
        $firstCell = strtoupper(trim((string)reset($row)));
        if ($firstCell === 'N°' || $firstCell === 'N°.' || $firstCell === 'NO' || $firstCell === 'NUMERO') {
            $header = $row;
            $headerIndex = $i;
            break;
        }
    }
    if (!$header) {
        echo json_encode([
            'success' => false,
            'message' => "Impossible de trouver la ligne d'en-tête (colonne N°)."
        ]);
        exit;
    }

    // Associe lettre colonne => nom colonne
    $colMap = [];
    foreach ($header as $colLetter => $colName) {
        $colMap[trim((string)$colName)] = $colLetter;
    }

    // Colonnes attendues (adapter selon ton fichier SPL)
    $expectedCols = [
        'N°',
        'CODE SAP',
        'Code Article',
        'Qte',
        'Designation Article',
        'UNITE BASE',
        'METIER',
        'N° PCE FABRICANT',
        'FABRICANT',
        'Equipement'
    ];
    foreach ($expectedCols as $col) {
        if (!isset($colMap[$col])) {
            echo json_encode([
                'success' => false,
                'message' => "Colonne manquante dans le fichier : $col"
            ]);
            exit;
        }
    }

    // On ne traite que les lignes après l'en-tête
    $dataRows = array_slice($rows, $headerIndex + 1);

    $inserted = 0;
    $duplicates = 0;
    $errors = 0;
    $log = [];
    $import_par = $_SESSION['user_id'] ?? null;

    foreach ($dataRows as $i => $row) {
        $ligneMsg = "Ligne " . ($headerIndex + 2 + $i) . ": ";
        // On force la valeur à string pour éviter le warning PHP 8.1+
        $equipements = trim((string)($row[$colMap['Equipement']] ?? ''));
        if ($equipements === '') {
            $log[] = ['message' => $ligneMsg . "Aucun repère, ignorée.", 'enCours' => false];
            continue;
        }
        // Plusieurs repères séparés par /
        $reperes = preg_split('/\s*\/\s*/', $equipements);
        foreach ($reperes as $repere) {
            $repere = trim((string)$repere);
            if ($repere === '') continue;
            $data = [
                'numero' => trim((string)($row[$colMap['N°']] ?? '')),
                'code_sap' => trim((string)($row[$colMap['CODE SAP']] ?? '')),
                'code_article' => trim((string)($row[$colMap['Code Article']] ?? '')),
                'quantite' => trim((string)($row[$colMap['Qte']] ?? '')),
                'designation_article' => trim((string)($row[$colMap['Designation Article']] ?? '')),
                'unite_base' => trim((string)($row[$colMap['UNITE BASE']] ?? '')),
                'metier' => trim((string)($row[$colMap['METIER']] ?? '')),
                'numero_piece_fabricant' => trim((string)($row[$colMap['N° PCE FABRICANT']] ?? '')),
                'fabricant' => trim((string)($row[$colMap['FABRICANT']] ?? '')),
                'equipement' => $repere,
                'import_par' => $import_par
            ];
            // Vérifie si la ligne existe déjà (optionnel, selon ta logique)
            if (TemplateSPL::exists($data)) {
                $log[] = ['message' => $ligneMsg . "Doublon détecté ($repere / {$data['code_article']}), ignorée.", 'enCours' => false];
                $duplicates++;
                continue;
            }
            if (TemplateSPL::create($data)) {
                $log[] = ['message' => $ligneMsg . "Ajouté ($repere / {$data['code_article']})", 'enCours' => false];
                $inserted++;
            } else {
                $log[] = ['message' => $ligneMsg . "Erreur lors de l'ajout ($repere / {$data['code_article']})", 'enCours' => false];
                $errors++;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Import terminé : $inserted ajout(s), $duplicates doublon(s), $errors erreur(s).",
        'log' => $log
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de la lecture du fichier : " . $e->getMessage()
    ]);
    exit;
}
