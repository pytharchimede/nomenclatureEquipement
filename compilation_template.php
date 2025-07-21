<?php
require_once 'includes/auth.php';
require_once 'model/TemplateSPL.php';

// Récupération des lignes SPL compilées
$templates = TemplateSPL::getAll();

// Statistiques pour mini-cards
$totalLignes = count($templates);
$totalArticles = count(array_unique(array_column($templates, 'code_article')));
$totalEquipements = count(array_unique(
    array_merge(...array_map(function ($tpl) {
        return preg_split('/\s*\/\s*/', $tpl['equipement']);
    }, $templates))
));

// Préparation pour affichage (une ligne par repère)
$rows = [];
foreach ($templates as $tpl) {
    $reperes = preg_split('/\s*\/\s*/', $tpl['equipement']);
    foreach ($reperes as $repere) {
        $row = $tpl;
        $row['equipement'] = $repere;
        $rows[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Compilation Template SPL</title>
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
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Compilation Template SPL</h2>
                <div class="d-flex gap-2">
                    <a href="export_template_spl.php?type=excel" class="btn btn-outline-success"><span class="material-icons">file_download</span>Excel</a>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal"><span class="material-icons">upload_file</span>Importer Excel</button>
                    <a href="logout.php" class="btn btn-outline-primary ms-2">
                        <span class="material-icons">logout</span>Déconnexion
                    </a>
                </div>
            </div>
            <!-- Mini Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4 col-6">
                    <div class="mini-card">
                        <span class="material-icons">table_view</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $totalLignes ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Lignes SPL</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#43a047;">category</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $totalArticles ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Articles uniques</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="mini-card">
                        <span class="material-icons" style="color:#1976d2;">build</span>
                        <div>
                            <div style="font-size:1.3rem;font-weight:700;"><?= $totalEquipements ?></div>
                            <div class="text-muted" style="font-size:0.95rem;">Repères uniques</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tableau des lignes SPL -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color:#1976d2;">Lignes SPL compilées</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Code SAP</th>
                                    <th>Code Article</th>
                                    <th>Qte</th>
                                    <th>Désignation Article</th>
                                    <th>Unité</th>
                                    <th>Métier</th>
                                    <th>Repère</th>
                                    <th>Date import</th>
                                </tr>
                                <tr id="filter-row">
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Filtrer"></th>
                                    <th><input type="date" class="form-control form-control-sm"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $tpl): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tpl['numero']) ?></td>
                                        <td><?= htmlspecialchars($tpl['code_sap']) ?></td>
                                        <td><?= htmlspecialchars($tpl['code_article']) ?></td>
                                        <td><?= htmlspecialchars($tpl['quantite']) ?></td>
                                        <td><?= htmlspecialchars($tpl['designation_article']) ?></td>
                                        <td><?= htmlspecialchars($tpl['unite_base']) ?></td>
                                        <td><?= htmlspecialchars($tpl['metier']) ?></td>
                                        <td><?= htmlspecialchars($tpl['equipement']) ?></td>
                                        <td><?= htmlspecialchars($tpl['date_import']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Modal Import SPL -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="excelImportForm" enctype="multipart/form-data" onsubmit="return false;">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importModalLabel">Importer SPL Excel</h5>
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
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script src="js/function_template_SPL.js"></script>
</body>

</html>