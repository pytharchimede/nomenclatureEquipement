<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Article.php';
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
if (!empty($_GET['code_famille'])) $filters['code_famille'] = $_GET['code_famille'];
if (!empty($_GET['designation'])) $filters['designation'] = $_GET['designation'];
if (!empty($_GET['type_quantitatif'])) $filters['type_quantitatif'] = $_GET['type_quantitatif'];

// Récupération des articles avec filtres si fournis
if (!empty($_POST['selected_codes'])) {
    // Export de sélection spécifique
    $selectedCodes = json_decode($_POST['selected_codes'], true);
    $articles = [];

    foreach ($selectedCodes as $code) {
        // Utilisation de getPaginated avec un filtre sur le code
        $result = Article::getPaginated(1, 1, ['search' => $code]);
        if (!empty($result['data'])) {
            $articles[] = $result['data'][0];
        }
    }
} elseif (!empty($filters)) {
    // Export filtré - récupération par pagination pour éviter les problèmes mémoire
    $articles = [];
    $page = 1;
    $limit = 1000;

    do {
        $result = Article::getPaginated($page, $limit, $filters);
        $articles = array_merge($articles, $result['data']);
        $page++;
    } while ($result['hasMore'] && count($articles) < 50000); // Limite de sécurité
} else {
    // Export complet - utilisation d'une méthode optimisée
    $articles = Article::getAll();
}

$columns = [
    'Code Article',
    'Désignation Article',
    'Code Famille',
    'Désignation Famille',
    'Type Quantitatif',
    'Unité Quantitatif',
    'Valeur Quantitatif',
    'Date de Création',
    'Date de Modification'
];

if ($type === 'pdf') {
    require_once('../vendor/autoload.php');

    // Limite pour le PDF (pour éviter les gros fichiers)
    $maxPdfRows = 5000;
    if (count($articles) > $maxPdfRows) {
        $articles = array_slice($articles, 0, $maxPdfRows);
        $truncated = true;
    } else {
        $truncated = false;
    }

    // Création du PDF
    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Nomenclature Equipement');
    $pdf->SetAuthor('Nomenclature Equipement');

    // Titre dynamique selon le type d'export
    $title = 'Liste des articles';
    if (!empty($_POST['selected_codes'])) {
        $title = 'Articles sélectionnés (' . count($articles) . ')';
    } elseif (!empty($filters)) {
        $title = 'Articles filtrés (' . count($articles) . ')';
    } else {
        $title = 'Tous les articles (' . count($articles) . ')';
    }

    $pdf->SetTitle($title);
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();

    // En-tête du document
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->Ln(5);

    // Date d'export
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 8, 'Exporté le : ' . date('d/m/Y à H:i'), 0, 1, 'R');

    if ($truncated) {
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(0, 8, 'ATTENTION : Liste tronquée à ' . $maxPdfRows . ' articles', 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
    }

    $pdf->Ln(5);

    // En-têtes du tableau
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(230, 230, 230);

    // Largeurs des colonnes adaptées au format paysage
    $colWidths = [30, 50, 25, 40, 25, 20, 25, 25, 25];

    foreach ($columns as $i => $column) {
        $pdf->Cell($colWidths[$i], 8, $column, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Données
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetFillColor(245, 245, 245);

    foreach ($articles as $index => $article) {
        if ($index % 2 == 1) {
            $fill = true;
        } else {
            $fill = false;
        }

        $dateCreation = !empty($article['date_creation']) ? date('d/m/Y', strtotime($article['date_creation'])) : '';
        $dateModification = !empty($article['date_modification']) ? date('d/m/Y', strtotime($article['date_modification'])) : '';

        $data = [
            $article['code_article'] ?? '',
            substr($article['designation_article'] ?? '', 0, 35), // Limiter la longueur
            $article['code_famille'] ?? '',
            substr($article['designation_famille'] ?? '', 0, 30),
            $article['type_quantitatif'] ?? '',
            $article['unite_quantitatif'] ?? '',
            $article['valeur_quantitatif'] ?? '',
            $dateCreation,
            $dateModification
        ];

        foreach ($data as $i => $value) {
            $pdf->Cell($colWidths[$i], 6, $value, 1, 0, 'C', $fill);
        }
        $pdf->Ln();

        // Vérifier si on a besoin d'une nouvelle page
        if ($pdf->GetY() > 180) {
            $pdf->AddPage();

            // Répéter les en-têtes
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(230, 230, 230);

            foreach ($columns as $i => $column) {
                $pdf->Cell($colWidths[$i], 8, $column, 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetFont('helvetica', '', 7);
        }
    }

    // Pied de page avec résumé
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell(0, 8, 'Total : ' . count($articles) . ' articles', 0, 1, 'L');

    // Sortie du PDF
    $filename = 'articles_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
} else {
    // Export Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Définition du titre
    $title = 'Articles Export';
    if (!empty($_POST['selected_codes'])) {
        $title = 'Articles sélectionnés (' . count($articles) . ')';
    } elseif (!empty($filters)) {
        $title = 'Articles filtrés (' . count($articles) . ')';
    } else {
        $title = 'Tous les articles (' . count($articles) . ')';
    }

    $sheet->setTitle('Articles');

    // Métadonnées
    $sheet->setCellValue('A1', $title);
    $sheet->setCellValue('A2', 'Exporté le : ' . date('d/m/Y à H:i:s'));

    // En-têtes dans la ligne 4
    $headerRow = 4;
    foreach ($columns as $i => $column) {
        $columnLetter = chr(65 + $i); // A, B, C, etc.
        $sheet->setCellValue($columnLetter . $headerRow, $column);
    }

    // Style des en-têtes
    $headerRange = 'A' . $headerRow . ':' . chr(64 + count($columns)) . $headerRow;
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1976D2']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);

    // Données
    $dataStartRow = $headerRow + 1;
    foreach ($articles as $index => $article) {
        $row = $dataStartRow + $index;

        $dateCreation = !empty($article['date_creation']) ? date('d/m/Y H:i', strtotime($article['date_creation'])) : '';
        $dateModification = !empty($article['date_modification']) ? date('d/m/Y H:i', strtotime($article['date_modification'])) : '';

        $sheet->setCellValue('A' . $row, $article['code_article'] ?? '');
        $sheet->setCellValue('B' . $row, $article['designation_article'] ?? '');
        $sheet->setCellValue('C' . $row, $article['code_famille'] ?? '');
        $sheet->setCellValue('D' . $row, $article['designation_famille'] ?? '');
        $sheet->setCellValue('E' . $row, $article['type_quantitatif'] ?? '');
        $sheet->setCellValue('F' . $row, $article['unite_quantitatif'] ?? '');
        $sheet->setCellValue('G' . $row, $article['valeur_quantitatif'] ?? '');
        $sheet->setCellValue('H' . $row, $dateCreation);
        $sheet->setCellValue('I' . $row, $dateModification);

        // Alternance des couleurs pour les lignes
        if ($index % 2 == 1) {
            $rowRange = 'A' . $row . ':' . chr(64 + count($columns)) . $row;
            $sheet->getStyle($rowRange)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']]
            ]);
        }
    }

    // Auto-ajustement des colonnes
    foreach (range('A', chr(64 + count($columns))) as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Style du titre
    $sheet->getStyle('A1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1976D2']]
    ]);

    // Bordures pour toutes les données
    $lastRow = $dataStartRow + count($articles) - 1;
    $dataRange = 'A' . $headerRow . ':' . chr(64 + count($columns)) . $lastRow;
    $sheet->getStyle($dataRange)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
    ]);

    // Figer les en-têtes
    $sheet->freezePane('A' . ($headerRow + 1));

    // Ajout d'un résumé en bas
    $summaryRow = $lastRow + 3;
    $sheet->setCellValue('A' . $summaryRow, 'Résumé :');
    $sheet->setCellValue('A' . ($summaryRow + 1), 'Total articles : ' . count($articles));

    if (!empty($filters)) {
        $filterText = 'Filtres appliqués : ';
        $filterParts = [];

        if (!empty($filters['search'])) $filterParts[] = 'Recherche: "' . $filters['search'] . '"';
        if (!empty($filters['code_famille'])) $filterParts[] = 'Famille: ' . $filters['code_famille'];
        if (!empty($filters['designation'])) $filterParts[] = 'Désignation: "' . $filters['designation'] . '"';
        if (!empty($filters['type_quantitatif'])) $filterParts[] = 'Type: ' . $filters['type_quantitatif'];

        $sheet->setCellValue('A' . ($summaryRow + 2), $filterText . implode(', ', $filterParts));
    }

    // Configuration de l'en-tête de téléchargement
    $filename = 'articles_' . date('Y-m-d_H-i-s') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    // Sauvegarde et envoi
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

// Nettoyage mémoire
$spreadsheet = null;
unset($articles);
exit;
