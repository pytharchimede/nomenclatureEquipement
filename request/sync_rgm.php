<?php
require_once '../model/Nomenclature.php';
session_start();
header('Content-Type: application/json');

$inserted = Nomenclature::syncFromRgmSynthese();
echo json_encode(['success' => true, 'inserted' => $inserted]);
