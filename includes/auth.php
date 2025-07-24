<?php
// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/DroitUtilisateur.php';

function hasDroit($ressource, $droit)
{
    if (!isset($_SESSION['user'])) return false;
    $groupe_id = $_SESSION['user']['groupe_id'];
    return DroitUtilisateur::hasDroit($groupe_id, $ressource, $droit);
}

// Pour forcer la restriction sur une page
function requireDroit($ressource, $droit)
{
    if (!hasDroit($ressource, $droit)) {
        header('HTTP/1.1 403 Forbidden');
        echo "<div style='padding:2em;text-align:center;color:#d93025;font-size:1.3em;'>Accès refusé</div>";
        exit;
    }
}
