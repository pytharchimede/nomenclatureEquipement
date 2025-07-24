<?php
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();

    // Vérifier la structure de la table familles
    $stmt = $pdo->query("DESCRIBE familles");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Structure de la table 'familles':\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

    echo "\n\nExemple de données:\n";
    $stmt = $pdo->query("SELECT * FROM familles LIMIT 3");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        print_r($data[0]);
    } else {
        echo "Aucune donnée trouvée\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
