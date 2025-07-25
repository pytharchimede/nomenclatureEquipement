<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Flux d'Authentification - EquiNomTech</title>
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
            max-width: 700px;
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

        .flow-diagram {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin: 2rem 0;
        }

        .flow-step {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .flow-step:hover {
            border-color: #667eea;
            transform: translateY(-5px);
        }

        .flow-step h3 {
            color: #667eea;
            margin-bottom: 1rem;
            font-weight: 700;
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

        .arrow {
            font-size: 2rem;
            color: #667eea;
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <div class="test-container">
        <h1 class="test-title">🔄 Nouveau Flux d'Authentification</h1>

        <div class="status">
            ✅ <strong>Erreurs de session corrigées !</strong><br>
            ✅ <strong>Logique de redirection implémentée !</strong><br>
            ✅ <strong>Structure simplifiée et claire !</strong>
        </div>

        <h3 style="color: #667eea; margin-bottom: 1rem;">📋 Nouveau flux de navigation :</h3>

        <div class="flow-diagram">
            <div class="flow-step">
                <h3>1. 📁 index.php</h3>
                <p><strong>Simple redirection :</strong><br>
                    • Si connecté → accueil.php<br>
                    • Si non connecté → auth.php</p>
            </div>

            <div class="flow-step">
                <h3>2. 🔐 auth.php</h3>
                <p><strong>Page de connexion moderne</strong><br>
                    • Interface élégante<br>
                    • Animations fluides<br>
                    • Sécurité renforcée</p>
            </div>

            <div class="flow-step">
                <h3>3. 🏠 accueil.php</h3>
                <p><strong>Page d'accueil moderne</strong><br>
                    • Design spectaculaire<br>
                    • Particules animées<br>
                    • Navigation intégrée</p>
            </div>
        </div>

        <div class="arrow">⬇️</div>

        <p><strong>🎯 Avantages du nouveau système :</strong></p>
        <ul style="text-align: left; margin: 1rem 0; max-width: 500px; margin-left: auto; margin-right: auto;">
            <li>✅ Plus d'erreurs de session</li>
            <li>✅ Flux d'authentification clair</li>
            <li>✅ Code plus maintenable</li>
            <li>✅ Navigation cohérente</li>
            <li>✅ Sécurité améliorée</li>
        </ul>

        <div class="test-links">
            <a href="index.php" class="test-link">
                🔄 Tester le flux complet
            </a>
            <a href="auth.php" class="test-link">
                🔐 Page de connexion
            </a>
            <a href="accueil.php" class="test-link">
                🏠 Accueil moderne
            </a>
            <a href="logout.php" class="test-link">
                🚪 Se déconnecter
            </a>
        </div>

        <div style="margin-top: 2rem; color: #6b7280; font-size: 0.9rem;">
            <strong>Note :</strong> Le fichier index.php est maintenant un simple gestionnaire de redirection de 8 lignes seulement !
        </div>
    </div>
</body>

</html>