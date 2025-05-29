<?php
require_once '../model/Database.php';
require_once '../model/Nomenclature.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success' => false, 'message' => "Aucune nomenclature sélectionnée."]);
    exit;
}

try {
    $pdo = Database::getConnection();
    $in  = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("DELETE FROM nomenclatures WHERE id IN ($in)");
    $stmt->execute($ids);
    echo json_encode(['success' => true, 'message' => count($ids) . " nomenclature(s) supprimée(s)."]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Erreur lors de la suppression."]);
}
