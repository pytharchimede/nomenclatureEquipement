<?php
// Script de test simple pour vérifier l'export
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST EXPORT ARTICLES ===\n";

try {
    require_once __DIR__ . '/model/Database.php';
    echo "✓ Database.php chargé\n";

    require_once __DIR__ . '/model/Article.php';
    echo "✓ Article.php chargé\n";

    // Test de récupération des articles
    $articles = Article::getAll();
    echo "✓ Articles récupérés: " . count($articles) . " articles\n";

    if (count($articles) > 0) {
        echo "✓ Premier article: " . json_encode($articles[0]) . "\n";
    }

    // Test des en-têtes pour Excel
    echo "\n=== Test en-têtes Excel ===\n";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="test.xlsx"');
    echo "✓ En-têtes envoyés\n";
} catch (Exception $e) {
    echo "✗ ERREUR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
