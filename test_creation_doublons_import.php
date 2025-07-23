<?php

/**
 * Script de test pour créer des doublons d'importation
 * Permet de tester le système de gestion des doublons d'import
 */
require_once 'includes/auth.php';
require_once 'model/Database.php';

// Configuration
$testFichierImport = 'test_nomenclatures_doublons_' . date('Y-m-d_H-i-s') . '.xlsx';
$nomUtilisateur = $_SESSION['username'] ?? 'admin';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Test Création Doublons Import</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href='plugins/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .result { margin: 1rem 0; padding: 1rem; border-radius: 4px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container mt-4'>
        <h1>🧪 Test Création Doublons Import</h1>
        <div class='alert alert-info'>
            <strong>Objectif :</strong> Créer des données de test dans la table nomenclatures_doublons_import 
            pour valider le système de gestion des doublons d'importation.
        </div>";

try {
    $database = new Database();
    $conn = $database->getConnection();

    echo "<div class='result info'>
        <h3>🔍 Vérification de la table nomenclatures_doublons_import</h3>";

    // Vérifier la structure de la table
    $checkTable = $conn->query("SHOW TABLES LIKE 'nomenclatures_doublons_import'");
    if ($checkTable->rowCount() === 0) {
        echo "<div class='alert alert-danger'>❌ La table nomenclatures_doublons_import n'existe pas. Veuillez d'abord exécuter create_doublons_import_table.php</div>";
        exit;
    }

    $columns = $conn->query("SHOW COLUMNS FROM nomenclatures_doublons_import");
    echo "<p>✅ Table existe avec " . $columns->rowCount() . " colonnes</p>";
    echo "</div>";

    // Nettoyer les anciennes données de test
    echo "<div class='result warning'>
        <h3>🧹 Nettoyage des données de test existantes</h3>";

    $deleteTest = $conn->prepare("DELETE FROM nomenclatures_doublons_import WHERE fichier_import LIKE 'test_%'");
    $deleteTest->execute();
    $deletedCount = $deleteTest->rowCount();
    echo "<p>🗑️ Supprimé $deletedCount anciens enregistrements de test</p>";
    echo "</div>";

    // Création des doublons de test
    echo "<div class='result info'>
        <h3>📝 Création des doublons de test</h3>";

    $doublonsTest = [
        [
            'type_doublon' => 'doublon_exact',
            'base' => [
                'code_equipement' => '10001001',
                'code_article' => '5920010001',
                'repere_equipement' => 'REP001',
                'designation_equipement' => 'VANNE PRINCIPALE',
                'fabricant' => 'VALFAB',
                'type' => 'VANNE',
                'numero_serie_fabricant' => 'SN123456',
                'designation_article' => 'VANNE 3/4 POUCE',
                'numero_poste' => 'P001',
                'quantite' => 2,
                'unite' => 'PCE',
                'poste_technique' => 'CIRCUIT PRINCIPAL',
                'metier' => 'MECANIQUE',
                'source' => 'IMPORT'
            ],
            'conflict' => [
                'id_nomenclature' => 1,
                'differences' => ['quantite' => ['base' => 2, 'import' => 3]],
                'score_similitude' => 95.5
            ]
        ],
        [
            'type_doublon' => 'doublon_repere_article',
            'base' => [
                'code_equipement' => '10001002',
                'code_article' => '5920010002',
                'repere_equipement' => 'REP002',
                'designation_equipement' => 'POMPE ALIMENTATION',
                'fabricant' => 'POMPFAB',
                'type' => 'POMPE',
                'numero_serie_fabricant' => 'SN789012',
                'designation_article' => 'POMPE CENTRIFUGE 10KW',
                'numero_poste' => 'P002',
                'quantite' => 1,
                'unite' => 'PCE',
                'poste_technique' => 'CIRCUIT POMPAGE',
                'metier' => 'MECANIQUE',
                'source' => 'IMPORT'
            ],
            'conflict' => [
                'id_nomenclature' => 2,
                'differences' => [
                    'designation_equipement' => ['base' => 'POMPE ALIMENTATION', 'import' => 'POMPE ALIMENTATION MODIFIEE'],
                    'fabricant' => ['base' => 'POMPFAB', 'import' => 'POMPFAB_NEW']
                ],
                'score_similitude' => 87.3
            ]
        ],
        [
            'type_doublon' => 'doublon_suspect',
            'base' => [
                'code_equipement' => '10001003',
                'code_article' => '5920010003',
                'repere_equipement' => 'REP003',
                'designation_equipement' => 'CAPTEUR TEMPERATURE',
                'fabricant' => 'SENSORFAB',
                'type' => 'CAPTEUR',
                'numero_serie_fabricant' => 'SN345678',
                'designation_article' => 'CAPTEUR PT100',
                'numero_poste' => 'P003',
                'quantite' => 4,
                'unite' => 'PCE',
                'poste_technique' => 'MESURE TEMPERATURE',
                'metier' => 'INSTRUMENTATION',
                'source' => 'IMPORT'
            ],
            'conflict' => [
                'id_nomenclature' => 3,
                'differences' => [
                    'code_equipement' => ['base' => '10001003', 'import' => '10001003A'],
                    'type' => ['base' => 'CAPTEUR', 'import' => 'SONDE'],
                    'quantite' => ['base' => 4, 'import' => 5]
                ],
                'score_similitude' => 78.9
            ]
        ]
    ];

    $insertQuery = "INSERT INTO nomenclatures_doublons_import (
        code_equipement, code_article, repere_equipement, designation_equipement,
        fabricant, type, numero_serie_fabricant, designation_article,
        numero_poste, quantite, unite, poste_technique, metier, source,
        fichier_import, ligne_import, date_import,
        raison_rejet, details_conflit, statut
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insertQuery);
    $ligneImport = 2; // Commence à ligne 2 (après header)

    foreach ($doublonsTest as $doublon) {
        $base = $doublon['base'];
        $conflict = $doublon['conflict'];

        $success = $stmt->execute([
            $base['code_equipement'],
            $base['code_article'],
            $base['repere_equipement'],
            $base['designation_equipement'],
            $base['fabricant'],
            $base['type'],
            $base['numero_serie_fabricant'],
            $base['designation_article'],
            $base['numero_poste'],
            $base['quantite'],
            $base['unite'],
            $base['poste_technique'],
            $base['metier'],
            $base['source'],
            $testFichierImport,
            $ligneImport,
            date('Y-m-d H:i:s'),
            $doublon['type_doublon'],
            json_encode($conflict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'en_attente'
        ]);

        if ($success) {
            echo "<p>✅ Doublon de type '{$doublon['type_doublon']}' créé (ligne $ligneImport)</p>";
        } else {
            echo "<p>❌ Erreur lors de la création du doublon ligne $ligneImport</p>";
            echo "<pre>" . print_r($stmt->errorInfo(), true) . "</pre>";
        }

        $ligneImport++;
    }

    echo "</div>";

    // Vérification des données créées
    echo "<div class='result success'>
        <h3>📊 Vérification des données créées</h3>";

    $verification = $conn->prepare("SELECT * FROM nomenclatures_doublons_import WHERE fichier_import = ?");
    $verification->execute([$testFichierImport]);
    $doublons = $verification->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>✅ " . count($doublons) . " doublons créés dans la table</p>";

    if (!empty($doublons)) {
        echo "<h4>Détails des doublons créés :</h4>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-sm'>";
        echo "<thead><tr>
            <th>ID</th><th>Repère</th><th>Code Article</th><th>Raison Rejet</th>
            <th>Statut</th><th>Ligne</th><th>Date Import</th>
        </tr></thead><tbody>";

        foreach ($doublons as $doublon) {
            echo "<tr>
                <td>{$doublon['id']}</td>
                <td>{$doublon['repere_equipement']}</td>
                <td>{$doublon['code_article']}</td>
                <td><span class='badge bg-warning'>{$doublon['raison_rejet']}</span></td>
                <td><span class='badge bg-secondary'>{$doublon['statut']}</span></td>
                <td>{$doublon['ligne_import']}</td>
                <td>{$doublon['date_import']}</td>
            </tr>";
        }

        echo "</tbody></table>";
        echo "</div>";
    }

    echo "</div>";

    // Statistiques
    echo "<div class='result info'>
        <h3>📈 Statistiques des doublons d'importation</h3>";

    $stats = [
        'total' => $conn->query("SELECT COUNT(*) FROM nomenclatures_doublons_import")->fetchColumn(),
        'en_attente' => $conn->query("SELECT COUNT(*) FROM nomenclatures_doublons_import WHERE statut = 'en_attente'")->fetchColumn(),
        'valide' => $conn->query("SELECT COUNT(*) FROM nomenclatures_doublons_import WHERE statut = 'valide'")->fetchColumn(),
        'rejete' => $conn->query("SELECT COUNT(*) FROM nomenclatures_doublons_import WHERE statut = 'rejete'")->fetchColumn(),
        'importe' => $conn->query("SELECT COUNT(*) FROM nomenclatures_doublons_import WHERE statut = 'importe'")->fetchColumn(),
        'par_type' => $conn->query("
            SELECT raison_rejet, COUNT(*) as count 
            FROM nomenclatures_doublons_import 
            GROUP BY raison_rejet
        ")->fetchAll(PDO::FETCH_ASSOC)
    ];

    echo "<div class='row'>";
    echo "<div class='col-md-3'><div class='card text-center'><div class='card-body'>";
    echo "<h5 class='card-title'>{$stats['total']}</h5><p class='card-text'>Total</p></div></div></div>";
    echo "<div class='col-md-2'><div class='card text-center bg-warning'><div class='card-body'>";
    echo "<h5 class='card-title'>{$stats['en_attente']}</h5><p class='card-text'>En attente</p></div></div></div>";
    echo "<div class='col-md-2'><div class='card text-center bg-success'><div class='card-body'>";
    echo "<h5 class='card-title'>{$stats['valide']}</h5><p class='card-text'>Validé</p></div></div></div>";
    echo "<div class='col-md-2'><div class='card text-center bg-danger'><div class='card-body'>";
    echo "<h5 class='card-title'>{$stats['rejete']}</h5><p class='card-text'>Rejeté</p></div></div></div>";
    echo "<div class='col-md-3'><div class='card text-center bg-info'><div class='card-body'>";
    echo "<h5 class='card-title'>{$stats['importe']}</h5><p class='card-text'>Importé</p></div></div></div>";
    echo "</div>";

    if (!empty($stats['par_type'])) {
        echo "<h4 class='mt-4'>Répartition par type de doublon :</h4>";
        foreach ($stats['par_type'] as $type) {
            echo "<p>• <strong>{$type['raison_rejet']}</strong> : {$type['count']} doublon(s)</p>";
        }
    }

    echo "</div>";

    // Liens utiles
    echo "<div class='result success'>
        <h3>🔗 Liens pour tester le système</h3>
        <div class='row g-3'>
            <div class='col-md-4'>
                <a href='validation_doublons_import.php' class='btn btn-primary w-100'>
                    📋 Interface de validation
                </a>
            </div>
            <div class='col-md-4'>
                <a href='request/doublons_import_stats.php' class='btn btn-info w-100' target='_blank'>
                    📊 API Statistiques
                </a>
            </div>
            <div class='col-md-4'>
                <a href='request/doublons_import_list.php?page=1&limit=10' class='btn btn-warning w-100' target='_blank'>
                    📋 API Liste doublons
                </a>
            </div>
        </div>
        <div class='row g-3 mt-2'>
            <div class='col-md-6'>
                <a href='nomenclatures.php' class='btn btn-outline-primary w-100'>
                    🏠 Retour aux nomenclatures
                </a>
            </div>
            <div class='col-md-6'>
                <a href='test_import_duplicates.php' class='btn btn-outline-secondary w-100'>
                    🧪 Tests généraux
                </a>
            </div>
        </div>
    </div>";
} catch (Exception $e) {
    echo "<div class='result error'>
        <h3>❌ Erreur</h3>
        <p>Erreur lors de la création des doublons de test :</p>
        <pre>" . htmlspecialchars($e->getMessage()) . "</pre>
        <p><strong>Trace :</strong></p>
        <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
    </div>";
}

echo "    </div>
    <script src='plugins/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
