<?php
require_once '../model/Database.php';
require_once '../model/Equipement.php';
require_once '../model/Article.php'; // à adapter selon ton modèle

// 1. Équipements sans pièces de rechange
$equipements = Equipement::getAll();
$equipSansPiece = 0;
foreach ($equipements as $eq) {
    // À adapter selon ta structure : ici on suppose une méthode ou champ
    if (empty($eq['pieces_rechange']) || $eq['pieces_rechange'] == 0) {
        $equipSansPiece++;
    }
}

// 2. Articles non liés à des équipements
$articles = Article::getAll(); // à adapter selon ton modèle
$articlesNonLies = 0;
foreach ($articles as $art) {
    if (empty($art['equipement_id'])) {
        $articlesNonLies++;
    }
}

echo json_encode([
    'equipSansPiece' => $equipSansPiece,
    'articlesNonLies' => $articlesNonLies
]);
