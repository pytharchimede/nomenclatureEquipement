<?php
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();

    // Récupérer toutes les sources distinctes
    $stmt = $pdo->query("SELECT DISTINCT source FROM nomenclatures WHERE source IS NOT NULL ORDER BY source");
    $sources = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Sources distinctes trouvées dans la table nomenclatures:\n";
    foreach ($sources as $source) {
        echo "- " . $source . "\n";
    }

    // Compter les articles par source
    echo "\nNombre d'enregistrements par source:\n";
    $stmt = $pdo->query("SELECT source, COUNT(*) as count FROM nomenclatures WHERE source IS NOT NULL GROUP BY source ORDER BY count DESC");
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($counts as $count) {
        echo "- " . $count['source'] . ": " . $count['count'] . " enregistrements\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
