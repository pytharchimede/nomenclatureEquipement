<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';
require_once '../model/Quantitatif.php';

header('Content-Type: application/json');

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(10, (int)$_GET['limit'])) : 50;

    // Récupération des filtres
    $filters = [];
    if (!empty($_GET['famille'])) {
        $filters['famille'] = trim($_GET['famille']);
    }
    if (!empty($_GET['repere'])) {
        $filters['repere'] = trim($_GET['repere']);
    }
    if (!empty($_GET['unite'])) {
        $filters['unite'] = trim($_GET['unite']);
    }

    // Vérifier d'abord si la table existe
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SHOW TABLES LIKE 'quantitatif'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Table quantitatif non trouvée');
    }

    // Récupération des données paginées
    $data = Quantitatif::getPaginated($page, $limit, $filters);
    $totalCount = Quantitatif::getTotalCount($filters);

    $totalPages = ceil($totalCount / $limit);
    $hasMore = $page < $totalPages;

    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'limit' => $limit,
            'has_more' => $hasMore,
            'showing' => count($data),
            'from' => (($page - 1) * $limit) + 1,
            'to' => min($page * $limit, $totalCount)
        ],
        'filters_applied' => $filters
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'page' => $_GET['page'] ?? null,
            'limit' => $_GET['limit'] ?? null,
            'filters' => $filters ?? []
        ]
    ]);
}
