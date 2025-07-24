<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// Augmenter les limites pour les gros fichiers
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '512M');

try {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Aucun fichier uploadé ou erreur lors de l\'upload');
    }

    $uploadedFile = $_FILES['excel_file']['tmp_name'];

    // Utiliser un reader optimisé pour les gros fichiers
    $reader = IOFactory::createReaderForFile($uploadedFile);
    $reader->setReadDataOnly(true);
    $reader->setReadEmptyCells(false);

    $spreadsheet = $reader->load($uploadedFile);    // Recherche de la feuille SPL
    $splSheet = null;
    $worksheetNames = $spreadsheet->getSheetNames();

    foreach ($worksheetNames as $sheetName) {
        if (stripos($sheetName, 'spl') !== false) {
            $splSheet = $spreadsheet->getSheetByName($sheetName);
            break;
        }
    }

    if (!$splSheet) {
        // Si pas de feuille SPL trouvée, utiliser la première feuille
        $splSheet = $spreadsheet->getActiveSheet();
    }

    $highestRow = $splSheet->getHighestRow();
    $pdo = Database::getConnection();

    // Configuration pour l'import par chunks
    $chunkSize = 100; // Traiter 100 lignes à la fois
    $imported = 0;
    $importedNomenclatures = 0;
    $duplicates = 0;
    $duplicatesNomenclatures = 0;
    $errors = [];

    // Préparer les requêtes une seule fois
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM template_spl WHERE code_article = ? AND equipement = ?");
    $checkNomenclatureStmt = $pdo->prepare("SELECT COUNT(*) FROM nomenclatures WHERE code_article = ? AND repere_equipement = ? AND source = 'SPL'");

    $insertStmt = $pdo->prepare("
        INSERT INTO template_spl (
            numero, code_sap, code_article, quantite, designation_article, 
            unite_base, metier, numero_piece_fabricant, fabricant, equipement, import_par
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insertNomenclatureStmt = $pdo->prepare("
        INSERT INTO nomenclatures (
            code_equipement, code_article, repere_equipement, designation_equipement, 
            fabricant, numero_serie_fabricant, designation_article, quantite, 
            unite, metier, date_creation, source
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'SPL')
    ");

    // Commencer la transaction pour de meilleures performances
    $pdo->beginTransaction();

    try {
        // Traitement par chunks pour éviter les timeouts
        for ($startRow = 2; $startRow <= $highestRow; $startRow += $chunkSize) {
            $endRow = min($startRow + $chunkSize - 1, $highestRow);

            for ($row = $startRow; $row <= $endRow; $row++) {
                try {
                    // Lecture des cellules selon la structure fournie
                    $numeroValue = $splSheet->getCell('A' . $row)->getCalculatedValue();
                    $codeSapValue = $splSheet->getCell('B' . $row)->getCalculatedValue();
                    $codeArticleValue = $splSheet->getCell('D' . $row)->getCalculatedValue(); // Colonne D selon structure
                    $quantiteValue = $splSheet->getCell('E' . $row)->getCalculatedValue();
                    $designationValue = $splSheet->getCell('F' . $row)->getCalculatedValue();
                    $uniteValue = $splSheet->getCell('G' . $row)->getCalculatedValue();
                    $metierValue = $splSheet->getCell('H' . $row)->getCalculatedValue();
                    $numeroPieceValue = $splSheet->getCell('I' . $row)->getCalculatedValue();
                    $fabricantValue = $splSheet->getCell('J' . $row)->getCalculatedValue();
                    $equipementValue = $splSheet->getCell('K' . $row)->getCalculatedValue();

                    $data = [
                        'numero' => $numeroValue,
                        'code_sap' => $codeSapValue,
                        'code_article' => $codeArticleValue,
                        'quantite' => $quantiteValue,
                        'designation_article' => $designationValue,
                        'unite_base' => $uniteValue,
                        'metier' => $metierValue,
                        'numero_piece_fabricant' => $numeroPieceValue,
                        'fabricant' => $fabricantValue,
                        'equipement' => $equipementValue,
                        'import_par' => $_SESSION['user_id'] ?? null
                    ];

                    // Vérification des champs obligatoires
                    if (empty($data['code_article']) || empty($data['equipement'])) {
                        continue; // Ignorer les lignes vides
                    }

                    // Traitement des équipements multiples (séparés par /)
                    $equipements = preg_split('/\s*\/\s*/', $data['equipement']);

                    foreach ($equipements as $equipement) {
                        $equipement = trim($equipement);
                        if (empty($equipement)) continue;

                        // === INSERTION DANS TEMPLATE_SPL ===
                        // Vérification des doublons template_spl
                        $checkStmt->execute([$data['code_article'], $equipement]);

                        if ($checkStmt->fetchColumn() == 0) {
                            // Insertion dans template_spl
                            $insertStmt->execute([
                                $data['numero'],
                                $data['code_sap'],
                                $data['code_article'],
                                $data['quantite'],
                                $data['designation_article'],
                                $data['unite_base'],
                                $data['metier'],
                                $data['numero_piece_fabricant'],
                                $data['fabricant'],
                                $equipement,
                                $data['import_par']
                            ]);
                            $imported++;
                        } else {
                            $duplicates++;
                        }

                        // === INSERTION DANS NOMENCLATURES ===
                        // Vérification des doublons nomenclatures (même repère + même code article + source SPL)
                        $checkNomenclatureStmt->execute([$data['code_article'], $equipement]);

                        if ($checkNomenclatureStmt->fetchColumn() == 0) {
                            // Insertion dans nomenclatures avec mapping des champs
                            $insertNomenclatureStmt->execute([
                                $equipement,                        // code_equipement = repère
                                $data['code_article'],              // code_article
                                $equipement,                        // repere_equipement = même que code_equipement
                                'SPL - ' . $data['metier'],         // designation_equipement = SPL + métier
                                $data['fabricant'],                 // fabricant
                                $data['numero_piece_fabricant'],    // numero_serie_fabricant
                                $data['designation_article'],       // designation_article
                                $data['quantite'],                  // quantite
                                $data['unite_base'],               // unite
                                $data['metier']                    // metier
                            ]);
                            $importedNomenclatures++;
                        } else {
                            $duplicatesNomenclatures++;
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = "Ligne $row: " . $e->getMessage();
                    // Continuer même en cas d'erreur sur une ligne
                }
            }

            // Libérer la mémoire à chaque chunk
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        // Valider la transaction
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

    // Nettoyer la mémoire
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);

    $response = [
        'success' => true,
        'message' => "Import terminé avec succès",
        'stats' => [
            'imported' => $imported,
            'imported_nomenclatures' => $importedNomenclatures,
            'duplicates' => $duplicates,
            'duplicates_nomenclatures' => $duplicatesNomenclatures,
            'errors' => count($errors),
            'total_processed' => $highestRow - 1
        ],
        'errors' => array_slice($errors, 0, 10) // Limiter les erreurs affichées
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'import: ' . $e->getMessage()
    ]);
}
