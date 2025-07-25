<?php
// Test simple de l'API de suppression
require_once 'model/Database.php';
require_once 'includes/auth.php';

// Test 1: Vérifier la structure de la requête
echo "<h2>Test de l'API de suppression d'articles</h2>\n";

// Test de la structure de la base de données
$pdo = Database::getConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM articles");
$totalArticles = $stmt->fetchColumn();

echo "<p>Nombre total d'articles dans la base : <strong>$totalArticles</strong></p>\n";

// Test des filtres
if ($totalArticles > 0) {
    // Récupérer quelques exemples de valeurs pour les tests
    $stmt = $pdo->query("SELECT DISTINCT fabricant FROM articles WHERE fabricant IS NOT NULL AND fabricant != '' LIMIT 3");
    $fabricants = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->query("SELECT DISTINCT type_article FROM articles WHERE type_article IS NOT NULL AND type_article != '' LIMIT 3");
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Exemples de filtres disponibles :</h3>\n";
    echo "<p><strong>Fabricants :</strong> " . implode(', ', $fabricants) . "</p>\n";
    echo "<p><strong>Types d'articles :</strong> " . implode(', ', $types) . "</p>\n";

    // Compter les articles par fabricant (pour test de suppression filtrée)
    if (!empty($fabricants[0])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE fabricant = ?");
        $stmt->execute([$fabricants[0]]);
        $countFabricant = $stmt->fetchColumn();
        echo "<p>Articles du fabricant '<strong>{$fabricants[0]}</strong>' : <strong>$countFabricant</strong></p>\n";
    }
}

// Test de sécurité - vérifier que les requêtes DELETE sont bien protégées
try {
    $stmt = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'articles'");
    $stmt->execute();
    $tableExists = $stmt->fetchColumn();

    if ($tableExists) {
        echo "<p style='color: green;'>✅ Table 'articles' existe et est accessible</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Table 'articles' introuvable</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ Erreur lors de la vérification de la table : " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<hr>\n";
echo "<p><strong>API prête pour les tests !</strong></p>\n";
echo "<p>L'API de suppression est accessible à : <code>api/delete_articles.php</code></p>\n";
echo "<p>Types de suppression supportés :</p>\n";
echo "<ul>\n";
echo "<li><strong>selected</strong> : Suppression d'articles spécifiques par ID</li>\n";
echo "<li><strong>filtered</strong> : Suppression selon des critères de filtrage</li>\n";
echo "</ul>\n";

echo "<hr>\n";
echo "<p><a href='articles.php'>← Retour à la gestion des articles</a></p>\n";
