<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Paramètres de pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = ($page - 1) * $limit;

    // Construction de la requête avec filtres
    $whereConditions = [];
    $params = [];

    // Filtres de recherche
    $filters = [
        'numero' => 'numero',
        'code_sap' => 'code_sap',
        'code_article' => 'code_article',
        'quantite' => 'quantite',
        'designation_article' => 'designation_article',
        'unite_base' => 'unite_base',
        'metier' => 'metier',
        'numero_piece_fabricant' => 'numero_piece_fabricant',
        'fabricant' => 'fabricant',
        'equipement' => 'equipement'
    ];

    foreach ($filters as $param => $column) {
        if (isset($_GET[$param]) && $_GET[$param] !== '') {
            if ($param === 'quantite') {
                $whereConditions[] = "$column = ?";
                $params[] = $_GET[$param];
            } else {
                $whereConditions[] = "$column LIKE ?";
                $params[] = '%' . $_GET[$param] . '%';
            }
        }
    }

    // Filtre par date
    if (isset($_GET['date_debut']) && $_GET['date_debut'] !== '') {
        $whereConditions[] = "DATE(date_import) >= ?";
        $params[] = $_GET['date_debut'];
    }

    if (isset($_GET['date_fin']) && $_GET['date_fin'] !== '') {
        $whereConditions[] = "DATE(date_import) <= ?";
        $params[] = $_GET['date_fin'];
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Comptage total pour pagination
    $countSql = "SELECT COUNT(*) FROM template_spl $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();

    // Requête principale avec séparation des équipements
    $sql = "SELECT * FROM template_spl $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Séparation des équipements (une ligne par repère)
    $rows = [];
    foreach ($templates as $tpl) {
        $reperes = preg_split('/\s*\/\s*/', $tpl['equipement']);
        foreach ($reperes as $repere) {
            $row = $tpl;
            $row['equipement'] = trim($repere);
            $rows[] = $row;
        }
    }

    // Statistiques pour les mini-cards
    $statsQueries = [
        'total_lignes' => "SELECT COUNT(*) FROM template_spl",
        'total_articles' => "SELECT COUNT(DISTINCT code_article) FROM template_spl",
        'total_equipements' => "SELECT COUNT(DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(equipement, '/', ','), ',', n), ',', -1))) 
                               FROM template_spl 
                               CROSS JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) numbers 
                               WHERE CHAR_LENGTH(equipement) - CHAR_LENGTH(REPLACE(equipement, '/', '')) >= n - 1",
        'total_metiers' => "SELECT COUNT(DISTINCT metier) FROM template_spl WHERE metier IS NOT NULL AND metier != ''"
    ];

    $stats = [];
    foreach ($statsQueries as $key => $query) {
        $stmt = $pdo->query($query);
        $stats[$key] = $stmt->fetchColumn();
    }

    // Préparation de la réponse
    $response = [
        'success' => true,
        'data' => $rows,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalRecords / $limit),
            'total_records' => $totalRecords,
            'per_page' => $limit,
            'has_next' => $page < ceil($totalRecords / $limit),
            'has_prev' => $page > 1
        ],
        'stats' => $stats
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
    ]);
}
