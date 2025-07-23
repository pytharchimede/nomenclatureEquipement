<?php

/**
 * Export progressif des équipements avec suivi temps réel
 * Compatible Server-Sent Events pour affichage en temps réel
 */

require_once '../model/Database.php';
require_once '../model/Equipement.php';
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
function sendProgress($step, $message, $percent = null, $details = null)
{
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
    if (isset($_GET['fabricant'])) $filters['fabricant'] = $_GET['fabricant'];
    if (isset($_GET['type_objet'])) $filters['type_objet'] = $_GET['type_objet'];
    if (isset($_GET['categorie_equipement'])) $filters['categorie_equipement'] = $_GET['categorie_equipement'];

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
            $conditions[] = "(repere_equipement LIKE ? OR designation_equipement LIKE ? OR fabricant LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (isset($filters['fabricant']) && !empty($filters['fabricant'])) {
            $conditions[] = "fabricant = ?";
            $params[] = $filters['fabricant'];
        }

        if (isset($filters['type_objet']) && !empty($filters['type_objet'])) {
            $conditions[] = "type_objet = ?";
            $params[] = $filters['type_objet'];
        }

        if (isset($filters['categorie_equipement']) && !empty($filters['categorie_equipement'])) {
            $conditions[] = "categorie_equipement = ?";
            $params[] = $filters['categorie_equipement'];
        }

        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
    } elseif ($exportType === 'selected' && isset($_GET['selected_reperes'])) {
        $selectedReperes = explode(',', $_GET['selected_reperes']);
        $placeholders = str_repeat('?,', count($selectedReperes) - 1) . '?';
        $whereClause = "WHERE repere_equipement IN ($placeholders)";
        $params = $selectedReperes;
    }

    sendProgress('counting', 'Comptage des équipements...', 5);

    // Compter le total d'équipements
    $countQuery = "SELECT COUNT(*) FROM equipements $whereClause";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalEquipements = $stmt->fetchColumn();

    if ($totalEquipements == 0) {
        sendProgress('error', 'Aucun équipement trouvé pour l\'export', 0);
        exit;
    }

    sendProgress('preparing', "Préparation de l'export de $totalEquipements équipements...", 10);

    // Création du spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Equipements');

    // En-têtes
    $headers = [
        'A' => 'Code Équipement',
        'B' => 'Désignation',
        'C' => 'Repère',
        'D' => 'Fabricant',
        'E' => 'Type d\'Objet',
        'F' => 'Désignation Type',
        'G' => 'N° Série Fabricant',
        'H' => 'N° Pièce Fabricant',
        'I' => 'Poste Technique',
        'J' => 'Désignation Poste Technique',
        'K' => 'Poste Travail Principal',
        'L' => 'Catégorie Équipement',
        'M' => 'Centre de Coûts',
        'N' => 'Date Création'
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

    // Requête pour récupérer les équipements
    $query = "SELECT * FROM equipements $whereClause ORDER BY repere_equipement";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $row = 2; // Commence après les en-têtes
    $processed = 0;
    $batchSize = 100; // Traiter par lots de 100

    sendProgress('processing', 'Début du traitement des données...', 20);

    while ($equipement = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $row, $equipement['code_equipement'] ?? '');
        $sheet->setCellValue('B' . $row, $equipement['designation_equipement'] ?? '');
        $sheet->setCellValue('C' . $row, $equipement['repere_equipement'] ?? '');
        $sheet->setCellValue('D' . $row, $equipement['fabricant'] ?? '');
        $sheet->setCellValue('E' . $row, $equipement['type_objet'] ?? '');
        $sheet->setCellValue('F' . $row, $equipement['designation_type'] ?? '');
        $sheet->setCellValue('G' . $row, $equipement['numero_serie_fabricant'] ?? '');
        $sheet->setCellValue('H' . $row, $equipement['numero_piece_fabricant'] ?? '');
        $sheet->setCellValue('I' . $row, $equipement['poste_technique'] ?? '');
        $sheet->setCellValue('J' . $row, $equipement['designation_poste_technique'] ?? '');
        $sheet->setCellValue('K' . $row, $equipement['poste_travail_principal'] ?? '');
        $sheet->setCellValue('L' . $row, $equipement['categorie_equipement'] ?? '');
        $sheet->setCellValue('M' . $row, $equipement['centre_de_couts'] ?? '');
        $sheet->setCellValue('N' . $row, $equipement['date_creation'] ?? '');

        $row++;
        $processed++;

        // Mise à jour du progrès tous les 100 équipements
        if ($processed % $batchSize === 0) {
            $percent = 20 + (($processed / $totalEquipements) * 60); // 20% à 80%
            $details = "Équipement traité : " . ($equipement['repere_equipement'] ?? 'N/A');
            sendProgress('processing', "Traitement en cours... $processed/$totalEquipements", $percent, $details);
        }
    }

    sendProgress('finalizing', 'Finalisation du fichier...', 85);

    // Ajustement automatique des colonnes
    foreach (range('A', 'N') as $col) {
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

    $filename = "equipements{$filterSuffix}_{$timestamp}.xlsx";
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

    sendProgress('complete', "Export terminé ! $processed équipements exportés", 100, [
        'filename' => $filename,
        'filepath' => $filepath,
        'total_processed' => $processed,
        'download_url' => "tmp/$filename"
    ]);
} catch (Exception $e) {
    error_log("Erreur export équipements: " . $e->getMessage());
    sendProgress('error', 'Erreur lors de l\'export: ' . $e->getMessage(), 0);
}
