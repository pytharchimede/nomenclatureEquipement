<?php
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Récupération de la liste des fichiers d'import
    $query = "
        SELECT DISTINCT fichier_import 
        FROM nomenclatures_doublons_import 
        WHERE fichier_import IS NOT NULL 
        ORDER BY fichier_import
    ";

    $stmt = $pdo->query($query);
    $fichiers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'fichiers' => $fichiers
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des fichiers: ' . $e->getMessage()
    ]);
}
