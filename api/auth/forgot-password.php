<?php

/**
 * API de récupération de mot de passe
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../model/Database.php';
require_once '../../model/Utilisateur.php';
require_once '../../model/EmailManager.php';

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

    // Validation des champs
    if (empty($email)) {
        throw new Exception('Email requis');
    }

    // Validation du format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }

    // Vérification que l'utilisateur existe
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id, nom_utilisateur, email FROM utilisateurs WHERE email = ? AND actif = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Par sécurité, on ne révèle pas si l'email existe ou non
        echo json_encode([
            'success' => true,
            'message' => 'Si cet email existe dans notre système, un lien de récupération a été envoyé.'
        ]);
        exit;
    }

    // Génération d'un token de récupération
    $resetToken = bin2hex(random_bytes(32));
    $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expire dans 1 heure

    // Enregistrement du token en base
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, token, expires_at, created_at) 
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        token = VALUES(token), 
        expires_at = VALUES(expires_at), 
        created_at = NOW()
    ");
    $stmt->execute([$user['id'], $resetToken, $resetExpiry]);

    // Préparation du lien de récupération
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/nomenclatureequipement/reset-password.php?token=" . $resetToken;

    // Contenu de l'email
    $emailSubject = "Récupération de votre mot de passe - EquiNomTech";
    $emailBody = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #667eea;'>EquiNomTech</h1>
                <p style='color: #666;'>Gestion Nomenclature Équipements</p>
            </div>
            
            <h2 style='color: #333;'>Récupération de mot de passe</h2>
            
            <p>Bonjour <strong>{$user['nom_utilisateur']}</strong>,</p>
            
            <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte EquiNomTech.</p>
            
            <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' 
                   style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                          color: white; 
                          padding: 15px 30px; 
                          text-decoration: none; 
                          border-radius: 8px; 
                          display: inline-block;
                          font-weight: bold;'>
                    Réinitialiser mon mot de passe
                </a>
            </div>
            
            <p style='color: #666; font-size: 14px;'>
                Ce lien est valable pendant <strong>1 heure</strong> seulement.
            </p>
            
            <p style='color: #666; font-size: 14px;'>
                Si vous n'avez pas demandé cette réinitialisation, ignorez cet email. 
                Votre mot de passe restera inchangé.
            </p>
            
            <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
            
            <p style='color: #999; font-size: 12px; text-align: center;'>
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.<br>
                © 2025 EquiNomTech - Tous droits réservés
            </p>
        </div>
    </body>
    </html>
    ";

    // Envoi de l'email
    try {
        if (class_exists('EmailManager')) {
            EmailManager::sendEmail($email, $emailSubject, $emailBody);
        } else {
            // Fallback avec mail() PHP basique
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: noreply@equinomtech.com" . "\r\n";

            mail($email, $emailSubject, $emailBody, $headers);
        }

        // Log de sécurité
        error_log("Demande de récupération de mot de passe pour: {$email}");

        echo json_encode([
            'success' => true,
            'message' => 'Un lien de récupération a été envoyé à votre adresse email.'
        ]);
    } catch (Exception $e) {
        // Log de l'erreur d'envoi
        error_log("Erreur envoi email récupération: " . $e->getMessage());

        // On ne révèle pas l'erreur d'envoi pour des raisons de sécurité
        echo json_encode([
            'success' => true,
            'message' => 'Si cet email existe dans notre système, un lien de récupération a été envoyé.'
        ]);
    }
} catch (Exception $e) {
    // Log des erreurs
    error_log("Erreur récupération mot de passe: " . $e->getMessage());

    // Réponse d'erreur
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
