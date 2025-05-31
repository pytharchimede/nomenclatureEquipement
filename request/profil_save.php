<?php

session_start();
require_once '../model/Utilisateur.php';

header('Content-Type: application/json');

$user_logged = $_SESSION['user'] ?? null;
if (!$user_logged) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$id = $user_logged['id'];
$data = [
    'nom' => $_POST['nom'] ?? '',
    'telephone' => $_POST['telephone'] ?? '',
    'empreinte_numerique' => $_POST['empreinte_numerique'] ?? '',
    'photo_profil' => null, // à gérer ci-dessous
];

// Gestion du mot de passe
if (!empty($_POST['mot_de_passe'])) {
    $data['mot_de_passe'] = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
} else {
    // On ne change pas le mot de passe
    $user = Utilisateur::getById($id);
    $data['mot_de_passe'] = $user['mot_de_passe'];
}

// Gestion de la photo de profil
if (!empty($_FILES['photo_profil']['tmp_name'])) {
    $ext = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
    $filename = 'uploads/profil_' . $id . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['photo_profil']['tmp_name'], '../' . $filename);
    $data['photo_profil'] = $filename;
} else {
    $user = Utilisateur::getById($id);
    $data['photo_profil'] = $user['photo_profil'];
}

// Compléter les autres champs nécessaires selon ta classe
$data['email'] = $user['email'];
$data['groupe_id'] = $user['groupe_id'];
$data['actif'] = $user['actif'];

$ok = Utilisateur::update($id, $data);

echo json_encode(['success' => $ok]);
