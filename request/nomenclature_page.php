<?php
require_once '../model/Nomenclature.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = ($page - 1) * $limit;

// Récupérer les filtres envoyés en GET ou POST
$filters = [];
if (!empty($_GET['filters'])) {
    $filters = json_decode($_GET['filters'], true);
}

$data = Nomenclature::getPageFiltered($offset, $limit, $filters);
$total = Nomenclature::countFiltered($filters);

echo json_encode([
    'data' => $data,
    'total' => $total
]);
