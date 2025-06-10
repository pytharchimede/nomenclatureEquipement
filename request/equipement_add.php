<?php
header('Content-Type: application/json');
require_once '../model/Database.php';
require_once '../model/Equipement.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

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

// Vérification doublon et champ obligatoire sur le repère
if (empty($data['repere_equipement'])) {
    echo json_encode(['success' => false, 'message' => 'Le repère équipement est obligatoire.']);
    exit;
}
if (Equipement::getByRepere($data['repere_equipement'])) {
    echo json_encode(['success' => false, 'message' => 'Cet équipement existe déjà (doublon détecté sur le repère).']);
    exit;
}

// Insertion
if (Equipement::create($data)) {
    echo json_encode(['success' => true, 'message' => 'Équipement ajouté avec succès !']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout.']);
}
exit;
