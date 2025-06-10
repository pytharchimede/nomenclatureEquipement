<?php
set_time_limit(600); // 10 minutes

require_once '../model/Database.php';
require_once '../model/Nomenclature.php';
require_once '../model/Equipement.php';
require_once '../model/Article.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'errors' => ["Erreur lors de l'upload du fichier."]]);
    exit;
}

$tmpFile = $_FILES['excel_file']['tmp_name'];
$spreadsheet = IOFactory::load($tmpFile);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

// Supposons que la première ligne contient les entêtes
$header = array_shift($rows);

// Mapping adapté à ton fichier Excel
$map = [
    'A' => 'code_equipement',
    'B' => 'repere_equipement',
    'C' => 'designation_equipement',
    'D' => 'fabricant',
    'E' => 'type',
    'F' => 'numero_serie_fabricant',
    'G' => 'code_article',
    'H' => 'designation_article',
    'I' => 'numero_poste',
    'J' => 'quantite',
    'K' => 'unite',
    'L' => 'poste_technique',
    'M' => 'metier',
    'N' => 'date_creation',
    'O' => 'source'
];

$imported = 0;
$duplicates = [];
$errors = [];

foreach ($rows as $row) {
    $data = [];
    foreach ($map as $col => $field) {
        $data[$field] = isset($row[$col]) ? trim($row[$col]) : null;
    }

    $ligneInfo = [
        'code_equipement' => $data['code_equipement'],
        'code_article' => $data['code_article'],
        'details' => $data
    ];

    // Cas 1 & 2 : gestion des absences de repere_equipement ou code_article
    if (empty($data['repere_equipement']) || empty($data['code_article'])) {
        $equipementExiste = !empty($data['repere_equipement']) && Equipement::getByRepere($data['repere_equipement']);
        $articleExiste = !empty($data['code_article']) && Article::exists($data['code_article']);

        // Si les deux n'existent pas, ignorer la ligne
        if (empty($data['repere_equipement']) && empty($data['code_article'])) {
            $ligneInfo['message'] = "Ligne ignorée : repère équipement et code article manquants";
            $errors[] = $ligneInfo;
            continue;
        }

        // Si repere_equipement existe dans nomenclature mais pas dans Equipement, l'ajouter
        if (!empty($data['repere_equipement']) && !Equipement::getByRepere($data['repere_equipement'])) {
            Equipement::add(['repere_equipement' => $data['repere_equipement']]);
            $ligneInfo['message'] = "Ajouté uniquement dans Equipement (nomenclature ignorée)";
            $errors[] = $ligneInfo;
            continue;
        }

        // Si code_article existe dans nomenclature mais pas dans Article, l'ajouter
        if (!empty($data['code_article']) && !Article::exists($data['code_article'])) {
            Article::add(['code_article' => $data['code_article']]);
            $ligneInfo['message'] = "Ajouté uniquement dans Article (nomenclature ignorée)";
            $errors[] = $ligneInfo;
            continue;
        }

        // Sinon, ignorer la ligne
        $ligneInfo['message'] = "Ligne ignorée : repère équipement ou code article manquant";
        $errors[] = $ligneInfo;
        continue;
    }

    // Formatage de la date (accepte 23/2/2015 ou 23/02/2015)
    if (!empty($data['date_creation'])) {
        $date = date_create_from_format('d/m/Y', $data['date_creation']);
        if (!$date) {
            $date = date_create_from_format('j/n/Y', $data['date_creation']);
        }
        if ($date) {
            $data['date_creation'] = $date->format('Y-m-d');
        } else {
            $data['date_creation'] = null;
        }
    }

    // Vérifie doublon (repere_equipement + code_article)
    if (Nomenclature::existsByRepereArticle($data['repere_equipement'], $data['code_article'])) {
        $duplicates[] = $data['repere_equipement'] . ' / ' . $data['code_article'];
        continue;
    }

    // Ajoute l'équipement s'il n'existe pas
    if (!Equipement::getByRepere($data['repere_equipement'])) {
        Equipement::add(['repere_equipement' => $data['repere_equipement']]);
    }
    // Ajoute l'article s'il n'existe pas
    if (!Article::exists($data['code_article'])) {
        Article::add(['code_article' => $data['code_article']]);
    }
    // Ajout en base
    if (Nomenclature::add($data)) {
        $imported++;
    } else {
        $ligneInfo['message'] = "Erreur lors de l'ajout de la nomenclature";
        $errors[] = $ligneInfo;
    }
}

// Génération du rapport d'importation
$residualRows = [];
foreach ($errors as $err) {
    $residualRows[] = $err['details'] ?? [];
}

echo json_encode([
    'success' => true,
    'imported' => $imported,
    'duplicates' => $duplicates,
    'errors' => $errors, // rapport détaillé
    'residual' => $residualRows // lignes non importées
]);
