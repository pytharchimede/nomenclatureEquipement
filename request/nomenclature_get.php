<?php
require_once '../model/Nomenclature.php';
header('Content-Type: application/json');
$id = $_GET['id'] ?? 0;
echo json_encode(Nomenclature::getById($id));
