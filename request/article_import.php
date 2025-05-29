<?php
require_once '../model/Database.php';
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

$map = [
    'A' => 'code_article',
    'B' => 'designation_article',
    'C' => 'type_article',
    'D' => 'temsup_niv_mdt',
    'E' => 'ancien_num_article',
    'F' => 'uq_base',
    'G' => 'fabricant',
    'H' => 'numero_piece_fabricant',
    'I' => 'groupe_articles',
    'J' => 'groupe_marche_externe', // <-- corrige ici
    'K' => 'document',
    'L' => 'description',
    'M' => 'date_creation',
    'N' => 'cree_par'
];

$imported = 0;
$duplicates = [];
$errors = [];

foreach ($rows as $row) {
    $data = [];
    foreach ($map as $col => $field) {
        $data[$field] = isset($row[$col]) ? trim($row[$col]) : null;
    }
    // Formatage de la date
    if (!empty($data['date_creation'])) {
        $date = date_create_from_format('d/m/Y', $data['date_creation']);
        if ($date) {
            $data['date_creation'] = $date->format('Y-m-d');
        } else {
            $data['date_creation'] = null;
        }
    }
    // Vérifie doublon
    if (Article::exists($data['code_article'])) {
        $duplicates[] = $data['code_article'];
        continue;
    }
    // Ajout en base
    if (Article::add($data)) {
        $imported++;
    } else {
        $errors[] = $data['code_article'] ?: 'Ligne inconnue';
    }
}

echo json_encode([
    'success' => true,
    'imported' => $imported,
    'duplicates' => $duplicates,
    'errors' => $errors
]);
