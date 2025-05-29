<?php
require_once '../model/Database.php';
require_once '../model/Article.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$type = $_GET['type'] ?? 'excel';
$articles = Article::getAll();

$columns = [
    'Code Article',
    'Désignation Article',
    "Type d'article",
    'TémSup.:niv.mdt',
    'Anc. n° article',
    'UQ base',
    'Fabricant',
    'N° pce fabricant',
    'Famille',
    'Gpe march.ext.',
    'Document',
    'Description',
    'Date création',
    'Créé par'
];

// --- EXPORT PDF EN PREMIER ---
if ($type === 'pdf') {
    require_once('../vendor/autoload.php');
    // Limite à 1000 lignes pour éviter les soucis mémoire
    $maxPdfRows = 1000;
    if (count($articles) > $maxPdfRows) {
        $articles = array_slice($articles, 0, $maxPdfRows);
    }

    // Création du PDF
    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Nomenclature Articles');
    $pdf->SetAuthor('Nomenclature Articles');
    $pdf->SetTitle('Liste des articles');
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();

    // Styles
    $styleHeader = 'background-color:#1976d2;color:#fff;font-weight:bold;text-align:center;';
    $styleTotal = 'font-weight:bold;color:#388E3C;';

    // Table HTML
    $html = '<h2 style="color:#1976d2;">Liste des articles</h2>';
    $html .= '<table border="1" cellpadding="3" cellspacing="0" style="font-size:11pt;width:100%">';
    $html .= '<thead><tr>';
    foreach ($columns as $col) {
        $html .= '<th style="' . $styleHeader . '">' . htmlspecialchars($col) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($articles as $art) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($art['code_article'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['designation_article'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['type_article'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['temsup_niv_mdt'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['ancien_num_article'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['uq_base'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['fabricant'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['numero_piece_fabricant'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['groupe_articles'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['groupe_marche_externe'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['document'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['description'] ?? '') . '</td>';
        $html .= '<td>' . (!empty($art['date_creation']) ? date('d/m/Y', strtotime($art['date_creation'])) : '') . '</td>';
        $html .= '<td>' . htmlspecialchars($art['cree_par'] ?? '') . '</td>';
        $html .= '</tr>';
    }

    // Ligne de total
    $html .= '<tr><td style="' . $styleTotal . '">Total articles :</td><td colspan="' . (count($columns) - 1) . '" style="' . $styleTotal . '">' . count($articles) . '</td></tr>';
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('articles_export.pdf', 'D');
    exit;
}

// --- EXPORT FILTRÉ ---
if (isset($_GET['filtered']) && $_GET['filtered'] == 1 && isset($_POST['filtered_data'])) {
    $filteredData = json_decode($_POST['filtered_data'], true);

    // Récupère les filtres
    $filters = [];
    if (isset($_POST['filters'])) {
        $filters = json_decode($_POST['filters'], true);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Articles filtrés');

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
    $sheet->getStyle('A' . $rowNum . ':N' . $rowNum)->applyFromArray([
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
            $sheet->setCellValue($colLetters[$i] . $rowNum, htmlspecialchars((string)($val ?? '')));
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
    $sheet->setCellValue('A' . $rowNum, 'Total articles :');
    $sheet->setCellValue('B' . $rowNum, count($filteredData));
    $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => '388E3C']]
    ]);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="articles_filtres.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// --- EXPORT COMPLET ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Articles');

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
foreach ($articles as $art) {
    $sheet->setCellValue('A' . $rowNum, $art['code_article']);
    $sheet->setCellValue('B' . $rowNum, $art['designation_article']);
    $sheet->setCellValue('C' . $rowNum, $art['type_article']);
    $sheet->setCellValue('D' . $rowNum, $art['temsup_niv_mdt']);
    $sheet->setCellValue('E' . $rowNum, $art['ancien_num_article']);
    $sheet->setCellValue('F' . $rowNum, $art['uq_base']);
    $sheet->setCellValue('G' . $rowNum, $art['fabricant']);
    $sheet->setCellValue('H' . $rowNum, $art['numero_piece_fabricant']);
    $sheet->setCellValue('I' . $rowNum, $art['groupe_articles']);
    $sheet->setCellValue('J' . $rowNum, $art['groupe_marche_externe']);
    $sheet->setCellValue('K' . $rowNum, $art['document']);
    $sheet->setCellValue('L' . $rowNum, $art['description']);
    if (!empty($art['date_creation'])) {
        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($art['date_creation']));
        $sheet->setCellValue('M' . $rowNum, $date);
        $sheet->getStyle('M' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
    }
    $sheet->setCellValue('N' . $rowNum, $art['cree_par']);
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
$sheet->setCellValue('A' . $rowNum, 'Total articles :');
$sheet->setCellValue('B' . $rowNum, '=COUNTA(A2:A' . ($rowNum - 1) . ')');
$sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => '388E3C']]
]);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="articles_export.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
