<?php
require_once 'model/Database.php';

echo "🔍 Vérification des contraintes et index\n";
echo "=======================================\n";

try {
    $pdo = Database::getConnection();

    // Voir les index de la table nomenclatures
    echo "📊 Index de la table nomenclatures:\n";
    $stmt = $pdo->query('SHOW INDEX FROM nomenclatures');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Key_name']}: {$row['Column_name']} (Unique: " . ($row['Non_unique'] ? 'Non' : 'Oui') . ")\n";
    }

    echo "\n🛠️  Pour permettre les doublons, il faut supprimer la contrainte d'unicité:\n";
    echo "ALTER TABLE nomenclatures DROP INDEX uniq_repere_article;\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
