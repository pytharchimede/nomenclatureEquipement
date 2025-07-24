<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Statistiques par métier
    $matiersQuery = "SELECT metier, COUNT(*) as total, COUNT(DISTINCT code_article) as articles_uniques 
                     FROM template_spl 
                     WHERE metier IS NOT NULL AND metier != '' 
                     GROUP BY metier 
                     ORDER BY total DESC";
    $matiersStmt = $pdo->query($matiersQuery);
    $metiers = $matiersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Répartition par unité de base
    $unitesQuery = "SELECT unite_base, COUNT(*) as total 
                    FROM template_spl 
                    WHERE unite_base IS NOT NULL AND unite_base != '' 
                    GROUP BY unite_base 
                    ORDER BY total DESC 
                    LIMIT 10";
    $unitesStmt = $pdo->query($unitesQuery);
    $unites = $unitesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Top 10 des articles les plus utilisés
    $articlesQuery = "SELECT code_article, designation_article, COUNT(*) as occurrences,
                             GROUP_CONCAT(DISTINCT metier) as metiers
                      FROM template_spl 
                      WHERE code_article IS NOT NULL AND code_article != '' 
                      GROUP BY code_article, designation_article 
                      ORDER BY occurrences DESC 
                      LIMIT 10";
    $articlesStmt = $pdo->query($articlesQuery);
    $articles = $articlesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Évolution des imports par mois (6 derniers mois)
    $evolutionQuery = "SELECT DATE_FORMAT(date_import, '%Y-%m') as mois,
                              COUNT(*) as total_imports,
                              COUNT(DISTINCT code_article) as nouveaux_articles
                       FROM template_spl 
                       WHERE date_import >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                       GROUP BY DATE_FORMAT(date_import, '%Y-%m')
                       ORDER BY mois ASC";
    $evolutionStmt = $pdo->query($evolutionQuery);
    $evolution = $evolutionStmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques par fabricant
    $fabricantsQuery = "SELECT fabricant, COUNT(*) as total,
                               COUNT(DISTINCT code_article) as articles_uniques
                        FROM template_spl 
                        WHERE fabricant IS NOT NULL AND fabricant != '' 
                        GROUP BY fabricant 
                        ORDER BY total DESC 
                        LIMIT 10";
    $fabricantsStmt = $pdo->query($fabricantsQuery);
    $fabricants = $fabricantsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques globales
    $globalStats = [
        'total_lignes' => $pdo->query("SELECT COUNT(*) FROM template_spl")->fetchColumn(),
        'total_articles_uniques' => $pdo->query("SELECT COUNT(DISTINCT code_article) FROM template_spl")->fetchColumn(),
        'total_metiers' => $pdo->query("SELECT COUNT(DISTINCT metier) FROM template_spl WHERE metier IS NOT NULL AND metier != ''")->fetchColumn(),
        'total_fabricants' => $pdo->query("SELECT COUNT(DISTINCT fabricant) FROM template_spl WHERE fabricant IS NOT NULL AND fabricant != ''")->fetchColumn(),
        'dernier_import' => $pdo->query("SELECT MAX(date_import) FROM template_spl")->fetchColumn()
    ];

    $response = [
        'success' => true,
        'stats' => $globalStats,
        'metiers' => $metiers,
        'unites' => $unites,
        'articles_populaires' => $articles,
        'evolution_imports' => $evolution,
        'fabricants' => $fabricants
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
    ]);
}
