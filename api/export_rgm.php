<?php
require_once '../model/Database.php';

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

    // Récupération de toutes les données RGM depuis la table nomenclatures
    $data = getRgmExportData($filters);

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
            isset($row['date_creation']) ? date('d/m/Y', strtotime($row['date_creation'])) : ''
        ];
        fputcsv($output, $csvRow, ';');
    }

    fclose($output);
} catch (Exception $e) {
    // En cas d'erreur, redirection vers la page avec message
    header('Location: ../rgm_synthese.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Fonction pour récupérer les données RGM à exporter
function getRgmExportData($filters)
{
    $pdo = Database::getConnection();

    $whereConditions = ["source = 'RGM'"];
    $params = [];

    // Construction des conditions WHERE
    if (!empty($filters['repere_equipement'])) {
        $whereConditions[] = "repere_equipement LIKE ?";
        $params[] = '%' . $filters['repere_equipement'] . '%';
    }
    if (!empty($filters['code_article'])) {
        $whereConditions[] = "code_article LIKE ?";
        $params[] = '%' . $filters['code_article'] . '%';
    }
    if (!empty($filters['designation_article'])) {
        $whereConditions[] = "designation_article LIKE ?";
        $params[] = '%' . $filters['designation_article'] . '%';
    }
    if (!empty($filters['unite'])) {
        $whereConditions[] = "unite = ?";
        $params[] = $filters['unite'];
    }

    $whereClause = "WHERE " . implode(" AND ", $whereConditions);

    $sql = "
        SELECT repere_equipement, code_article, designation_article, 
               quantite, unite, source, date_creation
        FROM nomenclatures 
        {$whereClause}
        ORDER BY date_creation DESC, repere_equipement ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
