<?php
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Récupération des paramètres de filtrage et pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(5, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $statut = $_GET['statut'] ?? '';
    $typeDoublon = $_GET['type_doublon'] ?? '';
    $fichier = $_GET['fichier'] ?? '';
    $recherche = trim($_GET['recherche'] ?? '');

    // Construction de la requête avec filtres
    $whereConditions = [];
    $params = [];

    if (!empty($statut)) {
        $whereConditions[] = "statut = ?";
        $params[] = $statut;
    }

    if (!empty($typeDoublon)) {
        $whereConditions[] = "raison_rejet = ?";
        $params[] = $typeDoublon;
    }

    if (!empty($fichier)) {
        $whereConditions[] = "fichier_import = ?";
        $params[] = $fichier;
    }

    if (!empty($recherche)) {
        $whereConditions[] = "(repere_equipement LIKE ? OR code_article LIKE ? OR designation_equipement LIKE ? OR designation_article LIKE ?)";
        $searchTerm = "%$recherche%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);

    // Comptage total pour la pagination
    $countQuery = "SELECT COUNT(*) FROM nomenclatures_doublons_import $whereClause";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();

    // Récupération des données paginées
    $query = "
        SELECT * 
        FROM nomenclatures_doublons_import 
        $whereClause 
        ORDER BY date_import DESC, id DESC 
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $doublons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcul des informations de pagination
    $totalPages = ceil($totalItems / $limit);
    $pagination = [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'items_per_page' => $limit,
        'has_next' => $page < $totalPages,
        'has_prev' => $page > 1
    ];

    echo json_encode([
        'success' => true,
        'doublons' => $doublons,
        'pagination' => $pagination
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement de la liste: ' . $e->getMessage()
    ]);
}
