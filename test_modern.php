<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test du système moderne</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .test-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 600px;
        }

        .test-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
        }

        .test-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .test-link {
            display: block;
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
        <h1 class="test-title">🎉 Système Moderne Activé !</h1>

        <div class="status">
            ✅ Tous les fichiers ont été remplacés par leurs versions modernes !
        </div>

        <p>Votre application utilise maintenant les designs "wowwww" et modernes avec :</p>
        <ul style="text-align: left; margin: 2rem 0;">
            <li>🎨 Particules animées</li>
            <li>✨ Effets de glassmorphisme</li>
            <li>🌈 Gradients dynamiques</li>
            <li>🚀 Animations fluides</li>
            <li>📱 Design responsive</li>
            <li>🔧 Correction du champ "nom" dans la base de données</li>
        </ul>

        <div class="test-links">
            <a href="auth.php" class="test-link">🔐 Authentification</a>
            <a href="index.php" class="test-link">🏠 Accueil</a>
            <a href="utilisateurs.php" class="test-link">👥 Utilisateurs</a>
            <a href="groupes.php" class="test-link">🏢 Groupes</a>
            <a href="profil.php" class="test-link">👤 Profil</a>
            <a href="dashboard.php" class="test-link">📊 Dashboard</a>
        </div>

        <p style="margin-top: 2rem; color: #6b7280;">
            <strong>Note :</strong> Les anciens fichiers ont été sauvegardés avec le suffixe "_old.php"
        </p>
    </div>
</body>

</html>