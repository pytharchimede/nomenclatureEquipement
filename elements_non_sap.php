<?php
session_start();
require_once 'includes/auth.php';
require_once 'model/Database.php';

// Mode ultra-simple: calculs côté serveur, pas d'animations/JS
$equipementsNonSAPCount = 0;
$articlesNonSAPCount = 0;
try {
    $pdo = Database::getConnection();

    // Équipements non SAP: repères d'équipements absents de nomenclatures SAP
    $equipementsNonSAPCount = (int)$pdo->query("\n        SELECT COUNT(DISTINCT e.repere_equipement) AS total\n        FROM equipements e\n        WHERE e.repere_equipement IS NOT NULL\n          AND e.repere_equipement NOT IN (\n            SELECT DISTINCT n.repere_equipement\n            FROM nomenclatures n\n            WHERE n.source = 'SAP' AND n.repere_equipement IS NOT NULL\n          )\n    ")->fetchColumn();

    // Articles non SAP: codes article présents mais pas en source SAP
    $articlesNonSAPCount = (int)$pdo->query("\n        SELECT COUNT(DISTINCT n.code_article) AS total\n        FROM nomenclatures n\n        WHERE n.code_article IS NOT NULL\n          AND n.code_article NOT IN (\n            SELECT DISTINCT code_article\n            FROM nomenclatures\n            WHERE source = 'SAP' AND code_article IS NOT NULL\n          )\n    ")->fetchColumn();
} catch (Throwable $e) {
    // Valeurs par défaut à 0 si indisponible
}
$totalNonSAP = $equipementsNonSAPCount + $articlesNonSAPCount;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Éléments Non SAP - Nomenclature Équipements</title>
    <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-inline'; object-src 'none';">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <link href="css/elements_non_sap.css" rel="stylesheet">
    <!-- Minimal override for lightweight rendering -->
    <link href="css/minimal_override.css" rel="stylesheet">
</head>

<body>
    <!-- Barre de progression -->
    <!-- <div class="progress-container" id="progressContainer" style="display: block;">
        <div class="container-fluid">
            <div class="progress" style="height: 6px; background: #f0f0f0;">
                <div class="progress-bar-custom" id="progressBar" style="width: 0%; background: var(--danger-color); transition: width 0.3s ease;"></div>
            </div>
            <div class="progress-text" id="progressText">⚡ Chargement ultra-rapide en cours...</div>
        </div>
    </div> -->

    <div class="d-flex">
        <?php include 'menu.php'; ?>

        <div class="flex-grow-1 content" style="background: transparent; overflow-x: hidden; padding: 1rem;">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div class="mb-3 mb-md-0">
                    <h1 class="mb-1" style="font-weight: 800; color: var(--danger-color); font-size: 2.5rem;">
                        <span class="material-icons me-3" style="font-size: 2.5rem; vertical-align: middle;">error_outline</span>
                        Éléments Non SAP
                    </h1>
                    <p class="mb-0 text-muted" style="font-weight: 500;">Équipements et articles non codifiés dans SAP</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="request/export_equipements_non_sap.php?format=csv" class="btn btn-export btn-sm">
                        <span class="material-icons me-2" style="font-size: 18px;">file_download</span>Exporter Équipements
                    </a>
                    <a href="request/export_articles_non_sap.php?format=csv" class="btn btn-export btn-sm">
                        <span class="material-icons me-2" style="font-size: 18px;">file_download</span>Exporter Articles
                    </a>
                </div>
            </div>

            <!-- Alertes de résumé -->
            <div class="non-sap-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">
                            <span class="material-icons me-2" style="font-size: 2rem; vertical-align: middle;">warning</span>
                            État de la Codification SAP
                        </h3>
                        <p class="mb-0">Éléments nécessitant une codification SAP pour être pleinement intégrés au système</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h2 mb-0"><?= number_format($totalNonSAP) ?></div>
                        <div class="small opacity-75">éléments non codifiés</div>
                    </div>
                </div>
            </div>

            <!-- Statistiques principales (ultra-simples) -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4">
                    <div class="stat-card-non-sap" id="cardEquipements">
                        <div class="h1 text-danger mb-3"><?= number_format($equipementsNonSAPCount) ?></div>
                        <h5 class="mb-2">Équipements Non SAP</h5>
                        <p class="text-muted mb-3">Équipements sans codification SAP</p>
                        <a href="request/export_equipements_non_sap.php?format=csv" class="btn btn-outline-danger btn-sm">
                            <span class="material-icons me-1" style="font-size: 16px;">download</span>Exporter
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="stat-card-non-sap" id="cardArticles">
                        <div class="h1 text-danger mb-3"><?= number_format($articlesNonSAPCount) ?></div>
                        <h5 class="mb-2">Articles Non SAP</h5>
                        <p class="text-muted mb-3">Articles sans codification SAP</p>
                        <a href="request/export_articles_non_sap.php?format=csv" class="btn btn-outline-danger btn-sm">
                            <span class="material-icons me-1" style="font-size: 16px;">download</span>Exporter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aucun JS requis: rendu serveur uniquement pour des performances maximales -->
</body>

</html>