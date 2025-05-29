<?php
header('Content-Type: application/json');
require_once '../model/Database.php';
require_once '../model/Equipement.php';

// Vérifie la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Récupère les données du formulaire
$id = $_POST['id'] ?? '';
$data = [
    'code_equipement' => trim($_POST['code_equipement'] ?? ''),
    'designation_equipement' => trim($_POST['designation_equipement'] ?? ''),
    'repere_equipement' => trim($_POST['repere_equipement'] ?? ''),
    'fabricant' => trim($_POST['fabricant'] ?? ''),
    'type_objet' => trim($_POST['type_objet'] ?? ''),
    'designation_type' => trim($_POST['designation_type'] ?? ''),
    'numero_serie_fabricant' => trim($_POST['numero_serie_fabricant'] ?? ''),
    'numero_piece_fabricant' => trim($_POST['numero_piece_fabricant'] ?? ''),
    'poste_technique' => trim($_POST['poste_technique'] ?? ''),
    'designation_poste_technique' => trim($_POST['designation_poste_technique'] ?? ''),
    'poste_travail_principal' => trim($_POST['poste_travail_principal'] ?? ''),
    'categorie_equipement' => trim($_POST['categorie_equipement'] ?? ''),
    'centre_de_couts' => trim($_POST['centre_de_couts'] ?? ''),
    'date_creation' => trim($_POST['date_creation'] ?? '')
];

// Vérification des champs obligatoires
if (empty($id) || empty($data['code_equipement'])) {
    echo json_encode(['success' => false, 'message' => 'ID et code équipement obligatoires.']);
    exit;
}

// Vérification doublon (autre que l'équipement en cours)
$exist = Equipement::getByCode($data['code_equipement']);
if ($exist && $exist['id'] != $id) {
    echo json_encode(['success' => false, 'message' => 'Un autre équipement possède déjà ce code (doublon détecté).']);
    exit;
}

// Mise à jour via la classe Equipement
if (Equipement::update($id, $data)) {
    echo json_encode(['success' => true, 'message' => 'Équipement modifié avec succès !']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification.']);
}
exit;
