<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Menu Complet - EquiNomTech</title>
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
            margin-bottom: 1rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .feature-list li:last-child {
            border-bottom: none;
        }

        .feature-icon {
            margin-right: 1rem;
            color: #667eea;
        }

        .badge-demo {
            margin-left: auto;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-demo.danger {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .badge-demo.warning {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
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
                <h1 class="test-title">🎉 Menu Moderne Complet !</h1>
                <p class="lead">Le menu a été mis à jour avec tous les liens et badges d'alerte !</p>

                <h3 style="color: #667eea; margin-top: 2rem;">✨ Nouvelles fonctionnalités ajoutées :</h3>
                <ul class="feature-list">
                    <li>
                        <span class="material-icons feature-icon">error_outline</span>
                        <span>Éléments Non SAP avec badge de danger</span>
                        <span class="badge-demo danger">12</span>
                    </li>
                    <li>
                        <span class="material-icons feature-icon">rule</span>
                        <span>Validation Import avec badge d'avertissement</span>
                        <span class="badge-demo warning">5</span>
                    </li>
                    <li>
                        <span class="material-icons feature-icon">table_view</span>
                        <span>Synthèse RGM</span>
                    </li>
                    <li>
                        <span class="material-icons feature-icon">table_view</span>
                        <span>Compilation Template SPL</span>
                    </li>
                    <li>
                        <span class="material-icons feature-icon">category</span>
                        <span>Section "Gestion des problèmes" réorganisée</span>
                    </li>
                </ul>

                <h3 style="color: #667eea; margin-top: 2rem;">🎨 Améliorations visuelles :</h3>
                <ul class="feature-list">
                    <li>
                        <span class="material-icons feature-icon">palette</span>
                        <span>Badges colorés selon le type d'alerte (rouge, orange)</span>
                    </li>
                    <li>
                        <span class="material-icons feature-icon">animation</span>
                        <span>Animations de bounce pour attirer l'attention</span>
                    </li>
                    <li>
                        <span class="material-icons feature-icon">view_sidebar</span>
                        <span>Sections organisées logiquement</span>
                    </li>
                    <li>
                        <span class="material-icons feature-icon">grade</span>
                        <span>Design glassmorphique avec effets modernes</span>
                    </li>
                </ul>

                <div class="alert alert-success mt-4" role="alert">
                    <strong>✅ Complet !</strong> Le menu contient maintenant tous les liens de l'ancien menu avec le design moderne et les badges d'alerte fonctionnels.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>