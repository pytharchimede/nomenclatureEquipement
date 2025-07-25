<?php
require_once '../model/Utilisateur.php';
require_once '../includes/db.php';
session_start();

header('Content-Type: application/json');

try {
    // Vérification de l'authentification
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        exit;
    }

    // Vérification de la méthode
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        exit;
    }

    // Récupération des données
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['id']) || !isset($input['actif'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
        exit;
    }

    $userId = intval($input['id']);
    $actif = $input['actif'] ? 1 : 0;

    // Empêcher l'utilisateur de se désactiver lui-même
    if ($userId == $_SESSION['user_id'] && $actif == 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas désactiver votre propre compte']);
        exit;
    }

    // Mise à jour du statut
    $sql = "UPDATE utilisateur SET actif = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$actif, $userId]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès']);
    } else {
        throw new Exception('Erreur lors de la mise à jour du statut');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
