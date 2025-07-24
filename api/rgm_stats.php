<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Statistiques générales pour les données RGM dans la table nomenclatures
    $totalElements = $pdo->query("SELECT COUNT(*) as total FROM nomenclatures WHERE source = 'RGM'")->fetch()['total'];

    $totalDesignations = $pdo->query("SELECT COUNT(DISTINCT designation_article) as total FROM nomenclatures WHERE source = 'RGM' AND designation_article IS NOT NULL AND designation_article != ''")->fetch()['total'];

    $totalReperes = $pdo->query("SELECT COUNT(DISTINCT repere_equipement) as total FROM nomenclatures WHERE source = 'RGM' AND repere_equipement IS NOT NULL AND repere_equipement != ''")->fetch()['total'];

    $totalUnites = $pdo->query("SELECT COUNT(DISTINCT unite) as total FROM nomenclatures WHERE source = 'RGM' AND unite IS NOT NULL AND unite != ''")->fetch()['total'];

    // Top désignations pour les données RGM
    $topDesignations = $pdo->query("
        SELECT designation_article, COUNT(*) as count 
        FROM nomenclatures 
        WHERE source = 'RGM' AND designation_article IS NOT NULL AND designation_article != ''
        GROUP BY designation_article 
        ORDER BY count DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Top unités pour les données RGM
    $topUnites = $pdo->query("
        SELECT unite, COUNT(*) as count 
        FROM nomenclatures 
        WHERE source = 'RGM' AND unite IS NOT NULL AND unite != ''
        GROUP BY unite 
        ORDER BY count DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Répartition par source (toutes sources confondues pour comparaison)
    $sources = $pdo->query("
        SELECT source, COUNT(*) as count 
        FROM nomenclatures 
        GROUP BY source 
        ORDER BY count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Import récents RGM (7 derniers jours)
    $recentImports = $pdo->query("
        SELECT DATE(date_creation) as date, COUNT(*) as count 
        FROM nomenclatures 
        WHERE source = 'RGM' AND date_creation >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(date_creation) 
        ORDER BY date DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Calcul du statut de synchronisation (tous les RGM sont déjà "synchronisés" dans nomenclatures)
    $syncedCount = $totalElements; // Toutes les données RGM sont dans nomenclatures

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_count' => (int)$totalElements,
            'unique_designations' => (int)$totalDesignations,
            'unique_reperes' => (int)$totalReperes,
            'unique_unites' => (int)$totalUnites,
            'synced_count' => (int)$syncedCount,
            'top_designation' => $topDesignations[0] ?? null,
            'top_unite' => $topUnites[0] ?? null,
            'unites' => $topUnites,
            'designations' => $topDesignations,
            'sources' => $sources,
            'recent_imports' => $recentImports
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
