<?php
// Pas de logique PHP ici, uniquement l'affichage des liens d'export
require_once 'includes/auth.php';

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Exportations - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>
        <!-- Main Content -->
        <div class="flex-grow-1 content">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div class="d-flex align-items-center">
                    <span class="menu-toggle material-icons d-lg-none me-2" onclick="toggleSidebar()">menu</span>
                    <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Exportations</h2>
                </div>
                <a href="logout.php" class="btn btn-outline-primary ms-auto">
                    <span class="material-icons">logout</span>Déconnexion
                </a>
            </div>
            <div class="card shadow-sm p-4">
                <div class="row g-4">

                    <!-- Groupe Équipements -->
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <span class="material-icons me-2">build</span>Équipements
                            </div>
                            <div class="card-body d-flex flex-column gap-2">
                                <a href="request/export_equipements.php" class="btn btn-outline-primary">
                                    <span class="material-icons">file_download</span> Exporter la liste des équipements
                                </a>
                                <a href="request/export_equipements_sans_piece.php" class="btn btn-outline-danger">
                                    <span class="material-icons">file_download</span> Équipements sans pièce de rechange
                                </a>
                                <a href="uploads/sample_files/" class="btn btn-outline-secondary">
                                    <span class="material-icons">file_download</span> Fichier exemple import équipements
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Groupe Articles -->
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-success text-white">
                                <span class="material-icons me-2">inventory_2</span>Articles
                            </div>
                            <div class="card-body d-flex flex-column gap-2">
                                <a href="request/export_articles.php" class="btn btn-outline-success">
                                    <span class="material-icons">file_download</span> Exporter la liste des articles
                                </a>
                                <a href="request/export_articles_non_lies.php" class="btn btn-outline-info">
                                    <span class="material-icons">file_download</span> Articles non liés à un équipement
                                </a>
                                <a href="uploads/sample_files/" class="btn btn-outline-secondary">
                                    <span class="material-icons">file_download</span> Fichier exemple import articles
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Groupe Familles & Quantitatif -->
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-warning text-dark">
                                <span class="material-icons me-2">category</span>Familles & Quantitatif
                            </div>
                            <div class="card-body d-flex flex-column gap-2">
                                <a href="request/export_familles.php" class="btn btn-outline-warning">
                                    <span class="material-icons">file_download</span> Exporter la liste des familles
                                </a>
                                <a href="request/export_quantitatif_excel.php" class="btn btn-outline-warning">
                                    <span class="material-icons">file_download</span> Exporter le fichier quantitatif
                                </a>
                                <a href="uploads/sample_files/" class="btn btn-outline-secondary">
                                    <span class="material-icons">file_download</span> Fichier exemple import quantitatif
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Groupe Nomenclatures -->
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-info text-white">
                                <span class="material-icons me-2">list_alt</span>Nomenclatures
                            </div>
                            <div class="card-body d-flex flex-column gap-2">
                                <a href="request/export_nomenclatures.php" class="btn btn-outline-info">
                                    <span class="material-icons">file_download</span> Exporter la liste des nomenclatures
                                </a>
                                <a href="request/export_nomenclatures_doublons.php" class="btn btn-outline-danger">
                                    <span class="material-icons">file_download</span> Exporter les doublons dans nomenclature
                                </a>
                                <a href="uploads/sample_files/" class="btn btn-outline-secondary">
                                    <span class="material-icons">file_download</span> Fichier exemple import nomenclature
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

</html>