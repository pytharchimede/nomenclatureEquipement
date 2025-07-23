<?php

require_once 'model/Article.php';
require_once 'includes/auth.php';

// Statistiques optimisées pour les gros volumes
$totalArticles = 0;
$pdo = Database::getConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM articles");
$totalArticles = $stmt->fetchColumn();

// Récupération des statistiques par famille (optimisé pour les gros volumes)
$statsFamilles = [];
$nonAffectes = 0;

// Si le volume est raisonnable, on fait le calcul complet
if ($totalArticles <= 5000) {
    $articles = Article::getAll();
    foreach ($articles as $art) {
        $famille = $art['groupe_articles'] ?? 'Non défini';
        if ($famille && $famille !== 'Non défini' && $famille !== '') {
            if (!isset($statsFamilles[$famille])) $statsFamilles[$famille] = 0;
            $statsFamilles[$famille]++;
        } else {
            $nonAffectes++;
        }
    }
} else {
    // Pour les gros volumes, on fait un échantillonnage
    $stmt = $pdo->query("SELECT groupe_articles FROM articles ORDER BY RAND() LIMIT 1000");
    $echantillon = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($echantillon as $famille) {
        if ($famille && $famille !== 'Non défini' && $famille !== '') {
            if (!isset($statsFamilles[$famille])) $statsFamilles[$famille] = 0;
            $statsFamilles[$famille]++;
        } else {
            $nonAffectes++;
        }
    }
}

// Calcul des statistiques
$total = $totalArticles;
$topFamille = '';
$maxFamille = 0;
if ($statsFamilles) {
    $maxFamille = max($statsFamilles);
    $topFamille = array_search($maxFamille, $statsFamilles);
}
$familleLabels = array_keys($statsFamilles);
$familleData = array_values($statsFamilles);

// Récupération des valeurs distinctes pour les filtres
$fabricants = Article::getDistinctValues('fabricant');
$typesArticle = Article::getDistinctValues('type_article');
$groupes = Article::getDistinctValues('groupe_articles');
$unites = Article::getDistinctValues('uq_base');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Articles</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <link href="css/style_articles.css" rel="stylesheet">
    <style>
        .article-checkbox {
            accent-color: #1976d2;
            width: 1.2em;
            height: 1.2em;
            border-radius: 6px;
            box-shadow: 0 1px 2px #1976d233;
            transition: box-shadow 0.2s;
            cursor: pointer;
            border: 2px solid #1976d2;
        }

        .article-checkbox:focus {
            outline: none;
            box-shadow: 0 0 0 2px #90caf9;
        }

        .article-checkbox:checked {
            background-color: #1976d2;
            border-color: #1976d2;
        }

        #dropZone.dragover {
            background: #e3f2fd;
            border-color: #1976d2;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>
        <!-- Main Content -->
        <div class="flex-grow-1 content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="menu-toggle material-icons d-lg-none" onclick="toggleSidebar()">menu</span>
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Articles</h2>
                <div class="d-flex gap-2">
                    <a href="request/export_articles.php?type=excel" class="btn btn-outline-success" id="exportExcelBtn">
                        <span class="material-icons">file_download</span>Excel
                    </a>
                    <a href="request/export_articles.php?type=pdf" class="btn btn-outline-danger" id="exportPdfBtn">
                        <span class="material-icons">picture_as_pdf</span>PDF
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addArticleModal"><span class="material-icons">add</span>Ajouter</button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importArticleModal"><span class="material-icons">upload_file</span>Importer Excel</button>
                    <a href="logout.php" class="btn btn-outline-primary ms-2">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>
            <!-- Mini Cards et Graphes -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons">inventory_2</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;" data-stat="total-articles"><?= $total ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Total articles</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;" data-stat="top-famille"><?= htmlspecialchars($topFamille) ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Famille la + présente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPie" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;" data-stat="max-famille"><?= $maxFamille ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max dans une famille</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#e53935;">help_outline</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;" data-stat="non-affectes"><?= $nonAffectes ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Non affectés à une famille</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Filtres de recherche (style Excel) -->
            <div class="card shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Recherche globale</label>
                            <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Code, désignation, fabricant..." />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Fabricant</label>
                            <select id="fabricant-filter" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <?php foreach ($fabricants as $fabricant): ?>
                                    <option value="<?= htmlspecialchars($fabricant) ?>"><?= htmlspecialchars($fabricant) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Type d'article</label>
                            <select id="type-filter" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <?php foreach ($typesArticle as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Groupe</label>
                            <select id="groupe-filter" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <?php foreach ($groupes as $groupe): ?>
                                    <option value="<?= htmlspecialchars($groupe) ?>"><?= htmlspecialchars($groupe) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small text-muted mb-1">UQ</label>
                            <select id="uq-filter" class="form-select form-select-sm">
                                <option value="">Toutes</option>
                                <?php foreach ($unites as $unite): ?>
                                    <option value="<?= htmlspecialchars($unite) ?>"><?= htmlspecialchars($unite) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small text-muted mb-1">&nbsp;</label>
                            <button id="reset-filters" class="btn btn-outline-secondary btn-sm w-100">
                                <span class="material-icons" style="font-size:16px;">clear</span>
                            </button>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small text-muted mb-1">Actions</label>
                            <div class="d-flex gap-1">
                                <button id="exportFilteredBtn" class="btn btn-outline-primary btn-sm" style="display:none;">
                                    <span class="material-icons" style="font-size:16px;">file_download</span>
                                </button>
                                <button id="deleteSelectedBtn" class="btn btn-outline-danger btn-sm" style="display:none;">
                                    <span class="material-icons" style="font-size:16px;">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des articles -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">Liste des articles</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div id="pagination-info" class="text-muted small">
                                Chargement...
                            </div>
                            <div id="loading-indicator" style="display: none;">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <span class="small">Chargement...</span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table id="articles-table" class="table table-hover align-middle">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th><input type="checkbox" id="select-all-articles"></th>
                                    <th>Code</th>
                                    <th>Désignation</th>
                                    <th>Type</th>
                                    <th>UQ base</th>
                                    <th>Fabricant</th>
                                    <th>N° pce fabricant</th>
                                    <th>Groupe</th>
                                    <th>Document</th>
                                    <th>Description</th>
                                    <th>Date création</th>
                                    <th>Créé par</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Les données seront chargées via JavaScript avec pagination -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Ajout -->
        <div class="modal fade" id="addArticleModal" tabindex="-1" aria-labelledby="addArticleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" id="addArticleForm" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addArticleModalLabel">Ajouter un article</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Code article</label>
                            <input type="text" name="code_article" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Désignation</label>
                            <input type="text" name="designation_article" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type d'article</label>
                            <input type="text" name="type_article" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">UQ base</label>
                            <input type="text" name="uq_base" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fabricant</label>
                            <input type="text" name="fabricant" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">N° pièce fabricant</label>
                            <input type="text" name="numero_piece_fabricant" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Famille</label>
                            <input type="text" name="groupe_articles" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Document</label>
                            <input type="text" name="document" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date création</label>
                            <input type="date" name="date_creation" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Créé par</label>
                            <input type="text" name="cree_par" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="addArticleBtn">Ajouter</button>
                    </div>
                    <div id="addArticleMsg" class="w-100 mt-2"></div>
                </form>
            </div>
        </div>
        <!-- Modal Importation Articles -->
        <div class="modal fade" id="importArticleModal" tabindex="-1" aria-labelledby="importArticleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" id="importArticleForm" enctype="multipart/form-data" method="post" action="request/article_import_optimized.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importArticleModalLabel">Importer des articles (Excel)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="dropZone" class="border rounded p-4 text-center mb-3" style="cursor:pointer;background:#f8fafc;">
                            <span class="material-icons" style="font-size:2.5rem;color:#1976d2;">cloud_upload</span>
                            <div class="mt-2">Glissez-déposez votre fichier ici ou cliquez pour sélectionner</div>
                            <input type="file" name="excel_file" id="excel_file" class="form-control d-none" accept=".xlsx,.xls" required>
                        </div>
                        <div class="progress mb-2 d-none" id="importProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div>
                        </div>
                        <div id="importLog" class="small" style="max-height:150px;overflow:auto;"></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" id="importBtn" type="submit">
                            <span class="spinner-border spinner-border-sm d-none" id="importSpinner"></span>
                            Importer
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Modal Modification Article -->
        <div class="modal fade" id="editArticleModal" tabindex="-1" aria-labelledby="editArticleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" id="editArticleForm" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editArticleModalLabel">Modifier un article</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3" id="editArticleFields">
                        <!-- Les champs seront injectés dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="editArticleBtn">Enregistrer</button>
                        <div id="editArticleMsg" class="w-100 mt-2"></div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Modal export en cours -->
        <div class="modal fade" id="exportLoadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center p-4">
                    <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                    <div id="exportProgressText" style="font-size:1.2rem;">Préparation de l’export, veuillez patienter...</div>
                    <div class="progress mt-3" style="height:18px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="exportProgressBar" style="width:0%">0%</div>
                    </div>
                    <button id="closeExportModalBtn" class="btn btn-outline-secondary mt-3" style="display:none;">Fermer</button>
                    <div class="mt-2 text-muted" style="font-size:0.95em;">Le téléchargement va démarrer automatiquement.<br>Si ce n'est pas le cas, cliquez sur "Fermer".</div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- Chart.js -->
    <script src="plugins/js/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Mini Pie Chart Famille
        new Chart(document.getElementById('miniPieArticle').getContext('2d'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    data: <?= json_encode($catData) ?>,
                    backgroundColor: [
                        '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa'
                    ]
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                responsive: false
            }
        });

        // Mini Pie Chart Unité
        new Chart(document.getElementById('miniPieUnite').getContext('2d'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($uniteLabels) ?>,
                datasets: [{
                    data: <?= json_encode($uniteData) ?>,
                    backgroundColor: [
                        '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa'
                    ]
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                responsive: false
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('table.table');
            const tbody = table.querySelector('tbody');
            const filterRow = document.getElementById('filter-row-article');
            const filterInputs = filterRow.querySelectorAll('input, select');
            const exportFilteredBtn = document.getElementById('exportFilteredArticleBtn');
            const deleteBtn = document.getElementById('deleteSelectedArticleBtn');
            const selectAll = document.getElementById('selectAllArticle');
            const checkboxes = document.querySelectorAll('.article-checkbox');

            function filterTable() {
                let total = 0;
                let catCounts = {};
                tbody.querySelectorAll('tr').forEach(tr => {
                    let show = true;
                    filterInputs.forEach((input, idx) => {
                        let val = input.value.trim().toLowerCase();
                        let colIdx = parseInt(input.getAttribute('data-col'), 10);
                        let cell = tr.children[colIdx];
                        if (!cell) return;
                        if (input.tagName === 'SELECT') {
                            if (val && cell.textContent.trim().toLowerCase() !== val) show = false;
                        } else if (input.type === 'date') {
                            if (val && cell.textContent.trim().substr(0, 10) !== val) show = false;
                        } else {
                            if (val && !cell.textContent.toLowerCase().includes(val)) show = false;
                        }
                    });
                    tr.style.display = show ? '' : 'none';
                    if (show) {
                        total++;
                        let cat = tr.children[7].textContent.trim();
                        catCounts[cat] = (catCounts[cat] || 0) + 1;
                    }
                });
                // Affiche ou masque le bouton d'export selon le résultat
                if (exportFilteredBtn) exportFilteredBtn.style.display = total > 0 ? '' : 'none';
                if (deleteBtn) deleteBtn.style.display = document.querySelectorAll('.article-checkbox:checked').length > 0 ? '' : 'none';
            }

            filterInputs.forEach(input => {
                input.addEventListener('input', filterTable);
                input.addEventListener('change', filterTable);
            });
            filterTable();

            // Sélectionner tout
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => {
                        if (cb.closest('tr').style.display !== 'none') {
                            cb.checked = selectAll.checked;
                        }
                    });
                    filterTable();
                });
            }

            // Mise à jour bouton suppression sur chaque case
            checkboxes.forEach(cb => {
                cb.addEventListener('change', filterTable);
            });

            // Export sélection filtrée
            if (exportFilteredBtn) {
                exportFilteredBtn.addEventListener('click', function() {
                    const rows = Array.from(document.querySelectorAll('table.table tbody tr'))
                        .filter(tr => tr.style.display !== 'none');
                    if (rows.length === 0) {
                        alert("Aucune donnée à exporter !");
                        return;
                    }
                    const data = rows.map(tr => Array.from(tr.children).map(td => td.textContent.trim()));
                    // Récupère les critères de filtre non vides
                    const filterInputs = filterRow.querySelectorAll('input, select');
                    const columnLabels = [
                        "Code Article", "Désignation Article", "Type d'article", "UQ base", "Fabricant",
                        "N° pce fabricant", "Famille", "Document", "Description", "Date création", "Créé par"
                    ];
                    const filters = [];
                    filterInputs.forEach((input, idx) => {
                        if (input.value && input.value.trim() !== '') {
                            filters.push({
                                col: idx,
                                label: columnLabels[idx],
                                value: input.value
                            });
                        }
                    });

                    // Affiche le modal de chargement
                    const exportModal = new bootstrap.Modal(document.getElementById('exportLoadingModal'));
                    const progressBar = document.getElementById('exportProgressBar');
                    const progressText = document.getElementById('exportProgressText');
                    const closeBtn = document.getElementById('closeExportModalBtn');
                    progressBar.style.width = "0%";
                    progressBar.textContent = "0%";
                    progressText.textContent = "Préparation de l’export, veuillez patienter...";
                    closeBtn.style.display = "none";
                    closeBtn.disabled = true;
                    exportModal.show();

                    // Animation de progression fictive
                    let percent = 0;
                    const interval = setInterval(() => {
                        percent += Math.random() * 10 + 5;
                        if (percent > 90) percent = 90;
                        progressBar.style.width = percent + "%";
                        progressBar.textContent = Math.round(percent) + "%";
                    }, 200);

                    // Envoie en POST vers le script d'export filtré
                    setTimeout(() => {
                        clearInterval(interval);
                        progressBar.style.width = "100%";
                        progressBar.textContent = "100%";
                        progressText.textContent = "Téléchargement en cours...";

                        // Création et soumission du formulaire caché
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'request/export_articles.php?type=excel&filtered=1';
                        form.style.display = 'none';
                        const inputData = document.createElement('input');
                        inputData.type = 'hidden';
                        inputData.name = 'filtered_data';
                        inputData.value = JSON.stringify(data);
                        form.appendChild(inputData);
                        const inputFilters = document.createElement('input');
                        inputFilters.type = 'hidden';
                        inputFilters.name = 'filters';
                        inputFilters.value = JSON.stringify(filters);
                        form.appendChild(inputFilters);
                        document.body.appendChild(form);
                        form.submit();

                        // Affiche le bouton "Fermer" après 10 secondes
                        setTimeout(() => {
                            closeBtn.style.display = "";
                            closeBtn.disabled = false;
                        }, 10000);

                    }, 1200); // Délai pour simuler la préparation

                    closeBtn.onclick = function() {
                        exportModal.hide();
                    };
                });
            }

            // Suppression de masse
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const checked = Array.from(document.querySelectorAll('.article-checkbox:checked'));
                    if (checked.length === 0) return;
                    if (!confirm(`Voulez-vous vraiment supprimer ${checked.length} article(s) ? Cette action est irréversible.`)) return;
                    const ids = checked.map(cb => cb.value);
                    fetch('request/article_delete.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                ids
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                checked.forEach(cb => cb.closest('tr').remove());
                                filterTable();
                                alert(res.message || "Suppression réussie !");
                            } else {
                                alert(res.message || "Erreur lors de la suppression.");
                            }
                        })
                        .catch(() => alert("Erreur réseau lors de la suppression."));
                });
            }

            // Importation d'articles
            const importForm = document.getElementById('importArticleForm');
            const importBtn = document.getElementById('importBtn');
            const importSpinner = document.getElementById('importSpinner');
            const importLog = document.getElementById('importLog');
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('excel_file');
            const importProgress = document.getElementById('importProgress');
            const importProgressBar = importProgress.querySelector('.progress-bar');

            // Drag & drop
            dropZone.addEventListener('click', () => fileInput.click());
            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.classList.add('dragover');
            });
            dropZone.addEventListener('dragleave', e => {
                e.preventDefault();
                dropZone.classList.remove('dragover');
            });
            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    // Affiche le nom du fichier
                    dropZone.querySelector('div.mt-2').textContent = fileInput.files[0].name;
                }
            });

            // Affichage du nom du fichier sélectionné
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length) {
                    dropZone.querySelector('div.mt-2').textContent = fileInput.files[0].name;
                } else {
                    dropZone.querySelector('div.mt-2').textContent = "Glissez-déposez votre fichier ici ou cliquez pour sélectionner";
                }
            });

            // Soumission AJAX avec jauge et log
            importForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!fileInput.files.length) return;

                importBtn.disabled = true;
                importSpinner.classList.remove('d-none');
                importBtn.textContent = " Importation...";
                importBtn.prepend(importSpinner);
                importProgress.classList.remove('d-none');
                importProgressBar.style.width = "0%";
                importProgressBar.textContent = "0%";
                importLog.innerHTML = "";

                const formData = new FormData();
                formData.append('excel_file', fileInput.files[0]);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', importForm.action, true);

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        let percent = Math.round((e.loaded / e.total) * 100);
                        importProgressBar.style.width = percent + "%";
                        importProgressBar.textContent = percent + "%";
                    }
                };

                xhr.onload = function() {
                    importSpinner.classList.add('d-none');
                    importBtn.disabled = false;
                    importBtn.classList.remove('btn-primary');
                    importBtn.classList.add('btn-success');
                    importBtn.textContent = "Fermer";
                    importBtn.setAttribute('data-bs-dismiss', 'modal');
                    importProgress.classList.add('d-none');
                    // Affichage du log
                    try {
                        const res = JSON.parse(xhr.responseText);
                        let html = "";
                        if (res.success) {
                            html += `<div class="text-success mb-2"><b>Importation réussie !</b></div>`;
                        } else {
                            html += `<div class="text-danger mb-2"><b>Erreur lors de l'importation.</b></div>`;
                        }
                        if (res.imported) html += `<div>${res.imported} article(s) importé(s).</div>`;
                        if (res.duplicates && res.duplicates.length)
                            html += `<div class="text-warning">Doublons ignorés :<ul>${res.duplicates.map(d => `<li>${d}</li>`).join('')}</ul></div>`;
                        if (res.errors && res.errors.length)
                            html += `<div class="text-danger">Erreurs :<ul>${res.errors.map(d => `<li>${d}</li>`).join('')}</ul></div>`;
                        importLog.innerHTML = html;
                    } catch {
                        importLog.innerHTML = "<div class='text-danger'>Erreur inattendue lors de l'import.</div>";
                    }
                };

                xhr.onerror = function() {
                    importSpinner.classList.add('d-none');
                    importBtn.disabled = false;
                    importBtn.textContent = "Fermer";
                    importBtn.setAttribute('data-bs-dismiss', 'modal');
                    importLog.innerHTML = "<div class='text-danger'>Erreur réseau lors de l'import.</div>";
                };

                xhr.send(formData);
            });

            // Réinitialisation du modal à l'ouverture
            document.getElementById('importArticleModal').addEventListener('show.bs.modal', function() {
                importBtn.disabled = false;
                importBtn.classList.remove('btn-success');
                importBtn.classList.add('btn-primary');
                importBtn.textContent = "Importer";
                importBtn.removeAttribute('data-bs-dismiss');
                importSpinner.classList.add('d-none');
                importProgress.classList.add('d-none');
                progressBar.style.width = "0%";
                progressBar.textContent = "0%";
                importLog.innerHTML = "";
                dropZone.querySelector('div.mt-2').textContent = "Glissez-déposez votre fichier ici ou cliquez pour sélectionner";
                fileInput.value = "";
            });

            const addArticleForm = document.getElementById('addArticleForm');
            const addArticleBtn = document.getElementById('addArticleBtn');
            const addArticleMsg = document.getElementById('addArticleMsg');

            if (addArticleForm) {
                addArticleForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    addArticleBtn.disabled = true;
                    addArticleMsg.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ajout...';

                    const formData = new FormData(addArticleForm);

                    fetch('request/article_add.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                addArticleMsg.innerHTML = '<span class="text-success">' + res.message + '</span>';
                                addArticleBtn.classList.remove('btn-primary');
                                addArticleBtn.classList.add('btn-success');
                                addArticleBtn.textContent = "Fermer";
                                addArticleBtn.setAttribute('data-bs-dismiss', 'modal');
                                // Optionnel : recharger la page ou ajouter la ligne dynamiquement
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                addArticleMsg.innerHTML = '<span class="text-danger">' + res.message + '</span>';
                                addArticleBtn.disabled = false;
                            }
                        })
                        .catch(() => {
                            addArticleMsg.innerHTML = '<span class="text-danger">Erreur réseau.</span>';
                            addArticleBtn.disabled = false;
                        });
                });

                // Réinitialisation du modal à l'ouverture
                document.getElementById('addArticleModal').addEventListener('show.bs.modal', function() {
                    addArticleBtn.disabled = false;
                    addArticleBtn.classList.remove('btn-success');
                    addArticleBtn.classList.add('btn-primary');
                    addArticleBtn.textContent = "Ajouter";
                    addArticleBtn.removeAttribute('data-bs-dismiss');
                    addArticleMsg.innerHTML = "";
                    addArticleForm.reset();
                });
            }

            // Édition d'un article
            const editModal = new bootstrap.Modal(document.getElementById('editArticleModal'));
            const editArticleForm = document.getElementById('editArticleForm');
            const editArticleFields = document.getElementById('editArticleFields');
            const editArticleBtn = document.getElementById('editArticleBtn');
            const editArticleMsg = document.getElementById('editArticleMsg');

            // Ouvre le modal au clic sur une ligne (hors checkbox)
            document.querySelectorAll('table.table tbody tr').forEach(tr => {
                tr.addEventListener('click', function(e) {
                    if (e.target.tagName === 'INPUT' && e.target.type === 'checkbox') return;
                    const tds = tr.querySelectorAll('td');
                    const id = tds[1].getAttribute('data-id');
                    fetch('request/article_get.php?id=' + id)
                        .then(r => r.json())
                        .then(data => {
                            // Génère les champs dynamiquement
                            let html = '';
                            const fields = [{
                                    name: 'code_article',
                                    label: 'Code article'
                                },
                                {
                                    name: 'designation_article',
                                    label: 'Désignation'
                                },
                                {
                                    name: 'type_article',
                                    label: "Type d'article"
                                },
                                {
                                    name: 'temsup_niv_mdt',
                                    label: "TémSup.:niv.mdt"
                                },
                                {
                                    name: 'ancien_num_article',
                                    label: "Anc. n° article"
                                },
                                {
                                    name: 'uq_base',
                                    label: "UQ base"
                                },
                                {
                                    name: 'fabricant',
                                    label: "Fabricant"
                                },
                                {
                                    name: 'numero_piece_fabricant',
                                    label: "N° pce fabricant"
                                },
                                {
                                    name: 'groupe_articles',
                                    label: "Famille"
                                },
                                {
                                    name: 'groupe_marche_externe',
                                    label: "Gpe march.ext."
                                },
                                {
                                    name: 'document',
                                    label: "Document"
                                },
                                {
                                    name: 'description',
                                    label: "Description"
                                },
                                {
                                    name: 'date_creation',
                                    label: "Date création",
                                    type: 'date'
                                },
                                {
                                    name: 'cree_par',
                                    label: "Créé par"
                                }
                            ];
                            fields.forEach(f => {
                                let val = data[f.name] ?? '';
                                let type = f.type || 'text';
                                let valid = val ? 'is-valid' : 'is-invalid';
                                html += `
                                <div class="col-md-6">
                                    <label class="form-label">${f.label}</label>
                                    <input type="${type}" name="${f.name}" value="${type==='date' && val ? val.substr(0,10) : val}" class="form-control ${valid}">
                                </div>`;
                            });
                            html += `<input type="hidden" name="id" value="${data.id}">`;
                            editArticleFields.innerHTML = html;
                            editArticleMsg.innerHTML = '';
                            editArticleBtn.disabled = false;
                            editArticleBtn.classList.remove('btn-success');
                            editArticleBtn.classList.add('btn-primary');
                            editArticleBtn.textContent = "Enregistrer";
                            editArticleBtn.removeAttribute('data-bs-dismiss');
                            editModal.show();

                            // Coloration dynamique
                            editArticleFields.querySelectorAll('input').forEach(input => {
                                input.addEventListener('input', function() {
                                    if (input.value.trim()) {
                                        input.classList.remove('is-invalid');
                                        input.classList.add('is-valid');
                                    } else {
                                        input.classList.remove('is-valid');
                                        input.classList.add('is-invalid');
                                    }
                                });
                            });
                        });
                });
            });

            // Soumission AJAX du formulaire de modification
            if (editArticleForm) {
                editArticleForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    editArticleBtn.disabled = true;
                    editArticleMsg.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Modification...';

                    const formData = new FormData(editArticleForm);

                    fetch('request/article_update.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                editArticleMsg.innerHTML = '<span class="text-success">' + res.message + '</span>';
                                editArticleBtn.classList.remove('btn-primary');
                                editArticleBtn.classList.add('btn-success');
                                editArticleBtn.textContent = "Fermer";
                                editArticleBtn.setAttribute('data-bs-dismiss', 'modal');
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                editArticleMsg.innerHTML = '<span class="text-danger">' + res.message + '</span>';
                                editArticleBtn.disabled = false;
                            }
                        })
                        .catch(() => {
                            editArticleMsg.innerHTML = '<span class="text-danger">Erreur réseau.</span>';
                            editArticleBtn.disabled = false;
                        });
                });
            }

            // Gestion du modal export pour Excel/PDF complet
            const exportExcelBtn = document.getElementById('exportExcelBtn');
            const exportPdfBtn = document.getElementById('exportPdfBtn');
            const exportModal = new bootstrap.Modal(document.getElementById('exportLoadingModal'));
            const exportProgressBar = document.getElementById('exportProgressBar');
            const progressText = document.getElementById('exportProgressText');
            const closeBtn = document.getElementById('closeExportModalBtn');

            function showExportModalAndDownload(url) {
                exportProgressBar.style.width = "0%";
                exportProgressBar.textContent = "0%";
                progressText.textContent = "Préparation de l’export, veuillez patienter...";
                closeBtn.style.display = "none";
                closeBtn.disabled = true;
                exportModal.show();

                let percent = 0;
                const interval = setInterval(() => {
                    percent += Math.random() * 10 + 5;
                    if (percent > 90) percent = 90;
                    exportProgressBar.style.width = percent + "%";
                    exportProgressBar.textContent = Math.round(percent) + "%";
                }, 200);

                setTimeout(() => {
                    clearInterval(interval);
                    exportProgressBar.style.width = "100%";
                    exportProgressBar.textContent = "100%";
                    progressText.textContent = "Téléchargement en cours...";

                    // Lance le téléchargement
                    window.location.href = url;

                    // Affiche le bouton "Fermer" après 10 secondes
                    setTimeout(() => {
                        closeBtn.style.display = "";
                        closeBtn.disabled = false;
                    }, 10000);

                }, 1200);

                closeBtn.onclick = function() {
                    exportModal.hide();
                };
            }

            if (exportExcelBtn) {
                exportExcelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showExportModalAndDownload(this.href);
                });
            }
            if (exportPdfBtn) {
                exportPdfBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showExportModalAndDownload(this.href);
                });
            }
        });
    </script>
    <!-- Integration du nouveau système de pagination et filtrage -->
    <script src="js/function_articles_updated.js"></script>
</body>

</html>