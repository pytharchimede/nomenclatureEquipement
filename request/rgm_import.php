<?php
set_time_limit(600);

require_once '../model/Database.php';
require_once '../model/RgmSynthese.php';
require_once '../model/Equipement.php';
require_once '../model/Article.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

if (!isset($_FILES['rgm_file']) || $_FILES['rgm_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'errors' => ["Erreur lors de l'upload du fichier."]]);
    exit;
}

$tmpFile = $_FILES['rgm_file']['tmp_name'];
$spreadsheet = IOFactory::load($tmpFile);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

$header = array_shift($rows);

$map = [
    'A' => 'repere_equipement',
    'B' => 'code_article',
    'C' => 'designation_article',
    'D' => 'quantite',
    'E' => 'unite'
];

$imported = 0;
$duplicates = [];
$errors = [];
$residualRows = [];

$startTime = time();
$maxTime = ini_get('max_execution_time') - 5; // 5s de marge

foreach ($rows as $i => $row) {
    if ($maxTime > 0 && (time() - $startTime) > $maxTime) {
        $errors[] = [
            'message' => "Arrêt anticipé pour éviter le time-out",
            'ligne' => $i + 2,
            'row' => $row
        ];
        // Ajoute toutes les lignes restantes au résiduel
        for ($j = $i; $j < count($rows); $j++) {
            $residualRows[] = $rows[$j];
        }
        break;
    }

    $data = [];
    foreach ($map as $col => $field) {
        $data[$field] = isset($row[$col]) ? trim($row[$col]) : null;
    }
    $data['source'] = 'RGM';

    if (empty($data['repere_equipement']) || empty($data['code_article'])) {
        $errors[] = [
            'message' => "Ligne ignorée : repère équipement ou code article manquant",
            'ligne' => $i + 2,
            'row' => $row
        ];
        $residualRows[] = $row;
        continue;
    }

    if (!Equipement::exists($data['repere_equipement'])) {
        Equipement::add(['code_equipement' => $data['repere_equipement']]);
    }
    if (!Article::exists($data['code_article'])) {
        Article::add(['code_article' => $data['code_article']]);
    }

    if (RgmSynthese::exists($data['repere_equipement'], $data['code_article'])) {
        $duplicates[] = [
            'message' => "Doublon : {$data['repere_equipement']} / {$data['code_article']}",
            'ligne' => $i + 2,
            'row' => $row
        ];
        $residualRows[] = $row;
        continue;
    }

    if (RgmSynthese::create($data)) {
        $imported++;
    } else {
        $errors[] = [
            'message' => "Erreur lors de l'ajout de la synthèse RGM",
            'ligne' => $i + 2,
            'row' => $row
        ];
        $residualRows[] = $row;
    }
}

// Génération du fichier résiduel (CSV)
$residualFile = null;
if (count($residualRows) > 0) {
    $filename = 'residuel_rgm_' . date('Ymd_His') . '.csv';
    $filepath = '../tmp/' . $filename;
    $f = fopen($filepath, 'w');
    fputcsv($f, array_keys($header), ';');
    foreach ($residualRows as $resRow) {
        fputcsv($f, $resRow, ';');
    }
    fclose($f);
    $residualFile = 'tmp/' . $filename;
}

echo json_encode([
    'success' => true,
    'imported' => $imported,
    'duplicates' => $duplicates,
    'errors' => $errors,
    'residualFile' => $residualFile
]);
