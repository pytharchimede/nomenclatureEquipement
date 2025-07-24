<?php
session_start();
require_once 'model/Database.php';
require_once 'model/Nomenclature.php';
require_once 'includes/auth.php';

// Chargement initial des premières données RGM (pour éviter l'écran vide)
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM nomenclatures WHERE source = 'RGM' ORDER BY date_creation DESC LIMIT 50");
$stmt->execute();
$initialRgmData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Centre de Gestion RGM Synthèse - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .content {
            padding: 1rem;
            margin-left: 0;
            transition: all 0.3s ease;
        }

        .rgm-hero {
            background: linear-gradient(135deg, #e65100 0%, #ff9800 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .rgm-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1) rotate(0deg);
            }

            50% {
                transform: scale(1.1) rotate(180deg);
            }
        }

        .import-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            margin-bottom: 30px;
        }

        .import-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .file-upload-zone {
            border: 3px dashed #e65100;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: linear-gradient(45deg, rgba(230, 81, 0, 0.05), rgba(255, 152, 0, 0.05));
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-zone:hover {
            border-color: #ff9800;
            background: linear-gradient(45deg, rgba(230, 81, 0, 0.1), rgba(255, 152, 0, 0.1));
            transform: scale(1.02);
        }

        .file-upload-zone.dragover {
            border-color: #4caf50;
            background: linear-gradient(45deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05));
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #e65100, #ff9800);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .filter-panel {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .data-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            height: 600px;
            display: flex;
            flex-direction: column;
        }

        .table-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
        }

        .table-container::-webkit-scrollbar {
            width: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #e65100, #ff9800);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #d84315, #f57c00);
        }

        .table-info-bar {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 20px;
            flex-shrink: 0;
            border-radius: 15px 15px 0 0;
        }

        .table-rgm {
            margin-bottom: 0;
        }

        .table-rgm th {
            background: linear-gradient(45deg, #e65100, #ff9800);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-rgm td {
            padding: 12px 15px;
            border-color: #f0f0f0;
            vertical-align: middle;
        }

        .table-rgm tbody tr:hover {
            background: linear-gradient(90deg, rgba(230, 81, 0, 0.05), rgba(255, 152, 0, 0.05));
        }

        .btn-modern {
            background: linear-gradient(45deg, #e65100, #ff9800);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(230, 81, 0, 0.4);
            color: white;
        }

        .btn-outline-modern {
            background: transparent;
            border: 2px solid #e65100;
            color: #e65100;
            padding: 10px 23px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline-modern:hover {
            background: linear-gradient(45deg, #e65100, #ff9800);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-success {
            background: linear-gradient(45deg, #4caf50, #66bb6a);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(45deg, #ff9800, #ffb74d);
            color: white;
        }

        .badge-rgm {
            background: linear-gradient(45deg, #e65100, #ff9800);
            color: white;
        }

        .scroll-hint {
            position: absolute;
            bottom: 10px;
            right: 20px;
            background: rgba(230, 81, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            animation: fadeInOut 3s infinite;
            z-index: 5;
            pointer-events: none;
        }

        @keyframes fadeInOut {

            0%,
            100% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }
        }

        .table-container.scrolled .scroll-hint {
            display: none;
        }

        .sync-panel {
            background: linear-gradient(145deg, #e8f5e8, #f1f8e9);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #4caf50;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(45deg, #e65100, #ff9800);
            color: white;
            border-radius: 20px 20px 0 0;
            border-bottom: none;
        }

        .progress-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .progress-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            min-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .btn-floating {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #e65100, #ff9800);
            border: none;
            color: white;
            font-size: 24px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .btn-floating:hover {
            transform: scale(1.1) rotate(180deg);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 content">
            <div class="container-fluid p-4">
                <!-- Hero Section -->
                <div class="rgm-hero">
                    <div class="position-relative">
                        <h1 class="display-4 font-weight-bold mb-3">
                            <i class="material-icons" style="font-size: 3rem; vertical-align: middle;">engineering</i>
                            Centre de Gestion RGM Synthèse
                        </h1>
                        <p class="lead mb-4">Interface avancée pour la gestion des données RGM avec synchronisation nomenclature</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="#" class="btn-modern" onclick="showImportModal()">
                                <i class="material-icons">cloud_upload</i>
                                Importer RGM
                            </a>
                            <a href="#" class="btn-outline-modern" onclick="exportRgmData()">
                                <i class="material-icons">get_app</i>
                                Exporter Excel
                            </a>
                            <a href="nomenclatures.php" class="btn-outline-modern">
                                <i class="material-icons">view_list</i>
                                Voir Nomenclature Complète
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number" id="totalCount">0</div>
                        <div class="text-muted font-weight-bold">Total Éléments RGM</div>
                        <small class="text-success">
                            <i class="material-icons" style="font-size: 16px;">trending_up</i>
                            Données synchronisées
                        </small>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="categoriesCount">0</div>
                        <div class="text-muted font-weight-bold">Désignations Uniques</div>
                        <small class="text-info">
                            <i class="material-icons" style="font-size: 16px;">category</i>
                            <span id="topCategory">-</span>
                        </small>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="unitesCount">0</div>
                        <div class="text-muted font-weight-bold">Unités Différentes</div>
                        <small class="text-warning">
                            <i class="material-icons" style="font-size: 16px;">straighten</i>
                            <span id="topUnite">-</span>
                        </small>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="syncedCount">0</div>
                        <div class="text-muted font-weight-bold">Total Données RGM</div>
                        <small class="text-success">
                            <i class="material-icons" style="font-size: 16px;">storage</i>
                            Dans nomenclature globale
                        </small>
                    </div>
                </div>

                <!-- Information Panel -->
                <div class="sync-panel">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                <i class="material-icons text-success" style="vertical-align: middle;">info</i>
                                Données RGM dans Nomenclature
                            </h5>
                            <p class="mb-0 text-muted">Les données RGM sont intégrées directement dans la nomenclature globale avec source = "RGM"</p>
                        </div>
                        <div>
                            <a href="nomenclatures.php" class="btn btn-info btn-sm">
                                <i class="material-icons">view_list</i>
                                Voir tout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filters Panel -->
                <div class="filter-panel">
                    <h5 class="mb-3">
                        <i class="material-icons" style="vertical-align: middle;">filter_list</i>
                        Filtres de Recherche
                    </h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label font-weight-bold">Référence Équipement</label>
                            <input type="text" class="form-control" id="filterRepere" placeholder="Filtrer par repère...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label font-weight-bold">Code Article</label>
                            <input type="text" class="form-control" id="filterCode" placeholder="Filtrer par code...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label font-weight-bold">Désignation</label>
                            <input type="text" class="form-control" id="filterDesignation" placeholder="Filtrer par désignation...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label font-weight-bold">Unité</label>
                            <select class="form-control" id="filterUnite">
                                <option value="">Toutes les unités</option>
                                <!-- Options chargées dynamiquement -->
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn-modern" onclick="applyFilters()">
                            <i class="material-icons">search</i>
                            Appliquer Filtres
                        </button>
                        <button class="btn-outline-modern" onclick="clearFilters()">
                            <i class="material-icons">clear</i>
                            Effacer
                        </button>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="data-table">
                    <div class="table-info-bar">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <span id="currentRange" class="font-weight-bold text-primary">Chargement...</span>
                                <span class="text-muted">|</span>
                                <span id="totalItems" class="text-muted">Total: 0 éléments</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="material-icons text-muted">info</i>
                                <span class="text-muted small">Scroll pour charger plus</span>
                            </div>
                        </div>
                    </div>
                    <div class="table-container" id="tableContainer">
                        <table class="table table-rgm table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Repère Équipement</th>
                                    <th style="width: 15%;">Code Article</th>
                                    <th style="width: 35%;">Désignation Article</th>
                                    <th style="width: 12%;">Quantité</th>
                                    <th style="width: 10%;">Unité</th>
                                    <th style="width: 13%;">Date Import</th>
                                </tr>
                            </thead>
                            <tbody id="rgmTableBody">
                                <!-- Données chargées dynamiquement -->
                            </tbody>
                        </table>
                        <div class="scroll-hint">
                            <i class="material-icons" style="font-size: 16px;">keyboard_arrow_down</i>
                            Scroll pour plus
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div class="text-center mt-4" id="loadingIndicator" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                    <p class="mt-2 text-muted">Chargement des données RGM...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="material-icons" style="vertical-align: middle;">cloud_upload</i>
                        Import de Données RGM
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="import-card">
                        <div class="file-upload-zone" id="dropZone">
                            <i class="material-icons mb-3" style="font-size: 3rem; color: #e65100;">cloud_upload</i>
                            <h5>Glissez votre fichier ici</h5>
                            <p class="text-muted">ou cliquez pour sélectionner</p>
                            <input type="file" id="fileInput" accept=".csv,.xlsx,.xls" style="display: none;">
                            <small class="text-muted">Formats supportés: CSV, Excel (.xlsx, .xls)</small>
                        </div>
                        <div class="mt-4" id="fileInfo" style="display: none;">
                            <div class="alert alert-info">
                                <i class="material-icons">info</i>
                                <span id="fileName"></span> - <span id="fileSize"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="startImport()" id="importBtn" disabled>
                        <i class="material-icons">upload</i>
                        Importer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Overlay -->
    <div class="progress-overlay" id="progressOverlay">
        <div class="progress-content">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <h5 id="progressTitle">Traitement en cours...</h5>
            <p id="progressMessage" class="text-muted">Veuillez patienter</p>
            <div class="progress mt-3">
                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-action">
        <button class="btn-floating" onclick="scrollToTop()">
            <i class="material-icons">keyboard_arrow_up</i>
        </button>
    </div>

    </div> <!-- container-fluid -->
    </div> <!-- content -->
    </div> <!-- d-flex -->

    <script src="plugins/js/jquery.min.js"></script>
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales pour la pagination infinie
        let currentPage = 1;
        let isLoading = false;
        let hasMoreData = true;
        let currentFilters = {};
        let totalCount = 0;
        let loadedCount = 0;

        // Initialisation
        $(document).ready(function() {
            initializeFilters();
            loadStatistics();
            loadInitialData();
            setupInfiniteScroll();
            setupDropZone();
            updateLastSyncTime();
        });

        // Fonctions pour les modals
        function showImportModal() {
            $('#importModal').modal('show');
        }

        function showEditModal(id) {
            // Code pour charger les données et afficher le modal d'édition
            $('#editModal').modal('show');
        }

        function showDeleteModal(id) {
            // Code pour afficher le modal de confirmation de suppression
            $('#deleteModal').modal('show');
        }

        // Chargement des statistiques
        function loadStatistics() {
            $.ajax({
                url: 'api/rgm_stats.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $('#totalCount').text(data.stats.total_count);
                        $('#categoriesCount').text(data.stats.unique_designations);
                        $('#unitesCount').text(data.stats.unique_unites);
                        $('#syncedCount').text(data.stats.synced_count);

                        if (data.stats.top_designation) {
                            $('#topCategory').text(data.stats.top_designation.designation_article);
                        }

                        if (data.stats.top_unite) {
                            $('#topUnite').text(data.stats.top_unite.unite);
                        }

                        // Charger les options d'unités dans le filtre
                        const $uniteFilter = $('#filterUnite');
                        $uniteFilter.empty().append('<option value="">Toutes les unités</option>');

                        if (data.stats.unites) {
                            data.stats.unites.forEach(function(unite) {
                                $uniteFilter.append(`<option value="${unite.unite}">${unite.unite} (${unite.count})</option>`);
                            });
                        }
                    }
                },
                error: function() {
                    console.error('Erreur lors du chargement des statistiques');
                }
            });
        }

        // Chargement initial des données
        function loadInitialData() {
            loadedCount = 0;
            $('#rgmTableBody').empty();
            loadMoreData(true);
        }

        // Chargement de données supplémentaires
        function loadMoreData(isInitial = false) {
            if (isLoading || (!hasMoreData && !isInitial)) return;

            isLoading = true;
            $('#loadingIndicator').show();

            const params = {
                page: currentPage,
                limit: 50,
                ...currentFilters
            };

            $.ajax({
                url: 'api/rgm_pagination.php',
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        totalCount = response.total;
                        hasMoreData = response.has_more;

                        // Ajouter les nouvelles lignes
                        response.data.forEach(function(item, index) {
                            const row = createTableRow(item, loadedCount + index + 1);
                            $('#rgmTableBody').append(row);
                        });

                        loadedCount += response.data.length;
                        currentPage++;

                        // Mettre à jour les indicateurs
                        updateTableInfo();

                        // Animation d'apparition pour les nouvelles lignes
                        $('#rgmTableBody tr').slice(-response.data.length).each(function(index) {
                            $(this).css('opacity', '0').delay(index * 50).animate({
                                opacity: 1
                            }, 300);
                        });
                    } else {
                        console.error('Erreur:', response.message);
                    }
                },
                error: function() {
                    console.error('Erreur lors du chargement des données');
                },
                complete: function() {
                    isLoading = false;
                    $('#loadingIndicator').hide();
                }
            });
        }

        // Création d'une ligne de tableau
        function createTableRow(item, index) {
            const dateFormat = item.date_creation ?
                new Date(item.date_creation).toLocaleDateString('fr-FR') : '-';

            return `
                <tr>
                    <td class="font-weight-bold">${item.repere_equipement || '-'}</td>
                    <td><code>${item.code_article || '-'}</code></td>
                    <td>${item.designation_article || '-'}</td>
                    <td class="text-right font-weight-bold">${item.quantite || 0}</td>
                    <td><span class="badge badge-rgm">${item.unite || '-'}</span></td>
                    <td class="text-muted">${dateFormat}</td>
                </tr>
            `;
        }

        // Mise à jour des informations du tableau
        function updateTableInfo() {
            const start = Math.min(1, loadedCount);
            const end = loadedCount;
            $('#currentRange').text(`${start}-${end}`);
            $('#totalItems').text(`Total: ${totalCount} éléments`);
        }

        // Configuration du scroll infini
        function setupInfiniteScroll() {
            const container = document.getElementById('tableContainer');

            container.addEventListener('scroll', function() {
                // Masquer l'hint de scroll après le premier scroll
                container.classList.add('scrolled');

                // Vérifier si on approche du bas
                if (this.scrollTop + this.clientHeight >= this.scrollHeight - 100) {
                    loadMoreData();
                }
            });
        }

        // Initialisation des filtres
        function initializeFilters() {
            // Débounce pour les champs de texte
            let filterTimeout;

            $('#filterRepere, #filterCode, #filterDesignation').on('input', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(applyFilters, 500);
            });

            $('#filterUnite').on('change', applyFilters);
        }

        // Application des filtres
        function applyFilters() {
            currentFilters = {
                repere: $('#filterRepere').val(),
                code: $('#filterCode').val(),
                designation: $('#filterDesignation').val(),
                unite: $('#filterUnite').val()
            };

            // Reset pagination
            currentPage = 1;
            hasMoreData = true;
            loadedCount = 0;
            $('#rgmTableBody').empty();

            loadMoreData(true);
        }

        // Effacement des filtres
        function clearFilters() {
            $('#filterRepere, #filterCode, #filterDesignation').val('');
            $('#filterUnite').val('');
            currentFilters = {};

            // Reset pagination
            currentPage = 1;
            hasMoreData = true;
            loadedCount = 0;
            $('#rgmTableBody').empty();

            loadMoreData(true);
        }

        // Configuration de la zone de drop
        function setupDropZone() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');

            dropZone.addEventListener('click', () => fileInput.click());

            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelection(files[0]);
                }
            });

            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    handleFileSelection(e.target.files[0]);
                }
            });
        }

        // Gestion de la sélection de fichier
        function handleFileSelection(file) {
            $('#fileName').text(file.name);
            $('#fileSize').text(formatFileSize(file.size));
            $('#fileInfo').show();
            $('#importBtn').prop('disabled', false);
        }

        // Formatage de la taille de fichier
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Functions pour les actions
        function showImportModal() {
            $('#importModal').modal('show');
        }

        function startImport() {
            const fileInput = document.getElementById('fileInput');
            if (!fileInput.files[0]) return;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            showProgress('Import en cours...', 'Traitement du fichier RGM');

            $.ajax({
                url: 'api/import_rgm.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideProgress();
                    $('#importModal').modal('hide');

                    if (response.success) {
                        alert('Import réussi ! ' + response.imported + ' éléments importés.');
                        loadStatistics();
                        loadInitialData();
                    } else {
                        alert('Erreur lors de l\'import: ' + response.message);
                    }
                },
                error: function() {
                    hideProgress();
                    alert('Erreur lors de l\'import');
                }
            });
        }

        function exportRgmData() {
            showProgress('Export en cours...', 'Génération du fichier Excel');

            const params = new URLSearchParams(currentFilters);
            window.location.href = 'api/export_rgm.php?' + params.toString();

            setTimeout(hideProgress, 2000);
        }

        function syncToNomenclature() {
            showProgress('Synchronisation...', 'Mise à jour de la nomenclature');

            $.ajax({
                url: 'api/sync_rgm_nomenclature.php',
                method: 'POST',
                success: function(response) {
                    hideProgress();
                    if (response.success) {
                        alert('Synchronisation réussie ! ' + response.synced + ' éléments synchronisés.');
                        loadStatistics();
                        loadInitialData();
                        updateLastSyncTime();
                    } else {
                        alert('Erreur lors de la synchronisation: ' + response.message);
                    }
                },
                error: function() {
                    hideProgress();
                    alert('Erreur lors de la synchronisation');
                }
            });
        }

        function forceSyncToNomenclature() {
            if (confirm('Forcer la synchronisation complète ? Cette opération peut prendre du temps.')) {
                syncToNomenclature();
            }
        }

        // Gestion de l'overlay de progression
        function showProgress(title, message) {
            $('#progressTitle').text(title);
            $('#progressMessage').text(message);
            $('#progressOverlay').css('display', 'flex');
        }

        function hideProgress() {
            $('#progressOverlay').hide();
        }

        // Scroll vers le haut
        function scrollToTop() {
            document.getElementById('tableContainer').scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Mise à jour de l'heure de dernière synchro
        function updateLastSyncTime() {
            const now = new Date();
            const timeStr = now.toLocaleString('fr-FR');
            $('#lastSync').text(timeStr);
        }
    </script>
</body>

</html>