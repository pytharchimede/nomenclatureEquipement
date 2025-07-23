<?php
// Version debug de l'export
ob_start();

require_once __DIR__ . '/model/Database.php';
require_once __DIR__ . '/model/Article.php';
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $type = $_GET['type'] ?? 'excel';
    $articles = Article::getAll();

    if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $sheet->setCellValue('A1', 'Code Article');
        $sheet->setCellValue('B1', 'Désignation');
        $sheet->setCellValue('C1', 'Famille');

        // Quelques données de test
        $row = 2;
        foreach (array_slice($articles, 0, 5) as $article) {
            $sheet->setCellValue('A' . $row, $article['code_article']);
            $sheet->setCellValue('B' . $row, $article['designation_article']);
            $sheet->setCellValue('C' . $row, $article['groupe_articles'] ?? '');
            $row++;
        }

        // Nettoyer le buffer de sortie
        $output = ob_get_clean();
        if (!empty($output)) {
            file_put_contents('debug_output.txt', "Output avant en-têtes: " . $output);
        }

        // En-têtes
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="test_debug.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    } else {
        ob_end_clean();
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment;filename="test_debug.txt"');
        echo "PDF debug - Articles: " . count($articles);
    }
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: text/plain');
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}

exit;
