<?php
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();
    echo "✅ Connexion réussie\n";

    // Vérification des sources disponibles
    $stmt = $pdo->query("SELECT DISTINCT source FROM nomenclatures WHERE source IS NOT NULL ORDER BY source");
    $sources = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "📊 Sources disponibles: " . implode(', ', $sources) . "\n";

    // Comptage par source
    $stmt = $pdo->query("SELECT source, COUNT(*) as total FROM nomenclatures WHERE source IS NOT NULL GROUP BY source ORDER BY total DESC");
    $sourcesCount = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📈 Répartition par source:\n";
    foreach ($sourcesCount as $sc) {
        echo "  - " . $sc['source'] . ": " . $sc['total'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
