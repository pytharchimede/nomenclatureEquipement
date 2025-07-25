<?php
require_once 'includes/db.php';
require_once 'model/Utilisateur.php';

try {
    echo "<h1>Test de connexion et des utilisateurs</h1>";

    // Test de connexion à la base
    echo "<h2>1. Test de connexion à la base de données</h2>";
    if ($pdo) {
        echo "✅ Connexion réussie à la base de données<br>";
    } else {
        echo "❌ Échec de connexion à la base de données<br>";
        exit;
    }

    // Test de récupération des utilisateurs
    echo "<h2>2. Test de récupération des utilisateurs</h2>";
    $users = Utilisateur::getAll();
    echo "Nombre d'utilisateurs trouvés: " . count($users) . "<br>";

    if (count($users) > 0) {
        echo "<h3>Liste des utilisateurs:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Nom</th>";
        echo "<th style='padding: 8px;'>Nom utilisateur</th>";
        echo "<th style='padding: 8px;'>Email</th>";
        echo "<th style='padding: 8px;'>Actif</th>";
        echo "<th style='padding: 8px;'>Groupe ID</th>";
        echo "<th style='padding: 8px;'>Date création</th>";
        echo "</tr>";

        foreach ($users as $user) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['nom'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['nom_utilisateur'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='padding: 8px;'>" . ($user['actif'] ? '✅' : '❌') . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['groupe_id'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['date_creation']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Test des groupes
    echo "<h2>3. Test de récupération des groupes</h2>";
    $stmt = $pdo->query("SELECT * FROM groupe_utilisateur ORDER BY nom");
    $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Nombre de groupes trouvés: " . count($groupes) . "<br>";

    if (count($groupes) > 0) {
        echo "<h3>Liste des groupes:</h3>";
        echo "<ul>";
        foreach ($groupes as $groupe) {
            echo "<li>" . htmlspecialchars($groupe['nom']) . " - " . htmlspecialchars($groupe['description']) . "</li>";
        }
        echo "</ul>";
    }

    // Test d'authentification
    echo "<h2>4. Test d'authentification</h2>";
    if (count($users) > 0) {
        $firstUser = $users[0];
        echo "Test d'authentification avec l'utilisateur: " . htmlspecialchars($firstUser['email']) . "<br>";
        echo "Note: Pour tester l'authentification, utilisez la page auth.php<br>";
    }

    echo "<h2>✅ Tous les tests de base sont passés avec succès!</h2>";
    echo "<p><a href='auth.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;'>Aller à la page d'authentification</a></p>";
    echo "<p><a href='utilisateurs_modern.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; margin-left: 10px;'>Aller à la gestion des utilisateurs</a></p>";
} catch (Exception $e) {
    echo "<h2>❌ Erreur</h2>";
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Trace:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
