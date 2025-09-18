<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="equipements_sans_nomenclature_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

try {
    $pdo = Database::getConnection();

    $sql = "
        SELECT 
            e.repere_equipement,
            MAX(e.designation_equipement) AS designation_equipement,
            MAX(e.fabricant) AS fabricant,
            MAX(e.type_objet) AS type_objet,
            COALESCE(MAX(e.famille), 'Non définie') AS famille,
            MAX(e.poste_technique) AS poste_technique,
            MAX(e.designation_poste_technique) AS designation_poste_technique,
            MAX(e.categorie_equipement) AS categorie_equipement,
            MAX(e.centre_de_couts) AS centre_de_couts,
            MAX(e.date_creation) AS date_creation
        FROM equipements e
        LEFT JOIN (
            SELECT DISTINCT repere_equipement FROM nomenclatures WHERE repere_equipement IS NOT NULL
        ) n ON n.repere_equipement = e.repere_equipement
        WHERE n.repere_equipement IS NULL
        GROUP BY e.repere_equipement
        ORDER BY e.repere_equipement
    ";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    echo '<table border="1">';
    echo '<tr style="background-color:#f0f0f0;font-weight:bold">';
    echo '<th>Repère</th><th>Désignation</th><th>Fabricant</th><th>Type Objet</th><th>Famille</th><th>Poste Tech.</th><th>Design. Poste</th><th>Catégorie</th><th>Centre de Coûts</th><th>Date Création</th>';
    echo '</tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['repere_equipement']) . '</td>';
        echo '<td>' . htmlspecialchars($r['designation_equipement'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['fabricant'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['type_objet'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['famille'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['poste_technique'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['designation_poste_technique'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['categorie_equipement'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['centre_de_couts'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($r['date_creation'] ?? '') . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<br><table border="1">';
    echo '<tr style="background-color:#e3f2fd"><th colspan="2">Résumé Équipements sans nomenclature</th></tr>';
    echo '<tr><td>Total</td><td>' . count($rows) . '</td></tr>';
    echo '<tr><td>Exporté le</td><td>' . date('d/m/Y H:i:s') . '</td></tr>';
    echo '</table>';
} catch (Throwable $e) {
    echo '<p style="color:red">Erreur lors de l\'export: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
