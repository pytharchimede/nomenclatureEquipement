<?php
// Utiliser la configuration de session robuste
require_once __DIR__ . '/includes/session_config.php';

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

// Contrôle d'accès
require_once __DIR__ . '/includes/auth.php';
?>
<style>
    .sidebar {
        width: 280px;
        min-height: 100vh;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
        backdrop-filter: blur(20px);
        box-shadow: 0 15px 45px rgba(0, 0, 0, 0.1);
        border-right: 1px solid rgba(255, 255, 255, 0.3);
        position: relative;
        overflow: hidden;
    }

    .sidebar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .sidebar-brand {
        padding: 2rem 1.5rem;
        border-bottom: 1px solid rgba(229, 231, 235, 0.3);
        margin-bottom: 1rem;
    }

    .brand-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: white;
        font-size: 1.5rem;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    .brand-text {
        font-size: 1.3rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .brand-subtitle {
        color: #6b7280;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .nav-section {
        margin-bottom: 2rem;
    }

    .nav-title {
        color: #9ca3af;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        padding: 0 1.5rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        color: #6b7280;
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 0 25px 25px 0;
        margin: 0.2rem 0;
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: width 0.3s ease;
        opacity: 0.1;
    }

    .nav-link:hover::before {
        width: 100%;
    }

    .nav-link:hover {
        color: #667eea;
        transform: translateX(5px);
    }

    .nav-link.active {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        color: #667eea;
        font-weight: 600;
    }

    .nav-link.active::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .nav-icon {
        margin-right: 1rem;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }

    .nav-link:hover .nav-icon {
        transform: scale(1.1);
    }

    .badge-notification {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        margin-left: auto;
        animation: bounce 2s ease-in-out infinite;
    }

    .badge-warning {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        margin-left: auto;
        animation: bounce 2s ease-in-out infinite;
    }

    .badge-danger {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        margin-left: auto;
        animation: bounce 2s ease-in-out infinite;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-5px);
        }

        60% {
            transform: translateY(-2px);
        }
    }

    .user-profile {
        position: absolute;
        bottom: 1rem;
        left: 1rem;
        right: 1rem;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        margin-right: 0.75rem;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
    }

    .user-role {
        color: #6b7280;
        font-size: 0.75rem;
    }

    .logout-btn {
        color: #f44336;
        border: none;
        background: none;
        padding: 0.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .logout-btn:hover {
        background: rgba(244, 67, 54, 0.1);
        transform: scale(1.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            position: fixed;
            top: 0;
            left: -100%;
            z-index: 1050;
            transition: left 0.3s ease;
        }

        .sidebar.show {
            left: 0;
        }
    }
</style>

<nav class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="d-flex align-items-center">
            <div class="brand-icon">
                <span class="material-icons">precision_manufacturing</span>
            </div>
            <div>
                <div class="brand-text">EquiNomTech</div>
                <div class="brand-subtitle">Gestion moderne</div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="nav-section">
        <div class="nav-title">Principal</div>
        <a href="accueil.php" class="nav-link <?= isActive(['accueil.php', 'index.php']) ?>">
            <span class="material-icons nav-icon">home</span>
            Accueil
        </a>
        <a href="dashboard.php" class="nav-link <?= isActive('dashboard.php') ?>">
            <span class="material-icons nav-icon">dashboard</span>
            Tableau de bord
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-title">Données</div>
        <a href="equipements.php" class="nav-link <?= isActive('equipements.php') ?>">
            <span class="material-icons nav-icon">precision_manufacturing</span>
            Équipements
        </a>
        <a href="articles.php" class="nav-link <?= isActive('articles.php') ?>">
            <span class="material-icons nav-icon">inventory</span>
            Articles
        </a>
        <a href="nomenclatures.php" class="nav-link <?= isActive('nomenclatures.php') ?>">
            <span class="material-icons nav-icon">list_alt</span>
            Nomenclatures
            <?php if ($nbDoublons > 0): ?>
                <span class="badge-notification"><?= $nbDoublons ?></span>
            <?php endif; ?>
        </a>
        <a href="familles.php" class="nav-link <?= isActive('familles.php') ?>">
            <span class="material-icons nav-icon">category</span>
            Familles
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-title">Gestion des problèmes</div>
        <a href="gestion_doublons_nomenclature.php" class="nav-link <?= isActive('gestion_doublons_nomenclature.php') ?>">
            <span class="material-icons nav-icon">content_copy</span>
            Gestion doublons
            <?php if ($nbDoublons > 0): ?>
                <span class="badge-notification"><?= $nbDoublons ?></span>
            <?php endif; ?>
        </a>
        <a href="elements_non_sap.php" class="nav-link <?= isActive('elements_non_sap.php') ?>">
            <span class="material-icons nav-icon">error_outline</span>
            Éléments Non SAP
            <?php
            // Comptage des éléments non SAP
            try {
                $stmt = $pdo->query("
                    SELECT 
                        (SELECT COUNT(DISTINCT e.repere_equipement) 
                         FROM equipements e 
                         WHERE e.repere_equipement NOT IN (
                             SELECT DISTINCT n.repere_equipement 
                             FROM nomenclatures n 
                             WHERE n.source = 'SAP' AND n.repere_equipement IS NOT NULL
                         )) +
                        (SELECT COUNT(DISTINCT a.code_article) 
                         FROM articles a 
                         WHERE a.code_article NOT IN (
                             SELECT DISTINCT n.code_article 
                             FROM nomenclatures n 
                             WHERE n.source = 'SAP' AND n.code_article IS NOT NULL
                         )) as total_non_sap
                ");
                $nbNonSAP = $stmt->fetchColumn();
                if ($nbNonSAP > 0): ?>
                    <span class="badge-danger"><?= $nbNonSAP ?></span>
            <?php endif;
            } catch (Exception $e) {
                // Silencieux en cas d'erreur
            }
            ?>
        </a>
        <a href="validation_doublons_import.php" class="nav-link <?= isActive('validation_doublons_import.php') ?>">
            <span class="material-icons nav-icon">rule</span>
            Validation Import
            <?php
            // Comptage des doublons d'import en attente
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM nomenclatures_doublons_import WHERE statut = 'en_attente'");
                $nbDoublonsImport = $stmt->fetchColumn();
                if ($nbDoublonsImport > 0): ?>
                    <span class="badge-warning"><?= $nbDoublonsImport ?></span>
            <?php endif;
            } catch (Exception $e) {
                // Silencieux si la table n'existe pas encore
            }
            ?>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-title">Outils</div>
        <a href="import_quantitatif.php" class="nav-link <?= isActive('import_quantitatif.php') ?>">
            <span class="material-icons nav-icon">file_upload</span>
            Import quantitatif
        </a>
        <a href="exportations.php" class="nav-link <?= isActive('exportations.php') ?>">
            <span class="material-icons nav-icon">file_download</span>
            Exportations
        </a>
        <a href="rgm_synthese.php" class="nav-link <?= isActive('rgm_synthese.php') ?>">
            <span class="material-icons nav-icon">table_view</span>
            Synthèse RGM
        </a>
        <a href="compilation_template.php" class="nav-link <?= isActive('compilation_template.php') ?>">
            <span class="material-icons nav-icon">table_view</span>
            Compilation Template SPL
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-title">Gestion des accès</div>
        <a href="utilisateurs.php" class="nav-link <?= isActive(['utilisateurs.php', 'utilisateurs_modern.php', 'utilisateurs_simple.php']) ?>">
            <span class="material-icons nav-icon">people</span>
            Utilisateurs
        </a>
        <a href="groupes.php" class="nav-link <?= isActive(['groupes.php', 'groupes_modern.php']) ?>">
            <span class="material-icons nav-icon">groups</span>
            Groupes
        </a>
        <a href="profil.php" class="nav-link <?= isActive(['profil.php', 'profil_modern.php']) ?>">
            <span class="material-icons nav-icon">account_circle</span>
            Mon Profil
        </a>
    </div>

    <!-- Profil utilisateur -->
    <div class="user-profile">
        <div class="d-flex align-items-center">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['user_nom'] ?? 'U', 0, 2)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></div>
                <div class="user-role">Administrateur</div>
            </div>
            <button class="logout-btn" onclick="logout()" title="Déconnexion">
                <span class="material-icons">logout</span>
            </button>
        </div>
    </div>
</nav>

<script>
    // Fonction de déconnexion
    function logout() {
        if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
            window.location.href = 'logout.php';
        }
    }

    // Responsive sidebar toggle
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }
</script>