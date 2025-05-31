<?php
require_once '../model/Utilisateur.php';
header('Content-Type: application/json');
$id = $_POST['id'] ?? null;
$actif = isset($_POST['actif']) ? (int)$_POST['actif'] : null;
if ($id !== null && $actif !== null) {
    $ok = Utilisateur::update($id, ['actif' => $actif]);
    echo json_encode(['success' => $ok]);
} else {
    echo json_encode(['success' => false]);
}
