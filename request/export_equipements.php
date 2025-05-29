<?php
require_once '../model/Database.php';
require_once '../model/Equipement.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;


$type = $_GET['type'] ?? 'excel';
$equipements = Equipement::getAll();

$columns = [
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

if ($type === 'pdf') {
    require_once('../vendor/autoload.php');
    // Limite à 1000 lignes pour éviter les soucis mémoire
    $maxPdfRows = 1000;
    if (count($equipements) > $maxPdfRows) {
        $equipements = array_slice($equipements, 0, $maxPdfRows);
    }

    // Création du PDF
    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Nomenclature Equipement');
    $pdf->SetAuthor('Nomenclature Equipement');
    $pdf->SetTitle('Liste des équipements');
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();

    // Styles
    $styleHeader = 'background-color:#1976d2;color:#fff;font-weight:bold;text-align:center;';
    $styleTotal = 'font-weight:bold;color:#388E3C;';

    // Table HTML
    $html = '<h2 style="color:#1976d2;">Liste des équipements</h2>';
    $html .= '<table border="1" cellpadding="3" cellspacing="0" style="font-size:11pt;width:100%">';
    $html .= '<thead><tr>';
    foreach ($columns as $col) {
        $html .= '<th style="' . $styleHeader . '">' . htmlspecialchars($col) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($equipements as $eq) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($eq['code_equipement']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['designation_equipement']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['repere_equipement']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['fabricant']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['type_objet']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['designation_type']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['numero_serie_fabricant']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['numero_piece_fabricant']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['poste_technique']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['designation_poste_technique']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['poste_travail_principal']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['categorie_equipement']) . '</td>';
        $html .= '<td>' . htmlspecialchars($eq['centre_de_couts']) . '</td>';
        $html .= '<td>' . (!empty($eq['date_creation']) ? date('d/m/Y', strtotime($eq['date_creation'])) : '') . '</td>';
        $html .= '</tr>';
    }

    // Ligne de total
    $html .= '<tr><td style="' . $styleTotal . '">Total équipements :</td><td colspan="' . (count($columns) - 1) . '" style="' . $styleTotal . '">' . count($equipements) . '</td></tr>';
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('equipements_export.pdf', 'D');
    exit;
}

// --- EXPORT FILTRÉ ---
if (isset($_GET['filtered']) && $_GET['filtered'] == 1 && isset($_POST['filtered_data'])) {
    $columns = [
        'Code Equipement',
        'Désignation équipement',
        'Repère équipement',
        'Fabricant',
        "Type d'objet",
        'N° série fabricant',
        'Catégorie équipement',
        'Date création'
    ];
    $filteredData = json_decode($_POST['filtered_data'], true);

    // Récupère les filtres
    $filters = [];
    if (isset($_POST['filters'])) {
        $filters = json_decode($_POST['filters'], true);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Équipements filtrés');

    $rowNum = 1;

    // Affiche les critères de recherche non vides
    if (!empty($filters)) {
        $sheet->setCellValue('A' . $rowNum, 'Critères de recherche utilisés :');
        $rowNum++;
        foreach ($filters as $filter) {
            $label = $columns[$filter['col']] ?? $filter['label'];
            $sheet->setCellValue('A' . $rowNum, $label);
            $sheet->setCellValue('B' . $rowNum, $filter['value']);
            $rowNum++;
        }
        $rowNum++; // Ligne vide avant l'entête
    }

    // Entête
    $colLetters = range('A', 'Z');
    foreach ($columns as $i => $col) {
        $cell = $colLetters[$i] . $rowNum;
        $sheet->setCellValue($cell, $col);
    }

    // Style entête
    $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1976D2']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1976D2']]]
    ]);
    $sheet->freezePane('A' . ($rowNum + 1));

    // Largeur auto
    foreach ($colLetters as $i => $col) {
        if ($i >= count($columns)) break;
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Données filtrées
    $rowNum++;
    foreach ($filteredData as $row) {
        foreach ($row as $i => $val) {
            $sheet->setCellValue($colLetters[$i] . $rowNum, $val);
        }
        $rowNum++;
    }

    // Bordures sur tout le tableau
    $sheet->getStyle('A1:H' . ($rowNum - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'B0BEC5']
            ]
        ]
    ]);

    // Ligne de total en bas
    $sheet->setCellValue('A' . $rowNum, 'Total équipements :');
    $sheet->setCellValue('B' . $rowNum, count($filteredData));
    $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => '388E3C']]
    ]);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="equipements_filtrés.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// --- EXPORT COMPLET ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Équipements');

// Entête
$colLetters = range('A', 'Z');
foreach ($columns as $i => $col) {
    $cell = $colLetters[$i] . '1';
    $sheet->setCellValue($cell, $col);
}

// Style entête
$sheet->getStyle('A1:N1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1976D2']],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1976D2']]]
]);

$sheet->freezePane('A2');

// Largeur auto
foreach ($colLetters as $i => $col) {
    if ($i >= count($columns)) break;
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Données
$rowNum = 2;
foreach ($equipements as $eq) {
    $sheet->setCellValue('A' . $rowNum, $eq['code_equipement']);
    $sheet->setCellValue('B' . $rowNum, $eq['designation_equipement']);
    $sheet->setCellValue('C' . $rowNum, $eq['repere_equipement']);
    $sheet->setCellValue('D' . $rowNum, $eq['fabricant']);
    $sheet->setCellValue('E' . $rowNum, $eq['type_objet']);
    $sheet->setCellValue('F' . $rowNum, $eq['designation_type']);
    $sheet->setCellValue('G' . $rowNum, $eq['numero_serie_fabricant']);
    $sheet->setCellValue('H' . $rowNum, $eq['numero_piece_fabricant']);
    $sheet->setCellValue('I' . $rowNum, $eq['poste_technique']);
    $sheet->setCellValue('J' . $rowNum, $eq['designation_poste_technique']);
    $sheet->setCellValue('K' . $rowNum, $eq['poste_travail_principal']);
    $sheet->setCellValue('L' . $rowNum, $eq['categorie_equipement']);
    $sheet->setCellValue('M' . $rowNum, $eq['centre_de_couts']);
    if (!empty($eq['date_creation'])) {
        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($eq['date_creation']));
        $sheet->setCellValue('N' . $rowNum, $date);
        $sheet->getStyle('N' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
    }
    $rowNum++;
}

// Bordures sur tout le tableau
$sheet->getStyle('A1:N' . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'B0BEC5']
        ]
    ]
]);

// Ligne de total en bas
$sheet->setCellValue('A' . $rowNum, 'Total équipements :');
$sheet->setCellValue('B' . $rowNum, '=COUNTA(A2:A' . ($rowNum - 1) . ')');
$sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => '388E3C']]
]);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="equipements_export.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
