<?php
require_once 'model/Database.php';

echo "🛠️  Suppression de la contrainte d'unicité pour permettre les doublons\n";
echo "====================================================================\n";

try {
    $pdo = Database::getConnection();

    $pdo->exec('ALTER TABLE nomenclatures DROP INDEX uniq_repere_article');
    echo "✅ Contrainte d'unicité supprimée avec succès\n";
    echo "💡 Les doublons sont maintenant possibles dans la table nomenclatures\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
