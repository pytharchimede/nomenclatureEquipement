<?php

/**
 * API pour la pagination des équipements avec filtrage
 * Retourne les équipements paginés au format JSON
 */

require_once __DIR__ . '/../model/Equipement.php';

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

    if (!empty($_GET['type_objet'])) {
        $filters['type_objet'] = trim($_GET['type_objet']);
    }

    if (!empty($_GET['categorie_equipement'])) {
        $filters['categorie_equipement'] = trim($_GET['categorie_equipement']);
    }

    // Récupération des données paginées
    $result = Equipement::getPaginated($page, $limit, $filters);

    // Formatage des données pour l'affichage
    $equipements = [];
    foreach ($result['data'] as $eq) {
        $equipements[] = [
            'repere_equipement' => $eq['repere_equipement'],
            'code_equipement' => $eq['code_equipement'],
            'designation_equipement' => $eq['designation_equipement'] ?? '',
            'fabricant' => $eq['fabricant'] ?? '',
            'type_objet' => $eq['type_objet'] ?? '',
            'numero_serie_fabricant' => $eq['numero_serie_fabricant'] ?? '',
            'categorie_equipement' => $eq['categorie_equipement'] ?? '',
            'date_creation' => $eq['date_creation'] ?? '',
            // Ajout d'informations de liaison pour les statistiques
            'has_nomenclature' => false // À calculer si nécessaire
        ];
    }

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'data' => $equipements,
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
        'message' => 'Erreur lors de la récupération des équipements : ' . $e->getMessage()
    ]);
}
