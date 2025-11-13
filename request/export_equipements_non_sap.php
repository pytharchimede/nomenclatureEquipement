<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../model/Database.php';

// Options de sortie (par défaut: xls HTML). Utiliser ?format=csv pour un export plus léger.
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'xls';
$limit = isset($_GET['limit']) ? max(0, (int)$_GET['limit']) : 0;

try {
    // Éviter l'expiration sur gros volumes
    if (!ini_get('safe_mode')) {
        @set_time_limit(0);
    }
    @ignore_user_abort(true);
    $pdo = Database::getConnection();
    // Désactiver le buffering MySQL pour streamer les résultats
    try {
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    } catch (Throwable $e) {
    }

    // Mode CSV léger: streaming ligne à ligne
    if ($format === 'csv') {
        $sqlCsv = "
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
                    WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'RGM') THEN 'RGM'
                    WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'Template') THEN 'Template'
                    ELSE 'Aucune source'
                END as source_actuelle,
                CASE 
                    WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement) THEN 'Oui' 
                    ELSE 'Non' 
                END as a_nomenclature
            FROM equipements e
            WHERE NOT EXISTS (
                SELECT 1 FROM nomenclatures n
                WHERE n.source = 'SAP' 
                  AND n.repere_equipement IS NOT NULL
                  AND n.repere_equipement = e.repere_equipement
            )
            ORDER BY e.repere_equipement";
        if ($limit > 0) {
            $sqlCsv .= "\n            LIMIT " . (int)$limit;
        }

        $stmtCsv = $pdo->query($sqlCsv);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="equipements_non_sap_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Cache-Control: max-age=0');
        $out = fopen('php://output', 'w');
        fprintf($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel Windows
        fputcsv($out, [
            'Repère Équipement',
            'Désignation',
            'Fabricant',
            'Type Objet',
            'Famille',
            'Poste Technique',
            'Désignation Poste',
            'Catégorie',
            'Centre de Coûts',
            'Date Création',
            'Source Actuelle',
            'A Nomenclature',
            'Statut SAP'
        ], ';');
        while ($r = $stmtCsv->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($out, [
                $r['repere_equipement'] ?? '',
                $r['designation_equipement'] ?? '',
                $r['fabricant'] ?? '',
                $r['type_objet'] ?? '',
                $r['famille'] ?? '',
                $r['poste_technique'] ?? '',
                $r['designation_poste_technique'] ?? '',
                $r['categorie_equipement'] ?? '',
                $r['centre_de_couts'] ?? '',
                $r['date_creation'] ?? '',
                $r['source_actuelle'] ?? '',
                $r['a_nomenclature'] ?? '',
                'NON CODIFIÉ SAP'
            ], ';');
        }
        fclose($out);
        exit;
    }

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
            WHERE NOT EXISTS (
                SELECT 1 FROM nomenclatures n 
                WHERE n.source = 'SAP' 
                  AND n.repere_equipement IS NOT NULL
                  AND n.repere_equipement = e.repere_equipement
            )
            ORDER BY e.repere_equipement
        ");

    // Mode XLS: streaming direct et LIMIT éventuel
    $sqlXls = "
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
                    WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'RGM') THEN 'RGM'
                    WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement AND n.source = 'Template') THEN 'Template'
                    ELSE 'Aucune source'
                END as source_actuelle,
                CASE 
                    WHEN EXISTS (SELECT 1 FROM nomenclatures n WHERE n.repere_equipement = e.repere_equipement) THEN 'Oui' 
                    ELSE 'Non' 
                END as a_nomenclature
            FROM equipements e
            WHERE NOT EXISTS (
                SELECT 1 FROM nomenclatures n 
                WHERE n.source = 'SAP' 
                  AND n.repere_equipement IS NOT NULL
                  AND n.repere_equipement = e.repere_equipement
            )
            ORDER BY e.repere_equipement";
    if ($limit > 0) {
        $sqlXls .= "\n        LIMIT " . (int)$limit;
    }
    $stmtXls = $pdo->query($sqlXls);

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="equipements_non_sap_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Cache-Control: max-age=0');

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

    $total = 0;
    while ($equipement = $stmtXls->fetch(PDO::FETCH_ASSOC)) {
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
        $total++;
        if (($total % 500) === 0) {
            echo "\n";
            flush();
        }
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
    echo '<td>' . $total . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><strong>Date d\'export :</strong></td>';
    echo '<td>' . date('d/m/Y H:i:s') . '</td>';
    echo '</tr>';
    echo '</table>';
} catch (Exception $e) {
    echo '<p style="color: red;">Erreur lors de l\'export : ' . htmlspecialchars($e->getMessage()) . '</p>';
}
