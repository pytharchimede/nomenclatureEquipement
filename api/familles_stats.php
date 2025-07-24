<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Statistiques générales des familles
    $stats = [];

    // Total de familles définies dans la table familles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM familles");
    $stats['total_familles_definies'] = $stmt->fetchColumn();

    // Total de familles utilisées dans quantitatif
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT famille) as total 
        FROM quantitatif 
        WHERE famille IS NOT NULL AND famille != ''
    ");
    $stats['total_familles_utilisees'] = $stmt->fetchColumn();

    // Total d'éléments quantitatifs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quantitatif");
    $stats['total_elements'] = $stmt->fetchColumn();

    // Détails par famille avec statistiques complètes
    $stmt = $pdo->query("
        SELECT 
            f.id,
            f.nom as famille_nom,
            COALESCE(q.count_elements, 0) as nb_elements,
            COALESCE(q.count_reperes, 0) as nb_reperes_uniques,
            COALESCE(q.count_unites, 0) as nb_unites_differentes,
            COALESCE(q.total_quantite, 0) as total_quantite,
            COALESCE(q.avg_quantite, 0) as moyenne_quantite,
            COALESCE(q.max_quantite, 0) as max_quantite,
            CASE 
                WHEN q.count_elements > 0 THEN 'active'
                ELSE 'inactive'
            END as status
        FROM familles f
        LEFT JOIN (
            SELECT 
                famille,
                COUNT(*) as count_elements,
                COUNT(DISTINCT repere) as count_reperes,
                COUNT(DISTINCT unite) as count_unites,
                SUM(CASE WHEN quantite REGEXP '^[0-9]+$' THEN CAST(quantite AS UNSIGNED) ELSE 0 END) as total_quantite,
                AVG(CASE WHEN quantite REGEXP '^[0-9]+$' THEN CAST(quantite AS UNSIGNED) ELSE NULL END) as avg_quantite,
                MAX(CASE WHEN quantite REGEXP '^[0-9]+$' THEN CAST(quantite AS UNSIGNED) ELSE 0 END) as max_quantite
            FROM quantitatif 
            WHERE famille IS NOT NULL AND famille != ''
            GROUP BY famille
        ) q ON f.nom = q.famille
        ORDER BY q.count_elements DESC, f.nom ASC
    ");
    $stats['familles_details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top 5 familles les plus utilisées
    $stmt = $pdo->query("
        SELECT 
            famille,
            COUNT(*) as count_elements,
            COUNT(DISTINCT repere) as count_reperes,
            SUM(CASE WHEN quantite REGEXP '^[0-9]+$' THEN CAST(quantite AS UNSIGNED) ELSE 0 END) as total_quantite
        FROM quantitatif 
        WHERE famille IS NOT NULL AND famille != ''
        GROUP BY famille 
        ORDER BY count_elements DESC 
        LIMIT 5
    ");
    $stats['top_familles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Répartition des unités par famille (pour les graphiques)
    $stmt = $pdo->query("
        SELECT 
            famille,
            unite,
            COUNT(*) as count
        FROM quantitatif 
        WHERE famille IS NOT NULL AND famille != '' 
        AND unite IS NOT NULL AND unite != ''
        GROUP BY famille, unite
        ORDER BY famille, count DESC
    ");
    $stats['unites_par_famille'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques d'évolution (derniers imports par famille)
    $stmt = $pdo->query("
        SELECT 
            famille,
            DATE(date_import) as date_import,
            COUNT(*) as elements_importes
        FROM quantitatif 
        WHERE famille IS NOT NULL AND famille != ''
        AND date_import >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY famille, DATE(date_import)
        ORDER BY date_import DESC, elements_importes DESC
        LIMIT 20
    ");
    $stats['evolution_recente'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Familles non utilisées
    $stmt = $pdo->query("
        SELECT f.nom as famille_nom
        FROM familles f
        LEFT JOIN quantitatif q ON f.nom = q.famille
        WHERE q.famille IS NULL
        ORDER BY f.nom
    ");
    $stats['familles_non_utilisees'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Performance et efficacité
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_familles_avec_donnees,
            COUNT(CASE WHEN total_q > 100 THEN 1 END) as familles_volumineuses,
            COUNT(CASE WHEN total_q BETWEEN 10 AND 100 THEN 1 END) as familles_moyennes,
            COUNT(CASE WHEN total_q < 10 THEN 1 END) as familles_petites
        FROM (
            SELECT 
                famille,
                COUNT(*) as total_q
            FROM quantitatif 
            WHERE famille IS NOT NULL AND famille != ''
            GROUP BY famille
        ) f
    ");
    $stats['repartition_tailles'] = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
