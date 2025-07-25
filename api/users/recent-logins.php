<?php
require_once '../model/Database.php';
session_start();

header('Content-Type: application/json');

try {
    // Vérification de l'authentification
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        exit;
    }

    // Récupération du nombre de connexions récentes (24h)
    $twentyFourHoursAgo = date('Y-m-d H:i:s', strtotime('-24 hours'));

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as count FROM utilisateur WHERE last_login_at >= ?"
    );
    $stmt->execute([$twentyFourHoursAgo]);
    $result = $stmt->fetch();
    echo json_encode([
        'success' => true,
        'count' => $result['count']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
