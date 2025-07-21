<?php

/**
 * Suppression d'équipements par leurs repères
 * Utilise le repère comme clé primaire métier
 */

require_once '../model/Database.php';
require_once '../model/Equipement.php';

header('Content-Type: application/json');

try {
    // Récupération des données JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }

    $reperes = $data['reperes'] ?? [];

    if (!is_array($reperes) || empty($reperes)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Aucun équipement sélectionné pour la suppression.'
        ]);
        exit;
    }

    // Validation des repères (pas vides)
    $validReperes = array_filter($reperes, function ($repere) {
        return !empty(trim($repere));
    });

    if (empty($validReperes)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Aucun repère d\'équipement valide fourni.'
        ]);
        exit;
    }

    // Suppression en base par repere_equipement
    $success = Equipement::deleteByReperes($validReperes);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => count($validReperes) . ' équipement(s) supprimé(s) avec succès.',
            'deleted_count' => count($validReperes)
        ]);
    } else {
        throw new Exception('Erreur lors de la suppression en base de données');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
    ]);
}
