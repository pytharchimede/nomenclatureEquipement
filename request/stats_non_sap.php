<?php
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Configuration pour éviter les timeouts
    set_time_limit(60);
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 30);

    // 1. Comptage des équipements non SAP
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT e.repere_equipement) as total
        FROM equipements e
        WHERE e.repere_equipement NOT IN (
            SELECT DISTINCT n.repere_equipement 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.repere_equipement IS NOT NULL
        )
    ");
    $equipementsNonSAP = $stmt->fetchColumn();

    // 2. Comptage des articles non SAP
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT a.code_article) as total
        FROM articles a
        WHERE a.code_article NOT IN (
            SELECT DISTINCT n.code_article 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.code_article IS NOT NULL
        )
    ");
    $articlesNonSAP = $stmt->fetchColumn();

    // 3. Répartition des équipements non SAP par famille
    $stmt = $pdo->query("
        SELECT 
            COALESCE(e.famille, 'Non définie') as famille,
            COUNT(DISTINCT e.repere_equipement) as nombre
        FROM equipements e
        WHERE e.repere_equipement NOT IN (
            SELECT DISTINCT n.repere_equipement 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.repere_equipement IS NOT NULL
        )
        GROUP BY e.famille
        ORDER BY nombre DESC
    ");
    $equipementsNonSAPParFamille = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Répartition des articles non SAP par métier
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
            COUNT(DISTINCT a.code_article) as nombre
        FROM articles a
        WHERE a.code_article NOT IN (
            SELECT DISTINCT n.code_article 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.code_article IS NOT NULL
        )
        GROUP BY metier
        ORDER BY nombre DESC
    ");
    $articlesNonSAPParMetier = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Équipements non SAP par source actuelle
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'RGM') 
                THEN 'RGM' 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'Template') 
                THEN 'Template'
                ELSE 'Aucune source'
            END as source_actuelle,
            COUNT(DISTINCT e.repere_equipement) as nombre
        FROM equipements e
        WHERE e.repere_equipement NOT IN (
            SELECT DISTINCT n.repere_equipement 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.repere_equipement IS NOT NULL
        )
        GROUP BY source_actuelle
        ORDER BY nombre DESC
    ");
    $equipementsNonSAPParSource = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Articles non SAP par source actuelle
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'RGM') 
                THEN 'RGM' 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'Template') 
                THEN 'Template'
                ELSE 'Aucune source'
            END as source_actuelle,
            COUNT(DISTINCT a.code_article) as nombre
        FROM articles a
        WHERE a.code_article NOT IN (
            SELECT DISTINCT n.code_article 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.code_article IS NOT NULL
        )
        GROUP BY source_actuelle
        ORDER BY nombre DESC
    ");
    $articlesNonSAPParSource = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'equipements_non_sap' => $equipementsNonSAP,
            'articles_non_sap' => $articlesNonSAP,
            'equipements_par_famille' => $equipementsNonSAPParFamille,
            'articles_par_metier' => $articlesNonSAPParMetier,
            'equipements_par_source' => $equipementsNonSAPParSource,
            'articles_par_source' => $articlesNonSAPParSource
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
