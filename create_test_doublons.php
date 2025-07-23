<?php
require_once 'model/Database.php';

echo "🧪 Création de nomenclatures de test avec doublons\n";
echo "===============================================\n";

try {
    $pdo = Database::getConnection();

    // Données de test avec doublons intentionnels
    $testData = [
        // Groupe 1 - 3 doublons
        ['EQ001', 'ART001', 'REP001', 'Moteur Principal', 'SIEMENS', 'Roulement 6202', 1, 'PCS', 'SAP'],
        ['EQ001', 'ART001', 'REP001', 'Moteur Principal', 'SIEMENS', 'Roulement 6202', 1, 'PCS', 'EXCEL'],
        ['EQ001', 'ART001', 'REP001', 'Moteur Principal', 'SIEMENS', 'Roulement 6202', 1, 'PCS', 'MANUEL'],

        // Groupe 2 - 2 doublons  
        ['EQ002', 'ART002', 'REP002', 'Pompe Centrifuge', 'KSB', 'Joint Torique', 2, 'PCS', 'SAP'],
        ['EQ002', 'ART002', 'REP002', 'Pompe Centrifuge', 'KSB', 'Joint Torique', 2, 'PCS', 'IMPORT'],

        // Groupe 3 - 4 doublons
        ['EQ003', 'ART003', 'REP003', 'Compresseur', 'ATLAS COPCO', 'Filtre à Huile', 1, 'PCS', 'SAP'],
        ['EQ003', 'ART003', 'REP003', 'Compresseur', 'ATLAS COPCO', 'Filtre à Huile', 1, 'PCS', 'SAP'], // Doublon SAP
        ['EQ003', 'ART003', 'REP003', 'Compresseur', 'ATLAS COPCO', 'Filtre à Huile', 1, 'PCS', 'EXCEL'],
        ['EQ003', 'ART003', 'REP003', 'Compresseur', 'ATLAS COPCO', 'Filtre à Huile', 1, 'PCS', 'MAINTENANCE'],

        // Entrées uniques (pas de doublons)
        ['EQ004', 'ART004', 'REP004', 'Ventilateur', 'EBMPAPST', 'Courroie', 1, 'PCS', 'SAP'],
        ['EQ005', 'ART005', 'REP005', 'Réducteur', 'SEW', 'Huile ISO 220', 5, 'L', 'SAP'],
        ['EQ006', 'ART006', 'REP006', 'Convoyeur', 'INTERROLL', 'Galet', 12, 'PCS', 'EXCEL'],
    ];

    $insertQuery = "
        INSERT INTO nomenclatures (
            code_equipement, code_article, repere_equipement, 
            designation_equipement, fabricant, designation_article,
            quantite, unite, source, date_creation
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";

    $stmt = $pdo->prepare($insertQuery);
    $insertCount = 0;

    foreach ($testData as $data) {
        $success = $stmt->execute($data);
        if ($success) {
            $insertCount++;
            echo "✅ Nomenclature créée: {$data[2]} | {$data[1]} (source: {$data[8]})\n";
        }
    }

    echo "\n🎯 Résumé:\n";
    echo "- Nomenclatures créées: $insertCount\n";
    echo "- Doublons attendus: 3 groupes (3+2+4 doublons)\n";
    echo "- Entrées uniques: 3\n";

    // Vérification des doublons
    $verificationQuery = "
        SELECT 
            repere_equipement, code_article, COUNT(*) as count,
            GROUP_CONCAT(source ORDER BY source SEPARATOR ', ') as sources
        FROM nomenclatures 
        WHERE repere_equipement IS NOT NULL 
        AND repere_equipement != ''
        AND code_article IS NOT NULL 
        AND code_article != ''
        GROUP BY repere_equipement, code_article 
        HAVING count > 1
        ORDER BY count DESC
    ";

    $stmt = $pdo->query($verificationQuery);
    $doublons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n🔍 Vérification - Groupes de doublons détectés: " . count($doublons) . "\n";
    foreach ($doublons as $doublon) {
        echo "  - {$doublon['repere_equipement']} | {$doublon['code_article']} : {$doublon['count']} occurences ({$doublon['sources']})\n";
    }

    echo "\n✅ Données de test créées ! Vous pouvez maintenant tester l'export des doublons.\n";
    echo "🌐 Accédez à: gestion_doublons_nomenclature.php\n";
    echo "💡 Pour nettoyer: TRUNCATE TABLE nomenclatures\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
