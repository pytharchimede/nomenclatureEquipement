<?php
// Démarrer la session avant tout output HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>émarrer la session avant tout output HTML
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
    }
    require_once 'includes/auth.php';
    ?>
    <!DOCTYPE html>
    <html lang="fr"

        <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquiNomTech - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Particles animés */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }

            50% {
                transform: translateY(-100px) rotate(180deg);
                opacity: 0.8;
            }
        }

        .main-container {
            position: relative;
            z-index: 10;
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            margin-left: 280px;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 4rem;
            padding: 4rem 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
        }

        .hero-description {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 600px;
            margin: 0 auto 3rem;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .cta-btn {
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-width: 200px;
        }

        .cta-btn-primary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .cta-btn-primary:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .cta-btn-secondary {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            color: #667eea;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .cta-btn-secondary:hover {
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .feature-description {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .feature-action {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: inline-block;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .feature-action:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateX(5px);
        }

        .quick-help-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            margin-top: 4rem;
        }

        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .help-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .help-step {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .help-item h4 {
            color: white;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .help-item p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .stats-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-item {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <!-- Particles animés -->
    <div class="particles" id="particles"></div>

    <div class="main-container">
        <!-- Menu moderne -->
        <?php include 'menu.php'; ?>

        <!-- Contenu principal -->
        <div class="content">
            <!-- Section hero -->
            <div class="hero-section">
                <h1 class="hero-title">Bienvenue dans EquiNomTech</h1>
                <p class="hero-subtitle">Plateforme de gestion moderne des équipements et nomenclatures</p>
                <p class="hero-description">
                    Bonjour <strong><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></strong> !
                    Vous voici sur votre tableau de bord centralisé pour gérer efficacement vos équipements,
                    articles et nomenclatures avec des outils modernes et intuitifs.
                </p>
                <div class="cta-buttons">
                    <a href="dashboard.php" class="cta-btn cta-btn-primary">
                        <span class="material-icons" style="vertical-align: middle; margin-right: 0.5rem;">dashboard</span>
                        Tableau de Bord
                    </a>
                    <a href="equipements.php" class="cta-btn cta-btn-secondary">
                        <span class="material-icons" style="vertical-align: middle; margin-right: 0.5rem;">precision_manufacturing</span>
                        Gérer Équipements
                    </a>
                </div>
            </div>

            <!-- Grille des fonctionnalités principales -->
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="material-icons">precision_manufacturing</span>
                    </div>
                    <h3 class="feature-title">Gestion des Équipements</h3>
                    <p class="feature-description">
                        Centralisez la gestion de votre parc d'équipements industriels. Suivez l'état,
                        la maintenance et l'historique de chaque équipement en temps réel.
                    </p>
                    <a href="equipements.php" class="feature-action">Accéder →</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="material-icons">inventory</span>
                    </div>
                    <h3 class="feature-title">Catalogue d'Articles</h3>
                    <p class="feature-description">
                        Gérez votre inventaire d'articles avec traçabilité complète.
                        Recherchez, filtrez et organisez vos composants facilement.
                    </p>
                    <a href="articles.php" class="feature-action">Explorer →</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="material-icons">list_alt</span>
                    </div>
                    <h3 class="feature-title">Nomenclatures Techniques</h3>
                    <p class="feature-description">
                        Créez et maintenez vos nomenclatures techniques. Visualisez les relations
                        entre équipements et articles pour une meilleure traçabilité.
                    </p>
                    <a href="nomenclatures.php" class="feature-action">Consulter →</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="material-icons">file_upload</span>
                    </div>
                    <h3 class="feature-title">Import de Données</h3>
                    <p class="feature-description">
                        Importez vos données quantitatives depuis Excel ou CSV.
                        Le système valide automatiquement la cohérence des données.
                    </p>
                    <a href="import_quantitatif.php" class="feature-action">Importer →</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="material-icons">assessment</span>
                    </div>
                    <h3 class="feature-title">Analyses & Rapports</h3>
                    <p class="feature-description">
                        Générez des rapports détaillés et des analyses visuelles pour
                        optimiser la gestion de vos ressources industrielles.
                    </p>
                    <a href="dashboard.php" class="feature-action">Analyser →</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="material-icons">settings</span>
                    </div>
                    <h3 class="feature-title">Administration</h3>
                    <p class="feature-description">
                        Gérez les utilisateurs, groupes et permissions.
                        Configurez votre système selon vos besoins organisationnels.
                    </p>
                    <a href="utilisateurs.php" class="feature-action">Configurer →</a>
                </div>
            </div>

            <!-- Section aide rapide -->
            <div class="quick-help-section">
                <h2 style="color: white; margin-bottom: 2rem; font-weight: 700;">🚀 Démarrage Rapide</h2>
                <div class="help-grid">
                    <div class="help-item">
                        <div class="help-step">1</div>
                        <h4>Consultez vos équipements</h4>
                        <p>Commencez par explorer votre parc d'équipements existants dans la section dédiée.</p>
                    </div>
                    <div class="help-item">
                        <div class="help-step">2</div>
                        <h4>Vérifiez les nomenclatures</h4>
                        <p>Assurez-vous que vos nomenclatures techniques sont à jour et complètes.</p>
                    </div>
                    <div class="help-item">
                        <div class="help-step">3</div>
                        <h4>Importez de nouvelles données</h4>
                        <p>Utilisez l'outil d'import pour ajouter ou mettre à jour vos informations.</p>
                    </div>
                    <div class="help-item">
                        <div class="help-step">4</div>
                        <h4>Consultez le tableau de bord</h4>
                        <p>Surveillez les indicateurs clés et les alertes dans votre dashboard.</p>
                    </div>
                </div>
            </div>

            <!-- Section statistiques en temps réel -->
            <div class="stats-section">
                <h2 style="color: white; margin-bottom: 2rem; font-weight: 700;">📊 Aperçu de votre système</h2>
                <div class="stats-grid">
                    <?php
                    try {
                        require_once 'model/Database.php';
                        $pdo = Database::getConnection();

                        // Compter les équipements
                        $stmt = $pdo->query("SELECT COUNT(*) FROM equipements");
                        $nbEquipements = $stmt->fetchColumn();

                        // Compter les articles
                        $stmt = $pdo->query("SELECT COUNT(*) FROM articles");
                        $nbArticles = $stmt->fetchColumn();

                        // Compter les nomenclatures
                        $stmt = $pdo->query("SELECT COUNT(*) FROM nomenclatures");
                        $nbNomenclatures = $stmt->fetchColumn();

                        // Compter les utilisateurs actifs
                        $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE actif = 1");
                        $nbUtilisateurs = $stmt->fetchColumn();
                    } catch (Exception $e) {
                        $nbEquipements = 0;
                        $nbArticles = 0;
                        $nbNomenclatures = 0;
                        $nbUtilisateurs = 0;
                    }
                    ?>
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($nbEquipements) ?></div>
                        <div class="stat-label">Équipements</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($nbArticles) ?></div>
                        <div class="stat-label">Articles</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($nbNomenclatures) ?></div>
                        <div class="stat-label">Nomenclatures</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= $nbUtilisateurs ?></div>
                        <div class="stat-label">Utilisateurs actifs</div>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <p style="color: rgba(255, 255, 255, 0.8);">
                        <strong>Dernière connexion :</strong> <?= date('d/m/Y à H:i') ?> •
                        <strong>Statut système :</strong> <span style="color: #4caf50;">✅ Opérationnel</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Génération des particules animées
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';

                // Taille et position aléatoires
                const size = Math.random() * 6 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';

                // Animation delay aléatoire
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 4 + 4) + 's';

                container.appendChild(particle);
            }
        }

        // Animation d'apparition des cartes
        function animateCards() {
            const cards = document.querySelectorAll('.feature-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(50px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }

        // Animation des statistiques
        function animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent.replace(/[^\d]/g, ''));
                let current = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    stat.textContent = stat.textContent.includes('%') ?
                        current.toFixed(1) + '%' :
                        Math.floor(current).toLocaleString();
                }, 20);
            });
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            animateCards();

            // Observer pour les animations au scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.target.classList.contains('stats-section')) {
                        animateStats();
                    }
                });
            });

            document.querySelectorAll('.stats-section').forEach(section => {
                observer.observe(section);
            });
        });

        // Effet de parallaxe subtil
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.hero-section');
            if (parallax) {
                parallax.style.transform = `translateY(${scrolled * 0.1}px)`;
            }
        });
    </script>
</body>

</html>