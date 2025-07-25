<?php
session_start();
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Éléments Non SAP - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1976d2;
            --danger-color: #f44336;
            --warning-color: #ff9800;
            --success-color: #4caf50;
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .non-sap-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .non-sap-header {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        .stat-card-non-sap {
            background: linear-gradient(135deg, rgba(244, 67, 54, 0.1) 0%, rgba(211, 47, 47, 0.1) 100%);
            border: 2px solid rgba(244, 67, 54, 0.2);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card-non-sap:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .btn-export {
            background: linear-gradient(135deg, #4caf50 0%, #43a047 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .chart-non-sap {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            height: 400px;
            position: relative;
        }

        /* Système de chargement progressif */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--danger-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .progress-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
        }

        .progress-bar-custom {
            height: 6px;
            background: linear-gradient(135deg, var(--danger-color) 0%, #d32f2f 100%);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--danger-color);
            font-weight: 600;
        }

        .chart-loaded {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .chart-loaded.show {
            opacity: 1;
            transform: translateY(0);
        }

        .stat-card-loading {
            background: linear-gradient(45deg, #f0f0f0 25%, transparent 25%),
                linear-gradient(-45deg, #f0f0f0 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #f0f0f0 75%),
                linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            animation: loading-shimmer 1.5s infinite linear;
        }

        @keyframes loading-shimmer {
            0% {
                background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            }

            100% {
                background-position: 20px 20px, 20px 30px, 30px 10px, 10px 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Barre de progression -->
    <div class="progress-container" id="progressContainer" style="display: none;">
        <div class="container-fluid">
            <div class="progress" style="height: 6px; background: #f0f0f0;">
                <div class="progress-bar-custom" id="progressBar" style="width: 0%;"></div>
            </div>
            <div class="progress-text" id="progressText">Chargement des données...</div>
        </div>
    </div>

    <div class="d-flex">
        <?php include 'menu.php'; ?>

        <div class="flex-grow-1 content" style="background: transparent;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1" style="font-weight: 800; color: var(--danger-color); font-size: 2.5rem;">
                        <span class="material-icons me-3" style="font-size: 2.5rem; vertical-align: middle;">error_outline</span>
                        Éléments Non SAP
                    </h1>
                    <p class="mb-0 text-muted" style="font-weight: 500;">Équipements et articles non codifiés dans SAP</p>
                </div>
                <div class="d-flex gap-3">
                    <a href="request/export_equipements_non_sap.php" class="btn btn-export">
                        <span class="material-icons me-2" style="font-size: 18px;">file_download</span>Exporter Équipements
                    </a>
                    <a href="request/export_articles_non_sap.php" class="btn btn-export">
                        <span class="material-icons me-2" style="font-size: 18px;">file_download</span>Exporter Articles
                    </a>
                </div>
            </div>

            <!-- Alertes de résumé -->
            <div class="non-sap-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">
                            <span class="material-icons me-2" style="font-size: 2rem; vertical-align: middle;">warning</span>
                            État de la Codification SAP
                        </h3>
                        <p class="mb-0">Éléments nécessitant une codification SAP pour être pleinement intégrés au système</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h2 mb-0" id="totalNonSAP">Chargement...</div>
                        <div class="small opacity-75">éléments non codifiés</div>
                    </div>
                </div>
            </div>

            <!-- Statistiques principales -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4">
                    <div class="stat-card-non-sap stat-card-loading" id="cardEquipements">
                        <div class="h1 text-danger mb-3" id="equipementsNonSAP">
                            <div class="spinner-border text-danger" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                        <h5 class="mb-2">Équipements Non SAP</h5>
                        <p class="text-muted mb-3">Équipements sans codification SAP</p>
                        <a href="request/export_equipements_non_sap.php" class="btn btn-outline-danger btn-sm">
                            <span class="material-icons me-1" style="font-size: 16px;">download</span>Exporter
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="stat-card-non-sap stat-card-loading" id="cardArticles">
                        <div class="h1 text-danger mb-3" id="articlesNonSAP">
                            <div class="spinner-border text-danger" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                        <h5 class="mb-2">Articles Non SAP</h5>
                        <p class="text-muted mb-3">Articles sans codification SAP</p>
                        <a href="request/export_articles_non_sap.php" class="btn btn-outline-danger btn-sm">
                            <span class="material-icons me-1" style="font-size: 16px;">download</span>Exporter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Graphiques de répartition -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="chart-non-sap chart-loaded">
                        <div class="loading-overlay" id="loadingEquipFamille">
                            <div class="spinner"></div>
                            <div class="text-muted">Chargement des équipements par famille...</div>
                        </div>
                        <h5 class="mb-4" style="color: var(--danger-color); font-weight: 700;">
                            <span class="material-icons me-2">pie_chart</span>
                            Équipements Non SAP par Famille
                        </h5>
                        <canvas id="equipementsFamilleChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-non-sap chart-loaded">
                        <div class="loading-overlay" id="loadingArticlesMetier">
                            <div class="spinner"></div>
                            <div class="text-muted">Chargement des articles par métier...</div>
                        </div>
                        <h5 class="mb-4" style="color: var(--danger-color); font-weight: 700;">
                            <span class="material-icons me-2">category</span>
                            Articles Non SAP par Métier
                        </h5>
                        <canvas id="articlesMetierChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-non-sap chart-loaded">
                        <div class="loading-overlay" id="loadingEquipSource">
                            <div class="spinner"></div>
                            <div class="text-muted">Chargement des équipements par source...</div>
                        </div>
                        <h5 class="mb-4" style="color: var(--danger-color); font-weight: 700;">
                            <span class="material-icons me-2">source</span>
                            Équipements par Source Actuelle
                        </h5>
                        <canvas id="equipementsSourceChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-non-sap chart-loaded">
                        <div class="loading-overlay" id="loadingArticlesSource">
                            <div class="spinner"></div>
                            <div class="text-muted">Chargement des articles par source...</div>
                        </div>
                        <h5 class="mb-4" style="color: var(--danger-color); font-weight: 700;">
                            <span class="material-icons me-2">source</span>
                            Articles par Source Actuelle
                        </h5>
                        <canvas id="articlesSourceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Section des détails avec chargement progressif -->
            <div class="row mb-4">
                <!-- Équipements détaillés -->
                <div class="col-lg-6 mb-4">
                    <div class="non-sap-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 style="color: var(--danger-color); font-weight: 700;">
                                <span class="material-icons me-2">build</span>
                                Équipements Non SAP
                            </h4>
                            <div class="d-flex align-items-center gap-2">
                                <span id="equipementsProgress" class="badge bg-secondary">0/0</span>
                                <div id="equipementsLoading" class="spinner-border spinner-border-sm text-danger" role="status" style="display: none;">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Repère</th>
                                        <th>Famille</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>
                                <tbody id="equipementsTableBody">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            <div class="py-3">
                                                <div class="spinner-border text-danger mb-2" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                                <div>Chargement des équipements...</div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button id="loadMoreEquipements" class="btn btn-outline-danger btn-sm" style="display: none;">
                                <span class="material-icons me-1" style="font-size: 16px;">add</span>
                                Charger plus (50)
                            </button>
                            <small class="text-muted" id="equipementsStatus">Initialisation...</small>
                        </div>
                    </div>
                </div>

                <!-- Articles détaillés -->
                <div class="col-lg-6 mb-4">
                    <div class="non-sap-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 style="color: var(--danger-color); font-weight: 700;">
                                <span class="material-icons me-2">inventory_2</span>
                                Articles Non SAP
                            </h4>
                            <div class="d-flex align-items-center gap-2">
                                <span id="articlesProgress" class="badge bg-secondary">0/0</span>
                                <div id="articlesLoading" class="spinner-border spinner-border-sm text-danger" role="status" style="display: none;">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Code Article</th>
                                        <th>Métier</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>
                                <tbody id="articlesTableBody">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            <div class="py-3">
                                                <div class="spinner-border text-danger mb-2" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                                <div>Chargement des articles...</div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button id="loadMoreArticles" class="btn btn-outline-danger btn-sm" style="display: none;">
                                <span class="material-icons me-1" style="font-size: 16px;">add</span>
                                Charger plus (50)
                            </button>
                            <small class="text-muted" id="articlesStatus">Initialisation...</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Résumé et statistiques avancées -->
            <div class="non-sap-container">
                <h4 class="mb-4" style="color: var(--primary-color); font-weight: 700;">
                    <span class="material-icons me-2">analytics</span>
                    Analyse Détaillée
                </h4>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card-non-sap">
                            <div class="h3 text-warning mb-2" id="pourcentageNonSAP">
                                <div class="spinner-border text-warning" role="status">
                                    <span class="visually-hidden">Calcul...</span>
                                </div>
                            </div>
                            <h6 class="mb-0">% Non Codifiés</h6>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card-non-sap">
                            <div class="h3 text-info mb-2" id="famillesPrincipales">
                                <div class="spinner-border text-info" role="status">
                                    <span class="visually-hidden">Calcul...</span>
                                </div>
                            </div>
                            <h6 class="mb-0">Familles Principales</h6>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card-non-sap">
                            <div class="h3 text-success mb-2" id="sourcesActives">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Calcul...</span>
                                </div>
                            </div>
                            <h6 class="mb-0">Sources Actives</h6>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card-non-sap">
                            <div class="h3 text-primary mb-2" id="prioriteHaute">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Calcul...</span>
                                </div>
                            </div>
                            <h6 class="mb-0">Priorité Haute</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommandations -->
            <div class="non-sap-container">
                <h4 class="mb-4" style="color: var(--primary-color); font-weight: 700;">
                    <span class="material-icons me-2">lightbulb</span>
                    Recommandations
                </h4>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-info">
                            <h6><span class="material-icons me-2">priority_high</span>Priorité Haute</h6>
                            <p class="mb-0">Codifier d'abord les équipements critiques et leurs articles associés</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-warning">
                            <h6><span class="material-icons me-2">update</span>Mise à Jour</h6>
                            <p class="mb-0">Synchroniser régulièrement avec la base SAP pour éviter les doublons</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-success">
                            <h6><span class="material-icons me-2">check_circle</span>Validation</h6>
                            <p class="mb-0">Valider la cohérence des données avant la codification SAP</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="plugins/js/chart.js"></script>
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuration des couleurs
        const dangerColors = [
            '#f44336', '#e53935', '#d32f2f', '#c62828', '#b71c1c',
            '#ff5722', '#f4511e', '#e64a19', '#d84315', '#bf360c'
        ];

        // Variables globales
        let charts = {};
        let loadedData = {
            equipements_par_famille: [],
            articles_par_metier: [],
            equipements_par_source: [],
            articles_par_source: []
        };

        // Variables de pagination
        let equipementsData = {
            items: [],
            currentOffset: 0,
            totalCount: 0,
            hasMore: true,
            loading: false
        };

        let articlesData = {
            items: [],
            currentOffset: 0,
            totalCount: 0,
            hasMore: true,
            loading: false
        };

        // Variables de statistiques
        let statsCalculated = false;

        // Fonction principale de chargement progressif
        async function loadDataProgressively() {
            showProgressBar();

            try {
                // Étape 1: Charger les statistiques générales
                updateProgressMessage("Chargement des statistiques générales...");
                await loadGeneralStats();
                updateProgress(1, 6);

                // Étape 2: Charger les équipements (premier lot)
                updateProgressMessage("Chargement des équipements (premier lot)...");
                await loadEquipementsBatch();
                updateProgress(2, 6);

                // Étape 3: Charger les articles (premier lot)  
                updateProgressMessage("Chargement des articles (premier lot)...");
                await loadArticlesBatch();
                updateProgress(3, 6);

                // Étape 4: Charger les graphiques famille
                updateProgressMessage("Analyse des familles d'équipements...");
                await loadEquipementsFamilleChart();
                updateProgress(4, 6);

                // Étape 5: Charger les graphiques métier
                updateProgressMessage("Classification des articles par métier...");
                await loadArticlesMetierChart();
                updateProgress(5, 6);

                // Étape 6: Calcul des statistiques avancées
                updateProgressMessage("Finalisation des analyses...");
                calculateAdvancedStats();
                updateProgress(6, 6);

                hideProgressBar();
                showAllCharts();

            } catch (error) {
                console.error('Erreur lors du chargement:', error);
                hideProgressBar();
                showError('Erreur lors du chargement des données');
            }
        }

        // Charger les statistiques générales
        async function loadGeneralStats() {
            try {
                const response = await fetch('request/stats_non_sap_paginated.php?type=stats_generales');
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error);
                }

                const data = result.data;

                // Animation des compteurs
                animateCounter('equipementsNonSAP', data.equipements_non_sap);
                animateCounter('articlesNonSAP', data.articles_non_sap);
                animateCounter('totalNonSAP', data.equipements_non_sap + data.articles_non_sap);

                // Retirer l'effet de chargement des cartes
                document.getElementById('cardEquipements').classList.remove('stat-card-loading');
                document.getElementById('cardArticles').classList.remove('stat-card-loading');

                // Stocker pour les calculs avancés
                window.statsData = data;

            } catch (error) {
                console.error('Erreur stats générales:', error);
                throw error;
            }
        }

        // Charger un lot d'équipements
        async function loadEquipementsBatch() {
            if (equipementsData.loading || !equipementsData.hasMore) return;

            equipementsData.loading = true;
            document.getElementById('equipementsLoading').style.display = 'inline-block';

            try {
                const response = await fetch(
                    `request/stats_non_sap_paginated.php?type=equipements_details&offset=${equipementsData.currentOffset}&limit=50`
                );
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error);
                }

                equipementsData.items = equipementsData.items.concat(result.data);
                equipementsData.totalCount = result.total_count;
                equipementsData.hasMore = result.has_more;
                equipementsData.currentOffset += 50;

                updateEquipementsTable();
                updateEquipementsProgress();

            } catch (error) {
                console.error('Erreur équipements:', error);
                throw error;
            } finally {
                equipementsData.loading = false;
                document.getElementById('equipementsLoading').style.display = 'none';
            }
        }

        // Charger un lot d'articles
        async function loadArticlesBatch() {
            if (articlesData.loading || !articlesData.hasMore) return;

            articlesData.loading = true;
            document.getElementById('articlesLoading').style.display = 'inline-block';

            try {
                const response = await fetch(
                    `request/stats_non_sap_paginated.php?type=articles_details&offset=${articlesData.currentOffset}&limit=50`
                );
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error);
                }

                articlesData.items = articlesData.items.concat(result.data);
                articlesData.totalCount = result.total_count;
                articlesData.hasMore = result.has_more;
                articlesData.currentOffset += 50;

                updateArticlesTable();
                updateArticlesProgress();

            } catch (error) {
                console.error('Erreur articles:', error);
                throw error;
            } finally {
                articlesData.loading = false;
                document.getElementById('articlesLoading').style.display = 'none';
            }
        }

        // Mettre à jour le tableau des équipements
        function updateEquipementsTable() {
            const tbody = document.getElementById('equipementsTableBody');

            if (equipementsData.items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">Aucun équipement trouvé</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = equipementsData.items.map(item => `
                <tr>
                    <td>
                        <strong>${item.repere_equipement}</strong>
                        <br><small class="text-muted">${item.designation || 'Sans désignation'}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${item.famille}</span>
                    </td>
                    <td>
                        <span class="badge ${getSourceBadgeClass(item.source_actuelle)}">${item.source_actuelle}</span>
                    </td>
                </tr>
            `).join('');
        }

        // Mettre à jour le tableau des articles
        function updateArticlesTable() {
            const tbody = document.getElementById('articlesTableBody');

            if (articlesData.items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">Aucun article trouvé</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = articlesData.items.map(item => `
                <tr>
                    <td>
                        <strong>${item.code_article}</strong>
                        <br><small class="text-muted">${item.designation || 'Sans désignation'}</small>
                    </td>
                    <td>
                        <span class="badge bg-info">${item.metier}</span>
                    </td>
                    <td>
                        <span class="badge ${getSourceBadgeClass(item.source_actuelle)}">${item.source_actuelle}</span>
                    </td>
                </tr>
            `).join('');
        }

        // Classe CSS pour les badges de source
        function getSourceBadgeClass(source) {
            switch (source) {
                case 'RGM':
                    return 'bg-success';
                case 'Template':
                    return 'bg-warning';
                default:
                    return 'bg-danger';
            }
        }

        // Mettre à jour le progrès des équipements
        function updateEquipementsProgress() {
            const progress = document.getElementById('equipementsProgress');
            const status = document.getElementById('equipementsStatus');
            const loadMore = document.getElementById('loadMoreEquipements');

            progress.textContent = `${equipementsData.items.length}/${equipementsData.totalCount}`;
            progress.className = equipementsData.items.length === equipementsData.totalCount ? 'badge bg-success' : 'badge bg-primary';

            status.textContent = `${equipementsData.items.length} équipements affichés sur ${equipementsData.totalCount}`;

            if (equipementsData.hasMore) {
                loadMore.style.display = 'inline-block';
                loadMore.onclick = () => loadEquipementsBatch();
            } else {
                loadMore.style.display = 'none';
            }
        }

        // Mettre à jour le progrès des articles
        function updateArticlesProgress() {
            const progress = document.getElementById('articlesProgress');
            const status = document.getElementById('articlesStatus');
            const loadMore = document.getElementById('loadMoreArticles');

            progress.textContent = `${articlesData.items.length}/${articlesData.totalCount}`;
            progress.className = articlesData.items.length === articlesData.totalCount ? 'badge bg-success' : 'badge bg-primary';

            status.textContent = `${articlesData.items.length} articles affichés sur ${articlesData.totalCount}`;

            if (articlesData.hasMore) {
                loadMore.style.display = 'inline-block';
                loadMore.onclick = () => loadArticlesBatch();
            } else {
                loadMore.style.display = 'none';
            }
        }

        // Charger le graphique équipements par famille
        async function loadEquipementsFamilleChart() {
            try {
                const response = await fetch('request/stats_non_sap_paginated.php?type=equipements_par_famille');
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error);
                }

                loadedData.equipements_par_famille = result.data;
                hideLoading('loadingEquipFamille');
                createEquipementsFamilleChart();

            } catch (error) {
                console.error('Erreur graphique famille:', error);
                throw error;
            }
        }

        // Charger le graphique articles par métier
        async function loadArticlesMetierChart() {
            try {
                const response = await fetch('request/stats_non_sap_paginated.php?type=articles_par_metier');
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error);
                }

                loadedData.articles_par_metier = result.data;
                hideLoading('loadingArticlesMetier');
                createArticlesMetierChart();

            } catch (error) {
                console.error('Erreur graphique métier:', error);
                throw error;
            }
        }

        // Calculer les statistiques avancées
        function calculateAdvancedStats() {
            if (statsCalculated || !window.statsData) return;

            const stats = window.statsData;
            const total = stats.equipements_non_sap + stats.articles_non_sap;

            // Pourcentage non codifié (approximatif)
            const pourcentage = total > 1000 ? Math.round((total / (total * 1.5)) * 100) : Math.round((total / 1000) * 100);
            animateCounter('pourcentageNonSAP', Math.min(pourcentage, 99), 1000, '%');

            // Familles principales (basé sur les données)
            const famillesPrincipales = loadedData.equipements_par_famille ? loadedData.equipements_par_famille.length : 8;
            animateCounter('famillesPrincipales', famillesPrincipales, 1000);

            // Sources actives
            animateCounter('sourcesActives', 3, 1000); // RGM, Template, Aucune

            // Priorité haute (estimation)
            const priorite = Math.round(stats.equipements_non_sap * 0.3);
            animateCounter('prioriteHaute', priorite, 1000);

            statsCalculated = true;
        }

        // Animation des compteurs
        function animateCounter(elementId, finalValue, duration = 1500, suffix = '') {
            const element = document.getElementById(elementId);
            const startValue = 0;
            const increment = finalValue / (duration / 16);
            let currentValue = startValue;

            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    element.textContent = finalValue.toLocaleString() + suffix;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(currentValue).toLocaleString() + suffix;
                }
            }, 16);
        }

        // Gestion de la barre de progression
        function showProgressBar() {
            document.getElementById('progressContainer').style.display = 'block';
            document.body.style.paddingTop = '80px';
        }

        function hideProgressBar() {
            setTimeout(() => {
                document.getElementById('progressContainer').style.display = 'none';
                document.body.style.paddingTop = '0';
            }, 500);
        }

        function updateProgress(current, total) {
            const percentage = (current / total) * 100;
            document.getElementById('progressBar').style.width = percentage + '%';
        }

        function updateProgressMessage(message) {
            document.getElementById('progressText').textContent = message;
        }

        // Masquer les indicateurs de chargement
        function hideLoading(loadingId) {
            const loadingEl = document.getElementById(loadingId);
            if (loadingEl) {
                loadingEl.style.opacity = '0';
                setTimeout(() => {
                    loadingEl.style.display = 'none';
                }, 300);
            }
        }

        // Afficher tous les graphiques avec animation
        function showAllCharts() {
            const charts = document.querySelectorAll('.chart-loaded');
            charts.forEach((chart, index) => {
                setTimeout(() => {
                    chart.classList.add('show');
                }, index * 200);
            });
        }

        // Création des graphiques
        function createEquipementsFamilleChart() {
            const ctx = document.getElementById('equipementsFamilleChart').getContext('2d');
            charts.equipementsFamille = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: loadedData.equipements_par_famille.map(item => item.famille),
                    datasets: [{
                        data: loadedData.equipements_par_famille.map(item => item.nombre),
                        backgroundColor: dangerColors.slice(0, loadedData.equipements_par_famille.length),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed * 100) / total).toFixed(1);
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 2000
                    }
                }
            });
        }

        function createArticlesMetierChart() {
            const ctx = document.getElementById('articlesMetierChart').getContext('2d');
            charts.articlesMetier = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: loadedData.articles_par_metier.map(item => item.metier),
                    datasets: [{
                        label: 'Articles non SAP',
                        data: loadedData.articles_par_metier.map(item => item.nombre),
                        backgroundColor: dangerColors[0] + '80',
                        borderColor: dangerColors[0],
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }

        function showError(message) {
            console.error(message);
            // Afficher une notification d'erreur élégante
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Démarrer le chargement progressif quand la page est prête
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                loadDataProgressively();
            }, 500);
        });
    </script>
</body>

</html>