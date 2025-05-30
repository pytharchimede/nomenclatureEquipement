<?php
require_once '../model/Database.php';
require_once '../model/Quantitatif.php';
require_once '../model/Famille.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Récupère toutes les familles
$familles = Famille::getAll(); // À adapter selon ta méthode
$colonnesAttendues = [
    'N°',
    'Unité',
    'Quantité',
    // Colonne repère dynamique
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

$spreadsheet = new Spreadsheet();

foreach ($familles as $famille) {
    // Récupère les quantitatifs de la famille
    $quantitatifs = Quantitatif::getByFamille($famille['nom']); // À adapter selon ta méthode

    // Détecte la colonne repère dynamique
    $colRepere = null;
    if (!empty($quantitatifs)) {
        $autres = json_decode($quantitatifs[0]['autres_colonnes'], true);
        foreach ($autres as $k => $v) {
            if (preg_match('/^Repère/i', $k)) {
                $colRepere = $k;
                break;
            }
        }
    }
    // Construit l'entête pour cette famille
    $entetes = $colonnesAttendues;
    if ($colRepere && !in_array($colRepere, $entetes)) {
        array_splice($entetes, 3, 0, $colRepere); // Ajoute après Quantité
    }

    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle(substr($famille['nom'], 0, 31)); // Excel limite à 31 caractères

    // Écrit l'entête
    $sheet->fromArray($entetes, null, 'A1');

    // Écrit les données
    $rowNum = 2;
    foreach ($quantitatifs as $q) {
        $autres = json_decode($q['autres_colonnes'], true);
        $ligne = [];
        foreach ($entetes as $col) {
            $ligne[] = $autres[$col] ?? '';
        }
        $sheet->fromArray($ligne, null, 'A' . $rowNum++);
    }
}

// Supprime la première feuille vide créée par défaut
$spreadsheet->removeSheetByIndex(0);

// Envoie le fichier Excel au navigateur
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="quantitatif_export.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
