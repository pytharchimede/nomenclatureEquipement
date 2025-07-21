<?php

/**
 * Page de gestion des équipements avec pagination et repère comme clé primaire
 * Version mise à jour pour la cohérence du système
 */

require_once 'includes/auth.php';
require_once 'model/Equipement.php';
require_once 'model/Quantitatif.php';

// Récupération des équipements pour les statistiques (on garde un échantillon pour les stats)
$equipements = Equipement::getAll();

// Calcul des statistiques par famille
$statsFamilles = [];
$nonAffectes = 0;
foreach ($equipements as $eq) {
    $repere = preg_replace('/\s+/', '', $eq['repere_equipement'] ?? '');
    $famille = Quantitatif::getFamilleByRepere($repere);
    if ($famille && $famille !== 'Non défini') {
        if (!isset($statsFamilles[$famille])) $statsFamilles[$famille] = 0;
        $statsFamilles[$famille]++;
    } else {
        $nonAffectes++;
    }
}

$total = count($equipements);
$topFamille = '';
$maxFamille = 0;
if ($statsFamilles) {
    $maxFamille = max($statsFamilles);
    $topFamille = array_search($maxFamille, $statsFamilles);
}

// Récupération des valeurs distinctes pour les filtres
$fabricants = Equipement::getDistinctValues('fabricant');
$typesObjet = Equipement::getDistinctValues('type_objet');
$categories = Equipement::getDistinctValues('categorie_equipement');
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
    <link href="css/style_equipements.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 content">
            <!-- Header avec actions -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div class="d-flex align-items-center">
                    <span class="menu-toggle material-icons d-lg-none me-2" onclick="toggleSidebar()">menu</span>
                    <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Équipements</h2>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button id="export-excel-btn" class="btn btn-outline-success">
                        <span class="material-icons">file_download</span>Excel
                    </button>
                    <button id="export-pdf-btn" class="btn btn-outline-danger">
                        <span class="material-icons">picture_as_pdf</span>PDF
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipModal">
                        <span class="material-icons">add</span>Ajouter
                    </button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                        <span class="material-icons">upload_file</span>Importer Excel
                    </button>
                    <button id="delete-selected-btn" class="btn btn-outline-danger">
                        <span class="material-icons">delete</span>Supprimer sélection
                    </button>
                    <a href="logout.php" class="btn btn-outline-primary ms-2">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>

            <!-- Container pour les alertes -->
            <div id="alerts-container"></div>

            <!-- Mini Cards et Statistiques -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons">build</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;" data-stat="total-equipements"><?= $total ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Total équipements</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= htmlspecialchars($topFamille) ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Famille la + présente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPie" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $maxFamille ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max dans une famille</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#e53935;">help_outline</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $nonAffectes ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Non affectés</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres de recherche -->
            <div class="card mb-4 shadow-sm" style="border-radius:16px;">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#1976d2;">
                        <span class="material-icons align-middle">search</span>
                        Recherche et filtres
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="search-input" class="form-label">Recherche générale</label>
                            <input type="text" class="form-control" id="search-input"
                                placeholder="Repère, désignation, fabricant...">
                        </div>
                        <div class="col-md-3">
                            <label for="fabricant-filter" class="form-label">Fabricant</label>
                            <select class="form-select" id="fabricant-filter">
                                <option value="">Tous les fabricants</option>
                                <?php foreach ($fabricants as $fabricant): ?>
                                    <option value="<?= htmlspecialchars($fabricant) ?>"><?= htmlspecialchars($fabricant) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type-filter" class="form-label">Type d'objet</label>
                            <select class="form-select" id="type-filter">
                                <option value="">Tous les types</option>
                                <?php foreach ($typesObjet as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="categorie-filter" class="form-label">Catégorie</label>
                            <select class="form-select" id="categorie-filter">
                                <option value="">Toutes</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?= htmlspecialchars($categorie) ?>"><?= htmlspecialchars($categorie) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau principal -->
            <div class="card shadow-sm" style="border-radius:16px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">
                            <span class="material-icons align-middle">list</span>
                            Liste des équipements
                        </h5>
                        <div class="d-flex align-items-center gap-3">
                            <div id="pagination-info" class="text-muted small"></div>
                            <div id="loading-indicator" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-striped" id="equipements-table">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width: 50px;">
                                        <input type="checkbox" id="select-all-equipements" class="form-check-input">
                                    </th>
                                    <th>Code</th>
                                    <th>Désignation</th>
                                    <th>Repère</th>
                                    <th>Fabricant</th>
                                    <th>Type</th>
                                    <th>N° série</th>
                                    <th>Catégorie</th>
                                    <th>Date création</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Les données seront chargées via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout d'équipement -->
    <div class="modal fade" id="addEquipModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un équipement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addEquipForm" action="request/equipement_add.php" method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="add_repere_equipement" class="form-label">Repère équipement *</label>
                                <input type="text" class="form-control" name="repere_equipement" id="add_repere_equipement" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_code_equipement" class="form-label">Code équipement *</label>
                                <input type="text" class="form-control" name="code_equipement" id="add_code_equipement" required>
                            </div>
                            <div class="col-12">
                                <label for="add_designation_equipement" class="form-label">Désignation</label>
                                <input type="text" class="form-control" name="designation_equipement" id="add_designation_equipement">
                            </div>
                            <div class="col-md-6">
                                <label for="add_fabricant" class="form-label">Fabricant</label>
                                <input type="text" class="form-control" name="fabricant" id="add_fabricant">
                            </div>
                            <div class="col-md-6">
                                <label for="add_type_objet" class="form-label">Type d'objet</label>
                                <input type="text" class="form-control" name="type_objet" id="add_type_objet">
                            </div>
                            <div class="col-md-6">
                                <label for="add_numero_serie_fabricant" class="form-label">N° série fabricant</label>
                                <input type="text" class="form-control" name="numero_serie_fabricant" id="add_numero_serie_fabricant">
                            </div>
                            <div class="col-md-6">
                                <label for="add_categorie_equipement" class="form-label">Catégorie équipement</label>
                                <input type="text" class="form-control" name="categorie_equipement" id="add_categorie_equipement">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter l'équipement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal d'import Excel -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importer des équipements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="import-excel-input" class="form-label">Fichier Excel</label>
                        <input type="file" class="form-control" id="import-excel-input" accept=".xlsx,.xls" required>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>Format attendu :</strong> Le fichier Excel doit contenir les colonnes suivantes :
                            Repère équipement, Code équipement, Désignation, Fabricant, etc.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'édition -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'équipement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" action="request/equipement_edit.php" method="POST">
                    <input type="hidden" name="repere_original" value="">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_repere_equipement" class="form-label">Repère équipement *</label>
                                <input type="text" class="form-control" name="repere_equipement" id="edit_repere_equipement" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_code_equipement" class="form-label">Code équipement *</label>
                                <input type="text" class="form-control" name="code_equipement" id="edit_code_equipement" required>
                            </div>
                            <div class="col-12">
                                <label for="edit_designation_equipement" class="form-label">Désignation</label>
                                <input type="text" class="form-control" name="designation_equipement" id="edit_designation_equipement">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_fabricant" class="form-label">Fabricant</label>
                                <input type="text" class="form-control" name="fabricant" id="edit_fabricant">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_type_objet" class="form-label">Type d'objet</label>
                                <input type="text" class="form-control" name="type_objet" id="edit_type_objet">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_numero_serie_fabricant" class="form-label">N° série fabricant</label>
                                <input type="text" class="form-control" name="numero_serie_fabricant" id="edit_numero_serie_fabricant">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_categorie_equipement" class="form-label">Catégorie équipement</label>
                                <input type="text" class="form-control" name="categorie_equipement" id="edit_categorie_equipement">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de chargement d'export -->
    <div class="modal fade" id="exportLoadingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <h5 id="exportProgressText">Préparation de l'export...</h5>
                    <div class="progress mt-3" style="height: 6px;">
                        <div id="exportProgressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <button type="button" id="closeExportModalBtn" class="btn btn-secondary mt-3" style="display: none;">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Chart.js -->
    <script src="plugins/js/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <!-- Script de gestion des équipements -->
    <script src="js/function_equipements_updated.js"></script>

    <script>
        // Initialisation des graphiques mini
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('miniPie').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_keys($statsFamilles)) ?>,
                    datasets: [{
                        data: <?= json_encode(array_values($statsFamilles)) ?>,
                        backgroundColor: ['#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa', '#00838f'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });

        // Fonction responsive sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
    </script>
</body>

</html>