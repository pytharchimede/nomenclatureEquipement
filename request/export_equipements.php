<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Equipement.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Configuration pour gros volumes
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '512M');

$type = $_GET['type'] ?? 'excel';

// Récupération des filtres de la session ou paramètres URL
$filters = [];
if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
if (!empty($_GET['fabricant'])) $filters['fabricant'] = $_GET['fabricant'];
if (!empty($_GET['type_objet'])) $filters['type_objet'] = $_GET['type_objet'];
if (!empty($_GET['categorie_equipement'])) $filters['categorie_equipement'] = $_GET['categorie_equipement'];

// Récupération des équipements avec filtres si fournis
if (!empty($_POST['selected_reperes'])) {
    // Export de sélection spécifique
    $selectedReperes = json_decode($_POST['selected_reperes'], true);
    $equipements = [];

    foreach ($selectedReperes as $repere) {
        $equipement = Equipement::getByRepere($repere);
        if ($equipement) {
            $equipements[] = $equipement;
        }
    }
} elseif (!empty($filters)) {
    // Export filtré - récupération par pagination pour éviter les problèmes mémoire
    $equipements = [];
    $page = 1;
    $limit = 1000;

    do {
        $result = Equipement::getPaginated($page, $limit, $filters);
        $equipements = array_merge($equipements, $result['data']);
        $page++;
    } while ($result['hasMore'] && count($equipements) < 50000); // Limite de sécurité
} else {
    // Export complet - utilisation d'une méthode optimisée
    $equipements = Equipement::getAll();
}

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

    // Limite pour le PDF (pour éviter les gros fichiers)
    $maxPdfRows = 5000;
    if (count($equipements) > $maxPdfRows) {
        $equipements = array_slice($equipements, 0, $maxPdfRows);
        $truncated = true;
    } else {
        $truncated = false;
    }

    // Création du PDF
    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Nomenclature Equipement');
    $pdf->SetAuthor('Nomenclature Equipement');

    // Titre dynamique selon le type d'export
    $title = 'Liste des équipements';
    if (!empty($_POST['selected_reperes'])) {
        $title = 'Équipements sélectionnés (' . count($equipements) . ')';
    } elseif (!empty($filters)) {
        $title = 'Équipements filtrés (' . count($equipements) . ')';
    } else {
        $title = 'Tous les équipements (' . count($equipements) . ')';
    }

    $pdf->SetTitle($title);
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();

    // Styles
    $styleHeader = 'background-color:#1976d2;color:#fff;font-weight:bold;text-align:center;font-size:10px;';
    $styleTotal = 'font-weight:bold;color:#388E3C;';
    $styleInfo = 'color:#666;font-size:9px;';

    // En-tête avec informations sur l'export
    $html = '<h2 style="color:#1976d2;">' . $title . '</h2>';

    // Informations sur les filtres appliqués
    if (!empty($filters)) {
        $html .= '<div style="' . $styleInfo . 'margin-bottom:10px;">';
        $html .= '<strong>Filtres appliqués :</strong><br>';
        foreach ($filters as $key => $value) {
            $filterName = match ($key) {
                'search' => 'Recherche',
                'fabricant' => 'Fabricant',
                'type_objet' => 'Type d\'objet',
                'categorie_equipement' => 'Catégorie',
                default => $key
            };
            $html .= '• ' . $filterName . ' : "' . htmlspecialchars($value) . '"<br>';
        }
        $html .= '</div>';
    }

    if ($truncated) {
        $html .= '<div style="color:#f44336;font-weight:bold;margin-bottom:10px;">⚠️ Export limité aux ' . $maxPdfRows . ' premiers résultats</div>';
    }

    $html .= '<div style="' . $styleInfo . 'margin-bottom:10px;">Export généré le ' . date('d/m/Y à H:i') . '</div>';

    // Colonnes à afficher (simplifiées pour le PDF)
    $pdfColumns = [
        'Code',
        'Repère',
        'Désignation',
        'Fabricant',
        'Type',
        'N° Série',
        'Catégorie',
        'Date'
    ];

    // Table HTML
    $html .= '<table border="1" cellpadding="2" cellspacing="0" style="font-size:8pt;width:100%">';
    $html .= '<thead><tr>';
    foreach ($pdfColumns as $col) {
        $html .= '<th style="' . $styleHeader . '">' . htmlspecialchars($col) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($equipements as $eq) {
        $html .= '<tr>';
        $html .= '<td style="font-size:8px;">' . htmlspecialchars($eq['code_equipement'] ?? '') . '</td>';
        $html .= '<td style="font-size:8px;font-weight:bold;">' . htmlspecialchars($eq['repere_equipement'] ?? '') . '</td>';
        $html .= '<td style="font-size:8px;">' . htmlspecialchars(substr($eq['designation_equipement'] ?? '', 0, 30)) . '</td>';
        $html .= '<td style="font-size:8px;">' . htmlspecialchars($eq['fabricant'] ?? '') . '</td>';
        $html .= '<td style="font-size:8px;">' . htmlspecialchars($eq['type_objet'] ?? '') . '</td>';
        $html .= '<td style="font-size:8px;">' . htmlspecialchars($eq['numero_serie_fabricant'] ?? '') . '</td>';
        $html .= '<td style="font-size:8px;">' . htmlspecialchars($eq['categorie_equipement'] ?? '') . '</td>';
        $html .= '<td style="font-size:8px;">' . (!empty($eq['date_creation']) ? date('d/m/Y', strtotime($eq['date_creation'])) : '') . '</td>';
        $html .= '</tr>';
    }

    // Ligne de total
    $html .= '</tbody></table>';
    $html .= '<div style="' . $styleTotal . 'margin-top:10px;">Total : ' . count($equipements) . ' équipements</div>';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Nom de fichier dynamique
    $filename = 'equipements_export_' . date('Ymd_His') . '.pdf';
    $pdf->Output($filename, 'D');
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
    $sheet->setCellValue('C' . $rowNum, $eq['repere_equipement']); // repère bien exporté
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
