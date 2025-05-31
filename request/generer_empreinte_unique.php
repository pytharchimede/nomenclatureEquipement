<?php

require_once '../model/Utilisateur.php';
header('Content-Type: application/json');
$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';
$empreinte = Utilisateur::genererEmpreinteUnique($nom, $email, 4);
echo json_encode(['empreinte' => $empreinte]);
