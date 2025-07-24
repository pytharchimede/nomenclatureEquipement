<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$repere = $_GET['repere'] ?? '';
$codeArticle = $_GET['code_article'] ?? '';

if (empty($repere) || empty($codeArticle)) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

try {
    $db = Database::getConnection();

    // Récupérer tous les doublons pour ce repère/code article
    $stmtDoublons = $db->prepare("
        SELECT 
            id,
            repere_equipement,
            code_article,
            designation_equipement,
            designation_article,
            fabricant,
            type,
            quantite,
            unite,
            numero_poste,
            metier,
            source,
            fichier_import,
            ligne_import,
            raison_rejet,
            details_conflit,
            statut,
            commentaire_validation,
            valide_par,
            date_import,
            date_validation
        FROM nomenclatures_doublons_import 
        WHERE repere_equipement = ? AND code_article = ?
        ORDER BY date_import DESC, ligne_import ASC
    ");

    $stmtDoublons->execute([$repere, $codeArticle]);
    $doublons = $stmtDoublons->fetchAll(PDO::FETCH_ASSOC);

    // Formater les dates
    foreach ($doublons as &$doublon) {
        if ($doublon['date_import']) {
            $doublon['date_import'] = date('d/m/Y H:i', strtotime($doublon['date_import']));
        }
        if ($doublon['date_validation']) {
            $doublon['date_validation'] = date('d/m/Y H:i', strtotime($doublon['date_validation']));
        }
    }

    // Récupérer la nomenclature existante en base de données (si elle existe)
    $stmtNomenclature = $db->prepare("
        SELECT 
            id,
            repere_equipement,
            code_article,
            designation_equipement,
            designation_article,
            fabricant,
            type,
            quantite,
            unite,
            source,
            date_creation
        FROM nomenclatures 
        WHERE repere_equipement = ? AND code_article = ?
        LIMIT 1
    ");

    $stmtNomenclature->execute([$repere, $codeArticle]);
    $nomenclatureExistante = $stmtNomenclature->fetch(PDO::FETCH_ASSOC);

    // Formater les dates de la nomenclature
    if ($nomenclatureExistante) {
        if ($nomenclatureExistante['date_creation']) {
            $nomenclatureExistante['date_creation'] = date('d/m/Y H:i', strtotime($nomenclatureExistante['date_creation']));
        }
    }

    echo json_encode([
        'success' => true,
        'doublons' => $doublons,
        'nomenclature_existante' => $nomenclatureExistante,
        'repere' => $repere,
        'code_article' => $codeArticle,
        'total_doublons' => count($doublons),
        'doublons_en_attente' => count(array_filter($doublons, function ($d) {
            return $d['statut'] === 'en_attente';
        })),
        'doublons_valides' => count(array_filter($doublons, function ($d) {
            return $d['statut'] === 'valide';
        })),
        'doublons_rejetes' => count(array_filter($doublons, function ($d) {
            return $d['statut'] === 'rejete';
        }))
    ]);
} catch (Exception $e) {
    error_log("Erreur dans doublons_import_groupe.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération du groupe de doublons: ' . $e->getMessage()
    ]);
}
