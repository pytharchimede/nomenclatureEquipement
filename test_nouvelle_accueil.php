<?php
// Simuler une session utilisateur pour le test
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simuler une session utilisateur connecté
if (!isset($_SESSION['user_nom'])) {
    $_SESSION['user_nom'] = 'Jean Dupont';
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'jean.dupont@exemple.com';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Nouvelle Page d'Accueil - EquiNomTech</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1rem;
        }

        .test-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 800px;
            width: 100%;
        }

        .test-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 2rem;
        }

        .improvements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .improvement-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: left;
        }

        .improvement-item h3 {
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .improvement-item ul {
            list-style: none;
            padding: 0;
        }

        .improvement-item li {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }

        .improvement-item li:before {
            content: '✅';
            position: absolute;
            left: 0;
        }

        .test-link {
            display: inline-block;
            margin: 1rem 0.5rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .test-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .status {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="test-container">
        <h1 class="test-title">🏠 Nouvelle Page d'Accueil Intelligente</h1>

        <div class="status">
            ✅ <strong>Page d'accueil complètement repensée !</strong><br>
            ✅ <strong>Contenu informatif et orienté utilisateur</strong><br>
            ✅ <strong>Statistiques en temps réel intégrées</strong>
        </div>

        <h3 style="color: #667eea; margin-bottom: 1rem;">🚀 Améliorations apportées :</h3>

        <div class="improvements-grid">
            <div class="improvement-item">
                <h3>👋 Accueil Personnalisé</h3>
                <ul>
                    <li>Message de bienvenue avec nom utilisateur</li>
                    <li>Contexte clair de la plateforme</li>
                    <li>Actions principales en évidence</li>
                    <li>Navigation intuitive</li>
                </ul>
            </div>

            <div class="improvement-item">
                <h3>🎯 Fonctionnalités Métier</h3>
                <ul>
                    <li>Focus sur les équipements</li>
                    <li>Gestion des articles et nomenclatures</li>
                    <li>Import de données simplifié</li>
                    <li>Liens directs vers chaque section</li>
                </ul>
            </div>

            <div class="improvement-item">
                <h3>📊 Données Temps Réel</h3>
                <ul>
                    <li>Statistiques live de la base</li>
                    <li>Compteurs d'équipements/articles</li>
                    <li>Nombre d'utilisateurs actifs</li>
                    <li>Statut système en temps réel</li>
                </ul>
            </div>

            <div class="improvement-item">
                <h3>🎓 Guide d'Utilisation</h3>
                <ul>
                    <li>Section "Démarrage Rapide"</li>
                    <li>Étapes claires numérotées</li>
                    <li>Workflow logique</li>
                    <li>Aide contextuelle</li>
                </ul>
            </div>
        </div>

        <h3 style="color: #667eea; margin: 2rem 0 1rem;">🧪 Testez la nouvelle page :</h3>

        <a href="accueil.php" class="test-link">
            🏠 Voir la Nouvelle Page d'Accueil
        </a>

        <a href="index.php" class="test-link">
            🔄 Tester le Flux Complet
        </a>

        <div style="margin-top: 2rem; color: #6b7280; font-size: 0.9rem; text-align: left;">
            <strong>💡 Ce qui rend cette page spéciale :</strong><br><br>
            • <strong>Orientée métier :</strong> Focus sur les vrais besoins (équipements, nomenclatures)<br>
            • <strong>Informative :</strong> L'utilisateur comprend immédiatement ce qu'il peut faire<br>
            • <strong>Actionnable :</strong> Chaque section a un lien direct vers l'action<br>
            • <strong>Temps réel :</strong> Les données affichées sont vraies et actuelles<br>
            • <strong>Guidée :</strong> Un parcours d'utilisation clair pour les nouveaux utilisateurs
        </div>
    </div>
</body>

</html>