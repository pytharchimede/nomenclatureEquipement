<?php
// Test simple pour vérifier les tables
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();
    
    // Test 1: Vérifier la structure de la table equipements
    $stmt = $pdo->query("DESCRIBE equipements");
    $equipements_structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Test 2: Vérifier la structure de la table nomenclatures
    $stmt = $pdo->query("DESCRIBE nomenclatures");
    $nomenclatures_structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Test 3: Compter les équipements
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM equipements LIMIT 1");
    $equipements_count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test 4: Compter les nomenclatures
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM nomenclatures LIMIT 1");
    $nomenclatures_count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test 5: Sources disponibles
    $stmt = $pdo->query("SELECT DISTINCT source FROM nomenclatures LIMIT 10");
    $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'equipements_structure' => $equipements_structure,
        'nomenclatures_structure' => $nomenclatures_structure,
        'equipements_count' => $equipements_count,
        'nomenclatures_count' => $nomenclatures_count,
        'sources_disponibles' => $sources
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
