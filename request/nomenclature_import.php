<?php
set_time_limit(600); // 10 minutes

require_once '../model/Database.php';
require_once '../model/Nomenclature.php';
require_once '../model/Equipement.php';
require_once '../model/Article.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'errors' => ["Erreur lors de l'upload du fichier."]]);
    exit;
}

$tmpFile = $_FILES['excel_file']['tmp_name'];
$spreadsheet = IOFactory::load($tmpFile);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

// Supposons que la première ligne contient les entêtes
$header = array_shift($rows);

// Mapping adapté à ton fichier Excel
$map = [
    'A' => 'code_equipement',
    'B' => 'repere_equipement',
    'C' => 'designation_equipement',
    'D' => 'fabricant',
    'E' => 'type',
    'F' => 'numero_serie_fabricant',
    'G' => 'code_article',
    'H' => 'designation_article',
    'I' => 'numero_poste',
    'J' => 'quantite',
    'K' => 'unite',
    'L' => 'poste_technique',
    'M' => 'metier',
    'N' => 'date_creation',
    'O' => 'source'
];

$imported = 0;
$duplicates = [];
$errors = [];

foreach ($rows as $row) {
    $data = [];
    foreach ($map as $col => $field) {
        $data[$field] = isset($row[$col]) ? trim($row[$col]) : null;
    }
    // Formatage de la date (accepte 23/2/2015 ou 23/02/2015)
    if (!empty($data['date_creation'])) {
        $date = date_create_from_format('d/m/Y', $data['date_creation']);
        if (!$date) {
            $date = date_create_from_format('j/n/Y', $data['date_creation']);
        }
        if ($date) {
            $data['date_creation'] = $date->format('Y-m-d');
        } else {
            $data['date_creation'] = null;
        }
    }
    // Vérifie doublon (code_equipement + code_article)
    if (Nomenclature::exists($data['code_equipement'], $data['code_article'])) {
        $duplicates[] = $data['code_equipement'] . ' / ' . $data['code_article'];
        continue;
    }
    // Ajoute l'équipement s'il n'existe pas
    if (!Equipement::exists($data['code_equipement'])) {
        Equipement::add(['code_equipement' => $data['code_equipement']]);
    }
    // Ajoute l'article s'il n'existe pas
    if (!Article::exists($data['code_article'])) {
        Article::add(['code_article' => $data['code_article']]);
    }
    // Ajout en base
    if (Nomenclature::add($data)) {
        $imported++;
    } else {
        $errors[] = ($data['code_equipement'] ?? '') . ' / ' . ($data['code_article'] ?? '');
    }
}

echo json_encode([
    'success' => true,
    'imported' => $imported,
    'duplicates' => $duplicates,
    'errors' => $errors
]);
