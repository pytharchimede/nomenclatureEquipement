<?php
/**
 * Export progressif des nomenclatures avec suivi temps réel
 * Compatible Server-Sent Events pour affichage en temps réel
 * Optimisé pour 26 531+ lignes
 */

require_once '../model/Database.php';
require_once '../model/Nomenclature.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Headers pour Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Pour nginx

// Fonction pour envoyer les données de progression
function sendProgress($step, $message, $percent = null, $details = null) {
    $data = [
        'step' => $step,
        'message' => $message,
        'percent' => $percent,
        'details' => $details,
        'timestamp' => date('H:i:s')
    ];
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

try {
    // Paramètres reçus
    $type = $_GET['type'] ?? 'excel';
    $exportType = $_GET['export_type'] ?? 'all'; // all, filtered, selected
    
    // Récupération des filtres
    $filters = [];
    if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
    if (isset($_GET['code_equipement'])) $filters['code_equipement'] = $_GET['code_equipement'];
    if (isset($_GET['code_article'])) $filters['code_article'] = $_GET['code_article'];
    if (isset($_GET['repere_equipement'])) $filters['repere_equipement'] = $_GET['repere_equipement'];
    if (isset($_GET['fabricant'])) $filters['fabricant'] = $_GET['fabricant'];
    if (isset($_GET['type'])) $filters['type'] = $_GET['type'];
    if (isset($_GET['designation_article'])) $filters['designation_article'] = $_GET['designation_article'];
    if (isset($_GET['unite'])) $filters['unite'] = $_GET['unite'];
    if (isset($_GET['poste_technique'])) $filters['poste_technique'] = $_GET['poste_technique'];
    if (isset($_GET['metier'])) $filters['metier'] = $_GET['metier'];
    if (isset($_GET['source'])) $filters['source'] = $_GET['source'];
    
    sendProgress('init', 'Initialisation de l\'export...', 0);
    
    // Connexion à la base de données
    $pdo = Database::getConnection();
    
    // Construction de la requête selon le type d'export
    $whereClause = '';
    $params = [];
    
    if ($exportType === 'filtered' && !empty($filters)) {
        $conditions = [];
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = "(code_equipement LIKE ? OR code_article LIKE ? OR repere_equipement LIKE ? OR designation_equipement LIKE ? OR designation_article LIKE ? OR fabricant LIKE ?)";
            for ($i = 0; $i < 6; $i++) {
                $params[] = $search;
            }
        }
        
        $filterFields = ['code_equipement', 'code_article', 'repere_equipement', 'fabricant', 'type', 'designation_article', 'unite', 'poste_technique', 'metier', 'source'];
        foreach ($filterFields as $field) {
            if (isset($filters[$field]) && !empty($filters[$field])) {
                $conditions[] = "$field LIKE ?";
                $params[] = '%' . $filters[$field] . '%';
            }
        }
        
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
    } elseif ($exportType === 'selected' && isset($_GET['selected_ids'])) {
        $selectedIds = explode(',', $_GET['selected_ids']);
        $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
        $whereClause = "WHERE id IN ($placeholders)";
        $params = $selectedIds;
    }
    
    sendProgress('counting', 'Comptage des nomenclatures...', 5);
    
    // Compter le total de nomenclatures
    $countQuery = "SELECT COUNT(*) FROM nomenclatures $whereClause";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalNomenclatures = $stmt->fetchColumn();
    
    if ($totalNomenclatures == 0) {
        sendProgress('error', 'Aucune nomenclature trouvée pour l\'export', 0);
        exit;
    }
    
    sendProgress('preparing', "Préparation de l'export de $totalNomenclatures nomenclatures...", 10);
    
    // Création du spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Nomenclatures');
    
    // En-têtes (toutes les colonnes importantes)
    $headers = [
        'A' => 'Code Équipement',
        'B' => 'Repère Équipement',
        'C' => 'Désignation Équipement',
        'D' => 'Fabricant',
        'E' => 'Type',
        'F' => 'N° Série Fabricant',
        'G' => 'Code Article',
        'H' => 'Désignation Article',
        'I' => 'N° Poste',
        'J' => 'Quantité',
        'K' => 'Unité',
        'L' => 'Poste Technique',
        'M' => 'Métier',
        'N' => 'Date Création',
        'O' => 'Source'
    ];
    
    // Style des en-têtes
    foreach ($headers as $col => $title) {
        $sheet->setCellValue($col . '1', $title);
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
        $sheet->getStyle($col . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E3F2FD');
        $sheet->getStyle($col . '1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    sendProgress('headers', 'En-têtes créés', 15);
    
    // Requête pour récupérer les nomenclatures
    $query = "SELECT * FROM nomenclatures $whereClause ORDER BY repere_equipement, code_article, numero_poste";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $row = 2; // Commence après les en-têtes
    $processed = 0;
    $batchSize = 50; // Traiter par lots de 50 pour les nomenclatures (plus de données par ligne)
    
    sendProgress('processing', 'Début du traitement des données...', 20);
    
    while ($nomenclature = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $row, $nomenclature['code_equipement'] ?? '');
        $sheet->setCellValue('B' . $row, $nomenclature['repere_equipement'] ?? '');
        $sheet->setCellValue('C' . $row, $nomenclature['designation_equipement'] ?? '');
        $sheet->setCellValue('D' . $row, $nomenclature['fabricant'] ?? '');
        $sheet->setCellValue('E' . $row, $nomenclature['type'] ?? '');
        $sheet->setCellValue('F' . $row, $nomenclature['numero_serie_fabricant'] ?? '');
        $sheet->setCellValue('G' . $row, $nomenclature['code_article'] ?? '');
        $sheet->setCellValue('H' . $row, $nomenclature['designation_article'] ?? '');
        $sheet->setCellValue('I' . $row, $nomenclature['numero_poste'] ?? '');
        $sheet->setCellValue('J' . $row, $nomenclature['quantite'] ?? '');
        $sheet->setCellValue('K' . $row, $nomenclature['unite'] ?? '');
        $sheet->setCellValue('L' . $row, $nomenclature['poste_technique'] ?? '');
        $sheet->setCellValue('M' . $row, $nomenclature['metier'] ?? '');
        $sheet->setCellValue('N' . $row, $nomenclature['date_creation'] ?? '');
        $sheet->setCellValue('O' . $row, $nomenclature['source'] ?? '');
        
        $row++;
        $processed++;
        
        // Mise à jour du progrès tous les 50 nomenclatures
        if ($processed % $batchSize === 0) {
            $percent = 20 + (($processed / $totalNomenclatures) * 60); // 20% à 80%
            $details = "Nomenclature traitée : " . ($nomenclature['repere_equipement'] ?? 'N/A') . " / " . ($nomenclature['code_article'] ?? 'N/A');
            sendProgress('processing', "Traitement en cours... $processed/$totalNomenclatures", $percent, $details);
        }
    }
    
    sendProgress('finalizing', 'Finalisation du fichier...', 85);
    
    // Ajustement automatique des colonnes
    foreach (range('A', 'O') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Génération du nom de fichier
    $timestamp = date('Y-m-d_H-i-s');
    $filterSuffix = '';
    if ($exportType === 'filtered') {
        $filterSuffix = '_filtre';
    } elseif ($exportType === 'selected') {
        $filterSuffix = '_selection';
    }
    
    $filename = "nomenclatures{$filterSuffix}_{$timestamp}.xlsx";
    $filepath = "../tmp/$filename";
    
    sendProgress('saving', 'Sauvegarde du fichier...', 90);
    
    // Création du répertoire tmp si nécessaire
    if (!is_dir('../tmp')) {
        mkdir('../tmp', 0755, true);
    }
    
    // Sauvegarde du fichier
    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);
    
    // Libération de la mémoire
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    
    sendProgress('complete', "Export terminé ! $processed nomenclatures exportées", 100, [
        'filename' => $filename,
        'filepath' => $filepath,
        'total_processed' => $processed,
        'download_url' => "tmp/$filename"
    ]);
    
} catch (Exception $e) {
    error_log("Erreur export nomenclatures: " . $e->getMessage());
    sendProgress('error', 'Erreur lors de l\'export: ' . $e->getMessage(), 0);
}
?>
