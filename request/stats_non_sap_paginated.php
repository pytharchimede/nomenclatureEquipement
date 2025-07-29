<?php
// Supprimer les warnings PHP de la sortie
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();
    set_time_limit(120);
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 60);

    $batch = isset($_GET['batch']) ? (int)$_GET['batch'] : 1;
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    $response = [
        'success' => true,
        'batch' => $batch,
        'type' => $type,
        'offset' => $offset,
        'limit' => $limit,
        'data' => [],
        'has_more' => false,
        'total_count' => 0
    ];

    switch ($type) {
        case 'general_stats':
        case 'stats_generales':
            // Statistiques générales - pas de pagination
            $stmt = $pdo->query("
                SELECT 
                    (SELECT COUNT(DISTINCT e.repere_equipement) 
                     FROM equipements e 
                     LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement AND n.source = 'SAP'
                     WHERE n.id IS NULL) as equipements_non_sap,
                    (SELECT COUNT(DISTINCT a.code_article) 
                     FROM articles a 
                     LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source = 'SAP'
                     WHERE n.id IS NULL) as articles_non_sap
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['stats'] = [
                'equipements_non_sap' => $stats['equipements_non_sap'] ?? 0,
                'articles_non_sap' => $stats['articles_non_sap'] ?? 0
            ];
            $response['has_more'] = false;
            break;

        case 'equipements_details':
            // Équipements non SAP avec pagination
            // D'abord compter le total
            $countStmt = $pdo->query("
                SELECT COUNT(DISTINCT e.repere_equipement) as total
                FROM equipements e
                LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement AND n.source = 'SAP'
                WHERE n.id IS NULL
            ");
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Ensuite récupérer les données paginées
            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    e.repere_equipement,
                    e.designation,
                    COALESCE(e.famille, 'Non définie') as famille,
                    e.localisation,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'RGM') 
                        THEN 'RGM' 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'Template') 
                        THEN 'Template'
                        ELSE 'Aucune source'
                    END as source_actuelle
                FROM equipements e
                LEFT JOIN nomenclatures n_sap ON e.repere_equipement = n_sap.repere_equipement AND n_sap.source = 'SAP'
                WHERE n_sap.id IS NULL
                ORDER BY e.repere_equipement
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = $equipements;
            $response['total_count'] = $totalCount;
            $response['has_more'] = ($offset + $limit) < $totalCount;
            break;

        case 'articles_details':
            // Articles non SAP avec pagination
            $countStmt = $pdo->query("
                SELECT COUNT(DISTINCT a.code_article) as total
                FROM articles a
                LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source = 'SAP'
                WHERE n.id IS NULL
            ");
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    a.code_article,
                    a.designation,
                    a.unite,
                    CASE 
                        WHEN a.code_article LIKE '1%' THEN 'Mécanique'
                        WHEN a.code_article LIKE '2%' THEN 'Électrique'
                        WHEN a.code_article LIKE '3%' THEN 'Instrumentation'
                        WHEN a.code_article LIKE '4%' THEN 'Tuyauterie'
                        WHEN a.code_article LIKE '5%' THEN 'Chaudronnerie'
                        WHEN a.code_article LIKE '8%' THEN 'Sécurité'
                        ELSE 'Autres'
                    END as metier,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'RGM') 
                        THEN 'RGM' 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'Template') 
                        THEN 'Template'
                        ELSE 'Aucune source'
                    END as source_actuelle
                FROM articles a
                LEFT JOIN nomenclatures n_sap ON a.code_article = n_sap.code_article AND n_sap.source = 'SAP'
                WHERE n_sap.id IS NULL
                ORDER BY a.code_article
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = $articles;
            $response['total_count'] = $totalCount;
            $response['has_more'] = ($offset + $limit) < $totalCount;
            break;

        case 'equipements_par_famille':
            // Équipements par famille
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
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = $data;
            $response['has_more'] = false;
            break;

        case 'articles_par_metier':
            // Articles par métier
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
                    COUNT(DISTINCT a.code_article) as count
                FROM articles a
                LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source = 'SAP'
                WHERE n.id IS NULL
                GROUP BY metier
                ORDER BY count DESC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = $data;
            $response['has_more'] = false;
            break;

        case 'equipements_par_source':
            // Équipements par source - version simplifiée
            try {
                $stmt = $pdo->query("
                    SELECT 
                        COALESCE(n.source, 'Aucune source') as source_actuelle,
                        COUNT(DISTINCT e.repere_equipement) as count
                    FROM equipements e
                    LEFT JOIN nomenclatures n_sap ON e.repere_equipement = n_sap.repere_equipement AND n_sap.source = 'SAP'
                    LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement AND n.source != 'SAP'
                    WHERE n_sap.id IS NULL
                    GROUP BY COALESCE(n.source, 'Aucune source')
                    ORDER BY count DESC
                ");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $response['data'] = $data ?: [];
                $response['has_more'] = false;
            } catch (Exception $e) {
                $response['success'] = false;
                $response['error'] = 'Erreur requête equipements_par_source: ' . $e->getMessage();
            }
            break;

        case 'articles_par_source':
            // Articles par source - version simplifiée
            try {
                $stmt = $pdo->query("
                    SELECT 
                        COALESCE(n.source, 'Aucune source') as source_actuelle,
                        COUNT(DISTINCT a.code_article) as count
                    FROM articles a
                    LEFT JOIN nomenclatures n_sap ON a.code_article = n_sap.code_article AND n_sap.source = 'SAP'
                    LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source != 'SAP'
                    WHERE n_sap.id IS NULL
                    GROUP BY COALESCE(n.source, 'Aucune source')
                    ORDER BY count DESC
                ");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $response['data'] = $data ?: [];
                $response['has_more'] = false;
            } catch (Exception $e) {
                $response['success'] = false;
                $response['error'] = 'Erreur requête articles_par_source: ' . $e->getMessage();
            }
            break;

        default:
            $response['success'] = false;
            $response['error'] = 'Type de données invalide';
            break;
    }

    // Simulation d'un délai réaliste
    if ($type !== 'general_stats' && $type !== 'stats_generales') {
        usleep(300000); // 0.3 seconde
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'batch' => $batch ?? 0,
        'type' => $type ?? ''
    ]);
}
