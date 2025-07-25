<?php
require_once 'model/Database.php';
require_once 'model/Utilisateur.php';

try {
    echo "<h1>Test de connexion à la base de données</h1>";

    // Test de connexion
    $pdo = Database::getConnection();
    echo "<p style='color: green;'>✓ Connexion à la base de données réussie</p>";

    // Test de récupération des utilisateurs
    $users = Utilisateur::getAll();
    echo "<p style='color: green;'>✓ Récupération des utilisateurs réussie</p>";
    echo "<p>Nombre d'utilisateurs: " . count($users) . "</p>";

    if (!empty($users)) {
        echo "<h2>Utilisateurs existants:</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Groupe ID</th><th>Actif</th><th>Date création</th></tr>";

        foreach ($users as $user) {
            $nom_utilisateur = $user['nom_utilisateur'] ?? $user['nom'] ?? 'N/A';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($nom_utilisateur) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['groupe_id'] ?? 'N/A') . "</td>";
            echo "<td>" . ($user['actif'] ? 'Oui' : 'Non') . "</td>";
            echo "<td>" . htmlspecialchars($user['date_creation']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Test de la structure de la table
    echo "<h2>Structure de la table utilisateur:</h2>";
    $stmt = $pdo->query("DESCRIBE utilisateur");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";

    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Vérification des nouveaux champs
    $requiredFields = ['nom_utilisateur', 'updated_at', 'last_login_at', 'login_attempts', 'locked_until'];
    $missingFields = [];

    $existingFields = array_column($columns, 'Field');

    foreach ($requiredFields as $field) {
        if (!in_array($field, $existingFields)) {
            $missingFields[] = $field;
        }
    }

    if (empty($missingFields)) {
        echo "<p style='color: green;'>✓ Tous les champs requis pour l'authentification moderne sont présents</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Champs manquants: " . implode(', ', $missingFields) . "</p>";
        echo "<p>Exécutez le script SQL dans Database/update_auth_tables.sql pour ajouter ces champs.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
