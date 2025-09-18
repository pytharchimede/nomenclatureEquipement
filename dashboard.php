<?php
session_start();
require_once 'includes/auth.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'model/Equipement.php';
require_once 'model/Article.php';
require_once 'model/Nomenclature.php';
require_once 'model/Database.php';

// Statistiques avancées
$pdo = Database::getConnection();

// Totaux généraux
$totalEquipements = count(Equipement::getAll());
$totalArticles = count(Article::getAll());
$totalNomenclatures = Nomenclature::countAll();

// Équipements avec/sans nomenclatures
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT e.id) as total
    FROM equipements e
    INNER JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement
");
$equipAvecNomenclature = $stmt->fetchColumn();
$equipSansNomenclature = $totalEquipements - $equipAvecNomenclature;

// Articles codifiés/non codifiés SAP
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT a.id) as total
    FROM articles a
    INNER JOIN nomenclatures n ON a.code_article = n.code_article
    WHERE n.source = 'SAP'
");
$articlesCodesSAP = $stmt->fetchColumn();
$articlesNonCodesSAP = $totalArticles - $articlesCodesSAP;

// Équipements non SAP (repères qui n'apparaissent pas avec source='SAP')
$equipementsNonSAP = (int)$pdo->query("
        SELECT COUNT(DISTINCT e.repere_equipement) AS total
        FROM equipements e
        WHERE e.repere_equipement IS NOT NULL
            AND e.repere_equipement NOT IN (
                SELECT DISTINCT n.repere_equipement
                FROM nomenclatures n
                WHERE n.source = 'SAP' AND n.repere_equipement IS NOT NULL
            )
")->fetchColumn();

// Articles non SAP côté SPL (codes article présents en nomenclatures mais absents en SAP)
$articlesNonSAP_SPL = (int)$pdo->query("
        SELECT COUNT(DISTINCT n.code_article) AS total
        FROM nomenclatures n
        WHERE n.code_article IS NOT NULL
            AND n.code_article NOT IN (
                SELECT DISTINCT code_article
                FROM nomenclatures
                WHERE source = 'SAP' AND code_article IS NOT NULL
            )
")->fetchColumn();

// Répartition par source
$stmt = $pdo->query("
    SELECT 
        source,
        COUNT(DISTINCT repere_equipement) as equipements,
        COUNT(DISTINCT code_article) as articles
    FROM nomenclatures 
    WHERE source IS NOT NULL
    GROUP BY source
");
$repartitionSources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pourcentages
$pourcentEquipAvecNomen = $totalEquipements > 0 ? round($equipAvecNomenclature / $totalEquipements * 100, 1) : 0;
$pourcentArticlesSAP = $totalArticles > 0 ? round($articlesCodesSAP / $totalArticles * 100, 1) : 0;

// Évolution sur 30 jours
$nbAjoutsEquip = Equipement::countAddedLast30Days();
$nbAjoutsArticles = Article::countAddedLast30Days();
$nbAjoutsNomenclatures = Nomenclature::countAddedLast30Days();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Nomenclature Équipements - Vue d'ensemble</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1976d2;
            --secondary-color: #424242;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gradient-primary: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            --gradient-success: linear-gradient(135deg, #4caf50 0%, #43a047 100%);
            --gradient-warning: linear-gradient(135deg, #ff9800 0%, #fb8c00 100%);
            --gradient-danger: linear-gradient(135deg, #f44336 0%, #e53935 100%);
            --gradient-info: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .dashboard-card.success::before {
            background: var(--gradient-success);
        }

        .dashboard-card.warning::before {
            background: var(--gradient-warning);
        }

        .dashboard-card.danger::before {
            background: var(--gradient-danger);
        }

        .dashboard-card.info::before {
            background: var(--gradient-info);
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 1) 100%);
            border-radius: 24px;
            padding: 1.75rem;
            box-shadow: var(--shadow-md);
            border: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            height: 200px;
            /* Hauteur augmentée pour accommoder les grands nombres */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(45deg, transparent 0%, rgba(255, 255, 255, 0.1) 50%, transparent 100%);
            transform: rotate(45deg);
            transition: all 0.6s;
            opacity: 0;
        }

        .stat-card:hover::after {
            opacity: 1;
            right: 150%;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.75rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            word-break: break-all;
            overflow-wrap: break-word;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 60px;
        }

        /* Styles responsive pour optimiser l'affichage */
        @media (max-width: 992px) {
            .stat-card {
                height: 180px;
                padding: 1.5rem;
            }

            .stat-number {
                font-size: 2.2rem;
                min-height: 55px;
            }
        }

        @media (max-width: 768px) {
            .stat-card {
                height: 170px;
                padding: 1.25rem;
            }

            .stat-number {
                font-size: 2rem;
                min-height: 50px;
            }

            .chart-container {
                height: 400px;
                padding: 2.5rem 2rem;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                height: 160px;
                padding: 1rem;
            }

            .stat-number {
                font-size: 1.8rem;
                min-height: 45px;
            }

            .chart-container {
                height: 350px;
                padding: 2rem 1.5rem;
            }
        }

        .stat-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }

        .alert-modern {
            border-radius: 16px;
            border: none;
            box-shadow: var(--shadow-sm);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .alert-modern::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }

        .alert-modern.alert-warning::before {
            background: var(--warning-color);
        }

        .alert-modern.alert-success::before {
            background: var(--success-color);
        }

        .alert-modern.alert-info::before {
            background: var(--info-color);
        }

        .chart-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            padding: 3rem 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: visible;
            height: 450px;
            /* Hauteur fixe pour tous les graphiques avec padding augmenté */
        }

        .chart-container.small {
            height: 350px;
            padding: 2.5rem 2rem;
            /* Hauteur réduite pour petits graphiques avec padding adapté */
        }

        .chart-container.large {
            height: 550px;
            padding: 3.5rem 3rem;
            /* Hauteur augmentée pour graphiques complexes avec padding généreux */
        }

        .chart-container canvas {
            max-height: calc(100% - 4rem) !important;
            width: calc(100% - 1rem) !important;
            height: auto !important;
            margin: 0.5rem;
            /* Espace pour éviter que le graphique touche les bords */
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .chart-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            height: 50px;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
            /* Hauteur fixe pour les titres avec séparateur */
        }

        .progress-modern {
            height: 8px;
            border-radius: 10px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .progress-modern .progress-bar {
            background: var(--gradient-primary);
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }

        .metric-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            height: 120px;
            /* Hauteur fixe pour les cartes métriques */
            display: flex;
            align-items: center;
        }

        .metric-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateX(5px);
        }

        .btn-modern {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Responsive adjustments for charts */
        @media (max-width: 768px) {
            .chart-container {
                height: 350px;
                padding: 2.5rem 2rem;
            }

            .chart-container.small {
                height: 280px;
                padding: 2rem 1.5rem;
            }

            .chart-container canvas {
                max-height: calc(100% - 3.5rem) !important;
                width: calc(100% - 0.75rem) !important;
                margin: 0.375rem;
            }

            .stat-card {
                height: 150px;
                padding: 1.5rem;
            }

            .metric-card {
                height: 100px;
                padding: 1rem;
            }
        }

        @media (max-width: 576px) {
            .chart-container {
                height: 280px;
                padding: 2rem 1.5rem;
            }

            .chart-container.small {
                height: 230px;
                padding: 1.5rem 1rem;
            }

            .chart-container canvas {
                max-height: calc(100% - 3rem) !important;
                width: calc(100% - 0.5rem) !important;
                margin: 0.25rem;
            }

            .stat-card {
                height: 130px;
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 content" style="background: transparent;">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
                <div class="d-flex align-items-center">
                    <span class="menu-toggle material-icons d-lg-none me-3" onclick="toggleSidebar()" style="color: var(--primary-color);">menu</span>
                    <div>
                        <h1 class="mb-1" style="font-weight: 800; color: var(--primary-color); font-size: 2.5rem;">Tableau de Bord</h1>
                        <p class="mb-0 text-muted" style="font-weight: 500;">Vue d'ensemble de la nomenclature des équipements</p>
                    </div>
                </div>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="request/export_equipements.php" class="btn btn-primary btn-modern">
                        <span class="material-icons me-2" style="font-size: 18px;">file_download</span>Équipements
                    </a>
                    <a href="request/export_articles.php" class="btn btn-success btn-modern">
                        <span class="material-icons me-2" style="font-size: 18px;">file_download</span>Articles
                    </a>
                    <a href="elements_non_sap.php" class="btn btn-danger btn-modern">
                        <span class="material-icons me-2" style="font-size: 18px;">error_outline</span>Non SAP
                    </a>
                    <a href="import_quantitatif.php" class="btn btn-info btn-modern">
                        <span class="material-icons me-2" style="font-size: 18px;">analytics</span>Quantitatif
                    </a>
                    <a href="logout.php" class="btn btn-outline-secondary btn-modern">
                        <span class="material-icons me-2" style="font-size: 18px;">logout</span>Déconnexion
                    </a>
                </div>
            </div>

            <!-- Alertes Critiques -->
            <div class="row mb-5">
                <div class="col-md-4">
                    <?php if ($equipSansNomenclature > 0): ?>
                        <div class="alert alert-warning alert-modern d-flex align-items-center" role="alert">
                            <span class="material-icons me-3" style="font-size: 24px;">warning</span>
                            <div>
                                <strong><?= $equipSansNomenclature ?></strong> équipement(s) sans nomenclature
                                <div class="small mt-1">Nécessite une attention immédiate</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success alert-modern d-flex align-items-center" role="alert">
                            <span class="material-icons me-3 text-success" style="font-size: 24px;">check_circle</span>
                            <div>
                                <strong>Parfait !</strong> Tous les équipements ont une nomenclature
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <?php if ($articlesNonCodesSAP > 0): ?>
                        <div class="alert alert-info alert-modern d-flex align-items-center" role="alert">
                            <span class="material-icons me-3" style="font-size: 24px;">info</span>
                            <div>
                                <strong><?= $articlesNonCodesSAP ?></strong> article(s) non codifiés SAP
                                <div class="small mt-1">
                                    <a href="elements_non_sap.php" class="text-decoration-none">Voir détails →</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success alert-modern d-flex align-items-center" role="alert">
                            <span class="material-icons me-3 text-success" style="font-size: 24px;">check_circle</span>
                            <div>
                                <strong>Excellent !</strong> Tous les articles sont codifiés SAP
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <div class="alert alert-info alert-modern d-flex align-items-center" role="alert">
                        <span class="material-icons me-3" style="font-size: 24px;">timeline</span>
                        <div>
                            <strong>+<?= $nbAjoutsEquip + $nbAjoutsArticles ?></strong> éléments ajoutés
                            <div class="small mt-1">Ces 30 derniers jours</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bouton d'accès rapide aux éléments non SAP -->
            <?php if ($equipSansNomenclature > 0 || $articlesNonCodesSAP > 0): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert" style="background: linear-gradient(135deg, #ffebee 0%, #fce4ec 100%); border: 2px solid #f44336; border-radius: 16px;" role="alert">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="material-icons me-3 text-danger" style="font-size: 2rem;">error_outline</span>
                                    <div>
                                        <h5 class="mb-1 text-danger" style="font-weight: 700;">Action Requise - Éléments Non SAP</h5>
                                        <p class="mb-0">
                                            <strong><?= $equipSansNomenclature + $articlesNonCodesSAP ?></strong> éléments nécessitent une attention pour la codification SAP
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <a href="elements_non_sap.php" class="btn btn-danger btn-modern">
                                        <span class="material-icons me-2" style="font-size: 18px;">manage_search</span>
                                        Analyser & Exporter
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Accès rapide — Non SAP & Exports -->
            <div class="row mb-4">
                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                    <div class="metric-card" style="border-left-color: var(--warning-color);">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div>
                                <div class="text-muted small">Équipements sans nomenclature</div>
                                <div class="h3 mb-0" style="font-weight:800; color: var(--warning-color);">
                                    <?= number_format($equipSansNomenclature) ?>
                                </div>
                            </div>
                            <div>
                                <a href="request/export_equipements_sans_nomenclature.php" class="btn btn-outline-warning btn-sm">
                                    <span class="material-icons me-1" style="font-size:16px;">download</span>
                                    Exporter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                    <div class="metric-card" style="border-left-color: var(--danger-color);">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div>
                                <div class="text-muted small">Équipements non SAP</div>
                                <div class="h3 mb-0" style="font-weight:800; color: var(--danger-color);">
                                    <?= number_format($equipementsNonSAP) ?>
                                </div>
                            </div>
                            <div>
                                <a href="request/export_equipements_non_sap.php" class="btn btn-outline-danger btn-sm">
                                    <span class="material-icons me-1" style="font-size:16px;">download</span>
                                    Exporter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-12 mb-3">
                    <div class="metric-card" style="border-left-color: var(--info-color);">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div>
                                <div class="text-muted small">Articles non SAP (SPL)</div>
                                <div class="h3 mb-0" style="font-weight:800; color: var(--info-color);">
                                    <?= number_format($articlesNonSAP_SPL) ?>
                                </div>
                            </div>
                            <div>
                                <a href="request/export_articles_non_sap_spl.php" class="btn btn-outline-info btn-sm">
                                    <span class="material-icons me-1" style="font-size:16px;">download</span>
                                    Exporter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques Principales -->
            <div class="row mb-5">
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="stat-card text-center">
                        <div class="stat-number text-primary"><?= number_format($totalEquipements) ?></div>
                        <div class="stat-label">Équipements</div>
                        <div class="mt-2">
                            <div class="progress-modern">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="stat-card text-center">
                        <div class="stat-number text-success"><?= number_format($totalArticles) ?></div>
                        <div class="stat-label">Articles</div>
                        <div class="mt-2">
                            <div class="progress-modern">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="stat-card text-center">
                        <div class="stat-number text-info"><?= number_format($totalNomenclatures) ?></div>
                        <div class="stat-label">Nomenclatures</div>
                        <div class="mt-2">
                            <div class="progress-modern">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="stat-card text-center">
                        <div class="stat-number text-warning"><?= $pourcentEquipAvecNomen ?>%</div>
                        <div class="stat-label">Équipements avec nomenclature</div>
                        <div class="mt-2">
                            <div class="progress-modern">
                                <div class="progress-bar bg-warning" style="width: <?= $pourcentEquipAvecNomen ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="stat-card text-center">
                        <div class="stat-number text-danger"><?= $pourcentArticlesSAP ?>%</div>
                        <div class="stat-label">Articles codifiés SAP</div>
                        <div class="mt-2">
                            <div class="progress-modern">
                                <div class="progress-bar bg-danger" style="width: <?= $pourcentArticlesSAP ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sources de Données -->
            <div class="row mb-5">
                <?php foreach ($repartitionSources as $source): ?>
                    <div class="col-md-4 mb-3">
                        <div class="metric-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1" style="color: var(--primary-color); font-weight: 700;"><?= $source['source'] ?></h5>
                                    <div class="text-muted small">Source de données</div>
                                </div>
                                <div class="text-end">
                                    <div style="font-size: 1.2rem; font-weight: 600; color: var(--secondary-color);">
                                        <?= $source['equipements'] ?>E / <?= $source['articles'] ?>A
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Graphiques Avancés -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="chart-container">
                        <h5 class="chart-title">
                            <span class="material-icons">family_restroom</span>
                            Familles d'Équipements
                            <small class="text-muted ms-2">(Quantité vs Diversité)</small>
                        </h5>
                        <canvas id="famillesChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="chart-container">
                        <h5 class="chart-title">
                            <span class="material-icons">trending_up</span>
                            Croissance des Équipements
                            <small class="text-muted ms-2">(7 derniers mois)</small>
                        </h5>
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-8 mb-4">
                    <div class="chart-container">
                        <h5 class="chart-title">
                            <span class="material-icons">device_hub</span>
                            Types d'Équipements
                            <small class="text-muted ms-2">(Pompes, Compresseurs, etc.)</small>
                        </h5>
                        <canvas id="typesChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="chart-container small">
                        <h5 class="chart-title">
                            <span class="material-icons">category</span>
                            Métiers des Articles
                            <small class="text-muted ms-2">(Par domaine)</small>
                        </h5>
                        <canvas id="metiersChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Footer Stats -->
            <div class="row mt-4 mb-3">
                <div class="col-12 text-center">
                    <div class="small text-muted" style="font-weight: 500;">
                        Dernière mise à jour: <?= date('d/m/Y à H:i') ?> •
                        +<?= $nbAjoutsEquip ?> équipements, +<?= $nbAjoutsArticles ?> articles sur 30 jours
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
        // Configuration globale pour Chart.js
        Chart.defaults.font.family = 'Inter';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#6c757d';
        Chart.defaults.layout = {
            padding: {
                top: 20,
                right: 20,
                bottom: 30,
                left: 20
            }
        };

        // Couleurs modernes
        const modernColors = [
            '#1976d2', '#43a047', '#ff9800', '#f44336', '#9c27b0',
            '#00bcd4', '#4caf50', '#ff5722', '#795548', '#607d8b',
            '#e91e63', '#3f51b5', '#009688', '#8bc34a', '#ffc107'
        ];

        const gradientColors = modernColors.map(color => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color);
            gradient.addColorStop(1, color + '80');
            return gradient;
        });

        // Responsive sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Animation des compteurs
        function animateCounter(elementClass, finalValue, duration = 2000) {
            const elements = document.querySelectorAll(elementClass);
            elements.forEach(element => {
                let currentValue = 0;
                const increment = finalValue / (duration / 16);

                function updateCounter() {
                    if (currentValue < finalValue) {
                        currentValue += increment;
                        element.textContent = Math.floor(currentValue).toLocaleString();
                        requestAnimationFrame(updateCounter);
                    } else {
                        element.textContent = finalValue.toLocaleString();
                    }
                }
                updateCounter();
            });
        }

        // Chargement des données SIMPLES et PERFORMANTES
        fetch('request/dashboard_stats_simple.php')
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    console.error('Erreur:', result.error);
                    createFallbackCharts();
                    return;
                }

                const data = result.data;
                console.log('Données reçues:', data);
                console.log('Familles:', data.familles);
                console.log('Nombre de familles:', data.familles ? data.familles.length : 0);

                // Vérification si les données familles sont vides
                if (!data.familles || data.familles.length === 0) {
                    console.warn('⚠️ ATTENTION: Aucune donnée de famille reçue!');
                    // Créer des données de test pour le graphique
                    data.familles = [{
                            famille: 'Test Pompes',
                            nb_equipements: 120,
                            nb_articles_differents: 45
                        },
                        {
                            famille: 'Test Compresseurs',
                            nb_equipements: 85,
                            nb_articles_differents: 32
                        },
                        {
                            famille: 'Test Échangeurs',
                            nb_equipements: 95,
                            nb_articles_differents: 38
                        }
                    ];
                    console.log('Utilisation de données de test:', data.familles);
                }

                // 1. FAMILLES : Graphique en barres SIMPLE et CLAIR
                const famillesCtx = document.getElementById('famillesChart').getContext('2d');
                new Chart(famillesCtx, {
                    type: 'bar',
                    data: {
                        labels: data.familles.map(item => item.famille),
                        datasets: [{
                            label: 'Équipements',
                            data: data.familles.map(item => item.nb_equipements),
                            backgroundColor: modernColors[0] + '80',
                            borderColor: modernColors[0],
                            borderWidth: 2,
                            borderRadius: 8,
                            yAxisID: 'y'
                        }, {
                            label: 'Articles différents',
                            data: data.familles.map(item => item.nb_articles_differents),
                            backgroundColor: modernColors[2] + '80',
                            borderColor: modernColors[2],
                            borderWidth: 2,
                            borderRadius: 8,
                            yAxisID: 'y1',
                            type: 'line',
                            tension: 0.4,
                            pointBackgroundColor: modernColors[2],
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 3,
                            pointRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 30,
                                right: 30,
                                bottom: 40,
                                left: 30
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.9)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: '#ffffff',
                                borderWidth: 1,
                                cornerRadius: 8,
                                callbacks: {
                                    afterLabel: function(context) {
                                        const famille = data.familles[context.dataIndex];
                                        if (context.datasetIndex === 0) {
                                            return `Diversité: ${famille.nb_articles_differents} articles`;
                                        }
                                        return `Total: ${famille.nb_equipements} équipements`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    maxRotation: 45,
                                    font: {
                                        weight: '500'
                                    }
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Nombre d\'équipements',
                                    font: {
                                        weight: '600'
                                    }
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Articles différents',
                                    font: {
                                        weight: '600'
                                    }
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });

                // 2. ÉVOLUTION : Ligne claire avec nouveaux vs cumulé
                const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
                new Chart(evolutionCtx, {
                    type: 'line',
                    data: {
                        labels: data.evolution.map(item => item.mois),
                        datasets: [{
                            label: 'Total cumulé',
                            data: data.evolution.map(item => item.cumule),
                            borderColor: modernColors[0],
                            backgroundColor: modernColors[0] + '20',
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: modernColors[0],
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 3,
                            pointRadius: 6,
                            borderWidth: 3
                        }, {
                            label: 'Nouveaux ajouts',
                            data: data.evolution.map(item => item.nouveaux_equipements),
                            borderColor: modernColors[3],
                            backgroundColor: modernColors[3] + '40',
                            tension: 0.3,
                            fill: false,
                            pointBackgroundColor: modernColors[3],
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            borderDash: [8, 4],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 30,
                                right: 30,
                                bottom: 40,
                                left: 30
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.9)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: '#ffffff',
                                borderWidth: 1,
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                },
                                ticks: {
                                    font: {
                                        weight: '500'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                },
                                ticks: {
                                    font: {
                                        weight: '500'
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Nombre d\'équipements',
                                    font: {
                                        weight: '600'
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 2500,
                            easing: 'easeOutQuart'
                        }
                    }
                });

                // 3. TYPES : Graphique en barres horizontales SIMPLE
                const typesCtx = document.getElementById('typesChart').getContext('2d');
                new Chart(typesCtx, {
                    type: 'bar',
                    data: {
                        labels: data.types_equipements.map(item => item.type_equipement),
                        datasets: [{
                            label: 'Nombre d\'équipements',
                            data: data.types_equipements.map(item => item.nombre_equipements),
                            backgroundColor: modernColors.slice(0, data.types_equipements.length).map(color => color + '80'),
                            borderColor: modernColors.slice(0, data.types_equipements.length),
                            borderWidth: 2,
                            borderRadius: 8
                        }, {
                            label: 'Variétés d\'articles',
                            data: data.types_equipements.map(item => item.varietes_articles),
                            backgroundColor: modernColors.slice(1, data.types_equipements.length + 1).map(color => color + '60'),
                            borderColor: modernColors.slice(1, data.types_equipements.length + 1),
                            borderWidth: 2,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 30,
                                right: 30,
                                bottom: 40,
                                left: 30
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.9)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: '#ffffff',
                                borderWidth: 1,
                                cornerRadius: 8,
                                callbacks: {
                                    afterLabel: function(context) {
                                        const item = data.types_equipements[context.dataIndex];
                                        if (context.datasetIndex === 0) {
                                            return `Utilise ${item.varietes_articles} types d'articles`;
                                        } else {
                                            return `Pour ${item.nombre_equipements} équipements`;
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    maxRotation: 45,
                                    font: {
                                        weight: '500'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                },
                                ticks: {
                                    font: {
                                        weight: '500'
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });

                // 4. MÉTIERS : Doughnut simple et clair
                const metiersCtx = document.getElementById('metiersChart').getContext('2d');
                new Chart(metiersCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.metiers.map(item => item.metier),
                        datasets: [{
                            data: data.metiers.map(item => item.nb_articles),
                            backgroundColor: modernColors.slice(0, data.metiers.length).map(color => color + '80'),
                            borderColor: modernColors.slice(0, data.metiers.length),
                            borderWidth: 2,
                            hoverBorderWidth: 4,
                            hoverBorderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 25,
                                right: 25,
                                bottom: 35,
                                left: 25
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: {
                                        size: 11,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.9)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: '#ffffff',
                                borderWidth: 1,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed * 100) / total).toFixed(1);
                                        return `${context.label}: ${context.parsed.toLocaleString()} articles (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 2000
                        }
                    }
                });

            })
            .catch(error => {
                console.error('Erreur lors du chargement des données:', error);
                createFallbackCharts();
            });

        function createFallbackCharts() {
            // Graphiques de base en cas d'erreur
            const typesCtx = document.getElementById('typesChart').getContext('2d');
            new Chart(typesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pompes', 'Vannes', 'Compresseurs', 'Moteurs', 'Autres'],
                    datasets: [{
                        data: [25, 20, 15, 10, 30],
                        backgroundColor: modernColors.slice(0, 5),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        // Animation d'entrée pour les cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .chart-container, .metric-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>

</html>