<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Statistiques de performance du système
    $performanceStats = [
        'memory_usage' => [
            'current' => round(memory_get_usage() / 1024 / 1024, 2), // MB
            'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2), // MB
            'limit' => ini_get('memory_limit')
        ],
        'execution_time' => [
            'max' => ini_get('max_execution_time'),
            'current' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3)
        ],
        'disk_space' => [
            'free' => round(disk_free_space('.') / 1024 / 1024 / 1024, 2), // GB
            'total' => round(disk_total_space('.') / 1024 / 1024 / 1024, 2) // GB
        ]
    ];

    // Statistiques de la base de données
    $dbStats = [
        'table_size' => $pdo->query("
            SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'DB Size in MB' 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() AND table_name = 'template_spl'
        ")->fetchColumn(),
        'total_rows' => $pdo->query("SELECT COUNT(*) FROM template_spl")->fetchColumn(),
        'last_import_date' => $pdo->query("SELECT MAX(date_import) FROM template_spl")->fetchColumn()
    ];

    // Activité récente (dernière heure)
    $recentActivity = $pdo->query("
        SELECT COUNT(*) as recent_imports
        FROM template_spl 
        WHERE date_import >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ")->fetchColumn();

    // Health check
    $healthStatus = 'healthy';
    $healthIssues = [];

    if ($performanceStats['memory_usage']['peak'] > 400) {
        $healthIssues[] = 'Utilisation mémoire élevée';
        $healthStatus = 'warning';
    }

    if ($performanceStats['disk_space']['free'] < 1) {
        $healthIssues[] = 'Espace disque faible';
        $healthStatus = 'critical';
    }

    if ($dbStats['table_size'] > 500) {
        $healthIssues[] = 'Taille de base de données importante';
        if ($healthStatus === 'healthy') $healthStatus = 'warning';
    }

    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'performance' => $performanceStats,
        'database' => $dbStats,
        'recent_activity' => $recentActivity,
        'health' => [
            'status' => $healthStatus,
            'issues' => $healthIssues
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des informations système: ' . $e->getMessage()
    ]);
}
