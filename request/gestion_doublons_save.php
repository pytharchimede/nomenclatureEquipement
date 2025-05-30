<?php
require_once '../model/Nomenclature.php';

header('Content-Type: application/json');

if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    Nomenclature::delete($id);
    echo json_encode(['deleted' => true]);
    exit;
}

$keep = $_POST['keep'] ?? [];
$edit = $_POST['edit'] ?? [];

foreach ($edit as $id => $cols) {
    Nomenclature::update($id, $cols);
}

foreach ($keep as $key => $idToKeep) {
    $ids = array_keys($edit);
    foreach ($ids as $id) {
        if ($id != $idToKeep) {
            Nomenclature::delete($id);
        }
    }
}

echo json_encode(['success' => true]);
exit;
