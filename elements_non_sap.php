<?php
session_start();
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Éléments Non SAP - Nomenclature Équipements</title>
    <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-inline'; object-src 'none';">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <link href="css/elements_non_sap.css" rel="stylesheet">
</head>

<body>
    <!-- Barre de progression -->
    <div class="progress-container" id="progressContainer" style="display: block;">
        <div class="container-fluid">
            <div class="progress" style="height: 6px; background: #f0f0f0;">
                <div class="progress-bar-custom" id="progressBar" style="width: 0%; background: var(--danger-color); transition: width 0.3s ease;"></div>
            </div>
            <div class="progress-text" id="progressText">⚡ Chargement ultra-rapide en cours...</div>
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
                    <div class="stat-card-non-sap" id="cardEquipements">
                        <div class="h1 text-danger mb-3" id="equipementsNonSAP">
                            <span class="text-muted">Calcul...</span>
                        </div>
                        <h5 class="mb-2">Équipements Non SAP</h5>
                        <p class="text-muted mb-3">Équipements sans codification SAP</p>
                        <a href="request/export_equipements_non_sap.php" class="btn btn-outline-danger btn-sm">
                            <span class="material-icons me-1" style="font-size: 16px;">download</span>Exporter
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="stat-card-non-sap" id="cardArticles">
                        <div class="h1 text-danger mb-3" id="articlesNonSAP">
                            <span class="text-muted">Calcul...</span>
                        </div>
                        <h5 class="mb-2">Articles Non SAP</h5>
                        <p class="text-muted mb-3">Articles sans codification SAP</p>
                        <a href="request/export_articles_non_sap.php" class="btn btn-outline-danger btn-sm">
                            <span class="material-icons me-1" style="font-size: 16px;">download</span>Exporter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Section de statut de chargement - s'affiche immédiatement -->
            <div class="non-sap-container mb-4" id="loadingStatusSection">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-3" style="color: var(--primary-color); font-weight: 700;">
                            <span class="material-icons me-2">data_usage</span>
                            État du Chargement des Données
                        </h4>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div id="statusStats" class="spinner-border spinner-border-sm text-success me-2" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <span id="statusStatsText">Statistiques générales</span>
                                    <span id="statusStatsIcon" class="material-icons ms-auto text-muted" style="display: none;">check_circle</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div id="statusEquipements" class="spinner-border spinner-border-sm text-warning me-2" role="status" style="display: none;">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <span id="statusEquipementsText" class="text-muted">Équipements détaillés</span>
                                    <span id="statusEquipementsIcon" class="material-icons ms-auto text-muted" style="display: none;">check_circle</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div id="statusArticles" class="spinner-border spinner-border-sm text-info me-2" role="status" style="display: none;">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <span id="statusArticlesText" class="text-muted">Articles détaillés</span>
                                    <span id="statusArticlesIcon" class="material-icons ms-auto text-muted" style="display: none;">check_circle</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div id="statusCharts" class="spinner-border spinner-border-sm text-danger me-2" role="status" style="display: none;">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <span id="statusChartsText" class="text-muted">Graphiques d'analyse</span>
                                    <span id="statusChartsIcon" class="material-icons ms-auto text-muted" style="display: none;">check_circle</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="position-relative d-inline-block">
                            <svg width="120" height="120" class="progress-ring">
                                <circle cx="60" cy="60" r="50" stroke="#e9ecef" stroke-width="8" fill="transparent"></circle>
                                <circle id="progressRingCircle" cx="60" cy="60" r="50" stroke="var(--danger-color)" stroke-width="8"
                                    fill="transparent" stroke-dasharray="314" stroke-dashoffset="314"
                                    style="transform-origin: center; transform: rotate(-90deg); transition: stroke-dashoffset 0.5s ease;"></circle>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <h4 id="globalProgress" class="mb-0 text-danger">0%</h4>
                                <small class="text-muted">Terminé</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aperçu rapide des données - s'affiche pendant le chargement -->
            <div id="quickPreviewSection" class="mb-4">
                <div class="row">
                    <div class="col-lg-3 mb-3">
                        <div class="stat-card-non-sap">
                            <div class="h4 text-info mb-2" id="previewFamilles">
                                <span class="text-muted">...</span>
                            </div>
                            <h6 class="mb-0">Familles d'Équipements</h6>
                            <small class="text-muted">Types identifiés</small>
                        </div>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <div class="stat-card-non-sap">
                            <div class="h4 text-warning mb-2" id="previewMetiers">
                                <span class="text-muted">...</span>
                            </div>
                            <h6 class="mb-0">Métiers d'Articles</h6>
                            <small class="text-muted">Catégories identifiées</small>
                        </div>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <div class="stat-card-non-sap">
                            <div class="h4 text-success mb-2" id="previewSources">
                                <span class="text-muted">...</span>
                            </div>
                            <h6 class="mb-0">Sources Actives</h6>
                            <small class="text-muted">Systèmes détectés</small>
                        </div>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <div class="stat-card-non-sap">
                            <div class="h4 text-primary mb-2" id="previewStatus">
                                <span class="material-icons">hourglass_empty</span>
                            </div>
                            <h6 class="mb-0">Statut Global</h6>
                            <small class="text-muted" id="previewStatusText">Analyse en cours...</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section de préparation des graphiques - visible immédiatement -->
            <div class="non-sap-container mb-4" id="chartsPreparationSection">
                <h4 class="mb-4" style="color: var(--primary-color); font-weight: 700;">
                    <span class="material-icons me-2">timeline</span>
                    Analyse Graphique en Préparation
                </h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-info me-3" role="status">
                                    <span class="visually-hidden">Préparation...</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Répartition par Famille</h6>
                                    <small class="text-muted">Classification des équipements en cours...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-warning me-3" role="status">
                                    <span class="visually-hidden">Préparation...</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Analyse par Métier</h6>
                                    <small class="text-muted">Catégorisation des articles en cours...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-success me-3" role="status">
                                    <span class="visually-hidden">Préparation...</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Sources d'Équipements</h6>
                                    <small class="text-muted">Identification des sources en cours...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-danger me-3" role="status">
                                    <span class="visually-hidden">Préparation...</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Sources d'Articles</h6>
                                    <small class="text-muted">Traçabilité des articles en cours...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <div class="text-muted">
                        <span class="material-icons me-1" style="vertical-align: middle; font-size: 18px;">info</span>
                        Les graphiques apparaîtront automatiquement une fois les données analysées
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

                        <div class="table-responsive" style="max-height: 600px;">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark sticky-top">
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
                                                <div class="spinner-border text-danger mb-2" role="status" style="width: 1.5rem; height: 1.5rem;">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                                <div>Préparation des équipements...</div>
                                                <small class="text-muted">100 premiers éléments en cours de chargement</small>
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

                        <div class="table-responsive" style="max-height: 600px;">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark sticky-top">
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
                                                <div class="spinner-border text-danger mb-2" role="status" style="width: 1.5rem; height: 1.5rem;">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                                <div>Préparation des articles...</div>
                                                <small class="text-muted">100 premiers éléments en cours de chargement</small>
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

            <!-- Section graphiques supprimée pour alléger la page -->
            <div class="text-center text-muted py-4" id="graphicsDisabledSection">
                <span class="material-icons me-2" style="font-size: 2rem;">analytics_off</span>
                <h5>Graphiques désactivés pour des performances optimales</h5>
                <p>Les données sont disponibles dans les tableaux ci-dessus</p>
                <button id="enableGraphicsBtn" class="btn btn-primary mt-3">
                    <span class="material-icons me-2">analytics</span>
                    Activer les Graphiques
                </button>
            </div>

            <!-- Section graphiques - masquée par défaut -->
            <div id="graphicsSection" style="display: none;">
                <div class="non-sap-container mb-4">
                    <h4 class="mb-4" style="color: var(--primary-color); font-weight: 700;">
                        <span class="material-icons me-2">timeline</span>
                        Analyse Graphique
                        <button id="disableGraphicsBtn" class="btn btn-outline-secondary btn-sm float-end">
                            <span class="material-icons me-1" style="font-size: 16px;">close</span>
                            Masquer
                        </button>
                    </h4>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="chart-non-sap">
                            <div class="loading-overlay" id="loadingEquipFamille" style="display: block;">
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
                        <div class="chart-non-sap">
                            <div class="loading-overlay" id="loadingArticlesMetier" style="display: block;">
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
                        <div class="chart-non-sap">
                            <div class="loading-overlay" id="loadingEquipSource" style="display: block;">
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
                        <div class="chart-non-sap">
                            <div class="loading-overlay" id="loadingArticlesSource" style="display: block;">
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
            </div>

        </div>
    </div>

    <script src="plugins/js/chart.js"></script>
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script src="js/elements_non_sap_ultra_light.js"></script>
</body>

</html>