<?php
require_once 'model/Equipement.php';

echo "Test rapide avec la source SAP :\n";
$result = Equipement::getPaginated(1, 3, ['source' => 'SAP']);
echo "Total SAP: " . $result['total'] . "\n";
echo "Échantillon:\n";
foreach ($result['data'] as $eq) {
    echo "- " . $eq['repere_equipement'] . " : " . ($eq['designation_equipement'] ?? '') . "\n";
}
