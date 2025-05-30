<?php
require_once '../model/Article.php';
require_once '../model/Nomenclature.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Récupère tous les articles
$articles = Article::getAll();
$articlesNonLies = [];

// Pour chaque article, vérifie s'il n'est pas lié à un équipement
foreach ($articles as $art) {
    if (!Nomenclature::articleIsLied($art['code_article'])) {
        $articlesNonLies[] = $art;
    }
}

// Structure d'entête à adapter selon ton import
$headers = [
    'Code Article',
    'Désignation Article',
    "Type d'article",
    'TémSup.:niv.mdt',
    'Anc. n° article',
    'UQ base',
    'Fabricant',
    'N° pce fabricant',
    'Grpe articles',
    'Gpe march.ext.',
    'Document',
    'Description',
    'Créé le',
    'Créé par'
];

$mapping = [
    'Code Article' => 'code_article',
    'Désignation Article' => 'designation_article',
    "Type d'article" => 'type_article',
    'TémSup.:niv.mdt' => 'temsup_niv_mdt',
    'Anc. n° article' => 'ancien_num_article',
    'UQ base' => 'uq_base',
    'Fabricant' => 'fabricant',
    'N° pce fabricant' => 'num_piece_fabricant',
    'Grpe articles' => 'groupe_articles',
    'Gpe march.ext.' => 'groupe_march_ext',
    'Document' => 'document',
    'Description' => 'description',
    'Créé le' => 'date_creation',
    'Créé par' => 'cree_par'
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray($headers, null, 'A1');
$rowNum = 2;
foreach ($articlesNonLies as $art) {
    $row = [];
    foreach ($headers as $col) {
        $key = $mapping[$col];
        $row[] = $art[$key] ?? '';
    }
    $sheet->fromArray($row, null, 'A' . $rowNum++);
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="articles_non_lies.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
