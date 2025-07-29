<?php
// Test script pour l'API ultra-rapide
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST API ULTRA-RAPIDE ===\n";

// Simuler les paramètres GET
$_GET['type'] = 'all_data';

echo "Test avec type=all_data\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Capturer la sortie de l'API
ob_start();
include 'request/stats_non_sap_ultra_fast.php';
$output = ob_get_clean();

echo "Réponse de l'API:\n";
echo $output . "\n";

// Vérifier que c'est du JSON valide
$decoded = json_decode($output, true);
if ($decoded === null) {
    echo "\nERREUR: Réponse JSON invalide\n";
    echo "Erreur JSON: " . json_last_error_msg() . "\n";
} else {
    echo "\n✅ JSON valide reçu\n";
    if (isset($decoded['success']) && $decoded['success']) {
        echo "✅ API répond avec succès\n";
        if (isset($decoded['data'])) {
            echo "✅ Données présentes\n";
            if (isset($decoded['data']['stats'])) {
                echo "✅ Statistiques générales: " . print_r($decoded['data']['stats'], true) . "\n";
            }
        }
    } else {
        echo "❌ API en erreur: " . ($decoded['error'] ?? 'Erreur inconnue') . "\n";
    }
}
