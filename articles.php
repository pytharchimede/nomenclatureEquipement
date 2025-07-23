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
                    <button class="btn btn-outline-success" id="export-excel-btn" onclick="handleExcelExport()">
                        <span class="material-icons">file_download</span>Excel
                    </button>
                    <button class="btn btn-outline-danger" id="export-pdf-btn" onclick="handlePdfExport()">
                        <span class="material-icons">picture_as_pdf</span>PDF
                    </button>
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
                    <div id="exportProgressText" style="font-size:1.2rem;">Préparation de l'export, veuillez patienter...</div>
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

        // Mini Pie Chart pour les familles
        if (document.getElementById('miniPie')) {
            new Chart(document.getElementById('miniPie').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: <?= json_encode($familleLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($familleData) ?>,
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
        }
    </script>
    <!-- Integration du nouveau système de pagination et filtrage -->
    <script src="js/function_articles_updated.js"></script>
</body>

</html>