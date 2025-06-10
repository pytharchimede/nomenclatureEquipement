<?php
require_once '../model/Database.php';
require_once '../model/Nomenclature.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$type = $_GET['type'] ?? 'excel';

$columns = [
    'Repère équipement',      // <-- repère en premier
    'Code équipement',
    'Code article',
    'Désignation équipement',
    'Fabricant',
    'Type',
    'N° série fabricant',
    'Désignation article',
    'N° poste',
    'Quantité',
    'Unité',
    'Poste technique',
    'Métier',
    'Date création',
    'Source'
];
$fields = [
    'repere_equipement',      // <-- repère en premier
    'code_equipement',
    'code_article',
    'designation_equipement',
    'fabricant',
    'type',
    'numero_serie_fabricant',
    'designation_article',
    'numero_poste',
    'quantite',
    'unite',
    'poste_technique',
    'metier',
    'date_creation',
    'source'
];

// --- EXPORT PDF ---
if ($type === 'pdf') {
    require_once('../vendor/autoload.php');
    $nomenclatures = Nomenclature::getAll();
    $maxPdfRows = 1000;
    if (count($nomenclatures) > $maxPdfRows) {
        $nomenclatures = array_slice($nomenclatures, 0, $maxPdfRows);
    }

    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Nomenclature SI');
    $pdf->SetAuthor('Nomenclature SI');
    $pdf->SetTitle('Export Nomenclatures');
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();

    $styleHeader = 'background-color:#1976d2;color:#fff;font-weight:bold;text-align:center;';
    $styleTotal = 'font-weight:bold;color:#388E3C;';

    $html = '<h2 style="color:#1976d2;">Liste des nomenclatures</h2>';
    $html .= '<table border="1" cellpadding="3" cellspacing="0" style="font-size:11pt;width:100%">';
    $html .= '<thead><tr>';
    foreach ($columns as $col) {
        $html .= '<th style="' . $styleHeader . '">' . htmlspecialchars($col) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($nomenclatures as $nom) {
        $html .= '<tr>';
        foreach ($fields as $f) {
            if ($f === 'date_creation' && !empty($nom[$f])) {
                $html .= '<td>' . date('d/m/Y', strtotime($nom[$f])) . '</td>';
            } else {
                $html .= '<td>' . htmlspecialchars($nom[$f] ?? '') . '</td>';
            }
        }
        $html .= '</tr>';
    }
    $html .= '<tr><td style="' . $styleTotal . '">Total nomenclatures :</td><td colspan="' . (count($columns) - 1) . '" style="' . $styleTotal . '">' . count($nomenclatures) . '</td></tr>';
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('nomenclatures_export.pdf', 'D');
    exit;
}

// --- EXPORT FILTRÉ ---
if (isset($_GET['filtered']) && $_GET['filtered'] == 1 && isset($_POST['filtered_data'])) {
    $filteredData = json_decode($_POST['filtered_data'], true);
    $filters = [];
    if (isset($_POST['filters'])) {
        $filters = json_decode($_POST['filters'], true);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Nomenclatures filtrées');

    $rowNum = 1;
    $colLetters = range('A', 'Z');

    // Critères de recherche
    if (!empty($filters)) {
        $sheet->setCellValue('A' . $rowNum, 'Critères de recherche utilisés :');
        $rowNum++;
        foreach ($filters as $filter) {
            $label = $columns[$filter['col']] ?? $filter['label'];
            $sheet->setCellValue('A' . $rowNum, $label);
            $sheet->setCellValue('B' . $rowNum, $filter['value']);
            $rowNum++;
        }
        $rowNum++;
    }

    // Entête
    foreach ($columns as $i => $col) {
        $cell = $colLetters[$i] . $rowNum;
        $sheet->setCellValue($cell, $col);
    }
    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1976D2']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1976D2']]]
    ]);
    $sheet->freezePane('A' . ($rowNum + 1));

    foreach ($colLetters as $i => $col) {
        if ($i >= count($columns)) break;
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Données filtrées
    $rowNum++;
    foreach ($filteredData as $row) {
        foreach ($row as $i => $val) {
            // Date formatée si colonne date_creation
            if ($columns[$i] === 'Date création' && !empty($val)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($val));
                $sheet->setCellValue($colLetters[$i] . $rowNum, $date);
                $sheet->getStyle($colLetters[$i] . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
            } else {
                $sheet->setCellValue($colLetters[$i] . $rowNum, htmlspecialchars((string)($val ?? '')));
            }
        }
        $rowNum++;
    }

    // Bordures
    $sheet->getStyle('A1:O' . ($rowNum - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'B0BEC5']
            ]
        ]
    ]);

    // Ligne de total
    $sheet->setCellValue('A' . $rowNum, 'Total nomenclatures :');
    $sheet->setCellValue('B' . $rowNum, count($filteredData));
    $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => '388E3C']]
    ]);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="nomenclatures_filtres.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// --- EXPORT COMPLET ---
$nomenclatures = Nomenclature::getAll();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Nomenclatures');

$colLetters = range('A', 'Z');

// Entête
foreach ($columns as $i => $col) {
    $cell = $colLetters[$i] . '1';
    $sheet->setCellValue($cell, $col);
}
$sheet->getStyle('A1:O1')->applyFromArray([
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
foreach ($nomenclatures as $nom) {
    foreach ($fields as $i => $f) {
        if ($f === 'date_creation' && !empty($nom[$f])) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($nom[$f]));
            $sheet->setCellValue($colLetters[$i] . $rowNum, $date);
            $sheet->getStyle($colLetters[$i] . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
        } else {
            $sheet->setCellValue($colLetters[$i] . $rowNum, $nom[$f] ?? '');
        }
    }
    $rowNum++;
}

// Bordures
$sheet->getStyle('A1:O' . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'B0BEC5']
        ]
    ]
]);

// Ligne de total
$sheet->setCellValue('A' . $rowNum, 'Total nomenclatures :');
$sheet->setCellValue('B' . $rowNum, '=COUNTA(A2:A' . ($rowNum - 1) . ')');
$sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => '388E3C']]
]);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="nomenclatures_export.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
