<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';
require_once '../model/Quantitatif.php';
require_once '../model/Famille.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    // Récupération des filtres depuis les paramètres GET
    $filters = [];
    if (!empty($_GET['famille'])) {
        $filters['famille'] = trim($_GET['famille']);
    }
    if (!empty($_GET['repere'])) {
        $filters['repere'] = trim($_GET['repere']);
    }
    if (!empty($_GET['unite'])) {
        $filters['unite'] = trim($_GET['unite']);
    }

    // Récupération de toutes les données filtrées
    $quantitatifs = Quantitatif::getPaginated(1, 10000, $filters); // Grande limite pour tout récupérer

    if (empty($quantitatifs)) {
        throw new Exception('Aucune donnée à exporter avec les filtres appliqués');
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Titre de la feuille
    $filterInfo = '';
    if (!empty($filters)) {
        $filterParts = [];
        foreach ($filters as $key => $value) {
            $filterParts[] = ucfirst($key) . ': ' . $value;
        }
        $filterInfo = ' (' . implode(', ', $filterParts) . ')';
    }

    $sheet->setTitle('Export Quantitatif');

    // En-têtes
    $headers = [
        'ID',
        'Famille',
        'Repère',
        'Unité',
        'Quantité'
    ];

    // Ajouter les colonnes additionnelles si elles existent
    $additionalColumns = [];
    if (!empty($quantitatifs)) {
        $firstItem = $quantitatifs[0];
        if (!empty($firstItem['autres_colonnes'])) {
            $autresColonnes = json_decode($firstItem['autres_colonnes'], true);
            if (is_array($autresColonnes)) {
                foreach ($autresColonnes as $key => $value) {
                    if (!in_array($key, ['famille', 'repere', 'unite', 'quantite']) && !in_array($key, $additionalColumns)) {
                        $additionalColumns[] = $key;
                    }
                }
            }
        }
    }

    $allHeaders = array_merge($headers, $additionalColumns);
    $sheet->fromArray($allHeaders, null, 'A1');

    // Style des en-têtes
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '667eea']
        ]
    ];
    $sheet->getStyle('A1:' . chr(65 + count($allHeaders) - 1) . '1')->applyFromArray($headerStyle);

    // Données
    $rowNum = 2;
    foreach ($quantitatifs as $q) {
        $rowData = [
            $q['id'],
            $q['famille'] ?? 'Non défini',
            $q['repere'] ?? '',
            $q['unite'] ?? '',
            $q['quantite'] ?? ''
        ];

        // Ajouter les colonnes additionnelles
        if (!empty($q['autres_colonnes'])) {
            $autresColonnes = json_decode($q['autres_colonnes'], true);
            if (is_array($autresColonnes)) {
                foreach ($additionalColumns as $col) {
                    $rowData[] = $autresColonnes[$col] ?? '';
                }
            } else {
                // Remplir avec des valeurs vides si le JSON n'est pas valide
                $rowData = array_merge($rowData, array_fill(0, count($additionalColumns), ''));
            }
        } else {
            // Remplir avec des valeurs vides si pas d'autres colonnes
            $rowData = array_merge($rowData, array_fill(0, count($additionalColumns), ''));
        }

        $sheet->fromArray($rowData, null, 'A' . $rowNum);
        $rowNum++;
    }

    // Auto-ajuster la largeur des colonnes
    foreach (range('A', chr(65 + count($allHeaders) - 1)) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Informations sur l'export
    $infoText = "Export généré le " . date('d/m/Y à H:i:s');
    if ($filterInfo) {
        $infoText .= " - Filtres appliqués: " . trim($filterInfo, ' ()');
    }
    $infoText .= " - Total: " . count($quantitatifs) . " éléments";

    $sheet->setCellValue('A' . ($rowNum + 1), $infoText);
    $sheet->getStyle('A' . ($rowNum + 1))->getFont()->setItalic(true)->setSize(10);

    // Configuration des en-têtes pour le téléchargement
    $filename = 'quantitatif_export_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    // En cas d'erreur, rediriger vers la page avec un message
    header('Location: ../import_quantitatif.php?error=' . urlencode('Erreur export Excel: ' . $e->getMessage()));
    exit;
}
