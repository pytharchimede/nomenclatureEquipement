<?php

/**
 * API pour la pagination des nomenclatures avec filtres optimisés
 * Compatible avec de gros volumes (26 531+ lignes)
 */

require_once '../model/Database.php';
require_once '../model/Nomenclature.php';

header('Content-Type: application/json');

try {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(10, min(200, intval($_GET['limit'] ?? 100))); // Max 200 par page
    $offset = ($page - 1) * $limit;

    $pdo = Database::getConnection();

    // Construction des filtres WHERE
    $whereConditions = [];
    $params = [];

    // Recherche globale
    if (!empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $whereConditions[] = "(
            code_equipement LIKE ? OR 
            code_article LIKE ? OR 
            repere_equipement LIKE ? OR 
            designation_equipement LIKE ? OR 
            designation_article LIKE ? OR 
            fabricant LIKE ? OR 
            type LIKE ? OR 
            numero_serie_fabricant LIKE ? OR
            poste_technique LIKE ? OR 
            metier LIKE ? OR 
            source LIKE ?
        )";
        // Ajouter le paramètre 11 fois pour chaque champ
        for ($i = 0; $i < 11; $i++) {
            $params[] = $search;
        }
    }

    // Filtres spécifiques
    $filterFields = [
        'code_equipement',
        'code_article',
        'repere_equipement',
        'designation_equipement',
        'fabricant',
        'type',
        'numero_serie_fabricant',
        'designation_article',
        'unite',
        'poste_technique',
        'metier',
        'source'
    ];

    foreach ($filterFields as $field) {
        if (!empty($_GET[$field])) {
            $whereConditions[] = "$field LIKE ?";
            $params[] = '%' . $_GET[$field] . '%';
        }
    }

    // Filtres exacts
    $exactFields = ['numero_poste', 'quantite'];
    foreach ($exactFields as $field) {
        if (!empty($_GET[$field])) {
            $whereConditions[] = "$field = ?";
            $params[] = $_GET[$field];
        }
    }

    // Filtre par date
    if (!empty($_GET['date_creation'])) {
        $whereConditions[] = "DATE(date_creation) = ?";
        $params[] = $_GET['date_creation'];
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Requête pour compter le total
    $countQuery = "SELECT COUNT(*) FROM nomenclatures $whereClause";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // Requête pour récupérer les données paginées
    $dataQuery = "
        SELECT 
            id,
            code_equipement,
            code_article,
            repere_equipement,
            designation_equipement,
            fabricant,
            type,
            numero_serie_fabricant,
            designation_article,
            numero_poste,
            quantite,
            unite,
            poste_technique,
            metier,
            DATE_FORMAT(date_creation, '%d/%m/%Y') as date_creation,
            source
        FROM nomenclatures 
        $whereClause 
        ORDER BY repere_equipement, code_article, numero_poste
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($dataQuery);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcul de la pagination
    $totalPages = ceil($total / $limit);
    $hasMore = $page < $totalPages;

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $totalPages,
            'hasMore' => $hasMore
        ]
    ]);
} catch (Exception $e) {
    error_log("Erreur pagination nomenclatures: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des données',
        'error' => $e->getMessage()
    ]);
}
