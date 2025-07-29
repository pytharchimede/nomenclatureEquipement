<?php
// Script d'optimisation de la base de données pour accélérer les requêtes
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    $optimisations = [];

    // Ajouter des index pour accélérer les requêtes les plus fréquentes
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_equipements_code_sap ON equipements(code_sap)",
        "CREATE INDEX IF NOT EXISTS idx_equipements_famille ON equipements(famille)",
        "CREATE INDEX IF NOT EXISTS idx_equipements_source ON equipements(source_actuelle)",
        "CREATE INDEX IF NOT EXISTS idx_articles_code_sap ON articles(code_sap)",
        "CREATE INDEX IF NOT EXISTS idx_articles_code_prefix ON articles(code_article(1))",
        "CREATE INDEX IF NOT EXISTS idx_articles_source ON articles(source_actuelle)",
        "CREATE INDEX IF NOT EXISTS idx_nomenclatures_repere ON nomenclatures(repere_equipement)",
        "CREATE INDEX IF NOT EXISTS idx_nomenclatures_article ON nomenclatures(code_article)",
        "CREATE INDEX IF NOT EXISTS idx_nomenclatures_source ON nomenclatures(source)"
    ];

    foreach ($indexes as $index) {
        try {
            $pdo->exec($index);
            $optimisations[] = "✅ Index créé: " . substr($index, 29, 30) . "...";
        } catch (Exception $e) {
            $optimisations[] = "⚠️ Index existant: " . substr($index, 29, 30) . "...";
        }
    }

    // Optimiser les tables
    $tables = ['equipements', 'articles', 'nomenclatures'];
    foreach ($tables as $table) {
        try {
            $pdo->exec("OPTIMIZE TABLE $table");
            $optimisations[] = "🚀 Table optimisée: $table";
        } catch (Exception $e) {
            $optimisations[] = "⚠️ Erreur optimisation: $table - " . $e->getMessage();
        }
    }

    echo json_encode([
        'success' => true,
        'optimisations' => $optimisations,
        'message' => 'Base de données optimisée pour des requêtes ultra-rapides!'
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
