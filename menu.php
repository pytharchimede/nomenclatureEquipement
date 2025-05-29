<?php

// Détecte la page active pour le menu
$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($pages)
{
    global $currentPage;
    if (is_array($pages)) {
        return in_array($currentPage, $pages) ? 'active' : '';
    }
    return $currentPage === $pages ? 'active' : '';
}
?>
<nav class="sidebar p-3" id="sidebar">
    <div class="mb-4 d-flex align-items-center">
        <span class="material-icons" style="font-size:2rem;color:#1976d2;">dashboard</span>
        <span style="font-size:1.3rem;font-weight:700;color:#1976d2;">Nomenclature</span>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo isActive(['dashboard.php', 'index.php']); ?>" href="dashboard.php">
                <span class="material-icons">home</span>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('equipements.php'); ?>" href="equipements.php">
                <span class="material-icons">build</span>Équipements
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('articles.php'); ?>" href="articles.php">
                <span class="material-icons">widgets</span>Articles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('nomenclatures.php'); ?>" href="nomenclatures.php">
                <span class="material-icons">list_alt</span>Nomenclatures
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive(['export_equipements.php', 'export_articles.php', 'export_nomenclatures.php']); ?>" href="export_equipements.php">
                <span class="material-icons">file_download</span>Exportation
            </a>
        </li>
    </ul>
</nav>