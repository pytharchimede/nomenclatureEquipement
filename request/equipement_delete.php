<?php
require_once '../model/Database.php';
require_once '../model/Equipement.php';

$data = json_decode(file_get_contents('php://input'), true);
$reperes = $data['reperes'] ?? [];

if (!is_array($reperes) || empty($reperes)) {
    echo json_encode(['success' => false, 'message' => 'Aucun équipement sélectionné.']);
    exit;
}

// Suppression en base par repere_equipement
$success = Equipement::deleteByReperes($reperes);

echo json_encode([
    'success' => $success,
    'message' => $success ? count($reperes) . ' équipement(s) supprimé(s).' : "Erreur lors de la suppression."
]);
