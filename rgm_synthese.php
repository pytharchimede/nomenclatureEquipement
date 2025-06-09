<?php

session_start();

require_once 'model/RgmSynthese.php';
require_once 'model/Nomenclature.php';
require_once 'includes/auth.php';

// Synchronisation régulière à chaque chargement de page
//Nomenclature::syncFromRgmSynthese();

$rgmData = RgmSynthese::getAll();

// Utilisation de la classe pour la map des existants
$existMap = Nomenclature::getExistingRepereArticleMap();

// Statistiques pour mini-cards
$total = count($rgmData);

// Par désignation article
$categories = [];
foreach ($rgmData as $row) {
    $cat = $row['designation_article'] ?? 'Non défini';
    $categories[$cat] = ($categories[$cat] ?? 0) + 1;
}
$catLabels = array_keys($categories);
$catData = array_values($categories);
$catMax = $catData ? max($catData) : 0;
$catTop = $catLabels ? $catLabels[array_search($catMax, $catData)] : '';

// Par unité
$unites = [];
foreach ($rgmData as $row) {
    $u = $row['unite'] ?? 'Non défini';
    $unites[$u] = ($unites[$u] ?? 0) + 1;
}
$uniteLabels = array_keys($unites);
$uniteData = array_values($unites);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Synthèse RGM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
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
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Synthèse RGM</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importRgmModal">
                        <span class="material-icons">upload_file</span>Importer Synthèse RGM
                    </button>
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
                            <div class="text-muted" style="font-size:0.95rem;">Total synthèses</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catTop ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Article le + présent</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPieRgm" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catMax ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max pour un article</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPieUniteRgm" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $uniteLabels[0] ?? '' ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Unité la + utilisée</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Bouton Synchroniser RGM -->
            <button id="syncRgmBtn" class="btn btn-outline-success mb-3">
                <span class="material-icons">sync</span>Synchroniser RGM → Nomenclature
            </button>
            <div id="syncRgmProgress" class="mt-2" style="display:none;">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="syncRgmBar" style="width:0%">0%</div>
                </div>
                <div id="syncRgmText" class="mt-1"></div>
            </div>
            <!-- Tableau Synthèse RGM -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">Liste des synthèses RGM</h5>
                        <div>
                            <button id="exportFilteredRgmBtn" class="btn btn-outline-primary me-2" style="display:none;">
                                <span class="material-icons">file_download</span>Exporter la sélection
                            </button>
                            <button id="deleteSelectedRgmBtn" class="btn btn-outline-danger" style="display:none;">
                                <span class="material-icons">delete</span>Supprimer la sélection
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllRgm"></th>
                                    <th>Repère équipement</th>
                                    <th>Code article</th>
                                    <th>Désignation article</th>
                                    <th>Quantité</th>
                                    <th>Unité</th>
                                    <th>Date import</th>
                                    <th>Source</th>
                                    <th>Déjà en nomenclature</th> <!-- Nouvelle colonne -->
                                </tr>
                                <tr id="filter-row-rgm">
                                    <th></th>
                                    <?php for ($i = 1; $i <= 7; $i++): ?>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer" data-col="<?= $i ?>"></th>
                                    <?php endfor; ?>
                                    <th></th> <!-- Pour la nouvelle colonne -->
                                    <th>
                                        <button type="button" id="resetRgmFilters" class="btn btn-sm btn-outline-secondary" title="Réinitialiser les filtres">
                                            <span class="material-icons" style="font-size:1.1em;">close</span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="rgmTableBody">
                                <?php foreach ($rgmData as $row): ?>
                                    <?php
                                    $key = strtolower(trim($row['repere_equipement'] ?? '')) . '|' . strtolower(trim($row['code_article'] ?? ''));
                                    $isPresent = isset($existMap[$key]);
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" class="rgm-checkbox" value="<?= $row['id'] ?>"></td>
                                        <td><?= htmlspecialchars($row['repere_equipement'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['code_article'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['designation_article'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['quantite'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['unite'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['date_import'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['source'] ?? '') ?></td>
                                        <td class="text-center"><?= $isPresent ? '<span style="color:green;font-size:1.3em;">✔️</span>' : '' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center my-3" id="paginationRgm"></div>
                </div>
            </div>
            <!-- Modal Importation Synthèse RGM -->
            <div class="modal fade" id="importRgmModal" tabindex="-1" aria-labelledby="importRgmModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="importRgmForm" enctype="multipart/form-data" method="post" action="request/rgm_import.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importRgmModalLabel">Importer une synthèse RGM (CSV/Excel)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="dropzone border border-primary rounded p-3 text-center mb-2" id="dropzoneRgm">
                                <span class="material-icons" style="font-size:2.5em;color:#1976d2;">cloud_upload</span><br>
                                Glissez-déposez votre fichier ici ou cliquez pour sélectionner
                            </div>
                            <input type="file" name="rgm_file" id="rgm_file" class="form-control d-none" accept=".xlsx,.xls,.csv" required>
                            <div id="importRgmMsg" class="mt-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="importBtnRgm" type="submit">
                                <span class="spinner-border spinner-border-sm d-none" id="importSpinnerRgm"></span>
                                Importer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal export en cours -->
            <div class="modal fade" id="exportLoadingModalRgm" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-center p-4">
                        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                        <div id="exportProgressTextRgm" style="font-size:1.2rem;">Préparation de l’export, veuillez patienter...</div>
                        <div class="progress mt-3" style="height:18px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="exportProgressBarRgm" style="width:0%">0%</div>
                        </div>
                        <button id="closeExportModalBtnRgm" class="btn btn-outline-secondary mt-3" style="display:none;">Fermer</button>
                        <div class="mt-2 text-muted" style="font-size:0.95em;">Le téléchargement va démarrer automatiquement.<br>Si ce n'est pas le cas, cliquez sur "Fermer".</div>
                    </div>
                </div>
            </div>
            <!-- Tableau Synthèse RGM -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">Liste des synthèses RGM</h5>
                        <div>
                            <button id="exportFilteredRgmBtn" class="btn btn-outline-primary me-2" style="display:none;">
                                <span class="material-icons">file_download</span>Exporter la sélection
                            </button>
                            <button id="deleteSelectedRgmBtn" class="btn btn-outline-danger" style="display:none;">
                                <span class="material-icons">delete</span>Supprimer la sélection
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllRgm"></th>
                                    <th>Repère équipement</th>
                                    <th>Code article</th>
                                    <th>Désignation article</th>
                                    <th>Quantité</th>
                                    <th>Unité</th>
                                    <th>Date import</th>
                                    <th>Source</th>
                                    <th>Déjà en nomenclature</th> <!-- Nouvelle colonne -->
                                </tr>
                                <tr id="filter-row-rgm">
                                    <th></th>
                                    <?php for ($i = 1; $i <= 7; $i++): ?>
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer" data-col="<?= $i ?>"></th>
                                    <?php endfor; ?>
                                    <th></th> <!-- Pour la nouvelle colonne -->
                                    <th>
                                        <button type="button" id="resetRgmFilters" class="btn btn-sm btn-outline-secondary" title="Réinitialiser les filtres">
                                            <span class="material-icons" style="font-size:1.1em;">close</span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="rgmTableBody">
                                <?php foreach ($rgmData as $row): ?>
                                    <?php
                                    $key = strtolower(trim($row['repere_equipement'] ?? '')) . '|' . strtolower(trim($row['code_article'] ?? ''));
                                    $isPresent = isset($existMap[$key]);
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" class="rgm-checkbox" value="<?= $row['id'] ?>"></td>
                                        <td><?= htmlspecialchars($row['repere_equipement'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['code_article'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['designation_article'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['quantite'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['unite'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['date_import'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['source'] ?? '') ?></td>
                                        <td class="text-center"><?= $isPresent ? '<span style="color:green;font-size:1.3em;">✔️</span>' : '' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center my-3" id="paginationRgm"></div>
                </div>
            </div>
            <!-- Modal Importation Synthèse RGM -->
            <div class="modal fade" id="importRgmModal" tabindex="-1" aria-labelledby="importRgmModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="importRgmForm" enctype="multipart/form-data" method="post" action="request/rgm_import.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importRgmModalLabel">Importer une synthèse RGM (CSV/Excel)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="dropzone border border-primary rounded p-3 text-center mb-2" id="dropzoneRgm">
                                <span class="material-icons" style="font-size:2.5em;color:#1976d2;">cloud_upload</span><br>
                                Glissez-déposez votre fichier ici ou cliquez pour sélectionner
                            </div>
                            <input type="file" name="rgm_file" id="rgm_file" class="form-control d-none" accept=".xlsx,.xls,.csv" required>
                            <div id="importRgmMsg" class="mt-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="importBtnRgm" type="submit">
                                <span class="spinner-border spinner-border-sm d-none" id="importSpinnerRgm"></span>
                                Importer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal export en cours -->
            <div class="modal fade" id="exportLoadingModalRgm" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-center p-4">
                        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                        <div id="exportProgressTextRgm" style="font-size:1.2rem;">Préparation de l’export, veuillez patienter...</div>
                        <div class="progress mt-3" style="height:18px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="exportProgressBarRgm" style="width:0%">0%</div>
                        </div>
                        <button id="closeExportModalBtnRgm" class="btn btn-outline-secondary mt-3" style="display:none;">Fermer</button>
                        <div class="mt-2 text-muted" style="font-size:0.95em;">Le téléchargement va démarrer automatiquement.<br>Si ce n'est pas le cas, cliquez sur "Fermer".</div>
                    </div>
                </div>
            </div>
            <!-- Bouton Synchroniser RGM -->
            <button id="syncRgmBtn" class="btn btn-outline-success mb-3">
                <span class="material-icons">sync</span>Synchroniser RGM → Nomenclature
            </button>
            <div id="syncRgmProgress" class="mt-2" style="display:none;">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="syncRgmBar" style="width:0%">0%</div>
                </div>
                <div id="syncRgmText" class="mt-1"></div>
            </div>
        </div>
    </div>
    <script src="plugins/js/chart.js"></script>
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Mini Pie Chart Article
        new Chart(document.getElementById('miniPieRgm').getContext('2d'), {
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
        new Chart(document.getElementById('miniPieUniteRgm').getContext('2d'), {
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
            const tbody = document.getElementById('rgmTableBody');
            const filterRow = document.getElementById('filter-row-rgm');
            const filterInputs = filterRow.querySelectorAll('input, select');
            const resetBtn = document.getElementById('resetRgmFilters');
            const exportFilteredBtn = document.getElementById('exportFilteredRgmBtn');
            const deleteBtn = document.getElementById('deleteSelectedRgmBtn');
            const selectAll = document.getElementById('selectAllRgm');
            const checkboxes = document.querySelectorAll('.rgm-checkbox');

            function filterTable() {
                let total = 0;
                tbody.querySelectorAll('tr').forEach(tr => {
                    let show = true;
                    filterInputs.forEach((input, idx) => {
                        let val = input.value.trim().toLowerCase();
                        let colIdx = parseInt(input.getAttribute('data-col'), 10);
                        let cell = tr.children[colIdx];
                        if (!cell) return;
                        if (input.tagName === 'SELECT') {
                            if (val && cell.textContent.trim().toLowerCase() !== val) show = false;
                        } else {
                            if (val && !cell.textContent.toLowerCase().includes(val)) show = false;
                        }
                    });
                    tr.style.display = show ? '' : 'none';
                    if (show) total++;
                });
                if (exportFilteredBtn) exportFilteredBtn.style.display = total > 0 ? '' : 'none';
                if (deleteBtn) deleteBtn.style.display = document.querySelectorAll('.rgm-checkbox:checked').length > 0 ? '' : 'none';
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
                    const columnLabels = [
                        "Repère équipement", "Code article", "Désignation article", "Quantité", "Unité", "Date import", "Source"
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
                    const exportModal = new bootstrap.Modal(document.getElementById('exportLoadingModalRgm'));
                    const exportProgressBar = document.getElementById('exportProgressBarRgm');
                    const progressText = document.getElementById('exportProgressTextRgm');
                    const closeBtn = document.getElementById('closeExportModalBtnRgm');
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
                        form.action = 'request/export_rgm.php?filtered=1';
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
                    const checked = Array.from(document.querySelectorAll('.rgm-checkbox:checked'));
                    if (checked.length === 0) return;
                    if (!confirm(`Voulez-vous vraiment supprimer ${checked.length} ligne(s) ? Cette action est irréversible.`)) return;
                    const ids = checked.map(cb => cb.value);
                    fetch('request/rgm_delete.php', {
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

            // Réinitialisation des filtres
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    filterInputs.forEach(input => input.value = '');
                    filterTable();
                });
            }

            // Importation
            const importForm = document.getElementById('importRgmForm');
            const importBtn = document.getElementById('importBtnRgm');
            const importSpinner = document.getElementById('importSpinnerRgm');
            const fileInput = document.getElementById('rgm_file');
            const importMsg = document.getElementById('importRgmMsg');

            importForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!fileInput.files.length) return;
                importBtn.disabled = true;
                importSpinner.classList.remove('d-none');
                importBtn.textContent = " Importation...";
                importBtn.prepend(importSpinner);
                importMsg.innerHTML = "";

                const formData = new FormData(importForm);

                // Affiche une jauge fictive
                importMsg.innerHTML = `
                    <div class="progress my-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="importProgressBarRgm" style="width:0%">0%</div>
                    </div>
                    <div id="importProgressTextRgm" class="mb-2">Importation en cours...</div>
                `;
                let percent = 0;
                const progressBar = document.getElementById('importProgressBarRgm');
                const progressText = document.getElementById('importProgressTextRgm');
                const interval = setInterval(() => {
                    percent += Math.random() * 10 + 5;
                    if (percent > 90) percent = 90;
                    progressBar.style.width = percent + "%";
                    progressBar.textContent = Math.round(percent) + "%";
                }, 200);

                fetch('request/rgm_import.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        clearInterval(interval);
                        progressBar.style.width = "100%";
                        progressBar.textContent = "100%";
                        progressText.textContent = "Import terminé.";

                        let html = `<div class="alert alert-success my-2">Importation terminée.<br>
                        <b>${res.imported}</b> lignes importées.<br>
                        <b>${res.duplicates.length}</b> doublons ignorés.<br>
                        <b>${res.errors.length}</b> erreurs.<br>`;
                        if (res.residualFile) {
                            html += `<a href="${res.residualFile}" class="btn btn-warning btn-sm mt-2" download>Télécharger le fichier résiduel</a>`;
                        }
                        html += `</div>`;

                        if (res.duplicates.length > 0) {
                            html += `<details class="mt-2"><summary>Voir les doublons</summary><ul>`;
                            res.duplicates.forEach(d => html += `<li>${d.message} (ligne ${d.ligne ?? ''})</li>`);
                            html += `</ul></details>`;
                        }
                        if (res.errors.length > 0) {
                            html += `<details class="mt-2"><summary>Voir les erreurs</summary><ul>`;
                            res.errors.forEach(e => html += `<li>${e.message} (ligne ${e.ligne ?? ''})</li>`);
                            html += `</ul></details>`;
                        }
                        importMsg.innerHTML = html;
                        importBtn.disabled = false;
                        importBtn.textContent = "Importer";
                        importSpinner.classList.add('d-none');
                    })
                    .catch(() => {
                        clearInterval(interval);
                        importMsg.innerHTML = '<div class="alert alert-danger">Erreur lors de l\'importation.</div>';
                        importBtn.disabled = false;
                        importBtn.textContent = "Importer";
                        importSpinner.classList.add('d-none');
                    });
            });

            document.getElementById('importRgmModal').addEventListener('show.bs.modal', function() {
                importBtn.disabled = false;
                importBtn.classList.remove('btn-success');
                importBtn.classList.add('btn-primary');
                importBtn.textContent = "Importer";
                importBtn.removeAttribute('data-bs-dismiss');
                importSpinner.classList.add('d-none');
                fileInput.value = "";
            });

            const dropzone = document.getElementById('dropzoneRgm');

            dropzone.addEventListener('click', () => fileInput.click());
            dropzone.addEventListener('dragover', e => {
                e.preventDefault();
                dropzone.classList.add('bg-light');
            });
            dropzone.addEventListener('dragleave', e => {
                e.preventDefault();
                dropzone.classList.remove('bg-light');
            });
            dropzone.addEventListener('drop', e => {
                e.preventDefault();
                dropzone.classList.remove('bg-light');
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                }
            });
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length) {
                    dropzone.innerHTML = `<span class="material-icons" style="font-size:2.5em;color:#1976d2;">cloud_done</span><br>${fileInput.files[0].name}`;
                }
            });

            // Synchronisation RGM → Nomenclature
            const syncBtn = document.getElementById('syncRgmBtn');
            const syncProgress = document.getElementById('syncRgmProgress');
            const syncBar = document.getElementById('syncRgmBar');
            const syncText = document.getElementById('syncRgmText');

            if (syncBtn) {
                syncBtn.addEventListener('click', function() {
                    const progressDiv = document.getElementById('syncRgmProgress');
                    const bar = document.getElementById('syncRgmBar');
                    const text = document.getElementById('syncRgmText');
                    progressDiv.style.display = '';
                    bar.style.width = '0%';
                    bar.textContent = '0%';
                    text.textContent = 'Synchronisation en cours...';

                    // Animation fictive de la jauge
                    let percent = 0;
                    const interval = setInterval(() => {
                        percent += Math.random() * 10 + 5;
                        if (percent > 90) percent = 90;
                        bar.style.width = percent + "%";
                        bar.textContent = Math.round(percent) + "%";
                    }, 200);

                    fetch('request/sync_rgm.php')
                        .then(r => r.json())
                        .then(res => {
                            clearInterval(interval);
                            bar.style.width = "100%";
                            bar.textContent = "100%";
                            if (res.success) {
                                text.innerHTML = `<span class="text-success">Synchronisation terminée : <b>${res.inserted}</b> lignes ajoutées.</span>`;
                            } else {
                                text.innerHTML = `<span class="text-danger">Erreur lors de la synchronisation.</span>`;
                            }
                        })
                        .catch(() => {
                            clearInterval(interval);
                            text.innerHTML = `<span class="text-danger">Erreur réseau.</span>`;
                        });
                });
            }
        });
    </script>
</body>

</html>