<?php
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // 1. Statistiques des équipements par famille avec pièces
    $stmt = $pdo->query("
        SELECT 
            COALESCE(e.famille, 'Non définie') as famille,
            COUNT(DISTINCT e.id) as nb_equipements,
            COUNT(DISTINCT n.id) as nb_pieces,
            COUNT(DISTINCT CASE WHEN n.source = 'SAP' THEN n.id END) as pieces_sap,
            COUNT(DISTINCT CASE WHEN n.source = 'RGM' THEN n.id END) as pieces_rgm
        FROM equipements e
        LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement
        GROUP BY e.famille
        ORDER BY nb_equipements DESC
    ");
    $famillesStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Répartition par type d'équipement selon codification
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN repere_equipement LIKE 'K%' THEN 'Colonnes'
                WHEN repere_equipement LIKE 'E%' THEN 'Aéro/Échangeurs'
                WHEN repere_equipement LIKE 'D%' THEN 'Ballons'
                WHEN repere_equipement LIKE 'LFD%' OR repere_equipement LIKE '%S%' THEN 'Filtres Détendeurs'
                WHEN repere_equipement LIKE 'T%' THEN 'Réservoirs/Bacs'
                WHEN repere_equipement LIKE 'F%' THEN 'Fours'
                WHEN repere_equipement LIKE 'R%' THEN 'Réacteurs'
                WHEN repere_equipement LIKE 'C%' THEN 'Compresseurs'
                WHEN repere_equipement LIKE 'P%' THEN 'Pompes'
                WHEN repere_equipement LIKE 'MP%' THEN 'Moteurs'
                WHEN repere_equipement LIKE 'SV%' THEN 'Soupapes'
                WHEN repere_equipement LIKE 'LG%' THEN 'Niveaux à glace'
                WHEN repere_equipement LIKE 'FE%' THEN 'Plaques à orifice'
                WHEN repere_equipement LIKE 'EXT%' THEN 'Extincteurs'
                WHEN repere_equipement LIKE '__V%' THEN 'Vannes auto'
                ELSE 'Autres'
            END as type_codification,
            COUNT(*) as nombre,
            COUNT(CASE WHEN code_equipement IS NOT NULL AND code_equipement != '' THEN 1 END) as codifies_sap
        FROM equipements 
        GROUP BY type_codification
        ORDER BY nombre DESC
    ");
    $typesStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Équipements sans nomenclatures
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM equipements e
        LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement
        WHERE n.id IS NULL
    ");
    $equipementsSansNomenclature = $stmt->fetchColumn();

    // 4. Articles non codifiés SAP
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM articles a
        LEFT JOIN nomenclatures n ON a.code_article = n.code_article
        WHERE n.source != 'SAP' OR n.source IS NULL
    ");
    $articlesNonSAP = $stmt->fetchColumn();

    // 5. Sources des données
    $stmt = $pdo->query("
        SELECT 
            source,
            COUNT(DISTINCT repere_equipement) as equipements,
            COUNT(DISTINCT code_article) as articles,
            COUNT(*) as nomenclatures
        FROM nomenclatures 
        WHERE source IS NOT NULL
        GROUP BY source
    ");
    $sourcesStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Répartition des articles par métier (basé sur le code article)
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
            COUNT(DISTINCT n.repere_equipement) as nb_equipements_lies
        FROM articles a
        LEFT JOIN nomenclatures n ON a.code_article = n.code_article
        GROUP BY metier
        ORDER BY nb_articles DESC
    ");
    $metiersStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Évolution mensuelle des ajouts
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(date_creation, '%Y-%m') as mois,
            COUNT(*) as ajouts_equipements
        FROM equipements 
        WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY mois
        ORDER BY mois
    ");
    $evolutionEquipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'familles' => $famillesStats,
            'types_codification' => $typesStats,
            'equipements_sans_nomenclature' => $equipementsSansNomenclature,
            'articles_non_sap' => $articlesNonSAP,
            'sources' => $sourcesStats,
            'metiers' => $metiersStats,
            'evolution' => $evolutionEquipements
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
