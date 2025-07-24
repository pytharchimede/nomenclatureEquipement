<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'rgm_synthese'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Table rgm_synthese non trouvée');
    }

    // Statistiques générales
    $totalElements = $pdo->query("SELECT COUNT(*) as total FROM rgm_synthese")->fetch()['total'];

    $totalDesignations = $pdo->query("SELECT COUNT(DISTINCT designation_article) as total FROM rgm_synthese WHERE designation_article IS NOT NULL AND designation_article != ''")->fetch()['total'];

    $totalReperes = $pdo->query("SELECT COUNT(DISTINCT repere_equipement) as total FROM rgm_synthese WHERE repere_equipement IS NOT NULL AND repere_equipement != ''")->fetch()['total'];

    $totalUnites = $pdo->query("SELECT COUNT(DISTINCT unite) as total FROM rgm_synthese WHERE unite IS NOT NULL AND unite != ''")->fetch()['total'];

    // Top désignations
    $topDesignations = $pdo->query("
        SELECT designation_article, COUNT(*) as count 
        FROM rgm_synthese 
        WHERE designation_article IS NOT NULL AND designation_article != ''
        GROUP BY designation_article 
        ORDER BY count DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Top unités
    $topUnites = $pdo->query("
        SELECT unite, COUNT(*) as count 
        FROM rgm_synthese 
        WHERE unite IS NOT NULL AND unite != ''
        GROUP BY unite 
        ORDER BY count DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Répartition par source
    $sources = $pdo->query("
        SELECT source, COUNT(*) as count 
        FROM rgm_synthese 
        GROUP BY source 
        ORDER BY count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Import récents (7 derniers jours)
    $recentImports = $pdo->query("
        SELECT DATE(date_import) as date, COUNT(*) as count 
        FROM rgm_synthese 
        WHERE date_import >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(date_import) 
        ORDER BY date DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_elements' => (int)$totalElements,
            'total_designations' => (int)$totalDesignations,
            'total_reperes' => (int)$totalReperes,
            'total_unites' => (int)$totalUnites,
            'top_designations' => $topDesignations,
            'top_unites' => $topUnites,
            'sources' => $sources,
            'recent_imports' => $recentImports
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
