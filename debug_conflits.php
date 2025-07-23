<?php
require_once 'model/Database.php';

$pdo = Database::getConnection();
$stmt = $pdo->query('SELECT id, details_conflit FROM nomenclatures_doublons_import LIMIT 1');
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Structure des details_conflit:\n";
echo $result['details_conflit'] . "\n\n";

echo "Parsed JSON:\n";
$parsed = json_decode($result['details_conflit'], true);
print_r($parsed);
