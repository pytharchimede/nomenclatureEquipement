<?php

/**
 * API pour la pagination des articles avec filtrage
 * Retourne les articles paginés au format JSON
 */

require_once __DIR__ . '/../model/Article.php';

header('Content-Type: application/json');

try {
    // Récupération des paramètres
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 50))); // Entre 10 et 100 éléments

    // Filtres de recherche
    $filters = [];

    if (!empty($_GET['search'])) {
        $filters['search'] = trim($_GET['search']);
    }

    if (!empty($_GET['fabricant'])) {
        $filters['fabricant'] = trim($_GET['fabricant']);
    }

    if (!empty($_GET['type_article'])) {
        $filters['type_article'] = trim($_GET['type_article']);
    }

    if (!empty($_GET['groupe_articles'])) {
        $filters['groupe_articles'] = trim($_GET['groupe_articles']);
    }

    if (!empty($_GET['uq_base'])) {
        $filters['uq_base'] = trim($_GET['uq_base']);
    }

    if (!empty($_GET['source'])) {
        $filters['source'] = trim($_GET['source']);
    }

    // Récupération des données paginées
    $result = Article::getPaginated($page, $limit, $filters);

    // Formatage des données pour l'affichage
    $articles = [];
    foreach ($result['data'] as $art) {
        $articles[] = [
            'id' => $art['id'],
            'code_article' => $art['code_article'],
            'designation_article' => $art['designation_article'] ?? '',
            'type_article' => $art['type_article'] ?? '',
            'uq_base' => $art['uq_base'] ?? '',
            'fabricant' => $art['fabricant'] ?? '',
            'numero_piece_fabricant' => $art['numero_piece_fabricant'] ?? '',
            'groupe_articles' => $art['groupe_articles'] ?? '',
            'document' => $art['document'] ?? '',
            'description' => $art['description'] ?? '',
            'date_creation' => $art['date_creation'] ?? '',
            'cree_par' => $art['cree_par'] ?? ''
        ];
    }

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'data' => $articles,
        'pagination' => [
            'page' => $result['page'],
            'limit' => $result['limit'],
            'total' => $result['total'],
            'hasMore' => $result['hasMore'],
            'totalPages' => ceil($result['total'] / $result['limit'])
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des articles : ' . $e->getMessage()
    ]);
}
