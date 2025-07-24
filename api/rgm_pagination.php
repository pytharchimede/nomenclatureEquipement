<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';
require_once '../model/RgmSynthese.php';
require_once '../model/Nomenclature.php';

header('Content-Type: application/json');

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(10, (int)$_GET['limit'])) : 50;

    // Récupération des filtres
    $filters = [];
    if (!empty($_GET['repere_equipement'])) {
        $filters['repere_equipement'] = trim($_GET['repere_equipement']);
    }
    if (!empty($_GET['code_article'])) {
        $filters['code_article'] = trim($_GET['code_article']);
    }
    if (!empty($_GET['designation_article'])) {
        $filters['designation_article'] = trim($_GET['designation_article']);
    }
    if (!empty($_GET['unite'])) {
        $filters['unite'] = trim($_GET['unite']);
    }
    if (!empty($_GET['source'])) {
        $filters['source'] = trim($_GET['source']);
    }

    // Vérifier d'abord si la table existe
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SHOW TABLES LIKE 'rgm_synthese'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Table rgm_synthese non trouvée');
    }

    // Récupération des données paginées
    $data = RgmSynthese::getPaginated($page, $limit, $filters);
    $totalCount = RgmSynthese::getTotalCount($filters);

    // Enrichir les données avec l'état "déjà en nomenclature"
    $existMap = Nomenclature::getExistingRepereArticleMap();
    foreach ($data as &$row) {
        $key = strtolower(trim($row['repere_equipement'] ?? '')) . '|' . strtolower(trim($row['code_article'] ?? ''));
        $row['is_in_nomenclature'] = isset($existMap[$key]);
    }

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
