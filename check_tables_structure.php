<?php
require_once 'model/Database.php';

echo "🔍 Vérification de la structure des tables\n";
echo "=========================================\n";

try {
    $pdo = Database::getConnection();

    // Structure equipements
    echo "📋 Table equipements:\n";
    $stmt = $pdo->query('DESCRIBE equipements');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }

    // Structure articles
    echo "\n📦 Table articles:\n";
    $stmt = $pdo->query('DESCRIBE articles');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }

    // Structure nomenclatures
    echo "\n📊 Table nomenclatures:\n";
    $stmt = $pdo->query('DESCRIBE nomenclatures');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
