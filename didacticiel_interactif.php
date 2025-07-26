<?php
require_once 'includes/auth.php';
require_once 'model/Database.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎓 Didacticiel Interactif - EquiNomTech</title>

    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        :root {
            --primary-color: #1976d2;
            --secondary-color: #667eea;
            --accent-color: #764ba2;
            --success-color: #43a047;
            --warning-color: #ff8f00;
            --danger-color: #e53935;
            --info-color: #00acc1;
            --dark-color: #212529;
            --light-color: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #43a047 0%, #4caf50 100%);
            --gradient-warning: linear-gradient(135deg, #ff8f00 0%, #ffa726 100%);
            --gradient-danger: linear-gradient(135deg, #e53935 0%, #ef5350 100%);
            --gradient-info: linear-gradient(135deg, #00acc1 0%, #26c6da 100%);
            --shadow-soft: 0 2px 20px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 8px 40px rgba(0, 0, 0, 0.15);
            --border-radius: 15px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

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

        /* Header Héro */
        .hero-header {
            background: var(--gradient-primary);
            color: white;
            padding: 60px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,100 1000,0 1000,100"/></svg>');
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.4rem;
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 40px;
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
        }

        .hero-stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Navigation Didacticiel */
        .tutorial-nav {
            background: white;
            padding: 20px 0;
            box-shadow: var(--shadow-soft);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-progress {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .nav-progress-bar {
            height: 100%;
            background: var(--gradient-primary);
            width: 0%;
            transition: width 0.5s ease;
            border-radius: 2px;
        }

        .nav-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            padding: 15px;
            border-radius: var(--border-radius);
            min-width: 120px;
        }

        .nav-step:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .nav-step.active {
            background: var(--gradient-primary);
            color: white;
            transform: scale(1.05);
        }

        .nav-step.completed {
            background: var(--gradient-success);
            color: white;
        }

        .nav-step-icon {
            font-size: 2rem;
            margin-bottom: 8px;
            transition: var(--transition);
        }

        .nav-step.active .nav-step-icon,
        .nav-step.completed .nav-step-icon {
            transform: scale(1.2);
        }

        .nav-step-title {
            font-weight: 600;
            font-size: 0.85rem;
            text-align: center;
        }

        .nav-step-subtitle {
            font-size: 0.7rem;
            opacity: 0.8;
            text-align: center;
        }

        /* Contenu Principal */
        .main-content {
            background: white;
            margin: 40px auto;
            max-width: 1200px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .content-section {
            display: none;
            padding: 60px;
            min-height: 600px;
        }

        .content-section.active {
            display: block;
            animation: fadeInUp 0.6s ease-out;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #666;
            font-weight: 400;
        }

        /* Cards Interactives */
        .feature-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .feature-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
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
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-color);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card-icon {
            font-size: 3rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }

        .feature-card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .feature-card-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .feature-card-action {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: var(--transition);
        }

        .feature-card-action:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        /* Contrôles Navigation */
        .nav-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 60px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .nav-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }

        .nav-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .nav-btn.secondary {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .nav-btn.secondary:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Animations personnalisées */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 30px;
            }

            .nav-steps {
                overflow-x: auto;
                justify-content: flex-start;
                padding-bottom: 10px;
            }

            .nav-step {
                min-width: 100px;
                flex-shrink: 0;
            }

            .content-section {
                padding: 30px 20px;
            }

            .nav-controls {
                padding: 20px;
                flex-direction: column;
                gap: 15px;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        /* Indicateurs d'état */
        .status-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--success-color);
            box-shadow: 0 0 0 3px rgba(67, 160, 71, 0.3);
        }

        .status-indicator.pending {
            background: var(--warning-color);
            box-shadow: 0 0 0 3px rgba(255, 143, 0, 0.3);
        }

        .status-indicator.inactive {
            background: #ccc;
            box-shadow: 0 0 0 3px rgba(204, 204, 204, 0.3);
        }

        /* Tooltips personnalisés */
        .tooltip-custom {
            position: relative;
            cursor: pointer;
        }

        .tooltip-custom::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark-color);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: var(--transition);
            z-index: 1000;
        }

        .tooltip-custom:hover::after {
            opacity: 1;
            transform: translateX(-50%) translateY(-5px);
        }
    </style>
</head>

<body>
    <!-- Header Héro -->
    <div class="hero-header">
        <div class="hero-content">
            <div class="container">
                <h1 class="hero-title animate__animated animate__fadeInDown">
                    🎓 Didacticiel Interactif
                </h1>
                <p class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                    Maîtrisez EquiNomTech en quelques minutes avec notre guide pas-à-pas
                </p>
                <div class="hero-stats animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="hero-stat">
                        <span class="hero-stat-number">10</span>
                        <span class="hero-stat-label">Étapes Simples</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">15</span>
                        <span class="hero-stat-label">Minutes</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">100%</span>
                        <span class="hero-stat-label">Interactif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Didacticiel -->
    <div class="tutorial-nav">
        <div class="container">
            <div class="nav-progress">
                <div class="nav-progress-bar" id="progressBar"></div>
            </div>
            <div class="nav-steps" id="navSteps">
                <!-- Les étapes seront générées dynamiquement -->
            </div>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="main-content">
        <!-- Section d'accueil -->
        <div class="content-section active" id="section-0">
            <div class="section-header">
                <h2 class="section-title">🚀 Bienvenue dans EquiNomTech</h2>
                <p class="section-subtitle">
                    Découvrez la puissance de votre système de gestion de nomenclature d'équipements
                </p>
            </div>

            <div class="feature-cards">
                <div class="feature-card tooltip-custom" data-tooltip="Interface moderne et intuitive">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">🎨</div>
                    <h3 class="feature-card-title">Interface Moderne</h3>
                    <p class="feature-card-description">
                        Une interface élégante et intuitive conçue pour optimiser votre productivité
                    </p>
                    <button class="feature-card-action">Explorer</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Gestion complète des équipements">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">🔧</div>
                    <h3 class="feature-card-title">Gestion d'Équipements</h3>
                    <p class="feature-card-description">
                        Gérez vos équipements industriels avec une précision et une efficacité inégalées
                    </p>
                    <button class="feature-card-action">Découvrir</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Classification intelligente des articles">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">📦</div>
                    <h3 class="feature-card-title">Articles Intelligents</h3>
                    <p class="feature-card-description">
                        Classification automatique par métier et gestion avancée des pièces de rechange
                    </p>
                    <button class="feature-card-action">Apprendre</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Imports et exports simplifiés">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">📊</div>
                    <h3 class="feature-card-title">Import/Export Avancé</h3>
                    <p class="feature-card-description">
                        Outils puissants d'import Excel et d'export personnalisé pour tous vos besoins
                    </p>
                    <button class="feature-card-action">Tester</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Analyses et rapports en temps réel">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">📈</div>
                    <h3 class="feature-card-title">Analytics Temps Réel</h3>
                    <p class="feature-card-description">
                        Tableaux de bord dynamiques et rapports intelligents pour vos prises de décision
                    </p>
                    <button class="feature-card-action">Analyser</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Détection automatique des doublons">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">🔍</div>
                    <h3 class="feature-card-title">Détection Doublons</h3>
                    <p class="feature-card-description">
                        Algorithmes intelligents pour détecter et résoudre automatiquement les conflits
                    </p>
                    <button class="feature-card-action">Optimiser</button>
                </div>
            </div>
        </div>

        <!-- Les autres sections seront ajoutées dynamiquement -->
    </div>

    <!-- Contrôles Navigation -->
    <div class="nav-controls">
        <button class="nav-btn secondary" id="prevBtn" disabled>
            <span class="material-icons">arrow_back</span>
            Précédent
        </button>

        <div class="d-flex align-items-center gap-3">
            <span id="stepIndicator" class="text-muted">Étape 1 sur 10</span>
            <div class="tooltip-custom" data-tooltip="Votre progression dans le didacticiel">
                <span class="material-icons text-primary pulse-animation">help_outline</span>
            </div>
        </div>

        <button class="nav-btn" id="nextBtn">
            Suivant
            <span class="material-icons">arrow_forward</span>
        </button>
    </div>

    <!-- Scripts -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        class TutorialManager {
            constructor() {
                this.currentStep = 0;
                this.totalSteps = 10;
                this.steps = [{
                        icon: '🏠',
                        title: 'Accueil',
                        subtitle: 'Introduction'
                    },
                    {
                        icon: '🧭',
                        title: 'Navigation',
                        subtitle: 'Interface'
                    },
                    {
                        icon: '🔧',
                        title: 'Équipements',
                        subtitle: 'Gestion'
                    },
                    {
                        icon: '📦',
                        title: 'Articles',
                        subtitle: 'Classification'
                    },
                    {
                        icon: '📋',
                        title: 'Nomenclatures',
                        subtitle: 'Relations'
                    },
                    {
                        icon: '📤',
                        title: 'Import/Export',
                        subtitle: 'Données'
                    },
                    {
                        icon: '📊',
                        title: 'Analytics',
                        subtitle: 'Rapports'
                    },
                    {
                        icon: '⚠️',
                        title: 'Doublons',
                        subtitle: 'Résolution'
                    },
                    {
                        icon: '👥',
                        title: 'Administration',
                        subtitle: 'Gestion'
                    },
                    {
                        icon: '✅',
                        title: 'Finalisation',
                        subtitle: 'Récapitulatif'
                    }
                ];
                this.init();
            }

            init() {
                this.createNavSteps();
                this.updateUI();
                this.bindEvents();
                this.animateEntry();
            }

            createNavSteps() {
                const navSteps = document.getElementById('navSteps');
                navSteps.innerHTML = '';

                this.steps.forEach((step, index) => {
                    const stepElement = document.createElement('div');
                    stepElement.className = `nav-step ${index === 0 ? 'active' : ''}`;
                    stepElement.setAttribute('data-step', index);
                    stepElement.innerHTML = `
                        <div class="nav-step-icon">${step.icon}</div>
                        <div class="nav-step-title">${step.title}</div>
                        <div class="nav-step-subtitle">${step.subtitle}</div>
                    `;
                    stepElement.addEventListener('click', () => this.goToStep(index));
                    navSteps.appendChild(stepElement);
                });
            }

            updateUI() {
                // Mise à jour de la barre de progression
                const progressBar = document.getElementById('progressBar');
                const progress = ((this.currentStep + 1) / this.totalSteps) * 100;
                progressBar.style.width = `${progress}%`;

                // Mise à jour des étapes
                document.querySelectorAll('.nav-step').forEach((step, index) => {
                    step.classList.remove('active', 'completed');
                    if (index === this.currentStep) {
                        step.classList.add('active');
                    } else if (index < this.currentStep) {
                        step.classList.add('completed');
                    }
                });

                // Mise à jour des boutons
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');

                prevBtn.disabled = this.currentStep === 0;
                nextBtn.textContent = this.currentStep === this.totalSteps - 1 ? 'Terminer' : 'Suivant';

                // Mise à jour de l'indicateur
                document.getElementById('stepIndicator').textContent =
                    `Étape ${this.currentStep + 1} sur ${this.totalSteps}`;

                // Animation des sections
                this.showCurrentSection();
            }

            showCurrentSection() {
                document.querySelectorAll('.content-section').forEach((section, index) => {
                    section.classList.remove('active');
                    if (index === this.currentStep) {
                        section.classList.add('active');
                    }
                });
            }

            goToStep(step) {
                if (step >= 0 && step < this.totalSteps) {
                    this.currentStep = step;
                    this.updateUI();
                    this.trackProgress();
                }
            }

            nextStep() {
                if (this.currentStep < this.totalSteps - 1) {
                    this.currentStep++;
                    this.updateUI();
                    this.trackProgress();
                } else {
                    this.completeTutorial();
                }
            }

            prevStep() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                    this.updateUI();
                }
            }

            bindEvents() {
                document.getElementById('nextBtn').addEventListener('click', () => this.nextStep());
                document.getElementById('prevBtn').addEventListener('click', () => this.prevStep());

                // Gestion du clavier
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowRight') this.nextStep();
                    if (e.key === 'ArrowLeft') this.prevStep();
                });

                // Animation des cards
                document.querySelectorAll('.feature-card').forEach(card => {
                    card.addEventListener('click', () => {
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.style.transform = '';
                        }, 150);
                    });
                });
            }

            animateEntry() {
                // Animation d'entrée pour les éléments
                setTimeout(() => {
                    document.querySelectorAll('.feature-card').forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('animate__animated', 'animate__fadeInUp');
                        }, index * 100);
                    });
                }, 500);
            }

            trackProgress() {
                // Ici on pourrait ajouter un tracking des analytics
                console.log(`Étape ${this.currentStep + 1} visitée`);
            }

            completeTutorial() {
                // Animation de completion
                const confetti = this.createConfetti();
                alert('🎉 Félicitations ! Vous avez terminé le didacticiel EquiNomTech !');

                // Redirection optionnelle
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 2000);
            }

            createConfetti() {
                // Animation de confettis simple
                for (let i = 0; i < 50; i++) {
                    setTimeout(() => {
                        const confetti = document.createElement('div');
                        confetti.style.cssText = `
                            position: fixed;
                            top: -10px;
                            left: ${Math.random() * 100}%;
                            width: 10px;
                            height: 10px;
                            background: hsl(${Math.random() * 360}, 70%, 60%);
                            pointer-events: none;
                            z-index: 10000;
                            animation: fall 3s linear forwards;
                        `;
                        document.body.appendChild(confetti);

                        setTimeout(() => confetti.remove(), 3000);
                    }, i * 50);
                }
            }
        }

        // Style pour l'animation de chute
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            new TutorialManager();
        });

        // Animation de typing pour les textes
        function typeWriter(element, text, speed = 50) {
            let i = 0;
            element.innerHTML = '';

            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }

        // Effets visuels supplémentaires
        document.addEventListener('mousemove', (e) => {
            const cursor = document.querySelector('.cursor-trail');
            if (!cursor) {
                const trail = document.createElement('div');
                trail.className = 'cursor-trail';
                trail.style.cssText = `
                    position: fixed;
                    width: 20px;
                    height: 20px;
                    background: radial-gradient(circle, rgba(102,126,234,0.3) 0%, transparent 70%);
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 1000;
                    transition: transform 0.1s ease;
                `;
                document.body.appendChild(trail);
            }

            const trail = document.querySelector('.cursor-trail');
            trail.style.left = e.clientX - 10 + 'px';
            trail.style.top = e.clientY - 10 + 'px';
        });
    </script>
</body>

</html>