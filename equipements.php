<?php

require_once 'model/Equipement.php';
// Récupération des équipements
$equipements = Equipement::getAll();

// Données de test pour les mini-graphes et cards
$total = count($equipements);
$categories = ['Pompes', 'Vannes', 'Tableaux', 'Moteurs', 'Autres'];
$catData = [12, 8, 5, 3, 2]; // À remplacer par vos vraies stats
$catAssoc = array_combine($categories, $catData);
$catMax = max($catData);
$catTop = $categories[array_search($catMax, $catData)];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .card {
            border-radius: 16px;
        }

        .modal-content {
            border-radius: 16px;
        }

        .table thead {
            background: #f5f5f5;
        }

        .form-label {
            font-weight: 500;
        }

        .mini-card {
            min-width: 180px;
            border-radius: 12px;
            box-shadow: 0 2px 8px #eee;
            background: #fff;
            padding: 1rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .mini-card .material-icons {
            font-size: 2.2rem;
            color: #1976d2;
        }

        .mini-graph {
            width: 120px !important;
            height: 60px !important;
        }

        @media (max-width: 991px) {
            .mini-card {
                min-width: 120px;
                padding: 0.7rem 0.5rem;
            }

            .mini-graph {
                width: 80px !important;
                height: 40px !important;
            }
        }

        #drop-area {
            border: 2px dashed #1976d2;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #f8fafd;
            cursor: pointer;
        }

        #drop-area.dragover {
            background: #e3f2fd;
            border-color: #1976d2;
        }

        .progress {
            height: 22px;
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
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Équipements</h2>
                <div class="d-flex gap-2">
                    <a href="request/export_equipements.php?type=excel" class="btn btn-outline-success"><span class="material-icons">file_download</span>Excel</a>
                    <a href="request/export_equipements.php?type=pdf" class="btn btn-outline-danger"><span class="material-icons">picture_as_pdf</span>PDF</a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipModal"><span class="material-icons">add</span>Ajouter</button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal"><span class="material-icons">upload_file</span>Importer Excel</button>
                </div>
            </div>
            <!-- Mini Cards et Graphes -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons">build</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $total ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Total équipements</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catTop ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Catégorie la + présente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPie" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catMax ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max dans une catégorie</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniBar" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $catData[0] ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Pompes</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tableau des équipements -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="color:#1976d2;">Liste des équipements</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Désignation</th>
                                    <th>Repère</th>
                                    <th>Fabricant</th>
                                    <th>Type</th>
                                    <th>N° Série</th>
                                    <th>Catégorie</th>
                                    <th>Date création</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipements as $eq): ?>
                                    <tr>
                                        <td data-id="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['code_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['designation_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['repere_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['fabricant']) ?></td>
                                        <td><?= htmlspecialchars($eq['type_objet']) ?></td>
                                        <td><?= htmlspecialchars($eq['numero_serie_fabricant']) ?></td>
                                        <td><?= htmlspecialchars($eq['categorie_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['date_creation']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Modal Ajout -->
            <div class="modal fade" id="addEquipModal" tabindex="-1" aria-labelledby="addEquipModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="post" action="equipement_add.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addEquipModalLabel">Ajouter un équipement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Code équipement</label>
                                <input type="text" name="code_equipement" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Désignation</label>
                                <input type="text" name="designation_equipement" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Repère</label>
                                <input type="text" name="repere_equipement" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fabricant</label>
                                <input type="text" name="fabricant" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type d'objet</label>
                                <input type="text" name="type_objet" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Désignation type</label>
                                <input type="text" name="designation_type" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">N° série fabricant</label>
                                <input type="text" name="numero_serie_fabricant" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">N° pièce fabricant</label>
                                <input type="text" name="numero_piece_fabricant" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poste technique</label>
                                <input type="text" name="poste_technique" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Désignation poste technique</label>
                                <input type="text" name="designation_poste_technique" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poste travail principal</label>
                                <input type="text" name="poste_travail_principal" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catégorie équipement</label>
                                <input type="text" name="categorie_equipement" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Centre de coûts</label>
                                <input type="text" name="centre_de_couts" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date création</label>
                                <input type="date" name="date_creation" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal Import amélioré -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="excelImportForm" enctype="multipart/form-data" onsubmit="return false;">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importModalLabel">Importer depuis Excel</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="drop-area" class="border border-2 border-primary rounded-3 p-4 text-center mb-3" style="cursor:pointer; background:#f8fafd;">
                                <span class="material-icons" style="font-size:2.5rem;color:#1976d2;">upload_file</span>
                                <p class="mb-1">Glissez-déposez votre fichier Excel ici<br><span class="text-muted" style="font-size:0.95em;">(ou cliquez pour sélectionner)</span></p>
                                <input type="file" id="excelFileInput" name="excel_file" accept=".xls,.xlsx" style="display:none;" required>
                                <div id="fileName" class="text-success mt-2"></div>
                            </div>
                            <div class="progress mb-2" style="height: 22px; display:none;" id="importProgressBarContainer">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="importProgressBar" style="width:0%">0%</div>
                            </div>
                            <div id="importResult" class="mt-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="startImportBtn" disabled>Importer</button>
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
        // Responsive sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Mini Pie Chart
        new Chart(document.getElementById('miniPie').getContext('2d'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($categories) ?>,
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

        // Mini Bar Chart
        new Chart(document.getElementById('miniBar').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($categories) ?>,
                datasets: [{
                    data: <?= json_encode($catData) ?>,
                    backgroundColor: [
                        '#1976d2', '#43a047', '#fbc02d', '#e53935', '#8e24aa'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                responsive: false,
                scales: {
                    y: {
                        display: false
                    },
                    x: {
                        display: false
                    }
                }
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropArea = document.getElementById('drop-area');
            const fileInput = document.getElementById('excelFileInput');
            const fileName = document.getElementById('fileName');
            const startImportBtn = document.getElementById('startImportBtn');
            let selectedFile = null;

            // Drag & drop
            dropArea.addEventListener('click', () => fileInput.click());
            dropArea.addEventListener('dragover', e => {
                e.preventDefault();
                dropArea.classList.add('dragover');
            });
            dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));
            dropArea.addEventListener('drop', e => {
                e.preventDefault();
                dropArea.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    handleFileChange();
                }
            });
            fileInput.addEventListener('change', handleFileChange);

            function handleFileChange() {
                if (fileInput.files.length) {
                    selectedFile = fileInput.files[0];
                    fileName.textContent = selectedFile.name;
                    startImportBtn.disabled = false;
                } else {
                    fileName.textContent = '';
                    startImportBtn.disabled = true;
                }
            }

            // Import AJAX
            startImportBtn.addEventListener('click', function() {
                if (!selectedFile) return;
                startImportBtn.disabled = true;
                startImportBtn.textContent = "Import en cours...";
                document.getElementById('importResult').innerHTML = '';
                const progressBar = document.getElementById('importProgressBar');
                const progressBarContainer = document.getElementById('importProgressBarContainer');
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
                progressBarContainer.style.display = 'block';

                // Affichage du log
                let logDiv = document.getElementById('importLog');
                if (!logDiv) {
                    logDiv = document.createElement('div');
                    logDiv.id = 'importLog';
                    logDiv.style.maxHeight = '180px';
                    logDiv.style.overflowY = 'auto';
                    logDiv.className = 'mt-2 small';
                    document.getElementById('importResult').appendChild(logDiv);
                } else {
                    logDiv.innerHTML = '';
                }

                // Envoi du fichier en AJAX
                const formData = new FormData();
                formData.append('excel_file', selectedFile);

                fetch('request/equipement_import.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        // Affichage du log détaillé
                        if (res.log && Array.isArray(res.log)) {
                            logDiv.innerHTML = '';
                            res.log.forEach((ligne, idx) => {
                                logDiv.innerHTML += `<div${ligne.enCours ? ' style="background:#e3f2fd;"' : ''}>${ligne.message}</div>`;
                            });
                        }
                        progressBar.style.width = '100%';
                        progressBar.textContent = '100%';
                        if (res.success) {
                            document.getElementById('importResult').innerHTML += '<div class="alert alert-success mt-2">' + res.message + '</div>';
                        } else {
                            document.getElementById('importResult').innerHTML += '<div class="alert alert-danger mt-2">' + res.message + '</div>';
                        }
                        // Bouton devient "Fermer"
                        startImportBtn.textContent = "Fermer";
                        startImportBtn.disabled = false;
                        startImportBtn.onclick = function() {
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('importModal')).hide();
                            location.reload(); // <-- Ajoute ceci pour rafraîchir la page
                        };
                        fileInput.value = '';
                        fileName.textContent = '';
                    })
                    .catch(() => {
                        document.getElementById('importResult').innerHTML = '<div class="alert alert-danger">Erreur réseau.</div>';
                        startImportBtn.textContent = "Fermer";
                        startImportBtn.disabled = false;
                        startImportBtn.onclick = function() {
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('importModal')).hide();
                            startImportBtn.textContent = "Importer";
                            startImportBtn.disabled = true;
                        };
                    });
            });

            // Formulaire d'ajout AJAX
            const addForm = document.querySelector('#addEquipModal form');
            const addBtn = addForm.querySelector('button[type="submit"]');
            const addResult = document.createElement('div');
            addResult.className = "w-100 mt-2";
            addForm.querySelector('.modal-footer').prepend(addResult);

            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                addBtn.disabled = true;
                addResult.innerHTML = '';
                const formData = new FormData(addForm);

                fetch('request/equipement_add.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            addResult.innerHTML = '<div class="alert alert-success">' + res.message + '</div>';
                            addForm.reset();
                            // Optionnel : rafraîchir la page ou le tableau dynamiquement ici
                            setTimeout(() => {
                                location.reload();
                            }, 1200);
                        } else {
                            addResult.innerHTML = '<div class="alert alert-danger">' + res.message + '</div>';
                        }
                    })
                    .catch(() => {
                        addResult.innerHTML = '<div class="alert alert-danger">Erreur réseau.</div>';
                    })
                    .finally(() => {
                        addBtn.disabled = false;
                    });
            });

            // 1. Génère dynamiquement le modal de modification
            function showEditModal(equipement) {
                // Crée le modal si pas déjà présent
                let editModal = document.getElementById('editEquipModal');
                if (!editModal) {
                    editModal = document.createElement('div');
                    editModal.className = 'modal fade';
                    editModal.id = 'editEquipModal';
                    editModal.tabIndex = -1;
                    editModal.innerHTML = `
                    <div class="modal-dialog modal-lg">
                        <form class="modal-content" id="editEquipForm">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier l'équipement</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body row g-3">
                                <!-- Les champs seront injectés ici -->
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>`;
                    document.body.appendChild(editModal);
                }

                // Génère les champs du formulaire avec valeurs pré-remplies
                const fields = [{
                        name: 'code_equipement',
                        label: 'Code équipement',
                        required: true
                    },
                    {
                        name: 'designation_equipement',
                        label: 'Désignation'
                    },
                    {
                        name: 'repere_equipement',
                        label: 'Repère'
                    },
                    {
                        name: 'fabricant',
                        label: 'Fabricant'
                    },
                    {
                        name: 'type_objet',
                        label: "Type d'objet"
                    },
                    {
                        name: 'designation_type',
                        label: 'Désignation type'
                    },
                    {
                        name: 'numero_serie_fabricant',
                        label: 'N° série fabricant'
                    },
                    {
                        name: 'numero_piece_fabricant',
                        label: 'N° pièce fabricant'
                    },
                    {
                        name: 'poste_technique',
                        label: 'Poste technique'
                    },
                    {
                        name: 'designation_poste_technique',
                        label: 'Désignation poste technique'
                    },
                    {
                        name: 'poste_travail_principal',
                        label: 'Poste travail principal'
                    },
                    {
                        name: 'categorie_equipement',
                        label: 'Catégorie équipement'
                    },
                    {
                        name: 'centre_de_couts',
                        label: 'Centre de coûts'
                    },
                    {
                        name: 'date_creation',
                        label: 'Date création',
                        type: 'date'
                    }
                ];
                let html = '';
                fields.forEach(f => {
                    let value = equipement[f.name] ?? '';
                    let type = f.type || 'text';
                    let underline = value.trim() === '' ? 'border-bottom border-2 border-danger' : 'border-success';
                    html += `
                    <div class="col-md-6">
                        <label class="form-label">${f.label}</label>
                        <input type="${type}" name="${f.name}" class="form-control ${underline}" value="${type==='date' && value ? value.substr(0,10) : value}">
                    </div>`;
                });
                editModal.querySelector('.modal-body').innerHTML = html + `<input type="hidden" name="id" value="${equipement.id}">`;

                // Ajoute la gestion AJAX du formulaire de modification
                const editForm = editModal.querySelector('form');
                let editResult = editModal.querySelector('.edit-result');
                if (!editResult) {
                    editResult = document.createElement('div');
                    editResult.className = "w-100 mt-2 edit-result";
                    editModal.querySelector('.modal-footer').prepend(editResult);
                }
                editForm.onsubmit = function(e) {
                    e.preventDefault();
                    editResult.innerHTML = '';
                    const formData = new FormData(editForm);
                    fetch('request/equipement_edit.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                editResult.innerHTML = '<div class="alert alert-success">' + res.message + '</div>';
                                setTimeout(() => {
                                    location.reload();
                                }, 1200);
                            } else {
                                editResult.innerHTML = '<div class="alert alert-danger">' + res.message + '</div>';
                            }
                        })
                        .catch(() => {
                            editResult.innerHTML = '<div class="alert alert-danger">Erreur réseau.</div>';
                        });
                };

                // Affiche le modal
                let modal = bootstrap.Modal.getOrCreateInstance(editModal);
                modal.show();

                // Validation des champs
                setTimeout(() => {
                    editModal.querySelectorAll('input.form-control').forEach(input => {
                        // Ajoute la validation dynamique
                        input.addEventListener('input', function() {
                            updateFieldValidation(this);
                        });
                        // Validation initiale (pour cocher vert les champs déjà remplis)
                        updateFieldValidation(input);
                    });
                }, 50);
            }

            // 2. Ajoute l'écouteur sur chaque ligne du tableau
            document.querySelectorAll('table.table tbody tr').forEach(tr => {
                tr.style.cursor = 'pointer';
                tr.addEventListener('click', function() {
                    // Récupère les données de la ligne
                    const tds = this.querySelectorAll('td');
                    const equipement = {
                        id: tds[0].dataset.id ?? '', // à adapter si tu as un champ id caché
                        code_equipement: tds[0].textContent.trim(),
                        designation_equipement: tds[1].textContent.trim(),
                        repere_equipement: tds[2].textContent.trim(),
                        fabricant: tds[3].textContent.trim(),
                        type_objet: tds[4].textContent.trim(),
                        numero_serie_fabricant: tds[5].textContent.trim(),
                        categorie_equipement: tds[6].textContent.trim(),
                        date_creation: tds[7].textContent.trim()
                        // Ajoute les autres champs si besoin
                    };
                    showEditModal(equipement);
                });
            });
        });

        // Fonction utilitaire pour la validation des champs
        function updateFieldValidation(input) {
            if (input.value.trim() === '') {
                input.classList.remove('border-success');
                input.classList.add('border-bottom', 'border-2', 'border-danger');
            } else {
                input.classList.remove('border-danger', 'border-bottom', 'border-2');
                input.classList.add('border-success');
            }
        }
    </script>
    <!-- À placer juste avant </body> -->
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function showExportLoader() {
                const modal = new bootstrap.Modal(document.getElementById('exportLoadingModal'));
                modal.show();

                let progress = 0;
                const progressBar = document.getElementById('exportProgressBar');
                const progressText = document.getElementById('exportProgressText');
                const closeBtn = document.getElementById('closeExportModalBtn');
                closeBtn.style.display = 'none';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
                progressText.textContent = "Préparation de l’export, veuillez patienter...";

                const interval = setInterval(() => {
                    progress += Math.floor(Math.random() * 10) + 5;
                    if (progress > 100) progress = 100;
                    progressBar.style.width = progress + '%';
                    progressBar.textContent = progress + '%';
                    if (progress >= 100) {
                        clearInterval(interval);
                        progressText.textContent = "Téléchargement en cours...";
                    }
                }, 400);

                // Affiche le bouton "Fermer" après 10 secondes
                setTimeout(() => {
                    closeBtn.style.display = '';
                }, 10000);

                closeBtn.onclick = function() {
                    modal.hide();
                };
            }

            document.querySelector('a[href*="export_equipements.php?type=excel"]').addEventListener('click', function(e) {
                showExportLoader();
            });
            document.querySelector('a[href*="export_equipements.php?type=pdf"]').addEventListener('click', function(e) {
                showExportLoader();
            });
        });
    </script>
</body>

</html>