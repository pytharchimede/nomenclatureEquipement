<?php
header('Content-Type: application/json');

// API de test avec données forcées pour diagnostiquer le problème
echo json_encode([
    'success' => true,
    'data' => [
        'familles' => [
            ['famille' => 'Pompes Centrifuges', 'nb_equipements' => 125, 'nb_articles_differents' => 45],
            ['famille' => 'Compresseurs', 'nb_equipements' => 89, 'nb_articles_differents' => 32],
            ['famille' => 'Échangeurs', 'nb_equipements' => 95, 'nb_articles_differents' => 38],
            ['famille' => 'Réservoirs', 'nb_equipements' => 67, 'nb_articles_differents' => 28],
            ['famille' => 'Colonnes', 'nb_equipements' => 45, 'nb_articles_differents' => 22]
        ],
        'types_equipements' => [
            ['type_equipement' => 'Pompes', 'nombre_equipements' => 150, 'varietes_articles' => 55],
            ['type_equipement' => 'Compresseurs', 'nombre_equipements' => 89, 'varietes_articles' => 42],
            ['type_equipement' => 'Échangeurs', 'nombre_equipements' => 120, 'varietes_articles' => 48]
        ],
        'sources' => [
            ['source' => 'SAP', 'equipements' => 1200, 'articles' => 4500],
            ['source' => 'RGM', 'equipements' => 800, 'articles' => 2300]
        ],
        'metiers' => [
            ['metier' => 'Mécanique', 'nb_articles' => 5500],
            ['metier' => 'Électrique', 'nb_articles' => 3200],
            ['metier' => 'Sécurité', 'nb_articles' => 2800]
        ],
        'evolution' => [
            ['mois' => 'Jan 25', 'nouveaux_equipements' => 45, 'cumule' => 1200],
            ['mois' => 'Fév 25', 'nouveaux_equipements' => 52, 'cumule' => 1252],
            ['mois' => 'Mar 25', 'nouveaux_equipements' => 38, 'cumule' => 1290],
            ['mois' => 'Avr 25', 'nouveaux_equipements' => 41, 'cumule' => 1331],
            ['mois' => 'Mai 25', 'nouveaux_equipements' => 47, 'cumule' => 1378],
            ['mois' => 'Jun 25', 'nouveaux_equipements' => 39, 'cumule' => 1417],
            ['mois' => 'Jul 25', 'nouveaux_equipements' => 43, 'cumule' => 1460]
        ],
        'performance' => [
            'total_equipements' => 1460,
            'total_articles' => 12000,
            'total_nomenclatures' => 45000,
            'equipements_sap' => 1200,
            'articles_sap' => 8500
        ]
    ]
], JSON_PRETTY_PRINT);
