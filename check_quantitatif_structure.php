<?php
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();

    // Vérifier la structure de la table quantitatif
    $stmt = $pdo->query("DESCRIBE quantitatif");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Structure de la table 'quantitatif':\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
