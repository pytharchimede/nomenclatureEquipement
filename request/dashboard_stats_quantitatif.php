<?php
require_once '../model/Quantitatif.php';

$data = Quantitatif::countEquipementsByFamille();
$labels = [];
$values = [];
foreach ($data as $row) {
    $labels[] = $row['famille'];
    $values[] = (int)$row['nb'];
}
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
