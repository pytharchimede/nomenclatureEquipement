<?php

/**
 * API de connexion moderne avec sécurité renforcée
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../model/Database.php';
require_once '../../model/Utilisateur.php';

// Limitation des tentatives de connexion
class LoginAttempts
{
    private static $maxAttempts = 5;
    private static $lockoutTime = 900; // 15 minutes

    public static function isBlocked($ip)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as attempts, MAX(created_at) as last_attempt 
            FROM login_attempts 
            WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip, self::$lockoutTime]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['attempts'] >= self::$maxAttempts;
    }

    public static function recordAttempt($ip, $success = false)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO login_attempts (ip_address, success, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$ip, $success ? 1 : 0]);

        // Nettoyage des anciennes tentatives
        $pdo->query("
            DELETE FROM login_attempts 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
    }

    public static function clearAttempts($ip)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            DELETE FROM login_attempts 
            WHERE ip_address = ?
        ");
        $stmt->execute([$ip]);
    }
}

try {
    // Vérification de la méthode
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Récupération des données JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Données JSON invalides');
    }

    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    // Validation des champs
    if (empty($email) || empty($password)) {
        throw new Exception('Email et mot de passe requis');
    }

    // Validation du format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }

    // Vérification des tentatives de connexion
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if (LoginAttempts::isBlocked($clientIP)) {
        throw new Exception('Trop de tentatives de connexion. Réessayez dans 15 minutes.');
    }

    // Tentative d'authentification
    $user = Utilisateur::authenticate($email, $password);

    if (!$user) {
        // Enregistrement de la tentative échouée
        LoginAttempts::recordAttempt($clientIP, false);
        throw new Exception('Email ou mot de passe incorrect');
    }

    // Vérification que le compte est actif
    if (!$user['actif']) {
        throw new Exception('Votre compte a été désactivé. Contactez l\'administrateur.');
    }

    // Génération d'un token de session sécurisé
    $sessionToken = bin2hex(random_bytes(32));

    // Démarrage de la session
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = [
        'id' => $user['id'],
        'nom_utilisateur' => $user['nom_utilisateur'],
        'email' => $user['email'],
        'telephone' => $user['telephone'],
        'actif' => $user['actif'],
        'date_creation' => $user['date_creation']
    ];
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    // Mise à jour de la dernière connexion
    Utilisateur::updateLastLogin($user['id'], $clientIP);

    // Nettoyage des tentatives de connexion
    LoginAttempts::clearAttempts($clientIP);

    // Enregistrement de la tentative réussie
    LoginAttempts::recordAttempt($clientIP, true);

    // Log de sécurité
    error_log("Connexion réussie pour l'utilisateur: {$user['email']} depuis {$clientIP}");

    // Réponse de succès
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom_utilisateur'],
            'email' => $user['email']
        ],
        'redirect' => 'dashboard.php'
    ]);
} catch (Exception $e) {
    // Log des erreurs
    error_log("Erreur de connexion: " . $e->getMessage());

    // Réponse d'erreur
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
