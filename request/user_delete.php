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

    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
        exit;
    }

    $userId = intval($input['id']);

    // Empêcher l'utilisateur de se supprimer lui-même
    if ($userId == $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte']);
        exit;
    }

    // Vérifier que l'utilisateur existe
    $user = Utilisateur::getById($userId);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        exit;
    }

    // Supprimer l'utilisateur
    $result = Utilisateur::delete($userId);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
    } else {
        throw new Exception('Erreur lors de la suppression');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
