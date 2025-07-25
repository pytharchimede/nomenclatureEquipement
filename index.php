<?php
// Démarrer la session pour vérifier l'authentification
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: accueil.php');
    exit;
}

// Sinon, rediriger vers la page de connexion
header('Location: auth.php');
exit;
