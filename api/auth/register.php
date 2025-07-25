<?php

/**
 * API d'inscription moderne avec validation renforcée
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../model/Database.php';
require_once '../../model/Utilisateur.php';

// Validation des mots de passe
class PasswordValidator
{
    public static function validate($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }

        return $errors;
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

    $nom_utilisateur = trim($input['nom_utilisateur'] ?? '');
    $email = trim($input['email'] ?? '');
    $telephone = trim($input['telephone'] ?? '');
    $mot_de_passe = $input['mot_de_passe'] ?? '';

    // Validation des champs obligatoires
    if (empty($nom_utilisateur)) {
        throw new Exception('Le nom est requis');
    }

    if (empty($email)) {
        throw new Exception('L\'email est requis');
    }

    if (empty($mot_de_passe)) {
        throw new Exception('Le mot de passe est requis');
    }

    // Validation du format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }

    // Validation de la longueur du nom
    if (strlen($nom_utilisateur) < 2 || strlen($nom_utilisateur) > 50) {
        throw new Exception('Le nom doit contenir entre 2 et 50 caractères');
    }

    // Validation du mot de passe
    $passwordErrors = PasswordValidator::validate($mot_de_passe);
    if (!empty($passwordErrors)) {
        throw new Exception(implode('. ', $passwordErrors));
    }

    // Validation du téléphone si fourni
    if (!empty($telephone)) {
        // Nettoyage du numéro de téléphone
        $telephone = preg_replace('/[^\d+]/', '', $telephone);
        if (!preg_match('/^(\+33|0)[1-9](\d{8})$/', $telephone)) {
            throw new Exception('Format de téléphone invalide');
        }
    }

    // Vérification de l'unicité de l'email
    if (Utilisateur::emailExists($email)) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Vérification de l'unicité du nom d'utilisateur
    if (Utilisateur::usernameExists($nom_utilisateur)) {
        throw new Exception('Ce nom d\'utilisateur est déjà pris');
    }

    // Génération d'un token de vérification
    $verificationToken = bin2hex(random_bytes(32));

    // Préparation des données utilisateur
    $userData = [
        'nom_utilisateur' => $nom_utilisateur,
        'email' => $email,
        'telephone' => $telephone,
        'mot_de_passe' => password_hash($mot_de_passe, PASSWORD_DEFAULT),
        'actif' => true, // Actif par défaut, peut être modifié selon les besoins
        'verification_token' => $verificationToken,
        'created_at' => date('Y-m-d H:i:s'),
        'created_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ];

    // Création de l'utilisateur
    $userId = Utilisateur::create($userData);

    if (!$userId) {
        throw new Exception('Erreur lors de la création du compte');
    }

    // Attribution des droits par défaut (utilisateur standard)
    $defaultRights = [
        ['module' => 'dashboard', 'permission' => 'read'],
        ['module' => 'equipements', 'permission' => 'read'],
        ['module' => 'articles', 'permission' => 'read'],
        ['module' => 'nomenclatures', 'permission' => 'read'],
        ['module' => 'profil', 'permission' => 'write']
    ];

    foreach ($defaultRights as $right) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO droits_utilisateur (id_utilisateur, module, permission) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $right['module'], $right['permission']]);
    }

    // Log de création
    error_log("Nouveau compte créé: {$email} depuis " . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));

    // Envoi d'email de bienvenue (optionnel)
    // TODO: Implémenter l'envoi d'email de bienvenue

    // Réponse de succès
    echo json_encode([
        'success' => true,
        'message' => 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.',
        'user_id' => $userId
    ]);
} catch (Exception $e) {
    // Log des erreurs
    error_log("Erreur d'inscription: " . $e->getMessage());

    // Réponse d'erreur
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
