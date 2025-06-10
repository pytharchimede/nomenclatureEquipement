<?php

require_once '../model/Equipement.php';
require_once '../model/Nomenclature.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Récupère tous les équipements
$equipements = Equipement::getAll();
$equipSansPiece = [];

// Pour chaque équipement, vérifie s'il n'a pas de pièce de rechange
foreach ($equipements as $eq) {
    if (!Nomenclature::equipementHasPiece($eq['code_equipement'])) {
        $equipSansPiece[] = $eq;
    }
}

// Structure d'entête à adapter selon ton import
$headers = [
    'Code Equipement',
    'Désignation équipement',
    'Repère équipement',
    'Fabricant',
    "Type d'objet",
    'Désignat. type',
    'N° série fabr.',
    'N° pièce fabric',
    'Poste technique',
    'Désignation Poste Technique',
    'PosteTravPrinc.',
    'Catég.équipemnt',
    'Centre de coûts',
    'Créé le'
];

$mapping = [
    'Code Equipement' => 'code_equipement',
    'Désignation équipement' => 'designation_equipement',
    'Repère équipement' => 'repere_equipement',
    'Fabricant' => 'fabricant',
    "Type d'objet" => 'type_objet',
    'Désignat. type' => 'designation_type',
    'N° série fabr.' => 'numero_serie_fabricant',
    'N° pièce fabric' => 'numero_piece_fabricant',
    'Poste technique' => 'poste_technique',
    'Désignation Poste Technique' => 'designation_poste_technique',
    'PosteTravPrinc.' => 'poste_travail_principal',
    'Catég.équipemnt' => 'categorie_equipement',
    'Centre de coûts' => 'centre_de_couts',
    'Créé le' => 'date_creation'
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray($headers, null, 'A1');
$rowNum = 2;
foreach ($equipSansPiece as $eq) {
    $row = [];
    foreach ($headers as $col) {
        $key = $mapping[$col];
        $row[] = $eq[$key] ?? '';
    }
    $sheet->fromArray($row, null, 'A' . $rowNum++);
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="equipements_sans_piece.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
