<?php
set_time_limit(600); // 10 minutes pour traitement des gros fichiers

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Nomenclature.php';
require_once __DIR__ . '/../model/Equipement.php';
require_once __DIR__ . '/../model/Article.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors de l\'upload du fichier Excel');
    }

    $tmpFile = $_FILES['excel_file']['tmp_name'];
    $fileName = $_FILES['excel_file']['name'];

    // Validation du type de fichier
    $allowedExtensions = ['xls', 'xlsx'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception('Format de fichier non supporté. Utilisez .xls ou .xlsx');
    }

    // Chargement du fichier Excel
    $spreadsheet = IOFactory::load($tmpFile);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    if (empty($rows)) {
        throw new Exception('Le fichier Excel est vide');
    }

    // La première ligne contient les entêtes
    $header = array_shift($rows);

    // Mapping des colonnes (corrigé selon votre structure Excel)
    $columnMap = [
        'A' => 'code_equipement',        // Code Equipement
        'B' => 'repere_equipement',      // Repère équipement
        'C' => 'designation_equipement', // Désignation équipement
        'D' => 'fabricant',              // Fabricant
        'E' => 'type',                   // Type
        'F' => 'numero_serie_fabricant', // N° série fabr.
        'G' => 'code_article',           // Code Article
        'H' => 'designation_article',    // Désignation article
        'I' => 'numero_poste',           // N° Poste
        'J' => 'quantite',               // Quantité installée
        'K' => 'unite',                  // Unité de quantité
        'L' => 'poste_technique',        // Poste technique
        'M' => 'metier',                 // Métier
        'N' => 'date_creation',          // Créé le
        'O' => 'source'                  // Source
    ];

    $stats = [
        'total' => count($rows),
        'imported' => 0,
        'duplicates' => 0,
        'errors' => 0,
        'skipped' => 0
    ];

    $details = [
        'duplicates' => [],
        'errors' => [],
        'skipped' => []
    ];

    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    $lineNumber = 1; // Commence à 1 car on a enlevé l'en-tête
    $debugInfo = []; // Pour debug des premières lignes

    foreach ($rows as $row) {
        $lineNumber++;

        try {
            // Extraction des données selon le mapping
            $data = [];
            foreach ($columnMap as $col => $field) {
                $value = isset($row[$col]) ? trim($row[$col]) : null;
                $data[$field] = empty($value) ? null : $value;
            }

            // Debug : capturer les 3 premières lignes
            if ($lineNumber <= 4) {
                $debugInfo[] = [
                    'line_number' => $lineNumber,
                    'raw_row' => $row,
                    'mapped_data' => $data
                ];
            }

            // Validation des champs obligatoires
            if (empty($data['repere_equipement']) && empty($data['code_article'])) {
                $details['skipped'][] = [
                    'line' => $lineNumber,
                    'reason' => 'Repère équipement et code article manquants',
                    'data' => $data
                ];
                $stats['skipped']++;
                continue;
            }

            // Validation et formatage de la date
            if (!empty($data['date_creation'])) {
                $date = null;

                // Tentative de parsing de différents formats
                $dateFormats = ['d/m/Y', 'j/n/Y', 'Y-m-d', 'd-m-Y'];
                foreach ($dateFormats as $format) {
                    $date = DateTime::createFromFormat($format, $data['date_creation']);
                    if ($date !== false) break;
                }

                if ($date !== false) {
                    $data['date_creation'] = $date->format('Y-m-d');
                } else {
                    $data['date_creation'] = date('Y-m-d'); // Date actuelle par défaut
                }
            } else {
                $data['date_creation'] = date('Y-m-d');
            }

            // Validation de la quantité
            if (!empty($data['quantite'])) {
                if (!is_numeric($data['quantite'])) {
                    $data['quantite'] = 1; // Valeur par défaut
                }
            } else {
                $data['quantite'] = 1;
            }

            // Vérification des doublons (repere_equipement + code_article)
            if (!empty($data['repere_equipement']) && !empty($data['code_article'])) {
                $existingNomenclatures = Nomenclature::getByRepereArticle($data['repere_equipement'], $data['code_article']);

                if (!empty($existingNomenclatures)) {
                    // Stocker le doublon dans la table spécialisée
                    stockerDoublonImport($data, $filename, $lineNumber, $existingNomenclatures, $pdo);

                    $details['duplicates'][] = [
                        'line' => $lineNumber,
                        'repere_equipement' => $data['repere_equipement'],
                        'code_article' => $data['code_article'],
                        'data' => $data,
                        'conflits' => count($existingNomenclatures) . ' nomenclature(s) existante(s)'
                    ];
                    $stats['duplicates']++;
                    continue;
                }
            }

            // Ajout automatique des équipements/articles s'ils n'existent pas
            if (!empty($data['repere_equipement']) && !Equipement::getByRepere($data['repere_equipement'])) {
                $equipementData = [
                    'repere_equipement' => $data['repere_equipement'],
                    'designation_equipement' => $data['designation_equipement'] ?? null,
                    'fabricant' => $data['fabricant'] ?? null,
                    'type' => $data['type'] ?? null,
                    'numero_serie_fabricant' => $data['numero_serie_fabricant'] ?? null
                ];
                Equipement::add($equipementData);
            }

            if (!empty($data['code_article']) && !Article::exists($data['code_article'])) {
                $articleData = [
                    'code_article' => $data['code_article'],
                    'designation_article' => $data['designation_article'] ?? null
                ];
                Article::add($articleData);
            }

            // Ajout de la nomenclature
            if (Nomenclature::add($data)) {
                $stats['imported']++;
            } else {
                $details['errors'][] = [
                    'line' => $lineNumber,
                    'reason' => 'Erreur lors de l\'insertion en base de données',
                    'data' => $data
                ];
                $stats['errors']++;
            }
        } catch (Exception $e) {
            $details['errors'][] = [
                'line' => $lineNumber,
                'reason' => $e->getMessage(),
                'data' => $data ?? []
            ];
            $stats['errors']++;
        }
    }

    $pdo->commit();

    // Nettoyage du fichier temporaire
    unlink($tmpFile);

    echo json_encode([
        'success' => true,
        'message' => 'Importation terminée avec succès',
        'stats' => $stats,
        'details' => $details,
        'filename' => $fileName,
        'debug' => $debugInfo  // Infos de debug pour vérifier le mapping
    ]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }

    error_log("Erreur importation nomenclatures: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}

/**
 * Stocke un doublon détecté lors de l'import dans la table dédiée
 */
function stockerDoublonImport($data, $filename, $lineNumber, $existingNomenclatures, $pdo)
{
    try {
        // Préparation des détails du conflit
        $conflits = [];
        $nomenclatureIds = [];

        foreach ($existingNomenclatures as $existing) {
            $conflits[] = [
                'id' => $existing['id'],
                'designation_equipement' => $existing['designation_equipement'],
                'designation_article' => $existing['designation_article'],
                'fabricant' => $existing['fabricant'],
                'source' => $existing['source'],
                'date_creation' => $existing['date_creation']
            ];
            $nomenclatureIds[] = $existing['id'];
        }

        // Détermination du type de doublon
        $raisonRejet = 'doublon_repere_article';

        // Vérification si c'est un doublon exact (tous les champs identiques)
        foreach ($existingNomenclatures as $existing) {
            $isDuplicate = true;
            $fieldsToCheck = ['designation_equipement', 'fabricant', 'designation_article', 'quantite', 'unite'];

            foreach ($fieldsToCheck as $field) {
                if (trim($data[$field] ?? '') !== trim($existing[$field] ?? '')) {
                    $isDuplicate = false;
                    break;
                }
            }

            if ($isDuplicate) {
                $raisonRejet = 'doublon_exact';
                break;
            }
        }

        // Insertion dans la table des doublons
        $insertQuery = "
            INSERT INTO nomenclatures_doublons_import (
                code_equipement, code_article, repere_equipement, designation_equipement,
                fabricant, type, numero_serie_fabricant, designation_article, numero_poste,
                quantite, unite, poste_technique, metier, source,
                fichier_import, ligne_import, raison_rejet, details_conflit,
                nomenclatures_conflits, statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')
        ";

        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([
            $data['code_equipement'],
            $data['code_article'],
            $data['repere_equipement'],
            $data['designation_equipement'],
            $data['fabricant'],
            $data['type'],
            $data['numero_serie_fabricant'],
            $data['designation_article'],
            $data['numero_poste'],
            $data['quantite'],
            $data['unite'],
            $data['poste_technique'],
            $data['metier'],
            $data['source'],
            $filename,
            $lineNumber,
            $raisonRejet,
            json_encode($conflits, JSON_UNESCAPED_UNICODE),
            json_encode($nomenclatureIds)
        ]);

        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Erreur stockage doublon import: " . $e->getMessage());
        return false;
    }
}
