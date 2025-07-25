<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="articles_non_sap_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

try {
    $pdo = Database::getConnection();

    // Récupération des articles qui ne sont pas dans SAP
    $stmt = $pdo->query("
        SELECT DISTINCT
            a.code_article,
            a.designation_article,
            a.type_article,
            a.fabricant,
            a.numero_piece_fabricant,
            a.groupe_articles,
            a.groupe_marche_externe,
            a.uq_base,
            a.temsup_niv_mdt,
            a.description,
            a.date_creation,
            a.cree_par,
            CASE 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'RGM') 
                THEN 'RGM' 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article AND n.source = 'Template') 
                THEN 'Template'
                ELSE 'Aucune source'
            END as source_actuelle,
            CASE 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.code_article = a.code_article) 
                THEN 'Oui' 
                ELSE 'Non' 
            END as a_nomenclature,
            COUNT(DISTINCT n.repere_equipement) as nb_equipements_lies,
            CASE 
                WHEN a.code_article LIKE '1%' THEN 'Mécanique'
                WHEN a.code_article LIKE '2%' THEN 'Électrique'
                WHEN a.code_article LIKE '3%' THEN 'Instrumentation'
                WHEN a.code_article LIKE '4%' THEN 'Tuyauterie'
                WHEN a.code_article LIKE '5%' THEN 'Chaudronnerie'
                WHEN a.code_article LIKE '6%' THEN 'Civil/Structure'
                WHEN a.code_article LIKE '7%' THEN 'Chimie/Process'
                WHEN a.code_article LIKE '8%' THEN 'Sécurité'
                WHEN a.code_article LIKE '9%' THEN 'Maintenance'
                ELSE 'Autres'
            END as metier
        FROM articles a
        LEFT JOIN nomenclatures n ON a.code_article = n.code_article
        WHERE a.code_article NOT IN (
            SELECT DISTINCT n2.code_article 
            FROM nomenclatures n2 
            WHERE n2.source = 'SAP' 
            AND n2.code_article IS NOT NULL
        )
        GROUP BY a.id, a.code_article, a.designation_article, a.type_article, a.fabricant, 
                 a.numero_piece_fabricant, a.groupe_articles, a.groupe_marche_externe, 
                 a.uq_base, a.temsup_niv_mdt, a.description, a.date_creation, a.cree_par
        ORDER BY a.code_article
    ");

    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
    echo '<th>Code Article</th>';
    echo '<th>Désignation</th>';
    echo '<th>Type Article</th>';
    echo '<th>Métier</th>';
    echo '<th>Fabricant</th>';
    echo '<th>N° Pièce Fabricant</th>';
    echo '<th>Groupe Articles</th>';
    echo '<th>Groupe Marché</th>';
    echo '<th>UQ Base</th>';
    echo '<th>Niveau MDT</th>';
    echo '<th>Description</th>';
    echo '<th>Date Création</th>';
    echo '<th>Créé Par</th>';
    echo '<th>Source Actuelle</th>';
    echo '<th>A Nomenclature</th>';
    echo '<th>Nb Équipements Liés</th>';
    echo '<th>Statut SAP</th>';
    echo '</tr>';

    foreach ($articles as $article) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($article['code_article'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['designation_article'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['type_article'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['metier']) . '</td>';
        echo '<td>' . htmlspecialchars($article['fabricant'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['numero_piece_fabricant'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['groupe_articles'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['groupe_marche_externe'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['uq_base'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['temsup_niv_mdt'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['description'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['date_creation'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['cree_par'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($article['source_actuelle']) . '</td>';
        echo '<td>' . htmlspecialchars($article['a_nomenclature']) . '</td>';
        echo '<td>' . htmlspecialchars($article['nb_equipements_lies']) . '</td>';
        echo '<td style="background-color: #ffebee; color: #d32f2f;">NON CODIFIÉ SAP</td>';
        echo '</tr>';
    }

    echo '</table>';

    // Résumé par métier
    $resumeMetier = [];
    foreach ($articles as $article) {
        $metier = $article['metier'];
        if (!isset($resumeMetier[$metier])) {
            $resumeMetier[$metier] = 0;
        }
        $resumeMetier[$metier]++;
    }

    echo '<br><br>';
    echo '<table border="1">';
    echo '<tr style="background-color: #e8f5e8;">';
    echo '<th colspan="2">RÉSUMÉ PAR MÉTIER</th>';
    echo '</tr>';
    foreach ($resumeMetier as $metier => $count) {
        echo '<tr>';
        echo '<td><strong>' . htmlspecialchars($metier) . ' :</strong></td>';
        echo '<td>' . $count . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    // Résumé général
    echo '<br>';
    echo '<table border="1">';
    echo '<tr style="background-color: #e3f2fd;">';
    echo '<th colspan="2">RÉSUMÉ ARTICLES NON SAP</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><strong>Total articles non SAP :</strong></td>';
    echo '<td>' . count($articles) . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><strong>Date d\'export :</strong></td>';
    echo '<td>' . date('d/m/Y H:i:s') . '</td>';
    echo '</tr>';
    echo '</table>';
} catch (Exception $e) {
    echo '<p style="color: red;">Erreur lors de l\'export : ' . htmlspecialchars($e->getMessage()) . '</p>';
}
