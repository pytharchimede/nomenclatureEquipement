<?php
require_once 'model/Database.php';

$pdo = Database::getConnection();
$stmt = $pdo->query('SELECT COUNT(*) FROM nomenclatures');
echo 'Total nomenclatures: ' . $stmt->fetchColumn() . PHP_EOL;

$stmt = $pdo->query('SELECT * FROM nomenclatures LIMIT 2');
$nomenclatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo 'Échantillon: ' . count($nomenclatures) . ' trouvés' . PHP_EOL;

if (!empty($nomenclatures)) {
    foreach ($nomenclatures as $nom) {
        echo "ID: {$nom['id']}, Repere: {$nom['repere_equipement']}, Article: {$nom['code_article']}" . PHP_EOL;
    }
}
