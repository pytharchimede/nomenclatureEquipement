<?php
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Validation des Doublons d'Importation - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .doublon-card {
            transition: all 0.3s ease;
            border-left: 4px solid #ffc107;
        }

        .doublon-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .doublon-card.valide {
            border-left-color: #28a745;
        }

        .doublon-card.rejete {
            border-left-color: #dc3545;
        }

        .conflict-details {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 12px;
            margin: 10px 0;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .field-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
        }

        .field-comparison .new-value {
            background: #e8f5e8;
            padding: 8px;
            border-radius: 4px;
            border-left: 3px solid #28a745;
        }

        .field-comparison .existing-value {
            background: #fff3cd;
            padding: 8px;
            border-radius: 4px;
            border-left: 3px solid #ffc107;
        }

        .stats-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .btn-group .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            font-weight: 500;
        }

        .btn-group .btn-danger {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
            border: none;
            font-weight: 500;
        }

        .btn-group .btn-success:hover,
        .btn-group .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
                    <h2 class="mb-0" style="font-weight:700;color:#1976d2;">
                        <span class="material-icons me-2" style="vertical-align: middle;">rule</span>
                        Validation des Doublons d'Importation
                    </h2>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-success" onclick="validerTousVisible()">
                            <span class="material-icons">check_circle</span>Valider Tous Visibles
                        </button>
                        <button class="btn btn-success" onclick="validerTous()">
                            <span class="material-icons">check_circle_outline</span>Valider TOUS
                        </button>
                    </div>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-danger" onclick="rejeterTousVisible()">
                            <span class="material-icons">cancel</span>Rejeter Tous Visibles
                        </button>
                        <button class="btn btn-danger" onclick="rejeterTous()">
                            <span class="material-icons">highlight_off</span>Rejeter TOUS
                        </button>
                    </div>
                    <a href="logout.php" class="btn btn-outline-primary">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="stats-container">
                <div class="row text-center" id="statsContainer">
                    <div class="col-md-3">
                        <h3 id="totalDoublons" class="mb-1">-</h3>
                        <small>Total Doublons</small>
                    </div>
                    <div class="col-md-3">
                        <h3 id="enAttente" class="mb-1">-</h3>
                        <small>En Attente</small>
                    </div>
                    <div class="col-md-3">
                        <h3 id="valides" class="mb-1">-</h3>
                        <small>Validés</small>
                    </div>
                    <div class="col-md-3">
                        <h3 id="rejetes" class="mb-1">-</h3>
                        <small>Rejetés</small>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select id="statutFilter" class="form-select">
                                <option value="">Tous</option>
                                <option value="en_attente" selected>En Attente</option>
                                <option value="valide">Validés</option>
                                <option value="rejete">Rejetés</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type de Doublon</label>
                            <select id="typeDoublonFilter" class="form-select">
                                <option value="">Tous</option>
                                <option value="doublon_exact">Doublon Exact</option>
                                <option value="doublon_repere_article">Repère + Article</option>
                                <option value="doublon_suspect">Suspect</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fichier d'Import</label>
                            <select id="fichierFilter" class="form-select">
                                <option value="">Tous</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Recherche</label>
                            <input type="text" id="rechercheFilter" class="form-control" placeholder="Repère, code article...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des doublons -->
            <div id="doublonsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-3">Chargement des doublons...</p>
                </div>
            </div>

            <!-- Pagination -->
            <nav aria-label="Navigation des doublons" class="mt-4">
                <ul class="pagination justify-content-center" id="paginationContainer">
                    <!-- Pagination générée dynamiquement -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Modal de validation -->
    <div class="modal fade" id="validationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Validation du Doublon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select id="actionValidation" class="form-select">
                            <option value="valide">Valider et Importer</option>
                            <option value="rejete">Rejeter Définitivement</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea id="commentaireValidation" class="form-control" rows="3" placeholder="Raison de votre décision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="confirmerValidation()">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de groupe de doublons -->
    <div class="modal fade" id="groupeDoublonsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <span class="material-icons me-2">group</span>
                        Groupe de Doublons
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="groupeDoublonsContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <p class="mt-3">Chargement du groupe de doublons...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <button class="btn btn-success" onclick="validerTousGroupe()">
                            <span class="material-icons">check_circle</span>Valider Tous du Groupe
                        </button>
                        <button class="btn btn-danger" onclick="rejeterTousGroupe()">
                            <span class="material-icons">cancel</span>Rejeter Tous du Groupe
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentDoublonId = null;
        let currentPage = 1;
        const itemsPerPage = 10;

        // Chargement initial
        document.addEventListener('DOMContentLoaded', function() {
            chargerStatistiques();
            chargerFichiers();
            chargerDoublons();

            // Event listeners pour les filtres
            document.getElementById('statutFilter').addEventListener('change', chargerDoublons);
            document.getElementById('typeDoublonFilter').addEventListener('change', chargerDoublons);
            document.getElementById('fichierFilter').addEventListener('change', chargerDoublons);
            document.getElementById('rechercheFilter').addEventListener('input', debounce(chargerDoublons, 500));
        });

        // Debounce function
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

        // Chargement des statistiques
        async function chargerStatistiques() {
            try {
                const response = await fetch('request/doublons_import_stats.php');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalDoublons').textContent = data.stats.total;
                    document.getElementById('enAttente').textContent = data.stats.en_attente;
                    document.getElementById('valides').textContent = data.stats.valide;
                    document.getElementById('rejetes').textContent = data.stats.rejete;
                }
            } catch (error) {
                console.error('Erreur chargement statistiques:', error);
            }
        }

        // Chargement des fichiers pour le filtre
        async function chargerFichiers() {
            try {
                const response = await fetch('request/doublons_import_fichiers.php');
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('fichierFilter');
                    data.fichiers.forEach(fichier => {
                        const option = document.createElement('option');
                        option.value = fichier;
                        option.textContent = fichier;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Erreur chargement fichiers:', error);
            }
        }

        // Chargement des doublons avec filtres
        async function chargerDoublons(page = 1) {
            currentPage = page;

            // Afficher le spinner
            const container = document.getElementById('doublonsContainer');
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-3">Chargement des doublons...</p>
                </div>
            `;

            const filtres = {
                statut: document.getElementById('statutFilter').value,
                type_doublon: document.getElementById('typeDoublonFilter').value,
                fichier: document.getElementById('fichierFilter').value,
                recherche: document.getElementById('rechercheFilter').value,
                page: page,
                limit: itemsPerPage
            };

            const params = new URLSearchParams(filtres).toString();

            try {
                const response = await fetch(`request/doublons_import_list.php?${params}`);

                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    afficherDoublons(data.doublons);
                    afficherPagination(data.pagination);
                } else {
                    container.innerHTML = `
                        <div class="alert alert-danger text-center">
                            <span class="material-icons">error</span>
                            <h4>Erreur de chargement</h4>
                            <p>${data.message || 'Erreur inconnue'}</p>
                            <button class="btn btn-primary" onclick="chargerDoublons(${page})">
                                <span class="material-icons">refresh</span> Réessayer
                            </button>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erreur chargement doublons:', error);
                container.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <span class="material-icons">error</span>
                        <h4>Erreur de connexion</h4>
                        <p>Impossible de charger les doublons: ${error.message}</p>
                        <button class="btn btn-primary" onclick="chargerDoublons(${page})">
                            <span class="material-icons">refresh</span> Réessayer
                        </button>
                    </div>
                `;
            }
        }

        // Fonction pour voir les détails d'un doublon
        function voirDetails(idDoublon) {
            // Trouver le doublon dans les données chargées
            const doublon = derniersDoublons.find(d => d.id == idDoublon);
            if (!doublon) {
                alert('Doublon non trouvé');
                return;
            }

            const conflitData = JSON.parse(doublon.details_conflit || '{}');
            const differences = conflitData.differences || {};

            let detailsHtml = `
                <div class="modal fade" id="detailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Détails du doublon #${doublon.id}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary">Données importées</h6>
                                        <table class="table table-sm">
                                            <tr><td><strong>Repère:</strong></td><td>${doublon.repere_equipement}</td></tr>
                                            <tr><td><strong>Code article:</strong></td><td>${doublon.code_article}</td></tr>
                                            <tr><td><strong>Équipement:</strong></td><td>${doublon.designation_equipement}</td></tr>
                                            <tr><td><strong>Article:</strong></td><td>${doublon.designation_article}</td></tr>
                                            <tr><td><strong>Fabricant:</strong></td><td>${doublon.fabricant}</td></tr>
                                            <tr><td><strong>Type:</strong></td><td>${doublon.type}</td></tr>
                                            <tr><td><strong>Quantité:</strong></td><td>${doublon.quantite} ${doublon.unite}</td></tr>
                                            <tr><td><strong>Poste:</strong></td><td>${doublon.numero_poste}</td></tr>
                                            <tr><td><strong>Métier:</strong></td><td>${doublon.metier}</td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-warning">Analyse du conflit</h6>
                                        <p><strong>Type:</strong> <span class="badge bg-warning">${doublon.raison_rejet}</span></p>
                                        <p><strong>Score de similarité:</strong> ${conflitData.score_similitude || 'N/A'}%</p>
                                        <p><strong>ID Nomenclature en conflit:</strong> ${conflitData.id_nomenclature || 'N/A'}</p>
                                        
                                        ${Object.keys(differences).length > 0 ? `
                                            <h6 class="mt-3">Différences détectées:</h6>
                                            <div class="differences-list">
                                                ${Object.entries(differences).map(([champ, diff]) => `
                                                    <div class="mb-2 p-2 border rounded">
                                                        <strong>${champ}:</strong><br>
                                                        <span class="text-danger">Existant: ${diff.base}</span><br>
                                                        <span class="text-success">Nouveau: ${diff.import}</span>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : '<p><em>Aucune différence (doublon exact)</em></p>'}
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <h6>Informations d'import</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Fichier:</strong> ${doublon.fichier_import}</li>
                                        <li><strong>Ligne:</strong> ${doublon.ligne_import}</li>
                                        <li><strong>Date import:</strong> ${doublon.date_import}</li>
                                        <li><strong>Statut:</strong> <span class="badge bg-${getStatutColor(doublon.statut)}">${doublon.statut}</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="modal-footer">
                                ${doublon.statut === 'en_attente' ? `
                                    <button type="button" class="btn btn-success" onclick="ouvrirValidation(${doublon.id}, 'valide'); $('#detailsModal').modal('hide');">
                                        <span class="material-icons">check</span> Valider
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="ouvrirValidation(${doublon.id}, 'rejete'); $('#detailsModal').modal('hide');">
                                        <span class="material-icons">close</span> Rejeter
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Supprimer l'ancien modal s'il existe
            const existingModal = document.getElementById('detailsModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Ajouter le nouveau modal
            document.body.insertAdjacentHTML('beforeend', detailsHtml);

            // Afficher le modal
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        }

        // Variable pour stocker les derniers doublons chargés
        let derniersDoublons = [];

        // Affichage des doublons
        function afficherDoublons(doublons) {
            derniersDoublons = doublons; // Stocker pour la fonction voirDetails
            const container = document.getElementById('doublonsContainer');

            if (doublons.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <span class="material-icons" style="font-size: 4rem; color: #28a745;">check_circle</span>
                        <h4 class="mt-3">Aucun doublon trouvé</h4>
                        <p class="text-muted">Tous les doublons ont été traités ou aucun doublon ne correspond aux filtres.</p>
                    </div>
                `;
                return;
            }

            let html = '';

            doublons.forEach(doublon => {
                const conflitData = JSON.parse(doublon.details_conflit || '{}');
                const statutClass = doublon.statut === 'valide' ? 'valide' : doublon.statut === 'rejete' ? 'rejete' : '';

                // Extraire les informations de conflit
                const idNomenclatureConflit = conflitData.id_nomenclature || 'N/A';
                const differences = conflitData.differences || {};
                const scoreSimilitude = conflitData.score_similitude || 0;

                html += `
                    <div class="card doublon-card ${statutClass} mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${doublon.repere_equipement} | ${doublon.code_article}</strong>
                                <span class="badge bg-warning ms-2">${doublon.raison_rejet}</span>
                                <span class="badge bg-${getStatutColor(doublon.statut)} ms-1">${doublon.statut}</span>
                                <small class="text-muted ms-2">Similarité: ${scoreSimilitude}%</small>
                            </div>
                            <div class="action-buttons">
                                ${doublon.statut === 'en_attente' ? `
                                    <button class="btn btn-success btn-sm" onclick="ouvrirValidation(${doublon.id}, 'valide')">
                                        <span class="material-icons">check</span>Valider
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="ouvrirValidation(${doublon.id}, 'rejete')">
                                        <span class="material-icons">close</span>Rejeter
                                    </button>
                                ` : ''}
                                <button class="btn btn-info btn-sm" onclick="voirDetails(${doublon.id})">
                                    <span class="material-icons">visibility</span>Détails
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="voirGroupeDoublons('${doublon.repere_equipement}', '${doublon.code_article}')">
                                    <span class="material-icons">group</span>Groupe
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Nouvelle Entrée (Ligne ${doublon.ligne_import})</h6>
                                    <div class="new-value">
                                        <strong>Équipement:</strong> ${doublon.designation_equipement || 'N/A'}<br>
                                        <strong>Article:</strong> ${doublon.designation_article || 'N/A'}<br>
                                        <strong>Fabricant:</strong> ${doublon.fabricant || 'N/A'}<br>
                                        <strong>Quantité:</strong> ${doublon.quantite || 'N/A'} ${doublon.unite || ''}<br>
                                        <strong>Type:</strong> ${doublon.type || 'N/A'}<br>
                                        <strong>Source:</strong> ${doublon.source || 'N/A'}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-warning">Conflit avec Nomenclature ID: ${idNomenclatureConflit}</h6>
                                    <div class="existing-value mb-2">
                                        ${Object.keys(differences).length > 0 ? `
                                            <strong>Différences détectées:</strong><br>
                                            ${Object.entries(differences).map(([champ, diff]) => `
                                                <div class="mb-1">
                                                    <strong>${champ}:</strong><br>
                                                    <span class="text-danger">Base: ${diff.base}</span><br>
                                                    <span class="text-success">Import: ${diff.import}</span>
                                                </div>
                                            `).join('')}
                                        ` : '<em>Doublon exact détecté</em>'}
                                    </div>
                                    <small class="text-muted">Fichier: ${doublon.fichier_import}</small><br>
                                    <small class="text-muted">Importé le: ${doublon.date_import}</small>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>Fichier:</strong> ${doublon.fichier_import} | 
                                    <strong>Import:</strong> ${doublon.date_import}
                                    ${doublon.date_validation ? ` | <strong>Validé par:</strong> ${doublon.valide_par} le ${doublon.date_validation}` : ''}
                                </small>
                                ${doublon.commentaire_validation ? `
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <strong>Commentaire:</strong> ${doublon.commentaire_validation}
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Affichage de la pagination
        function afficherPagination(pagination) {
            const container = document.getElementById('paginationContainer');
            let html = '';

            // Bouton précédent
            html += `
                <li class="page-item ${pagination.current_page <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="chargerDoublons(${pagination.current_page - 1})">Précédent</a>
                </li>
            `;

            // Pages
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === pagination.current_page ||
                    Math.abs(i - pagination.current_page) <= 2 ||
                    i === 1 ||
                    i === pagination.total_pages) {
                    html += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="chargerDoublons(${i})">${i}</a>
                        </li>
                    `;
                } else if (Math.abs(i - pagination.current_page) === 3) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            // Bouton suivant
            html += `
                <li class="page-item ${pagination.current_page >= pagination.total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="chargerDoublons(${pagination.current_page + 1})">Suivant</a>
                </li>
            `;

            container.innerHTML = html;
        }

        // Ouverture du modal de validation
        function ouvrirValidation(doublonId, action) {
            currentDoublonId = doublonId;
            document.getElementById('actionValidation').value = action;
            document.getElementById('commentaireValidation').value = '';

            const modal = new bootstrap.Modal(document.getElementById('validationModal'));
            modal.show();
        }

        // Confirmation de la validation
        async function confirmerValidation() {
            const action = document.getElementById('actionValidation').value;
            const commentaire = document.getElementById('commentaireValidation').value;

            try {
                const response = await fetch('request/doublons_import_validation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: currentDoublonId,
                        action: action,
                        commentaire: commentaire
                    })
                });

                const data = await response.json();

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('validationModal')).hide();
                    chargerStatistiques();
                    chargerDoublons(currentPage);

                    // Notification de succès
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la validation', 'error');
            }
        }

        // Fonctions utilitaires
        function getStatutColor(statut) {
            switch (statut) {
                case 'valide':
                    return 'success';
                case 'rejete':
                    return 'danger';
                case 'importe':
                    return 'info';
                default:
                    return 'warning';
            }
        }

        function showNotification(message, type) {
            // Implémentation simple d'une notification
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

        // Actions en lot
        async function validerTousVisible() {
            if (!confirm('Voulez-vous vraiment valider tous les doublons visibles ?')) return;

            // Filtrer les doublons en attente parmi ceux affichés
            const doublonsEnAttente = derniersDoublons.filter(d => d.statut === 'en_attente');

            if (doublonsEnAttente.length === 0) {
                alert('Aucun doublon en attente à valider sur cette page.');
                return;
            }

            const bouton = event.target.closest('button');
            const texteBouton = bouton.innerHTML;
            bouton.disabled = true;
            bouton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Validation...';

            let succes = 0;
            let erreurs = 0;

            try {
                for (const doublon of doublonsEnAttente) {
                    try {
                        const response = await fetch('request/doublons_import_validation.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                id: doublon.id,
                                action: 'valide',
                                commentaire: 'Validation en lot'
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            succes++;
                        } else {
                            erreurs++;
                            console.error(`Erreur validation doublon ${doublon.id}:`, result.message);
                        }
                    } catch (error) {
                        erreurs++;
                        console.error(`Erreur validation doublon ${doublon.id}:`, error);
                    }
                }

                // Afficher le résultat
                const message = `Validation terminée: ${succes} réussies, ${erreurs} erreurs`;
                showNotification(message, erreurs === 0 ? 'success' : 'warning');

                // Recharger les données
                await chargerStatistiques();
                chargerDoublons(currentPage);

            } catch (error) {
                console.error('Erreur lors de la validation en lot:', error);
                showNotification('Erreur lors de la validation en lot', 'error');
            } finally {
                bouton.disabled = false;
                bouton.innerHTML = texteBouton;
            }
        }

        async function rejeterTousVisible() {
            if (!confirm('Voulez-vous vraiment rejeter tous les doublons visibles ?')) return;

            // Filtrer les doublons en attente parmi ceux affichés
            const doublonsEnAttente = derniersDoublons.filter(d => d.statut === 'en_attente');

            if (doublonsEnAttente.length === 0) {
                alert('Aucun doublon en attente à rejeter sur cette page.');
                return;
            }

            const bouton = event.target.closest('button');
            const texteBouton = bouton.innerHTML;
            bouton.disabled = true;
            bouton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Rejet...';

            let succes = 0;
            let erreurs = 0;

            try {
                for (const doublon of doublonsEnAttente) {
                    try {
                        const response = await fetch('request/doublons_import_validation.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                id: doublon.id,
                                action: 'rejete',
                                commentaire: 'Rejet en lot'
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            succes++;
                        } else {
                            erreurs++;
                            console.error(`Erreur rejet doublon ${doublon.id}:`, result.message);
                        }
                    } catch (error) {
                        erreurs++;
                        console.error(`Erreur rejet doublon ${doublon.id}:`, error);
                    }
                }

                // Afficher le résultat
                const message = `Rejet terminé: ${succes} réussies, ${erreurs} erreurs`;
                showNotification(message, erreurs === 0 ? 'success' : 'warning');

                // Recharger les données
                await chargerStatistiques();
                chargerDoublons(currentPage);

            } catch (error) {
                console.error('Erreur lors du rejet en lot:', error);
                showNotification('Erreur lors du rejet en lot', 'error');
            } finally {
                bouton.disabled = false;
                bouton.innerHTML = texteBouton;
            }
        }

        // Valider TOUS les doublons (même non visibles)
        async function validerTous() {
            console.log('=== DÉBUT validerTous ===');

            if (!confirm('⚠️ ATTENTION : Cette action va valider TOUS les doublons en attente dans la base de données.\n\nÊtes-vous sûr de vouloir continuer ?')) {
                console.log('Utilisateur a annulé');
                return;
            }

            console.log('Utilisateur a confirmé, début du traitement...');

            const bouton = event.target.closest('button');
            const texteBouton = bouton.innerHTML;
            bouton.disabled = true;
            bouton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Validation de tous...';

            try {
                console.log('Envoi de la requête vers request/doublons_import_validation_bulk.php');

                const response = await fetch('request/doublons_import_validation_bulk.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'valider_tous',
                        commentaire: 'Validation en lot de tous les doublons'
                    })
                });

                console.log('Réponse reçue, status:', response.status);
                console.log('Response OK:', response.ok);

                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }

                const result = await response.json();
                console.log('Données JSON décodées:', result);

                if (result.success) {
                    showNotification(`Tous les doublons ont été validés avec succès ! (${result.count} doublons traités)`, 'success');

                    // Recharger les données
                    console.log('Rechargement des statistiques...');
                    await chargerStatistiques();
                    console.log('Rechargement des doublons...');
                    chargerDoublons(currentPage);
                } else {
                    console.error('Erreur du serveur:', result.message);
                    showNotification(result.message || 'Erreur lors de la validation globale', 'error');
                }

            } catch (error) {
                console.error('Erreur lors de la validation globale:', error);
                showNotification(`Erreur lors de la validation globale: ${error.message}`, 'error');
            } finally {
                bouton.disabled = false;
                bouton.innerHTML = texteBouton;
                console.log('=== FIN validerTous ===');
            }
        }

        // Rejeter TOUS les doublons (même non visibles)
        async function rejeterTous() {
            if (!confirm('⚠️ ATTENTION : Cette action va rejeter TOUS les doublons en attente dans la base de données.\n\nÊtes-vous sûr de vouloir continuer ?')) return;

            const bouton = event.target.closest('button');
            const texteBouton = bouton.innerHTML;
            bouton.disabled = true;
            bouton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Rejet de tous...';

            try {
                const response = await fetch('request/doublons_import_validation_bulk.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'rejeter_tous',
                        commentaire: 'Rejet en lot de tous les doublons'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(`Tous les doublons ont été rejetés avec succès ! (${result.count} doublons traités)`, 'success');

                    // Recharger les données
                    await chargerStatistiques();
                    chargerDoublons(currentPage);
                } else {
                    showNotification(result.message || 'Erreur lors du rejet global', 'error');
                }

            } catch (error) {
                console.error('Erreur lors du rejet global:', error);
                showNotification('Erreur lors du rejet global', 'error');
            } finally {
                bouton.disabled = false;
                bouton.innerHTML = texteBouton;
            }
        }

        // Variables pour le groupe de doublons
        let currentGroupeDoublons = [];

        // Voir le groupe de doublons pour un repère/article
        async function voirGroupeDoublons(repere, codeArticle) {
            console.log('Affichage du groupe de doublons pour:', repere, codeArticle);

            // Afficher le modal
            const modal = new bootstrap.Modal(document.getElementById('groupeDoublonsModal'));
            modal.show();

            // Mettre à jour le titre
            document.querySelector('#groupeDoublonsModal .modal-title').innerHTML = `
                <span class="material-icons me-2">group</span>
                Groupe de Doublons: ${repere} | ${codeArticle}
            `;

            try {
                const response = await fetch(`request/doublons_import_groupe.php?repere=${encodeURIComponent(repere)}&code_article=${encodeURIComponent(codeArticle)}`);
                const data = await response.json();

                if (data.success) {
                    currentGroupeDoublons = data.doublons;
                    afficherGroupeDoublons(data.doublons, data.nomenclature_existante);
                } else {
                    document.getElementById('groupeDoublonsContent').innerHTML = `
                        <div class="alert alert-danger">
                            <span class="material-icons">error</span>
                            Erreur: ${data.message}
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erreur lors du chargement du groupe:', error);
                document.getElementById('groupeDoublonsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <span class="material-icons">error</span>
                        Erreur de connexion: ${error.message}
                    </div>
                `;
            }
        }

        // Afficher les doublons d'un groupe
        function afficherGroupeDoublons(doublons, nomenclatureExistante) {
            let html = '';

            // Afficher la nomenclature existante si elle existe
            if (nomenclatureExistante) {
                html += `
                    <div class="card border-success mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <span class="material-icons me-2">check_circle</span>
                                Nomenclature Existante (Base de données)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Repère:</strong> ${nomenclatureExistante.repere_equipement}<br>
                                    <strong>Code Article:</strong> ${nomenclatureExistante.code_article}<br>
                                    <strong>Équipement:</strong> ${nomenclatureExistante.designation_equipement}<br>
                                    <strong>Article:</strong> ${nomenclatureExistante.designation_article}
                                </div>
                                <div class="col-md-6">
                                    <strong>Fabricant:</strong> ${nomenclatureExistante.fabricant || 'N/A'}<br>
                                    <strong>Type:</strong> ${nomenclatureExistante.type || 'N/A'}<br>
                                    <strong>Quantité:</strong> ${nomenclatureExistante.quantite} ${nomenclatureExistante.unite}<br>
                                    <strong>Source:</strong> ${nomenclatureExistante.source || 'N/A'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Afficher les doublons
            html += `<h6 class="text-warning mb-3">
                <span class="material-icons me-2">warning</span>
                Doublons Détectés (${doublons.length})
            </h6>`;

            doublons.forEach((doublon, index) => {
                const conflitData = JSON.parse(doublon.details_conflit || '{}');
                const differences = conflitData.differences || {};
                const scoreSimilitude = conflitData.score_similitude || 0;

                html += `
                    <div class="card mb-3 ${doublon.statut === 'valide' ? 'border-success' : doublon.statut === 'rejete' ? 'border-danger' : 'border-warning'}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Import #${index + 1}</strong>
                                <span class="badge bg-${getStatutColor(doublon.statut)} ms-2">${doublon.statut}</span>
                                <span class="badge bg-info ms-1">Similarité: ${scoreSimilitude}%</span>
                                <small class="text-muted ms-2">${doublon.fichier_import} (ligne ${doublon.ligne_import})</small>
                            </div>
                            <div>
                                ${doublon.statut === 'en_attente' ? `
                                    <button class="btn btn-success btn-sm" onclick="validerDoublonGroupe(${doublon.id})">
                                        <span class="material-icons">check</span>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="rejeterDoublonGroupe(${doublon.id})">
                                        <span class="material-icons">close</span>
                                    </button>
                                ` : ''}
                                <button class="btn btn-info btn-sm" onclick="voirDetails(${doublon.id}); bootstrap.Modal.getInstance(document.getElementById('groupeDoublonsModal')).hide();">
                                    <span class="material-icons">visibility</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Données d'Import</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Équipement:</strong></td><td>${doublon.designation_equipement || 'N/A'}</td></tr>
                                        <tr><td><strong>Article:</strong></td><td>${doublon.designation_article || 'N/A'}</td></tr>
                                        <tr><td><strong>Fabricant:</strong></td><td>${doublon.fabricant || 'N/A'}</td></tr>
                                        <tr><td><strong>Type:</strong></td><td>${doublon.type || 'N/A'}</td></tr>
                                        <tr><td><strong>Quantité:</strong></td><td>${doublon.quantite || 'N/A'} ${doublon.unite || ''}</td></tr>
                                        <tr><td><strong>Source:</strong></td><td>${doublon.source || 'N/A'}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-warning">Analyse du Conflit</h6>
                                    <p><strong>Type:</strong> <span class="badge bg-warning text-dark">${doublon.raison_rejet}</span></p>
                                    <p><strong>Date Import:</strong> ${doublon.date_import}</p>
                                    
                                    ${Object.keys(differences).length > 0 ? `
                                        <div class="mt-3">
                                            <h6>Différences Détectées:</h6>
                                            ${Object.entries(differences).map(([champ, diff]) => `
                                                <div class="mb-2 p-2 border rounded">
                                                    <strong>${champ}:</strong><br>
                                                    <span class="text-danger">Base: ${diff.base}</span><br>
                                                    <span class="text-success">Import: ${diff.import}</span>
                                                </div>
                                            `).join('')}
                                        </div>
                                    ` : '<p class="text-muted"><em>Doublon exact</em></p>'}
                                </div>
                            </div>
                            
                            ${doublon.commentaire_validation ? `
                                <div class="mt-3 p-2 bg-light rounded">
                                    <strong>Commentaire:</strong> ${doublon.commentaire_validation}
                                    ${doublon.valide_par ? `<br><small class="text-muted">par ${doublon.valide_par} le ${doublon.date_validation}</small>` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });

            document.getElementById('groupeDoublonsContent').innerHTML = html;
        }

        // Valider un doublon dans le groupe
        async function validerDoublonGroupe(doublonId) {
            try {
                const response = await fetch('request/doublons_import_validation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: doublonId,
                        action: 'valide',
                        commentaire: 'Validation depuis le groupe'
                    })
                });

                const result = await response.json();
                if (result.success) {
                    showNotification('Doublon validé avec succès', 'success');

                    // Recharger le groupe et les données principales
                    const currentModal = document.querySelector('#groupeDoublonsModal .modal-title');
                    const repereMatch = currentModal.textContent.match(/Groupe de Doublons: ([^|]+) \| (.+)/);
                    if (repereMatch) {
                        voirGroupeDoublons(repereMatch[1].trim(), repereMatch[2].trim());
                    }

                    await chargerStatistiques();
                    chargerDoublons(currentPage);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur validation doublon:', error);
                showNotification('Erreur lors de la validation', 'error');
            }
        }

        // Rejeter un doublon dans le groupe
        async function rejeterDoublonGroupe(doublonId) {
            try {
                const response = await fetch('request/doublons_import_validation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: doublonId,
                        action: 'rejete',
                        commentaire: 'Rejet depuis le groupe'
                    })
                });

                const result = await response.json();
                if (result.success) {
                    showNotification('Doublon rejeté avec succès', 'success');

                    // Recharger le groupe et les données principales
                    const currentModal = document.querySelector('#groupeDoublonsModal .modal-title');
                    const repereMatch = currentModal.textContent.match(/Groupe de Doublons: ([^|]+) \| (.+)/);
                    if (repereMatch) {
                        voirGroupeDoublons(repereMatch[1].trim(), repereMatch[2].trim());
                    }

                    await chargerStatistiques();
                    chargerDoublons(currentPage);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur rejet doublon:', error);
                showNotification('Erreur lors du rejet', 'error');
            }
        }

        // Valider tous les doublons du groupe
        async function validerTousGroupe() {
            const doublonsEnAttente = currentGroupeDoublons.filter(d => d.statut === 'en_attente');

            if (doublonsEnAttente.length === 0) {
                alert('Aucun doublon en attente dans ce groupe.');
                return;
            }

            if (!confirm(`Voulez-vous vraiment valider tous les ${doublonsEnAttente.length} doublons en attente de ce groupe ?`)) return;

            let succes = 0;
            let erreurs = 0;

            for (const doublon of doublonsEnAttente) {
                try {
                    const response = await fetch('request/doublons_import_validation.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: doublon.id,
                            action: 'valide',
                            commentaire: 'Validation en lot du groupe'
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        succes++;
                    } else {
                        erreurs++;
                    }
                } catch (error) {
                    erreurs++;
                }
            }

            showNotification(`Validation terminée: ${succes} réussies, ${erreurs} erreurs`, erreurs === 0 ? 'success' : 'warning');

            // Recharger le groupe et les données principales
            const currentModal = document.querySelector('#groupeDoublonsModal .modal-title');
            const repereMatch = currentModal.textContent.match(/Groupe de Doublons: ([^|]+) \| (.+)/);
            if (repereMatch) {
                voirGroupeDoublons(repereMatch[1].trim(), repereMatch[2].trim());
            }

            await chargerStatistiques();
            chargerDoublons(currentPage);
        }

        // Rejeter tous les doublons du groupe
        async function rejeterTousGroupe() {
            const doublonsEnAttente = currentGroupeDoublons.filter(d => d.statut === 'en_attente');

            if (doublonsEnAttente.length === 0) {
                alert('Aucun doublon en attente dans ce groupe.');
                return;
            }

            if (!confirm(`Voulez-vous vraiment rejeter tous les ${doublonsEnAttente.length} doublons en attente de ce groupe ?`)) return;

            let succes = 0;
            let erreurs = 0;

            for (const doublon of doublonsEnAttente) {
                try {
                    const response = await fetch('request/doublons_import_validation.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: doublon.id,
                            action: 'rejete',
                            commentaire: 'Rejet en lot du groupe'
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        succes++;
                    } else {
                        erreurs++;
                    }
                } catch (error) {
                    erreurs++;
                }
            }

            showNotification(`Rejet terminé: ${succes} réussies, ${erreurs} erreurs`, erreurs === 0 ? 'success' : 'warning');

            // Recharger le groupe et les données principales
            const currentModal = document.querySelector('#groupeDoublonsModal .modal-title');
            const repereMatch = currentModal.textContent.match(/Groupe de Doublons: ([^|]+) \| (.+)/);
            if (repereMatch) {
                voirGroupeDoublons(repereMatch[1].trim(), repereMatch[2].trim());
            }

            await chargerStatistiques();
            chargerDoublons(currentPage);
        }
    </script>
</body>

</html>