<?php
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();

    // Vérifier si la table nomenclatures existe
    $stmt = $pdo->query('DESCRIBE nomenclatures');
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "✅ Table nomenclatures trouvée avec " . count($columns) . " colonnes:\n";
    foreach ($columns as $col) {
        echo "- " . $col . "\n";
    }

    // Compter les lignes
    $stmt = $pdo->query('SELECT COUNT(*) FROM nomenclatures');
    $count = $stmt->fetchColumn();
    echo "\n📊 Nombre total de nomenclatures: " . $count . "\n";

    // Test de récupération des premières lignes
    $stmt = $pdo->query('SELECT code_equipement, code_article, repere_equipement FROM nomenclatures LIMIT 3');
    $sample = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n🔍 Échantillon des 3 premiers enregistrements:\n";
    foreach ($sample as $row) {
        echo "- " . ($row['code_equipement'] ?? 'N/A') . " | " . ($row['code_article'] ?? 'N/A') . " | " . ($row['repere_equipement'] ?? 'N/A') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
