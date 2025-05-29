<?php

require_once 'model/Equipement.php';
// Récupération des équipements
$equipements = Equipement::getAll();

// Données de test pour les mini-graphes et cards
$total = count($equipements);
$categories = ['Pompes', 'Vannes', 'Tableaux', 'Moteurs', 'Autres'];
$catData = [12, 8, 5, 3, 2]; // À remplacer par vos vraies stats
$catAssoc = array_combine($categories, $catData);
$catMax = max($catData);
$catTop = $categories[array_search($catMax, $catData)];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .card {
            border-radius: 16px;
        }

        .modal-content {
            border-radius: 16px;
        }

        .table thead {
            background: #f5f5f5;
        }

        .form-label {
            font-weight: 500;
        }

        .mini-card {
            min-width: 180px;
            border-radius: 12px;
            box-shadow: 0 2px 8px #eee;
            background: #fff;
            padding: 1rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .mini-card .material-icons {
            font-size: 2.2rem;
            color: #1976d2;
        }

        .mini-graph {
            width: 120px !important;
            height: 60px !important;
        }

        @media (max-width: 991px) {
            .mini-card {
                min-width: 120px;
                padding: 0.7rem 0.5rem;
            }

            .mini-graph {
                width: 80px !important;
                height: 40px !important;
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="menu-toggle material-icons d-lg-none" onclick="toggleSidebar()">menu</span>
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Équipements</h2>
                <div class="d-flex gap-2">
                    <a href="export_equipements.php?type=excel" class="btn btn-outline-success"><span class="material-icons">file_download</span>Excel</a>
                    <a href="export_equipements.php?type=pdf" class="btn btn-outline-danger"><span class="material-icons">picture_as_pdf</span>PDF</a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipModal"><span class="material-icons">add</span>Ajouter</button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal"><span class="material-icons">upload_file</span>Importer Excel</button>
                </div>
            </div>
            <!-- Mini Cards et Graphes -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons">build</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $total ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Total équipements</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catTop ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Catégorie la + présente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPie" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catMax ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max dans une catégorie</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniBar" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catData[0] ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Pompes</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tableau des équipements -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#1976d2;">Liste des équipements</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Désignation</th>
                                    <th>Repère</th>
                                    <th>Fabricant</th>
                                    <th>Type</th>
                                    <th>N° Série</th>
                                    <th>Catégorie</th>
                                    <th>Date création</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipements as $eq): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($eq['code_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['designation_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['repere_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['fabricant']) ?></td>
                                        <td><?= htmlspecialchars($eq['type_objet']) ?></td>
                                        <td><?= htmlspecialchars($eq['numero_serie_fabricant']) ?></td>
                                        <td><?= htmlspecialchars($eq['categorie_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['date_creation']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Modal Ajout -->
            <div class="modal fade" id="addEquipModal" tabindex="-1" aria-labelledby="addEquipModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="post" action="equipement_add.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addEquipModalLabel">Ajouter un équipement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Code équipement</label>
                                <input type="text" name="code_equipement" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Désignation</label>
                                <input type="text" name="designation_equipement" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Repère</label>
                                <input type="text" name="repere_equipement" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fabricant</label>
                                <input type="text" name="fabricant" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type d'objet</label>
                                <input type="text" name="type_objet" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Désignation type</label>
                                <input type="text" name="designation_type" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">N° série fabricant</label>
                                <input type="text" name="numero_serie_fabricant" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">N° pièce fabricant</label>
                                <input type="text" name="numero_piece_fabricant" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poste technique</label>
                                <input type="text" name="poste_technique" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Désignation poste technique</label>
                                <input type="text" name="designation_poste_technique" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poste travail principal</label>
                                <input type="text" name="poste_travail_principal" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catégorie équipement</label>
                                <input type="text" name="categorie_equipement" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Centre de coûts</label>
                                <input type="text" name="centre_de_couts" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date création</label>
                                <input type="date" name="date_creation" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal Import -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" method="post" action="equipement_import.php" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importModalLabel">Importer depuis Excel</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="file" name="excel_file" accept=".xls,.xlsx" class="form-control" required>
                            <small class="text-muted">Téléchargez un fichier respectant la structure fournie.</small>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Importer</button>
                        </div>
                    </form>
                </div>
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

        // Mini Pie Chart
        new Chart(document.getElementById('miniPie').getContext('2d'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($categories) ?>,
                datasets: [{
                    data: <?= json_encode($catData) ?>,
                    backgroundColor: [
                        '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa'
                    ]
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                responsive: false
            }
        });

        // Mini Bar Chart
        new Chart(document.getElementById('miniBar').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($categories) ?>,
                datasets: [{
                    data: <?= json_encode($catData) ?>,
                    backgroundColor: [
                        '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                responsive: false,
                scales: {
                    y: {
                        display: false
                    },
                    x: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>

</html>