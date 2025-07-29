<?php
// Version ultra-optimisée pour chargement rapide
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Optimisation : pas de timeout, exécution rapide
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);

    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    $response = [
        'success' => true,
        'type' => $type,
        'data' => [],
        'has_more' => false,
        'total_count' => 0
    ];

    switch ($type) {
        case 'general_stats':
        case 'stats_generales':
            // Requête ultra-optimisée en une seule passe
            $stmt = $pdo->query("
                SELECT 
                    (SELECT COUNT(*) FROM equipements WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini') as equipements_non_sap,
                    (SELECT COUNT(*) FROM articles WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini') as articles_non_sap
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['stats'] = [
                'equipements_non_sap' => (int)($stats['equipements_non_sap'] ?? 0),
                'articles_non_sap' => (int)($stats['articles_non_sap'] ?? 0)
            ];
            break;

        case 'equipements_details':
            // Requête simplifiée pour les équipements
            $stmt = $pdo->prepare("
                SELECT 
                    repere_equipement,
                    designation,
                    COALESCE(famille, 'Non définie') as famille,
                    COALESCE(source_actuelle, 'Inconnue') as source_actuelle
                FROM equipements 
                WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                ORDER BY repere_equipement
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compter rapidement
            $countStmt = $pdo->query("
                SELECT COUNT(*) as total 
                FROM equipements 
                WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
            ");
            $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $response['data'] = $equipements;
            $response['total_count'] = $totalCount;
            $response['has_more'] = ($offset + $limit) < $totalCount;
            break;

        case 'articles_details':
            // Requête simplifiée pour les articles
            $stmt = $pdo->prepare("
                SELECT 
                    code_article,
                    designation,
                    CASE 
                        WHEN code_article LIKE '1%' THEN 'Mécanique'
                        WHEN code_article LIKE '2%' THEN 'Électrique'
                        WHEN code_article LIKE '3%' THEN 'Instrumentation'
                        WHEN code_article LIKE '4%' THEN 'Tuyauterie'
                        WHEN code_article LIKE '5%' THEN 'Chaudronnerie'
                        WHEN code_article LIKE '8%' THEN 'Sécurité'
                        ELSE 'Autres'
                    END as metier,
                    COALESCE(source_actuelle, 'Inconnue') as source_actuelle
                FROM articles 
                WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                ORDER BY code_article
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compter rapidement
            $countStmt = $pdo->query("
                SELECT COUNT(*) as total 
                FROM articles 
                WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
            ");
            $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $response['data'] = $articles;
            $response['total_count'] = $totalCount;
            $response['has_more'] = ($offset + $limit) < $totalCount;
            break;

        case 'equipements_par_famille':
            // Requête ultra-rapide pour familles
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(famille, 'Non définie') as famille,
                    COUNT(*) as count
                FROM equipements 
                WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                GROUP BY famille
                ORDER BY count DESC
                LIMIT 10
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['data'] = $data;
            break;

        case 'articles_par_metier':
            // Requête ultra-rapide pour métiers
            $stmt = $pdo->query("
                SELECT 
                    CASE 
                        WHEN code_article LIKE '1%' THEN 'Mécanique'
                        WHEN code_article LIKE '2%' THEN 'Électrique'
                        WHEN code_article LIKE '3%' THEN 'Instrumentation'
                        WHEN code_article LIKE '4%' THEN 'Tuyauterie'
                        WHEN code_article LIKE '5%' THEN 'Chaudronnerie'
                        WHEN code_article LIKE '8%' THEN 'Sécurité'
                        ELSE 'Autres'
                    END as metier,
                    COUNT(*) as count
                FROM articles 
                WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                GROUP BY metier
                ORDER BY count DESC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['data'] = $data;
            break;

        case 'equipements_par_source':
            // Requête simplifiée pour sources
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(source_actuelle, 'Aucune source') as source_actuelle,
                    COUNT(*) as count
                FROM equipements 
                WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                GROUP BY source_actuelle
                ORDER BY count DESC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['data'] = $data;
            break;

        case 'articles_par_source':
            // Requête simplifiée pour sources d'articles
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(source_actuelle, 'Aucune source') as source_actuelle,
                    COUNT(*) as count
                FROM articles 
                WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                GROUP BY source_actuelle
                ORDER BY count DESC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['data'] = $data;
            break;

        case 'all_data':
            // Nouvelle route : charger TOUTES les données en une fois
            $allData = [];

            // Stats générales
            $stmt = $pdo->query("
                SELECT 
                    (SELECT COUNT(*) FROM equipements WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini') as equipements_non_sap,
                    (SELECT COUNT(*) FROM articles WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini') as articles_non_sap
            ");
            $allData['stats'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Familles d'équipements
            $stmt = $pdo->query("
                SELECT COALESCE(famille, 'Non définie') as famille, COUNT(*) as count
                FROM equipements WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                GROUP BY famille ORDER BY count DESC LIMIT 10
            ");
            $allData['equipements_par_famille'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Métiers d'articles
            $stmt = $pdo->query("
                SELECT 
                    CASE 
                        WHEN code_article LIKE '1%' THEN 'Mécanique'
                        WHEN code_article LIKE '2%' THEN 'Électrique'
                        WHEN code_article LIKE '3%' THEN 'Instrumentation'
                        WHEN code_article LIKE '4%' THEN 'Tuyauterie'
                        WHEN code_article LIKE '5%' THEN 'Chaudronnerie'
                        WHEN code_article LIKE '8%' THEN 'Sécurité'
                        ELSE 'Autres'
                    END as metier,
                    COUNT(*) as count
                FROM articles WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                GROUP BY metier ORDER BY count DESC
            ");
            $allData['articles_par_metier'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Sources équipements
            $stmt = $pdo->query("
                SELECT COALESCE(source_actuelle, 'Aucune source') as source_actuelle, COUNT(*) as count
                FROM equipements WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                GROUP BY source_actuelle ORDER BY count DESC
            ");
            $allData['equipements_par_source'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Sources articles
            $stmt = $pdo->query("
                SELECT COALESCE(source_actuelle, 'Aucune source') as source_actuelle, COUNT(*) as count
                FROM articles WHERE code_sap IS NULL OR code_sap = '' OR code_sap = 'Non défini'
                GROUP BY source_actuelle ORDER BY count DESC
            ");
            $allData['articles_par_source'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['data'] = $allData;
            break;

        default:
            $response['success'] = false;
            $response['error'] = 'Type de données invalide';
            break;
    }

    // PAS DE DÉLAI ARTIFICIEL - réponse immédiate
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'type' => $type ?? ''
    ]);
}
