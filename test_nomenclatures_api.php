<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Test API Nomenclatures</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .result {
            background: #f5f5f5;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
        }

        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        button {
            padding: 10px 20px;
            margin: 5px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <h1>Test API Nomenclatures</h1>

    <div>
        <button onclick="testPagination()">Test Pagination API</button>
        <button onclick="testStats()">Test Statistiques API</button>
        <button onclick="testExport()">Test Export Progressif</button>
    </div>

    <div id="results"></div>

    <script>
        function addResult(content, isSuccess = true) {
            const div = document.createElement('div');
            div.className = 'result ' + (isSuccess ? 'success' : 'error');
            div.innerHTML = '<h3>' + (isSuccess ? '✅ Succès' : '❌ Erreur') + '</h3>' + content;
            document.getElementById('results').appendChild(div);
        }

        async function testPagination() {
            try {
                const response = await fetch('request/nomenclatures_paginated.php?page=1&limit=5');
                const data = await response.json();

                if (data.success) {
                    addResult(`
                        <p><strong>Pagination OK</strong></p>
                        <p>Total: ${data.total} nomenclatures</p>
                        <p>Page 1/5: ${data.data.length} résultats</p>
                        <p>Premier élément: ${data.data[0]?.code_equipement || 'N/A'} - ${data.data[0]?.designation_equipement || 'N/A'}</p>
                    `);
                } else {
                    addResult(`Erreur pagination: ${data.message}`, false);
                }
            } catch (error) {
                addResult(`Erreur réseau pagination: ${error.message}`, false);
            }
        }

        async function testStats() {
            try {
                const response = await fetch('request/nomenclatures_stats.php');
                const data = await response.json();

                if (data.success) {
                    addResult(`
                        <p><strong>Statistiques OK</strong></p>
                        <p>Total nomenclatures: ${data.stats.total}</p>
                        <p>Top famille: ${data.stats.topFamille?.nom || 'N/A'} (${data.stats.topFamille?.count || 0})</p>
                        <p>Top unité: ${data.stats.topUnite?.nom || 'N/A'} (${data.stats.topUnite?.count || 0})</p>
                        <p>Doublons détectés: ${data.stats.doublons?.length || 0}</p>
                    `);
                } else {
                    addResult(`Erreur statistiques: ${data.message}`, false);
                }
            } catch (error) {
                addResult(`Erreur réseau statistiques: ${error.message}`, false);
            }
        }

        async function testExport() {
            addResult(`
                <p><strong>Test Export Progressif</strong></p>
                <p>L'export progressif utilise Server-Sent Events.</p>
                <p>Pour tester complètement, utilisez la page principale.</p>
                <p>Vérification de l'endpoint...</p>
            `);

            try {
                const response = await fetch('request/export_nomenclatures_progressive.php', {
                    method: 'HEAD' // Test si le fichier existe
                });

                if (response.ok) {
                    addResult(`✅ Endpoint export_nomenclatures_progressive.php accessible`);
                } else {
                    addResult(`❌ Endpoint export inaccessible (${response.status})`, false);
                }
            } catch (error) {
                addResult(`Erreur test export: ${error.message}`, false);
            }
        }

        // Test automatique au chargement
        document.addEventListener('DOMContentLoaded', function() {
            addResult(`
                <p><strong>Tests API Nomenclatures - Système Moderne</strong></p>
                <p>Page de test pour vérifier le bon fonctionnement des nouvelles APIs.</p>
                <p>Cliquez sur les boutons pour tester chaque composant.</p>
            `);
        });
    </script>
</body>

</html>