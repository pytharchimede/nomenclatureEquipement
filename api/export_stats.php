<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getConnection();

    // Statistiques des équipements
    $equipements = $db->query("SELECT COUNT(*) as count FROM equipements")->fetch()['count'];

    // Statistiques des articles
    $articles = $db->query("SELECT COUNT(*) as count FROM articles")->fetch()['count'];

    // Statistiques des nomenclatures
    $nomenclatures = $db->query("SELECT COUNT(*) as count FROM nomenclatures")->fetch()['count'];

    // Statistiques des familles (approximation basée sur les groupes d'équipements)
    $familles = $db->query("SELECT COUNT(DISTINCT famille) as count FROM equipements WHERE famille IS NOT NULL")->fetch()['count'];

    // Statistiques des doublons
    $doublons = $db->query("SELECT COUNT(*) as count FROM nomenclatures_doublons_import WHERE statut = 'en_attente'")->fetch()['count'];

    // Exports d'aujourd'hui (simulation - vous pourriez avoir une table de logs)
    $today = date('Y-m-d');
    $exports_today = 0; // À implémenter avec une vraie table de logs si nécessaire

    echo json_encode([
        'success' => true,
        'stats' => [
            'equipements' => (int)$equipements,
            'articles' => (int)$articles,
            'nomenclatures' => (int)$nomenclatures,
            'familles' => (int)$familles,
            'doublons' => (int)$doublons,
            'exports_today' => (int)$exports_today
        ]
    ]);
} catch (Exception $e) {
    error_log("Erreur dans export_stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des statistiques'
    ]);
}
