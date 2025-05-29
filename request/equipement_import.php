<?php
header('Content-Type: application/json');

require_once '../model/Database.php';
require_once '../model/Equipement.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => "Aucun fichier reçu ou erreur d'upload."
    ]);
    exit;
}

$fileTmpPath = $_FILES['excel_file']['tmp_name'];

try {
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    // Première ligne = entête
    $header = array_shift($rows);

    // Associe lettre colonne => nom colonne
    $colMap = [];
    foreach ($header as $colLetter => $colName) {
        $colMap[trim($colName)] = $colLetter;
    }

    $expectedCols = [
        'Code Equipement',
        'Désignation équipement',
        'Repère équipement',
        'Fabricant',
        'Type d\'objet',
        'Désignat. type',
        'N° série fabr.',
        'N° pièce fabric',
        'Poste technique',
        'Désignation Poste Technique',
        'PosteTravPrinc.',
        'Catég.équipemnt',
        'Centre de coûts',
        'Créé le'
    ];
    foreach ($expectedCols as $col) {
        if (!isset($colMap[$col])) {
            echo json_encode([
                'success' => false,
                'message' => "Colonne manquante dans le fichier : $col"
            ]);
            exit;
        }
    }

    $inserted = 0;
    $duplicates = 0;
    $errors = 0;
    $log = [];

    foreach ($rows as $i => $row) {
        $ligneMsg = "Ligne " . ($i + 2) . ": ";
        $code_equipement = trim($row[$colMap['Code Equipement']] ?? '');
        if ($code_equipement === '') {
            $log[] = ['message' => $ligneMsg . "Code équipement vide, ignorée.", 'enCours' => false];
            continue;
        }
        // Vérifie si l'équipement existe déjà via la classe Equipement
        if (Equipement::getByCode($code_equipement)) {
            $log[] = ['message' => $ligneMsg . "Doublon détecté ($code_equipement), ignorée.", 'enCours' => false];
            $duplicates++;
            continue;
        }

        // Prépare les autres champs
        $data = [
            'code_equipement' => $code_equipement,
            'designation_equipement' => $row[$colMap['Désignation équipement']] ?? '',
            'repere_equipement' => $row[$colMap['Repère équipement']] ?? '',
            'fabricant' => $row[$colMap['Fabricant']] ?? '',
            'type_objet' => $row[$colMap['Type d\'objet']] ?? '',
            'designation_type' => $row[$colMap['Désignat. type']] ?? '',
            'numero_serie_fabricant' => $row[$colMap['N° série fabr.']] ?? '',
            'numero_piece_fabricant' => $row[$colMap['N° pièce fabric']] ?? '',
            'poste_technique' => $row[$colMap['Poste technique']] ?? '',
            'designation_poste_technique' => $row[$colMap['Désignation Poste Technique']] ?? '',
            'poste_travail_principal' => $row[$colMap['PosteTravPrinc.']] ?? '',
            'categorie_equipement' => $row[$colMap['Catég.équipemnt']] ?? '',
            'centre_de_couts' => $row[$colMap['Centre de coûts']] ?? '',
            'date_creation' => $row[$colMap['Créé le']] ?? ''
        ];

        // Conversion date
        if ($data['date_creation']) {
            if (is_numeric($data['date_creation'])) {
                $data['date_creation'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data['date_creation'])->format('Y-m-d');
            } else {
                $data['date_creation'] = date('Y-m-d', strtotime($data['date_creation']));
            }
        } else {
            $data['date_creation'] = null;
        }

        // Insertion via la classe Equipement
        if (Equipement::create($data)) {
            $log[] = ['message' => $ligneMsg . "Ajouté ($code_equipement)", 'enCours' => false];
            $inserted++;
        } else {
            $log[] = ['message' => $ligneMsg . "Erreur lors de l'ajout ($code_equipement)", 'enCours' => false];
            $errors++;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Import terminé : $inserted ajout(s), $duplicates doublon(s), $errors erreur(s).",
        'log' => $log
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de la lecture du fichier : " . $e->getMessage()
    ]);
    exit;
}
