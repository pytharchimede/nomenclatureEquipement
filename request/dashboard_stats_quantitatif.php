<?php
require_once '../model/Equipement.php';
require_once '../model/Quantitatif.php';

$equipements = Equipement::getAll();
$statsFamilles = [];
$nonAffectes = 0;

foreach ($equipements as $eq) {
    $repere = preg_replace('/\s+/', '', $eq['repere_equipement'] ?? '');
    $famille = Quantitatif::getFamilleByRepere($repere);
    if ($famille && $famille !== 'Non défini') {
        if (!isset($statsFamilles[$famille])) $statsFamilles[$famille] = 0;
        $statsFamilles[$famille]++;
    } else {
        $nonAffectes++;
    }
}

$labels = array_keys($statsFamilles);
$values = array_values($statsFamilles);

echo json_encode([
    'labels' => $labels,
    'values' => $values,
    'nonAffectes' => $nonAffectes
]);
