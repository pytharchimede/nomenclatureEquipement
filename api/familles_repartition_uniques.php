<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Répartition par famille: compter 1 fois chaque repère
    $sql = "
        WITH uniq_repere AS (
            SELECT DISTINCT repere_equipement, COALESCE(famille, 'Non définie') AS famille
            FROM equipements
            WHERE repere_equipement IS NOT NULL AND repere_equipement <> ''
        ),
        repere_with_flags AS (
            SELECT 
                u.famille,
                u.repere_equipement,
                -- A-t-il au moins une nomenclature (toutes sources)
                EXISTS(
                    SELECT 1 FROM nomenclatures n 
                    WHERE n.repere_equipement = u.repere_equipement
                ) AS has_nomenclature,
                -- Est-il présent dans SAP (via nomenclatures source SAP)
                EXISTS(
                    SELECT 1 FROM nomenclatures n 
                    WHERE n.repere_equipement = u.repere_equipement AND n.source = 'SAP'
                ) AS in_sap
            FROM uniq_repere u
        ),
        art_non_sap AS (
            SELECT DISTINCT n.code_article
            FROM nomenclatures n
            LEFT JOIN (
                SELECT DISTINCT code_article FROM nomenclatures WHERE source = 'SAP' AND code_article IS NOT NULL
            ) sap ON sap.code_article = n.code_article
            WHERE n.code_article IS NOT NULL AND sap.code_article IS NULL
        ),
        fam_art_non_sap AS (
            SELECT 
                COALESCE(e.famille, 'Non définie') AS famille,
                COUNT(DISTINCT n.code_article) AS nb_articles_non_sap
            FROM nomenclatures n
            JOIN equipements e ON e.repere_equipement = n.repere_equipement
            WHERE n.code_article IN (SELECT code_article FROM art_non_sap)
            GROUP BY COALESCE(e.famille, 'Non définie')
        )
        SELECT 
            r.famille,
            COUNT(DISTINCT r.repere_equipement) AS nb_reperes_uniques,
            SUM(CASE WHEN r.has_nomenclature = 0 THEN 1 ELSE 0 END) AS nb_equipements_sans_nomenclature,
            SUM(CASE WHEN r.in_sap = 0 THEN 1 ELSE 0 END) AS nb_equipements_non_sap,
            COALESCE(f.nb_articles_non_sap, 0) AS nb_articles_non_sap
        FROM repere_with_flags r
        LEFT JOIN fam_art_non_sap f ON f.famille = r.famille
        GROUP BY r.famille
        ORDER BY nb_reperes_uniques DESC, r.famille ASC
    ";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $rows
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
