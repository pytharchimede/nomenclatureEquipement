<?php
require_once '../model/Database.php';
require_once '../model/RgmSynthese.php';

try {
    // Récupération des filtres depuis les paramètres GET
    $filters = [
        'repere_equipement' => $_GET['repere'] ?? '',
        'code_article' => $_GET['code'] ?? '',
        'designation_article' => $_GET['designation'] ?? '',
        'unite' => $_GET['unite'] ?? ''
    ];

    // Nettoyage des filtres vides
    $filters = array_filter($filters, function ($value) {
        return !empty(trim($value));
    });

    // Récupération de toutes les données avec filtres
    $data = RgmSynthese::getPaginated(1, 10000, $filters); // Grande limite pour export complet

    // Nom du fichier avec timestamp
    $filename = 'export_rgm_' . date('Y-m-d_H-i-s') . '.csv';

    // Headers pour le téléchargement
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Ouverture du flux de sortie
    $output = fopen('php://output', 'w');

    // BOM UTF-8 pour Excel
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // En-têtes CSV
    $headers = [
        'Repère Équipement',
        'Code Article',
        'Désignation Article',
        'Quantité',
        'Unité',
        'Source',
        'Date Création'
    ];
    fputcsv($output, $headers, ';');

    // Écriture des données
    foreach ($data as $row) {
        $csvRow = [
            $row['repere_equipement'] ?? '',
            $row['code_article'] ?? '',
            $row['designation_article'] ?? '',
            $row['quantite'] ?? 0,
            $row['unite'] ?? '',
            $row['source'] ?? 'RGM',
            isset($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : ''
        ];
        fputcsv($output, $csvRow, ';');
    }

    fclose($output);
} catch (Exception $e) {
    // En cas d'erreur, redirection vers la page avec message
    header('Location: ../rgm_synthese.php?error=' . urlencode($e->getMessage()));
    exit;
}
