<?php
session_start();
require_once 'model/Database.php';
require_once 'model/Quantitatif.php';
require_once 'model/Famille.php';
require_once 'includes/auth.php';

// Chargement initial des premières données (pour éviter l'écran vide)
$initialQuantitatif = Quantitatif::getPaginated(1, 50);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Centre de Gestion Quantitatif - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .quantitatif-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .quantitatif-hero::before {
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
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-zone:hover {
            border-color: #764ba2;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            transform: scale(1.02);
        }

        .file-upload-zone.dragover {
            border-color: #28a745;
            background: linear-gradient(45deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
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
            background: linear-gradient(45deg, #667eea, #764ba2);
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
            /* Hauteur fixe pour la box */
            display: flex;
            flex-direction: column;
        }

        .table-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
        }

        /* Scrollbar personnalisée pour le container */
        .table-container::-webkit-scrollbar {
            width: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
        }

        /* Animation pour indiquer le défilement possible */
        .scroll-hint {
            position: absolute;
            bottom: 10px;
            right: 20px;
            background: rgba(102, 126, 234, 0.9);
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

        /* Masquer l'indicateur quand on scroll */
        .table-container.scrolled .scroll-hint {
            display: none;
        }

        .table-quantitatif {
            margin-bottom: 0;
        }

        .table-quantitatif th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-quantitatif td {
            padding: 12px 15px;
            border-color: #f0f0f0;
            vertical-align: middle;
        }

        .table-quantitatif tbody tr:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
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

        .progress-ring {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
        }

        .progress-ring__circle {
            stroke: #667eea;
            stroke-linecap: round;
            stroke-dasharray: 251;
            stroke-dashoffset: 251;
            animation: progress-ring 2s ease-in-out;
        }

        @keyframes progress-ring {
            to {
                stroke-dashoffset: 0;
            }
        }

        .btn-modern {
            background: linear-gradient(45deg, #667eea, #764ba2);
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
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-outline-modern {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
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
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
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
            background: linear-gradient(45deg, #667eea, #764ba2);
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

        .import-history {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .history-item {
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 0 10px 10px 0;
            transition: all 0.3s ease;
        }

        .history-item:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(5px);
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-success {
            background: linear-gradient(45deg, #28a745, #34ce57);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(45deg, #ffc107, #ffcd3a);
            color: #333;
        }

        .badge-danger {
            background: linear-gradient(45deg, #dc3545, #e85563);
            color: white;
        }

        #logContent {
            background: #2d3748;
            color: #a0aec0;
            border: 1px solid #4a5568;
            font-family: 'JetBrains Mono', 'Consolas', 'Courier New', monospace;
            font-size: 0.9em;
            padding: 20px;
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 10px;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px 20px 0 0;
            border-bottom: none;
        }

        .table-info-bar {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 20px;
            flex-shrink: 0;
            border-radius: 15px 15px 0 0;
        }

        /* Animation pour indiquer le défilement possible */
        .scroll-hint {
            position: absolute;
            bottom: 10px;
            right: 20px;
            background: rgba(102, 126, 234, 0.9);
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

        /* Masquer l'indicateur quand on scroll */
        .table-container.scrolled .scroll-hint {
            display: none;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'menu.php'; ?>
        <div class="flex-grow-1 content">
            <!-- Hero Section -->
            <div class="quantitatif-hero">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-3" style="font-weight: 700; font-size: 2.5rem;">
                            <span class="material-icons me-3" style="font-size: 3rem; vertical-align: middle;">assessment</span>
                            Centre de Gestion Quantitatif
                        </h1>
                        <p class="mb-0" style="font-size: 1.2rem; opacity: 0.9;">
                            Gestion avancée des livrables avec import Excel multi-feuilles et traçabilité complète
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="exportations.php" class="btn btn-outline-light">
                                <span class="material-icons me-2">cloud_download</span>Exports
                            </a>
                            <a href="logout.php" class="btn btn-outline-light">
                                <span class="material-icons me-2">logout</span>Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="stats-grid" id="statsContainer">
                <div class="stat-card">
                    <div class="stat-number" id="totalQuantitatif">-</div>
                    <small class="text-muted">Total Éléments</small>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalFamilles">-</div>
                    <small class="text-muted">Familles</small>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalReperes">-</div>
                    <small class="text-muted">Repères Uniques</small>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalUnites">-</div>
                    <small class="text-muted">Unités Différentes</small>
                </div>
            </div>

            <!-- Import Section -->
            <div class="import-card">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="mb-3" style="color: #667eea; font-weight: 600;">
                            <span class="material-icons me-2" style="vertical-align: middle;">upload_file</span>
                            Import de Fichier Excel Multi-Feuilles
                        </h4>
                        <div class="file-upload-zone" id="fileUploadZone">
                            <form id="importForm" enctype="multipart/form-data">
                                <span class="material-icons mb-3" style="font-size: 4rem; color: #667eea;">cloud_upload</span>
                                <h5 class="mb-2">Glissez votre fichier Excel ici</h5>
                                <p class="text-muted mb-3">ou cliquez pour sélectionner un fichier</p>
                                <input type="file" name="quantitatif" id="quantitatif" accept=".xlsx,.xls" required style="display:none;">
                                <button type="button" class="btn-modern" onclick="document.getElementById('quantitatif').click()">
                                    <span class="material-icons">folder_open</span>
                                    Choisir un fichier
                                </button>
                            </form>
                        </div>
                        <div class="mt-3">
                            <div class="alert alert-info border-0" style="background: linear-gradient(45deg, rgba(13, 202, 240, 0.1), rgba(13, 110, 253, 0.1));">
                                <strong>Format attendu :</strong> Fichier Excel avec une feuille par famille contenant les colonnes :
                                Unité, Quantité, Repère, et colonnes de suivi (Av. %, Fiches Photos, etc.)
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="import-history">
                            <h6 class="mb-3" style="color: #667eea; font-weight: 600;">
                                <span class="material-icons me-2" style="font-size: 1.2rem;">history</span>
                                Historique des Imports
                            </h6>
                            <div id="importHistory">
                                <div class="text-muted text-center py-3">
                                    <span class="material-icons mb-2" style="font-size: 2rem; opacity: 0.5;">inbox</span>
                                    <br>Aucun import récent
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="filter-panel">
                <h5 class="mb-3" style="color: #667eea; font-weight: 600;">
                    <span class="material-icons me-2" style="vertical-align: middle;">filter_list</span>
                    Filtres et Actions
                </h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Famille</label>
                        <input type="text" id="filterFamille" class="form-control" placeholder="Filtrer par famille">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Repère</label>
                        <input type="text" id="filterRepere" class="form-control" placeholder="Filtrer par repère">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Unité</label>
                        <input type="text" id="filterUnite" class="form-control" placeholder="Filtrer par unité">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Actions</label>
                        <div class="d-flex gap-2">
                            <button id="normalizeRepere" class="btn-outline-modern" title="Normaliser les repères">
                                <span class="material-icons">auto_fix_high</span>
                            </button>
                            <div class="dropdown">
                                <button class="btn-modern dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <span class="material-icons">download</span>
                                    Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" id="exportQuantitatif" href="#">
                                            <span class="material-icons me-2">description</span>CSV
                                        </a></li>
                                    <li><a class="dropdown-item" id="exportQuantitatifExcel" href="#">
                                            <span class="material-icons me-2">table_view</span>Excel
                                        </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table de données -->
            <div class="data-table">
                <!-- Barre d'informations fixe en haut -->
                <div class="table-info-bar d-flex justify-content-between align-items-center">
                    <div id="tableInfo" class="text-muted">
                        <span class="material-icons me-2" style="vertical-align: middle;">info</span>
                        Chargement des données...
                    </div>
                    <div id="loadingIndicator" class="text-primary" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Chargement...
                    </div>
                </div>

                <!-- Container avec défilement -->
                <div class="table-container" id="tableContainer">
                    <!-- Indicateur de défilement -->
                    <div class="scroll-hint" id="scrollHint">
                        <span class="material-icons" style="font-size: 1rem; vertical-align: middle;">keyboard_arrow_down</span>
                        Faites défiler pour plus de données
                    </div>

                    <table class="table table-quantitatif">
                        <thead>
                            <tr>
                                <th>
                                    <span class="material-icons me-2" style="vertical-align: middle;">category</span>
                                    Famille
                                </th>
                                <th>
                                    <span class="material-icons me-2" style="vertical-align: middle;">place</span>
                                    Repère
                                </th>
                                <th>
                                    <span class="material-icons me-2" style="vertical-align: middle;">straighten</span>
                                    Unité
                                </th>
                                <th>
                                    <span class="material-icons me-2" style="vertical-align: middle;">numbers</span>
                                    Quantité
                                </th>
                                <th>
                                    <span class="material-icons me-2" style="vertical-align: middle;">more_horiz</span>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="quantitatifTableBody">
                            <!-- Données chargées dynamiquement -->
                        </tbody>
                    </table>

                    <!-- Message de fin de données -->
                    <div id="endOfDataMessage" class="text-center text-muted py-4" style="display: none;">
                        <span class="material-icons mb-2" style="font-size: 2rem; opacity: 0.5;">check_circle</span>
                        <br>Toutes les données ont été chargées
                    </div>

                    <!-- Indicateur de chargement en bas -->
                    <div id="bottomLoadingIndicator" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <div class="mt-2 text-muted">Chargement des données suivantes...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Overlays et Modals -->
    <div class="progress-overlay" id="progressOverlay">
        <div class="progress-content">
            <svg class="progress-ring" width="80" height="80">
                <circle class="progress-ring__circle" stroke="#667eea" stroke-width="4" fill="transparent" r="36" cx="40" cy="40" />
            </svg>
            <h5 class="mb-2">Import en cours...</h5>
            <div class="progress mb-3" style="height: 8px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div>
            </div>
            <div id="progressText" class="text-muted">Initialisation...</div>
        </div>
    </div>

    <!-- Modal de rapport d'import -->
    <div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logModalLabel">
                        <span class="material-icons me-2">analytics</span>
                        Rapport d'Import Détaillé
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-number text-success" id="importSuccess">0</div>
                                <small class="text-muted">Lignes Importées</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-number text-warning" id="importWarnings">0</div>
                                <small class="text-muted">Avertissements</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-number text-danger" id="importErrors">0</div>
                                <small class="text-muted">Erreurs</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Journal détaillé :</h6>
                        <pre id="logContent"></pre>
                    </div>

                    <div class="d-flex gap-2">
                        <a id="downloadLog" href="#" download="rapport_import.txt" class="btn-modern">
                            <span class="material-icons">download</span>
                            Télécharger le Rapport
                        </a>
                        <a id="viewLog" href="#" class="btn-outline-modern" target="_blank">
                            <span class="material-icons">open_in_new</span>
                            Ouvrir dans un Nouvel Onglet
                        </a>
                        <a id="downloadResidu" href="#" download="residu_import.csv" class="btn-outline-modern" style="display:none;">
                            <span class="material-icons">warning</span>
                            Télécharger les Résidus
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de détails d'un élément -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">
                        <span class="material-icons me-2">info</span>
                        Détails de l'Élément
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <!-- Contenu dynamique -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton flottant d'aide -->
    <div class="floating-action">
        <button class="btn-floating" data-bs-toggle="modal" data-bs-target="#helpModal" title="Aide">
            <span class="material-icons">help</span>
        </button>
    </div>

    <!-- Modal d'aide -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">
                        <span class="material-icons me-2">help_center</span>
                        Guide d'Utilisation - Quantitatif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3">Format de Fichier Excel</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <span class="material-icons text-success me-2" style="font-size: 1rem;">check_circle</span>
                                    Une feuille par famille d'équipements
                                </li>
                                <li class="mb-2">
                                    <span class="material-icons text-success me-2" style="font-size: 1rem;">check_circle</span>
                                    Colonnes obligatoires : Unité, Quantité, Repère
                                </li>
                                <li class="mb-2">
                                    <span class="material-icons text-success me-2" style="font-size: 1rem;">check_circle</span>
                                    Colonnes de suivi optionnelles (Av. %, Fiches Photos, etc.)
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3">Fonctionnalités</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <span class="material-icons text-info me-2" style="font-size: 1rem;">upload</span>
                                    Import par glisser-déposer ou sélection
                                </li>
                                <li class="mb-2">
                                    <span class="material-icons text-info me-2" style="font-size: 1rem;">filter_list</span>
                                    Filtrage avancé par famille, repère, unité
                                </li>
                                <li class="mb-2">
                                    <span class="material-icons text-info me-2" style="font-size: 1rem;">auto_fix_high</span>
                                    Normalisation automatique des repères
                                </li>
                                <li class="mb-2">
                                    <span class="material-icons text-info me-2" style="font-size: 1rem;">download</span>
                                    Export CSV et Excel avec filtres appliqués
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>Astuce :</strong> Le système détecte automatiquement les feuilles valides dans votre fichier Excel
                        et importe uniquement celles contenant les colonnes requises.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script>
        // Variables globales
        let uploadInProgress = false;

        // Variables pour le défilement infini
        let currentPage = 1;
        let isLoading = false;
        let hasMoreData = true;
        let currentFilters = {};
        let allData = []; // Pour stocker toutes les données chargées
        let observer; // Pour l'Intersection Observer

        // Chargement des données avec pagination
        async function loadQuantitatifData(page = 1, resetData = false) {
            if (isLoading) return;

            isLoading = true;
            const loadingIndicator = document.getElementById('loadingIndicator');
            const bottomLoadingIndicator = document.getElementById('bottomLoadingIndicator');

            if (page === 1) {
                loadingIndicator.style.display = 'block';
            } else {
                bottomLoadingIndicator.style.display = 'block';
            }

            try {
                const params = new URLSearchParams({
                    page: page,
                    limit: 50,
                    ...currentFilters
                });

                const response = await fetch(`api/quantitatif_pagination.php?${params}`);
                const data = await response.json();

                if (data.success) {
                    if (resetData) {
                        allData = data.data;
                        currentPage = 1;
                    } else {
                        allData = [...allData, ...data.data];
                    }

                    hasMoreData = data.pagination.has_more;
                    currentPage = data.pagination.current_page;

                    renderTable();
                    updateTableInfo(data.pagination);

                    // Mettre à jour l'observer si on a plus de données
                    if (hasMoreData) {
                        observeLastRow();
                    } else {
                        document.getElementById('endOfDataMessage').style.display = 'block';
                    }
                } else {
                    throw new Error(data.error || 'Erreur lors du chargement des données');
                }
            } catch (error) {
                console.error('Erreur lors du chargement des données:', error);

                if (allData.length === 0) {
                    document.getElementById('quantitatifTableBody').innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <span class="material-icons mb-2" style="font-size: 3rem; opacity: 0.3; color: #dc3545;">error</span>
                                <br>Erreur lors du chargement des données
                                <br><small>${error.message}</small>
                            </td>
                        </tr>
                    `;
                }
            } finally {
                isLoading = false;
                loadingIndicator.style.display = 'none';
                bottomLoadingIndicator.style.display = 'none';
            }
        }

        // Rendu de la table avec toutes les données chargées
        function renderTable() {
            const tbody = document.getElementById('quantitatifTableBody');
            const tableContainer = document.getElementById('tableContainer');
            const scrollHint = document.getElementById('scrollHint');

            if (allData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <span class="material-icons mb-2" style="font-size: 3rem; opacity: 0.3;">inventory_2</span>
                            <br>Aucune donnée quantitative trouvée
                            <br><small>Importez un fichier Excel ou modifiez vos filtres</small>
                        </td>
                    </tr>
                `;
                scrollHint.style.display = 'none';
                return;
            }

            tbody.innerHTML = allData.map(q => `
                <tr data-id="${q.id}">
                    <td>
                        <span class="badge-status badge-success">
                            ${escapeHtml(q.famille || 'Non défini')}
                        </span>
                    </td>
                    <td>
                        <strong>${escapeHtml(q.repere || '')}</strong>
                    </td>
                    <td>${escapeHtml(q.unite || '')}</td>
                    <td>
                        <span class="badge bg-light text-dark">
                            ${escapeHtml(q.quantite || '')}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${q.id})">
                            <span class="material-icons" style="font-size: 1rem;">visibility</span>
                        </button>
                    </td>
                </tr>
            `).join('');

            // Afficher l'indicateur de défilement seulement s'il y a plus de données et qu'on n'a pas encore scrollé
            setTimeout(() => {
                const {
                    scrollHeight,
                    clientHeight
                } = tableContainer;
                if (hasMoreData && scrollHeight > clientHeight && !tableContainer.classList.contains('scrolled')) {
                    scrollHint.style.display = 'block';
                } else {
                    scrollHint.style.display = 'none';
                }
            }, 100);
        }

        // Mise à jour des informations de la table
        function updateTableInfo(pagination) {
            const tableInfo = document.getElementById('tableInfo');
            const {
                from,
                to,
                total_count
            } = pagination;

            tableInfo.innerHTML = `
                <span class="material-icons me-2" style="vertical-align: middle;">info</span>
                Affichage de ${from} à ${to} sur ${total_count} éléments
                ${Object.keys(currentFilters).length > 0 ? ' (filtré)' : ''}
            `;
        }

        // Configuration de l'Intersection Observer pour le défilement infini
        function setupInfiniteScroll() {
            const tableContainer = document.getElementById('tableContainer');

            const options = {
                root: tableContainer, // Observer dans le container de la table
                rootMargin: '50px',
                threshold: 0.1
            };

            observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && hasMoreData && !isLoading) {
                        loadQuantitatifData(currentPage + 1);
                    }
                });
            }, options);

            // Alternative : écouter l'événement de scroll sur le container
            tableContainer.addEventListener('scroll', () => {
                const {
                    scrollTop,
                    scrollHeight,
                    clientHeight
                } = tableContainer;

                // Masquer l'indicateur de défilement après le premier scroll
                if (scrollTop > 0) {
                    tableContainer.classList.add('scrolled');
                }

                // Si on est proche du bas (50px avant la fin)
                if (scrollTop + clientHeight >= scrollHeight - 50 && hasMoreData && !isLoading) {
                    loadQuantitatifData(currentPage + 1);
                }
            });
        }

        // Observer la dernière ligne pour déclencher le chargement
        function observeLastRow() {
            if (observer) {
                observer.disconnect();
            }

            const lastRow = document.querySelector('#quantitatifTableBody tr:last-child');
            if (lastRow && hasMoreData) {
                observer.observe(lastRow);
            }
        }

        // Fonction utilitaire pour échapper le HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Chargement des statistiques
        async function loadStats() {
            try {
                const response = await fetch('api/quantitatif_stats.php');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalQuantitatif').textContent = data.stats.total_elements || '0';
                    document.getElementById('totalFamilles').textContent = data.stats.total_familles || '0';
                    document.getElementById('totalReperes').textContent = data.stats.total_reperes || '0';
                    document.getElementById('totalUnites').textContent = data.stats.total_unites || '0';
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
            }
        }

        // Chargement de l'historique des imports
        async function loadImportHistory() {
            try {
                const response = await fetch('api/quantitatif_history.php');
                const data = await response.json();

                if (data.success && data.history.length > 0) {
                    const historyContainer = document.getElementById('importHistory');
                    historyContainer.innerHTML = data.history.map(item => `
                        <div class="history-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">${item.filename}</div>
                                    <small class="text-muted">${item.date}</small>
                                </div>
                                <span class="badge-status ${item.status === 'success' ? 'badge-success' : 'badge-warning'}">
                                    ${item.status === 'success' ? 'Réussi' : 'Partiel'}
                                </span>
                            </div>
                            <div class="mt-2">
                                <small>${item.lines_imported} lignes importées</small>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Erreur lors du chargement de l\'historique:', error);
            }
        }

        // Gestion du drag & drop
        function setupDragAndDrop() {
            const uploadZone = document.getElementById('fileUploadZone');
            const fileInput = document.getElementById('quantitatif');

            uploadZone.addEventListener('click', () => {
                if (!uploadInProgress) {
                    fileInput.click();
                }
            });

            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });

            uploadZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
            });

            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');

                if (!uploadInProgress && e.dataTransfer.files.length > 0) {
                    const file = e.dataTransfer.files[0];
                    if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
                        fileInput.files = e.dataTransfer.files;
                        handleFileUpload();
                    } else {
                        alert('Veuillez sélectionner un fichier Excel (.xlsx ou .xls)');
                    }
                }
            });
        }

        // Gestion de l'upload de fichier
        function handleFileUpload() {
            const form = document.getElementById('importForm');
            const fileInput = document.getElementById('quantitatif');

            if (!fileInput.files.length || uploadInProgress) return;

            uploadInProgress = true;
            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            // Affichage de l'overlay de progression
            const overlay = document.getElementById('progressOverlay');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            overlay.style.display = 'flex';

            // Gestion de la progression
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    if (percent < 100) {
                        progressText.textContent = `Envoi du fichier... ${percent}%`;
                    }
                }
            };

            xhr.onload = function() {
                progressBar.style.width = '100%';
                progressText.textContent = 'Traitement des données...';
            };

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    uploadInProgress = false;
                    overlay.style.display = 'none';

                    try {
                        const resp = JSON.parse(xhr.responseText);
                        showImportResults(resp);

                        // Recharger les données après un import réussi
                        if (resp.success) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        }
                    } catch (error) {
                        alert('Erreur lors du traitement de la réponse du serveur');
                        console.error('Erreur parsing JSON:', error);
                    }
                }
            };

            xhr.onerror = function() {
                uploadInProgress = false;
                overlay.style.display = 'none';
                alert('Erreur lors de l\'envoi du fichier');
            };

            xhr.open('POST', 'request/import_quantitatif_ajax.php', true);
            xhr.send(formData);
        }

        // Affichage des résultats d'import
        function showImportResults(response) {
            const logModal = new bootstrap.Modal(document.getElementById('logModal'));
            const logContent = document.getElementById('logContent');

            // Mise à jour des statistiques du modal
            document.getElementById('importSuccess').textContent = response.stats?.success || '0';
            document.getElementById('importWarnings').textContent = response.stats?.warnings || '0';
            document.getElementById('importErrors').textContent = response.stats?.errors || '0';

            // Affichage du log
            logContent.textContent = response.log_txt || 'Aucun log disponible';
            logContent.scrollTop = logContent.scrollHeight;

            // Configuration des liens de téléchargement
            const downloadLog = document.getElementById('downloadLog');
            const viewLog = document.getElementById('viewLog');
            const residuBtn = document.getElementById('downloadResidu');

            if (response.log_url) {
                downloadLog.href = response.log_url;
                viewLog.href = response.log_url;
            } else {
                const blob = new Blob([response.log_txt], {
                    type: "text/plain"
                });
                const url = URL.createObjectURL(blob);
                downloadLog.href = url;
                viewLog.href = url;
            }

            if (response.residu_url) {
                residuBtn.href = response.residu_url;
                residuBtn.style.display = '';
            } else {
                residuBtn.style.display = 'none';
            }

            logModal.show();
        }

        // Fonction pour voir les détails d'un élément
        async function viewDetails(id) {
            try {
                const response = await fetch(`api/quantitatif_details.php?id=${id}`);

                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    const detailContent = document.getElementById('detailContent');

                    // Gestion des colonnes additionnelles (déjà parsées côté serveur)
                    let autresColonnesHtml = 'Aucune donnée additionnelle';
                    if (data.item.autres_colonnes) {
                        try {
                            // Les autres_colonnes sont déjà un objet depuis l'API
                            const autresColonnes = data.item.autres_colonnes;

                            if (autresColonnes && typeof autresColonnes === 'object') {
                                autresColonnesHtml = Object.entries(autresColonnes)
                                    .filter(([key, value]) => value !== null && value !== '' && key !== 'famille' && key !== 'repere' && key !== 'unite' && key !== 'quantite')
                                    .map(([key, value]) => `
                                        <div class="mb-2 p-2 bg-white rounded border-start border-3 border-primary">
                                            <strong class="text-primary">${key}:</strong> 
                                            <span class="ms-2">${value || 'Non renseigné'}</span>
                                        </div>
                                    `).join('');

                                if (autresColonnesHtml === '') {
                                    autresColonnesHtml = 'Aucune donnée additionnelle disponible';
                                }
                            }
                        } catch (parseError) {
                            console.error('Erreur traitement autres_colonnes:', parseError);
                            autresColonnesHtml = `<div class="alert alert-warning">Erreur lors du traitement des données additionnelles</div>`;
                        }
                    }

                    // Affichage des erreurs de parsing JSON si présentes côté serveur
                    if (data.item.autres_colonnes_error) {
                        autresColonnesHtml = `<div class="alert alert-warning">${data.item.autres_colonnes_error}</div>`;
                    }

                    detailContent.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary mb-3">
                                    <span class="material-icons me-2" style="vertical-align: middle;">info</span>
                                    Informations Principales
                                </h6>
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <td class="fw-bold text-muted" style="width: 30%;">ID:</td>
                                                <td><span class="badge bg-secondary">${data.item.id}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-muted">Famille:</td>
                                                <td><span class="badge bg-primary">${data.item.famille || 'Non définie'}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-muted">Repère:</td>
                                                <td><strong class="text-dark">${data.item.repere || 'Non défini'}</strong></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-muted">Unité:</td>
                                                <td>${data.item.unite || 'Non définie'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-muted">Quantité:</td>
                                                <td><span class="badge bg-success">${data.item.quantite || 'Non définie'}</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary mb-3">
                                    <span class="material-icons me-2" style="vertical-align: middle;">extension</span>
                                    Données de Suivi
                                </h6>
                                <div class="card border-0 bg-light">
                                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                        ${autresColonnesHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                    detailModal.show();
                } else {
                    throw new Error(data.error || 'Erreur inconnue');
                }
            } catch (error) {
                console.error('Erreur lors du chargement des détails:', error);
                alert(`❌ Erreur lors du chargement des détails: ${error.message}`);
            }
        } // Normalisation des repères
        async function normalizeReperes() {
            const rows = document.querySelectorAll('.table-quantitatif tbody tr');
            const updates = [];

            rows.forEach(row => {
                const tds = row.querySelectorAll('td');
                if (tds.length < 2) return;

                const famille = tds[0].textContent.trim();
                const repere = tds[1].textContent.trim();
                const repereNormalise = repere.replace(/\s+/g, '');

                if (repere !== repereNormalise && repereNormalise !== '') {
                    tds[1].innerHTML = `<strong>${repereNormalise}</strong>`;
                    updates.push({
                        famille: famille,
                        repere: repere,
                        repereNormalise: repereNormalise
                    });
                }
            });

            if (updates.length > 0) {
                try {
                    const response = await fetch('request/normalize_reperes.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            updates: updates
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert(`✅ ${updates.length} repères normalisés avec succès !`);
                    } else {
                        alert('❌ Erreur lors de la normalisation: ' + result.message);
                    }
                } catch (error) {
                    alert('❌ Erreur réseau lors de la normalisation');
                    console.error('Erreur:', error);
                }
            } else {
                alert('ℹ️ Aucun repère à normaliser trouvé');
            }
        }

        // Filtrage de la table avec défilement infini
        function filterTable() {
            const famille = document.getElementById('filterFamille').value.trim();
            const repere = document.getElementById('filterRepere').value.trim();
            const unite = document.getElementById('filterUnite').value.trim();

            // Mise à jour des filtres actuels
            currentFilters = {};
            if (famille) currentFilters.famille = famille;
            if (repere) currentFilters.repere = repere;
            if (unite) currentFilters.unite = unite;

            // Reset et rechargement avec les nouveaux filtres
            allData = [];
            hasMoreData = true;
            currentPage = 1;
            document.getElementById('endOfDataMessage').style.display = 'none';

            loadQuantitatifData(1, true);
        }

        // Debounce pour les filtres
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Export CSV avec filtres appliqués
        async function exportCSV() {
            try {
                // Récupérer toutes les données filtrées
                const params = new URLSearchParams({
                    page: 1,
                    limit: 10000, // Grande limite pour récupérer toutes les données
                    ...currentFilters
                });

                const response = await fetch(`api/quantitatif_pagination.php?${params}`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Erreur lors de l\'export');
                }

                // Créer le CSV
                const headers = ['Famille', 'Repère', 'Unité', 'Quantité'];
                const csvContent = [
                    headers.map(h => `"${h}"`).join(';'),
                    ...data.data.map(row => [
                        `"${(row.famille || 'Non défini').replace(/"/g, '""')}"`,
                        `"${(row.repere || '').replace(/"/g, '""')}"`,
                        `"${(row.unite || '').replace(/"/g, '""')}"`,
                        `"${(row.quantite || '').replace(/"/g, '""')}"`
                    ].join(';'))
                ].join('\r\n');

                // Télécharger le fichier
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `quantitatif_export_${new Date().toISOString().split('T')[0]}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

            } catch (error) {
                console.error('Erreur lors de l\'export CSV:', error);
                alert('Erreur lors de l\'export CSV: ' + error.message);
            }
        }

        // Export Excel avec filtres appliqués
        function exportExcel() {
            const params = new URLSearchParams(currentFilters);
            const url = `request/export_quantitatif_excel.php?${params}`;
            window.open(url, '_blank');
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration du défilement infini
            setupInfiniteScroll();

            // Chargement initial des données
            loadStats();
            loadImportHistory();
            loadQuantitatifData(1, true);

            // Configuration du drag & drop
            setupDragAndDrop();

            // Event listeners pour les boutons et inputs
            document.getElementById('quantitatif').addEventListener('change', handleFileUpload);
            document.getElementById('normalizeRepere').addEventListener('click', normalizeReperes);
            document.getElementById('exportQuantitatif').addEventListener('click', exportCSV);
            document.getElementById('exportQuantitatifExcel').addEventListener('click', exportExcel);

            // Event listeners pour les filtres avec debounce
            const debouncedFilter = debounce(filterTable, 500);
            ['filterFamille', 'filterRepere', 'filterUnite'].forEach(id => {
                document.getElementById(id).addEventListener('input', debouncedFilter);
            });

            // Actualisation périodique des statistiques
            setInterval(loadStats, 30000); // Toutes les 30 secondes
        });

        // Fonction globale pour les détails (accessible depuis le HTML)
        window.viewDetails = viewDetails;
    </script>
</body>

</html>