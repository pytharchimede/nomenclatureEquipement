<?php
require_once 'model/Database.php';

try {
    $pdo = Database::getConnection();
    echo "✅ Connexion réussie\n";

    // Test des équipements non SAP
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT e.repere_equipement) as total
        FROM equipements e
        WHERE e.repere_equipement NOT IN (
            SELECT DISTINCT n.repere_equipement 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.repere_equipement IS NOT NULL
        )
    ");
    $equipementsNonSAP = $stmt->fetchColumn();
    echo "🔴 Équipements non SAP: $equipementsNonSAP\n";

    // Test des articles non SAP
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT a.code_article) as total
        FROM articles a
        WHERE a.code_article NOT IN (
            SELECT DISTINCT n.code_article 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.code_article IS NOT NULL
        )
    ");
    $articlesNonSAP = $stmt->fetchColumn();
    echo "🔴 Articles non SAP: $articlesNonSAP\n";

    // Échantillon d'équipements non SAP
    $stmt = $pdo->query("
        SELECT e.repere_equipement, e.designation_equipement
        FROM equipements e
        WHERE e.repere_equipement NOT IN (
            SELECT DISTINCT n.repere_equipement 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.repere_equipement IS NOT NULL
        )
        LIMIT 5
    ");
    $echantillonEquip = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📋 Échantillon équipements non SAP:\n";
    foreach ($echantillonEquip as $equip) {
        echo "  - " . $equip['repere_equipement'] . ": " . $equip['designation_equipement'] . "\n";
    }

    // Échantillon d'articles non SAP
    $stmt = $pdo->query("
        SELECT a.code_article, a.designation_article
        FROM articles a
        WHERE a.code_article NOT IN (
            SELECT DISTINCT n.code_article 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.code_article IS NOT NULL
        )
        LIMIT 5
    ");
    $echantillonArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📋 Échantillon articles non SAP:\n";
    foreach ($echantillonArticles as $article) {
        echo "  - " . $article['code_article'] . ": " . $article['designation_article'] . "\n";
    }

    echo "✅ Tests non SAP réussis\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
