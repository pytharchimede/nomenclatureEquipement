<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

try {
    $pdo = Database::getConnection();

    // Articles non SAP (basé sur nomenclatures source SAP manquante)
    $sql = "
        SELECT 
            DISTINCT n.code_article,
            MAX(a.designation_article) AS designation_article,
            MAX(a.uq_base) AS unite_base,
            MAX(a.numero_piece_fabricant) AS numero_piece_fabricant,
            MAX(a.fabricant) AS fabricant,
            GROUP_CONCAT(DISTINCT n.repere_equipement SEPARATOR ' / ') AS equipements,
            MAX(n.source) AS source
        FROM nomenclatures n
        LEFT JOIN articles a ON a.code_article = n.code_article
        WHERE n.code_article IS NOT NULL
        AND n.code_article NOT IN (
            SELECT DISTINCT code_article FROM nomenclatures WHERE source = 'SAP' AND code_article IS NOT NULL
        )
        GROUP BY n.code_article
        ORDER BY n.code_article
    ";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Articles non SAP (SPL)');

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
    foreach ($headers as $cell => $val) $sheet->setCellValue($cell, $val);

    $row = 2;
    foreach ($rows as $r) {
        $sheet->setCellValue('A' . $row, ''); // N° SPL vide
        $sheet->setCellValue('B' . $row, ''); // Code SAP vide (non SAP)
        $sheet->setCellValue('C' . $row, $r['code_article']);
        $sheet->setCellValue('D' . $row, ''); // Quantité inconnue au global
        $sheet->setCellValue('E' . $row, $r['designation_article']);
        $sheet->setCellValue('F' . $row, $r['unite_base']);
        // Métier approximé via code article
        $metier = 'Autres';
        $code = (string)$r['code_article'];
        if (str_starts_with($code, '1')) $metier = 'Mécanique';
        else if (str_starts_with($code, '2')) $metier = 'Électrique';
        else if (str_starts_with($code, '3')) $metier = 'Instrumentation';
        else if (str_starts_with($code, '4')) $metier = 'Tuyauterie';
        else if (str_starts_with($code, '5')) $metier = 'Chaudronnerie';
        else if (str_starts_with($code, '8')) $metier = 'Sécurité';
        $sheet->setCellValue('G' . $row, $metier);
        $sheet->setCellValue('H' . $row, $r['numero_piece_fabricant']);
        $sheet->setCellValue('I' . $row, $r['fabricant']);
        $sheet->setCellValue('J' . $row, $r['equipements']);
        $sheet->setCellValue('K' . $row, date('Y-m-d'));
        $sheet->setCellValue('L' . $row, $r['source'] ?: 'Nomenclatures');
        $sheet->setCellValue('M' . $row, $_SESSION['username'] ?? 'Système');
        $row++;
    }

    foreach (range('A', 'M') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

    $filename = 'articles_non_sap_SPL_' . ($row - 2) . '_lignes_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename=' . $filename);
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
