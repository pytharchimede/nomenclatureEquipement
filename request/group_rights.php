<?php

require_once '../model/DroitUtilisateur.php';
header('Content-Type: application/json');
$id = $_GET['id'] ?? null;
if ($id) {
    $droits = DroitUtilisateur::getByGroupe($id);
    echo json_encode(['droits' => $droits]);
} else {
    echo json_encode(['droits' => []]);
}
