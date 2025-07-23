<?php
require_once 'model/Nomenclature.php';
require_once 'includes/auth.php';

// Statistiques initiales simplifiées pour l'affichage de base
$total = 0;
$catTop = '';
$catMax = 0;
$catLabels = [];
$catData = [];
$uniteLabels = [];
$uniteData = [];

// Récupération rapide du total
$pdo = Database::getConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM nomenclatures");
$total = $stmt->fetchColumn();

// Statistiques par famille (top 5)
$stmt = $pdo->query("
    SELECT designation_article, COUNT(*) as count 
    FROM nomenclatures 
    WHERE designation_article IS NOT NULL AND designation_article != '' 
    GROUP BY designation_article 
    ORDER BY count DESC 
    LIMIT 5
");
$familles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($familles) {
    $catTop = $familles[0]['designation_article'];
    $catMax = $familles[0]['count'];
    $catLabels = array_column($familles, 'designation_article');
    $catData = array_column($familles, 'count');
}

// Statistiques par unité (top 5)
$stmt = $pdo->query("
    SELECT unite, COUNT(*) as count 
    FROM nomenclatures 
    WHERE unite IS NOT NULL AND unite != '' 
    GROUP BY unite 
    ORDER BY count DESC 
    LIMIT 5
");
$unites = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($unites) {
    $uniteLabels = array_column($unites, 'unite');
    $uniteData = array_column($unites, 'count');
}

// Détection des doublons (repere_equipement + code_article)
$stmt = $pdo->query("
    SELECT repere_equipement, code_article, COUNT(*) as count 
    FROM nomenclatures 
    WHERE repere_equipement IS NOT NULL AND repere_equipement != '' 
    AND code_article IS NOT NULL AND code_article != ''
    GROUP BY repere_equipement, code_article 
    HAVING count > 1
");
$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hasDups = count($duplicates) > 0;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Nomenclatures</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <link href="css/style_nomenclature.css" rel="stylesheet">
    <style>
        /* Styles pour les filtres style Excel et le système moderne */
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

        .table-responsive {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .sticky-top {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-input {
            transition: all 0.2s ease;
        }

        .filter-input:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
        }

        #drop-area {
            transition: all 0.3s ease;
        }

        #drop-area:hover {
            border-color: #1976d2 !important;
            background-color: #f0f7ff !important;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'menu.php'; ?>
        <div class="flex-grow-1 content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="menu-toggle material-icons d-lg-none" onclick="toggleSidebar()">menu</span>
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Nomenclatures</h2>
                <div class="d-flex gap-2">
                    <button id="export-excel-btn" class="btn btn-outline-success">
                        <span class="material-icons">file_download</span>Excel
                    </button>
                    <button id="export-pdf-btn" class="btn btn-outline-danger">
                        <span class="material-icons">picture_as_pdf</span>PDF
                    </button>
                    <button id="export-filtered-excel-btn" class="btn btn-outline-info" title="Exporter les nomenclatures filtrées en Excel">
                        <span class="material-icons">filter_alt</span>Excel Filtré
                    </button>
                    <button id="check-duplicates-btn" class="btn btn-outline-warning" onclick="loadDuplicates()" title="Détecter et gérer les doublons">
                        <span class="material-icons">find_in_page</span>Doublons
                    </button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importNomenclatureModal">
                        <span class="material-icons">upload_file</span>Importer Excel
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNomenclatureModal">
                        <span class="material-icons">add</span>Ajouter
                    </button>
                    <a href="logout.php" class="btn btn-outline-primary ms-2">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>

            <!-- Mini Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons">list_alt</span>
                        <div>
                            <div data-stat="total-nomenclatures" style="font-size:1.3rem;font-weight:700;"><?= number_format($total) ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Total nomenclatures</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div data-stat="top-famille" style="font-size:1.3rem;font-weight:700;"><?= htmlspecialchars($catTop) ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Famille la + présente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPieNomenclature" class="mini-graph"></canvas>
                        <div>
                            <div data-stat="max-famille" style="font-size:1.3rem;font-weight:700;"><?= number_format($catMax) ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max dans une famille</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPieUniteNomenclature" class="mini-graph"></canvas>
                        <div>
                            <div data-stat="top-unite" style="font-size:1.3rem;font-weight:700;"><?= htmlspecialchars($uniteLabels[0] ?? '') ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Unité la + utilisée</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($hasDups): ?>
                <div class="alert alert-danger d-flex align-items-center justify-content-between" style="font-size:1.1em;">
                    <div>
                        <span class="material-icons me-2" style="vertical-align:middle;">warning</span>
                        <b>Doublons détectés :</b>
                        <?= count($duplicates) ?> doublon(s) trouvé(s) (même <b>repère équipement</b> et code article).
                    </div>
                    <a href="gestion_doublons_nomenclature.php" class="btn btn-danger btn-sm">
                        Gérer les doublons
                    </a>
                </div>
            <?php endif; ?>

            <!-- Filtres de recherche (style Excel) -->
            <div class="card shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Recherche globale</label>
                            <input type="text" id="search-input" class="form-control form-control-sm filter-input" placeholder="Code, repère, désignation..." />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Code Équipement</label>
                            <input type="text" id="code-equipement-filter" class="form-control form-control-sm filter-input" placeholder="Code équipement" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Code Article</label>
                            <input type="text" id="code-article-filter" class="form-control form-control-sm filter-input" placeholder="Code article" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Repère Équipement</label>
                            <input type="text" id="repere-equipement-filter" class="form-control form-control-sm filter-input" placeholder="Repère" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Fabricant</label>
                            <input type="text" id="fabricant-filter" class="form-control form-control-sm filter-input" placeholder="Fabricant" />
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small text-muted mb-1">&nbsp;</label>
                            <button id="reset-filters" class="btn btn-outline-secondary btn-sm w-100">
                                <span class="material-icons" style="font-size:16px;">clear</span>
                            </button>
                        </div>
                    </div>
                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Type</label>
                            <input type="text" id="type-filter" class="form-control form-control-sm filter-input" placeholder="Type" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Désignation Article</label>
                            <input type="text" id="designation-article-filter" class="form-control form-control-sm filter-input" placeholder="Désignation" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Unité</label>
                            <input type="text" id="unite-filter" class="form-control form-control-sm filter-input" placeholder="Unité" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Poste Technique</label>
                            <input type="text" id="poste-technique-filter" class="form-control form-control-sm filter-input" placeholder="Poste technique" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Métier</label>
                            <input type="text" id="metier-filter" class="form-control form-control-sm filter-input" placeholder="Métier" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Source</label>
                            <input type="text" id="source-filter" class="form-control form-control-sm filter-input" placeholder="Source" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des nomenclatures -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">Liste des nomenclatures</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div id="pagination-info" class="text-muted small">
                                Chargement...
                            </div>
                            <div id="loading-indicator" style="display: none;">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <span class="small">Chargement...</span>
                            </div>
                            <div>
                                <button id="exportFilteredBtn" class="btn btn-outline-primary btn-sm me-2" style="display:none;">
                                    <span class="material-icons">file_download</span>Exporter la sélection
                                </button>
                                <button id="deleteSelectedBtn" class="btn btn-outline-danger btn-sm" style="display:none;">
                                    <span class="material-icons">delete</span>Supprimer la sélection
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table id="nomenclatures-table" class="table table-hover align-middle">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th><input type="checkbox" id="select-all-nomenclatures"></th>
                                    <th>Code équipement</th>
                                    <th>Code article</th>
                                    <th>Repère équipement</th>
                                    <th>Désignation équipement</th>
                                    <th>Fabricant</th>
                                    <th>Type</th>
                                    <th>N° série fabricant</th>
                                    <th>Désignation article</th>
                                    <th>N° Poste</th>
                                    <th>Quantité</th>
                                    <th>Unité</th>
                                    <th>Poste technique</th>
                                    <th>Métier</th>
                                    <th>Date création</th>
                                    <th>Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Les données seront chargées via JavaScript avec pagination -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Importation Nomenclatures -->
            <div class="modal fade" id="importNomenclatureModal" tabindex="-1" aria-labelledby="importNomenclatureModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="importNomenclatureForm" enctype="multipart/form-data" onsubmit="return false;">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importNomenclatureModalLabel">Importer des nomenclatures (Excel)</h5>
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

            <!-- Modal export en cours -->
            <div class="modal fade" id="exportLoadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-center p-4">
                        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                        <div id="exportProgressText" style="font-size:1.2rem;">Préparation de l'export, veuillez patienter...</div>
                        <div class="progress mt-3" style="height:18px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="exportProgressBar" style="width:0%">0%</div>
                        </div>
                        <button id="closeExportModalBtn" class="btn btn-outline-secondary mt-3" style="display:none;">Fermer</button>
                        <div class="mt-2 text-muted" style="font-size:0.95em;">Le téléchargement va démarrer automatiquement.<br>Si ce n'est pas le cas, cliquez sur "Fermer".</div>
                    </div>
                </div>
            </div>

            <!-- Modal Ajout Nomenclature -->
            <div class="modal fade" id="addNomenclatureModal" tabindex="-1" aria-labelledby="addNomenclatureModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" id="addNomenclatureForm" autocomplete="off">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNomenclatureModalLabel">Ajouter une nomenclature</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body row g-3">
                            <?php
                            $fields = [
                                ['name' => 'code_equipement', 'label' => 'Code équipement'],
                                ['name' => 'code_article', 'label' => 'Code article'],
                                ['name' => 'repere_equipement', 'label' => 'Repère équipement'],
                                ['name' => 'designation_equipement', 'label' => 'Désignation équipement'],
                                ['name' => 'fabricant', 'label' => 'Fabricant'],
                                ['name' => 'type', 'label' => 'Type'],
                                ['name' => 'numero_serie_fabricant', 'label' => 'N° série fabricant'],
                                ['name' => 'designation_article', 'label' => 'Désignation article'],
                                ['name' => 'numero_poste', 'label' => 'N° poste'],
                                ['name' => 'quantite', 'label' => 'Quantité'],
                                ['name' => 'unite', 'label' => 'Unité'],
                                ['name' => 'poste_technique', 'label' => 'Poste technique'],
                                ['name' => 'metier', 'label' => 'Métier'],
                                ['name' => 'date_creation', 'label' => 'Date création', 'type' => 'date'],
                                ['name' => 'source', 'label' => 'Source']
                            ];
                            foreach ($fields as $f): ?>
                                <div class="col-md-6">
                                    <label class="form-label"><?= $f['label'] ?></label>
                                    <input type="<?= $f['type'] ?? 'text' ?>" name="<?= $f['name'] ?>" class="form-control">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="addNomenclatureBtn">Ajouter</button>
                        </div>
                        <div id="addNomenclatureMsg" class="w-100 mt-2"></div>
                    </form>
                </div>
            </div>

            <!-- Modal Modification Nomenclature -->
            <div class="modal fade" id="editNomenclatureModal" tabindex="-1" aria-labelledby="editNomenclatureModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" id="editNomenclatureForm" autocomplete="off">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editNomenclatureModalLabel">Modifier une nomenclature</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body row g-3" id="editNomenclatureFields">
                            <!-- Champs injectés dynamiquement -->
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="editNomenclatureBtn">Enregistrer</button>
                            <div id="editNomenclatureMsg" class="w-100 mt-2"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteneur pour les alertes -->
    <div id="alerts-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>

    <!-- Chart.js -->
    <script src="plugins/js/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>

    <!-- Initialisation des graphiques -->
    <script>
        // Données pour les graphiques depuis PHP
        const catLabels = <?= json_encode($catLabels) ?>;
        const catData = <?= json_encode($catData) ?>;
        const uniteLabels = <?= json_encode($uniteLabels) ?>;
        const uniteData = <?= json_encode($uniteData) ?>;

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Création des mini graphiques
        document.addEventListener('DOMContentLoaded', function() {
            // Mini Pie Chart Famille
            const ctxFamille = document.getElementById('miniPieNomenclature');
            if (ctxFamille && catLabels.length > 0) {
                new Chart(ctxFamille, {
                    type: 'doughnut',
                    data: {
                        labels: catLabels.slice(0, 5),
                        datasets: [{
                            data: catData.slice(0, 5),
                            backgroundColor: [
                                '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa'
                            ],
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
            }

            // Mini Pie Chart Unité
            const ctxUnite = document.getElementById('miniPieUniteNomenclature');
            if (ctxUnite && uniteLabels.length > 0) {
                new Chart(ctxUnite, {
                    type: 'doughnut',
                    data: {
                        labels: uniteLabels.slice(0, 5),
                        datasets: [{
                            data: uniteData.slice(0, 5),
                            backgroundColor: [
                                '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa'
                            ],
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
            }
        });
    </script>

    <!-- JavaScript moderne pour la gestion des nomenclatures -->
    <script src="js/function_nomenclatures_updated.js"></script>
</body>

</html>