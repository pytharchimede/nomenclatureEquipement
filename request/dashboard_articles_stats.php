<?php
require_once '../model/Database.php';
require_once '../model/Article.php';
header('Content-Type: application/json');

$articles = Article::getAll();
$stats = [];
foreach ($articles as $art) {
    $famille = $art['groupe_articles'] ?? 'Non défini';
    $stats[$famille] = ($stats[$famille] ?? 0) + 1;
}
echo json_encode([
    'labels' => array_keys($stats),
    'values' => array_values($stats)
]);