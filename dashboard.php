<?php
// Vous pouvez ajouter ici la logique PHP pour récupérer les alertes ou statistiques si besoin
require_once 'model/Equipement.php';
require_once 'model/Article.php';
require_once 'model/Nomenclature.php';

$totalEquipements = count(Equipement::getAll());
$totalArticles = count(Article::getAll());
$totalNomenclatures = Nomenclature::countAll(); // À créer si besoin

$articlesLies = Nomenclature::countDistinctArticles(); // À créer
$equipAvecPiece = Nomenclature::countDistinctEquipements(); // À créer

$articlesNonLies = $totalArticles - $articlesLies;
$equipSansPiece = $totalEquipements - $equipAvecPiece;

$pourcentArticlesLies = $totalArticles > 0 ? round($articlesLies / $totalArticles * 100, 1) : 0;
$pourcentEquipAvecPiece = $totalEquipements > 0 ? round($equipAvecPiece / $totalEquipements * 100, 1) : 0;

$nbAjoutsEquip = Equipement::countAddedLast30Days();
$nbAjoutsArticles = Article::countAddedLast30Days(); // à créer dans Article.php
$nbAjoutsNomenclatures = Nomenclature::countAddedLast30Days();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>
        <!-- Main Content -->
        <div class="flex-grow-1 content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="menu-toggle material-icons d-lg-none" onclick="toggleSidebar()">menu</span>
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Tableau de bord</h2>
                <a href="#" class="btn btn-outline-primary"><span class="material-icons">logout</span>Déconnexion</a>
            </div>
            <!-- Alertes -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-warning d-flex align-items-center" role="alert" id="alert-equip">
                        <span class="material-icons me-2">warning</span>
                        <div>
                            <!-- Contenu dynamique -->
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info d-flex align-items-center" role="alert" id="alert-article">
                        <span class="material-icons me-2">info</span>
                        <div>
                            <!-- Contenu dynamique -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Compteurs -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <div class="h1 text-primary"><?= $totalEquipements ?></div>
                            <div>Équipements</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <div class="h1 text-success"><?= $totalArticles ?></div>
                            <div>Articles</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <div class="h1 text-info"><?= $totalNomenclatures ?></div>
                            <div>Nomenclatures</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <div class="h2"><?= $pourcentArticlesLies ?>%</div>
                            <div>Articles liés à un équipement</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <div class="h2"><?= $pourcentEquipAvecPiece ?>%</div>
                            <div>Équipements avec pièce</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Graphiques -->
            <div class="card mb-4 shadow-sm" style="border-radius:16px;">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#1976d2;">Répartition des équipements par catégorie</h5>
                    <canvas id="equipChart" height="80"></canvas>
                </div>
            </div>
            <div class="card mb-4 shadow-sm" style="border-radius:16px;">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#1976d2;">Répartition des articles par famille</h5>
                    <canvas id="articleChart" height="80"></canvas>
                </div>
            </div>
            <!-- Liens d'exportation -->
            <div class="d-flex gap-3">
                <a href="export_equipements.php" class="btn btn-primary"><span class="material-icons">file_download</span>Exporter Équipements</a>
                <a href="export_articles.php" class="btn btn-primary"><span class="material-icons">file_download</span>Exporter Articles</a>
                <a href="export_nomenclatures.php" class="btn btn-primary"><span class="material-icons">file_download</span>Exporter Nomenclatures</a>
            </div>
            <div class="small text-muted">
                +<?= $nbAjoutsEquip ?> équipements ajoutés sur 30 jours
            </div>
        </div>
    </div>
    <!-- Chart.js -->
    <script src="plugins/js/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Responsive sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Données de test simulées via "API"
        fetch('request/dashboard_stats.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('equipChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Nombre d\'équipements',
                            data: data.values,
                            backgroundColor: [
                                '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa', '#00838f', '#c2185b'
                            ],
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
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

                // Graphique pour la répartition des articles par famille
                const ctxArticle = document.getElementById('articleChart').getContext('2d');
                new Chart(ctxArticle, {
                    type: 'pie',
                    data: {
                        labels: data.articleLabels,
                        datasets: [{
                            label: 'Répartition des articles',
                            data: data.articleValues,
                            backgroundColor: [
                                '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa', '#00838f', '#c2185b'
                            ],
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    color: '#333',
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        let label = tooltipItem.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += tooltipItem.raw;
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            })
            .catch(() => {
                // fallback si l'API ne répond pas
                const ctx = document.getElementById('equipChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Pompes', 'Vannes', 'Tableaux', 'Moteurs', 'Autres'],
                        datasets: [{
                            label: 'Nombre d\'équipements',
                            data: [12, 8, 5, 3, 2],
                            backgroundColor: [
                                '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa'
                            ],
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
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
            });

        // Graphique articles par famille
        fetch('request/dashboard_articles_stats.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('articleChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: "Nombre d'articles",
                            data: data.values,
                            backgroundColor: [
                                '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa', '#00838f', '#c2185b'
                            ],
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
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
            });

        // Récupération des alertes
        fetch('request/dashboard_alerts.php')
            .then(response => response.json())
            .then(data => {
                // Équipements sans pièces de rechange
                const alertEquip = document.querySelector('#alert-equip div');
                if (alertEquip) {
                    if (data.equipSansPiece > 0) {
                        alertEquip.textContent = `${data.equipSansPiece} équipement(s) sans pièces de rechange détecté(s) !`;
                    } else {
                        alertEquip.textContent = "Tous les équipements ont des pièces de rechange.";
                    }
                }
                // Articles non liés
                const alertArticle = document.querySelector('#alert-article div');
                if (alertArticle) {
                    if (data.articlesNonLies > 0) {
                        alertArticle.textContent = `${data.articlesNonLies} article(s) non lié(s) à des équipements.`;
                    } else {
                        alertArticle.textContent = "Tous les articles sont liés à des équipements.";
                    }
                }
            });
    </script>
</body>

</html>