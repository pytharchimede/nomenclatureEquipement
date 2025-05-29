<?php
require_once '../model/Database.php';
require_once '../model/Nomenclature.php';
require_once '../model/Equipement.php';
require_once '../model/Article.php';

header('Content-Type: application/json');

$data = $_POST;

// Vérifie le doublon (code_equipement + code_article)
if (Nomenclature::exists($data['code_equipement'], $data['code_article'])) {
    echo json_encode(['success' => false, 'message' => "Cette nomenclature existe déjà (code équipement + code article)."]);
    exit;
}

// Formatage date
if (!empty($data['date_creation'])) {
    $date = date_create_from_format('Y-m-d', $data['date_creation']);
    $data['date_creation'] = $date ? $date->format('Y-m-d') : null;
}

// Vérifie que le code équipement existe, sinon le crée via la classe Equipement
if (!Equipement::exists($data['code_equipement'])) {
    // Création minimale, à adapter si besoin
    Equipement::add(['code_equipement' => $data['code_equipement']]);
}

// Vérifie que le code article existe, sinon le crée via la classe Article
if (!Article::exists($data['code_article'])) {
    Article::add(['code_article' => $data['code_article']]);
}

// Construction du tableau avec toutes les clés attendues
$params = [
    'code_equipement'         => $data['code_equipement'] ?? null,
    'code_article'            => $data['code_article'] ?? null,
    'repere_equipement'       => $data['repere_equipement'] ?? null,
    'designation_equipement'  => $data['designation_equipement'] ?? null,
    'fabricant'               => $data['fabricant'] ?? null,
    'type'                    => $data['type'] ?? null,
    'numero_serie_fabricant'  => $data['numero_serie_fabricant'] ?? null,
    'designation_article'     => $data['designation_article'] ?? null,
    'numero_poste'            => $data['numero_poste'] ?? null,
    'quantite'                => $data['quantite'] ?? null,
    'unite'                   => $data['unite'] ?? null,
    'poste_technique'         => $data['poste_technique'] ?? null,
    'metier'                  => $data['metier'] ?? null,
    'date_creation'           => $data['date_creation'] ?? null,
    'source'                  => $data['source'] ?? null
];

if (Nomenclature::add($params)) {
    echo json_encode(['success' => true, 'message' => "Nomenclature ajoutée avec succès."]);
} else {
    echo json_encode(['success' => false, 'message' => "Erreur lors de l'ajout."]);
}
