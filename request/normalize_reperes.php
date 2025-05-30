<?php
require_once '../model/Database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['updates']) || !is_array($data['updates'])) {
    echo json_encode(['success' => false, 'message' => 'Aucune donnée reçue']);
    exit;
}

$pdo = Database::getConnection();
$ok = true;
foreach ($data['updates'] as $u) {
    // On met à jour le repère dans la famille concernée
    $stmt = $pdo->prepare("UPDATE quantitatif SET repere = ? WHERE famille = ? AND repere = ?");
    $ok = $ok && $stmt->execute([$u['repereNormalise'], $u['famille'], $u['repere']]);
}
echo json_encode(['success' => $ok]);
