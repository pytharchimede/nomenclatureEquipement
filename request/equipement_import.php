<?php
/**
 * Import optimisé pour les gros volumes (23 000+ lignes)
 * Utilise le repère comme clé primaire métier
 */

header('Content-Type: application/json');

require_once '../model/Database.php';
require_once '../model/Equipement.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Configuration pour gros volumes
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 600);

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => "Aucun fichier reçu ou erreur d'upload."
    ]);
    exit;
}

$fileTmpPath = $_FILES['excel_file']['tmp_name'];

try {
    // Lecture par chunks pour économiser la mémoire
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Lecture de l'entête
    $header = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', null, true, true, true)[1];
    
    // Nettoyage de l'entête
    $header = array_map('trim', $header);
    
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

    // Traitement par lots pour les gros volumes
    $batchSize = 1000;
    $totalRows = $sheet->getHighestRow() - 1; // -1 pour exclure l'entête
    $inserted = 0;
    $duplicates = 0;
    $errors = 0;
    $log = [];

    // Préparation des requêtes
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    try {
        // Lecture et traitement par chunks
        for ($startRow = 2; $startRow <= $totalRows + 1; $startRow += $batchSize) {
            $endRow = min($startRow + $batchSize - 1, $totalRows + 1);
            
            // Lecture du chunk
            $chunkData = $sheet->rangeToArray(
                'A' . $startRow . ':' . $sheet->getHighestColumn() . $endRow,
                null, true, true, true
            );

            foreach ($chunkData as $rowIndex => $row) {
                $actualRowNumber = $startRow + $rowIndex - 1;
                $ligneMsg = "Ligne $actualRowNumber: ";
                
                $repere_equipement = trim($row[$colMap['Repère équipement']] ?? '');
                
                if ($repere_equipement === '') {
                    $log[] = ['message' => $ligneMsg . "Repère équipement vide, ignorée.", 'enCours' => false];
                    $errors++;
                    continue;
                }
                
                // Vérification des doublons avec la base existante
                if (Equipement::getByRepere($repere_equipement)) {
                    $log[] = ['message' => $ligneMsg . "Doublon détecté ($repere_equipement), ignorée.", 'enCours' => false];
                    $duplicates++;
                    continue;
                }

                // Prépare les autres champs
                $data = [
                    'code_equipement' => trim($row[$colMap['Code Equipement']] ?? ''),
                    'designation_equipement' => $row[$colMap['Désignation équipement']] ?? '',
                    'repere_equipement' => $repere_equipement,
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
                    'date_creation' => date('Y-m-d')
                ];

                // Insertion en base
                try {
                    if (Equipement::create($data)) {
                        $inserted++;
                        $log[] = ['message' => $ligneMsg . "Ajouté avec succès ($repere_equipement)", 'enCours' => true];
                    } else {
                        $log[] = ['message' => $ligneMsg . "Erreur lors de l'ajout ($repere_equipement)", 'enCours' => false];
                        $errors++;
                    }
                } catch (Exception $e) {
                    $log[] = ['message' => $ligneMsg . "Erreur : " . $e->getMessage(), 'enCours' => false];
                    $errors++;
                }
            }
            
            // Libération mémoire
            unset($chunkData);
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
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
            $log[] = ['message' => $ligneMsg . "Ajouté ($repere_equipement)", 'enCours' => false];
            $inserted++;
        } else {
            $log[] = ['message' => $ligneMsg . "Erreur lors de l'ajout ($repere_equipement)", 'enCours' => false];
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
