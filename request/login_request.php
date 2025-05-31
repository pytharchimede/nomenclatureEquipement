<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../model/Utilisateur.php';

$email = $_POST['email'] ?? '';
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

if (!$email || !$mot_de_passe) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs.']);
    exit;
}

$user = Utilisateur::verifyPassword($email, $mot_de_passe);

if ($user) {
    // On ne stocke pas le mot de passe en session
    unset($user['mot_de_passe']);
    $_SESSION['user'] = $user;
    echo json_encode(['success' => true, 'message' => 'Connexion réussie.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
}
