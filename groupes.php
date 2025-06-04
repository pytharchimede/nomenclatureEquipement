<?php

session_start();
require_once 'model/GroupeUtilisateur.php';
require_once 'model/DroitUtilisateur.php';
require_once 'includes/auth.php';

// Récupération des groupes et de leurs droits
$groupes = GroupeUtilisateur::getAll();
function getDroitsForGroupe($groupe_id)
{
    return DroitUtilisateur::getByGroupe($groupe_id);
}

// Liste des ressources et droits possibles (à adapter selon ton appli)
$ressources = ['utilisateur', 'groupe', 'equipement', 'article', 'nomenclature'];
$droits_possibles = ['lire', 'creer', 'modifier', 'supprimer'];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des groupes utilisateurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .groupe-card {
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(60, 72, 88, 0.10);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.2s;
        }

        .groupe-card:hover {
            box-shadow: 0 8px 24px rgba(60, 72, 88, 0.18);
        }

        .badge-ressource {
            background: #e3eafc;
            color: #1976d2;
            font-size: 0.95rem;
            border-radius: 6px;
            padding: 0.3em 0.7em;
            margin-right: 0.3em;
        }

        .badge-droit {
            background: #1976d2;
            color: #fff;
            font-size: 0.93rem;
            border-radius: 6px;
            padding: 0.2em 0.6em;
            margin-right: 0.2em;
        }

        .no-groupe {
            text-align: center;
            color: #888;
            font-size: 1.1rem;
            margin-top: 2.5rem;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'menu.php'; ?>
        <div class="flex-grow-1 content">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Gestion des groupes utilisateurs</h2>
                <a href="#" class="btn btn-primary" onclick="openAddGroupeModal();return false;">
                    <span class="material-icons">group_add</span> Nouveau groupe
                </a>
            </div>
            <div class="row">
                <?php if (empty($groupes)): ?>
                    <div class="col-12">
                        <div class="no-groupe">
                            <span class="material-icons" style="font-size:2.5rem;">groups</span><br>
                            Aucun groupe pour le moment.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($groupes as $g): ?>
                        <div class="col-md-6">
                            <div class="card groupe-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span style="font-weight:600;font-size:1.1rem;"><?= htmlspecialchars($g['nom']) ?></span>
                                        <div style="font-size:0.97rem;color:#555;"><?= htmlspecialchars($g['description']) ?></div>
                                    </div>
                                    <div>
                                        <a href="#" class="btn btn-outline-primary btn-sm" title="Modifier"
                                            onclick="openEditGroupeModal(<?= htmlspecialchars(json_encode($g)) ?>);return false;">
                                            <span class="material-icons">edit</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <strong>Droits associés :</strong><br>
                                    <?php
                                    $droits = getDroitsForGroupe($g['id']);
                                    if (empty($droits)) {
                                        echo '<span class="text-muted">Aucun droit attribué</span>';
                                    } else {
                                        // Grouper par ressource
                                        $byRessource = [];
                                        foreach ($droits as $d) {
                                            $byRessource[$d['ressource']][] = $d['droit'];
                                        }
                                        foreach ($byRessource as $ressource => $droitsR) {
                                            echo '<span class="badge-ressource">' . htmlspecialchars($ressource) . '</span> ';
                                            foreach ($droitsR as $droit) {
                                                echo '<span class="badge-droit">' . htmlspecialchars($droit) . '</span> ';
                                            }
                                            echo '<br>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modale Ajout/Édition Groupe -->
    <div class="modal fade" id="groupeModal" tabindex="-1" aria-labelledby="groupeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="groupeForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="groupeModalLabel">Nouveau groupe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="groupeId">
                    <div class="mb-3">
                        <label for="groupeNom" class="form-label">Nom du groupe</label>
                        <input type="text" class="form-control" id="groupeNom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="groupeDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="groupeDescription" name="description">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Droits associés</label>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Ressource</th>
                                        <?php foreach ($droits_possibles as $d): ?>
                                            <th class="text-center"><?= ucfirst($d) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ressources as $ress): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ress) ?></td>
                                            <?php foreach ($droits_possibles as $d): ?>
                                                <td class="text-center">
                                                    <input type="checkbox" name="droits[<?= $ress ?>][]" value="<?= $d ?>" id="droit_<?= $ress ?>_<?= $d ?>">
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="groupeModalSubmit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        let groupeModal = new bootstrap.Modal(document.getElementById('groupeModal'));

        // Ouvrir modale ajout
        function openAddGroupeModal() {
            document.getElementById('groupeModalLabel').textContent = "Nouveau groupe";
            document.getElementById('groupeForm').reset();
            document.getElementById('groupeId').value = '';
            // Décocher tous les droits
            document.querySelectorAll('#groupeForm input[type=checkbox]').forEach(cb => cb.checked = false);
            groupeModal.show();
        }

        // Ouvrir modale édition
        function openEditGroupeModal(groupe) {
            document.getElementById('groupeModalLabel').textContent = "Modifier groupe";
            document.getElementById('groupeId').value = groupe.id;
            document.getElementById('groupeNom').value = groupe.nom;
            document.getElementById('groupeDescription').value = groupe.description || '';
            // Charger les droits du groupe via AJAX
            fetch('request/group_rights.php?id=' + groupe.id)
                .then(r => r.json())
                .then(data => {
                    // Décocher tous les droits
                    document.querySelectorAll('#groupeForm input[type=checkbox]').forEach(cb => cb.checked = false);
                    if (data.droits && data.droits.length) {
                        data.droits.forEach(function(droit) {
                            let cb = document.getElementById('droit_' + droit.ressource + '_' + droit.droit);
                            if (cb) cb.checked = true;
                        });
                    }
                    groupeModal.show();
                });
        }

        // Soumission AJAX ajout/édition
        document.getElementById('groupeForm').onsubmit = function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('request/groupe_save.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.message || "Erreur");
            });
        };
    </script>
</body>

</html>