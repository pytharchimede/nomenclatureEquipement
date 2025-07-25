<?php
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();
    
    // 1. Structure de la table equipements
    echo "=== Structure table equipements ===\n";
    $stmt = $pdo->query('DESCRIBE equipements');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($columns as $col) {
        echo $col['Field'] . ' - ' . $col['Type'] . "\n";
    }
    
    // 2. Quelques échantillons de dates
    echo "\n=== Échantillons de dates ===\n";
    $stmt = $pdo->query('SELECT id, repere_equipement, date_creation, date_modification FROM equipements LIMIT 5');
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($samples as $sample) {
        echo "ID: {$sample['id']}, Repère: {$sample['repere_equipement']}, Création: {$sample['date_creation']}, Modif: {$sample['date_modification']}\n";
    }
    
    // 3. Test evolution avec d'autres colonnes
    echo "\n=== Test évolution (avec date_modification) ===\n";
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(COALESCE(date_modification, NOW()), '%Y-%m') as mois,
            COUNT(*) as ajouts_equipements
        FROM equipements 
        WHERE COALESCE(date_modification, NOW()) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mois
        ORDER BY mois
        LIMIT 10
    ");
    $evolution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($evolution as $evo) {
        echo "Mois: {$evo['mois']}, Ajouts: {$evo['ajouts_equipements']}\n";
    }
    
} catch(Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
