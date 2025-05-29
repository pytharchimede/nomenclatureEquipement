<?php
// Vous pouvez ajouter ici la logique PHP pour récupérer les alertes ou statistiques si besoin
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar p-3" id="sidebar">
            <div class="mb-4 d-flex align-items-center">
                <span class="material-icons" style="font-size:2rem;color:#1976d2;">dashboard</span>
                <span style="font-size:1.3rem;font-weight:700;color:#1976d2;">Nomenclature</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#"><span class="material-icons">home</span>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><span class="material-icons">build</span>Équipements</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><span class="material-icons">widgets</span>Articles</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><span class="material-icons">list_alt</span>Nomenclatures</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><span class="material-icons">file_download</span>Exportation</a></li>
            </ul>
        </nav>
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
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <span class="material-icons me-2">warning</span>
                        <div>
                            2 équipements sans pièces de rechange détectés !
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <span class="material-icons me-2">info</span>
                        <div>
                            3 articles non liés à des équipements.
                        </div>
                    </div>
                </div>
            </div>
            <!-- Graphique -->
            <div class="card mb-4 shadow-sm" style="border-radius:16px;">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#1976d2;">Répartition des équipements par catégorie</h5>
                    <canvas id="equipChart" height="80"></canvas>
                </div>
            </div>
            <!-- Liens d'exportation -->
            <div class="d-flex gap-3">
                <a href="export_equipements.php" class="btn btn-primary"><span class="material-icons">file_download</span>Exporter Équipements</a>
                <a href="export_articles.php" class="btn btn-primary"><span class="material-icons">file_download</span>Exporter Articles</a>
                <a href="export_nomenclatures.php" class="btn btn-primary"><span class="material-icons">file_download</span>Exporter Nomenclatures</a>
            </div>
        </div>
    </div>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Responsive sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Données de test simulées via "API"
        fetch('https://mocki.io/v1/7e1e7b7e-1b2c-4e7e-9e9e-1e1e1e1e1e1e') // Remplacez par votre endpoint réel
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
    </script>
</body>

</html>