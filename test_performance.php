<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Performance - 23 000 Équipements</title>
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .performance-card {
            border-left: 4px solid #007bff;
        }

        .excellent {
            border-left-color: #28a745;
        }

        .good {
            border-left-color: #ffc107;
        }

        .warning {
            border-left-color: #fd7e14;
        }

        .critical {
            border-left-color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">🧪 Test de Performance - Gestion des Équipements</h1>

                <div class="alert alert-info">
                    <h5>📊 Tests pour validation avec 23 000+ équipements</h5>
                    <p>Cette page permet de valider que le système fonctionne correctement avec un gros volume de données.</p>
                </div>

                <!-- Tests de performance -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card performance-card mb-3">
                            <div class="card-header">
                                <h5>🔍 Test de Cohérence du Système</h5>
                            </div>
                            <div class="card-body">
                                <button id="testConsistency" class="btn btn-primary mb-3">Lancer le test</button>
                                <div id="consistencyResults"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card performance-card mb-3">
                            <div class="card-header">
                                <h5>⚡ Optimisation Base de Données</h5>
                            </div>
                            <div class="card-body">
                                <button id="optimizeDatabase" class="btn btn-warning mb-3">Optimiser</button>
                                <div id="optimizationResults"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tests de pagination -->
                <div class="card performance-card mb-3">
                    <div class="card-header">
                        <h5>📄 Test de Pagination</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <button id="testPagination" class="btn btn-success mb-3">Test Pagination</button>
                                <div id="paginationResults"></div>
                            </div>
                            <div class="col-md-4">
                                <button id="testSearch" class="btn btn-info mb-3">Test Recherche</button>
                                <div id="searchResults"></div>
                            </div>
                            <div class="col-md-4">
                                <button id="testFilters" class="btn btn-secondary mb-3">Test Filtres</button>
                                <div id="filtersResults"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test d'import -->
                <div class="card performance-card mb-3">
                    <div class="card-header">
                        <h5>📤 Test d'Import Optimisé</h5>
                    </div>
                    <div class="card-body">
                        <p>Test avec le nouveau script d'import optimisé pour les gros volumes :</p>
                        <div class="mb-3">
                            <input type="file" id="testImportFile" class="form-control" accept=".xlsx,.xls" />
                        </div>
                        <button id="testImport" class="btn btn-primary" disabled>Tester l'Import Optimisé</button>
                        <div id="importResults" class="mt-3"></div>
                    </div>
                </div>

                <!-- Statistiques temps réel -->
                <div class="card performance-card mb-3">
                    <div class="card-header">
                        <h5>📊 Statistiques en Temps Réel</h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="statsContainer">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 id="totalEquipements" class="text-primary">-</h3>
                                    <p>Équipements</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 id="avgResponseTime" class="text-success">-</h3>
                                    <p>Temps réponse (ms)</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 id="consistencyScore" class="text-info">-</h3>
                                    <p>Score cohérence</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 id="systemStatus" class="text-warning">-</h3>
                                    <p>Statut système</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Journal des opérations -->
                <div class="card performance-card">
                    <div class="card-header">
                        <h5>📝 Journal des Tests</h5>
                    </div>
                    <div class="card-body">
                        <div id="testLog" style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.9em;">
                            <div class="text-muted">Prêt pour les tests...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales pour les tests
        let testResults = {};
        let testStartTime = null;

        // Fonctions utilitaires
        function logMessage(message, type = 'info') {
            const log = document.getElementById('testLog');
            const timestamp = new Date().toLocaleTimeString();
            const colorClass = {
                'info': 'text-info',
                'success': 'text-success',
                'warning': 'text-warning',
                'error': 'text-danger'
            } [type] || 'text-muted';

            log.innerHTML += `<div class="${colorClass}">[${timestamp}] ${message}</div>`;
            log.scrollTop = log.scrollHeight;
        }

        function updateStats() {
            fetch('request/system_consistency_check.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        logMessage('Erreur lors de la récupération des stats: ' + data.message, 'error');
                        return;
                    }

                    document.getElementById('totalEquipements').textContent = data.performance_stats.total_equipements.toLocaleString();
                    document.getElementById('avgResponseTime').textContent = data.performance_stats.pagination_time_ms;
                    document.getElementById('consistencyScore').textContent = data.consistency_score + '%';
                    document.getElementById('systemStatus').textContent = data.status.toUpperCase();

                    // Coloration du statut
                    const statusEl = document.getElementById('systemStatus');
                    statusEl.className = {
                        'excellent': 'text-success',
                        'good': 'text-info',
                        'warning': 'text-warning',
                        'critical': 'text-danger'
                    } [data.status] || 'text-muted';
                })
                .catch(error => {
                    logMessage('Erreur lors de la récupération des stats: ' + error.message, 'error');
                });
        }

        // Test de cohérence
        document.getElementById('testConsistency').addEventListener('click', function() {
            logMessage('Démarrage du test de cohérence...', 'info');
            const btn = this;
            btn.disabled = true;

            fetch('request/system_consistency_check.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('consistencyResults');

                    if (data.error) {
                        container.innerHTML = `<div class="alert alert-danger">Erreur: ${data.message}</div>`;
                        logMessage('Test de cohérence échoué: ' + data.message, 'error');
                        return;
                    }

                    let html = `<div class="alert alert-${data.status === 'excellent' ? 'success' : (data.status === 'good' ? 'info' : 'warning')}">`;
                    html += `<strong>Score: ${data.consistency_score}%</strong> (${data.status})<br>`;
                    html += '<ul class="mb-0 mt-2">';
                    data.recommendations.forEach(rec => {
                        html += `<li>${rec}</li>`;
                    });
                    html += '</ul></div>';

                    container.innerHTML = html;
                    logMessage(`Test de cohérence terminé - Score: ${data.consistency_score}%`, 'success');
                })
                .catch(error => {
                    logMessage('Erreur test de cohérence: ' + error.message, 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });

        // Test d'optimisation
        document.getElementById('optimizeDatabase').addEventListener('click', function() {
            logMessage('Démarrage de l\'optimisation de la base de données...', 'info');
            const btn = this;
            btn.disabled = true;

            fetch('request/database_optimization.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('optimizationResults');

                    if (!data.success) {
                        container.innerHTML = `<div class="alert alert-danger">Erreur: ${data.message}</div>`;
                        logMessage('Optimisation échouée: ' + data.message, 'error');
                        return;
                    }

                    let html = '<div class="alert alert-success"><strong>Optimisation terminée</strong><ul class="mb-0 mt-2">';
                    data.optimizations.forEach(opt => {
                        html += `<li>${opt}</li>`;
                    });
                    html += '</ul></div>';

                    if (data.errors.length > 0) {
                        html += '<div class="alert alert-warning mt-2"><strong>Erreurs:</strong><ul class="mb-0">';
                        data.errors.forEach(err => {
                            html += `<li>${err}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    container.innerHTML = html;
                    logMessage('Optimisation terminée avec succès', 'success');
                    updateStats(); // Mettre à jour les stats
                })
                .catch(error => {
                    logMessage('Erreur optimisation: ' + error.message, 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });

        // Test de pagination
        document.getElementById('testPagination').addEventListener('click', function() {
            logMessage('Test de pagination en cours...', 'info');
            const startTime = performance.now();

            fetch('request/equipements_paginated.php?page=1&limit=50')
                .then(response => response.json())
                .then(data => {
                    const endTime = performance.now();
                    const responseTime = Math.round(endTime - startTime);

                    const container = document.getElementById('paginationResults');
                    if (data.success) {
                        container.innerHTML = `
                            <div class="alert alert-success">
                                <strong>✅ Pagination OK</strong><br>
                                Temps: ${responseTime}ms<br>
                                Résultats: ${data.data.length}/${data.pagination.total}
                            </div>
                        `;
                        logMessage(`Pagination testée: ${responseTime}ms pour ${data.data.length} éléments`, 'success');
                    } else {
                        container.innerHTML = `<div class="alert alert-danger">Erreur: ${data.message}</div>`;
                        logMessage('Test pagination échoué: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    logMessage('Erreur test pagination: ' + error.message, 'error');
                });
        });

        // Test de recherche
        document.getElementById('testSearch').addEventListener('click', function() {
            logMessage('Test de recherche en cours...', 'info');
            const startTime = performance.now();

            fetch('request/equipements_paginated.php?page=1&limit=50&search=test')
                .then(response => response.json())
                .then(data => {
                    const endTime = performance.now();
                    const responseTime = Math.round(endTime - startTime);

                    const container = document.getElementById('searchResults');
                    if (data.success) {
                        container.innerHTML = `
                            <div class="alert alert-success">
                                <strong>🔍 Recherche OK</strong><br>
                                Temps: ${responseTime}ms<br>
                                Résultats: ${data.data.length}
                            </div>
                        `;
                        logMessage(`Recherche testée: ${responseTime}ms`, 'success');
                    } else {
                        container.innerHTML = `<div class="alert alert-danger">Erreur: ${data.message}</div>`;
                        logMessage('Test recherche échoué: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    logMessage('Erreur test recherche: ' + error.message, 'error');
                });
        });

        // Test des filtres
        document.getElementById('testFilters').addEventListener('click', function() {
            logMessage('Test des filtres en cours...', 'info');
            const startTime = performance.now();

            fetch('request/equipements_paginated.php?page=1&limit=50&fabricant=test&type_objet=test')
                .then(response => response.json())
                .then(data => {
                    const endTime = performance.now();
                    const responseTime = Math.round(endTime - startTime);

                    const container = document.getElementById('filtersResults');
                    if (data.success) {
                        container.innerHTML = `
                            <div class="alert alert-success">
                                <strong>🎛️ Filtres OK</strong><br>
                                Temps: ${responseTime}ms<br>
                                Résultats: ${data.data.length}
                            </div>
                        `;
                        logMessage(`Filtres testés: ${responseTime}ms`, 'success');
                    } else {
                        container.innerHTML = `<div class="alert alert-danger">Erreur: ${data.message}</div>`;
                        logMessage('Test filtres échoué: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    logMessage('Erreur test filtres: ' + error.message, 'error');
                });
        });

        // Gestion de l'import test
        document.getElementById('testImportFile').addEventListener('change', function() {
            document.getElementById('testImport').disabled = !this.files.length;
        });

        document.getElementById('testImport').addEventListener('click', function() {
            const fileInput = document.getElementById('testImportFile');
            const file = fileInput.files[0];

            if (!file) return;

            logMessage('Démarrage test d\'import optimisé...', 'info');
            const btn = this;
            btn.disabled = true;

            const formData = new FormData();
            formData.append('excel_file', file);

            const startTime = performance.now();

            fetch('request/equipement_import_optimized.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const endTime = performance.now();
                    const totalTime = Math.round(endTime - startTime);

                    const container = document.getElementById('importResults');

                    if (data.success) {
                        container.innerHTML = `
                        <div class="alert alert-success">
                            <strong>✅ Import Réussi</strong><br>
                            Temps total: ${totalTime}ms<br>
                            Importés: ${data.imported}<br>
                            Doublons: ${data.duplicates}<br>
                            Erreurs: ${data.errors}
                        </div>
                    `;
                        logMessage(`Import terminé: ${data.imported} ajouts en ${totalTime}ms`, 'success');
                    } else {
                        container.innerHTML = `<div class="alert alert-danger">Erreur: ${data.message}</div>`;
                        logMessage('Import échoué: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    logMessage('Erreur test import: ' + error.message, 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    updateStats();
                });
        });

        // Initialisation et mise à jour automatique des stats
        updateStats();
        setInterval(updateStats, 30000); // Mise à jour toutes les 30 secondes

        logMessage('Page de test initialisée - Prêt pour validation 23K+ équipements', 'success');
    </script>
</body>

</html>