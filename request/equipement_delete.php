<?php
require_once '../model/Database.php';
require_once '../model/Equipement.php';

$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Aucun équipement sélectionné.']);
    exit;
}

// Suppression en base (adapte selon ta méthode)
$success = Equipement::deleteByIds($ids);

echo json_encode([
    'success' => $success,
    'message' => $success ? count($ids) . ' équipement(s) supprimé(s).' : "Erreur lors de la suppression."
]);
