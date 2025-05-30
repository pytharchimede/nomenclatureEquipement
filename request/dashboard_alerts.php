<?php
require_once '../model/Database.php';
require_once '../model/Equipement.php';
require_once '../model/Article.php';
require_once '../model/Nomenclature.php';

// Nombre total d'équipements et d'articles
$totalEquipements = count(Equipement::getAll());
$totalArticles = count(Article::getAll());

// Nombre d'équipements ayant au moins une pièce de rechange (vraiment liés)
$equipAvecPiece = Nomenclature::countEquipementsAvecPieceReelle();
// Nombre d'articles liés à au moins un équipement (vraiment liés)
$articlesLies = Nomenclature::countArticlesLiesReels();

$equipSansPiece = $totalEquipements - $equipAvecPiece;
$articlesNonLies = $totalArticles - $articlesLies;

echo json_encode([
    'equipSansPiece' => $equipSansPiece,
    'articlesNonLies' => $articlesNonLies
]);
