<?php

// Détecte la page active pour le menu
$currentPage = basename($_SERVER['PHP_SELF']);
if (!function_exists('isActive')) {
    function isActive($pages)
    {
        global $currentPage;
        if (is_array($pages)) {
            return in_array($currentPage, $pages) ? 'active' : '';
        }
        return $currentPage === $pages ? 'active' : '';
    }
}

// Détection des doublons nomenclature
require_once __DIR__ . '/model/Nomenclature.php';
require_once __DIR__ . '/model/Database.php';

// Connexion pour les compteurs
$pdo = Database::getConnection();

$nomenclatures = Nomenclature::getAll();
$dups = [];
$seen = [];
foreach ($nomenclatures as $nom) {
    $key = ($nom['code_equipement'] ?? '') . '|' . ($nom['code_article'] ?? '');
    if (!$nom['code_equipement'] || !$nom['code_article']) continue;
    if (isset($seen[$key])) {
        $dups[$key][] = $nom;
    } else {
        $seen[$key] = $nom;
    }
}
foreach ($seen as $key => $first) {
    if (isset($dups[$key])) {
        array_unshift($dups[$key], $first);
    }
}
$nbDoublons = count($dups);

// Ajout du contrôle d'accès
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/includes/auth.php';
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
                <span class="material-icons">list_alt</span>Nomenclatur
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('exportations.php'); ?>" href="exportations.php">
                <span class="material-icons">file_download</span>Exportation
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('gestion_doublons_nomenclature.php'); ?>" href="gestion_doublons_nomenclature.php">
                <span class="material-icons">warning</span>
                Doublons
                <?php if ($nbDoublons > 0): ?>
                    <span class="badge bg-danger ms-2"><?= $nbDoublons ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('validation_doublons_import.php'); ?>" href="validation_doublons_import.php">
                <span class="material-icons">rule</span>
                Validation Import
                <?php
                // Comptage des doublons d'import en attente
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM nomenclatures_doublons_import WHERE statut = 'en_attente'");
                    $nbDoublonsImport = $stmt->fetchColumn();
                    if ($nbDoublonsImport > 0): ?>
                        <span class="badge bg-warning ms-2"><?= $nbDoublonsImport ?></span>
                <?php endif;
                } catch (Exception $e) {
                    // Silencieux si la table n'existe pas encore
                }
                ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('familles.php'); ?>" href="familles.php">
                <span class="material-icons">category</span>
                Familles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('import_quantitatif.php'); ?>" href="import_quantitatif.php">
                <span class="material-icons">upload_file</span>
                Quantitatif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('utilisateurs.php'); ?>" href="utilisateurs.php">
                <span class="material-icons">people</span>
                Utilisateurs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('groupes.php'); ?>" href="groupes.php">
                <span class="material-icons">groups</span>
                Groupes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('profil.php'); ?>" href="profil.php">
                <span class="material-icons">account_circle</span>
                Mon profil
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('rgm_synthese.php'); ?>" href="rgm_synthese.php">
                <span class="material-icons">table_view</span>
                Synthèse RGM
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActive('compilation_template.php'); ?>" href="compilation_template.php">
                <span class="material-icons">table_view</span>
                Compilation Template SPL
            </a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger fw-bold" href="logout.php" style="background:#fff0f0;">
                <span class="material-icons">logout</span>
                Déconnexion
            </a>
        </li>
    </ul>
</nav>