<?php
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // 1. Analyse complète des familles d'équipements avec leurs articles
    $stmt = $pdo->query("
        SELECT 
            COALESCE(e.famille, 'Non définie') as famille,
            COUNT(DISTINCT e.id) as nb_equipements,
            COUNT(DISTINCT n.code_article) as nb_articles_differents,
            COUNT(DISTINCT n.id) as nb_pieces_totales,
            COUNT(DISTINCT CASE WHEN n.source = 'SAP' THEN n.id END) as pieces_sap,
            COUNT(DISTINCT CASE WHEN n.source = 'RGM' THEN n.id END) as pieces_rgm,
            ROUND(AVG(CASE WHEN n.id IS NOT NULL THEN 
                (SELECT COUNT(*) FROM nomenclatures n2 WHERE n2.repere_equipement = e.repere_equipement)
                ELSE 0 END), 1) as pieces_moyenne_par_equipement
        FROM equipements e
        LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement
        WHERE e.famille IS NOT NULL AND e.famille != ''
        GROUP BY e.famille
        HAVING nb_equipements > 0
        ORDER BY nb_pieces_totales DESC
        LIMIT 10
    ");
    $famillesComplete = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Répartition par source de données (SAP vs RGM) - plus détaillée
    $stmt = $pdo->query("
        SELECT 
            COALESCE(n.source, 'Non définie') as source,
            COUNT(DISTINCT n.repere_equipement) as equipements,
            COUNT(DISTINCT n.code_article) as articles,
            COUNT(*) as nomenclatures_total,
            ROUND(AVG(CASE 
                WHEN e.famille IS NOT NULL THEN 1 ELSE 0 
            END) * 100, 1) as pourcent_avec_famille
        FROM nomenclatures n
        LEFT JOIN equipements e ON n.repere_equipement = e.repere_equipement
        GROUP BY n.source
        ORDER BY nomenclatures_total DESC
    ");
    $sourcesComplete = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Top des articles les plus utilisés
    $stmt = $pdo->query("
        SELECT 
            a.code_article,
            a.designation,
            COUNT(DISTINCT n.repere_equipement) as nb_equipements_utilises,
            COUNT(*) as nb_utilisations,
            n.source,
            CASE 
                WHEN a.code_article LIKE '1%' THEN 'Mécanique'
                WHEN a.code_article LIKE '2%' THEN 'Électrique'
                WHEN a.code_article LIKE '3%' THEN 'Instrumentation'
                WHEN a.code_article LIKE '4%' THEN 'Tuyauterie'
                WHEN a.code_article LIKE '5%' THEN 'Chaudronnerie'
                WHEN a.code_article LIKE '6%' THEN 'Civil/Structure'
                WHEN a.code_article LIKE '7%' THEN 'Chimie/Process'
                WHEN a.code_article LIKE '8%' THEN 'Sécurité'
                WHEN a.code_article LIKE '9%' THEN 'Maintenance'
                ELSE 'Autres'
            END as metier
        FROM articles a
        INNER JOIN nomenclatures n ON a.code_article = n.code_article
        GROUP BY a.code_article, a.designation, n.source
        ORDER BY nb_equipements_utilises DESC
        LIMIT 15
    ");
    $articlesTop = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Types d'équipements avec répartition détaillée
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN e.repere_equipement LIKE 'K%' THEN 'Colonnes'
                WHEN e.repere_equipement LIKE 'E%' THEN 'Échangeurs'
                WHEN e.repere_equipement LIKE 'D%' THEN 'Ballons'
                WHEN e.repere_equipement LIKE 'T%' THEN 'Réservoirs'
                WHEN e.repere_equipement LIKE 'F%' THEN 'Fours'
                WHEN e.repere_equipement LIKE 'R%' THEN 'Réacteurs'
                WHEN e.repere_equipement LIKE 'C%' THEN 'Compresseurs'
                WHEN e.repere_equipement LIKE 'P%' THEN 'Pompes'
                WHEN e.repere_equipement LIKE 'MP%' THEN 'Moteurs'
                WHEN e.repere_equipement LIKE 'SV%' THEN 'Soupapes'
                WHEN e.repere_equipement LIKE '__V%' THEN 'Vannes'
                ELSE 'Autres'
            END as type_equipement,
            COUNT(*) as nombre,
            COUNT(DISTINCT n.code_article) as articles_differents,
            COUNT(n.id) as pieces_totales,
            ROUND(AVG(CASE WHEN n.id IS NOT NULL THEN 
                (SELECT COUNT(*) FROM nomenclatures n2 WHERE n2.repere_equipement = e.repere_equipement)
                ELSE 0 END), 1) as pieces_moyenne
        FROM equipements e
        LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement
        GROUP BY type_equipement
        HAVING nombre > 5
        ORDER BY nombre DESC
    ");
    $typesComplete = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Évolution des données (simulation car pas de dates réelles)
    $evolutionData = [];
    $months = ['2024-07', '2024-08', '2024-09', '2024-10', '2024-11', '2024-12', '2025-01', '2025-02', '2025-03', '2025-04', '2025-05', '2025-06'];
    
    foreach($months as $month) {
        // Simulation basée sur les IDs des équipements pour créer une évolution réaliste
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM equipements 
            WHERE id <= (SELECT MAX(id) FROM equipements) * ? / 12
        ");
        $monthIndex = array_search($month, $months) + 1;
        $stmt->execute([$monthIndex]);
        $count = $stmt->fetchColumn();
        
        $evolutionData[] = [
            'mois' => $month,
            'equipements_cumules' => $count,
            'equipements_ajoutes' => $monthIndex === 1 ? $count : ($count - ($monthIndex > 1 ? 
                (function($idx) use ($pdo) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipements WHERE id <= (SELECT MAX(id) FROM equipements) * ? / 12");
                    $stmt->execute([$idx - 1]);
                    return $stmt->fetchColumn();
                })($monthIndex) : 0))
        ];
    }

    // 6. Métiers avec détails sur les équipements
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN a.code_article LIKE '1%' THEN 'Mécanique'
                WHEN a.code_article LIKE '2%' THEN 'Électrique'
                WHEN a.code_article LIKE '3%' THEN 'Instrumentation'
                WHEN a.code_article LIKE '4%' THEN 'Tuyauterie'
                WHEN a.code_article LIKE '5%' THEN 'Chaudronnerie'
                WHEN a.code_article LIKE '6%' THEN 'Civil/Structure'
                WHEN a.code_article LIKE '7%' THEN 'Chimie/Process'
                WHEN a.code_article LIKE '8%' THEN 'Sécurité'
                WHEN a.code_article LIKE '9%' THEN 'Maintenance'
                ELSE 'Autres'
            END as metier,
            COUNT(DISTINCT a.id) as nb_articles,
            COUNT(DISTINCT n.repere_equipement) as nb_equipements_concernes,
            COUNT(DISTINCT CASE WHEN n.source = 'SAP' THEN a.id END) as articles_sap,
            COUNT(DISTINCT CASE WHEN n.source = 'RGM' THEN a.id END) as articles_rgm
        FROM articles a
        LEFT JOIN nomenclatures n ON a.code_article = n.code_article
        GROUP BY metier
        HAVING nb_articles > 0
        ORDER BY nb_equipements_concernes DESC
    ");
    $metiersComplete = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'familles_complete' => $famillesComplete,
            'sources_complete' => $sourcesComplete,
            'articles_top' => $articlesTop,
            'types_complete' => $typesComplete,
            'evolution_simulee' => $evolutionData,
            'metiers_complete' => $metiersComplete
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => __FILE__,
        'line' => __LINE__
    ]);
}
?>
