<?php

/**
 * Récupère les détails d'un équipement par son repère
 * Utilise le repère comme clé primaire métier
 */

require_once '../model/Equipement.php';

header('Content-Type: application/json');

try {
    $repere = $_GET['repere'] ?? '';

    if (empty($repere)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Le repère équipement est requis'
        ]);
        exit;
    }

    $equipement = Equipement::getByRepere($repere);

    if (!$equipement) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Équipement non trouvé'
        ]);
        exit;
    }

    // Retourne les données de l'équipement
    echo json_encode($equipement);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération de l\'équipement : ' . $e->getMessage()
    ]);
}
