<?php
require_once 'model/Database.php';

echo "📊 Monitoring en temps réel de l'importation nomenclatures\n";
echo "=======================================================\n";

$maxChecks = 30; // Max 30 vérifications (5 minutes)
$checkCount = 0;

while ($checkCount < $maxChecks) {
    try {
        $pdo = Database::getConnection();

        // Compter le total de nomenclatures
        $stmt = $pdo->query('SELECT COUNT(*) FROM nomenclatures');
        $total = $stmt->fetchColumn();

        // Compter les doublons potentiels (même code_article + même repere_equipement + même source)
        $stmt2 = $pdo->query("
            SELECT COUNT(*) FROM (
                SELECT repere_equipement, code_article, source, COUNT(*) as count
                FROM nomenclatures 
                WHERE repere_equipement IS NOT NULL 
                AND repere_equipement != ''
                AND code_article IS NOT NULL 
                AND code_article != ''
                AND source IS NOT NULL
                AND source != ''
                GROUP BY repere_equipement, code_article, source 
                HAVING count > 1
            ) as duplicates_count
        ");
        $duplicateGroups = $stmt2->fetchColumn();

        // Affichage du statut
        $timestamp = date('H:i:s');
        echo "[$timestamp] Total nomenclatures: " . number_format($total) . " | Groupes de doublons: $duplicateGroups\n";

        // Si on a des données, afficher quelques détails
        if ($total > 0) {
            // Top 3 des sources
            $stmt3 = $pdo->query("
                SELECT source, COUNT(*) as count 
                FROM nomenclatures 
                WHERE source IS NOT NULL 
                GROUP BY source 
                ORDER BY count DESC 
                LIMIT 3
            ");
            $sources = $stmt3->fetchAll(PDO::FETCH_ASSOC);

            echo "         Sources principales: ";
            foreach ($sources as $source) {
                echo $source['source'] . "(" . $source['count'] . ") ";
            }
            echo "\n";

            // Si on a des doublons, afficher quelques exemples
            if ($duplicateGroups > 0) {
                $stmt4 = $pdo->query("
                    SELECT repere_equipement, code_article, source, COUNT(*) as count
                    FROM nomenclatures 
                    WHERE repere_equipement IS NOT NULL 
                    AND repere_equipement != ''
                    AND code_article IS NOT NULL 
                    AND code_article != ''
                    AND source IS NOT NULL
                    AND source != ''
                    GROUP BY repere_equipement, code_article, source 
                    HAVING count > 1
                    ORDER BY count DESC
                    LIMIT 3
                ");
                $topDuplicates = $stmt4->fetchAll(PDO::FETCH_ASSOC);

                echo "         Top doublons: ";
                foreach ($topDuplicates as $dup) {
                    echo $dup['repere_equipement'] . "|" . $dup['code_article'] . "|" . $dup['source'] . "(" . $dup['count'] . ") ";
                }
                echo "\n";
            }
        }

        // Si l'importation semble terminée (pas de changement pendant 3 cycles)
        static $lastTotal = 0;
        static $unchangedCount = 0;

        if ($total == $lastTotal && $total > 0) {
            $unchangedCount++;
            if ($unchangedCount >= 3) {
                echo "\n✅ Importation semble terminée. Total final: " . number_format($total) . " nomenclatures\n";
                echo "🔍 Groupes de doublons détectés: $duplicateGroups\n";
                break;
            }
        } else {
            $unchangedCount = 0;
        }
        $lastTotal = $total;

        $checkCount++;

        // Si aucune donnée après 10 vérifications, arrêter
        if ($total == 0 && $checkCount > 10) {
            echo "\n⚠️  Aucune donnée détectée après 10 vérifications. L'importation a peut-être échoué.\n";
            break;
        }

        sleep(10); // Attendre 10 secondes entre chaque vérification

    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        break;
    }
}

if ($checkCount >= $maxChecks) {
    echo "\n⏰ Timeout atteint. Vérification manuelle recommandée.\n";
}

echo "\nMonitoring terminé.\n";
