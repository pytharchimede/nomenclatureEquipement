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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['quantitatif'])) {

    $file = $_FILES['quantitatif']['tmp_name'];
    $entetesAttendues = [
        'N°',
        'Unité',
        'Quantité',
        // On ne met plus de colonne repère fixe ici
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
        $spreadsheet = IOFactory::load($file);
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray(null, true, true, true);

            $enteteRowNum = null;
            $headers = [];
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
                // On cherche aussi une colonne repère dynamique
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
                    break;
                }
            }

            if ($enteteRowNum === null) {
                $log[] = "Famille \"$sheetName\" ignorée (entêtes non trouvées ou colonne repère absente)";
                // Ajoute toutes les lignes de cette feuille au résiduel
                foreach ($rows as $row) {
                    if ($residuHeaders === null) $residuHeaders = array_keys($row);
                    $residu[] = array_values($row);
                }
                continue;
            }

            $log[] = "Début importation de la famille \"$sheetName\"";
            Famille::insert($sheetName);
            $imported = 0;
            $errors = 0;
            // Recherche du nom de la colonne repère pour cette feuille
            $repereCol = null;
            foreach ($headers as $colName) {
                if (preg_match('/^Repère/i', $colName)) {
                    $repereCol = $colName;
                    break;
                }
            }
            foreach ($rows as $num => $row) {
                if ($num <= $enteteRowNum) continue;
                $data = [];
                foreach ($headers as $col => $header) {
                    $data[$header] = $row[$col] ?? null;
                }
                // Si aucun repère, ligne ignorée et ajoutée au résiduel
                if (empty($data[$repereCol])) {
                    $log[] = "[$sheetName] Ligne $num ignorée (aucun repère)";
                    if ($residuHeaders === null) $residuHeaders = $headers;
                    $residu[] = array_values($row);
                    $errors++;
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
                    $log[] = "[$sheetName] Ligne $num : ajout réussi (repère : " . $data[$repereCol] . ")";
                } catch (Exception $e) {
                    $log[] = "[$sheetName] Ligne $num : échec (" . $e->getMessage() . ")";
                    if ($residuHeaders === null) $residuHeaders = $headers;
                    $residu[] = array_values($row);
                    $errors++;
                }
            }
            $log[] = "Famille \"$sheetName\" importée : $imported ligne(s), $errors ignorée(s)/en erreur";
        }

        // Génération du fichier résiduel si besoin
        $residu_url = null;
        if (!empty($residu)) {
            $tmpDir = '../tmp';
            if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);
            $residuFile = $tmpDir . '/residu_import_' . date('Ymd_His') . '.csv';
            $f = fopen($residuFile, 'w');
            if ($residuHeaders) fputcsv($f, $residuHeaders);
            foreach ($residu as $ligne) fputcsv($f, $ligne);
            fclose($f);
            $residu_url = str_replace('../', '', $residuFile); // Pour accès web
        }

        $log_txt = implode("\r\n", $log);

        // Génération du fichier log.txt
        $logFile = '../tmp/log_import_' . date('Ymd_His') . '.txt';
        file_put_contents($logFile, $log_txt);
        $log_url = str_replace('../', '', $logFile);

        echo json_encode([
            'success' => true,
            'log_txt' => $log_txt,
            'log_url' => $log_url,
            'residu_url' => $residu_url
        ]);
    } catch (Exception $e) {
        $log_txt = "Erreur lors de l'import : " . $e->getMessage();
        echo json_encode(['success' => false, 'log_txt' => $log_txt]);
    }
    exit;
}
echo json_encode(['success' => false, 'log_txt' => "Aucun fichier reçu."]);
