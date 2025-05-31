<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../model/Utilisateur.php';
header('Content-Type: application/json');

try {
    $data = [
        'nom' => $_POST['nom'] ?? '',
        'email' => $_POST['email'] ?? '',
        'groupe_id' => $_POST['groupe_id'] ?? 1,
        'actif' => isset($_POST['actif']) ? 1 : 0
    ];

    if (!empty($_POST['mot_de_passe'])) {
        $data['mot_de_passe'] = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    }

    if (!empty($_POST['id'])) {
        // Edition
        if (empty($_POST['mot_de_passe'])) {
            $user = Utilisateur::getById($_POST['id']);
            if ($user) {
                $data['mot_de_passe'] = $user['mot_de_passe'];
            }
        }
        // Ajoute les champs manquants pour l'update
        $user = Utilisateur::getById($_POST['id']);
        $data['photo_profil'] = $user['photo_profil'] ?? null;
        $data['telephone'] = $user['telephone'] ?? null;
        $data['empreinte_numerique'] = $user['empreinte_numerique'] ?? null;

        $ok = Utilisateur::update($_POST['id'], $data);
    } else {
        // Ajout
        if (empty($data['mot_de_passe'])) {
            echo json_encode(['success' => false, 'message' => 'Mot de passe requis']);
            exit;
        }
        $ok = Utilisateur::create($data);
    }
    echo json_encode(['success' => $ok]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
