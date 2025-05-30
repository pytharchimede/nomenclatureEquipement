<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Vous pouvez ajouter ici la logique PHP pour récupérer les alertes ou statistiques si besoin
require_once 'model/Equipement.php';
require_once 'model/Article.php';
require_once 'model/Nomenclature.php';

$totalEquipements = count(Equipement::getAll());
$totalArticles = count(Article::getAll());
$totalNomenclatures = Nomenclature::countAll();

$equipAvecPiece = Nomenclature::countEquipementsAvecPieceReelle();
$articlesLies = Nomenclature::countArticlesLiesReels();

$equipSansPiece = $totalEquipements - $equipAvecPiece;
$articlesNonLies = $totalArticles - $articlesLies;

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
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div class="d-flex align-items-center">
                    <span class="menu-toggle material-icons d-lg-none me-2" onclick="toggleSidebar()">menu</span>
                    <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Tableau de bord</h2>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="request/export_equipements.php" class="btn btn-primary">
                        <span class="material-icons">file_download</span>Exporter Équipements
                    </a>
                    <a href="request/export_articles.php" class="btn btn-primary">
                        <span class="material-icons">file_download</span>Exporter Articles
                    </a>
                    <a href="request/export_nomenclatures.php" class="btn btn-primary">
                        <span class="material-icons">file_download</span>Exporter Nomenclatures
                    </a>
                    <a href="request/export_equipements_non_affectes.php" class="btn btn-warning">
                        <span class="material-icons">file_download</span>
                        Exporter équipements non affectés (Excel)
                    </a>
                    <a href="#" class="btn btn-outline-primary ms-2">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
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
                    <h5 class="card-title mb-3" style="color:#1976d2;">Répartition des équipements par famille</h5>
                    <canvas id="equipChart" height="80"></canvas>
                </div>
            </div>
            <div class="card mb-4 shadow-sm" style="border-radius:16px;">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#1976d2;">Répartition des articles par groupe</h5>
                    <canvas id="groupeArticleChart" height="80"></canvas>
                </div>
            </div>
            <div class="card mb-4 shadow-sm" style="border-radius:16px;">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#1976d2;">Répartition des articles par type</h5>
                    <canvas id="typeArticleChart" height="80"></canvas>
                </div>
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
        fetch('request/dashboard_stats_quantitatif.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('equipChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels.concat(['Non affectés']),
                        datasets: [{
                            label: "Nombre d'équipements",
                            data: data.values.concat([data.nonAffectes]),
                            backgroundColor: [
                                '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa', '#00838f', '#c2185b', '#bdbdbd'
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

                // Affichage du nombre non affectés à côté du graphique ou dans une carte
                const nonAffectesDiv = document.getElementById('nonAffectesEquip');
                if (nonAffectesDiv) {
                    nonAffectesDiv.textContent = data.nonAffectes + " équipement(s) sans famille";
                }
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

        // Répartition par groupe_article
        fetch('request/dashboard_articles_groupes.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('groupeArticleChart').getContext('2d');
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

        // Répartition par type_article
        fetch('request/dashboard_articles_types.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('typeArticleChart').getContext('2d');
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
                        alertEquip.innerHTML = `
                    ${data.equipSansPiece} équipement(s) sans pièces de rechange détecté(s) !
                    <a href="request/export_equipements_sans_piece.php" class="btn btn-sm btn-danger ms-3">
                        <span class="material-icons" style="font-size:18px;vertical-align:middle;">file_download</span>
                        Exporter la liste
                    </a>
                `;
                    } else {
                        alertEquip.textContent = "Tous les équipements ont des pièces de rechange.";
                    }
                }
                // Articles non liés
                const alertArticle = document.querySelector('#alert-article div');
                if (alertArticle) {
                    if (data.articlesNonLies > 0) {
                        alertArticle.innerHTML = `
                    ${data.articlesNonLies} article(s) non lié(s) à des équipements.
                    <a href="request/export_articles_non_lies.php" class="btn btn-sm btn-info ms-3">
                        <span class="material-icons" style="font-size:18px;vertical-align:middle;">file_download</span>
                        Exporter la liste
                    </a>
                `;
                    } else {
                        alertArticle.textContent = "Tous les articles sont liés à des équipements.";
                    }
                }
            });
    </script>
</body>

</html>