<?php
require_once 'model/Database.php';

try {
    echo "<h1>Mise à jour de la base de données pour l'authentification moderne</h1>";

    $pdo = Database::getConnection();

    // Lire le fichier SQL
    $sqlFile = __DIR__ . '/Database/setup_complete_auth_system.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Fichier SQL non trouvé: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    // Séparer les requêtes par point-virgule
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    $successCount = 0;
    $errorCount = 0;

    echo "<h2>Exécution des requêtes SQL...</h2>";
    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace;'>";

    foreach ($queries as $query) {
        $query = trim($query);

        // Ignorer les commentaires et les lignes vides
        if (
            empty($query) ||
            strpos($query, '--') === 0 ||
            strpos($query, '/*') === 0 ||
            strtoupper(substr($query, 0, 3)) === 'USE' ||
            strpos($query, 'CREATE EVENT') !== false
        ) {
            continue;
        }

        try {
            echo "<p>Exécution: " . htmlspecialchars(substr($query, 0, 100)) . "...</p>";
            $pdo->exec($query);
            echo "<p style='color: green;'>✓ Succès</p>";
            $successCount++;
        } catch (PDOException $e) {
            // Ignorer les erreurs "column already exists" ou "table already exists"
            if (
                strpos($e->getMessage(), 'Duplicate column name') !== false ||
                strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), 'already exists') !== false
            ) {
                echo "<p style='color: orange;'>⚠ " . htmlspecialchars($e->getMessage()) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
                $errorCount++;
            }
        }
    }

    echo "</div>";

    echo "<h2>Résumé</h2>";
    echo "<p>Requêtes exécutées avec succès: <strong>$successCount</strong></p>";
    echo "<p>Erreurs: <strong>$errorCount</strong></p>";

    // Vérification des tables créées
    echo "<h2>Vérification des tables</h2>";

    $tables = ['utilisateur', 'login_attempts', 'password_reset_tokens', 'email_verification_tokens'];

    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✓ Table '$table' existe</p>";

                // Afficher la structure
                $stmt = $pdo->query("DESCRIBE $table");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "<p style='margin-left: 20px; font-size: 0.9em;'>Colonnes: " . implode(', ', $columns) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Table '$table' n'existe pas</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Erreur vérification table '$table': " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Test final
    echo "<h2>Test de connexion</h2>";

    try {
        require_once 'model/Utilisateur.php';
        $users = Utilisateur::getAll();
        echo "<p style='color: green;'>✓ Récupération des utilisateurs réussie (" . count($users) . " utilisateurs)</p>";

        // Afficher les utilisateurs
        if (!empty($users)) {
            echo "<h3>Utilisateurs existants:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Actif</th></tr>";

            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['nom_utilisateur'] ?? $user['nom']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . ($user['actif'] ? 'Oui' : 'Non') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Erreur test utilisateurs: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    echo "<h2>✅ Mise à jour terminée!</h2>";
    echo "<p><a href='auth.php'>Tester la page de connexion</a></p>";
    echo "<p><a href='test_database_connection.php'>Tester la connexion DB</a></p>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erreur fatale</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
