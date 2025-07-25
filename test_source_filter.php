<?php
require_once 'model/Article.php';

echo "<h2>Test de la recherche par source</h2>\n";

try {
    // Test 1: Récupérer les sources disponibles
    $sources = Article::getDistinctSources();
    echo "<h3>Sources disponibles :</h3>\n";
    foreach ($sources as $source) {
        echo "- " . htmlspecialchars($source) . "\n";
    }
    echo "<br>\n";

    // Test 2: Rechercher des articles par source SAP
    echo "<h3>Test de recherche par source SAP (page 1, limite 10) :</h3>\n";
    $resultSAP = Article::getPaginated(1, 10, ['source' => 'SAP']);
    echo "Total trouvé : " . $resultSAP['total'] . " articles\n";
    echo "Articles de cette page :\n";
    foreach ($resultSAP['data'] as $article) {
        echo "- " . htmlspecialchars($article['code_article']) . " : " . htmlspecialchars($article['designation_article']) . "\n";
    }
    echo "<br>\n";

    // Test 3: Rechercher des articles par source RGM
    echo "<h3>Test de recherche par source RGM (page 1, limite 10) :</h3>\n";
    $resultRGM = Article::getPaginated(1, 10, ['source' => 'RGM']);
    echo "Total trouvé : " . $resultRGM['total'] . " articles\n";
    echo "Articles de cette page :\n";
    foreach ($resultRGM['data'] as $article) {
        echo "- " . htmlspecialchars($article['code_article']) . " : " . htmlspecialchars($article['designation_article']) . "\n";
    }
    echo "<br>\n";

    // Test 4: Combiner source et autres filtres
    echo "<h3>Test combiné : Source SAP + recherche 'VALVE' :</h3>\n";
    $resultCombined = Article::getPaginated(1, 5, [
        'source' => 'SAP',
        'search' => 'VALVE'
    ]);
    echo "Total trouvé : " . $resultCombined['total'] . " articles\n";
    echo "Articles trouvés :\n";
    foreach ($resultCombined['data'] as $article) {
        echo "- " . htmlspecialchars($article['code_article']) . " : " . htmlspecialchars($article['designation_article']) . "\n";
    }
} catch (Exception $e) {
    echo "Erreur : " . htmlspecialchars($e->getMessage()) . "\n";
}

echo "<hr>\n";
echo "<p><a href='articles.php'>← Tester dans l'interface articles</a></p>\n";
