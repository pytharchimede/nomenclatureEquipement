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
        }
    </style>
</head>

<body>
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
                    <div class="stat-card-non-sap">
                        <div class="h1 text-danger mb-3" id="equipementsNonSAP">-</div>
                        <h5 class="mb-2">Équipements Non SAP</h5>
                        <p class="text-muted mb-3">Équipements sans codification SAP</p>
                        <a href="request/export_equipements_non_sap.php" class="btn btn-outline-danger btn-sm">
                            <span class="material-icons me-1" style="font-size: 16px;">download</span>Exporter
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="stat-card-non-sap">
                        <div class="h1 text-danger mb-3" id="articlesNonSAP">-</div>
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
                    <div class="chart-non-sap">
                        <h5 class="mb-4" style="color: var(--danger-color); font-weight: 700;">
                            <span class="material-icons me-2">pie_chart</span>
                            Équipements Non SAP par Famille
                        </h5>
                        <canvas id="equipementsFamilleChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-non-sap">
                        <h5 class="mb-4" style="color: var(--danger-color); font-weight: 700;">
                            <span class="material-icons me-2">category</span>
                            Articles Non SAP par Métier
                        </h5>
                        <canvas id="articlesMetierChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-non-sap">
                        <h5 class="mb-4" style="color: var(--danger-color); font-weight: 700;">
                            <span class="material-icons me-2">source</span>
                            Équipements par Source Actuelle
                        </h5>
                        <canvas id="equipementsSourceChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-non-sap">
                        <h5 class="mb-4" style="color: var(--danger-color); font-weight: 700;">
                            <span class="material-icons me-2">source</span>
                            Articles par Source Actuelle
                        </h5>
                        <canvas id="articlesSourceChart"></canvas>
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

        // Chargement des données
        fetch('request/stats_non_sap.php')
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    console.error('Erreur:', result.error);
                    return;
                }

                const data = result.data;

                // Mise à jour des statistiques
                document.getElementById('equipementsNonSAP').textContent = data.equipements_non_sap.toLocaleString();
                document.getElementById('articlesNonSAP').textContent = data.articles_non_sap.toLocaleString();
                document.getElementById('totalNonSAP').textContent =
                    (data.equipements_non_sap + data.articles_non_sap).toLocaleString();

                // Graphique équipements par famille
                const equipFamilleCtx = document.getElementById('equipementsFamilleChart').getContext('2d');
                new Chart(equipFamilleCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.equipements_par_famille.map(item => item.famille),
                        datasets: [{
                            data: data.equipements_par_famille.map(item => item.nombre),
                            backgroundColor: dangerColors.slice(0, data.equipements_par_famille.length),
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
                            }
                        }
                    }
                });

                // Graphique articles par métier
                const articlesMetierCtx = document.getElementById('articlesMetierChart').getContext('2d');
                new Chart(articlesMetierCtx, {
                    type: 'bar',
                    data: {
                        labels: data.articles_par_metier.map(item => item.metier),
                        datasets: [{
                            label: 'Articles non SAP',
                            data: data.articles_par_metier.map(item => item.nombre),
                            backgroundColor: dangerColors[0] + '80',
                            borderColor: dangerColors[0],
                            borderWidth: 2
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
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Graphique équipements par source
                const equipSourceCtx = document.getElementById('equipementsSourceChart').getContext('2d');
                new Chart(equipSourceCtx, {
                    type: 'pie',
                    data: {
                        labels: data.equipements_par_source.map(item => item.source_actuelle),
                        datasets: [{
                            data: data.equipements_par_source.map(item => item.nombre),
                            backgroundColor: dangerColors.slice(0, data.equipements_par_source.length),
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
                            }
                        }
                    }
                });

                // Graphique articles par source
                const articlesSourceCtx = document.getElementById('articlesSourceChart').getContext('2d');
                new Chart(articlesSourceCtx, {
                    type: 'pie',
                    data: {
                        labels: data.articles_par_source.map(item => item.source_actuelle),
                        datasets: [{
                            data: data.articles_par_source.map(item => item.nombre),
                            backgroundColor: dangerColors.slice(0, data.articles_par_source.length),
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
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Erreur lors du chargement des données:', error);
            });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
    </script>
</body>

</html>