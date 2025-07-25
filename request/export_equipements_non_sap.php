<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="equipements_non_sap_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

try {
    $pdo = Database::getConnection();

    // Récupération des équipements qui ne sont pas dans SAP
    $stmt = $pdo->query("
        SELECT DISTINCT
            e.repere_equipement,
            e.designation_equipement,
            e.fabricant,
            e.type_objet,
            e.famille,
            e.poste_technique,
            e.designation_poste_technique,
            e.categorie_equipement,
            e.centre_de_couts,
            e.date_creation,
            CASE 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'RGM') 
                THEN 'RGM' 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'Template') 
                THEN 'Template'
                ELSE 'Aucune source'
            END as source_actuelle,
            CASE 
                WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement) 
                THEN 'Oui' 
                ELSE 'Non' 
            END as a_nomenclature
        FROM equipements e
        WHERE e.repere_equipement NOT IN (
            SELECT DISTINCT n.repere_equipement 
            FROM nomenclatures n 
            WHERE n.source = 'SAP' 
            AND n.repere_equipement IS NOT NULL
        )
        ORDER BY e.repere_equipement
    ");

    $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
    echo '<th>Repère Équipement</th>';
    echo '<th>Désignation</th>';
    echo '<th>Fabricant</th>';
    echo '<th>Type Objet</th>';
    echo '<th>Famille</th>';
    echo '<th>Poste Technique</th>';
    echo '<th>Désignation Poste</th>';
    echo '<th>Catégorie</th>';
    echo '<th>Centre de Coûts</th>';
    echo '<th>Date Création</th>';
    echo '<th>Source Actuelle</th>';
    echo '<th>A Nomenclature</th>';
    echo '<th>Statut SAP</th>';
    echo '</tr>';

    foreach ($equipements as $equipement) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($equipement['repere_equipement'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['designation_equipement'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['fabricant'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['type_objet'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['famille'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['poste_technique'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['designation_poste_technique'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['categorie_equipement'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['centre_de_couts'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['date_creation'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($equipement['source_actuelle']) . '</td>';
        echo '<td>' . htmlspecialchars($equipement['a_nomenclature']) . '</td>';
        echo '<td style="background-color: #ffebee; color: #d32f2f;">NON CODIFIÉ SAP</td>';
        echo '</tr>';
    }

    echo '</table>';

    // Résumé en fin de fichier
    echo '<br><br>';
    echo '<table border="1">';
    echo '<tr style="background-color: #e3f2fd;">';
    echo '<th colspan="2">RÉSUMÉ ÉQUIPEMENTS NON SAP</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><strong>Total équipements non SAP :</strong></td>';
    echo '<td>' . count($equipements) . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><strong>Date d\'export :</strong></td>';
    echo '<td>' . date('d/m/Y H:i:s') . '</td>';
    echo '</tr>';
    echo '</table>';
} catch (Exception $e) {
    echo '<p style="color: red;">Erreur lors de l\'export : ' . htmlspecialchars($e->getMessage()) . '</p>';
}
