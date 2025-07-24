<?php
require_once '../model/Database.php';
require_once '../model/Quantitatif.php';
require_once '../model/Famille.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

$log = [];
$residu = [];
$residuHeaders = null;
$importStats = ['success' => 0, 'warnings' => 0, 'errors' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['quantitatif'])) {

    $file = $_FILES['quantitatif']['tmp_name'];
    $filename = $_FILES['quantitatif']['name'];
    $filesize = $_FILES['quantitatif']['size'];

    $entetesAttendues = [
        'N°',
        'Unité',
        'Quantité',
        'Av. %',
        'Fiches Photos',
        "Fiche d'identité",
        'Fiches 4C',
        'Echaf',
        'Calo',
        'Plan des CND',
        'Plan de platinage',
        'Liste des brides',
        'Fiche Invent. joints',
        'Fiche Invent. boulons',
        'Fiches de serrage'
    ];

    try {
        $pdo = Database::getConnection();

        // Créer la table d'historique si elle n'existe pas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS quantitatif_import_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                date_import DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                lines_imported INT DEFAULT 0,
                lines_errors INT DEFAULT 0,
                status ENUM('success', 'partial', 'error') DEFAULT 'success',
                log_content TEXT,
                user_id INT,
                file_size BIGINT
            ) ENGINE=InnoDB
        ");

        $log[] = "🚀 Début de l'import du fichier: $filename (" . number_format($filesize / 1024, 2) . " KB)";
        $log[] = "📊 Analyse du fichier Excel multi-feuilles...";

        $spreadsheet = IOFactory::load($file);
        $totalSheets = count($spreadsheet->getSheetNames());
        $processedSheets = 0;

        $log[] = "📋 $totalSheets feuille(s) détectée(s) dans le fichier";

        foreach ($spreadsheet->getSheetNames() as $sheetIndex => $sheetName) {
            $log[] = "\n" . str_repeat("=", 50);
            $log[] = "📁 Traitement de la feuille: \"$sheetName\" ($processedSheets/$totalSheets)";

            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray(null, true, true, true);

            $enteteRowNum = null;
            $headers = [];

            // Recherche des en-têtes
            foreach ($rows as $num => $row) {
                $rowTrimmed = array_map(function ($v) {
                    return trim((string)$v);
                }, $row);

                $found = true;
                foreach ($entetesAttendues as $entete) {
                    if (!in_array($entete, $rowTrimmed, true)) {
                        $found = false;
                        break;
                    }
                }

                // Recherche de la colonne repère dynamique
                $repereCol = null;
                foreach ($rowTrimmed as $colName) {
                    if (preg_match('/^Repère/i', $colName)) {
                        $repereCol = $colName;
                        break;
                    }
                }

                if ($found && $repereCol) {
                    $enteteRowNum = $num;
                    $headers = $rowTrimmed;
                    $log[] = "✅ En-têtes trouvées à la ligne $num avec colonne repère: '$repereCol'";
                    break;
                }
            }

            if ($enteteRowNum === null) {
                $log[] = "⚠️  Feuille \"$sheetName\" IGNORÉE (en-têtes manquantes ou colonne repère absente)";
                $importStats['warnings']++;

                // Ajouter au résiduel
                foreach ($rows as $row) {
                    if ($residuHeaders === null) $residuHeaders = array_keys($row);
                    $residu[] = array_values($row);
                }
                $processedSheets++;
                continue;
            }

            // Insertion/mise à jour de la famille
            try {
                Famille::insert($sheetName);
                $log[] = "🏷️  Famille \"$sheetName\" enregistrée";
            } catch (Exception $e) {
                $log[] = "⚠️  Famille \"$sheetName\" existe déjà";
            }

            $imported = 0;
            $errors = 0;
            $lineCount = count($rows) - $enteteRowNum - 1;

            // Recherche du nom de la colonne repère pour cette feuille
            $repereCol = null;
            foreach ($headers as $colName) {
                if (preg_match('/^Repère/i', $colName)) {
                    $repereCol = $colName;
                    break;
                }
            }

            $log[] = "📝 Traitement de $lineCount ligne(s) de données...";

            foreach ($rows as $num => $row) {
                if ($num <= $enteteRowNum) continue;

                $data = [];
                foreach ($headers as $col => $header) {
                    $data[$header] = $row[$col] ?? null;
                }

                // Validation du repère
                if (empty($data[$repereCol])) {
                    $log[] = "❌ Ligne $num IGNORÉE (repère vide)";
                    if ($residuHeaders === null) $residuHeaders = $headers;
                    $residu[] = array_values($row);
                    $errors++;
                    $importStats['errors']++;
                    continue;
                }

                // Tentative d'import
                try {
                    Quantitatif::insert([
                        'famille' => $sheetName,
                        'unite' => $data['Unité'] ?? null,
                        'quantite' => $data['Quantité'] ?? null,
                        'repere' => $data[$repereCol],
                        'autres_colonnes' => $data
                    ]);
                    $imported++;
                    $importStats['success']++;

                    if ($imported % 10 == 0) {
                        $log[] = "⏳ Progression: $imported/$lineCount lignes traitées...";
                    }
                } catch (Exception $e) {
                    $log[] = "❌ Ligne $num ERREUR: " . $e->getMessage() . " (repère: " . $data[$repereCol] . ")";
                    if ($residuHeaders === null) $residuHeaders = $headers;
                    $residu[] = array_values($row);
                    $errors++;
                    $importStats['errors']++;
                }
            }

            $log[] = "✅ Feuille \"$sheetName\" terminée: $imported importée(s), $errors erreur(s)";
            $processedSheets++;
        }

        // Bilan final
        $log[] = "\n" . str_repeat("=", 50);
        $log[] = "📊 BILAN FINAL DE L'IMPORT";
        $log[] = "✅ Lignes importées avec succès: " . $importStats['success'];
        $log[] = "⚠️  Avertissements: " . $importStats['warnings'];
        $log[] = "❌ Erreurs: " . $importStats['errors'];
        $log[] = "📁 Feuilles traitées: $processedSheets/$totalSheets";

        // Génération du fichier résiduel si besoin
        $residu_url = null;
        if (!empty($residu)) {
            $tmpDir = '../tmp';
            if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);
            $residuFile = $tmpDir . '/residu_import_' . date('Ymd_His') . '.csv';
            $f = fopen($residuFile, 'w');
            if ($residuHeaders) fputcsv($f, $residuHeaders, ';');
            foreach ($residu as $ligne) fputcsv($f, $ligne, ';');
            fclose($f);
            $residu_url = str_replace('../', '', $residuFile);
            $log[] = "📄 Fichier résiduel généré: $residu_url";
        }

        $log_txt = implode("\r\n", $log);

        // Génération du fichier log
        $tmpDir = '../tmp';
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);
        $logFile = $tmpDir . '/log_import_' . date('Ymd_His') . '.txt';
        file_put_contents($logFile, $log_txt);
        $log_url = str_replace('../', '', $logFile);

        // Enregistrement dans l'historique
        $status = 'success';
        if ($importStats['errors'] > 0 && $importStats['success'] > 0) {
            $status = 'partial';
        } elseif ($importStats['errors'] > 0 && $importStats['success'] == 0) {
            $status = 'error';
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO quantitatif_import_history 
                (filename, lines_imported, lines_errors, status, log_content, file_size) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $filename,
                $importStats['success'],
                $importStats['errors'],
                $status,
                $log_txt,
                $filesize
            ]);
        } catch (Exception $e) {
            $log[] = "⚠️  Erreur lors de l'enregistrement de l'historique: " . $e->getMessage();
        }

        echo json_encode([
            'success' => true,
            'log_txt' => $log_txt,
            'log_url' => $log_url,
            'residu_url' => $residu_url,
            'stats' => $importStats,
            'message' => "Import terminé: {$importStats['success']} lignes importées, {$importStats['errors']} erreurs"
        ]);
    } catch (Exception $e) {
        $log_txt = "❌ ERREUR CRITIQUE lors de l'import: " . $e->getMessage();
        $log[] = $log_txt;

        echo json_encode([
            'success' => false,
            'log_txt' => implode("\r\n", $log),
            'stats' => $importStats,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode([
    'success' => false,
    'log_txt' => "❌ Aucun fichier reçu ou méthode incorrecte.",
    'error' => 'Aucun fichier uploadé'
]);
