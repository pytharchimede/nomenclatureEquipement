<?php
require_once '../model/Utilisateur.php';
require_once '../includes/db.php';
session_start();

header('Content-Type: application/json');

// Limitation de taux
$ipAddress = $_SERVER['REMOTE_ADDR'];
$sessionKey = 'user_save_attempts_' . $ipAddress;

if (!isset($_SESSION[$sessionKey])) {
    $_SESSION[$sessionKey] = [];
}

// Nettoyer les tentatives anciennes (plus de 1 heure)
$_SESSION[$sessionKey] = array_filter($_SESSION[$sessionKey], function ($time) {
    return (time() - $time) < 3600;
});

// Vérifier la limite (10 tentatives par heure)
if (count($_SESSION[$sessionKey]) >= 10) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Trop de tentatives. Réessayez plus tard.']);
    exit;
}

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

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
        exit;
    }

    // Validation des champs obligatoires
    if (empty($input['nom_utilisateur']) || empty($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nom d\'utilisateur et email sont obligatoires']);
        exit;
    }

    // Validation de l'email
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format d\'email invalide']);
        exit;
    }

    // Ajouter la tentative
    $_SESSION[$sessionKey][] = time();

    $userId = isset($input['id']) ? intval($input['id']) : null;

    // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel en édition)
    $emailCheck = "SELECT id FROM utilisateurs WHERE email = ?";
    if ($userId) {
        $emailCheck .= " AND id != ?";
        $stmt = $pdo->prepare($emailCheck);
        $stmt->execute([$input['email'], $userId]);
    } else {
        $stmt = $pdo->prepare($emailCheck);
        $stmt->execute([$input['email']]);
    }

    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        exit;
    }

    // Vérifier si le nom d'utilisateur existe déjà
    $usernameCheck = "SELECT id FROM utilisateurs WHERE nom_utilisateur = ?";
    if ($userId) {
        $usernameCheck .= " AND id != ?";
        $stmt = $pdo->prepare($usernameCheck);
        $stmt->execute([$input['nom_utilisateur'], $userId]);
    } else {
        $stmt = $pdo->prepare($usernameCheck);
        $stmt->execute([$input['nom_utilisateur']]);
    }

    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà utilisé']);
        exit;
    }

    if ($userId) {
        // Mise à jour d'un utilisateur existant
        $updateFields = [
            'nom_utilisateur = ?',
            'email = ?',
            'telephone = ?',
            'actif = ?'
        ];

        $params = [
            $input['nom_utilisateur'],
            $input['email'],
            $input['telephone'] ?? null,
            isset($input['actif']) ? 1 : 0
        ];

        // Si un nouveau mot de passe est fourni
        if (!empty($input['mot_de_passe'])) {
            if (strlen($input['mot_de_passe']) < 8) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères']);
                exit;
            }
            $updateFields[] = 'mot_de_passe = ?';
            $params[] = password_hash($input['mot_de_passe'], PASSWORD_DEFAULT);
        }

        $params[] = $userId;

        $sql = "UPDATE utilisateurs SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
        } else {
            throw new Exception('Erreur lors de la mise à jour');
        }
    } else {
        // Création d'un nouvel utilisateur
        if (empty($input['mot_de_passe'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mot de passe obligatoire pour un nouvel utilisateur']);
            exit;
        }

        if (strlen($input['mot_de_passe']) < 8) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères']);
            exit;
        }

        $hashedPassword = password_hash($input['mot_de_passe'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO utilisateurs (nom_utilisateur, email, telephone, mot_de_passe, actif, date_creation) 
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $input['nom_utilisateur'],
            $input['email'],
            $input['telephone'] ?? null,
            $hashedPassword,
            isset($input['actif']) ? 1 : 0
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur créé avec succès']);
        } else {
            throw new Exception('Erreur lors de la création');
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
