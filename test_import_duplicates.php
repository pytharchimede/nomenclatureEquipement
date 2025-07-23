<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Test Importation et Doublons - Nomenclatures</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .test-section {
            margin: 2rem 0;
            padding: 1.5rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .test-result {
            margin: 1rem 0;
            padding: 1rem;
            border-radius: 4px;
        }

        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4">
            <span class="material-icons me-2">science</span>
            Test Importation et Doublons - Nomenclatures
        </h1>

        <!-- Test APIs -->
        <div class="test-section">
            <h3>🔍 Test des APIs</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" onclick="testPagination()">
                        <span class="material-icons">view_list</span>Test Pagination
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-info w-100" onclick="testStats()">
                        <span class="material-icons">analytics</span>Test Statistiques
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-warning w-100" onclick="testDuplicates()">
                        <span class="material-icons">find_in_page</span>Test Doublons
                    </button>
                </div>
            </div>
            <div id="api-results" class="mt-3"></div>
        </div>

        <!-- Test Importation -->
        <div class="test-section">
            <h3>📥 Test Importation Excel</h3>
            <p>Testons l'importation d'un fichier Excel avec des données d'exemple.</p>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Fichier CSV de test</h5>
                        </div>
                        <div class="card-body">
                            <p>Un fichier CSV d'exemple a été créé : <code>test_import_nomenclatures.csv</code></p>
                            <button class="btn btn-success" onclick="generateTestExcel()">
                                <span class="material-icons">file_download</span>
                                Télécharger fichier test Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Test importation</h5>
                        </div>
                        <div class="card-body">
                            <input type="file" class="form-control mb-3" id="testFileInput" accept=".xlsx,.xls">
                            <button class="btn btn-primary" onclick="testImport()" disabled>
                                <span class="material-icons">upload</span>
                                Tester importation
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="import-results" class="mt-3"></div>
        </div>

        <!-- Test Doublons -->
        <div class="test-section">
            <h3>⚠️ Test Gestion Doublons</h3>
            <p>Créons quelques doublons artificiels pour tester la détection et la résolution.</p>

            <div class="row g-3">
                <div class="col-md-4">
                    <button class="btn btn-warning w-100" onclick="createTestDuplicates()">
                        <span class="material-icons">content_copy</span>
                        Créer doublons test
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-info w-100" onclick="detectDuplicates()">
                        <span class="material-icons">search</span>
                        Détecter doublons
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success w-100" onclick="cleanupTestData()">
                        <span class="material-icons">cleaning_services</span>
                        Nettoyer données test
                    </button>
                </div>
            </div>

            <div id="duplicates-results" class="mt-3"></div>
        </div>

        <div class="test-section">
            <h3>🔗 Liens utiles</h3>
            <div class="row g-2">
                <div class="col-md-4">
                    <a href="nomenclatures.php" class="btn btn-outline-primary w-100">
                        <span class="material-icons">dashboard</span>Page principale
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="test_nomenclatures_api.php" class="btn btn-outline-info w-100">
                        <span class="material-icons">api</span>Test APIs
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="gestion_doublons_nomenclature.php" class="btn btn-outline-warning w-100">
                        <span class="material-icons">manage_search</span>Gestion doublons
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        function showResult(containerId, message, type = 'info') {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = `test-result ${type}`;
            div.innerHTML = message;
            container.appendChild(div);
        }

        function clearResults(containerId) {
            document.getElementById(containerId).innerHTML = '';
        }

        async function testPagination() {
            clearResults('api-results');
            try {
                const response = await fetch('request/nomenclatures_paginated.php?page=1&limit=5');
                const data = await response.json();

                if (data.success) {
                    showResult('api-results', `
                        <strong>✅ API Pagination OK</strong><br>
                        Total: ${data.pagination.total} nomenclatures<br>
                        Page 1: ${data.data.length} résultats
                    `, 'success');
                } else {
                    showResult('api-results', `❌ Erreur pagination: ${data.message}`, 'error');
                }
            } catch (error) {
                showResult('api-results', `❌ Erreur réseau: ${error.message}`, 'error');
            }
        }

        async function testStats() {
            try {
                const response = await fetch('request/nomenclatures_stats.php');
                const data = await response.json();

                if (data.success) {
                    showResult('api-results', `
                        <strong>✅ API Statistiques OK</strong><br>
                        Total: ${data.stats.total} nomenclatures<br>
                        Top famille: ${data.stats.topFamille.nom} (${data.stats.topFamille.count})<br>
                        Top unité: ${data.stats.topUnite.nom} (${data.stats.topUnite.count})
                    `, 'success');
                } else {
                    showResult('api-results', `❌ Erreur stats: ${data.message}`, 'error');
                }
            } catch (error) {
                showResult('api-results', `❌ Erreur réseau: ${error.message}`, 'error');
            }
        }

        async function testDuplicates() {
            try {
                const response = await fetch('request/nomenclatures_duplicates.php?action=detect');
                const data = await response.json();

                if (data.success) {
                    showResult('api-results', `
                        <strong>✅ API Doublons OK</strong><br>
                        Groupes de doublons: ${data.total_groups}<br>
                        Total éléments en doublon: ${data.total_items}
                    `, 'success');
                } else {
                    showResult('api-results', `❌ Erreur doublons: ${data.message}`, 'error');
                }
            } catch (error) {
                showResult('api-results', `❌ Erreur réseau: ${error.message}`, 'error');
            }
        }

        function generateTestExcel() {
            showResult('import-results', '📄 Génération d\'un fichier Excel test...', 'info');

            // Simulation de téléchargement du CSV
            const csvContent = `code_equipement,code_article,repere_equipement,designation_equipement,fabricant,type,numero_serie_fabricant,designation_article,numero_poste,quantite,unite,poste_technique,metier,date_creation,source
10000999,5920010001,TEST001,VANNE TEST 1,TESTFAB,TYPE1,SN001,ARTICLE TEST 1,P001,2,PCE,POSTE TEST,MECA,23/07/2025,IMPORT
10001000,5920010002,TEST002,VANNE TEST 2,TESTFAB,TYPE2,SN002,ARTICLE TEST 2,P002,1,PCE,POSTE TEST,ELEC,23/07/2025,IMPORT
10001001,5920010003,TEST003,POMPE TEST 1,TESTFAB2,TYPE1,SN003,ARTICLE TEST 3,P003,3,LOT,POSTE TEST,INSTRU,23/07/2025,IMPORT`;

            const blob = new Blob([csvContent], {
                type: 'text/csv'
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'test_import_nomenclatures.csv';
            a.click();
            window.URL.revokeObjectURL(url);

            showResult('import-results', '✅ Fichier CSV téléchargé ! Convertissez-le en Excel si nécessaire.', 'success');
        }

        document.getElementById('testFileInput').addEventListener('change', function() {
            document.querySelector('[onclick="testImport()"]').disabled = !this.files.length;
        });

        async function testImport() {
            const fileInput = document.getElementById('testFileInput');
            if (!fileInput.files.length) {
                showResult('import-results', '❌ Veuillez sélectionner un fichier', 'error');
                return;
            }

            clearResults('import-results');
            showResult('import-results', '⏳ Test d\'importation en cours...', 'info');

            try {
                const formData = new FormData();
                formData.append('excel_file', fileInput.files[0]);

                const response = await fetch('request/nomenclature_import_optimized.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showResult('import-results', `
                        <strong>✅ Importation réussie !</strong><br>
                        <ul>
                            <li>Total traité: ${result.stats.total}</li>
                            <li>Importé: ${result.stats.imported}</li>
                            <li>Doublons: ${result.stats.duplicates}</li>
                            <li>Erreurs: ${result.stats.errors}</li>
                            <li>Ignoré: ${result.stats.skipped}</li>
                        </ul>
                    `, 'success');
                } else {
                    showResult('import-results', `❌ Erreur importation: ${result.message}`, 'error');
                }
            } catch (error) {
                showResult('import-results', `❌ Erreur réseau: ${error.message}`, 'error');
            }
        }

        async function createTestDuplicates() {
            clearResults('duplicates-results');
            showResult('duplicates-results', '⏳ Création de doublons de test...', 'info');

            // Simulation - en réalité, il faudrait ajouter manuellement des doublons via SQL
            showResult('duplicates-results', `
                <strong>ℹ️ Pour créer des doublons de test :</strong><br>
                1. Importez le fichier test deux fois<br>
                2. Ou ajoutez manuellement des nomenclatures avec même repère équipement + code article<br>
                3. Puis testez la détection
            `, 'info');
        }

        async function detectDuplicates() {
            try {
                const response = await fetch('request/nomenclatures_duplicates.php?action=detect');
                const data = await response.json();

                if (data.success) {
                    if (data.total_groups === 0) {
                        showResult('duplicates-results', '✅ Aucun doublon détecté dans la base', 'success');
                    } else {
                        showResult('duplicates-results', `
                            <strong>⚠️ Doublons détectés !</strong><br>
                            Groupes: ${data.total_groups}<br>
                            Total éléments: ${data.total_items}
                        `, 'error');
                    }
                } else {
                    showResult('duplicates-results', `❌ Erreur: ${data.message}`, 'error');
                }
            } catch (error) {
                showResult('duplicates-results', `❌ Erreur réseau: ${error.message}`, 'error');
            }
        }

        async function cleanupTestData() {
            showResult('duplicates-results', `
                <strong>🧹 Nettoyage des données test</strong><br>
                Pour nettoyer, supprimez manuellement les nomenclatures avec source 'IMPORT' ou 'TEST'.
            `, 'info');
        }

        // Test automatique au chargement
        document.addEventListener('DOMContentLoaded', function() {
            showResult('api-results', '🚀 Page de test chargée. Cliquez sur les boutons pour tester !', 'info');
        });
    </script>
</body>

</html>