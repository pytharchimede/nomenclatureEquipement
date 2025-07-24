<?php
require_once 'includes/auth.php';
require_once 'model/Database.php';

echo "=== Test Import SPL vers Nomenclatures ===\n\n";

$pdo = Database::getConnection();

// Test des données dans template_spl
echo "1. Données Template SPL :\n";
$templateQuery = "SELECT COUNT(*) as total, 
                         COUNT(DISTINCT code_article) as articles_uniques,
                         COUNT(DISTINCT equipement) as equipements_uniques,
                         COUNT(DISTINCT metier) as metiers_uniques
                  FROM template_spl";
$templateResult = $pdo->query($templateQuery)->fetch(PDO::FETCH_ASSOC);

echo "   📊 Total lignes SPL: " . $templateResult['total'] . "\n";
echo "   📋 Articles uniques: " . $templateResult['articles_uniques'] . "\n";
echo "   🔧 Équipements uniques: " . $templateResult['equipements_uniques'] . "\n";
echo "   🏭 Métiers uniques: " . $templateResult['metiers_uniques'] . "\n\n";

// Test des données dans nomenclatures avec source SPL
echo "2. Données Nomenclatures (source SPL) :\n";
$nomenclatureQuery = "SELECT COUNT(*) as total,
                             COUNT(DISTINCT code_article) as articles_uniques,
                             COUNT(DISTINCT repere_equipement) as reperes_uniques,
                             COUNT(DISTINCT metier) as metiers_uniques
                      FROM nomenclatures 
                      WHERE source = 'SPL'";
$nomenclatureResult = $pdo->query($nomenclatureQuery)->fetch(PDO::FETCH_ASSOC);

echo "   📊 Total lignes Nomenclatures SPL: " . $nomenclatureResult['total'] . "\n";
echo "   📋 Articles uniques: " . $nomenclatureResult['articles_uniques'] . "\n";
echo "   🔧 Repères uniques: " . $nomenclatureResult['reperes_uniques'] . "\n";
echo "   🏭 Métiers uniques: " . $nomenclatureResult['metiers_uniques'] . "\n\n";

// Vérification des doublons potentiels
echo "3. Vérification doublons dans Nomenclatures (même repère + même article + source SPL) :\n";
$doublonsQuery = "SELECT repere_equipement, code_article, COUNT(*) as occurrences
                  FROM nomenclatures 
                  WHERE source = 'SPL'
                  GROUP BY repere_equipement, code_article
                  HAVING COUNT(*) > 1";
$doublonsResult = $pdo->query($doublonsQuery)->fetchAll(PDO::FETCH_ASSOC);

if (empty($doublonsResult)) {
    echo "   ✅ Aucun doublon détecté (repère + article + source SPL)\n";
} else {
    echo "   ⚠️ Doublons détectés :\n";
    foreach ($doublonsResult as $doublon) {
        echo "      - Repère: " . $doublon['repere_equipement'] .
            ", Article: " . $doublon['code_article'] .
            ", Occurrences: " . $doublon['occurrences'] . "\n";
    }
}

echo "\n";

// Exemples de données
echo "4. Exemples de données importées :\n";
if ($nomenclatureResult['total'] > 0) {
    $exemplesQuery = "SELECT code_equipement, code_article, repere_equipement, 
                             designation_article, metier, fabricant, date_creation
                      FROM nomenclatures 
                      WHERE source = 'SPL'
                      ORDER BY date_creation DESC, id DESC
                      LIMIT 5";
    $exemples = $pdo->query($exemplesQuery)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($exemples as $i => $exemple) {
        echo "   Exemple " . ($i + 1) . ":\n";
        echo "      • Code équipement: " . $exemple['code_equipement'] . "\n";
        echo "      • Code article: " . $exemple['code_article'] . "\n";
        echo "      • Repère: " . $exemple['repere_equipement'] . "\n";
        echo "      • Désignation: " . $exemple['designation_article'] . "\n";
        echo "      • Métier: " . $exemple['metier'] . "\n";
        echo "      • Fabricant: " . $exemple['fabricant'] . "\n";
        echo "      • Date création: " . $exemple['date_creation'] . "\n\n";
    }
} else {
    echo "   ℹ️ Aucune donnée SPL trouvée dans nomenclatures.\n";
    echo "      Utilisez l'import Excel pour tester l'intégration.\n\n";
}

// Comparaison cohérence
echo "5. Cohérence entre Template SPL et Nomenclatures :\n";
if ($templateResult['total'] > 0 && $nomenclatureResult['total'] > 0) {
    $ratio = round(($nomenclatureResult['total'] / $templateResult['total']) * 100, 2);
    echo "   📊 Ratio Nomenclatures/Template: " . $ratio . "%\n";
    if ($ratio >= 95) {
        echo "   ✅ Excellente cohérence\n";
    } elseif ($ratio >= 80) {
        echo "   ⚠️ Cohérence acceptable\n";
    } else {
        echo "   ❌ Cohérence faible - vérifier import\n";
    }
} else {
    echo "   ℹ️ Pas de données pour comparaison\n";
}

echo "\n=== Test terminé ===\n";
