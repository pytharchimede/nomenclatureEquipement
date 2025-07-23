<?php

/**
 * Page de gestion des équipements avec pagination optimisée
 * Utilise le repère comme clé primaire métier
 */

require_once 'includes/auth.php';
require_once 'model/Equipement.php';
require_once 'model/Quantitatif.php';

// Pour les statistiques, on récupère un échantillon ou utilise des requêtes optimisées
$totalEquipements = 0;
$pdo = Database::getConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM equipements");
$totalEquipements = $stmt->fetchColumn();

// Récupération des statistiques par famille (optimisé pour les gros volumes)
$statsFamilles = [];
$nonAffectes = 0;

// Si le volume est raisonnable, on fait le calcul complet
if ($totalEquipements <= 5000) {
    $equipements = Equipement::getAll();
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
} else {
    // Pour les gros volumes, on fait un échantillonnage
    $stmt = $pdo->query("SELECT repere_equipement FROM equipements ORDER BY RAND() LIMIT 1000");
    $echantillon = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($echantillon as $repere) {
        $repere = preg_replace('/\s+/', '', $repere ?? '');
        $famille = Quantitatif::getFamilleByRepere($repere);
        if ($famille && $famille !== 'Non défini') {
            if (!isset($statsFamilles[$famille])) $statsFamilles[$famille] = 0;
            $statsFamilles[$famille]++;
        } else {
            $nonAffectes++;
        }
    }
}

// Calcul des statistiques
$total = $totalEquipements;
$topFamille = '';
$maxFamille = 0;
if ($statsFamilles) {
    $maxFamille = max($statsFamilles);
    $topFamille = array_search($maxFamille, $statsFamilles);
}
$familleLabels = array_keys($statsFamilles);
$familleData = array_values($statsFamilles);

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
    <style>
        /* Styles pour les filtres style Excel */
        .mini-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            height: 80px;
        }

        .mini-graph {
            width: 40px !important;
            height: 40px !important;
        }

        .form-label.small {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .btn-sm .material-icons {
            font-size: 16px;
        }

        #drop-area {
            transition: all 0.3s ease;
        }

        #drop-area:hover {
            border-color: #1976d2 !important;
            background-color: #f0f7ff !important;
        }

        .table-responsive {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .sticky-top {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
                    <button id="export-excel-btn" class="btn btn-outline-success"><span class="material-icons">file_download</span>Excel</button>
                    <button id="export-pdf-btn" class="btn btn-outline-danger"><span class="material-icons">picture_as_pdf</span>PDF</button>
                    <button id="export-filtered-excel-btn" class="btn btn-outline-info" title="Exporter les équipements filtrés en Excel"><span class="material-icons">filter_alt</span>Excel Filtré</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipModal"><span class="material-icons">add</span>Ajouter</button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal"><span class="material-icons">upload_file</span>Importer Excel</button>
                    <a href="logout.php" class="btn btn-outline-primary ms-2">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>
            <!-- Mini Cards et Graphes -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons">build</span>
                        <div>
                            <div data-stat="total-equipements" style="font-size:1.3rem;font-weight:700;"><?= $total ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Total équipements</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div data-stat="top-famille" style="font-size:1.3rem;font-weight:700;"><?= htmlspecialchars($topFamille) ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Famille la + présente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPie" class="mini-graph"></canvas>
                        <div>
                            <div data-stat="max-famille" style="font-size:1.3rem;font-weight:700;"><?= $maxFamille ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max dans une famille</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#e53935;">help_outline</span>
                        <div>
                            <div data-stat="non-affectes" style="font-size:1.3rem;font-weight:700;"><?= $nonAffectes ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Non affectés à une famille</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Filtres de recherche (style Excel) -->
            <div class="card shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Recherche globale</label>
                            <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Repère, désignation, fabricant..." />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Fabricant</label>
                            <select id="fabricant-filter" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <?php foreach ($fabricants as $fabricant): ?>
                                    <option value="<?= htmlspecialchars($fabricant) ?>"><?= htmlspecialchars($fabricant) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Type d'objet</label>
                            <select id="type-filter" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <?php foreach ($typesObjet as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Catégorie</label>
                            <select id="categorie-filter" class="form-select form-select-sm">
                                <option value="">Toutes</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small text-muted mb-1">&nbsp;</label>
                            <button id="reset-filters" class="btn btn-outline-secondary btn-sm w-100">
                                <span class="material-icons" style="font-size:16px;">clear</span>
                            </button>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Actions</label>
                            <div class="d-flex gap-1">
                                <button id="exportFilteredBtn" class="btn btn-outline-primary btn-sm" style="display:none;">
                                    <span class="material-icons" style="font-size:16px;">file_download</span>
                                </button>
                                <button id="deleteSelectedBtn" class="btn btn-outline-danger btn-sm" style="display:none;">
                                    <span class="material-icons" style="font-size:16px;">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des équipements -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">Liste des équipements</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div id="pagination-info" class="text-muted small">
                                Chargement...
                            </div>
                            <div id="loading-indicator" style="display: none;">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <span class="small">Chargement...</span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table id="equipements-table" class="table table-hover align-middle">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th><input type="checkbox" id="select-all-equipements"></th>
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
                                <!-- Les données seront chargées via JavaScript avec pagination -->
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
            <!-- Modal Import amélioré -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="excelImportForm" enctype="multipart/form-data" onsubmit="return false;">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importModalLabel">Importer depuis Excel</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="drop-area" class="border border-2 border-primary rounded-3 p-4 text-center mb-3" style="cursor:pointer; background:#f8fafd;">
                                <span class="material-icons" style="font-size:2.5rem;color:#1976d2;">upload_file</span>
                                <p class="mb-1">Glissez-déposez votre fichier Excel ici<br><span class="text-muted" style="font-size:0.95em;">(ou cliquez pour sélectionner)</span></p>
                                <input type="file" id="excelFileInput" name="excel_file" accept=".xls,.xlsx" style="display:none;" required>
                                <div id="fileName" class="text-success mt-2"></div>
                            </div>
                            <div class="progress mb-2" style="height: 22px; display:none;" id="importProgressBarContainer">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="importProgressBar" style="width:0%">0%</div>
                            </div>
                            <div id="importResult" class="mt-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="startImportBtn" disabled>Importer</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- À placer juste avant </body> -->
            <div class="modal fade" id="exportLoadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-center p-4">
                        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                        <div id="exportProgressText" style="font-size:1.2rem;">Préparation de l’export, veuillez patienter...</div>
                        <div class="progress mt-3" style="height:18px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="exportProgressBar" style="width:0%">0%</div>
                        </div>
                        <button id="closeExportModalBtn" class="btn btn-outline-secondary mt-3" style="display:none;">Fermer</button>
                        <div class="mt-2 text-muted" style="font-size:0.95em;">Le téléchargement va démarrer automatiquement.<br>Si ce n'est pas le cas, cliquez sur "Fermer".</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteneur pour les alertes -->
        <div id="alerts-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>
    </div>
    </div>

    <!-- Chart.js -->
    <script src="plugins/js/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>

    <!-- Initialisation du graphique -->
    <script>
        // Données PHP pour JavaScript
        const familleLabels = <?= json_encode($familleLabels) ?>;
        const familleData = <?= json_encode($familleData) ?>;

        // Création du mini graphique en secteurs
        if (familleLabels.length > 0) {
            const ctx = document.getElementById('miniPie');
            if (ctx) {
                window.miniPieChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: familleLabels.slice(0, 5), // Top 5 familles
                        datasets: [{
                            data: familleData.slice(0, 5),
                            backgroundColor: [
                                '#1976d2', '#43a047', '#ff9800',
                                '#e53935', '#9c27b0'
                            ],
                            borderWidth: 1
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
            }
        }
    </script>

    <!-- JavaScript optimisé pour les équipements avec pagination -->
    <script src="js/function_equipements_updated.js"></script>

    <!-- Initialisation des graphiques -->
    <script>
        // Données pour les graphiques depuis PHP
        const familleLabels = <?= json_encode($familleLabels) ?>;
        const familleData = <?= json_encode($familleData) ?>;

        // Création du mini graphique en secteurs
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('miniPie');
            if (ctx && familleLabels.length > 0) {
                // Couleurs pour le graphique
                const colors = [
                    '#1976d2', '#43a047', '#fb8c00', '#e53935', '#8e24aa',
                    '#00acc1', '#fdd835', '#f4511e', '#7cb342', '#546e7a'
                ];

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: familleLabels.slice(0, 5), // Top 5 familles
                        datasets: [{
                            data: familleData.slice(0, 5),
                            backgroundColor: colors.slice(0, familleLabels.length),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>