<?php

/**
 * API de connexion simple (version de secours)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../model/Database.php';
require_once '../../model/Utilisateur.php';

session_start();

try {
    // Vérification de la méthode
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        exit;
    }

    // Récupération des données
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
        exit;
    }

    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format d\'email invalide']);
        exit;
    }

    // Limitation de taux basique via session
    $sessionKey = 'login_attempts_' . $_SERVER['REMOTE_ADDR'];
    
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = [];
    }

    // Nettoyer les tentatives anciennes (plus de 1 heure)
    $_SESSION[$sessionKey] = array_filter($_SESSION[$sessionKey], function($time) {
        return (time() - $time) < 3600;
    });

    // Vérifier la limite (5 tentatives par heure)
    if (count($_SESSION[$sessionKey]) >= 5) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Trop de tentatives. Réessayez dans 1 heure.']);
        exit;
    }

    // Tentative d'authentification
    $user = Utilisateur::authenticate($email, $input['password']);
    
    if ($user) {
        // Vérifier si l'utilisateur est actif
        if (!$user['actif']) {
            // Ajouter la tentative échouée
            $_SESSION[$sessionKey][] = time();
            
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Compte désactivé']);
            exit;
        }

        // Connexion réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_nom'] = $user['nom_utilisateur'] ?? $user['nom'];
        $_SESSION['groupe_id'] = $user['groupe_id'];
        
        // Nettoyer les tentatives de connexion
        unset($_SESSION[$sessionKey]);
        
        // Mettre à jour la dernière connexion si possible
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                UPDATE utilisateur 
                SET last_login_at = NOW(), last_login_ip = ? 
                WHERE id = ?
            ");
            $stmt->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);
        } catch (Exception $e) {
            // Ignorer l'erreur si la colonne n'existe pas encore
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user['id'],
                'nom' => $user['nom_utilisateur'] ?? $user['nom'],
                'email' => $user['email'],
                'groupe_id' => $user['groupe_id']
            ],
            'redirect' => 'dashboard.php'
        ]);
        
    } else {
        // Ajouter la tentative échouée
        $_SESSION[$sessionKey][] = time();
        
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>
