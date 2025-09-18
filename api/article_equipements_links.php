<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();
    $code = $_GET['code_article'] ?? '';
    if ($code === '') {
        echo json_encode(['success' => false, 'message' => 'code_article requis']);
        exit;
    }

    $stmt = $pdo->prepare("\n        SELECT DISTINCT n.repere_equipement, e.famille, e.type_objet, e.designation_equipement\n        FROM nomenclatures n\n        LEFT JOIN equipements e ON e.repere_equipement = n.repere_equipement\n        WHERE n.code_article = ? AND n.repere_equipement IS NOT NULL\n        ORDER BY n.repere_equipement\n    ");
    $stmt->execute([$code]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'equipements' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
