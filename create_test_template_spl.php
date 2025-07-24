<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Créer un fichier Excel de test pour Template SPL
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('SPL');

// En-têtes selon la structure fournie
$headers = [
    'A1' => 'N°',
    'B1' => 'CODE SAP',
    'C1' => '',
    'D1' => 'Code Article',
    'E1' => 'Qte',
    'F1' => 'Designation Article',
    'G1' => 'UNITE BASE',
    'H1' => 'METIER',
    'I1' => 'N° PCE FABRICANT',
    'J1' => 'FABRICANT',
    'K1' => 'Equipement'
];

foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

// Données de test avec équipements multiples comme dans l'exemple
$testData = [
    [1, '', '2400062060', 40, 'ROULEMENT,RIGIDE 1 RANGEE BILLE SKF 6206', 'PCE', 'MECANIQUE', '', '', 'MP1080A/MP1080B'],
    [2, '', '2400063040', 32, 'ROULEMENT,RIGIDE BILLES SKF 6304', 'PCE', 'MECANIQUE', '', '', 'MP1080A/MP1080B'],
    [3, '', '4515012316', 37, 'VENTILATEUR,REFERENCE 8409F100 REP 36', 'PCE', 'ELECTRICITE', '', '', 'MP1080A/MP1080B'],
    [4, '', '4515012317', 39, 'DIAPHRAGME,ACIER-INOX REF7087P100 REP47', 'PCE', 'ELECTRICITE', '', '', 'MP1080A/MP1080B'],
    [5, 'SAP001', '2400062061', 15, 'JOINT TORIQUE NBR', 'PCE', 'MECANIQUE', 'REF123', 'SKF', 'MP1080A'],
    [6, 'SAP002', '4515012318', 8, 'CARTE ELECTRONIQUE', 'PCE', 'ELECTRICITE', 'REF456', 'SIEMENS', 'MP1080B/MP1080C'],
    [7, 'SAP003', '2400063041', 25, 'PALIER A BILLES', 'PCE', 'MECANIQUE', 'REF789', 'FAG', 'MP1080A/MP1080B/MP1080C'],
    [8, '', '4515012319', 12, 'CAPTEUR DE TEMPERATURE', 'PCE', 'INSTRUMENTATION', '', 'SCHNEIDER', 'MP1080D']
];

// Remplir les données
$row = 2;
foreach ($testData as $data) {
    $sheet->setCellValue('A' . $row, $data[0]); // N°
    $sheet->setCellValue('B' . $row, $data[1]); // CODE SAP
    $sheet->setCellValue('D' . $row, $data[2]); // Code Article (colonne D)
    $sheet->setCellValue('E' . $row, $data[3]); // Qte
    $sheet->setCellValue('F' . $row, $data[4]); // Designation
    $sheet->setCellValue('G' . $row, $data[5]); // Unite
    $sheet->setCellValue('H' . $row, $data[6]); // Metier
    $sheet->setCellValue('I' . $row, $data[7]); // N° PCE
    $sheet->setCellValue('J' . $row, $data[8]); // Fabricant
    $sheet->setCellValue('K' . $row, $data[9]); // Equipement
    $row++;
}

// Style des en-têtes
$sheet->getStyle('A1:K1')->getFont()->setBold(true);
$sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
$sheet->getStyle('A1:K1')->getFill()->getStartColor()->setRGB('366092');
$sheet->getStyle('A1:K1')->getFont()->getColor()->setRGB('FFFFFF');

// Auto-ajuster les colonnes
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Sauvegarder le fichier
$writer = new Xlsx($spreadsheet);
$filename = 'test_import_template_spl.xlsx';
$writer->save($filename);

echo "Fichier Excel de test créé: $filename\n";
echo "Contenu:\n";
echo "- 8 lignes de données\n";
echo "- Équipements multiples testés (séparés par /)\n";
echo "- Structure conforme à l'import SPL\n";
echo "- Métiers: MECANIQUE, ELECTRICITE, INSTRUMENTATION\n";
echo "- Fabricants: SKF, SIEMENS, FAG, SCHNEIDER\n";
echo "\nVous pouvez utiliser ce fichier pour tester l'import!\n";
