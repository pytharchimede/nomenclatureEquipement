<?php
require_once '../model/Database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $pdo = Database::getConnection();

    // Détection des groupes de doublons
    $query = "
        SELECT 
            repere_equipement,
            code_article,
            COUNT(*) as count_doublons,
            GROUP_CONCAT(id ORDER BY date_creation ASC) as ids,
            GROUP_CONCAT(source ORDER BY date_creation ASC SEPARATOR ' | ') as sources,
            GROUP_CONCAT(DISTINCT designation_equipement ORDER BY date_creation ASC SEPARATOR ' | ') as designations_equipement,
            GROUP_CONCAT(DISTINCT designation_article ORDER BY date_creation ASC SEPARATOR ' | ') as designations_article,
            GROUP_CONCAT(DISTINCT fabricant ORDER BY date_creation ASC SEPARATOR ' | ') as fabricants,
            GROUP_CONCAT(quantite ORDER BY date_creation ASC SEPARATOR ' | ') as quantites,
            GROUP_CONCAT(unite ORDER BY date_creation ASC SEPARATOR ' | ') as unites,
            GROUP_CONCAT(date_creation ORDER BY date_creation ASC SEPARATOR ' | ') as dates_creation,
            MIN(date_creation) as premiere_creation,
            MAX(date_creation) as derniere_creation
        FROM nomenclatures 
        WHERE repere_equipement IS NOT NULL 
        AND repere_equipement != ''
        AND code_article IS NOT NULL 
        AND code_article != ''
        GROUP BY repere_equipement, code_article 
        HAVING count_doublons > 1
        ORDER BY count_doublons DESC, repere_equipement, code_article
    ";

    $stmt = $pdo->query($query);
    $doublons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($doublons)) {
        // Pas de doublons trouvés
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Aucun doublon détecté dans la base de données.',
            'count' => 0
        ]);
        exit;
    }

    // Création du fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Doublons Nomenclatures');

    // En-têtes
    $headers = [
        'A1' => 'Groupe',
        'B1' => 'Repère Équipement',
        'C1' => 'Code Article',
        'D1' => 'Nb Doublons',
        'E1' => 'IDs Concernés',
        'F1' => 'Sources',
        'G1' => 'Désignations Équipement',
        'H1' => 'Désignations Article',
        'I1' => 'Fabricants',
        'J1' => 'Quantités',
        'K1' => 'Unités',
        'L1' => 'Dates Création',
        'M1' => 'Première Création',
        'N1' => 'Dernière Création'
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    // Style des en-têtes
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];

    $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

    // Données
    $row = 2;
    $groupe = 1;

    foreach ($doublons as $doublon) {
        $sheet->setCellValue('A' . $row, $groupe);
        $sheet->setCellValue('B' . $row, $doublon['repere_equipement']);
        $sheet->setCellValue('C' . $row, $doublon['code_article']);
        $sheet->setCellValue('D' . $row, $doublon['count_doublons']);
        $sheet->setCellValue('E' . $row, $doublon['ids']);
        $sheet->setCellValue('F' . $row, $doublon['sources']);
        $sheet->setCellValue('G' . $row, $doublon['designations_equipement']);
        $sheet->setCellValue('H' . $row, $doublon['designations_article']);
        $sheet->setCellValue('I' . $row, $doublon['fabricants']);
        $sheet->setCellValue('J' . $row, $doublon['quantites']);
        $sheet->setCellValue('K' . $row, $doublon['unites']);
        $sheet->setCellValue('L' . $row, $doublon['dates_creation']);
        $sheet->setCellValue('M' . $row, $doublon['premiere_creation']);
        $sheet->setCellValue('N' . $row, $doublon['derniere_creation']);

        // Style alterné pour les groupes
        if ($groupe % 2 == 0) {
            $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']]
            ]);
        }

        // Style spécial pour les groupes avec beaucoup de doublons (>= 5)
        if ($doublon['count_doublons'] >= 5) {
            $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFCDD2']],
                'font' => ['bold' => true]
            ]);
        }

        $row++;
        $groupe++;
    }

    // Auto-ajustement des colonnes
    foreach (range('A', 'N') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Ajout d'une feuille de synthèse
    $synthese = $spreadsheet->createSheet();
    $synthese->setTitle('Synthèse Doublons');

    $synthese->setCellValue('A1', 'SYNTHÈSE DES DOUBLONS');
    $synthese->setCellValue('A2', 'Date d\'export:');
    $synthese->setCellValue('B2', date('d/m/Y H:i:s'));
    $synthese->setCellValue('A3', 'Nombre total de groupes de doublons:');
    $synthese->setCellValue('B3', count($doublons));

    // Calcul du nombre total d'entrées en doublon
    $totalEntreesDupliquees = array_sum(array_column($doublons, 'count_doublons'));
    $synthese->setCellValue('A4', 'Nombre total d\'entrées en doublon:');
    $synthese->setCellValue('B4', $totalEntreesDupliquees);

    // Répartition par nombre de doublons
    $synthese->setCellValue('A6', 'Répartition par nombre de doublons:');
    $repartition = [];
    foreach ($doublons as $doublon) {
        $count = $doublon['count_doublons'];
        $repartition[$count] = ($repartition[$count] ?? 0) + 1;
    }

    $row = 7;
    foreach ($repartition as $nbDoublons => $nbGroupes) {
        $synthese->setCellValue('A' . $row, $nbDoublons . ' doublons:');
        $synthese->setCellValue('B' . $row, $nbGroupes . ' groupes');
        $row++;
    }

    // Style de la synthèse
    $synthese->getStyle('A1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 16],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);
    $synthese->getStyle('A1:B1')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
        'font' => ['color' => ['rgb' => 'FFFFFF']]
    ]);

    $synthese->getColumnDimension('A')->setAutoSize(true);
    $synthese->getColumnDimension('B')->setAutoSize(true);

    // Génération du fichier
    $filename = 'doublons_nomenclatures_' . date('Y-m-d_H-i-s') . '.xlsx';
    $filepath = '../tmp/' . $filename;

    // Créer le dossier tmp s'il n'existe pas
    if (!is_dir('../tmp/')) {
        mkdir('../tmp/', 0777, true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);

    // Headers pour téléchargement
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    // Envoyer le fichier
    readfile($filepath);

    // Nettoyer le fichier temporaire
    unlink($filepath);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
    ]);
}
