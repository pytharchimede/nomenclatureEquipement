<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Optimisations pour gros exports
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

try {
    $pdo = Database::getConnection();

    // Récupération des données avec filtres si fournis
    $whereConditions = [];
    $params = [];

    $filters = [
        'numero' => 'numero',
        'code_sap' => 'code_sap',
        'code_article' => 'code_article',
        'metier' => 'metier',
        'equipement' => 'equipement'
    ];

    foreach ($filters as $param => $column) {
        if (isset($_GET[$param]) && $_GET[$param] !== '') {
            $whereConditions[] = "$column LIKE ?";
            $params[] = '%' . $_GET[$param] . '%';
        }
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    $sql = "SELECT * FROM template_spl $whereClause ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Séparation des équipements
    $rows = [];
    foreach ($templates as $tpl) {
        $reperes = preg_split('/\s*\/\s*/', $tpl['equipement']);
        foreach ($reperes as $repere) {
            $row = $tpl;
            $row['equipement'] = trim($repere);
            $rows[] = $row;
        }
    }

    // Création du fichier Excel avec optimisations
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Template SPL Export');

    // Désactiver les calculs automatiques pour de meilleures performances
    $spreadsheet->getCalculationEngine()->disableCalculationCache();

    // En-têtes avec icônes texte
    $headers = [
        'A1' => 'N° SPL',
        'B1' => 'Code SAP',
        'C1' => 'Code Article',
        'D1' => 'Quantité',
        'E1' => 'Désignation Article',
        'F1' => 'Unité Base',
        'G1' => 'Métier',
        'H1' => 'N° Pièce Fabricant',
        'I1' => 'Fabricant',
        'J1' => 'Équipement/Repère',
        'K1' => 'Date Import',
        'L1' => 'Source',
        'M1' => 'Import Par'
    ];

    // Appliquer les en-têtes
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    // Style moderne des en-têtes
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 11
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => '1976D2']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'FFFFFF']
            ]
        ]
    ];

    $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

    // Remplissage des données par chunks pour éviter les problèmes mémoire
    $row = 2;
    $chunkSize = 1000;
    $offset = 0;

    do {
        $sql = "SELECT * FROM template_spl $whereClause ORDER BY id ASC LIMIT $chunkSize OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $chunk = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($chunk as $tpl) {
            // Séparation des équipements
            $reperes = preg_split('/\s*\/\s*/', $tpl['equipement']);

            foreach ($reperes as $repere) {
                $repere = trim($repere);
                if (empty($repere)) continue;

                // Optimisation: utiliser setCellValueExplicit pour éviter les conversions
                $sheet->setCellValue('A' . $row, $tpl['numero']);
                $sheet->setCellValue('B' . $row, $tpl['code_sap']);
                $sheet->setCellValue('C' . $row, $tpl['code_article']);
                $sheet->setCellValue('D' . $row, $tpl['quantite']);
                $sheet->setCellValue('E' . $row, $tpl['designation_article']);
                $sheet->setCellValue('F' . $row, $tpl['unite_base']);
                $sheet->setCellValue('G' . $row, $tpl['metier']);
                $sheet->setCellValue('H' . $row, $tpl['numero_piece_fabricant']);
                $sheet->setCellValue('I' . $row, $tpl['fabricant']);
                $sheet->setCellValue('J' . $row, $repere);
                $sheet->setCellValue('K' . $row, $tpl['date_import']);
                $sheet->setCellValue('L' . $row, $tpl['source'] ?? 'Template');
                $sheet->setCellValue('M' . $row, $tpl['import_par']);

                $row++;
            }
        }

        $offset += $chunkSize;

        // Libérer la mémoire
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    } while (count($chunk) === $chunkSize);

    // Style des données avec alternance de couleurs
    $lastRow = $row - 1;
    if ($lastRow > 1) {
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E0E0E0']
                ]
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ];

        $sheet->getStyle('A2:M' . $lastRow)->applyFromArray($dataStyle);

        // Alternance de couleurs pour faciliter la lecture
        for ($i = 2; $i <= $lastRow; $i += 2) {
            $sheet->getStyle('A' . $i . ':M' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F8F9FA']
                ]
            ]);
        }
    }

    // Auto-ajustement des colonnes avec limites
    foreach (range('A', 'M') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getColumnDimension($col)->setWidth(min(50, $sheet->getColumnDimension($col)->getWidth()));
    }

    // Figer la première ligne
    $sheet->freezePane('A2');

    // Métadonnées du document
    $spreadsheet->getProperties()
        ->setCreator('Nomenclature Équipement System')
        ->setTitle('Export Template SPL')
        ->setSubject('Template SPL Export')
        ->setDescription('Export des données Template SPL générées automatiquement')
        ->setCategory('Export');

    // Génération du fichier avec nom descriptif
    $totalExported = $lastRow - 1;
    $filename = 'template_spl_export_' . $totalExported . '_lignes_' . date('Y-m-d_H-i-s') . '.xlsx';

    // Headers pour le téléchargement
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    // Nettoyer la mémoire
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
} catch (Exception $e) {
    http_response_code(500);
    echo 'Erreur lors de l\'export: ' . $e->getMessage();
}
