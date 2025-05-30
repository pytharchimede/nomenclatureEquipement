<?php
require_once '../model/Database.php';
require_once '../model/Article.php';
header('Content-Type: application/json');

$data = Article::countByTypeArticle();
$labels = [];
$values = [];
foreach ($data as $row) {
    $labels[] = $row['type_article'];
    $values[] = (int)$row['nb'];
}
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
