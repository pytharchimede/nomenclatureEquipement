<?php

require_once '../model/Utilisateur.php';
header('Content-Type: application/json');
$id = $_POST['id'] ?? null;
if ($id) {
    $ok = Utilisateur::delete($id);
    echo json_encode(['success' => $ok]);
} else {
    echo json_encode(['success' => false]);
}
