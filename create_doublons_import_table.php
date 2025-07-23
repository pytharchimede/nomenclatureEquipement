<?php
require_once 'model/Database.php';

echo "🗃️  Création de la table pour stocker les doublons d'importation\n";
echo "============================================================\n";

try {
    $pdo = Database::getConnection();

    // Création de la table nomenclatures_doublons_import
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS nomenclatures_doublons_import (
            id INT AUTO_INCREMENT PRIMARY KEY,
            
            -- Données de la nomenclature tentée d'import
            code_equipement VARCHAR(50),
            code_article VARCHAR(50),
            repere_equipement VARCHAR(100),
            designation_equipement VARCHAR(255),
            fabricant VARCHAR(255),
            type VARCHAR(100),
            numero_serie_fabricant VARCHAR(100),
            designation_article VARCHAR(255),
            numero_poste VARCHAR(100),
            quantite INT,
            unite VARCHAR(50),
            poste_technique VARCHAR(100),
            metier VARCHAR(100),
            source VARCHAR(100),
            
            -- Métadonnées d'import
            fichier_import VARCHAR(255),
            ligne_import INT,
            date_import TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            -- Raison du rejet
            raison_rejet ENUM('doublon_exact', 'doublon_repere_article', 'doublon_suspect') DEFAULT 'doublon_repere_article',
            details_conflit TEXT,
            
            -- IDs des nomenclatures existantes en conflit
            nomenclatures_conflits TEXT, -- JSON des IDs en conflit
            
            -- Statut de validation
            statut ENUM('en_attente', 'valide', 'rejete', 'importe') DEFAULT 'en_attente',
            valide_par VARCHAR(100),
            date_validation TIMESTAMP NULL,
            commentaire_validation TEXT,
            
            -- Index pour performances
            INDEX idx_repere_article (repere_equipement, code_article),
            INDEX idx_statut (statut),
            INDEX idx_date_import (date_import),
            INDEX idx_fichier (fichier_import)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($createTableQuery);
    echo "✅ Table 'nomenclatures_doublons_import' créée avec succès\n";

    // Vérification de la structure
    $stmt = $pdo->query('DESCRIBE nomenclatures_doublons_import');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n📋 Structure de la table créée (" . count($columns) . " colonnes):\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\n🎯 Fonctionnalités disponibles:\n";
    echo "  ✓ Stockage des doublons détectés lors d'import\n";
    echo "  ✓ Métadonnées d'import (fichier, ligne, date)\n";
    echo "  ✓ Raisons de rejet détaillées\n";
    echo "  ✓ Workflow de validation manuelle\n";
    echo "  ✓ Traçabilité complète\n";

    echo "\n💡 Prochaines étapes:\n";
    echo "  1. Modifier l'import pour détecter et stocker les doublons\n";
    echo "  2. Créer l'interface de validation des doublons\n";
    echo "  3. Permettre la réintégration après validation\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
