<?php
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();

    echo "=== TEST FAMILLES D'ÉQUIPEMENTS ===\n";

    // Test 1: Vérifier si les équipements ont des familles
    echo "\n1. Équipements avec familles:\n";
    $stmt = $pdo->query("
        SELECT famille, COUNT(*) as nb 
        FROM equipements 
        WHERE famille IS NOT NULL AND famille != '' 
        GROUP BY famille 
        ORDER BY nb DESC 
        LIMIT 10
    ");
    $familles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($familles)) {
        echo "❌ PROBLÈME: Aucun équipement n'a de famille définie!\n";

        // Vérifier la structure de la table
        echo "\n2. Colonnes de la table equipements:\n";
        $stmt = $pdo->query("DESCRIBE equipements");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "- {$col['Field']} ({$col['Type']})\n";
        }

        // Échantillons d'équipements
        echo "\n3. Échantillons d'équipements:\n";
        $stmt = $pdo->query("SELECT id, repere_equipement, famille FROM equipements LIMIT 10");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($samples as $eq) {
            echo "- ID:{$eq['id']}, Repère:{$eq['repere_equipement']}, Famille:{$eq['famille']}\n";
        }
    } else {
        echo "✅ Familles trouvées:\n";
        foreach ($familles as $fam) {
            echo "- {$fam['famille']}: {$fam['nb']} équipements\n";
        }

        // Test de la requête complète comme dans l'API
        echo "\n4. Test requête API complète:\n";
        $stmt = $pdo->query("
            SELECT 
                COALESCE(e.famille, 'Non définie') as famille,
                COUNT(DISTINCT e.id) as nb_equipements,
                COUNT(DISTINCT n.code_article) as nb_articles_differents
            FROM equipements e
            LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement
            WHERE e.famille IS NOT NULL AND e.famille != ''
            GROUP BY e.famille
            HAVING nb_equipements >= 5
            ORDER BY nb_equipements DESC
            LIMIT 8
        ");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            echo "❌ PROBLÈME: Aucune famille avec >= 5 équipements\n";

            // Test sans limite
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(e.famille, 'Non définie') as famille,
                    COUNT(DISTINCT e.id) as nb_equipements,
                    COUNT(DISTINCT n.code_article) as nb_articles_differents
                FROM equipements e
                LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement
                WHERE e.famille IS NOT NULL AND e.famille != ''
                GROUP BY e.famille
                ORDER BY nb_equipements DESC
            ");
            $resultAll = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Toutes les familles trouvées:\n";
            foreach ($resultAll as $fam) {
                echo "- {$fam['famille']}: {$fam['nb_equipements']} équip., {$fam['nb_articles_differents']} articles\n";
            }
        } else {
            echo "✅ Données pour le graphique:\n";
            foreach ($result as $fam) {
                echo "- {$fam['famille']}: {$fam['nb_equipements']} équip., {$fam['nb_articles_differents']} articles\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}
