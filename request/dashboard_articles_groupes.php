<?php
require_once '../model/Article.php';

$data = Article::countByGroupeArticle();
$labels = [];
$values = [];
foreach ($data as $row) {
    $labels[] = $row['groupe_articles'] ?? '(Non défini)';
    $values[] = (int)$row['nb'];
}
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
