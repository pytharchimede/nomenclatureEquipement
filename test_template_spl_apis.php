<?php
// Script de test pour les APIs Template SPL
echo "=== Test des APIs Template SPL ===\n\n";

// Test 1: API Données
echo "1. Test API Données...\n";
$url1 = 'http://localhost/nomenclatureequipement/api/template_spl_data.php?page=1&limit=5';
$response1 = @file_get_contents($url1);
if ($response1) {
    $data1 = json_decode($response1, true);
    if ($data1 && isset($data1['success'])) {
        echo "   ✅ API Données OK - " . ($data1['success'] ? 'Succès' : 'Erreur: ' . ($data1['message'] ?? 'Unknown')) . "\n";
        if ($data1['success']) {
            echo "   📊 Total records: " . ($data1['pagination']['total_records'] ?? 0) . "\n";
        }
    } else {
        echo "   ❌ API Données - JSON invalide\n";
    }
} else {
    echo "   ❌ API Données - Pas de réponse\n";
}

echo "\n";

// Test 2: API Statistiques
echo "2. Test API Statistiques...\n";
$url2 = 'http://localhost/nomenclatureequipement/api/template_spl_stats.php';
$response2 = @file_get_contents($url2);
if ($response2) {
    $data2 = json_decode($response2, true);
    if ($data2 && isset($data2['success'])) {
        echo "   ✅ API Stats OK - " . ($data2['success'] ? 'Succès' : 'Erreur: ' . ($data2['message'] ?? 'Unknown')) . "\n";
        if ($data2['success']) {
            echo "   📈 Total lignes: " . ($data2['stats']['total_lignes'] ?? 0) . "\n";
            echo "   📋 Total articles: " . ($data2['stats']['total_articles_uniques'] ?? 0) . "\n";
        }
    } else {
        echo "   ❌ API Stats - JSON invalide\n";
    }
} else {
    echo "   ❌ API Stats - Pas de réponse\n";
}

echo "\n";

// Test 3: API Realtime
echo "3. Test API Realtime...\n";
$url3 = 'http://localhost/nomenclatureequipement/api/template_spl_realtime.php';
$response3 = @file_get_contents($url3);
if ($response3) {
    $data3 = json_decode($response3, true);
    if ($data3 && isset($data3['success'])) {
        echo "   ✅ API Realtime OK - " . ($data3['success'] ? 'Succès' : 'Erreur: ' . ($data3['message'] ?? 'Unknown')) . "\n";
    } else {
        echo "   ❌ API Realtime - JSON invalide\n";
    }
} else {
    echo "   ❌ API Realtime - Pas de réponse\n";
}

echo "\n";

// Test 4: API Monitoring
echo "4. Test API Monitoring...\n";
$url4 = 'http://localhost/nomenclatureequipement/api/template_spl_monitoring.php';
$response4 = @file_get_contents($url4);
if ($response4) {
    $data4 = json_decode($response4, true);
    if ($data4 && isset($data4['success'])) {
        echo "   ✅ API Monitoring OK - " . ($data4['success'] ? 'Succès' : 'Erreur: ' . ($data4['message'] ?? 'Unknown')) . "\n";
        if ($data4['success']) {
            echo "   💾 Mémoire utilisée: " . ($data4['performance']['memory_usage']['current'] ?? 0) . " MB\n";
            echo "   💽 Espace libre: " . ($data4['performance']['disk_space']['free'] ?? 0) . " GB\n";
        }
    } else {
        echo "   ❌ API Monitoring - JSON invalide\n";
    }
} else {
    echo "   ❌ API Monitoring - Pas de réponse\n";
}

echo "\n=== Test terminé ===\n";
?>
