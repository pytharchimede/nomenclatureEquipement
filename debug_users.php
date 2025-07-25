<?php
session_start();
require_once 'model/Database.php';
require_once 'model/Utilisateur.php';

// Démarrer la capture d'output pour capturer les erreurs
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Débogage - Page Utilisateurs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .error {
            background: #ffebee;
            border: 1px solid #f44336;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .success {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>🔍 Débogage - Page Utilisateurs</h1>

    <?php
    echo "<div class='info'><strong>Session actuelle:</strong><br>";
    echo "Utilisateur connecté: " . ($_SESSION['user_id'] ?? 'Non connecté') . "<br>";
    echo "Email: " . ($_SESSION['email'] ?? 'Non défini') . "<br>";
    echo "</div>";

    // Test 1: Connexion à la base de données
    try {
        $pdo = Database::getConnection();
        echo "<div class='success'>✓ Connexion à la base de données réussie</div>";
    } catch (Exception $e) {
        echo "<div class='error'>✗ Erreur de connexion DB: " . htmlspecialchars($e->getMessage()) . "</div>";
        exit;
    }

    // Test 2: Vérification de la structure de la table
    try {
        echo "<h2>Structure de la table utilisateur</h2>";
        $stmt = $pdo->query("DESCRIBE utilisateur");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<table>";
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

        echo "<div class='success'>✓ Structure de la table récupérée</div>";
    } catch (Exception $e) {
        echo "<div class='error'>✗ Erreur lors de la récupération de la structure: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

    // Test 3: Récupération des utilisateurs avec gestion d'erreur
    try {
        echo "<h2>Test de récupération des utilisateurs</h2>";
        $utilisateurs = Utilisateur::getAll();
        echo "<div class='success'>✓ Récupération des utilisateurs réussie</div>";
        echo "<div class='info'>Nombre d'utilisateurs trouvés: " . count($utilisateurs) . "</div>";

        if (!empty($utilisateurs)) {
            echo "<h3>Détails des utilisateurs:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nom</th><th>Nom Utilisateur</th><th>Email</th><th>Groupe ID</th><th>Actif</th><th>Date création</th></tr>";

            foreach ($utilisateurs as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($user['nom'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($user['nom_utilisateur'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($user['email'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($user['groupe_id'] ?? 'N/A') . "</td>";
                echo "<td>" . ($user['actif'] ? 'Oui' : 'Non') . "</td>";
                echo "<td>" . htmlspecialchars($user['date_creation'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>✗ Erreur lors de la récupération des utilisateurs: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<pre>Stack trace:\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }

    // Test 4: Vérification des groupes utilisateurs
    try {
        echo "<h2>Test de récupération des groupes</h2>";
        $stmt = $pdo->query("SELECT * FROM groupe_utilisateur");
        $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='success'>✓ Récupération des groupes réussie</div>";
        echo "<div class='info'>Nombre de groupes trouvés: " . count($groupes) . "</div>";

        if (!empty($groupes)) {
            echo "<h3>Groupes disponibles:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nom</th><th>Description</th></tr>";

            foreach ($groupes as $groupe) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($groupe['id']) . "</td>";
                echo "<td>" . htmlspecialchars($groupe['nom']) . "</td>";
                echo "<td>" . htmlspecialchars($groupe['description'] ?? '') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>✗ Erreur lors de la récupération des groupes: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

    // Test 5: Vérification de la session et de l'authentification
    echo "<h2>Vérification de l'authentification</h2>";
    if (isset($_SESSION['user_id'])) {
        echo "<div class='success'>✓ Utilisateur authentifié (ID: " . $_SESSION['user_id'] . ")</div>";

        // Vérifier si l'utilisateur existe dans la base
        try {
            $currentUser = Utilisateur::getById($_SESSION['user_id']);
            if ($currentUser) {
                echo "<div class='success'>✓ Utilisateur trouvé dans la base de données</div>";
                echo "<div class='info'>Détails de l'utilisateur connecté:<br>";
                echo "Nom: " . htmlspecialchars($currentUser['nom'] ?? 'N/A') . "<br>";
                echo "Email: " . htmlspecialchars($currentUser['email'] ?? 'N/A') . "<br>";
                echo "Groupe: " . htmlspecialchars($currentUser['groupe_id'] ?? 'N/A') . "<br>";
                echo "</div>";
            } else {
                echo "<div class='error'>✗ Utilisateur introuvable dans la base de données</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>✗ Erreur lors de la vérification de l'utilisateur: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<div class='error'>✗ Utilisateur non authentifié</div>";
    }

    // Test 6: Test de l'API de connexions récentes
    try {
        echo "<h2>Test de l'API connexions récentes</h2>";
        $twentyFourHoursAgo = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM utilisateur WHERE last_login_at >= ?");
        $stmt->execute([$twentyFourHoursAgo]);
        $result = $stmt->fetch();
        echo "<div class='success'>✓ API connexions récentes fonctionne</div>";
        echo "<div class='info'>Connexions dans les 24h: " . $result['count'] . "</div>";
    } catch (Exception $e) {
        echo "<div class='error'>✗ Erreur API connexions récentes: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='info'>Cela peut être normal si les nouveaux champs n'ont pas encore été ajoutés</div>";
    }

    // Capture des erreurs PHP
    $phpErrors = ob_get_contents();
    if (!empty($phpErrors)) {
        echo "<h2>Erreurs PHP capturées</h2>";
        echo "<pre>" . htmlspecialchars($phpErrors) . "</pre>";
    }
    ?>

    <hr>
    <div class='info'>
        <strong>Actions recommandées:</strong><br>
        1. Si vous voyez des erreurs de colonnes manquantes, exécutez le script setup_database.php<br>
        2. Si les utilisateurs ne s'affichent pas, vérifiez que le champ 'nom_utilisateur' existe<br>
        3. Si l'authentification échoue, vérifiez la session PHP<br>
    </div>

    <p><a href="utilisateurs_modern.php">→ Retour à la page utilisateurs</a></p>
    <p><a href="setup_database.php">→ Mettre à jour la base de données</a></p>
</body>

</html>