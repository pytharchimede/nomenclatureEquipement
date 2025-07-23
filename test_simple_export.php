<?php
require_once __DIR__ . '/model/Database.php';
require_once __DIR__ . '/model/Article.php';

$type = $_GET['type'] ?? 'excel';
$articles = Article::getAll();

if ($type === 'excel') {
    // Test simple sans PhpSpreadsheet
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="articles_test.xls"');

    echo "Code Article\tDésignation\tFamille\n";
    foreach (array_slice($articles, 0, 10) as $article) {
        echo $article['code_article'] . "\t" . $article['designation_article'] . "\t" . $article['groupe_articles'] . "\n";
    }
} else {
    // Test simple pour PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="articles_test.pdf"');
    echo "PDF Test - Articles: " . count($articles);
}
exit;
