<?php

/**
 * API pour récupérer les statistiques des équipements avec filtres
 * Mise à jour en temps réel lors des recherches
 */

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Equipement.php';
require_once __DIR__ . '/../model/Quantitatif.php';

header('Content-Type: application/json');

try {
    // Récupération des filtres
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

    // Récupération des équipements avec filtres
    $result = Equipement::getPaginated(1, 50000, $filters); // Grande limite pour avoir tous les résultats filtrés
    $equipements = $result['data'];
    $total = $result['total'];

    // Calcul des statistiques par famille
    $statsFamilles = [];
    $nonAffectes = 0;
    $fabricants = [];
    $typesObjet = [];
    $categories = [];

    foreach ($equipements as $eq) {
        // Statistiques des familles
        $repere = preg_replace('/\s+/', '', $eq['repere_equipement'] ?? '');
        $famille = Quantitatif::getFamilleByRepere($repere);

        if ($famille && $famille !== 'Non défini') {
            if (!isset($statsFamilles[$famille])) {
                $statsFamilles[$famille] = 0;
            }
            $statsFamilles[$famille]++;
        } else {
            $nonAffectes++;
        }

        // Collecte des valeurs pour les filtres
        if (!empty($eq['fabricant'])) {
            $fabricants[$eq['fabricant']] = true;
        }
        if (!empty($eq['type_objet'])) {
            $typesObjet[$eq['type_objet']] = true;
        }
        if (!empty($eq['categorie_equipement'])) {
            $categories[$eq['categorie_equipement']] = true;
        }
    }

    // Calcul de la famille la plus présente
    $topFamille = '';
    $maxFamille = 0;

    if (!empty($statsFamilles)) {
        $maxFamille = max($statsFamilles);
        $topFamille = array_search($maxFamille, $statsFamilles);
    }

    // Préparation des données pour les graphiques
    $familleLabels = array_keys($statsFamilles);
    $familleData = array_values($statsFamilles);

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'stats' => [
            'total' => $total,
            'topFamille' => $topFamille,
            'maxFamille' => $maxFamille,
            'nonAffectes' => $nonAffectes,
            'familleLabels' => $familleLabels,
            'familleData' => $familleData
        ],
        'filters' => [
            'fabricants' => array_keys($fabricants),
            'typesObjet' => array_keys($typesObjet),
            'categories' => array_keys($categories)
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du calcul des statistiques : ' . $e->getMessage()
    ]);
}
