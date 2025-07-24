<?php
require_once 'includes/auth.php';
require_once 'model/Database.php';

// Chargement initial rapide des familles pour éviter l'écran vide
$pdo = Database::getConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM familles");
$totalFamilles = $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT f.nom, COALESCE(COUNT(q.id), 0) as nb_elements
    FROM familles f 
    LEFT JOIN quantitatif q ON f.nom = q.famille 
    GROUP BY f.nom 
    ORDER BY nb_elements DESC 
    LIMIT 5
");
$topFamilles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Centre de Gestion des Familles - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .content {
            padding: 1rem;
            margin-left: 0;
            transition: all 0.3s ease;
        }

        .familles-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .familles-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1) rotate(0deg);
            }

            50% {
                transform: scale(1.1) rotate(180deg);
            }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .famille-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .famille-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .famille-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .famille-card:hover::before {
            opacity: 1;
        }

        .famille-card.active::before {
            opacity: 1;
        }

        .famille-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .famille-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin: 0;
        }

        .famille-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: linear-gradient(45deg, #28a745, #34ce57);
            color: white;
        }

        .status-inactive {
            background: linear-gradient(45deg, #6c757d, #868e96);
            color: white;
        }

        .famille-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .mini-stat {
            text-align: center;
            padding: 15px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .mini-stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
        }

        .mini-stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            position: relative;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .chart-wrapper canvas {
            max-height: 300px !important;
            width: 100% !important;
        }

        .filter-panel {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .search-input {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .btn-modern {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state .material-icons {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 20px;
        }

        .family-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }

        .insights-panel {
            background: linear-gradient(145deg, #e8f5e8, #f1f8e9);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid #28a745;
        }

        .insight-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .insight-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #28a745, #34ce57);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .insight-item:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .familles-hero {
                padding: 20px;
            }

            .family-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 content">
            <div class="container-fluid p-4">
                <!-- Hero Section -->
                <div class="familles-hero">
                    <div class="position-relative">
                        <h1 class="display-4 font-weight-bold mb-3">
                            <i class="material-icons" style="font-size: 3rem; vertical-align: middle;">category</i>
                            Centre de Gestion des Familles
                        </h1>
                        <p class="lead mb-4">Dashboard analytique complet pour la gestion et le suivi des familles d'équipements</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="#" class="btn-modern" onclick="refreshData()">
                                <i class="material-icons">refresh</i>
                                Actualiser
                            </a>
                            <a href="import_quantitatif.php" class="btn-modern">
                                <i class="material-icons">upload</i>
                                Importer Quantitatif
                            </a>
                            <a href="#" class="btn-modern" onclick="exportFamilleData()">
                                <i class="material-icons">download</i>
                                Export Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid" id="statsContainer">
                    <div class="stat-card">
                        <div class="stat-number" id="totalFamillesDefinies"><?= $totalFamilles ?></div>
                        <div class="text-muted font-weight-bold">Familles Définies</div>
                        <small class="text-info">
                            <i class="material-icons" style="font-size: 16px;">info</i>
                            Dans la base de données
                        </small>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="totalFamillesUtilisees">-</div>
                        <div class="text-muted font-weight-bold">Familles Actives</div>
                        <small class="text-success">
                            <i class="material-icons" style="font-size: 16px;">trending_up</i>
                            Avec données quantitatives
                        </small>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="totalElements">-</div>
                        <div class="text-muted font-weight-bold">Total Éléments</div>
                        <small class="text-primary">
                            <i class="material-icons" style="font-size: 16px;">inventory</i>
                            Tous types confondus
                        </small>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="familleTop">-</div>
                        <div class="text-muted font-weight-bold">Famille Leader</div>
                        <small class="text-warning">
                            <i class="material-icons" style="font-size: 16px;">star</i>
                            Plus d'éléments
                        </small>
                    </div>
                </div>

                <!-- Insights Panel -->
                <div class="insights-panel" id="insightsPanel">
                    <h5 class="mb-3">
                        <i class="material-icons" style="vertical-align: middle;">lightbulb</i>
                        Insights et Recommandations
                    </h5>
                    <div id="insightsList">
                        <div class="text-center text-muted">
                            <i class="material-icons">analytics</i>
                            Chargement des analyses...
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="filter-panel">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="material-icons" style="vertical-align: middle;">search</i>
                                Recherche et Filtres
                            </h5>
                            <input type="text" id="searchFamilles" class="form-control search-input" placeholder="Rechercher une famille...">
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Filtrer par statut:</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-outline-success btn-sm filter-btn active" data-filter="all">
                                    <i class="material-icons">select_all</i> Toutes
                                </button>
                                <button class="btn btn-outline-primary btn-sm filter-btn" data-filter="active">
                                    <i class="material-icons">check_circle</i> Actives
                                </button>
                                <button class="btn btn-outline-secondary btn-sm filter-btn" data-filter="inactive">
                                    <i class="material-icons">radio_button_unchecked</i> Inactives
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h6 class="mb-3">
                                <i class="material-icons" style="vertical-align: middle;">pie_chart</i>
                                Répartition par Famille
                            </h6>
                            <div class="chart-wrapper">
                                <canvas id="famillesPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h6 class="mb-3">
                                <i class="material-icons" style="vertical-align: middle;">bar_chart</i>
                                Top 10 Familles
                            </h6>
                            <div class="chart-wrapper">
                                <canvas id="famillesBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Familles Grid -->
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <i class="material-icons" style="vertical-align: middle;">view_module</i>
                            Vue Détaillée des Familles
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="sortFamilles('nom')">
                                <i class="material-icons">sort_by_alpha</i> Nom
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="sortFamilles('elements')">
                                <i class="material-icons">sort</i> Éléments
                            </button>
                        </div>
                    </div>

                    <div class="family-grid" id="famillesGrid">
                        <!-- Données chargées dynamiquement -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <p class="mt-3 text-muted">Chargement des familles...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="plugins/js/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>

    <script>
        // Variables globales
        let famillesData = [];
        let filteredFamillesData = [];
        let currentFilter = 'all';
        let currentSort = 'elements';
        let pieChart = null;
        let barChart = null;

        // Chargement des données
        async function loadFamillesData() {
            try {
                const response = await fetch('api/familles_stats.php');
                const data = await response.json();

                if (data.success) {
                    famillesData = data.stats;
                    filteredFamillesData = [...famillesData.familles_details];

                    updateStats();
                    updateInsights();
                    renderFamillesGrid();
                    createCharts();
                } else {
                    throw new Error(data.message || 'Erreur inconnue');
                }
            } catch (error) {
                console.error('Erreur lors du chargement des données:', error);
                showError('Erreur lors du chargement des données: ' + error.message);
            }
        }

        // Mise à jour des statistiques
        function updateStats() {
            document.getElementById('totalFamillesDefinies').textContent = famillesData.total_familles_definies || '0';
            document.getElementById('totalFamillesUtilisees').textContent = famillesData.total_familles_utilisees || '0';
            document.getElementById('totalElements').textContent = numberWithCommas(famillesData.total_elements || '0');

            // Famille leader
            if (famillesData.top_familles && famillesData.top_familles.length > 0) {
                document.getElementById('familleTop').textContent = famillesData.top_familles[0].famille;
            }
        }

        // Mise à jour des insights
        function updateInsights() {
            const insightsList = document.getElementById('insightsList');
            const insights = generateInsights();

            insightsList.innerHTML = insights.map(insight => `
                <div class="insight-item">
                    <div class="insight-icon">
                        <i class="material-icons">${insight.icon}</i>
                    </div>
                    <div>
                        <strong>${insight.title}</strong>
                        <div class="text-muted">${insight.description}</div>
                    </div>
                </div>
            `).join('');
        }

        // Génération d'insights intelligents
        function generateInsights() {
            const insights = [];

            // Taux d'utilisation
            const tauxUtilisation = (famillesData.total_familles_utilisees / famillesData.total_familles_definies * 100).toFixed(1);
            insights.push({
                icon: 'analytics',
                title: `Taux d'utilisation: ${tauxUtilisation}%`,
                description: `${famillesData.total_familles_utilisees} familles sur ${famillesData.total_familles_definies} contiennent des données`
            });

            // Famille dominante
            if (famillesData.top_familles && famillesData.top_familles.length > 0) {
                const topFamille = famillesData.top_familles[0];
                const pourcentage = (topFamille.count_elements / famillesData.total_elements * 100).toFixed(1);
                insights.push({
                    icon: 'star',
                    title: `${topFamille.famille} domine avec ${pourcentage}%`,
                    description: `${numberWithCommas(topFamille.count_elements)} éléments sur ${numberWithCommas(famillesData.total_elements)} total`
                });
            }

            // Familles non utilisées
            if (famillesData.familles_non_utilisees && famillesData.familles_non_utilisees.length > 0) {
                insights.push({
                    icon: 'warning',
                    title: `${famillesData.familles_non_utilisees.length} familles sans données`,
                    description: 'Ces familles pourraient être supprimées ou nécessiter un import'
                });
            }

            return insights;
        }

        // Rendu de la grille des familles
        function renderFamillesGrid() {
            const grid = document.getElementById('famillesGrid');

            if (filteredFamillesData.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state col-12">
                        <i class="material-icons">search_off</i>
                        <h5>Aucune famille trouvée</h5>
                        <p>Essayez de modifier vos filtres de recherche</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = filteredFamillesData.map(famille => `
                <div class="famille-card ${famille.status}" data-famille="${famille.famille_nom}" data-status="${famille.status}">
                    <div class="famille-header">
                        <h4 class="famille-name">${famille.famille_nom}</h4>
                        <span class="famille-status status-${famille.status}">
                            ${famille.status === 'active' ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                    
                    <div class="famille-stats">
                        <div class="mini-stat">
                            <div class="mini-stat-number">${numberWithCommas(famille.nb_elements)}</div>
                            <div class="mini-stat-label">Éléments</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-number">${famille.nb_reperes_uniques || 0}</div>
                            <div class="mini-stat-label">Repères</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-number">${famille.nb_unites_differentes || 0}</div>
                            <div class="mini-stat-label">Unités</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-number">${numberWithCommas(famille.total_quantite || 0)}</div>
                            <div class="mini-stat-label">Quantité Total</div>
                        </div>
                    </div>

                    ${famille.status === 'active' ? `
                        <div class="mt-3">
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Quantité moyenne: ${Math.round(famille.moyenne_quantite || 0)}</span>
                                <span>Max: ${famille.max_quantite || 0}</span>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-gradient" style="width: ${(famille.nb_elements / (famillesData.top_familles[0]?.count_elements || 1) * 100)}%; background: linear-gradient(45deg, #667eea, #764ba2);"></div>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `).join('');

            // Animation d'apparition
            setTimeout(() => {
                document.querySelectorAll('.famille-card').forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.transition = 'all 0.5s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }, 50);
        }

        // Création des graphiques
        function createCharts() {
            // Détruire les anciens graphiques s'ils existent
            if (pieChart) {
                pieChart.destroy();
                pieChart = null;
            }
            if (barChart) {
                barChart.destroy();
                barChart = null;
            }

            createPieChart();
            createBarChart();
        }

        // Graphique en secteurs
        function createPieChart() {
            const ctx = document.getElementById('famillesPieChart');
            if (!famillesData.top_familles || famillesData.top_familles.length === 0) return;

            const topFamilles = famillesData.top_familles.slice(0, 5);

            pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: topFamilles.map(f => f.famille),
                    datasets: [{
                        data: topFamilles.map(f => f.count_elements),
                        backgroundColor: [
                            '#667eea', '#764ba2', '#28a745', '#ffc107', '#dc3545'
                        ],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    },
                    layout: {
                        padding: 10
                    }
                }
            });
        }

        // Graphique en barres
        function createBarChart() {
            const ctx = document.getElementById('famillesBarChart');
            if (!famillesData.top_familles || famillesData.top_familles.length === 0) return;

            const topFamilles = famillesData.top_familles.slice(0, 10);

            barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topFamilles.map(f => f.famille),
                    datasets: [{
                        label: 'Nombre d\'éléments',
                        data: topFamilles.map(f => f.count_elements),
                        backgroundColor: '#667eea',
                        borderRadius: 5,
                        borderSkipped: false,
                        hoverBackgroundColor: '#764ba2'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    layout: {
                        padding: 10
                    }
                }
            });
        }

        // Filtrage des familles
        function filterFamilles(filter) {
            currentFilter = filter;

            filteredFamillesData = famillesData.familles_details.filter(famille => {
                if (filter === 'all') return true;
                return famille.status === filter;
            });

            applySorting();
            renderFamillesGrid();
            updateFilterButtons();
        }

        // Recherche de familles
        function searchFamilles(searchTerm) {
            filteredFamillesData = famillesData.familles_details.filter(famille => {
                const matchesSearch = famille.famille_nom.toLowerCase().includes(searchTerm.toLowerCase());
                const matchesFilter = currentFilter === 'all' || famille.status === currentFilter;
                return matchesSearch && matchesFilter;
            });

            applySorting();
            renderFamillesGrid();
        }

        // Tri des familles
        function sortFamilles(sortBy) {
            currentSort = sortBy;
            applySorting();
            renderFamillesGrid();
        }

        function applySorting() {
            filteredFamillesData.sort((a, b) => {
                if (currentSort === 'nom') {
                    return a.famille_nom.localeCompare(b.famille_nom);
                } else {
                    return b.nb_elements - a.nb_elements;
                }
            });
        }

        // Mise à jour des boutons de filtre
        function updateFilterButtons() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.filter === currentFilter) {
                    btn.classList.add('active');
                }
            });
        }

        // Fonctions utilitaires
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function showError(message) {
            const grid = document.getElementById('famillesGrid');
            grid.innerHTML = `
                <div class="empty-state col-12">
                    <i class="material-icons text-danger">error</i>
                    <h5>Erreur</h5>
                    <p>${message}</p>
                </div>
            `;
        }

        function refreshData() {
            document.getElementById('famillesGrid').innerHTML = `
                <div class="text-center py-5 col-12">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Actualisation...</span>
                    </div>
                    <p class="mt-3 text-muted">Actualisation des données...</p>
                </div>
            `;
            loadFamillesData();
        }

        function exportFamilleData() {
            window.open('api/export_familles.php', '_blank');
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            loadFamillesData();

            // Filtres
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    filterFamilles(btn.dataset.filter);
                });
            });

            // Recherche avec debounce
            let searchTimeout;
            document.getElementById('searchFamilles').addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchFamilles(e.target.value);
                }, 300);
            });
        });

        // Fonctions globales
        window.refreshData = refreshData;
        window.exportFamilleData = exportFamilleData;
        window.sortFamilles = sortFamilles;
    </script>
</body>

</html>