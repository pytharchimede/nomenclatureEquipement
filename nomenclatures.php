<?php
require_once 'model/Nomenclature.php';
$nomenclatures = Nomenclature::getAll();


session_start();
require_once 'includes/auth.php';


// Détection des doublons (code_equipement + code_article)
$dups = [];
$seen = [];
foreach ($nomenclatures as $nom) {
    $key = ($nom['code_equipement'] ?? '') . '|' . ($nom['code_article'] ?? '');
    if (!$nom['code_equipement'] || !$nom['code_article']) continue;
    if (isset($seen[$key])) {
        $dups[$key][] = $nom;
    } else {
        $seen[$key] = $nom;
    }
}
foreach ($seen as $key => $first) {
    if (isset($dups[$key])) {
        array_unshift($dups[$key], $first);
    }
}
$hasDups = count($dups) > 0;

// Statistiques pour mini-cards
$total = count($nomenclatures);

// Par désignation article (famille)
$categories = [];
foreach ($nomenclatures as $nom) {
    $cat = $nom['designation_article'] ?? 'Non défini';
    $categories[$cat] = ($categories[$cat] ?? 0) + 1;
}
$catLabels = array_keys($categories);
$catData = array_values($categories);
$catMax = $catData ? max($catData) : 0;
$catTop = $catLabels ? $catLabels[array_search($catMax, $catData)] : '';

// Par unité
$unites = [];
foreach ($nomenclatures as $nom) {
    $u = $nom['unite'] ?? 'Non défini';
    $unites[$u] = ($unites[$u] ?? 0) + 1;
}
$uniteLabels = array_keys($unites);
$uniteData = array_values($unites);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Nomenclatures</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <link href="css/style_nomenclature.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <?php include 'menu.php'; ?>
        <div class="flex-grow-1 content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="menu-toggle material-icons d-lg-none" onclick="toggleSidebar()">menu</span>
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Nomenclatures</h2>
                <div class="d-flex gap-2">
                    <?php if (hasDroit('nomenclature', 'lire')): ?>
                        <a href="request/export_nomenclatures.php?type=excel" class="btn btn-outline-success" id="exportExcelBtn">
                            <span class="material-icons">file_download</span>Excel
                        </a>
                        <a href="request/export_nomenclatures.php?type=pdf" class="btn btn-outline-danger" id="exportPdfBtn">
                            <span class="material-icons">picture_as_pdf</span>PDF
                        </a>
                    <?php endif; ?>
                    <?php if (hasDroit('nomenclature', 'creer')): ?>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importNomenclatureModal">
                            <span class="material-icons">upload_file</span>Importer Excel
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNomenclatureModal">
                            <span class="material-icons">add</span>Ajouter
                        </button>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-primary ms-2">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>
            <!-- Mini Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons">list_alt</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $total ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Total nomenclatures</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catTop ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Famille la + présente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPieNomenclature" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catMax ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max dans une famille</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPieUniteNomenclature" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $uniteLabels[0] ?? '' ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Unité la + utilisée</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($hasDups): ?>
                <div class="alert alert-danger d-flex align-items-center justify-content-between" style="font-size:1.1em;">
                    <div>
                        <span class="material-icons me-2" style="vertical-align:middle;">warning</span>
                        <b>Doublons détectés :</b>
                        <?= count($dups) ?> doublon(s) trouvé(s) (même code équipement et code article).
                    </div>
                    <a href="gestion_doublons_nomenclature.php" class="btn btn-danger btn-sm">
                        Gérer les doublons
                    </a>
                </div>
            <?php endif; ?>
            <!-- Tableau des nomenclatures -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">Liste des nomenclatures</h5>
                        <div>
                            <?php if (hasDroit('nomenclature', 'lire')): ?>
                                <button id="exportFilteredNomenclatureBtn" class="btn btn-outline-primary me-2" style="display:none;">
                                    <span class="material-icons">file_download</span>Exporter la sélection
                                </button>
                            <?php endif; ?>
                            <?php if (hasDroit('nomenclature', 'supprimer')): ?>
                                <button id="deleteSelectedNomenclatureBtn" class="btn btn-outline-danger" style="display:none;">
                                    <span class="material-icons">delete</span>Supprimer la sélection
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllNomenclature"></th>
                                    <th>Code équipement</th>
                                    <th>Code article</th>
                                    <th>Repère équipement</th>
                                    <th>Désignation équipement</th>
                                    <th>Fabricant</th>
                                    <th>Type</th>
                                    <th>N° série fabricant</th>
                                    <th>Désignation article</th>
                                    <th>N° poste</th>
                                    <th>Quantité</th>
                                    <th>Unité</th>
                                    <th>Poste technique</th>
                                    <th>Métier</th>
                                    <th>Date création</th>
                                    <th>Source</th>
                                </tr>
                                <tr id="filter-row-nomenclature">
                                    <th></th>
                                    <?php for ($i = 1; $i <= 15; $i++): ?>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer" data-col="<?= $i ?>"></th>
                                    <?php endfor; ?>
                                    <th>
                                        <button type="button" id="resetNomenclatureFilters" class="btn btn-sm btn-outline-secondary" title="Réinitialiser les filtres">
                                            <span class="material-icons" style="font-size:1.1em;">close</span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="nomenclatureTableBody">
                                <?php foreach ($nomenclatures as $nom): ?>
                                    <tr>
                                        <td><input type="checkbox" class="nomenclature-checkbox" value="<?= $nom['id'] ?>"></td>
                                        <td><?= htmlspecialchars($nom['code_equipement'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['code_article'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['repere_equipement'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['designation_equipement'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['fabricant'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['type'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['numero_serie_fabricant'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['designation_article'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['numero_poste'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['quantite'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['unite'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['poste_technique'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['metier'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['date_creation'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($nom['source'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center my-3" id="paginationNomenclature"></div>
                </div>
            </div>
            <!-- Modal Importation Nomenclatures -->
            <div class="modal fade" id="importNomenclatureModal" tabindex="-1" aria-labelledby="importNomenclatureModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="importNomenclatureForm" enctype="multipart/form-data" method="post" action="request/nomenclature_import.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importNomenclatureModalLabel">Importer des nomenclatures (Excel)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="dropZoneNomenclature" class="border rounded p-4 text-center mb-3" style="cursor:pointer;background:#f8fafc;">
                                <span class="material-icons" style="font-size:2.5rem;color:#1976d2;">cloud_upload</span>
                                <div class="mt-2">Glissez-déposez votre fichier ici ou cliquez pour sélectionner</div>
                                <input type="file" name="excel_file" id="excel_file_nomenclature" class="form-control d-none" accept=".xlsx,.xls" required>
                            </div>
                            <div class="progress mb-2 d-none" id="importProgressNomenclature">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div>
                            </div>
                            <div id="importLogNomenclature" class="small" style="max-height:150px;overflow:auto;"></div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="importBtnNomenclature" type="submit">
                                <span class="spinner-border spinner-border-sm d-none" id="importSpinnerNomenclature"></span>
                                Importer
                            </button>
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
            <!-- Modal Ajout Nomenclature -->
            <div class="modal fade" id="addNomenclatureModal" tabindex="-1" aria-labelledby="addNomenclatureModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" id="addNomenclatureForm" autocomplete="off">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNomenclatureModalLabel">Ajouter une nomenclature</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body row g-3">
                            <?php
                            $fields = [
                                ['name' => 'code_equipement', 'label' => 'Code équipement'],
                                ['name' => 'code_article', 'label' => 'Code article'],
                                ['name' => 'repere_equipement', 'label' => 'Repère équipement'],
                                ['name' => 'designation_equipement', 'label' => 'Désignation équipement'],
                                ['name' => 'fabricant', 'label' => 'Fabricant'],
                                ['name' => 'type', 'label' => 'Type'],
                                ['name' => 'numero_serie_fabricant', 'label' => 'N° série fabricant'],
                                ['name' => 'designation_article', 'label' => 'Désignation article'],
                                ['name' => 'numero_poste', 'label' => 'N° poste'],
                                ['name' => 'quantite', 'label' => 'Quantité'],
                                ['name' => 'unite', 'label' => 'Unité'],
                                ['name' => 'poste_technique', 'label' => 'Poste technique'],
                                ['name' => 'metier', 'label' => 'Métier'],
                                ['name' => 'date_creation', 'label' => 'Date création', 'type' => 'date'],
                                ['name' => 'source', 'label' => 'Source']
                            ];
                            foreach ($fields as $f): ?>
                                <div class="col-md-6">
                                    <label class="form-label"><?= $f['label'] ?></label>
                                    <input type="<?= $f['type'] ?? 'text' ?>" name="<?= $f['name'] ?>" class="form-control">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="addNomenclatureBtn">Ajouter</button>
                        </div>
                        <div id="addNomenclatureMsg" class="w-100 mt-2"></div>
                    </form>
                </div>
            </div>
            <!-- Modal Modification Nomenclature -->
            <div class="modal fade" id="editNomenclatureModal" tabindex="-1" aria-labelledby="editNomenclatureModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" id="editNomenclatureForm" autocomplete="off">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editNomenclatureModalLabel">Modifier une nomenclature</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body row g-3" id="editNomenclatureFields">
                            <!-- Champs injectés dynamiquement -->
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="editNomenclatureBtn">Enregistrer</button>
                            <div id="editNomenclatureMsg" class="w-100 mt-2"></div>
                        </div>
                    </form>
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
        new Chart(document.getElementById('miniPieNomenclature').getContext('2d'), {
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
        new Chart(document.getElementById('miniPieUniteNomenclature').getContext('2d'), {
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
            const tbody = document.getElementById('nomenclatureTableBody');
            const pagination = document.getElementById('paginationNomenclature');
            let currentPage = 1;
            const limit = 50; // nombre de lignes par page

            function renderRows(rows) {
                tbody.innerHTML = '';
                rows.forEach(nom => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><input type="checkbox" class="nomenclature-checkbox" value="${nom.id}"></td>
                        <td>${nom.code_equipement ?? ''}</td>
                        <td>${nom.code_article ?? ''}</td>
                        <td>${nom.repere_equipement ?? ''}</td>
                        <td>${nom.designation_equipement ?? ''}</td>
                        <td>${nom.fabricant ?? ''}</td>
                        <td>${nom.type ?? ''}</td>
                        <td>${nom.numero_serie_fabricant ?? ''}</td>
                        <td>${nom.designation_article ?? ''}</td>
                        <td>${nom.numero_poste ?? ''}</td>
                        <td>${nom.quantite ?? ''}</td>
                        <td>${nom.unite ?? ''}</td>
                        <td>${nom.poste_technique ?? ''}</td>
                        <td>${nom.metier ?? ''}</td>
                        <td>${nom.date_creation ?? ''}</td>
                        <td>${nom.source ?? ''}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            function renderPagination(total, page, limit) {
                const pageCount = Math.ceil(total / limit);
                let html = '';
                if (page > 1) {
                    html += `<button class="btn btn-outline-primary btn-sm me-2" data-page="${page-1}">Précédent</button>`;
                }
                html += `<span class="mx-2">Page ${page} / ${pageCount}</span>`;
                if (page < pageCount) {
                    html += `<button class="btn btn-outline-primary btn-sm ms-2" data-page="${page+1}">Suivant</button>`;
                }
                pagination.innerHTML = html;
                pagination.querySelectorAll('button[data-page]').forEach(btn => {
                    btn.onclick = function() {
                        loadPage(parseInt(this.getAttribute('data-page')));
                    };
                });
            }

            const filterRow = document.getElementById('filter-row-nomenclature');
            const filterInputs = filterRow.querySelectorAll('input, select');
            const resetBtn = document.getElementById('resetNomenclatureFilters');

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    filterInputs.forEach(input => input.value = '');
                    loadPage(1);
                });
            }

            let currentFilters = {};

            function getFilters() {
                const filterCols = [
                    'code_equipement', 'code_article', 'repere_equipement', 'designation_equipement', 'fabricant',
                    'type', 'numero_serie_fabricant', 'designation_article', 'numero_poste', 'quantite', 'unite',
                    'poste_technique', 'metier', 'date_creation', 'source'
                ];
                let filters = {};
                filterInputs.forEach((input, idx) => {
                    filters[filterCols[idx]] = input.value.trim();
                });
                return filters;
            }

            function loadPage(page) {
                currentFilters = getFilters();
                const params = new URLSearchParams({
                    page: page,
                    limit: limit,
                    filters: JSON.stringify(currentFilters)
                });
                fetch(`request/nomenclature_page.php?${params.toString()}`)
                    .then(r => r.json())
                    .then(res => {
                        renderRows(res.data);
                        renderPagination(res.total, page, limit);
                        currentPage = page;
                    });
            }

            // Rafraîchir la page à chaque changement de filtre
            filterInputs.forEach(input => {
                input.addEventListener('input', () => loadPage(1));
                input.addEventListener('change', () => loadPage(1));
            });

            // Initialisation
            loadPage(1);

            const exportFilteredBtn = document.getElementById('exportFilteredNomenclatureBtn');
            const deleteBtn = document.getElementById('deleteSelectedNomenclatureBtn');
            const selectAll = document.getElementById('selectAllNomenclature');
            const checkboxes = document.querySelectorAll('.nomenclature-checkbox');

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
                        let cat = tr.children[8].textContent.trim();
                        catCounts[cat] = (catCounts[cat] || 0) + 1;
                    }
                });
                if (exportFilteredBtn) exportFilteredBtn.style.display = total > 0 ? '' : 'none';
                if (deleteBtn) deleteBtn.style.display = document.querySelectorAll('.nomenclature-checkbox:checked').length > 0 ? '' : 'none';
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
                        "Code équipement", "Code article", "Repère équipement", "Désignation équipement", "Fabricant",
                        "Type", "N° série fabricant", "Désignation article", "N° poste", "Quantité", "Unité",
                        "Poste technique", "Métier", "Date création", "Source"
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
                    const exportProgressBar = document.getElementById('exportProgressBar');
                    const progressText = document.getElementById('exportProgressText');
                    const closeBtn = document.getElementById('closeExportModalBtn');
                    exportProgressBar.style.width = "0%";
                    exportProgressBar.textContent = "0%";
                    progressText.textContent = "Préparation de l’export, veuillez patienter...";
                    closeBtn.style.display = "none";
                    closeBtn.disabled = true;
                    exportModal.show();

                    // Animation de progression fictive
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

                        // Création et soumission du formulaire caché
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'request/export_nomenclatures.php?type=excel&filtered=1';
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

                        setTimeout(() => {
                            closeBtn.style.display = "";
                            closeBtn.disabled = false;
                        }, 10000);

                    }, 1200);

                    closeBtn.onclick = function() {
                        exportModal.hide();
                    };
                });
            }

            // Suppression de masse
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const checked = Array.from(document.querySelectorAll('.nomenclature-checkbox:checked'));
                    if (checked.length === 0) return;
                    if (!confirm(`Voulez-vous vraiment supprimer ${checked.length} nomenclature(s) ? Cette action est irréversible.`)) return;
                    const ids = checked.map(cb => cb.value);
                    fetch('request/nomenclature_delete.php', {
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

            // Importation de nomenclatures
            const importForm = document.getElementById('importNomenclatureForm');
            const importBtn = document.getElementById('importBtnNomenclature');
            const importSpinner = document.getElementById('importSpinnerNomenclature');
            const importLog = document.getElementById('importLogNomenclature');
            const dropZone = document.getElementById('dropZoneNomenclature');
            const fileInput = document.getElementById('excel_file_nomenclature');
            const importProgress = document.getElementById('importProgressNomenclature');
            const importProgressBar = importProgress.querySelector('.progress-bar');

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
                    dropZone.querySelector('div.mt-2').textContent = fileInput.files[0].name;
                }
            });

            fileInput.addEventListener('change', function() {
                if (fileInput.files.length) {
                    dropZone.querySelector('div.mt-2').textContent = fileInput.files[0].name;
                } else {
                    dropZone.querySelector('div.mt-2').textContent = "Glissez-déposez votre fichier ici ou cliquez pour sélectionner";
                }
            });

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
                    try {
                        const res = JSON.parse(xhr.responseText);
                        let html = "";
                        if (res.success) {
                            html += `<div class="text-success mb-2"><b>Importation réussie !</b></div>`;
                        } else {
                            html += `<div class="text-danger mb-2"><b>Erreur lors de l'importation.</b></div>`;
                        }
                        if (res.imported) html += `<div>${res.imported} nomenclature(s) importée(s).</div>`;
                        if (res.duplicates && res.duplicates.length)
                            html += `<div class="text-warning">Doublons ignorés :<ul>${res.duplicates.map(d => `<li>${d}</li>`).join('')}</ul></div>`;
                        if (res.errors && res.errors.length) {
                            html += `<div class="text-danger">Lignes non importées :<ul>${
                                res.errors.map((d, i) => `<li>${i+1}. ${d.code_equipement ?? ''} / ${d.code_article ?? ''} - ${d.message ?? d.error ?? ''}</li>`).join('')
                            }</ul></div>`;
                            if (res.residual && res.residual.length) {
                                html += `<button class="btn btn-outline-secondary btn-sm mt-2" id="exportResidualBtn">
                                            <span class="material-icons" style="font-size:1em;vertical-align:middle;">download</span>
                                            Exporter le fichier résiduel
                                        </button>`;
                            }
                        }
                        importLog.innerHTML = html;

                        // Gestion export résiduel
                        setTimeout(() => {
                            const exportBtn = document.getElementById('exportResidualBtn');
                            if (exportBtn) {
                                exportBtn.onclick = function() {
                                    exportResidualFile(res.residual);
                                };
                            }
                        }, 100);
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

            document.getElementById('importNomenclatureModal').addEventListener('show.bs.modal', function() {
                importBtn.disabled = false;
                importBtn.classList.remove('btn-success');
                importBtn.classList.add('btn-primary');
                importBtn.textContent = "Importer";
                importBtn.removeAttribute('data-bs-dismiss');
                importSpinner.classList.add('d-none');
                importProgress.classList.add('d-none');
                importProgressBar.style.width = "0%";
                importProgressBar.textContent = "0%";
                importLog.innerHTML = "";
                dropZone.querySelector('div.mt-2').textContent = "Glissez-déposez votre fichier ici ou cliquez pour sélectionner";
                fileInput.value = "";
            });

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

                    window.location.href = url;

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

            // Ouvre le modal de modification au clic sur une ligne (hors checkbox)
            document.querySelectorAll('table.table tbody tr').forEach(tr => {
                tr.addEventListener('click', function(e) {
                    if (e.target.tagName === 'INPUT' && e.target.type === 'checkbox') return;
                    const tds = tr.querySelectorAll('td');
                    const id = tds[0].querySelector('input').value;
                    fetch('request/nomenclature_get.php?id=' + id)
                        .then(r => r.json())
                        .then(data => {
                            // Génère les champs dynamiquement
                            let html = '';
                            const fields = [{
                                    name: 'code_equipement',
                                    label: 'Code équipement'
                                },
                                {
                                    name: 'code_article',
                                    label: 'Code article'
                                },
                                {
                                    name: 'repere_equipement',
                                    label: 'Repère équipement'
                                },
                                {
                                    name: 'designation_equipement',
                                    label: 'Désignation équipement'
                                },
                                {
                                    name: 'fabricant',
                                    label: 'Fabricant'
                                },
                                {
                                    name: 'type',
                                    label: 'Type'
                                },
                                {
                                    name: 'numero_serie_fabricant',
                                    label: 'N° série fabricant'
                                },
                                {
                                    name: 'designation_article',
                                    label: 'Désignation article'
                                },
                                {
                                    name: 'numero_poste',
                                    label: 'N° poste'
                                },
                                {
                                    name: 'quantite',
                                    label: 'Quantité'
                                },
                                {
                                    name: 'unite',
                                    label: 'Unité'
                                },
                                {
                                    name: 'poste_technique',
                                    label: 'Poste technique'
                                },
                                {
                                    name: 'metier',
                                    label: 'Métier'
                                },
                                {
                                    name: 'date_creation',
                                    label: 'Date création',
                                    type: 'date'
                                },
                                {
                                    name: 'source',
                                    label: 'Source'
                                }
                            ];
                            fields.forEach(f => {
                                let val = data[f.name] ?? '';
                                let type = f.type || 'text';
                                html += `
                                <div class="col-md-6">
                                    <label class="form-label">${f.label}</label>
                                    <input type="${type}" name="${f.name}" value="${type==='date' && val ? val.substr(0,10) : val}" class="form-control">
                                </div>`;
                            });
                            html += `<input type="hidden" name="id" value="${data.id}">`;
                            document.getElementById('editNomenclatureFields').innerHTML = html;
                            document.getElementById('editNomenclatureMsg').innerHTML = '';
                            document.getElementById('editNomenclatureBtn').disabled = false;
                            document.getElementById('editNomenclatureBtn').classList.remove('btn-success');
                            document.getElementById('editNomenclatureBtn').classList.add('btn-primary');
                            document.getElementById('editNomenclatureBtn').textContent = "Enregistrer";
                            document.getElementById('editNomenclatureBtn').removeAttribute('data-bs-dismiss');
                            new bootstrap.Modal(document.getElementById('editNomenclatureModal')).show();
                        });
                });
            });

            const addNomenclatureForm = document.getElementById('addNomenclatureForm');
            const addNomenclatureBtn = document.getElementById('addNomenclatureBtn');
            const addNomenclatureMsg = document.getElementById('addNomenclatureMsg');

            if (addNomenclatureForm) {
                addNomenclatureForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    addNomenclatureBtn.disabled = true;
                    addNomenclatureMsg.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ajout...';

                    const formData = new FormData(addNomenclatureForm);

                    fetch('request/nomenclature_add.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                addNomenclatureMsg.innerHTML = '<span class="text-success">' + res.message + '</span>';
                                addNomenclatureBtn.classList.remove('btn-primary');
                                addNomenclatureBtn.classList.add('btn-success');
                                addNomenclatureBtn.textContent = "Fermer";
                                addNomenclatureBtn.setAttribute('data-bs-dismiss', 'modal');
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                addNomenclatureMsg.innerHTML = '<span class="text-danger">' + res.message + '</span>';
                                addNomenclatureBtn.disabled = false;
                            }
                        })
                        .catch(() => {
                            addNomenclatureMsg.innerHTML = '<span class="text-danger">Erreur réseau.</span>';
                            addNomenclatureBtn.disabled = false;
                        });
                });

                // Réinitialisation du modal à l'ouverture
                document.getElementById('addNomenclatureModal').addEventListener('show.bs.modal', function() {
                    addNomenclatureBtn.disabled = false;
                    addNomenclatureBtn.classList.remove('btn-success');
                    addNomenclatureBtn.classList.add('btn-primary');
                    addNomenclatureBtn.textContent = "Ajouter";
                    addNomenclatureBtn.removeAttribute('data-bs-dismiss');
                    addNomenclatureMsg.innerHTML = "";
                    addNomenclatureForm.reset();
                });
            }

            function exportResidualFile(rows) {
                if (!rows || !rows.length) return;
                // Génère un CSV
                const headers = [
                    "code_equipement", "code_article", "repere_equipement", "designation_equipement", "fabricant",
                    "type", "numero_serie_fabricant", "designation_article", "numero_poste", "quantite", "unite",
                    "poste_technique", "metier", "date_creation", "source"
                ];
                let csv = headers.join(';') + '\n';
                rows.forEach(row => {
                    csv += headers.map(h => `"${(row[h] ?? '').replace(/"/g, '""')}"`).join(';') + '\n';
                });
                // Nom du fichier : residuel_nomenclatures_YYYYMMDD_HHMMSS.csv
                const now = new Date();
                const pad = n => n.toString().padStart(2, '0');
                const fileName = `residuel_nomenclatures_${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.csv`;
                // Téléchargement
                const blob = new Blob([csv], {
                    type: 'text/csv'
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    </script>
</body>

</html>