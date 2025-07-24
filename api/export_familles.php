<?php
require_once '../model/Database.php';
require_once '../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $pdo = Database::getConnection();

    // Récupération des données détaillées des familles
    $stmt = $pdo->query("
        SELECT 
            f.nom as famille_nom,
            COALESCE(COUNT(q.id), 0) as nb_elements,
            COALESCE(COUNT(DISTINCT q.repere), 0) as nb_reperes_uniques,
            COALESCE(COUNT(DISTINCT q.unite), 0) as nb_unites_differentes,
            COALESCE(SUM(q.quantite), 0) as total_quantite,
            COALESCE(AVG(q.quantite), 0) as moyenne_quantite,
            COALESCE(MAX(q.quantite), 0) as max_quantite,
            COALESCE(MIN(q.quantite), 0) as min_quantite,
            CASE 
                WHEN COUNT(q.id) > 0 THEN 'active' 
                ELSE 'inactive' 
            END as status
        FROM familles f 
        LEFT JOIN quantitatif q ON f.nom = q.famille 
        GROUP BY f.nom
        ORDER BY nb_elements DESC, f.nom ASC
    ");

    $familles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Création du fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Familles d\'Équipements');

    // Configuration des en-têtes
    $headers = [
        'A1' => 'Famille',
        'B1' => 'Statut',
        'C1' => 'Nb Éléments',
        'D1' => 'Nb Repères Uniques',
        'E1' => 'Nb Unités Différentes',
        'F1' => 'Quantité Totale',
        'G1' => 'Quantité Moyenne',
        'H1' => 'Quantité Maximale',
        'I1' => 'Quantité Minimale'
    ];

    // Définition des en-têtes
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    // Style des en-têtes
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 12
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '667EEA']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];

    $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

    // Ajout des données
    $row = 2;
    foreach ($familles as $famille) {
        $sheet->setCellValue('A' . $row, $famille['famille_nom']);
        $sheet->setCellValue('B' . $row, ucfirst($famille['status']));
        $sheet->setCellValue('C' . $row, $famille['nb_elements']);
        $sheet->setCellValue('D' . $row, $famille['nb_reperes_uniques']);
        $sheet->setCellValue('E' . $row, $famille['nb_unites_differentes']);
        $sheet->setCellValue('F' . $row, $famille['total_quantite']);
        $sheet->setCellValue('G' . $row, round($famille['moyenne_quantite'], 2));
        $sheet->setCellValue('H' . $row, $famille['max_quantite']);
        $sheet->setCellValue('I' . $row, $famille['min_quantite']);

        // Style alterné pour les lignes
        if ($row % 2 == 0) {
            $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ]
            ]);
        }

        // Couleur selon le statut
        $statusColor = $famille['status'] === 'active' ? '28A745' : '6C757D';
        $sheet->getStyle('B' . $row)->applyFromArray([
            'font' => [
                'color' => ['rgb' => $statusColor],
                'bold' => true
            ]
        ]);

        $row++;
    }

    // Auto-ajustement des colonnes
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Bordures pour toutes les données
    $sheet->getStyle('A1:I' . ($row - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ]);

    // Ajout d'une feuille de statistiques
    $statsSheet = $spreadsheet->createSheet();
    $statsSheet->setTitle('Statistiques');

    // Statistiques générales
    $totalFamilles = count($familles);
    $famillesActives = count(array_filter($familles, fn($f) => $f['status'] === 'active'));
    $totalElements = array_sum(array_column($familles, 'nb_elements'));
    $totalQuantite = array_sum(array_column($familles, 'total_quantite'));

    $statsData = [
        ['Statistique', 'Valeur'],
        ['Total Familles', $totalFamilles],
        ['Familles Actives', $famillesActives],
        ['Familles Inactives', $totalFamilles - $famillesActives],
        ['Taux d\'Utilisation (%)', round(($famillesActives / $totalFamilles) * 100, 2)],
        ['Total Éléments', $totalElements],
        ['Total Quantité', $totalQuantite],
        ['Moyenne Éléments par Famille Active', $famillesActives > 0 ? round($totalElements / $famillesActives, 2) : 0]
    ];

    $statsRow = 1;
    foreach ($statsData as $statData) {
        $statsSheet->setCellValue('A' . $statsRow, $statData[0]);
        $statsSheet->setCellValue('B' . $statsRow, $statData[1]);
        $statsRow++;
    }

    // Style des statistiques
    $statsSheet->getStyle('A1:B1')->applyFromArray($headerStyle);
    $statsSheet->getColumnDimension('A')->setAutoSize(true);
    $statsSheet->getColumnDimension('B')->setAutoSize(true);

    // Configuration de la réponse HTTP
    $filename = 'familles_export_' . date('Y-m-d_H-i-s') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    // Génération et envoi du fichier
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
    ]);
}
