<?php
require_once '../model/Database.php';
require_once '../model/Article.php';

$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Aucun article sélectionné.']);
    exit;
}

// Suppression en base (adapte selon ta méthode)
$success = Article::deleteByIds($ids);

echo json_encode([
    'success' => $success,
    'message' => $success ? count($ids) . ' article(s) supprimé(s).' : "Erreur lors de la suppression."
]);
