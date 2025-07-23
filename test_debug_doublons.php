<?php
require_once 'model/Database.php';

echo "🔍 Test de détection des doublons dans la base de données\n";

try {
    $pdo = Database::getConnection();

    // Test 1: Compter le total de nomenclatures
    $stmt = $pdo->query('SELECT COUNT(*) FROM nomenclatures');
    $total = $stmt->fetchColumn();
    echo "Total nomenclatures: " . $total . "\n";

    // Test 2: Chercher des doublons basés sur repere_equipement + code_article
    $query = "
        SELECT 
            repere_equipement,
            code_article,
            COUNT(*) as count
        FROM nomenclatures 
        WHERE repere_equipement IS NOT NULL 
        AND repere_equipement != ''
        AND code_article IS NOT NULL 
        AND code_article != ''
        GROUP BY repere_equipement, code_article 
        HAVING count > 1
        ORDER BY count DESC
        LIMIT 10
    ";

    $stmt = $pdo->query($query);
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Groupes de doublons trouvés: " . count($duplicates) . "\n";

    foreach ($duplicates as $dup) {
        echo "- " . $dup['repere_equipement'] . " | " . $dup['code_article'] . " (" . $dup['count'] . " doublons)\n";
    }

    // Test 3: Chercher des doublons basés uniquement sur repere_equipement
    $query2 = "
        SELECT 
            repere_equipement,
            COUNT(*) as count
        FROM nomenclatures 
        WHERE repere_equipement IS NOT NULL 
        AND repere_equipement != ''
        GROUP BY repere_equipement
        HAVING count > 1
        ORDER BY count DESC
        LIMIT 5
    ";

    $stmt2 = $pdo->query($query2);
    $duplicates2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo "\nDoublons par repère équipement seulement:\n";
    foreach ($duplicates2 as $dup) {
        echo "- " . $dup['repere_equipement'] . " (" . $dup['count'] . " occurrences)\n";
    }

    // Test 4: Vérifier la structure de quelques enregistrements
    $stmt3 = $pdo->query('SELECT repere_equipement, code_article, designation_equipement FROM nomenclatures LIMIT 5');
    $sample = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    echo "\nÉchantillon d'enregistrements:\n";
    foreach ($sample as $row) {
        echo "- repere: '" . ($row['repere_equipement'] ?? 'NULL') . "' | code: '" . ($row['code_article'] ?? 'NULL') . "' | designation: '" . ($row['designation_equipement'] ?? 'NULL') . "'\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
