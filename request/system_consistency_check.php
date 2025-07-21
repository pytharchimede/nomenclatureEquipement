<?php

/**
 * Script de vérification de la cohérence du système
 * Vérifie que le repère est bien utilisé comme clé primaire partout
 */

require_once 'model/Database.php';
require_once 'model/Equipement.php';
require_once 'model/Nomenclature.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();
    $report = [
        'timestamp' => date('Y-m-d H:i:s'),
        'database_structure' => [],
        'data_consistency' => [],
        'performance_stats' => [],
        'recommendations' => []
    ];

    // 1. Vérification de la structure de base de données
    $tables = ['equipements', 'nomenclatures'];

    foreach ($tables as $table) {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $report['database_structure'][$table] = [
            'columns' => $columns,
            'has_repere_equipement' => false,
            'has_code_equipement' => false,
            'primary_key' => null
        ];

        foreach ($columns as $col) {
            if ($col['Field'] == 'repere_equipement') {
                $report['database_structure'][$table]['has_repere_equipement'] = true;
            }
            if ($col['Field'] == 'code_equipement') {
                $report['database_structure'][$table]['has_code_equipement'] = true;
            }
            if ($col['Key'] == 'PRI') {
                $report['database_structure'][$table]['primary_key'] = $col['Field'];
            }
        }
    }

    // 2. Vérification de la cohérence des données

    // Vérifier les équipements sans repère
    $stmt = $pdo->query("SELECT COUNT(*) FROM equipements WHERE repere_equipement IS NULL OR repere_equipement = ''");
    $equipements_sans_repere = $stmt->fetchColumn();

    // Vérifier les nomenclatures orphelines
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM nomenclatures n 
        LEFT JOIN equipements e ON n.repere_equipement = e.repere_equipement 
        WHERE e.repere_equipement IS NULL AND n.repere_equipement IS NOT NULL AND n.repere_equipement != ''
    ");
    $nomenclatures_orphelines = $stmt->fetchColumn();

    // Vérifier les doublons de repères
    $stmt = $pdo->query("
        SELECT repere_equipement, COUNT(*) as nb 
        FROM equipements 
        WHERE repere_equipement IS NOT NULL AND repere_equipement != ''
        GROUP BY repere_equipement 
        HAVING COUNT(*) > 1
    ");
    $doublons_reperes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier les codes équipement orphelins
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM nomenclatures n 
        LEFT JOIN equipements e ON n.code_equipement = e.code_equipement 
        WHERE e.code_equipement IS NULL AND n.code_equipement IS NOT NULL AND n.code_equipement != ''
    ");
    $codes_orphelins = $stmt->fetchColumn();

    $report['data_consistency'] = [
        'equipements_sans_repere' => $equipements_sans_repere,
        'nomenclatures_orphelines_repere' => $nomenclatures_orphelines,
        'nomenclatures_orphelines_code' => $codes_orphelins,
        'doublons_reperes' => count($doublons_reperes),
        'detail_doublons' => $doublons_reperes
    ];

    // 3. Statistiques de performance
    $stmt = $pdo->query("SELECT COUNT(*) FROM equipements");
    $total_equipements = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM nomenclatures");
    $total_nomenclatures = $stmt->fetchColumn();

    // Test de performance pour pagination
    $start_time = microtime(true);
    $stmt = $pdo->query("SELECT * FROM equipements ORDER BY repere_equipement LIMIT 50");
    $pagination_time = microtime(true) - $start_time;

    // Test de performance pour recherche par repère
    $start_time = microtime(true);
    $stmt = $pdo->prepare("SELECT * FROM equipements WHERE repere_equipement = ? LIMIT 1");
    $stmt->execute(['TEST']);
    $search_time = microtime(true) - $start_time;

    $report['performance_stats'] = [
        'total_equipements' => $total_equipements,
        'total_nomenclatures' => $total_nomenclatures,
        'pagination_time_ms' => round($pagination_time * 1000, 2),
        'search_time_ms' => round($search_time * 1000, 2)
    ];

    // 4. Recommandations
    $recommendations = [];

    if ($equipements_sans_repere > 0) {
        $recommendations[] = "⚠️ $equipements_sans_repere équipements sans repère - Corriger en priorité";
    }

    if ($nomenclatures_orphelines > 0) {
        $recommendations[] = "⚠️ $nomenclatures_orphelines nomenclatures orphelines par repère - Vérifier les références";
    }

    if (count($doublons_reperes) > 0) {
        $recommendations[] = "🚨 " . count($doublons_reperes) . " doublons de repères détectés - Nettoyer immédiatement";
    }

    if ($pagination_time > 0.1) {
        $recommendations[] = "⏱️ Pagination lente ($pagination_time s) - Ajouter un index sur repere_equipement";
    }

    if ($total_equipements > 20000) {
        $recommendations[] = "📊 Gros volume ($total_equipements équipements) - S'assurer que la pagination est activée";
    }

    if (empty($recommendations)) {
        $recommendations[] = "✅ Système cohérent - Repère utilisé comme clé primaire métier";
    }

    $report['recommendations'] = $recommendations;

    // Score de cohérence global
    $score = 100;
    if ($equipements_sans_repere > 0) $score -= 20;
    if ($nomenclatures_orphelines > 0) $score -= 15;
    if (count($doublons_reperes) > 0) $score -= 30;
    if ($codes_orphelins > 0) $score -= 10;

    $report['consistency_score'] = max(0, $score);
    $report['status'] = $score >= 90 ? 'excellent' : ($score >= 70 ? 'good' : ($score >= 50 ? 'warning' : 'critical'));

    echo json_encode($report, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Erreur lors de la vérification : ' . $e->getMessage()
    ]);
}
