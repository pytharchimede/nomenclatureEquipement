<?php
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Statistiques globales
    $stats = [
        'total' => 0,
        'en_attente' => 0,
        'valide' => 0,
        'rejete' => 0,
        'importe' => 0
    ];

    // Requête pour compter par statut
    $query = "
        SELECT statut, COUNT(*) as count 
        FROM nomenclatures_doublons_import 
        GROUP BY statut
    ";

    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $stats[$result['statut']] = (int)$result['count'];
        $stats['total'] += (int)$result['count'];
    }

    // Statistiques supplémentaires
    $extraStats = [];

    // Répartition par type de doublon
    $query = "
        SELECT raison_rejet, COUNT(*) as count 
        FROM nomenclatures_doublons_import 
        GROUP BY raison_rejet
    ";
    $stmt = $pdo->query($query);
    $extraStats['par_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Répartition par fichier d'import
    $query = "
        SELECT fichier_import, COUNT(*) as count 
        FROM nomenclatures_doublons_import 
        GROUP BY fichier_import 
        ORDER BY count DESC 
        LIMIT 5
    ";
    $stmt = $pdo->query($query);
    $extraStats['par_fichier'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Doublons récents (dernières 24h)
    $query = "
        SELECT COUNT(*) as count 
        FROM nomenclatures_doublons_import 
        WHERE date_import >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ";
    $stmt = $pdo->query($query);
    $extraStats['recents_24h'] = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'extra_stats' => $extraStats
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des statistiques: ' . $e->getMessage()
    ]);
}
