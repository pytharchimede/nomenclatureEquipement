<?php
require_once '../model/Utilisateur.php';
header('Content-Type: application/json');
$id = $_POST['id'] ?? null;
$groupe_id = $_POST['groupe_id'] ?? null;
if ($id !== null && $groupe_id !== null) {
    $ok = Utilisateur::update($id, ['groupe_id' => $groupe_id]);
    echo json_encode(['success' => $ok]);
} else {
    echo json_encode(['success' => false]);
}
