<?php
require_once 'model/Database.php';
require_once 'model/Quantitatif.php';
require_once 'model/Famille.php';

$quantitatif = Quantitatif::getAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Quantitatif</title>
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .table-quantitatif th,
        .table-quantitatif td {
            font-size: 0.95em;
        }

        #logContent {
            background: #f8f9fa;
            border: 1px solid #b0b0b0;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 1em;
            padding: 1em;
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
            color: #222;
            box-shadow: 0 2px 8px #0001;
            margin-bottom: 1em;
        }

        #progressContainer {
            transition: opacity 0.5s;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'menu.php'; ?>
        <div class="flex-grow-1 content">
            <div class="container my-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0" style="color:#1976d2;font-weight:700;">Quantitatif</h2>
                    <form id="importForm" enctype="multipart/form-data" class="d-inline-block">
                        <label for="quantitatif" class="btn btn-primary mb-0">
                            <span class="material-icons" style="vertical-align:middle;">upload_file</span>
                            Importer Excel
                            <input type="file" name="quantitatif" id="quantitatif" accept=".xlsx" required style="display:none;">
                        </label>
                    </form>
                </div>
                <div id="progressContainer" style="display:none;">
                    <div class="progress mb-2" style="height:25px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%
                        </div>
                    </div>
                    <div id="progressText" class="small text-muted"></div>
                </div>
                <!-- Modal log -->
                <div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="logModalLabel">Rapport d'import</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                <pre id="logContent" style="max-height:400px;overflow:auto;"></pre>
                                <a id="downloadLog" href="#" download="rapport_import.txt" class="btn btn-outline-secondary btn-sm mt-2"
                                    target="_blank">
                                    Télécharger le log
                                </a>
                                <a id="viewLog" href="#" class="btn btn-outline-info btn-sm mt-2" target="_blank">
                                    Visualiser dans un nouvel onglet
                                </a>
                                <a id="downloadResidu" href="#" download="residu_import.csv" class="btn btn-outline-warning btn-sm mt-2"
                                    target="_blank" style="display:none;">
                                    Télécharger le résiduel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    Seules les feuilles contenant toutes les entêtes attendues sont importées.<br>
                    Le fichier doit contenir une feuille par famille, chaque feuille avec les colonnes attendues.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-quantitatif">
                        <thead class="table-light">
                            <tr>
                                <th>Famille</th>
                                <th>Repère</th>
                                <th>Unité</th>
                                <th>Quantité</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quantitatif as $q): ?>
                                <tr>
                                    <td><?= htmlspecialchars($q['famille'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($q['repere'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($q['unite'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($q['quantite'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($quantitatif)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Aucune donnée importée.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('quantitatif').addEventListener('change', function(e) {
            let form = document.getElementById('importForm');
            let fileInput = document.getElementById('quantitatif');
            if (!fileInput.files.length) return;
            let formData = new FormData(form);
            let xhr = new XMLHttpRequest();
            let progressBar = document.getElementById('progressBar');
            let progressContainer = document.getElementById('progressContainer');
            let progressText = document.getElementById('progressText');
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            progressBar.innerText = '0%';
            progressText.innerText = 'Envoi du fichier...';

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    let percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.innerText = percent + '%';
                }
            };
            xhr.onloadstart = function() {
                progressBar.style.width = '0%';
                progressBar.innerText = '0%';
                progressText.innerText = 'Envoi du fichier...';
            };
            xhr.onload = function() {
                progressBar.style.width = '100%';
                progressBar.innerText = '100%';
                progressText.innerText = 'Traitement terminé';
            };
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    progressBar.style.width = '100%';
                    progressBar.innerText = '100%';
                    progressText.innerText = 'Traitement terminé';

                    let resp = JSON.parse(xhr.responseText);

                    // Affiche le log dans la modale, scroll auto
                    let logModal = new bootstrap.Modal(document.getElementById('logModal'));
                    let logContent = document.getElementById('logContent');
                    logContent.textContent = resp.log_txt;
                    logContent.scrollTop = logContent.scrollHeight;

                    // Fournir le vrai log.txt du serveur si dispo
                    let downloadLog = document.getElementById('downloadLog');
                    let viewLog = document.getElementById('viewLog');
                    if (resp.log_url) {
                        downloadLog.href = resp.log_url;
                        viewLog.href = resp.log_url;
                    } else {
                        // fallback blob
                        let blob = new Blob([resp.log_txt], {
                            type: "text/plain"
                        });
                        let url = URL.createObjectURL(blob);
                        downloadLog.href = url;
                        viewLog.href = url;
                    }

                    // Résiduel
                    let residuBtn = document.getElementById('downloadResidu');
                    if (resp.residu_url) {
                        residuBtn.href = resp.residu_url;
                        residuBtn.style.display = '';
                    } else {
                        residuBtn.style.display = 'none';
                    }

                    logModal.show();

                    // Compte à rebours avant disparition de la jauge
                    let seconds = 5;
                    progressText.innerHTML = 'Traitement terminé<br>Fermeture de la jauge dans <span id="countdown">' + seconds + '</span> sec...';
                    let countdown = setInterval(function() {
                        seconds--;
                        document.getElementById('countdown').innerText = seconds;
                        if (seconds <= 0) {
                            clearInterval(countdown);
                            progressContainer.style.display = 'none';
                        }
                    }, 1000);

                    // Recharge la page après import pour afficher les nouvelles données
                    setTimeout(() => {
                        window.location.reload();
                    }, 5000);
                }
            };
            xhr.open('POST', 'request/import_quantitatif_ajax.php', true);
            xhr.send(formData);
        });
    </script>
</body>

</html>