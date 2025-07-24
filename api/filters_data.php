<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getConnection();

    // Récupérer les familles d'équipements
    $stmt = $db->query("
        SELECT DISTINCT famille as nom, famille as id 
        FROM equipements 
        WHERE famille IS NOT NULL AND famille != '' 
        ORDER BY famille
    ");
    $familles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les fabricants
    $stmt = $db->query("
        SELECT DISTINCT fabricant 
        FROM equipements 
        WHERE fabricant IS NOT NULL AND fabricant != '' 
        UNION 
        SELECT DISTINCT fabricant 
        FROM articles 
        WHERE fabricant IS NOT NULL AND fabricant != ''
        ORDER BY fabricant
    ");
    $fabricants = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'familles' => $familles,
        'fabricants' => $fabricants
    ]);
} catch (Exception $e) {
    error_log("Erreur dans filters_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des données de filtres'
    ]);
}
