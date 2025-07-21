<?php

/**
 * Script d'optimisation de la base de données pour les gros volumes
 * Ajoute les index nécessaires et optimise les performances
 */

require_once 'model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();
    $optimizations = [];
    $errors = [];

    // 1. Ajouter un index sur repere_equipement dans la table equipements
    try {
        $stmt = $pdo->query("SHOW INDEX FROM equipements WHERE Key_name = 'idx_repere_equipement'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE equipements ADD INDEX idx_repere_equipement (repere_equipement)");
            $optimizations[] = "✅ Index ajouté sur equipements.repere_equipement";
        } else {
            $optimizations[] = "ℹ️ Index sur equipements.repere_equipement déjà présent";
        }
    } catch (Exception $e) {
        $errors[] = "❌ Erreur ajout index equipements.repere_equipement: " . $e->getMessage();
    }

    // 2. Ajouter un index sur repere_equipement dans la table nomenclatures
    try {
        $stmt = $pdo->query("SHOW INDEX FROM nomenclatures WHERE Key_name = 'idx_nom_repere_equipement'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE nomenclatures ADD INDEX idx_nom_repere_equipement (repere_equipement)");
            $optimizations[] = "✅ Index ajouté sur nomenclatures.repere_equipement";
        } else {
            $optimizations[] = "ℹ️ Index sur nomenclatures.repere_equipement déjà présent";
        }
    } catch (Exception $e) {
        $errors[] = "❌ Erreur ajout index nomenclatures.repere_equipement: " . $e->getMessage();
    }

    // 3. Ajouter un index composé pour les filtres fréquents
    try {
        $stmt = $pdo->query("SHOW INDEX FROM equipements WHERE Key_name = 'idx_fabricant_type'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE equipements ADD INDEX idx_fabricant_type (fabricant, type_objet)");
            $optimizations[] = "✅ Index composé ajouté sur equipements(fabricant, type_objet)";
        } else {
            $optimizations[] = "ℹ️ Index composé sur equipements(fabricant, type_objet) déjà présent";
        }
    } catch (Exception $e) {
        $errors[] = "❌ Erreur ajout index composé: " . $e->getMessage();
    }

    // 4. Ajouter un index sur code_article dans nomenclatures pour les jointures
    try {
        $stmt = $pdo->query("SHOW INDEX FROM nomenclatures WHERE Key_name = 'idx_code_article'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE nomenclatures ADD INDEX idx_code_article (code_article)");
            $optimizations[] = "✅ Index ajouté sur nomenclatures.code_article";
        } else {
            $optimizations[] = "ℹ️ Index sur nomenclatures.code_article déjà présent";
        }
    } catch (Exception $e) {
        $errors[] = "❌ Erreur ajout index nomenclatures.code_article: " . $e->getMessage();
    }

    // 5. Ajouter un index sur date_creation pour les statistiques
    try {
        $stmt = $pdo->query("SHOW INDEX FROM equipements WHERE Key_name = 'idx_date_creation'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE equipements ADD INDEX idx_date_creation (date_creation)");
            $optimizations[] = "✅ Index ajouté sur equipements.date_creation";
        } else {
            $optimizations[] = "ℹ️ Index sur equipements.date_creation déjà présent";
        }
    } catch (Exception $e) {
        $errors[] = "❌ Erreur ajout index equipements.date_creation: " . $e->getMessage();
    }

    // 6. Optimiser les tables
    try {
        $pdo->exec("OPTIMIZE TABLE equipements");
        $optimizations[] = "✅ Table equipements optimisée";
    } catch (Exception $e) {
        $errors[] = "❌ Erreur optimisation table equipements: " . $e->getMessage();
    }

    try {
        $pdo->exec("OPTIMIZE TABLE nomenclatures");
        $optimizations[] = "✅ Table nomenclatures optimisée";
    } catch (Exception $e) {
        $errors[] = "❌ Erreur optimisation table nomenclatures: " . $e->getMessage();
    }

    // 7. Vérifier les statistiques après optimisation
    $stats = [];

    $stmt = $pdo->query("SELECT COUNT(*) FROM equipements");
    $stats['total_equipements'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM nomenclatures");
    $stats['total_nomenclatures'] = $stmt->fetchColumn();

    // Test de performance pagination
    $start = microtime(true);
    $stmt = $pdo->query("SELECT * FROM equipements ORDER BY repere_equipement LIMIT 50");
    $stats['pagination_time_ms'] = round((microtime(true) - $start) * 1000, 2);

    // Test de performance recherche
    $start = microtime(true);
    $stmt = $pdo->prepare("SELECT * FROM equipements WHERE repere_equipement = ?");
    $stmt->execute(['TEST123']);
    $stats['search_time_ms'] = round((microtime(true) - $start) * 1000, 2);

    echo json_encode([
        'success' => true,
        'message' => 'Optimisation terminée',
        'optimizations' => $optimizations,
        'errors' => $errors,
        'statistics' => $stats,
        'recommendations' => [
            $stats['pagination_time_ms'] < 50 ? "✅ Pagination optimale (< 50ms)" : "⚠️ Pagination lente (> 50ms)",
            $stats['search_time_ms'] < 10 ? "✅ Recherche optimale (< 10ms)" : "⚠️ Recherche lente (> 10ms)",
            $stats['total_equipements'] > 20000 ? "📊 Gros volume détecté - Pagination requise" : "ℹ️ Volume modéré"
        ]
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'optimisation : ' . $e->getMessage()
    ]);
}
