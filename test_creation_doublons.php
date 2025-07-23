<?php
require_once 'model/Database.php';

echo "🧪 Création de doublons de test pour tester l'export\n";
echo "===============================================\n";

try {
    $pdo = Database::getConnection();

    // Récupérer quelques nomenclatures existantes
    $stmt = $pdo->query("SELECT * FROM nomenclatures ORDER BY id LIMIT 3");
    $nomenclatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($nomenclatures)) {
        echo "❌ Aucune nomenclature trouvée pour créer des doublons\n";
        exit;
    }

    echo "📋 Création de doublons basés sur " . count($nomenclatures) . " nomenclatures existantes...\n\n";

    $dublonsCreated = 0;

    foreach ($nomenclatures as $nom) {
        // Créer 2-3 doublons pour chaque nomenclature
        $nbDoublons = rand(2, 3);

        for ($i = 1; $i <= $nbDoublons; $i++) {
            $insertQuery = "
                INSERT INTO nomenclatures (
                    code_equipement, code_article, repere_equipement, 
                    designation_equipement, fabricant, designation_article,
                    quantite, unite, source, date_creation
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";

            $stmt = $pdo->prepare($insertQuery);
            $success = $stmt->execute([
                $nom['code_equipement'],
                $nom['code_article'],
                $nom['repere_equipement'],
                $nom['designation_equipement'] . " (Doublon Test $i)",
                $nom['fabricant'],
                $nom['designation_article'] . " (Doublon Test $i)",
                $nom['quantite'],
                $nom['unite'],
                "TEST_DOUBLONS"
            ]);

            if ($success) {
                $dublonsCreated++;
                echo "✅ Doublon créé: {$nom['repere_equipement']} | {$nom['code_article']} (copie $i)\n";
            }
        }
    }

    echo "\n🎯 Résumé:\n";
    echo "- Doublons créés: $dublonsCreated\n";
    echo "- Source: TEST_DOUBLONS\n";

    // Vérification des doublons créés
    $verificationQuery = "
        SELECT 
            repere_equipement, code_article, COUNT(*) as count
        FROM nomenclatures 
        WHERE repere_equipement IS NOT NULL 
        AND repere_equipement != ''
        AND code_article IS NOT NULL 
        AND code_article != ''
        GROUP BY repere_equipement, code_article 
        HAVING count > 1
        ORDER BY count DESC
    ";

    $stmt = $pdo->query($verificationQuery);
    $doublons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n🔍 Vérification - Groupes de doublons détectés: " . count($doublons) . "\n";
    foreach ($doublons as $doublon) {
        echo "  - {$doublon['repere_equipement']} | {$doublon['code_article']} : {$doublon['count']} occurences\n";
    }

    echo "\n✅ Test terminé ! Vous pouvez maintenant tester l'export des doublons.\n";
    echo "💡 Pour nettoyer, exécutez: DELETE FROM nomenclatures WHERE source = 'TEST_DOUBLONS'\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
