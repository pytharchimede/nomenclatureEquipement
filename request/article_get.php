<?php
require_once '../model/Article.php';
header('Content-Type: application/json');
$id = $_GET['id'] ?? 0;
echo json_encode(Article::getById($id));
