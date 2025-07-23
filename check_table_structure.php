<?php
require_once 'model/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    echo "Colonnes de la table nomenclatures_doublons_import:\n";
    echo "=====================================================\n";

    $columns = $conn->query('SHOW COLUMNS FROM nomenclatures_doublons_import');
    while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
