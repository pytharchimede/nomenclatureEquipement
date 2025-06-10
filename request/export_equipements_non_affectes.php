<?php
require_once '../model/Equipement.php';
require_once '../model/Quantitatif.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Récupère tous les équipements
$equipements = Equipement::getAll();

// Filtre les non affectés à une famille
$nonAffectes = [];
foreach ($equipements as $eq) {
    $repere = preg_replace('/\s+/', '', $eq['repere_equipement'] ?? '');
    $famille = Quantitatif::getFamilleByRepere($repere);
    if (!$famille || $famille === 'Non défini') {
        $nonAffectes[] = $eq;
    }
}

// Si aucun équipement non affecté, on arrête le script
if (empty($nonAffectes)) {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Aucun équipement non affecté trouvé.']);
    exit;
}
// Entêtes pour le fichier Excel
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

// Correspondance entre les colonnes Excel et les clés de la table equipement
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
$sheet->setTitle('Non affectés');

// Entête
$sheet->fromArray($headers, null, 'A1');

// Données
$rowNum = 2;
foreach ($nonAffectes as $eq) {
    $row = [];
    foreach ($headers as $col) {
        $key = $mapping[$col];
        $row[] = $eq[$key] ?? '';
    }
    $sheet->fromArray($row, null, 'A' . $rowNum++);
}

// Téléchargement
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="equipements_non_affectes.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
