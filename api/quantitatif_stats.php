<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Statistiques générales
    $stats = [];

    // Total d'éléments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quantitatif");
    $stats['total_elements'] = $stmt->fetchColumn();

    // Total de familles distinctes
    $stmt = $pdo->query("SELECT COUNT(DISTINCT famille) as total FROM quantitatif WHERE famille IS NOT NULL AND famille != ''");
    $stats['total_familles'] = $stmt->fetchColumn();

    // Total de repères uniques
    $stmt = $pdo->query("SELECT COUNT(DISTINCT repere) as total FROM quantitatif WHERE repere IS NOT NULL AND repere != ''");
    $stats['total_reperes'] = $stmt->fetchColumn();

    // Total d'unités différentes
    $stmt = $pdo->query("SELECT COUNT(DISTINCT unite) as total FROM quantitatif WHERE unite IS NOT NULL AND unite != ''");
    $stats['total_unites'] = $stmt->fetchColumn();

    // Répartition par famille
    $stmt = $pdo->query("
        SELECT famille, COUNT(*) as count 
        FROM quantitatif 
        WHERE famille IS NOT NULL AND famille != '' 
        GROUP BY famille 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $stats['top_familles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques de quantités
    $stmt = $pdo->query("
        SELECT 
            SUM(CAST(quantite AS UNSIGNED)) as total_quantite,
            AVG(CAST(quantite AS UNSIGNED)) as moyenne_quantite,
            MAX(CAST(quantite AS UNSIGNED)) as max_quantite
        FROM quantitatif 
        WHERE quantite IS NOT NULL AND quantite != '' AND quantite REGEXP '^[0-9]+$'
    ");
    $quantite_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['quantites'] = $quantite_stats;

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors du chargement des statistiques: ' . $e->getMessage()
    ]);
}
