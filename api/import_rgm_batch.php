<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../model/Database.php';
require_once '../model/Nomenclature.php';

// Augmenter le temps d'exécution et la mémoire
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '512M');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Debug: Log des informations de requête
    error_log("DEBUG BATCH: Méthode REQUEST: " . $_SERVER['REQUEST_METHOD']);
    error_log("DEBUG BATCH: FILES reçus: " . json_encode($_FILES));

    // Vérification du fichier uploadé
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'Erreur lors de l\'upload du fichier';
        if (isset($_FILES['file'])) {
            $errorMsg .= ' - Code erreur: ' . $_FILES['file']['error'];
        } else {
            $errorMsg .= ' - Aucun fichier reçu';
        }
        throw new Exception($errorMsg);
    }

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $tmpName = $file['tmp_name'];

    // Vérification de l'extension
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['csv', 'xlsx', 'xls'];

    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Format de fichier non supporté. Utilisez CSV ou Excel.');
    }

    // Traitement selon le type de fichier avec progression
    if ($extension === 'csv') {
        // Pour l'instant, rediriger vers Excel pour les gros volumes
        throw new Exception('Pour les gros volumes, utilisez le format Excel (.xlsx)');
    } else {
        $result = importFromExcelBatch($tmpName);
    }

    // Réponse de succès
    echo json_encode([
        'success' => true,
        'imported' => $result['imported'],
        'updated' => $result['updated'],
        'skipped' => $result['skipped'],
        'errors' => $result['errors'],
        'total_processed' => $result['total_processed'],
        'processing_time' => $result['processing_time'],
        'message' => "Import RGM terminé avec succès!"
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'imported' => 0
    ]);
}

function importFromExcelBatch($filePath)
{
    $startTime = microtime(true);

    // Vérification si PhpSpreadsheet est disponible
    if (!file_exists('../vendor/autoload.php')) {
        throw new Exception('PhpSpreadsheet non installé. Utilisez le format CSV.');
    }

    require_once '../vendor/autoload.php';

    try {
        $pdo = Database::getConnection();

        // Préparer les index pour optimiser les performances
        createIndexesIfNotExists($pdo);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $totalProcessed = 0;

        // Charger toutes les données existantes en mémoire pour éviter les requêtes répétées
        $existingData = loadExistingRgmData($pdo);

        // Batch d'insertion
        $batchData = [];
        $batchSize = 100; // Traiter par lots de 100

        error_log("DEBUG BATCH: Début traitement de {$highestRow} lignes");

        // Commencer à la ligne 2 (ignorer les en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            $totalProcessed++;

            try {
                $repereEquipement = trim($worksheet->getCell('A' . $row)->getValue());
                $codeArticle = trim($worksheet->getCell('B' . $row)->getValue());
                $designationArticle = trim($worksheet->getCell('C' . $row)->getValue());
                $quantite = intval($worksheet->getCell('D' . $row)->getValue());
                $unite = trim($worksheet->getCell('E' . $row)->getValue());

                // Validation des données obligatoires
                if (empty($repereEquipement) || empty($codeArticle)) {
                    $errors[] = "Ligne {$row}: Repère équipement et code article obligatoires";
                    $skippedCount++;
                    continue;
                }

                $key = $repereEquipement . '|' . $codeArticle;

                if (isset($existingData[$key])) {
                    // Mise à jour
                    updateRgmEntryBatch($pdo, $existingData[$key]['id'], [
                        'designation_article' => $designationArticle,
                        'quantite' => $quantite,
                        'unite' => $unite,
                        'repere_equipement' => $repereEquipement,
                        'code_article' => $codeArticle
                    ]);
                    $updatedCount++;
                } else {
                    // Nouvelle entrée - ajouter au batch
                    $batchData[] = [
                        'repere_equipement' => $repereEquipement,
                        'code_article' => $codeArticle,
                        'designation_article' => $designationArticle,
                        'quantite' => $quantite,
                        'unite' => $unite
                    ];

                    // Traiter le batch quand il atteint la taille limite
                    if (count($batchData) >= $batchSize) {
                        $imported = insertBatchRgmEntries($pdo, $batchData);
                        $importedCount += $imported;
                        $batchData = []; // Vider le batch
                    }
                }

                // Log du progrès toutes les 100 lignes
                if ($row % 100 == 0) {
                    error_log("DEBUG BATCH: Ligne {$row}/{$highestRow} - Importés: {$importedCount}, Mis à jour: {$updatedCount}");
                }
            } catch (Exception $e) {
                $errors[] = "Ligne {$row}: " . $e->getMessage();
                $skippedCount++;
            }
        }

        // Traiter le dernier batch s'il reste des données
        if (!empty($batchData)) {
            $imported = insertBatchRgmEntries($pdo, $batchData);
            $importedCount += $imported;
        }

        $endTime = microtime(true);
        $processingTime = round($endTime - $startTime, 2);

        error_log("DEBUG BATCH: Terminé - Importés: {$importedCount}, Mis à jour: {$updatedCount}, Erreurs: " . count($errors));

        return [
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
            'total_processed' => $totalProcessed,
            'processing_time' => $processingTime
        ];
    } catch (Exception $e) {
        throw new Exception('Erreur lors de la lecture du fichier Excel: ' . $e->getMessage());
    }
}

function createIndexesIfNotExists($pdo)
{
    try {
        // Index pour rgm_synthese
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_rgm_repere_code ON rgm_synthese (repere_equipement, code_article)");

        // Index pour nomenclatures  
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_nomenclatures_repere_code_source ON nomenclatures (repere_equipement, code_article, source)");

        // Index pour articles
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_articles_code ON articles (code_article)");
    } catch (Exception $e) {
        error_log("Erreur création index: " . $e->getMessage());
    }
}

function loadExistingRgmData($pdo)
{
    $stmt = $pdo->query("SELECT id, repere_equipement, code_article FROM rgm_synthese");
    $existingData = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = $row['repere_equipement'] . '|' . $row['code_article'];
        $existingData[$key] = $row;
    }

    return $existingData;
}

function updateRgmEntryBatch($pdo, $id, $data)
{
    static $updateStmt = null;

    if ($updateStmt === null) {
        $updateStmt = $pdo->prepare("
            UPDATE rgm_synthese SET 
                designation_article = ?,
                quantite = ?,
                unite = ?,
                date_import = NOW()
            WHERE id = ?
        ");
    }

    $updateStmt->execute([
        $data['designation_article'],
        $data['quantite'],
        $data['unite'],
        $id
    ]);

    // Mise à jour aussi dans nomenclatures
    static $updateNomenclatureStmt = null;

    if ($updateNomenclatureStmt === null) {
        $updateNomenclatureStmt = $pdo->prepare("
            UPDATE nomenclatures SET 
                designation_article = ?,
                quantite = ?,
                unite = ?,
                date_creation = NOW()
            WHERE repere_equipement = ? AND code_article = ? AND source = 'RGM'
        ");
    }

    $updateNomenclatureStmt->execute([
        $data['designation_article'],
        $data['quantite'],
        $data['unite'],
        $data['repere_equipement'],
        $data['code_article']
    ]);
}

function insertBatchRgmEntries($pdo, $batchData)
{
    if (empty($batchData)) return 0;

    try {
        $pdo->beginTransaction();

        // Préparer les requêtes
        $rgmStmt = $pdo->prepare("
            INSERT INTO rgm_synthese (
                repere_equipement, code_article, designation_article, 
                quantite, unite, source, date_import
            ) VALUES (?, ?, ?, ?, ?, 'RGM', NOW())
        ");

        $nomenclatureStmt = $pdo->prepare("
            INSERT INTO nomenclatures (
                repere_equipement, code_article, designation_article, 
                quantite, unite, source, date_creation
            ) VALUES (?, ?, ?, ?, ?, 'RGM', NOW())
        ");

        $articleCheckStmt = $pdo->prepare("SELECT id FROM articles WHERE code_article = ?");
        $articleInsertStmt = $pdo->prepare("
            INSERT INTO articles (code_article, designation_article, date_creation, cree_par) 
            VALUES (?, ?, NOW(), 'RGM_IMPORT')
        ");

        $importedCount = 0;

        foreach ($batchData as $data) {
            // Vérifier/créer l'article
            $articleCheckStmt->execute([$data['code_article']]);
            if (!$articleCheckStmt->fetch()) {
                $articleInsertStmt->execute([
                    $data['code_article'],
                    $data['designation_article']
                ]);
            }

            // Insérer dans rgm_synthese
            $rgmStmt->execute([
                $data['repere_equipement'],
                $data['code_article'],
                $data['designation_article'],
                $data['quantite'],
                $data['unite']
            ]);

            // Insérer dans nomenclatures
            $nomenclatureStmt->execute([
                $data['repere_equipement'],
                $data['code_article'],
                $data['designation_article'],
                $data['quantite'],
                $data['unite']
            ]);

            $importedCount++;
        }

        $pdo->commit();
        return $importedCount;
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Erreur insertBatchRgmEntries: " . $e->getMessage());
        throw $e;
    }
}

// Note: Function importFromCSVBatch would be implemented here if needed for CSV batch processing
