<?php
require_once 'model/Database.php';

echo "🧪 Création de données complètes de test avec doublons\n";
echo "=====================================================\n";

try {
    $pdo = Database::getConnection();

    // 1. Créer d'abord les équipements
    echo "📋 Création des équipements...\n";
    $equipements = [
        ['EQ001', 'Moteur Principal', 'REP001', 'SIEMENS', 'Motors'],
        ['EQ002', 'Pompe Centrifuge', 'REP002', 'KSB', 'Pumps'],
        ['EQ003', 'Compresseur', 'REP003', 'ATLAS COPCO', 'Compressors'],
        ['EQ004', 'Ventilateur', 'REP004', 'EBMPAPST', 'Fans'],
        ['EQ005', 'Réducteur', 'REP005', 'SEW', 'Gears'],
        ['EQ006', 'Convoyeur', 'REP006', 'INTERROLL', 'Conveyors'],
    ];

    $equipementQuery = "
        INSERT IGNORE INTO equipements (
            code_equipement, designation_equipement, repere_equipement, 
            fabricant, famille, date_creation
        ) VALUES (?, ?, ?, ?, ?, CURDATE())
    ";

    $stmt = $pdo->prepare($equipementQuery);
    foreach ($equipements as $eq) {
        $stmt->execute($eq);
        echo "✅ Équipement créé: {$eq[0]} - {$eq[2]}\n";
    }

    // 2. Créer les articles
    echo "\n📦 Création des articles...\n";
    $articles = [
        ['ART001', 'Roulement 6202', 'SIEMENS'],
        ['ART002', 'Joint Torique', 'KSB'],
        ['ART003', 'Filtre à Huile', 'ATLAS COPCO'],
        ['ART004', 'Courroie', 'EBMPAPST'],
        ['ART005', 'Huile ISO 220', 'SEW'],
        ['ART006', 'Galet', 'INTERROLL'],
    ];

    $articleQuery = "
        INSERT IGNORE INTO articles (
            code_article, designation_article, fabricant, date_creation
        ) VALUES (?, ?, ?, CURDATE())
    ";

    $stmt = $pdo->prepare($articleQuery);
    foreach ($articles as $art) {
        $stmt->execute($art);
        echo "✅ Article créé: {$art[0]} - {$art[1]}\n";
    }

    // 3. Créer les nomenclatures avec doublons
    echo "\n📊 Création des nomenclatures avec doublons...\n";
    $nomenclatures = [
        // Groupe 1 - 3 doublons (REP001 + ART001)
        ['EQ001', 'ART001', 'REP001', 'Moteur Principal', 'SIEMENS', 'Roulement 6202', 1, 'PCS', 'SAP'],
        ['EQ001', 'ART001', 'REP001', 'Moteur Principal', 'SIEMENS', 'Roulement 6202', 1, 'PCS', 'EXCEL'],
        ['EQ001', 'ART001', 'REP001', 'Moteur Principal', 'SIEMENS', 'Roulement 6202', 1, 'PCS', 'MANUEL'],

        // Groupe 2 - 2 doublons (REP002 + ART002)
        ['EQ002', 'ART002', 'REP002', 'Pompe Centrifuge', 'KSB', 'Joint Torique', 2, 'PCS', 'SAP'],
        ['EQ002', 'ART002', 'REP002', 'Pompe Centrifuge', 'KSB', 'Joint Torique', 2, 'PCS', 'IMPORT'],

        // Groupe 3 - 4 doublons (REP003 + ART003)
        ['EQ003', 'ART003', 'REP003', 'Compresseur', 'ATLAS COPCO', 'Filtre à Huile', 1, 'PCS', 'SAP'],
        ['EQ003', 'ART003', 'REP003', 'Compresseur', 'ATLAS COPCO', 'Filtre à Huile', 1, 'PCS', 'SAP_BACKUP'],
        ['EQ003', 'ART003', 'REP003', 'Compresseur', 'ATLAS COPCO', 'Filtre à Huile', 1, 'PCS', 'EXCEL'],
        ['EQ003', 'ART003', 'REP003', 'Compresseur', 'ATLAS COPCO', 'Filtre à Huile', 1, 'PCS', 'MAINTENANCE'],

        // Entrées uniques (pas de doublons)
        ['EQ004', 'ART004', 'REP004', 'Ventilateur', 'EBMPAPST', 'Courroie', 1, 'PCS', 'SAP'],
        ['EQ005', 'ART005', 'REP005', 'Réducteur', 'SEW', 'Huile ISO 220', 5, 'L', 'SAP'],
        ['EQ006', 'ART006', 'REP006', 'Convoyeur', 'INTERROLL', 'Galet', 12, 'PCS', 'EXCEL'],
    ];

    $nomenclatureQuery = "
        INSERT INTO nomenclatures (
            code_equipement, code_article, repere_equipement, 
            designation_equipement, fabricant, designation_article,
            quantite, unite, source, date_creation
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())
    ";

    $stmt = $pdo->prepare($nomenclatureQuery);
    $insertCount = 0;

    foreach ($nomenclatures as $nom) {
        $success = $stmt->execute($nom);
        if ($success) {
            $insertCount++;
            echo "✅ Nomenclature créée: {$nom[2]} | {$nom[1]} (source: {$nom[8]})\n";
        }
    }

    // 4. Vérification des doublons
    echo "\n🔍 Vérification des doublons créés...\n";
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

    echo "\n🎯 Résumé final:\n";
    echo "- Équipements créés: " . count($equipements) . "\n";
    echo "- Articles créés: " . count($articles) . "\n";
    echo "- Nomenclatures créées: $insertCount\n";
    echo "- Groupes de doublons détectés: " . count($doublons) . "\n";

    foreach ($doublons as $doublon) {
        echo "  → {$doublon['repere_equipement']} | {$doublon['code_article']} : {$doublon['count']} occurences ({$doublon['sources']})\n";
    }

    echo "\n✅ Données de test créées avec succès !\n";
    echo "🌐 Testez maintenant: gestion_doublons_nomenclature.php\n";
    echo "📊 Export Excel: request/export_nomenclatures_doublons.php\n";
    echo "⚡ Export progressif: request/export_nomenclatures_doublons_progressif.php\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
