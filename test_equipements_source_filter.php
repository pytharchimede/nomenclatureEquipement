<?php

/**
 * Test du filtre par source pour les équipements
 */
require_once 'model/Database.php';
require_once 'model/Equipement.php';

echo "<h2>Test du filtre par source pour les équipements</h2>\n";

try {
    // Test 1: Récupérer les sources disponibles
    $sources = Equipement::getDistinctSources();
    echo "<h3>Sources disponibles :</h3>\n";
    foreach ($sources as $source) {
        echo "<p>- <strong>$source</strong></p>\n";
    }

    if (empty($sources)) {
        echo "<p style='color: orange;'>⚠️ Aucune source trouvée dans les nomenclatures.</p>\n";
        exit;
    }

    // Test 2: Compter les équipements par source
    echo "<h3>Nombre d'équipements par source :</h3>\n";
    foreach ($sources as $source) {
        $result = Equipement::getPaginated(1, 1000, ['source' => $source]);
        echo "<p>- <strong>$source</strong> : {$result['total']} équipements</p>\n";
    }

    // Test 3: Tester une requête spécifique avec la première source
    if (!empty($sources)) {
        $firstSource = $sources[0];
        echo "<h3>Test détaillé avec la source '$firstSource' :</h3>\n";

        $result = Equipement::getPaginated(1, 5, ['source' => $firstSource]);
        echo "<p>Total trouvé : <strong>{$result['total']}</strong> équipements</p>\n";
        echo "<p>Échantillon (5 premiers) :</p>\n";
        echo "<ul>\n";
        foreach ($result['data'] as $eq) {
            echo "<li><strong>{$eq['repere_equipement']}</strong> - {$eq['code_equipement']} - " . htmlspecialchars($eq['designation_equipement'] ?? '') . "</li>\n";
        }
        echo "</ul>\n";
    }

    // Test 4: Tester la combinaison de filtres
    echo "<h3>Test combiné (source + recherche) :</h3>\n";
    if (!empty($sources)) {
        $firstSource = $sources[0];
        $result = Equipement::getPaginated(1, 5, [
            'source' => $firstSource,
            'search' => 'VANNE'
        ]);
        echo "<p>Recherche 'VANNE' dans source '$firstSource' : <strong>{$result['total']}</strong> résultats</p>\n";
    }

    echo "<hr>\n";
    echo "<p style='color: green;'>✅ Tous les tests sont passés avec succès !</p>\n";
    echo "<p><a href='equipements.php'>Tester l'interface des équipements →</a></p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur lors du test : " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Trace de l'erreur :</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}
