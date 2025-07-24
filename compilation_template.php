<?php
require_once 'includes/auth.php';
require_once 'model/Database.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Compilation Template SPL - Moderne</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="css/style_dashboard.css" rel="stylesheet">
    <link href="css/style_equipements.css" rel="stylesheet">
    <link href="css/style_template_spl.css" rel="stylesheet">
    <style>
        .chart-wrapper {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .highlight {
            border-color: #1976d2 !important;
            background-color: rgba(25, 118, 210, 0.1) !important;
        }

        #loadingIndicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1976d2;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            z-index: 1000;
            display: none;
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .table-responsive {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="menu-toggle material-icons d-lg-none" onclick="toggleSidebar()">menu</span>
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">
                    <span class="material-icons" style="vertical-align: middle; margin-right: 10px;">psychology</span>
                    Compilation Template SPL
                </h2>
                <div class="d-flex gap-2">
                    <button id="exportExcel" class="btn btn-outline-success">
                        <span class="material-icons">file_download</span>Export Excel
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                        <span class="material-icons">upload_file</span>Import Excel
                    </button>
                    <a href="logout.php" class="btn btn-outline-primary">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>

            <!-- Mini Cards Stats -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#1976d2;">table_view</span>
                        <div>
                            <div id="totalLignes" style="font-size:1.5rem;font-weight:700;">0</div>
                            <div class="text-muted">Lignes SPL</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div id="totalArticles" style="font-size:1.5rem;font-weight:700;">0</div>
                            <div class="text-muted">Articles Uniques</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#ff8f00;">build</span>
                        <div>
                            <div id="totalMetiers" style="font-size:1.5rem;font-weight:700;">0</div>
                            <div class="text-muted">Métiers</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#e53935;">business</span>
                        <div>
                            <div id="totalFabricants" style="font-size:1.5rem;font-weight:700;">0</div>
                            <div class="text-muted">Fabricants</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques Statistiques -->
            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><span class="material-icons me-2">pie_chart</span>Répartition par Métier</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrapper">
                                <canvas id="metiersChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><span class="material-icons me-2">bar_chart</span>Top Unités</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrapper">
                                <canvas id="unitesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><span class="material-icons me-2">timeline</span>Évolution Imports</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrapper">
                                <canvas id="evolutionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Section Filtres -->
            <div class="filter-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><span class="material-icons me-2">filter_list</span>Filtres avancés</h5>
                    <button id="resetFilters" class="btn btn-outline-secondary btn-sm">
                        <span class="material-icons">refresh</span>Reset
                    </button>
                </div>
                <div class="row g-2" id="filterRow">
                    <div class="col-md-2">
                        <input type="text" name="numero" class="form-control form-control-sm" placeholder="N° SPL">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="code_sap" class="form-control form-control-sm" placeholder="Code SAP">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="code_article" class="form-control form-control-sm" placeholder="Code Article">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="metier" class="form-control form-control-sm" placeholder="Métier">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="equipement" class="form-control form-control-sm" placeholder="Équipement">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="fabricant" class="form-control form-control-sm" placeholder="Fabricant">
                    </div>
                </div>
            </div>

            <!-- Tableau Principal -->
            <div class="card shadow-sm table-modern">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">
                            <span class="material-icons me-2">table_rows</span>
                            Template SPL - Lignes Compilées
                        </h5>
                        <div id="paginationInfo" class="text-muted small"></div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th><span class="material-icons">tag</span> N°</th>
                                    <th><span class="material-icons">qr_code</span> Code SAP</th>
                                    <th><span class="material-icons">inventory</span> Code Article</th>
                                    <th><span class="material-icons">tag</span> Qté</th>
                                    <th><span class="material-icons">description</span> Désignation</th>
                                    <th><span class="material-icons">straighten</span> Unité</th>
                                    <th><span class="material-icons">work</span> Métier</th>
                                    <th><span class="material-icons">confirmation_number</span> N° Pièce</th>
                                    <th><span class="material-icons">business</span> Fabricant</th>
                                    <th><span class="material-icons">precision_manufacturing</span> Équipement</th>
                                    <th><span class="material-icons">schedule</span> Import</th>
                                </tr>
                            </thead>
                            <tbody id="templatesTableBody" class="fade-in">
                                <!-- Données chargées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Modal Import Excel -->
            <div class="modal fade modal-modern" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="importModalLabel">
                                <span class="material-icons me-2">cloud_upload</span>
                                Import Template SPL Excel
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <span class="material-icons me-2">info</span>
                                Importez un fichier Excel contenant une feuille "SPL" avec les colonnes : N°, Code SAP, Code Article, Qté, Désignation, Unité, Métier, N° Pièce, Fabricant, Équipement
                            </div>

                            <div id="dropArea" class="drop-area-modern mb-3">
                                <span class="material-icons" style="font-size:3rem;color:#1976d2;">cloud_upload</span>
                                <h5 class="mt-3 mb-2">Glissez-déposez votre fichier Excel ici</h5>
                                <p class="text-muted">ou cliquez pour sélectionner un fichier</p>
                                <input type="file" id="excelFileInput" name="excel_file" accept=".xls,.xlsx" style="display:none;" required>
                                <div id="fileName" class="text-success mt-3 fw-bold"></div>
                            </div>

                            <div class="progress progress-modern mb-3" style="display:none;" id="importProgressBarContainer">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="importProgressBar" style="width:0%">
                                    <span class="fw-bold">0%</span>
                                </div>
                            </div>

                            <div id="importResult"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-modern" data-bs-dismiss="modal">
                                <span class="material-icons">close</span>Annuler
                            </button>
                            <button type="button" class="btn btn-success btn-modern" id="startImportBtn" disabled>
                                <span class="material-icons">upload</span>Importer
                            </button>
                        </div>
                    </div>
                </div>
            </div> <!-- Loading Indicator -->
            <div id="loadingIndicator">
                <span class="material-icons me-2">refresh</span>
                Chargement...
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script src="js/function_template_spl_modern.js"></script>
</body>

</html>