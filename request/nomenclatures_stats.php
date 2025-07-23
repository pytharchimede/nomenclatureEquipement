<?php

/**
 * API pour les statistiques des nomenclatures
 * Calculs optimisés pour de gros volumes
 */

require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Statistiques par famille (désignation article)
    $familleQuery = "
        SELECT 
            designation_article,
            COUNT(*) as count
        FROM nomenclatures 
        WHERE designation_article IS NOT NULL 
        AND designation_article != ''
        GROUP BY designation_article 
        ORDER BY count DESC 
        LIMIT 10
    ";
    $stmt = $pdo->query($familleQuery);
    $familles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $topFamille = $familles[0]['designation_article'] ?? '';
    $maxFamille = $familles[0]['count'] ?? 0;

    // Statistiques par unité
    $uniteQuery = "
        SELECT 
            unite,
            COUNT(*) as count
        FROM nomenclatures 
        WHERE unite IS NOT NULL 
        AND unite != ''
        GROUP BY unite 
        ORDER BY count DESC 
        LIMIT 10
    ";
    $stmt = $pdo->query($uniteQuery);
    $unites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $topUnite = $unites[0]['unite'] ?? '';
    $maxUnite = $unites[0]['count'] ?? 0;

    // Statistiques par fabricant
    $fabricantQuery = "
        SELECT 
            fabricant,
            COUNT(*) as count
        FROM nomenclatures 
        WHERE fabricant IS NOT NULL 
        AND fabricant != ''
        GROUP BY fabricant 
        ORDER BY count DESC 
        LIMIT 5
    ";
    $stmt = $pdo->query($fabricantQuery);
    $fabricants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques par métier
    $metierQuery = "
        SELECT 
            metier,
            COUNT(*) as count
        FROM nomenclatures 
        WHERE metier IS NOT NULL 
        AND metier != ''
        GROUP BY metier 
        ORDER BY count DESC 
        LIMIT 5
    ";
    $stmt = $pdo->query($metierQuery);
    $metiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques par source
    $sourceQuery = "
        SELECT 
            source,
            COUNT(*) as count
        FROM nomenclatures 
        WHERE source IS NOT NULL 
        AND source != ''
        GROUP BY source 
        ORDER BY count DESC
    ";
    $stmt = $pdo->query($sourceQuery);
    $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nombre total de nomenclatures
    $totalQuery = "SELECT COUNT(*) FROM nomenclatures";
    $stmt = $pdo->query($totalQuery);
    $total = $stmt->fetchColumn();

    // Nomenclatures sans article (potentiellement problématiques)
    $sansArticleQuery = "SELECT COUNT(*) FROM nomenclatures WHERE code_article IS NULL OR code_article = ''";
    $stmt = $pdo->query($sansArticleQuery);
    $sansArticle = $stmt->fetchColumn();

    // Nomenclatures sans équipement (potentiellement problématiques)
    $sansEquipementQuery = "SELECT COUNT(*) FROM nomenclatures WHERE repere_equipement IS NULL OR repere_equipement = ''";
    $stmt = $pdo->query($sansEquipementQuery);
    $sansEquipement = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'stats' => [
            'total' => $total,
            'topFamille' => [
                'nom' => $topFamille,
                'count' => $maxFamille
            ],
            'topUnite' => [
                'nom' => $topUnite,
                'count' => $maxUnite
            ],
            'sansArticle' => $sansArticle,
            'sansEquipement' => $sansEquipement,
            'doublons' => [] // À implémenter si nécessaire
        ],
        'data' => [
            'familles' => $familles,
            'unites' => $unites,
            'fabricants' => $fabricants,
            'metiers' => $metiers,
            'sources' => $sources
        ]
    ]);
} catch (Exception $e) {
    error_log("Erreur statistiques nomenclatures: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du calcul des statistiques',
        'error' => $e->getMessage()
    ]);
}
