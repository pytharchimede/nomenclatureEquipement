<?php
// Pas de logique pour l’instant, juste la structure et le menu
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Familles - Nomenclature Équipements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>
        <!-- Main Content -->
        <div class="flex-grow-1 content d-flex flex-column align-items-center justify-content-center" style="min-height:100vh;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-4" style="width:4rem;height:4rem;" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <h2 class="mb-3" style="font-weight:700;color:#1976d2;">Familles</h2>
                <div class="fs-4 text-muted">Bientôt disponible</div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
</body>

</html>