<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';
require_once '../model/Nomenclature.php';

header('Content-Type: application/json');

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(10, (int)$_GET['limit'])) : 50;

    // Récupération des filtres
    $filters = [];
    if (!empty($_GET['repere'])) {
        $filters['repere_equipement'] = trim($_GET['repere']);
    }
    if (!empty($_GET['code'])) {
        $filters['code_article'] = trim($_GET['code']);
    }
    if (!empty($_GET['designation'])) {
        $filters['designation_article'] = trim($_GET['designation']);
    }
    if (!empty($_GET['unite'])) {
        $filters['unite'] = trim($_GET['unite']);
    }

    // Forcer le filtre source = 'RGM'
    $filters['source'] = 'RGM';

    // Récupération des données paginées depuis la table nomenclatures
    $data = getRgmPaginatedData($page, $limit, $filters);
    $totalCount = getRgmTotalCount($filters);

    $totalPages = ceil($totalCount / $limit);
    $hasMore = $page < $totalPages;

    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => $totalCount,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'has_more' => $hasMore,
        'filters_applied' => !empty(array_filter($filters, function ($value) {
            return $value !== 'RGM'; // Exclure le filtre source du comptage
        }))
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => [],
        'total' => 0
    ]);
}

// Fonctions utilitaires pour la pagination RGM

function getRgmPaginatedData($page, $limit, $filters)
{
    $pdo = Database::getConnection();

    $offset = ($page - 1) * $limit;
    $whereConditions = ["source = 'RGM'"];
    $params = [];

    // Construction des conditions WHERE
    if (!empty($filters['repere_equipement'])) {
        $whereConditions[] = "repere_equipement LIKE ?";
        $params[] = '%' . $filters['repere_equipement'] . '%';
    }
    if (!empty($filters['code_article'])) {
        $whereConditions[] = "code_article LIKE ?";
        $params[] = '%' . $filters['code_article'] . '%';
    }
    if (!empty($filters['designation_article'])) {
        $whereConditions[] = "designation_article LIKE ?";
        $params[] = '%' . $filters['designation_article'] . '%';
    }
    if (!empty($filters['unite'])) {
        $whereConditions[] = "unite = ?";
        $params[] = $filters['unite'];
    }

    $whereClause = "WHERE " . implode(" AND ", $whereConditions);

    $sql = "
        SELECT id, repere_equipement, code_article, designation_article, 
               quantite, unite, date_creation
        FROM nomenclatures 
        {$whereClause}
        ORDER BY date_creation DESC, id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter l'information de synchronisation (toujours true car déjà dans nomenclatures)
    foreach ($results as &$row) {
        $row['in_nomenclature'] = true;
    }

    return $results;
}

function getRgmTotalCount($filters)
{
    $pdo = Database::getConnection();

    $whereConditions = ["source = 'RGM'"];
    $params = [];

    // Construction des conditions WHERE (même logique que getRgmPaginatedData)
    if (!empty($filters['repere_equipement'])) {
        $whereConditions[] = "repere_equipement LIKE ?";
        $params[] = '%' . $filters['repere_equipement'] . '%';
    }
    if (!empty($filters['code_article'])) {
        $whereConditions[] = "code_article LIKE ?";
        $params[] = '%' . $filters['code_article'] . '%';
    }
    if (!empty($filters['designation_article'])) {
        $whereConditions[] = "designation_article LIKE ?";
        $params[] = '%' . $filters['designation_article'] . '%';
    }
    if (!empty($filters['unite'])) {
        $whereConditions[] = "unite = ?";
        $params[] = $filters['unite'];
    }

    $whereClause = "WHERE " . implode(" AND ", $whereConditions);

    $sql = "SELECT COUNT(*) FROM nomenclatures {$whereClause}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchColumn();
}
