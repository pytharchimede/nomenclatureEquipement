<?php
if (!isset($_SESSION)) session_start();
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Centre d'Exportation - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .export-card {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .export-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .export-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
        }

        .export-card.equipements {
            --card-color: #1976d2;
            --card-color-light: #42a5f5;
        }

        .export-card.articles {
            --card-color: #388e3c;
            --card-color-light: #66bb6a;
        }

        .export-card.nomenclatures {
            --card-color: #0288d1;
            --card-color-light: #29b6f6;
        }

        .export-card.familles {
            --card-color: #f57c00;
            --card-color-light: #ffb74d;
        }

        .export-card.doublons {
            --card-color: #d32f2f;
            --card-color-light: #ef5350;
        }

        .export-card.quantitatif {
            --card-color: #7b1fa2;
            --card-color-light: #ab47bc;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 15px;
            background: linear-gradient(145deg, var(--card-color), var(--card-color-light));
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            color: white;
        }

        .filter-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .btn-export {
            background: linear-gradient(45deg, var(--card-color), var(--card-color-light));
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            color: white;
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
            border-radius: 15px;
            text-align: center;
            min-width: 300px;
        }

        .stats-widget {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .advanced-filters {
            display: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .export-history {
            max-height: 300px;
            overflow-y: auto;
        }

        .history-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            border-left: 4px solid var(--card-color);
        }
    </style>
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
                    <h2 class="mb-0" style="font-weight:700;background: linear-gradient(45deg, #1976d2, #42a5f5);-webkit-background-clip: text;-webkit-text-fill-color: transparent;">
                        <span class="material-icons me-2" style="vertical-align: middle; color: #1976d2;">cloud_download</span>
                        Centre d'Exportation Avancé
                    </h2>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="toggleAdvancedFilters()">
                        <span class="material-icons">tune</span> Filtres Avancés
                    </button>
                    <a href="logout.php" class="btn btn-outline-primary">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4" id="statsContainer">
                <div class="col-md-2">
                    <div class="stats-widget">
                        <div class="stats-number" id="totalEquipements">-</div>
                        <small>Équipements</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-widget">
                        <div class="stats-number" id="totalArticles">-</div>
                        <small>Articles</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-widget">
                        <div class="stats-number" id="totalNomenclatures">-</div>
                        <small>Nomenclatures</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-widget">
                        <div class="stats-number" id="totalFamilles">-</div>
                        <small>Familles</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-widget">
                        <div class="stats-number" id="totalDoublons">-</div>
                        <small>Doublons</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-widget">
                        <div class="stats-number" id="totalExports">-</div>
                        <small>Exports Aujourd'hui</small>
                    </div>
                </div>
            </div>

            <!-- Filtres Avancés -->
            <div class="advanced-filters" id="advancedFilters">
                <div class="filter-section">
                    <h5 class="mb-4">
                        <span class="material-icons me-2">filter_list</span>
                        Filtres Personnalisés d'Exportation
                    </h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="filter-card">
                                <h6><span class="material-icons me-2">date_range</span>Période</h6>
                                <div class="mb-3">
                                    <label class="form-label">Date de début</label>
                                    <input type="date" class="form-control" id="dateDebut">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date de fin</label>
                                    <input type="date" class="form-control" id="dateFin">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="filter-card">
                                <h6><span class="material-icons me-2">source</span>Source de Données</h6>
                                <div class="mb-3">
                                    <label class="form-label">Source</label>
                                    <select class="form-select" id="sourceFilter">
                                        <option value="">Toutes les sources</option>
                                        <option value="SAP">SAP</option>
                                        <option value="IMPORT">Import Manuel</option>
                                        <option value="API">API Externe</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Statut</label>
                                    <select class="form-select" id="statutFilter">
                                        <option value="">Tous les statuts</option>
                                        <option value="actif">Actif</option>
                                        <option value="inactif">Inactif</option>
                                        <option value="archive">Archivé</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="filter-card">
                                <h6><span class="material-icons me-2">category</span>Catégories</h6>
                                <div class="mb-3">
                                    <label class="form-label">Famille d'équipement</label>
                                    <select class="form-select" id="familleFilter">
                                        <option value="">Toutes les familles</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Fabricant</label>
                                    <select class="form-select" id="fabricantFilter">
                                        <option value="">Tous les fabricants</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-light" onclick="resetFilters()">
                            <span class="material-icons">clear</span> Réinitialiser
                        </button>
                        <button class="btn btn-success" onclick="applyFilters()">
                            <span class="material-icons">check</span> Appliquer les Filtres
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cartes d'Exportation -->
            <div class="row g-4">
                <!-- Équipements -->
                <div class="col-lg-4 col-md-6">
                    <div class="card export-card equipements h-100">
                        <div class="card-body">
                            <div class="card-icon">
                                <span class="material-icons">precision_manufacturing</span>
                            </div>
                            <h5 class="card-title fw-bold">Équipements</h5>
                            <p class="card-text text-muted">Exportation complète des équipements avec filtres avancés</p>

                            <button class="btn btn-export" onclick="exportData('equipements', 'complet')">
                                <span class="material-icons me-2">file_download</span>Export Complet
                            </button>
                            <button class="btn btn-export" onclick="exportData('equipements', 'sans_piece')">
                                <span class="material-icons me-2">warning</span>Sans Pièces de Rechange
                            </button>
                            <button class="btn btn-export" onclick="exportData('equipements', 'par_famille')">
                                <span class="material-icons me-2">category</span>Groupé par Famille
                            </button>
                            <button class="btn btn-export" onclick="showCustomExport('equipements')">
                                <span class="material-icons me-2">tune</span>Export Personnalisé
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Articles -->
                <div class="col-lg-4 col-md-6">
                    <div class="card export-card articles h-100">
                        <div class="card-body">
                            <div class="card-icon">
                                <span class="material-icons">inventory_2</span>
                            </div>
                            <h5 class="card-title fw-bold">Articles</h5>
                            <p class="card-text text-muted">Gestion complète des articles et pièces de rechange</p>

                            <button class="btn btn-export" onclick="exportData('articles', 'complet')">
                                <span class="material-icons me-2">file_download</span>Export Complet
                            </button>
                            <button class="btn btn-export" onclick="exportData('articles', 'non_lies')">
                                <span class="material-icons me-2">link_off</span>Articles Non Liés
                            </button>
                            <button class="btn btn-export" onclick="exportData('articles', 'par_fabricant')">
                                <span class="material-icons me-2">business</span>Par Fabricant
                            </button>
                            <button class="btn btn-export" onclick="showCustomExport('articles')">
                                <span class="material-icons me-2">tune</span>Export Personnalisé
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Nomenclatures -->
                <div class="col-lg-4 col-md-6">
                    <div class="card export-card nomenclatures h-100">
                        <div class="card-body">
                            <div class="card-icon">
                                <span class="material-icons">list_alt</span>
                            </div>
                            <h5 class="card-title fw-bold">Nomenclatures</h5>
                            <p class="card-text text-muted">Relations équipements-articles avec analyses</p>

                            <button class="btn btn-export" onclick="exportData('nomenclatures', 'complet')">
                                <span class="material-icons me-2">file_download</span>Export Complet
                            </button>
                            <button class="btn btn-export" onclick="exportData('nomenclatures', 'par_equipement')">
                                <span class="material-icons me-2">build</span>Par Équipement
                            </button>
                            <button class="btn btn-export" onclick="exportData('nomenclatures', 'hierarchique')">
                                <span class="material-icons me-2">account_tree</span>Vue Hiérarchique
                            </button>
                            <button class="btn btn-export" onclick="showCustomExport('nomenclatures')">
                                <span class="material-icons me-2">tune</span>Export Personnalisé
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Doublons -->
                <div class="col-lg-4 col-md-6">
                    <div class="card export-card doublons h-100">
                        <div class="card-body">
                            <div class="card-icon">
                                <span class="material-icons">content_copy</span>
                            </div>
                            <h5 class="card-title fw-bold">Doublons & Conflits</h5>
                            <p class="card-text text-muted">Analyse et gestion des doublons détectés</p>

                            <button class="btn btn-export" onclick="exportData('doublons', 'tous')">
                                <span class="material-icons me-2">file_download</span>Tous les Doublons
                            </button>
                            <button class="btn btn-export" onclick="exportData('doublons', 'en_attente')">
                                <span class="material-icons me-2">pending</span>En Attente
                            </button>
                            <button class="btn btn-export" onclick="exportData('doublons', 'resolus')">
                                <span class="material-icons me-2">check_circle</span>Résolus
                            </button>
                            <button class="btn btn-export" onclick="showCustomExport('doublons')">
                                <span class="material-icons me-2">tune</span>Export Personnalisé
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Familles -->
                <div class="col-lg-4 col-md-6">
                    <div class="card export-card familles h-100">
                        <div class="card-body">
                            <div class="card-icon">
                                <span class="material-icons">category</span>
                            </div>
                            <h5 class="card-title fw-bold">Familles & Groupes</h5>
                            <p class="card-text text-muted">Classification et regroupements</p>

                            <button class="btn btn-export" onclick="exportData('familles', 'complet')">
                                <span class="material-icons me-2">file_download</span>Toutes les Familles
                            </button>
                            <button class="btn btn-export" onclick="exportData('familles', 'avec_stats')">
                                <span class="material-icons me-2">bar_chart</span>Avec Statistiques
                            </button>
                            <button class="btn btn-export" onclick="exportData('familles', 'arbre')">
                                <span class="material-icons me-2">account_tree</span>Structure Arbre
                            </button>
                            <button class="btn btn-export" onclick="showCustomExport('familles')">
                                <span class="material-icons me-2">tune</span>Export Personnalisé
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quantitatif -->
                <div class="col-lg-4 col-md-6">
                    <div class="card export-card quantitatif h-100">
                        <div class="card-body">
                            <div class="card-icon">
                                <span class="material-icons">analytics</span>
                            </div>
                            <h5 class="card-title fw-bold">Analyses Quantitatives</h5>
                            <p class="card-text text-muted">Rapports et analyses de données</p>

                            <button class="btn btn-export" onclick="exportData('quantitatif', 'global')">
                                <span class="material-icons me-2">file_download</span>Rapport Global
                            </button>
                            <button class="btn btn-export" onclick="exportData('quantitatif', 'par_periode')">
                                <span class="material-icons me-2">date_range</span>Par Période
                            </button>
                            <button class="btn btn-export" onclick="exportData('quantitatif', 'dashboard')">
                                <span class="material-icons me-2">dashboard</span>Dashboard Excel
                            </button>
                            <button class="btn btn-export" onclick="showCustomExport('quantitatif')">
                                <span class="material-icons me-2">tune</span>Export Personnalisé
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des Exports -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card export-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <span class="material-icons me-2">history</span>
                                Historique des Exportations
                            </h5>
                            <div class="export-history" id="exportHistory">
                                <div class="text-center py-3">
                                    <span class="material-icons" style="font-size: 3rem; color: #ccc;">file_download</span>
                                    <p class="text-muted">Aucun export récent</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Overlay -->
    <div class="progress-overlay" id="progressOverlay">
        <div class="progress-content">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <h5 id="progressTitle">Génération de l'export en cours...</h5>
            <p id="progressText">Veuillez patienter</p>
            <div class="progress mb-3">
                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
            </div>
            <small id="progressDetails">Initialisation...</small>
        </div>
    </div>

    <!-- Modal Export Personnalisé -->
    <div class="modal fade" id="customExportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="material-icons me-2">tune</span>
                        Export Personnalisé
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Colonnes à Exporter</h6>
                            <div id="columnsSelection">
                                <!-- Généré dynamiquement -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Options d'Export</h6>
                            <div class="mb-3">
                                <label class="form-label">Format</label>
                                <select class="form-select" id="exportFormat">
                                    <option value="excel">Excel (.xlsx)</option>
                                    <option value="csv">CSV</option>
                                    <option value="pdf">PDF</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Limite de lignes</label>
                                <select class="form-select" id="exportLimit">
                                    <option value="">Aucune limite</option>
                                    <option value="1000">1 000 lignes</option>
                                    <option value="5000">5 000 lignes</option>
                                    <option value="10000">10 000 lignes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="executeCustomExport()">
                        <span class="material-icons me-2">file_download</span>Exporter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton d'Action Flottant -->
    <div class="floating-action">
        <button class="btn btn-floating" onclick="quickExportAll()" title="Export Global Rapide">
            <span class="material-icons">download</span>
        </button>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let currentFilters = {};
        let exportHistory = [];
        let currentExportType = '';

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadFiltersData();
            loadExportHistory();
            setDefaultDates();
        });

        // Chargement des statistiques
        async function loadStatistics() {
            try {
                const response = await fetch('api/export_stats.php');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalEquipements').textContent = data.stats.equipements || '0';
                    document.getElementById('totalArticles').textContent = data.stats.articles || '0';
                    document.getElementById('totalNomenclatures').textContent = data.stats.nomenclatures || '0';
                    document.getElementById('totalFamilles').textContent = data.stats.familles || '0';
                    document.getElementById('totalDoublons').textContent = data.stats.doublons || '0';
                    document.getElementById('totalExports').textContent = data.stats.exports_today || '0';
                }
            } catch (error) {
                console.error('Erreur chargement statistiques:', error);
            }
        }

        // Chargement des données pour les filtres
        async function loadFiltersData() {
            try {
                const response = await fetch('api/filters_data.php');
                const data = await response.json();

                if (data.success) {
                    // Charger les familles
                    const familleSelect = document.getElementById('familleFilter');
                    data.familles.forEach(famille => {
                        const option = document.createElement('option');
                        option.value = famille.id;
                        option.textContent = famille.nom;
                        familleSelect.appendChild(option);
                    });

                    // Charger les fabricants
                    const fabricantSelect = document.getElementById('fabricantFilter');
                    data.fabricants.forEach(fabricant => {
                        const option = document.createElement('option');
                        option.value = fabricant;
                        option.textContent = fabricant;
                        fabricantSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Erreur chargement filtres:', error);
            }
        }

        // Chargement de l'historique
        async function loadExportHistory() {
            try {
                const response = await fetch('api/export_history.php');
                const data = await response.json();

                if (data.success && data.exports.length > 0) {
                    const container = document.getElementById('exportHistory');
                    container.innerHTML = '';

                    data.exports.forEach(exp => {
                        const item = document.createElement('div');
                        item.className = 'history-item';
                        item.style.setProperty('--card-color', getCardColor(exp.type));
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${exp.type}</strong> - ${exp.subtype}
                                    <br><small>${exp.date} par ${exp.user}</small>
                                </div>
                                <div>
                                    <span class="badge bg-success">${exp.rows} lignes</span>
                                    <a href="${exp.file_path}" class="btn btn-sm btn-outline-primary ms-2">
                                        <span class="material-icons">download</span>
                                    </a>
                                </div>
                            </div>
                        `;
                        container.appendChild(item);
                    });
                }
            } catch (error) {
                console.error('Erreur chargement historique:', error);
            }
        }

        // Définir les dates par défaut
        function setDefaultDates() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

            document.getElementById('dateDebut').value = firstDay.toISOString().split('T')[0];
            document.getElementById('dateFin').value = today.toISOString().split('T')[0];
        }

        // Toggle filtres avancés
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
        }

        // Appliquer les filtres
        function applyFilters() {
            currentFilters = {
                dateDebut: document.getElementById('dateDebut').value,
                dateFin: document.getElementById('dateFin').value,
                source: document.getElementById('sourceFilter').value,
                statut: document.getElementById('statutFilter').value,
                famille: document.getElementById('familleFilter').value,
                fabricant: document.getElementById('fabricantFilter').value
            };

            showNotification('Filtres appliqués avec succès!', 'success');
        }

        // Réinitialiser les filtres
        function resetFilters() {
            document.getElementById('dateDebut').value = '';
            document.getElementById('dateFin').value = '';
            document.getElementById('sourceFilter').value = '';
            document.getElementById('statutFilter').value = '';
            document.getElementById('familleFilter').value = '';
            document.getElementById('fabricantFilter').value = '';
            currentFilters = {};

            showNotification('Filtres réinitialisés', 'info');
        }

        // Export de données principal
        async function exportData(type, subtype) {
            showProgress(`Export ${type} - ${subtype}`, 'Préparation de l\'export...');

            try {
                const params = new URLSearchParams({
                    type: type,
                    subtype: subtype,
                    ...currentFilters
                });

                updateProgress(25, 'Génération des données...');

                const response = await fetch(`api/export_data.php?${params}`);

                updateProgress(75, 'Finalisation du fichier...');

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${type}_${subtype}_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    updateProgress(100, 'Export terminé!');

                    setTimeout(() => {
                        hideProgress();
                        showNotification('Export terminé avec succès!', 'success');
                        addToHistory(type, subtype);
                        loadExportHistory();
                    }, 1000);
                } else {
                    throw new Error('Erreur lors de l\'export');
                }
            } catch (error) {
                hideProgress();
                showNotification('Erreur lors de l\'export: ' + error.message, 'error');
            }
        }

        // Afficher modal export personnalisé
        function showCustomExport(type) {
            currentExportType = type;
            loadColumnOptions(type);
            const modal = new bootstrap.Modal(document.getElementById('customExportModal'));
            modal.show();
        }

        // Charger les options de colonnes
        async function loadColumnOptions(type) {
            try {
                const response = await fetch(`api/columns_${type}.php`);
                const data = await response.json();

                if (data.success) {
                    const container = document.getElementById('columnsSelection');
                    container.innerHTML = '';

                    data.columns.forEach(col => {
                        const div = document.createElement('div');
                        div.className = 'form-check mb-2';
                        div.innerHTML = `
                            <input class="form-check-input" type="checkbox" value="${col.key}" id="col_${col.key}" ${col.default ? 'checked' : ''}>
                            <label class="form-check-label" for="col_${col.key}">
                                ${col.label}
                            </label>
                        `;
                        container.appendChild(div);
                    });
                }
            } catch (error) {
                console.error('Erreur chargement colonnes:', error);
            }
        }

        // Exécuter export personnalisé
        async function executeCustomExport() {
            const selectedColumns = Array.from(document.querySelectorAll('#columnsSelection input:checked'))
                .map(input => input.value);

            if (selectedColumns.length === 0) {
                showNotification('Veuillez sélectionner au moins une colonne', 'warning');
                return;
            }

            const format = document.getElementById('exportFormat').value;
            const limit = document.getElementById('exportLimit').value;

            const modal = bootstrap.Modal.getInstance(document.getElementById('customExportModal'));
            modal.hide();

            showProgress(`Export personnalisé ${currentExportType}`, 'Préparation...');

            try {
                const params = new URLSearchParams({
                    type: currentExportType,
                    subtype: 'custom',
                    columns: selectedColumns.join(','),
                    format: format,
                    limit: limit,
                    ...currentFilters
                });

                const response = await fetch(`api/export_custom.php?${params}`);

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${currentExportType}_custom_${new Date().toISOString().split('T')[0]}.${format === 'excel' ? 'xlsx' : format}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    hideProgress();
                    showNotification('Export personnalisé terminé!', 'success');
                } else {
                    throw new Error('Erreur lors de l\'export');
                }
            } catch (error) {
                hideProgress();
                showNotification('Erreur: ' + error.message, 'error');
            }
        }

        // Export global rapide
        async function quickExportAll() {
            if (!confirm('Voulez-vous exporter TOUTES les données de la nomenclature?\nCette opération peut prendre plusieurs minutes.')) {
                return;
            }

            showProgress('Export Global', 'Génération de l\'export complet...');

            try {
                const response = await fetch('api/export_all.php');

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `nomenclature_complete_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    hideProgress();
                    showNotification('Export global terminé!', 'success');
                } else {
                    throw new Error('Erreur lors de l\'export global');
                }
            } catch (error) {
                hideProgress();
                showNotification('Erreur: ' + error.message, 'error');
            }
        }

        // Fonctions utilitaires
        function showProgress(title, text) {
            document.getElementById('progressTitle').textContent = title;
            document.getElementById('progressText').textContent = text;
            document.getElementById('progressBar').style.width = '0%';
            document.getElementById('progressDetails').textContent = 'Initialisation...';
            document.getElementById('progressOverlay').style.display = 'flex';
        }

        function updateProgress(percent, details) {
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressDetails').textContent = details;
        }

        function hideProgress() {
            document.getElementById('progressOverlay').style.display = 'none';
        }

        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : type === 'warning' ? 'alert-warning' : 'alert-danger';
            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

        function addToHistory(type, subtype) {
            exportHistory.unshift({
                type: type,
                subtype: subtype,
                date: new Date().toLocaleString('fr-FR'),
                user: 'Utilisateur Actuel'
            });
        }

        function getCardColor(type) {
            const colors = {
                'equipements': '#1976d2',
                'articles': '#388e3c',
                'nomenclatures': '#0288d1',
                'familles': '#f57c00',
                'doublons': '#d32f2f',
                'quantitatif': '#7b1fa2'
            };
            return colors[type] || '#666';
        }
    </script>
</body>

</html>