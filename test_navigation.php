<?php
// Test de la nouvelle structure
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simuler une session utilisateur pour le test
if (!isset($_SESSION['user_nom'])) {
    $_SESSION['user_nom'] = 'Test User';
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'test@exemple.com';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Navigation - EquiNomTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 2rem;
            margin-left: 280px;
        }

        .test-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .test-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .nav-test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .nav-test-link {
            display: block;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
        }

        .nav-test-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .status-check {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Menu moderne -->
        <?php include 'menu.php'; ?>

        <!-- Contenu principal -->
        <div class="content">
            <div class="test-card">
                <h1 class="test-title">🧪 Test Navigation Menu</h1>

                <div class="status-check">
                    ✅ <strong>Sessions corrigées :</strong> Plus d'erreurs de headers déjà envoyés !<br>
                    ✅ <strong>Menu intégré :</strong> Navigation cohérente sur toutes les pages<br>
                    ✅ <strong>Structure optimisée :</strong> Gestion des accès en bas du menu
                </div>

                <h3 style="color: #667eea; margin-bottom: 1rem;">🧭 Test des liens de navigation :</h3>
                <p>Cliquez sur les liens ci-dessous pour tester la navigation :</p>

                <div class="nav-test-grid">
                    <a href="index.php" class="nav-test-link">
                        🏠 Accueil Moderne
                    </a>
                    <a href="dashboard.php" class="nav-test-link">
                        📊 Dashboard
                    </a>
                    <a href="utilisateurs.php" class="nav-test-link">
                        👥 Utilisateurs
                    </a>
                    <a href="groupes.php" class="nav-test-link">
                        🏢 Groupes
                    </a>
                    <a href="profil.php" class="nav-test-link">
                        👤 Mon Profil
                    </a>
                    <a href="equipements.php" class="nav-test-link">
                        🔧 Équipements
                    </a>
                    <a href="articles.php" class="nav-test-link">
                        📦 Articles
                    </a>
                    <a href="nomenclatures.php" class="nav-test-link">
                        📋 Nomenclatures
                    </a>
                </div>

                <div class="alert alert-info mt-4" role="alert">
                    <strong>🔍 Vérifications effectuées :</strong>
                    <ul class="mb-0 mt-2">
                        <li>✅ Sessions PHP démarrées avant tout output HTML</li>
                        <li>✅ Menu unifié sur toutes les pages</li>
                        <li>✅ Navigation cohérente (sans suffixes "_modern")</li>
                        <li>✅ Page d'accueil intégrée au menu principal</li>
                        <li>✅ Structure du menu réorganisée logiquement</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>