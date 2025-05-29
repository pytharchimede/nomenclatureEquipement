<?php
require_once '../model/Database.php';
require_once '../model/Article.php';

header('Content-Type: application/json');

$data = $_POST;

// Vérifie le doublon
if (Article::exists($data['code_article'])) {
    echo json_encode(['success' => false, 'message' => "Code article déjà existant."]);
    exit;
}

// Formatage date
if (!empty($data['date_creation'])) {
    $date = date_create_from_format('Y-m-d', $data['date_creation']);
    $data['date_creation'] = $date ? $date->format('Y-m-d') : null;
}

// Construction du tableau avec toutes les clés attendues
$params = [
    'code_article'           => $data['code_article'] ?? null,
    'designation_article'    => $data['designation_article'] ?? null,
    'type_article'           => $data['type_article'] ?? null,
    'temsup_niv_mdt'         => $data['temsup_niv_mdt'] ?? null,
    'ancien_num_article'     => $data['ancien_num_article'] ?? null,
    'uq_base'                => $data['uq_base'] ?? null,
    'fabricant'              => $data['fabricant'] ?? null,
    'numero_piece_fabricant' => $data['numero_piece_fabricant'] ?? null,
    'groupe_articles'        => $data['groupe_articles'] ?? null,
    'groupe_marche_externe'  => $data['groupe_marche_externe'] ?? null,
    'document'               => $data['document'] ?? null,
    'description'            => $data['description'] ?? null,
    'date_creation'          => $data['date_creation'] ?? null,
    'cree_par'               => $data['cree_par'] ?? null
];

if (Article::add($params)) {
    echo json_encode(['success' => true, 'message' => "Article ajouté avec succès."]);
} else {
    echo json_encode(['success' => false, 'message' => "Erreur lors de l'ajout."]);
}
