<?php
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Doublons - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .progress-container {
            display: none;
        }

        .export-section {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            transition: all 0.3s ease;
        }

        .export-section:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }

        .btn-export {
            font-size: 1.1em;
            padding: 12px 24px;
        }

        .duplicate-group {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 10px;
            margin: 5px 0;
            border: 2px solid transparent;
        }

        .duplicate-group:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .duplicate-group.selected {
            background-color: #e3f2fd;
            border-color: #2196f3;
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
        }

        .details-card {
            max-height: 500px;
            overflow-y: auto;
        }

        .conflict-highlight {
            background: linear-gradient(45deg, #ffeb3b22, #ff572222);
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }

        .entry-card {
            border-left: 4px solid #2196f3;
            margin-bottom: 10px;
        }

        .entry-card.duplicate {
            border-left-color: #f44336;
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
                        <span class="material-icons me-2" style="vertical-align: middle;">content_copy</span>
                        Gestion des Doublons
                    </h2>
                </div>
                <a href="logout.php" class="btn btn-outline-primary ms-auto">
                    <span class="material-icons">logout</span>Déconnexion
                </a>
            </div>

            <!-- Statistiques rapides -->
            <div class="row g-3 mb-4" id="statsContainer">
                <div class="col-md-3">
                    <div class="card stat-card border-primary">
                        <div class="card-body text-center">
                            <span class="material-icons text-primary" style="font-size: 2rem;">list_alt</span>
                            <h5 class="card-title mt-2">Total Nomenclatures</h5>
                            <h4 class="text-primary" id="totalNomenclatures">-</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body text-center">
                            <span class="material-icons text-warning" style="font-size: 2rem;">content_copy</span>
                            <h5 class="card-title mt-2">Groupes Doublons</h5>
                            <h4 class="text-warning" id="groupesDoublons">-</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card border-danger">
                        <div class="card-body text-center">
                            <span class="material-icons text-danger" style="font-size: 2rem;">error_outline</span>
                            <h5 class="card-title mt-2">Entrées Dupliquées</h5>
                            <h4 class="text-danger" id="entreesDupliquees">-</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card border-success">
                        <div class="card-body text-center">
                            <span class="material-icons text-success" style="font-size: 2rem;">check_circle</span>
                            <h5 class="card-title mt-2">Entrées Uniques</h5>
                            <h4 class="text-success" id="entreesUniques">-</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Export -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <span class="material-icons me-2">file_download</span>
                        Export des Doublons
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="export-section">
                                <span class="material-icons text-success mb-3" style="font-size: 3rem;">table_view</span>
                                <h5>Export Excel Complet</h5>
                                <p class="text-muted">Export détaillé avec feuilles de synthèse et mise en forme</p>
                                <button class="btn btn-success btn-export" onclick="exportExcel()">
                                    <span class="material-icons me-2">file_download</span>
                                    Télécharger Excel
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="export-section">
                                <span class="material-icons text-primary mb-3" style="font-size: 3rem;">speed</span>
                                <h5>Export Progressif</h5>
                                <p class="text-muted">Export en temps réel avec suivi de progression (CSV)</p>
                                <button class="btn btn-primary btn-export" onclick="exportProgressif()">
                                    <span class="material-icons me-2">download</span>
                                    Export Progressif
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Barre de progression -->
                    <div class="progress-container mt-4">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <span class="material-icons me-2">hourglass_empty</span>
                                    Export en cours...
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="progress mb-3" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 0%" id="progressBar">
                                        <span id="progressText">0%</span>
                                    </div>
                                </div>
                                <div id="progressMessages" class="small text-muted"></div>
                                <div id="downloadSection" style="display: none;" class="mt-3">
                                    <div class="alert alert-success">
                                        <span class="material-icons me-2">check_circle</span>
                                        Export terminé !
                                        <a id="downloadLink" href="#" class="btn btn-success btn-sm ms-2">
                                            <span class="material-icons me-1">download</span>Télécharger
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Détails des Doublons -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <span class="material-icons me-2">analytics</span>
                        Analyse des Doublons
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><span class="material-icons me-2">list</span>Groupes de Doublons</h6>
                            <div id="analysisContainer">
                                <p class="text-center text-muted">
                                    <span class="material-icons" style="font-size: 3rem;">search</span><br>
                                    Chargement de l'analyse...
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><span class="material-icons me-2">visibility</span>Détails du Groupe Sélectionné</h6>
                            <div id="detailsContainer">
                                <div class="text-center text-muted p-4">
                                    <span class="material-icons" style="font-size: 3rem;">touch_app</span><br>
                                    Cliquez sur un groupe pour voir les détails
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chargement des statistiques
        async function loadStats() {
            try {
                const response = await fetch('request/nomenclatures_stats.php');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalNomenclatures').textContent = data.total.toLocaleString();
                }
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }

            // Charger les stats des doublons
            try {
                const response = await fetch('request/nomenclatures_duplicates.php?action=detect');
                const data = await response.json();

                if (data.success) {
                    const groupes = data.duplicates.length;
                    const entreesDupliquees = data.duplicates.reduce((sum, group) => sum + group.count, 0);
                    const total = parseInt(document.getElementById('totalNomenclatures').textContent.replace(/,/g, ''));
                    const uniques = total - entreesDupliquees;

                    document.getElementById('groupesDoublons').textContent = groupes.toLocaleString();
                    document.getElementById('entreesDupliquees').textContent = entreesDupliquees.toLocaleString();
                    document.getElementById('entreesUniques').textContent = uniques.toLocaleString();

                    // Analyse détaillée
                    generateAnalysis(data.duplicates);
                }
            } catch (error) {
                console.error('Erreur chargement doublons:', error);
                document.getElementById('groupesDoublons').textContent = '0';
                document.getElementById('entreesDupliquees').textContent = '0';
            }
        }

        // Export Excel
        function exportExcel() {
            const link = document.createElement('a');
            link.href = 'request/export_nomenclatures_doublons.php';
            link.click();
        }

        // Export progressif
        function exportProgressif() {
            const progressContainer = document.querySelector('.progress-container');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const progressMessages = document.getElementById('progressMessages');
            const downloadSection = document.getElementById('downloadSection');

            progressContainer.style.display = 'block';
            downloadSection.style.display = 'none';
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            progressMessages.innerHTML = '';

            const eventSource = new EventSource('request/export_nomenclatures_doublons_progressif.php');

            eventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);

                switch (data.type) {
                    case 'start':
                        progressMessages.innerHTML += '<div>' + data.message + '</div>';
                        break;

                    case 'progress':
                        if (data.percentage) {
                            progressBar.style.width = data.percentage + '%';
                            progressText.textContent = data.percentage + '%';
                        }
                        progressMessages.innerHTML += '<div>' + data.message + '</div>';
                        progressMessages.scrollTop = progressMessages.scrollHeight;
                        break;

                    case 'completed':
                        progressBar.style.width = '100%';
                        progressText.textContent = '100%';
                        progressMessages.innerHTML += '<div class="text-success"><strong>' + data.message + '</strong></div>';

                        if (data.download_url) {
                            const downloadLink = document.getElementById('downloadLink');
                            downloadLink.href = data.download_url;
                            downloadSection.style.display = 'block';
                        }

                        eventSource.close();
                        break;

                    case 'error':
                        progressMessages.innerHTML += '<div class="text-danger">❌ ' + data.message + '</div>';
                        eventSource.close();
                        break;
                }
            };

            eventSource.onerror = function() {
                progressMessages.innerHTML += '<div class="text-danger">❌ Erreur de connexion</div>';
                eventSource.close();
            };
        }

        // Génération de l'analyse
        function generateAnalysis(duplicates) {
            const container = document.getElementById('analysisContainer');

            if (duplicates.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-success">
                        <span class="material-icons" style="font-size: 3rem;">check_circle</span>
                        <h5 class="mt-2">Aucun doublon détecté !</h5>
                        <p class="text-muted">Votre base de données nomenclatures est propre.</p>
                    </div>
                `;
                return;
            }

            // Tri par nombre de doublons (descendant)
            duplicates.sort((a, b) => b.count - a.count);

            let html = `
                <div class="mb-3">
                    <small class="text-muted">
                        <span class="material-icons" style="font-size: 16px;">info</span>
                        Cliquez sur un groupe pour voir les détails
                    </small>
                </div>
            `;

            duplicates.forEach((dup, index) => {
                const severity = getSeverityClass(dup.count);
                html += `
                    <div class="duplicate-group ${severity}" data-group-index="${index}" onclick="selectGroup(${index})">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="conflict-highlight">
                                    ${dup.repere_equipement} | ${dup.code_article}
                                </strong>
                                <br>
                                <small class="text-muted">
                                    <span class="material-icons" style="font-size: 14px;">content_copy</span>
                                    ${dup.count} occurences
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-${getSeverityBadge(dup.count)} fs-6">
                                    ${dup.count}x
                                </span>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;

            // Stocker les données pour utilisation ultérieure
            window.duplicatesData = duplicates;
        }

        // Fonction pour déterminer la classe de sévérité
        function getSeverityClass(count) {
            if (count >= 5) return 'border-danger';
            if (count >= 3) return 'border-warning';
            return 'border-info';
        }

        // Fonction pour déterminer la couleur du badge
        function getSeverityBadge(count) {
            if (count >= 5) return 'danger';
            if (count >= 3) return 'warning';
            return 'info';
        }

        // Sélection d'un groupe de doublons
        async function selectGroup(groupIndex) {
            // Supprimer la sélection précédente
            document.querySelectorAll('.duplicate-group').forEach(el => {
                el.classList.remove('selected');
            });

            // Ajouter la sélection au groupe cliqué
            const selectedGroup = document.querySelector(`[data-group-index="${groupIndex}"]`);
            selectedGroup.classList.add('selected');

            const group = window.duplicatesData[groupIndex];

            // Charger les détails du groupe
            await loadGroupDetails(group.repere_equipement, group.code_article);
        }

        // Chargement des détails d'un groupe
        async function loadGroupDetails(repere, codeArticle) {
            const detailsContainer = document.getElementById('detailsContainer');

            // Afficher un spinner
            detailsContainer.innerHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des détails...</p>
                </div>
            `;

            try {
                const response = await fetch(`request/nomenclatures_duplicates.php?action=details&repere=${encodeURIComponent(repere)}&code_article=${encodeURIComponent(codeArticle)}`);
                const data = await response.json();

                if (data.success && data.entries) {
                    displayGroupDetails(repere, codeArticle, data.entries);
                } else {
                    detailsContainer.innerHTML = `
                        <div class="alert alert-warning">
                            <span class="material-icons me-2">warning</span>
                            Impossible de charger les détails de ce groupe
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erreur chargement détails:', error);
                detailsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <span class="material-icons me-2">error</span>
                        Erreur lors du chargement des détails
                    </div>
                `;
            }
        }

        // Affichage des détails d'un groupe
        function displayGroupDetails(repere, codeArticle, entries) {
            const detailsContainer = document.getElementById('detailsContainer');

            let html = `
                <div class="details-card">
                    <div class="mb-3 p-3 bg-light rounded">
                        <h6 class="mb-1">
                            <span class="material-icons me-2">info</span>
                            Conflit détecté
                        </h6>
                        <p class="mb-0">
                            Le <strong class="conflict-highlight">code article "${codeArticle}"</strong> 
                            apparaît plusieurs fois pour le 
                            <strong class="conflict-highlight">repère équipement "${repere}"</strong>
                        </p>
                        <small class="text-muted">
                            Règle violée : Un code article ne doit apparaître qu'une seule fois par repère équipement
                        </small>
                    </div>
                    
                    <h6>
                        <span class="material-icons me-2">list</span>
                        Entrées en conflit (${entries.length})
                    </h6>
            `;

            entries.forEach((entry, index) => {
                html += `
                    <div class="card entry-card duplicate mb-2">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="card-title mb-2">
                                        <span class="badge bg-secondary me-2">#${entry.id}</span>
                                        Entrée ${index + 1}
                                    </h6>
                                    <p class="mb-1">
                                        <strong>Repère:</strong> 
                                        <span class="conflict-highlight">${entry.repere_equipement}</span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Code Article:</strong> 
                                        <span class="conflict-highlight">${entry.code_article}</span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Désignation Équipement:</strong> 
                                        ${entry.designation_equipement || 'N/A'}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Désignation Article:</strong> 
                                        ${entry.designation_article || 'N/A'}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">
                                        <strong>Fabricant:</strong> ${entry.fabricant || 'N/A'}<br>
                                        <strong>Type:</strong> ${entry.type || 'N/A'}<br>
                                        <strong>Quantité:</strong> ${entry.quantite || 'N/A'} ${entry.unite || ''}<br>
                                        <strong>Poste:</strong> ${entry.numero_poste || 'N/A'}<br>
                                        <strong>Métier:</strong> ${entry.metier || 'N/A'}<br>
                                        <strong>Source:</strong> ${entry.source || 'N/A'}
                                    </small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-danger btn-sm" onclick="deleteEntry(${entry.id})">
                                    <span class="material-icons me-1">delete</span>
                                    Supprimer
                                </button>
                                <button class="btn btn-warning btn-sm ms-2" onclick="editEntry(${entry.id})">
                                    <span class="material-icons me-1">edit</span>
                                    Modifier
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                    <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded">
                        <small class="text-warning">
                            <span class="material-icons me-1" style="font-size: 16px;">warning</span>
                            <strong>Recommandation:</strong> 
                            Vérifiez si ces entrées sont réellement identiques ou s'il s'agit d'erreurs de saisie.
                            Supprimez les doublons ou corrigez les codes articles si nécessaire.
                        </small>
                    </div>
                </div>
            `;

            detailsContainer.innerHTML = html;
        }

        // Fonction pour supprimer une entrée
        async function deleteEntry(entryId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette entrée ?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('delete', entryId);

                const response = await fetch('request/gestion_doublons_save.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.deleted) {
                    showAlert('Entrée supprimée avec succès.', 'success');
                    // Recharger les données
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('Erreur lors de la suppression.', 'danger');
                }
            } catch (error) {
                console.error('Erreur suppression:', error);
                showAlert('Erreur lors de la suppression.', 'danger');
            }
        }

        // Fonction pour éditer une entrée (placeholder)
        function editEntry(entryId) {
            showAlert('Fonctionnalité d\'édition en cours de développement.', 'info');
        }

        // Fonction d'affichage d'alerte
        function showAlert(msg, type) {
            let alert = document.createElement('div');
            alert.className = 'alert alert-' + type + ' alert-dismissible fade show position-fixed';
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                ${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 3000);
        }

        // Chargement initial
        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
</body>

</html>