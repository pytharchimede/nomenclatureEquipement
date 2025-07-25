<?php
// Test simple de la connexion et des requêtes pour le dashboard
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();
    echo "✅ Connexion réussie\n";

    // Test simple de compte
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM equipements");
    $total = $stmt->fetchColumn();
    echo "📊 Total équipements: $total\n";

    // Test avec nomenclatures
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM nomenclatures");
    $total = $stmt->fetchColumn();
    echo "📋 Total nomenclatures: $total\n";

    // Test jointure
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT e.id) as total
        FROM equipements e
        LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement
    ");
    $total = $stmt->fetchColumn();
    echo "🔗 Équipements avec jointure: $total\n";

    echo "✅ Tests réussis\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
