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
                    <div id="analysisContainer">
                        <p class="text-center text-muted">
                            <span class="material-icons" style="font-size: 3rem;">search</span><br>
                            Chargement de l'analyse...
                        </p>
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

            // Répartition par nombre de doublons
            const repartition = {};
            duplicates.forEach(dup => {
                const count = dup.count;
                repartition[count] = (repartition[count] || 0) + 1;
            });

            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><span class="material-icons me-2">pie_chart</span>Répartition par nombre de doublons</h6>
                        <ul class="list-group">
            `;

            Object.keys(repartition).sort((a, b) => b - a).forEach(count => {
                const groupes = repartition[count];
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${count} doublons
                        <span class="badge bg-primary rounded-pill">${groupes} groupes</span>
                    </li>
                `;
            });

            html += `
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><span class="material-icons me-2">warning</span>Top 5 des groupes les plus problématiques</h6>
                        <ul class="list-group">
            `;

            duplicates.slice(0, 5).forEach((dup, index) => {
                html += `
                    <li class="list-group-item">
                        <strong>#${index + 1}</strong> - ${dup.repere_equipement} | ${dup.code_article}
                        <br><small class="text-muted">${dup.count} occurences</small>
                    </li>
                `;
            });

            html += `
                        </ul>
                    </div>
                </div>
            `;

            container.innerHTML = html;
        }

        // Chargement initial
        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('dupsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showAlert('Modifications enregistrées avec succès.', 'success');
                        setTimeout(() => location.reload(), 1200);
                    } else if (res.deleted) {
                        showAlert('Ligne supprimée avec succès.', 'info');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        showAlert('Erreur lors du traitement.', 'danger');
                    }
                })
                .catch(() => showAlert('Erreur réseau.', 'danger'));
        });

        // Gestion suppression directe (bouton supprimer)
        document.querySelectorAll('button[name="delete"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Supprimer cette ligne ?')) return;
                const formData = new FormData();
                formData.append('delete', this.value);
                fetch('request/gestion_doublons_save.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.deleted) {
                            showAlert('Ligne supprimée avec succès.', 'info');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            showAlert('Erreur lors de la suppression.', 'danger');
                        }
                    })
                    .catch(() => showAlert('Erreur réseau.', 'danger'));
            });
        });

        // Fonction d'affichage d'alerte
        function showAlert(msg, type) {
            let alert = document.createElement('div');
            alert.className = 'alert alert-' + type + ' mt-3';
            alert.innerHTML = msg;
            document.querySelector('.container').prepend(alert);
            setTimeout(() => alert.remove(), 3000);
        }
    </script>
</body>

</html>