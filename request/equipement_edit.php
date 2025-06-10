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
$repere = trim($_POST['repere_equipement'] ?? '');
$ancien_repere = trim($_POST['ancien_repere_equipement'] ?? $repere); // à passer dans le formulaire si on autorise le changement de repère

$data = [
    'code_equipement' => trim($_POST['code_equipement'] ?? ''),
    'designation_equipement' => trim($_POST['designation_equipement'] ?? ''),
    'repere_equipement' => $repere,
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
if (empty($repere) || empty($data['code_equipement'])) {
    echo json_encode(['success' => false, 'message' => 'Repère et code équipement obligatoires.']);
    exit;
}

// Vérification doublon code_equipement (autre que l'équipement en cours)
$existCode = Equipement::getByCode($data['code_equipement']);
if ($existCode && $existCode['repere_equipement'] !== $ancien_repere) {
    echo json_encode(['success' => false, 'message' => 'Un autre équipement possède déjà ce code (doublon détecté).']);
    exit;
}

// Vérification doublon repere_equipement (autre que l'équipement en cours)
if ($repere !== $ancien_repere) {
    $existRepere = Equipement::getByRepere($repere);
    if ($existRepere) {
        echo json_encode(['success' => false, 'message' => 'Un autre équipement possède déjà ce repère (doublon détecté).']);
        exit;
    }
}

// Mise à jour via la classe Equipement
if (Equipement::update($ancien_repere, $data)) {
    echo json_encode(['success' => true, 'message' => 'Équipement modifié avec succès !']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification.']);
}
exit;
