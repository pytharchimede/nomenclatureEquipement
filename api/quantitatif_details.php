<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        throw new Exception('ID manquant');
    }

    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM quantitatif WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception('Élément non trouvé');
    }

    // Décodage des colonnes additionnelles
    if ($item['autres_colonnes']) {
        $item['autres_colonnes'] = json_decode($item['autres_colonnes'], true) ?: $item['autres_colonnes'];
    }

    echo json_encode([
        'success' => true,
        'item' => $item
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
