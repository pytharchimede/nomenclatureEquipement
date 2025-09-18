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

    $response = [
        'success' => true,
        'type' => $type,
        'data' => [],
        'cached' => false
    ];

    switch ($type) {
        // Mode EVERYTHING - Tout en une seule requête !
        case 'everything':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;

            // Vérifier le cache en premier
            $cacheKey = getCacheKey('everything', ['limit' => $limit]);
            $cachedData = getCache($cacheKey);
            if ($cachedData) {
                echo json_encode($cachedData);
                exit;
            }

            $result = [
                'success' => true,
                'stats' => [],
                'equipements' => [],
                'articles' => [],
                'equipements_par_famille' => [],
                'articles_par_metier' => [],
                'equipements_par_source' => [],
                'articles_par_source' => []
            ];

            // NOUVELLE APPROCHE : Charger TOUT en mémoire puis traiter

            // 1. Charger TOUS les équipements non-SAP avec leurs sources réelles de la table nomenclatures
            $stmt = $pdo->query("
                SELECT 
                    e.repere_equipement,
                    COALESCE(e.famille, 'Non définie') as famille,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'RGM') 
                        THEN 'RGM' 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'Template') 
                        THEN 'Template'
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'SPL') 
                        THEN 'SPL'
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source IS NOT NULL) 
                        THEN (SELECT DISTINCT n.source FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source IS NOT NULL LIMIT 1)
                        ELSE 'Aucune source'
                    END as source_reelle
                FROM equipements e 
                WHERE e.repere_equipement NOT IN (
                    SELECT DISTINCT n.repere_equipement 
                    FROM nomenclatures n 
                    WHERE n.source = 'SAP' AND n.repere_equipement IS NOT NULL
                )
                ORDER BY e.repere_equipement
            ");
            $all_equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Charger TOUS les articles non-SAP avec leurs sources réelles de la table nomenclatures
            $stmt = $pdo->query("
                SELECT 
                    a.code_article,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'RGM') 
                        THEN 'RGM' 
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'Template') 
                        THEN 'Template'
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'SPL') 
                        THEN 'SPL'
                        WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source IS NOT NULL) 
                        THEN (SELECT DISTINCT n.source FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source IS NOT NULL LIMIT 1)
                        ELSE 'Aucune source'
                    END as source_reelle
                FROM articles a 
                WHERE a.code_article NOT IN (
                    SELECT DISTINCT n.code_article 
                    FROM nomenclatures n 
                    WHERE n.source = 'SAP' AND n.code_article IS NOT NULL
                )
                ORDER BY a.code_article
            ");
            $all_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);            // 3. TRAITEMENT EN MÉMOIRE - Ultra rapide !

            // Stats générales
            $result['stats'] = [
                'equipements_non_sap' => count($all_equipements),
                'articles_non_sap' => count($all_articles)
            ];

            // Équipements limités pour affichage
            $result['equipements'] = array_slice($all_equipements, 0, $limit);

            // Articles avec métier calculé + limités
            $articles_with_metier = [];
            foreach ($all_articles as $article) {
                $metier = 'Autres';
                $code = $article['code_article'];
                if (str_starts_with($code, '1')) $metier = 'Mécanique';
                else if (str_starts_with($code, '2')) $metier = 'Électrique';
                else if (str_starts_with($code, '3')) $metier = 'Instrumentation';
                else if (str_starts_with($code, '4')) $metier = 'Tuyauterie';
                else if (str_starts_with($code, '5')) $metier = 'Chaudronnerie';
                else if (str_starts_with($code, '8')) $metier = 'Sécurité';

                $articles_with_metier[] = [
                    'code_article' => $code,
                    'metier' => $metier,
                    'source' => $article['source_reelle']
                ];
            }
            $result['articles'] = array_slice($articles_with_metier, 0, $limit);

            // Équipements par famille (groupement en mémoire)
            $famille_counts = [];
            foreach ($all_equipements as $eq) {
                $famille = $eq['famille'];
                $famille_counts[$famille] = ($famille_counts[$famille] ?? 0) + 1;
            }
            arsort($famille_counts);
            $result['equipements_par_famille'] = [];
            $count = 0;
            foreach ($famille_counts as $label => $count_val) {
                if ($count++ >= 10) break;
                $result['equipements_par_famille'][] = ['label' => $label, 'count' => $count_val];
            }

            // Articles par métier (groupement en mémoire)
            $metier_counts = [];
            foreach ($articles_with_metier as $art) {
                $metier = $art['metier'];
                $metier_counts[$metier] = ($metier_counts[$metier] ?? 0) + 1;
            }
            arsort($metier_counts);
            $result['articles_par_metier'] = [];
            foreach ($metier_counts as $label => $count_val) {
                $result['articles_par_metier'][] = ['label' => $label, 'count' => $count_val];
            }

            // Équipements par source (groupement en mémoire avec sources réelles)
            $source_eq_counts = [];
            foreach ($all_equipements as $eq) {
                $source = $eq['source_reelle'];
                $source_eq_counts[$source] = ($source_eq_counts[$source] ?? 0) + 1;
            }
            arsort($source_eq_counts);
            $result['equipements_par_source'] = [];
            $count = 0;
            foreach ($source_eq_counts as $label => $count_val) {
                if ($count++ >= 10) break;
                $result['equipements_par_source'][] = ['label' => $label, 'count' => $count_val];
            }

            // Articles par source (groupement en mémoire avec sources réelles)
            $source_art_counts = [];
            foreach ($all_articles as $article) {
                $source = $article['source_reelle'];
                $source_art_counts[$source] = ($source_art_counts[$source] ?? 0) + 1;
            }
            arsort($source_art_counts);
            $result['articles_par_source'] = [];
            $count = 0;
            foreach ($source_art_counts as $label => $count_val) {
                if ($count++ >= 10) break;
                $result['articles_par_source'][] = ['label' => $label, 'count' => $count_val];
            }

            // Sauvegarder dans le cache
            setCache($cacheKey, $result);

            echo json_encode($result);
            exit;

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
