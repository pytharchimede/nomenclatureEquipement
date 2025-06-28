<?php

require_once 'includes/auth.php';
require_once 'model/Equipement.php';
require_once 'model/Quantitatif.php';
// Récupération des équipements
$equipements = Equipement::getAll();

// Récupération des familles et du nombre d'équipements par famille
$statsFamilles = [];
$nonAffectes = 0;
foreach ($equipements as $eq) {
    $repere = preg_replace('/\s+/', '', $eq['repere_equipement'] ?? '');
    $famille = Quantitatif::getFamilleByRepere($repere);
    if ($famille && $famille !== 'Non défini') {
        if (!isset($statsFamilles[$famille])) $statsFamilles[$famille] = 0;
        $statsFamilles[$famille]++;
    } else {
        $nonAffectes++;
    }
}
$total = count($equipements);
$topFamille = '';
$maxFamille = 0;
if ($statsFamilles) {
    $maxFamille = max($statsFamilles);
    $topFamille = array_search($maxFamille, $statsFamilles);
}
$familleLabels = array_keys($statsFamilles);
$familleData = array_values($statsFamilles);
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
    <link href="css/style_equipements.css" rel="stylesheet">
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
                    <a href="logout.php" class="btn btn-outline-primary ms-2">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
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
                            <div style="font-size:1.3rem;font-weight:700;"><?= htmlspecialchars($topFamille) ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Famille la + présente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <canvas id="miniPie" class="mini-graph"></canvas>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $maxFamille ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Max dans une famille</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#e53935;">help_outline</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $nonAffectes ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Non affectés à une famille</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tableau des équipements -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">Liste des équipements</h5>
                        <div>
                            <button id="exportFilteredBtn" class="btn btn-outline-primary me-2" style="display:none;">
                                <span class="material-icons">file_download</span>Exporter la sélection
                            </button>
                            <button id="deleteSelectedBtn" class="btn btn-outline-danger" style="display:none;">
                                <span class="material-icons">delete</span>Supprimer la sélection
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllEquip"></th>
                                    <th>Code</th>
                                    <th>Désignation</th>
                                    <th>Repère</th>
                                    <th>Fabricant</th>
                                    <th>Type</th>
                                    <th>N° Série</th>
                                    <th>Catégorie</th>
                                    <th>Date création</th>
                                </tr>
                                <tr id="filter-row">
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th>
                                        <select class="form-select form-select-sm">
                                            <option value="">Tous</option>
                                            <?php
                                            $cats = array_unique(array_column($equipements, 'categorie_equipement'));
                                            foreach ($cats as $cat) {
                                                echo '<option value="' . htmlspecialchars($cat) . '">' . htmlspecialchars($cat) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </th>
                                    <th><input type="date" class="form-control form-control-sm"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipements as $eq): ?>
                                    <tr>
                                        <td><input type="checkbox" class="equip-checkbox" value="<?= htmlspecialchars($eq['repere_equipement']) ?>"></td>
                                        <td data-repere="<?= htmlspecialchars($eq['repere_equipement']) ?>"><?= htmlspecialchars($eq['code_equipement']) ?></td>
                                        <td><?= htmlspecialchars($eq['designation_equipement'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eq['repere_equipement'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eq['fabricant'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eq['type_objet'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eq['numero_serie_fabricant'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eq['categorie_equipement'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($eq['date_creation'] ?? '') ?></td>
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
            <!-- Bouton Exporter la sélection -->
            <button id="exportFilteredBtn" class="btn btn-outline-primary">
                <span class="material-icons">file_download</span>Exporter la sélection
            </button>
            <button id="deleteSelectedBtn" class="btn btn-outline-danger" style="display:none;">
                <span class="material-icons">delete</span>Supprimer la sélection
            </button>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="plugins/js/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script src="js/function_equipements.js"></script>
</body>

</html>