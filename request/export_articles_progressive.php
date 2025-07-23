<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Article.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Configuration pour export en temps réel
set_time_limit(300);
ini_set('memory_limit', '1G');
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

function sendProgress($percentage, $message, $processed = 0, $total = 0)
{
    $data = [
        'percentage' => $percentage,
        'message' => $message,
        'processed' => $processed,
        'total' => $total,
        'timestamp' => date('H:i:s')
    ];
    echo "data: " . json_encode($data) . "\n\n";
    flush();
    ob_flush();
}

try {
    $type = $_GET['type'] ?? 'excel';
    $sessionId = $_GET['session'] ?? uniqid();

    // Récupération des filtres
    $filters = [];
    if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
    if (!empty($_GET['code_famille'])) $filters['code_famille'] = $_GET['code_famille'];
    if (!empty($_GET['designation'])) $filters['designation'] = $_GET['designation'];
    if (!empty($_GET['type_quantitatif'])) $filters['type_quantitatif'] = $_GET['type_quantitatif'];

    sendProgress(5, "Initialisation de l'export...");

    // Compter le total d'articles
    if (!empty($filters)) {
        $articles = [];
        $page = 1;
        $limit = 1000;
        $totalCount = 0;

        sendProgress(10, "Récupération des données filtrées...");

        do {
            $result = Article::getPaginated($page, $limit, $filters);
            $articles = array_merge($articles, $result['data']);
            $totalCount += count($result['data']);
            $page++;

            $progressCalc = 10 + (($page - 1) * 5); // 10% à 30% pour la récupération
            if ($progressCalc > 30) $progressCalc = 30;
            sendProgress($progressCalc, "Récupération page $page...", count($articles), $totalCount);
        } while ($result['hasMore'] && count($articles) < 50000);
    } else {
        sendProgress(15, "Récupération de tous les articles...");
        $articles = Article::getAll();
        $totalCount = count($articles);
    }

    sendProgress(35, "Préparation du fichier $type...", 0, $totalCount);

    if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Articles');

        // Définition des colonnes
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

        sendProgress(40, "Création des en-têtes...");

        // Métadonnées
        $title = !empty($filters) ? "Articles filtrés ($totalCount)" : "Tous les articles ($totalCount)";
        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', 'Exporté le : ' . date('d/m/Y à H:i:s'));

        // En-têtes dans la ligne 4
        $headerRow = 4;
        foreach ($columns as $i => $column) {
            $columnLetter = chr(65 + $i);
            $sheet->setCellValue($columnLetter . $headerRow, $column);
        }

        // Style des en-têtes
        $headerRange = 'A' . $headerRow . ':' . chr(64 + count($columns)) . $headerRow;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1976D2']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        sendProgress(45, "Écriture des données...");

        // Traitement par chunks pour progression
        $chunkSize = 500;
        $dataStartRow = $headerRow + 1;
        $processed = 0;

        for ($i = 0; $i < count($articles); $i += $chunkSize) {
            $chunk = array_slice($articles, $i, $chunkSize);

            foreach ($chunk as $index => $article) {
                $row = $dataStartRow + $processed;

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

                $processed++;
            }

            // Calcul progression (45% à 85% pour l'écriture)
            $progress = 45 + (($processed / $totalCount) * 40);
            sendProgress($progress, "Traitement article $processed/$totalCount", $processed, $totalCount);
        }

        sendProgress(90, "Finalisation du fichier...");

        // Auto-ajustement des colonnes
        foreach (range('A', chr(64 + count($columns))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Style du titre
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1976D2']]
        ]);

        sendProgress(95, "Génération du fichier Excel...");

        // Sauvegarde temporaire
        $filename = 'articles_' . date('Y-m-d_H-i-s') . '_' . $sessionId . '.xlsx';
        $tempPath = __DIR__ . '/../tmp/' . $filename;

        // Créer le dossier tmp s'il n'existe pas
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Nettoyage mémoire
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        sendProgress(100, "Export terminé ! Téléchargement...", $totalCount, $totalCount);

        // Envoyer le lien de téléchargement
        echo "data: " . json_encode([
            'percentage' => 100,
            'message' => 'Export terminé',
            'processed' => $totalCount,
            'total' => $totalCount,
            'downloadUrl' => "tmp/$filename",
            'filename' => $filename,
            'completed' => true
        ]) . "\n\n";
    } else { // PDF
        sendProgress(50, "Génération du PDF...");

        // Pour le PDF, limiter à 1000 articles max
        $maxPdfRows = 1000;
        if (count($articles) > $maxPdfRows) {
            $articles = array_slice($articles, 0, $maxPdfRows);
            sendProgress(60, "Limitation PDF à $maxPdfRows articles...");
        }

        require_once('../vendor/autoload.php');

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Nomenclature Equipement');
        $pdf->SetAuthor('Nomenclature Equipement');
        $pdf->SetTitle('Liste des articles');
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();

        sendProgress(70, "Création du PDF...");

        // Contenu simple pour éviter les problèmes de mémoire
        $html = '<h2>Liste des articles (' . count($articles) . ')</h2>';
        $html .= '<table border="1" cellpadding="2" style="font-size:8pt;">';
        $html .= '<tr><th>Code</th><th>Désignation</th><th>Famille</th></tr>';

        foreach ($articles as $index => $article) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($article['code_article'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars(substr($article['designation_article'] ?? '', 0, 50)) . '</td>';
            $html .= '<td>' . htmlspecialchars($article['code_famille'] ?? '') . '</td>';
            $html .= '</tr>';

            if ($index % 100 == 0) {
                $progress = 70 + (($index / count($articles)) * 25);
                sendProgress($progress, "PDF: article $index/" . count($articles), $index, count($articles));
            }
        }

        $html .= '</table>';

        sendProgress(95, "Finalisation PDF...");

        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'articles_' . date('Y-m-d_H-i-s') . '_' . $sessionId . '.pdf';
        $tempPath = __DIR__ . '/../tmp/' . $filename;

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $pdf->Output($tempPath, 'F');

        sendProgress(100, "Export PDF terminé !", count($articles), count($articles));

        echo "data: " . json_encode([
            'percentage' => 100,
            'message' => 'Export PDF terminé',
            'processed' => count($articles),
            'total' => count($articles),
            'downloadUrl' => "tmp/$filename",
            'filename' => $filename,
            'completed' => true
        ]) . "\n\n";
    }
} catch (Exception $e) {
    sendProgress(0, "ERREUR: " . $e->getMessage());
    echo "data: " . json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]) . "\n\n";
}

flush();
ob_flush();
