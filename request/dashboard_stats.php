<?php
require_once '../model/Database.php';
require_once '../model/Equipement.php';

// Récupère la répartition des équipements par catégorie
$equipements = Equipement::getAll();
$categories = [];
foreach ($equipements as $eq) {
    $cat = $eq['categorie_equipement'] ?? 'Non défini';
    $categories[$cat] = ($categories[$cat] ?? 0) + 1;
}

echo json_encode([
    'labels' => array_keys($categories),
    'values' => array_values($categories)
]);
