<?php
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Configuration pour éviter les timeouts
    set_time_limit(120);
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 60);

    // Paramètres de pagination
    $batch = isset($_GET['batch']) ? (int)$_GET['batch'] : 1;
    $batchSize = 50; // Traiter 50 éléments par lot
    $offset = ($batch - 1) * $batchSize;

    $response = [
        'success' => true,
        'batch' => $batch,
        'data' => []
    ];

    switch ($batch) {
        case 1:
            // Lot 1: Statistiques générales
            $stmt = $pdo->query("
                SELECT 
                    COUNT(DISTINCT e.repere_equipement) as equipements_non_sap,
                    COUNT(DISTINCT a.code_article) as articles_non_sap
                FROM equipements e
                LEFT JOIN nomenclatures n_eq ON e.repere_equipement = n_eq.repere_equipement AND n_eq.source = 'SAP'
                CROSS JOIN articles a
                LEFT JOIN nomenclatures n_art ON a.code_article = n_art.code_article AND n_art.source = 'SAP'
                WHERE n_eq.id IS NULL OR n_art.id IS NULL
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['data'] = [
                'type' => 'stats_generales',
                'equipements_non_sap' => $stats['equipements_non_sap'] ?? 0,
                'articles_non_sap' => $stats['articles_non_sap'] ?? 0,
                'total_batches' => 5 // Nombre total de lots
            ];
            break;

        case 2:
            // Lot 2: Équipements non SAP par famille
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(e.famille, 'Non définie') as famille,
                    COUNT(DISTINCT e.repere_equipement) as nombre
                FROM equipements e
                LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement AND n.source = 'SAP'
                WHERE n.id IS NULL
                GROUP BY e.famille
                ORDER BY nombre DESC
                LIMIT 15
            ");
            $equipements_par_famille = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = [
                'type' => 'equipements_par_famille',
                'data' => $equipements_par_famille
            ];
            break;

        case 3:
            // Lot 3: Articles non SAP par métier
            $stmt = $pdo->query("
                SELECT 
                    CASE 
                        WHEN a.code_article LIKE '1%' THEN 'Mécanique'
                        WHEN a.code_article LIKE '2%' THEN 'Électrique'
                        WHEN a.code_article LIKE '3%' THEN 'Instrumentation'
                        WHEN a.code_article LIKE '4%' THEN 'Tuyauterie'
                        WHEN a.code_article LIKE '5%' THEN 'Chaudronnerie'
                        WHEN a.code_article LIKE '8%' THEN 'Sécurité'
                        ELSE 'Autres'
                    END as metier,
                    COUNT(DISTINCT a.code_article) as nombre
                FROM articles a
                LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source = 'SAP'
                WHERE n.id IS NULL
                GROUP BY metier
                ORDER BY nombre DESC
            ");
            $articles_par_metier = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = [
                'type' => 'articles_par_metier',
                'data' => $articles_par_metier
            ];
            break;

        case 4:
            // Lot 4: Équipements par source actuelle
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
                LEFT JOIN nomenclatures n_sap ON e.repere_equipement = n_sap.repere_equipement AND n_sap.source = 'SAP'
                WHERE n_sap.id IS NULL
                GROUP BY source_actuelle
                ORDER BY nombre DESC
            ");
            $equipements_par_source = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = [
                'type' => 'equipements_par_source',
                'data' => $equipements_par_source
            ];
            break;

        case 5:
            // Lot 5: Articles par source actuelle
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
                LEFT JOIN nomenclatures n_sap ON a.code_article = n_sap.code_article AND n_sap.source = 'SAP'
                WHERE n_sap.id IS NULL
                GROUP BY source_actuelle
                ORDER BY nombre DESC
            ");
            $articles_par_source = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = [
                'type' => 'articles_par_source',
                'data' => $articles_par_source
            ];
            break;

        default:
            $response['success'] = false;
            $response['error'] = 'Lot invalide';
            break;
    }

    // Simulation d'un délai réaliste pour voir le chargement progressif
    if ($batch > 1) {
        usleep(500000); // 0.5 seconde de délai
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'batch' => $batch ?? 0
    ]);
}
