<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    // Simulation de l'historique des exports (vous pourriez avoir une vraie table de logs)
    $exports = [
        [
            'type' => 'equipements',
            'subtype' => 'complet',
            'date' => date('d/m/Y H:i', strtotime('-2 hours')),
            'user' => $_SESSION['user']['nom'] ?? 'Utilisateur',
            'rows' => 1250,
            'file_path' => 'uploads/exports/equipements_' . date('Y-m-d') . '.xlsx'
        ],
        [
            'type' => 'articles',
            'subtype' => 'non_lies',
            'date' => date('d/m/Y H:i', strtotime('-1 day')),
            'user' => $_SESSION['user']['nom'] ?? 'Utilisateur',
            'rows' => 89,
            'file_path' => 'uploads/exports/articles_non_lies_' . date('Y-m-d', strtotime('-1 day')) . '.xlsx'
        ],
        [
            'type' => 'nomenclatures',
            'subtype' => 'par_equipement',
            'date' => date('d/m/Y H:i', strtotime('-2 days')),
            'user' => $_SESSION['user']['nom'] ?? 'Utilisateur',
            'rows' => 2847,
            'file_path' => 'uploads/exports/nomenclatures_' . date('Y-m-d', strtotime('-2 days')) . '.xlsx'
        ]
    ];

    echo json_encode([
        'success' => true,
        'exports' => $exports
    ]);
} catch (Exception $e) {
    error_log("Erreur dans export_history.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement de l\'historique'
    ]);
}
