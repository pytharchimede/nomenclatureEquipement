<?php
// VERSION ULTRA-OPTIMISÉE avec cache pour des performances maximales
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');
header('Cache-Control: public, max-age=60'); // Cache 60 secondes

// Simple cache en fichier
function getCacheKey($type, $params = [])
{
    return md5($type . serialize($params));
}

function getCache($key)
{
    $cacheFile = __DIR__ . '/../cache/stats_' . $key . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 60) { // Cache 60 secondes
        return json_decode(file_get_contents($cacheFile), true);
    }
    return null;
}

function setCache($key, $data)
{
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $cacheFile = $cacheDir . '/stats_' . $key . '.json';
    file_put_contents($cacheFile, json_encode($data));
}

try {
    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 3); // Timeout très court

    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    // Vérifier le cache en premier
    $cacheKey = getCacheKey($type, ['offset' => $offset, 'limit' => $limit]);
    $cachedData = getCache($cacheKey);
    if ($cachedData && $type !== 'equipements_details' && $type !== 'articles_details') {
        echo json_encode($cachedData);
        exit;
    }

    $response = [
        'success' => true,
        'type' => $type,
        'data' => [],
        'cached' => false
    ];

    switch ($type) {
        case 'all_data':
            // SUPER REQUÊTE : Tout en une seule fois avec logique nomenclatures
            $allData = [];

            // Statistiques générales - Équipements et articles NON SAP
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
            $allData['stats'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Familles d'équipements non SAP
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(e.famille, 'Non définie') as label,
                    COUNT(DISTINCT e.repere_equipement) as count
                FROM equipements e 
                LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement AND n.source = 'SAP'
                WHERE n.id IS NULL
                GROUP BY e.famille
                ORDER BY count DESC
                LIMIT 10
            ");
            $allData['equipements_par_famille'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Métiers d'articles non SAP
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
                    END as label,
                    COUNT(DISTINCT a.code_article) as count
                FROM articles a 
                LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source = 'SAP'
                WHERE n.id IS NULL
                GROUP BY label
                ORDER BY count DESC
            ");
            $allData['articles_par_metier'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Sources d'équipements non SAP
            $stmt = $pdo->query("
                SELECT 
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n2 WHERE n2.repere_equipement = e.repere_equipement AND n2.source = 'RGM') 
                        THEN 'RGM' 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n2 WHERE n2.repere_equipement = e.repere_equipement AND n2.source = 'Template') 
                        THEN 'Template'
                        ELSE 'Aucune source'
                    END as label,
                    COUNT(DISTINCT e.repere_equipement) as count
                FROM equipements e 
                LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement AND n.source = 'SAP'
                WHERE n.id IS NULL
                GROUP BY label
                ORDER BY count DESC
            ");
            $allData['equipements_par_source'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Sources d'articles non SAP
            $stmt = $pdo->query("
                SELECT 
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n2 WHERE n2.code_article = a.code_article AND n2.source = 'RGM') 
                        THEN 'RGM' 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n2 WHERE n2.code_article = a.code_article AND n2.source = 'Template') 
                        THEN 'Template'
                        ELSE 'Aucune source'
                    END as label,
                    COUNT(DISTINCT a.code_article) as count
                FROM articles a 
                LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source = 'SAP'
                WHERE n.id IS NULL
                GROUP BY label
                ORDER BY count DESC
            ");
            $allData['articles_par_source'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = $allData;

            // Mettre en cache pour 60 secondes
            setCache($cacheKey, $response);
            break;

        case 'equipements_details':
            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    e.repere_equipement,
                    COALESCE(e.designation, '') as designation,
                    COALESCE(e.famille, 'Non définie') as famille,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n2 WHERE n2.repere_equipement = e.repere_equipement AND n2.source = 'RGM') 
                        THEN 'RGM' 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n2 WHERE n2.repere_equipement = e.repere_equipement AND n2.source = 'Template') 
                        THEN 'Template'
                        ELSE 'Aucune source'
                    END as source_actuelle
                FROM equipements e 
                LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement AND n.source = 'SAP'
                WHERE n.id IS NULL
                ORDER BY e.repere_equipement
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $countStmt = $pdo->query("
                SELECT COUNT(DISTINCT e.repere_equipement) as total 
                FROM equipements e 
                LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement AND n.source = 'SAP'
                WHERE n.id IS NULL
            ");
            $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $response['data'] = $data;
            $response['total_count'] = $totalCount;
            $response['has_more'] = ($offset + $limit) < $totalCount;
            break;

        case 'articles_details':
            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    a.code_article,
                    COALESCE(a.designation, '') as designation,
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
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n2 WHERE n2.code_article = a.code_article AND n2.source = 'RGM') 
                        THEN 'RGM' 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n2 WHERE n2.code_article = a.code_article AND n2.source = 'Template') 
                        THEN 'Template'
                        ELSE 'Aucune source'
                    END as source_actuelle
                FROM articles a 
                LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source = 'SAP'
                WHERE n.id IS NULL
                ORDER BY a.code_article
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $countStmt = $pdo->query("
                SELECT COUNT(DISTINCT a.code_article) as total 
                FROM articles a 
                LEFT JOIN nomenclatures n ON a.code_article = n.code_article AND n.source = 'SAP'
                WHERE n.id IS NULL
            ");
            $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $response['data'] = $data;
            $response['total_count'] = $totalCount;
            $response['has_more'] = ($offset + $limit) < $totalCount;
            break;

        default:
            $response['success'] = false;
            $response['error'] = 'Type invalide';
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
