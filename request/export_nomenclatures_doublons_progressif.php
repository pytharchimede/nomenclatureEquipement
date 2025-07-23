<?php
require_once '../model/Database.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

function sendEvent($data)
{
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

try {
    $pdo = Database::getConnection();

    sendEvent(['type' => 'start', 'message' => 'Démarrage de l\'export des doublons...']);

    // Compter d'abord le nombre de groupes de doublons
    $countQuery = "
        SELECT COUNT(*) FROM (
            SELECT repere_equipement, code_article
            FROM nomenclatures 
            WHERE repere_equipement IS NOT NULL 
            AND repere_equipement != ''
            AND code_article IS NOT NULL 
            AND code_article != ''
            GROUP BY repere_equipement, code_article 
            HAVING COUNT(*) > 1
        ) as count_doublons
    ";

    $stmt = $pdo->query($countQuery);
    $totalGroups = $stmt->fetchColumn();

    if ($totalGroups == 0) {
        sendEvent(['type' => 'completed', 'message' => 'Aucun doublon détecté.', 'count' => 0]);
        exit;
    }

    sendEvent(['type' => 'progress', 'message' => "Détection de $totalGroups groupes de doublons", 'total' => $totalGroups]);

    // Export des doublons par chunks
    $chunkSize = 100;
    $offset = 0;
    $processed = 0;

    // Créer le fichier CSV temporaire
    $filename = 'doublons_nomenclatures_' . date('Y-m-d_H-i-s') . '.csv';
    $filepath = '../tmp/' . $filename;

    if (!is_dir('../tmp/')) {
        mkdir('../tmp/', 0777, true);
    }

    $file = fopen($filepath, 'w');

    // En-têtes CSV
    $headers = [
        'Groupe',
        'Repère Équipement',
        'Code Article',
        'Nb Doublons',
        'IDs Concernés',
        'Sources',
        'Désignations Équipement',
        'Désignations Article',
        'Fabricants',
        'Quantités',
        'Unités',
        'Dates Création',
        'Première Création',
        'Dernière Création'
    ];

    fputcsv($file, $headers, ';');

    $groupe = 1;

    while ($processed < $totalGroups) {
        $query = "
            SELECT 
                repere_equipement,
                code_article,
                COUNT(*) as count_doublons,
                GROUP_CONCAT(id ORDER BY date_creation ASC) as ids,
                GROUP_CONCAT(source ORDER BY date_creation ASC SEPARATOR ' | ') as sources,
                GROUP_CONCAT(DISTINCT designation_equipement ORDER BY date_creation ASC SEPARATOR ' | ') as designations_equipement,
                GROUP_CONCAT(DISTINCT designation_article ORDER BY date_creation ASC SEPARATOR ' | ') as designations_article,
                GROUP_CONCAT(DISTINCT fabricant ORDER BY date_creation ASC SEPARATOR ' | ') as fabricants,
                GROUP_CONCAT(quantite ORDER BY date_creation ASC SEPARATOR ' | ') as quantites,
                GROUP_CONCAT(unite ORDER BY date_creation ASC SEPARATOR ' | ') as unites,
                GROUP_CONCAT(date_creation ORDER BY date_creation ASC SEPARATOR ' | ') as dates_creation,
                MIN(date_creation) as premiere_creation,
                MAX(date_creation) as derniere_creation
            FROM nomenclatures 
            WHERE repere_equipement IS NOT NULL 
            AND repere_equipement != ''
            AND code_article IS NOT NULL 
            AND code_article != ''
            GROUP BY repere_equipement, code_article 
            HAVING count_doublons > 1
            ORDER BY count_doublons DESC, repere_equipement, code_article
            LIMIT $chunkSize OFFSET $offset
        ";

        $stmt = $pdo->query($query);
        $doublons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($doublons)) {
            break;
        }

        foreach ($doublons as $doublon) {
            $row = [
                $groupe,
                $doublon['repere_equipement'],
                $doublon['code_article'],
                $doublon['count_doublons'],
                $doublon['ids'],
                $doublon['sources'],
                $doublon['designations_equipement'],
                $doublon['designations_article'],
                $doublon['fabricants'],
                $doublon['quantites'],
                $doublon['unites'],
                $doublon['dates_creation'],
                $doublon['premiere_creation'],
                $doublon['derniere_creation']
            ];

            fputcsv($file, $row, ';');
            $groupe++;
            $processed++;

            // Envoyer une mise à jour tous les 25 éléments
            if ($processed % 25 == 0) {
                $percentage = round(($processed / $totalGroups) * 100, 1);
                sendEvent([
                    'type' => 'progress',
                    'message' => "Export en cours... $processed/$totalGroups groupes traités ($percentage%)",
                    'processed' => $processed,
                    'total' => $totalGroups,
                    'percentage' => $percentage
                ]);
            }
        }

        $offset += $chunkSize;
    }

    fclose($file);

    // Calculs de synthèse
    $totalEntreesDupliquees = 0;
    $repartition = [];

    $synthQuery = "
        SELECT COUNT(*) as count_doublons
        FROM nomenclatures 
        WHERE repere_equipement IS NOT NULL 
        AND repere_equipement != ''
        AND code_article IS NOT NULL 
        AND code_article != ''
        GROUP BY repere_equipement, code_article 
        HAVING COUNT(*) > 1
    ";

    $stmt = $pdo->query($synthQuery);
    $counts = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($counts as $count) {
        $totalEntreesDupliquees += $count;
        $repartition[$count] = ($repartition[$count] ?? 0) + 1;
    }

    sendEvent([
        'type' => 'completed',
        'message' => "Export terminé ! $processed groupes de doublons exportés",
        'file' => $filename,
        'filepath' => $filepath,
        'download_url' => "tmp/$filename",
        'stats' => [
            'groupes_doublons' => $processed,
            'entrees_dupliquees' => $totalEntreesDupliquees,
            'repartition' => $repartition
        ]
    ]);
} catch (Exception $e) {
    sendEvent([
        'type' => 'error',
        'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
    ]);
}
