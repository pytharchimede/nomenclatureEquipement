<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Obtenir les statistiques générales
    $totalTemplates = $pdo->query("SELECT COUNT(*) FROM template_spl")->fetchColumn();
    $totalArticles = $pdo->query("SELECT COUNT(DISTINCT code_article) FROM template_spl")->fetchColumn();
    $totalMetiers = $pdo->query("SELECT COUNT(DISTINCT metier) FROM template_spl WHERE metier IS NOT NULL AND metier != ''")->fetchColumn();
    $totalFabricants = $pdo->query("SELECT COUNT(DISTINCT fabricant) FROM template_spl WHERE fabricant IS NOT NULL AND fabricant != ''")->fetchColumn();

    // Derniers imports (5 derniers)
    $lastImports = $pdo->query("
        SELECT DATE_FORMAT(date_import, '%d/%m/%Y %H:%i') as date_formatted, 
               COUNT(*) as count, 
               GROUP_CONCAT(DISTINCT metier) as metiers
        FROM template_spl 
        WHERE date_import >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(date_import), HOUR(date_import)
        ORDER BY date_import DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques en temps réel pour le dashboard
    $realtimeStats = [
        'total_templates' => $totalTemplates,
        'total_articles' => $totalArticles,
        'total_metiers' => $totalMetiers,
        'total_fabricants' => $totalFabricants,
        'last_imports' => $lastImports,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode([
        'success' => true,
        'data' => $realtimeStats
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
