<?php

session_start();
require_once 'model/Utilisateur.php';
require_once 'model/GroupeUtilisateur.php';
require_once 'model/DroitUtilisateur.php';
require_once 'includes/auth.php';

// Récupération des utilisateurs, groupes et droits
$utilisateurs = Utilisateur::getAll();
$groupes = GroupeUtilisateur::getAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .user-card {
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(60, 72, 88, 0.10);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.2s;
        }

        .user-card:hover {
            box-shadow: 0 8px 24px rgba(60, 72, 88, 0.18);
        }

        .user-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #e3eafc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #1976d2;
            margin-right: 1.2rem;
        }

        .user-actions .btn {
            margin-right: 0.4rem;
        }

        .badge-groupe {
            background: #1976d2;
            color: #fff;
            font-size: 0.95rem;
            border-radius: 6px;
            padding: 0.3em 0.7em;
        }

        .badge-inactif {
            background: #fce8e6;
            color: #d93025;
            font-size: 0.95rem;
            border-radius: 6px;
            padding: 0.3em 0.7em;
        }

        .no-user {
            text-align: center;
            color: #888;
            font-size: 1.1rem;
            margin-top: 2.5rem;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>
        <!-- Main Content -->
        <div class="flex-grow-1 content">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2 class="mb-0" style="font-weight:700;color:#1976d2;">Gestion des utilisateurs</h2>
                <a href="#" class="btn btn-primary" onclick="openAddUserModal();return false;">
                    <span class="material-icons">person_add</span> Nouvel utilisateur
                </a>
            </div>
            <div class="row">
                <?php if (empty($utilisateurs)): ?>
                    <div class="col-12">
                        <div class="no-user">
                            <span class="material-icons" style="font-size:2.5rem;">person_off</span><br>
                            Aucun utilisateur pour le moment.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($utilisateurs as $u): ?>
                        <div class="col-md-6">
                            <div class="card user-card p-3 d-flex flex-row align-items-center">
                                <div class="user-avatar">
                                    <span class="material-icons">
                                        <?= $u['actif'] ? 'person' : 'person_off' ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <span style="font-weight:600;font-size:1.1rem;"><?= htmlspecialchars($u['nom']) ?></span>
                                        <?php if (!$u['actif']): ?>
                                            <span class="badge-inactif ms-2">Inactif</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size:0.98rem;color:#555;">
                                        <span class="material-icons" style="font-size:1rem;vertical-align:middle;">mail</span>
                                        <?= htmlspecialchars($u['email']) ?>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge-groupe">
                                            Groupe :
                                            <?php
                                            $groupeNom = '';
                                            foreach ($groupes as $g) {
                                                if ($g['id'] == $u['groupe_id']) {
                                                    $groupeNom = $g['nom'];
                                                    break;
                                                }
                                            }
                                            echo htmlspecialchars($groupeNom);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="user-actions d-flex flex-column align-items-end ms-3">
                                    <a href="#" class="btn btn-outline-primary btn-sm mb-1" title="Modifier"
                                        onclick="openEditUserModal(<?= htmlspecialchars(json_encode($u)) ?>);return false;">
                                        <span class="material-icons">edit</span>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm mb-1" title="Droits"
                                        onclick="openRightsModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['groupe_id']) ?>');return false;">
                                        <span class="material-icons">security</span>
                                    </button>
                                    <?php if ($u['actif']): ?>
                                        <button class="btn btn-outline-warning btn-sm mb-1" title="Désactiver"
                                            onclick="openToggleModal(<?= $u['id'] ?>, false);return false;">
                                            <span class="material-icons">person_off</span>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success btn-sm mb-1" title="Réactiver"
                                            onclick="openToggleModal(<?= $u['id'] ?>, true);return false;">
                                            <span class="material-icons">person</span>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-danger btn-sm" title="Supprimer"
                                        onclick="openDeleteUserModal(<?= $u['id'] ?>);return false;">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modale Ajout/Édition Utilisateur -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="userForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Nouvel utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="userId">
                    <div class="mb-3">
                        <label for="userNom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="userNom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="userGroupe" class="form-label">Groupe</label>
                        <select class="form-control" id="userGroupe" name="groupe_id" required onchange="loadGroupRights(this.value)">
                            <option value="">Sélectionner un groupe</option>
                            <?php foreach ($groupes as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="groupRightsBox" style="display:none;">
                        <label class="form-label">Droits du groupe</label>
                        <div id="groupRightsList"></div>
                    </div>
                    <div class="mb-3" id="passwordField">
                        <label for="userPassword" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="userPassword" name="mot_de_passe">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="userActif" name="actif" checked>
                        <label class="form-check-label" for="userActif">Actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="userModalSubmit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Confirmation Suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="deleteForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Supprimer l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="deleteUserId">
                    <p>Voulez-vous vraiment supprimer cet utilisateur ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Désactivation/Réactivation -->
    <div class="modal fade" id="toggleModal" tabindex="-1" aria-labelledby="toggleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="toggleForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="toggleModalLabel">Changer le statut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="toggleUserId">
                    <input type="hidden" name="actif" id="toggleUserActif">
                    <p id="toggleText"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning" id="toggleBtn">Confirmer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale Droits/Groupe -->
    <div class="modal fade" id="rightsModal" tabindex="-1" aria-labelledby="rightsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="rightsForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="rightsModalLabel">Modifier le groupe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="rightsUserId">
                    <div class="mb-3">
                        <label for="rightsGroupe" class="form-label">Groupe</label>
                        <select class="form-control" id="rightsGroupe" name="groupe_id" required>
                            <option value="">Sélectionner un groupe</option>
                            <?php foreach ($groupes as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        let userModal = new bootstrap.Modal(document.getElementById('userModal'));
        let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        let toggleModal = new bootstrap.Modal(document.getElementById('toggleModal'));
        let rightsModal = new bootstrap.Modal(document.getElementById('rightsModal'));

        // Ouvrir modale ajout
        function openAddUserModal() {
            document.getElementById('userModalLabel').textContent = "Nouvel utilisateur";
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('passwordField').style.display = 'block';
            document.getElementById('userPassword').required = true; // Ajout
            document.getElementById('userGroupe').value = '';
            loadGroupRights('');
            userModal.show();
        }

        // Ouvrir modale édition
        function openEditUserModal(user) {
            document.getElementById('userModalLabel').textContent = "Modifier utilisateur";
            document.getElementById('userId').value = user.id;
            document.getElementById('userNom').value = user.nom;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userGroupe').value = user.groupe_id;
            loadGroupRights(user.groupe_id);
            document.getElementById('userActif').checked = user.actif == 1;
            document.getElementById('userPassword').value = '';
            document.getElementById('passwordField').style.display = 'none'; // Masque le champ
            document.getElementById('userPassword').required = false; // Pas requis en modif
            userModal.show();
        }

        // Ouvrir modale suppression
        function openDeleteUserModal(id) {
            document.getElementById('deleteUserId').value = id;
            deleteModal.show();
        }

        // Désactivation/réactivation
        function openToggleModal(id, activer) {
            document.getElementById('toggleUserId').value = id;
            document.getElementById('toggleUserActif').value = activer ? 1 : 0;
            document.getElementById('toggleText').textContent = activer ?
                "Voulez-vous réactiver cet utilisateur ?" :
                "Voulez-vous désactiver cet utilisateur ?";
            document.getElementById('toggleBtn').className = activer ? "btn btn-success" : "btn btn-warning";
            toggleModal.show();
        }
        document.getElementById('toggleForm').onsubmit = function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('request/user_toggle.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.message || "Erreur");
            });
        };

        // Gestion des droits/groupe
        function openRightsModal(id, groupe) {
            document.getElementById('rightsUserId').value = id;
            document.getElementById('rightsGroupe').value = groupe;
            rightsModal.show();
        }
        document.getElementById('rightsForm').onsubmit = function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('request/user_rights.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.message || "Erreur");
            });
        };

        // Soumission AJAX ajout/édition
        document.getElementById('userForm').onsubmit = function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('request/user_save.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.message || "Erreur");
            });
        };

        // Soumission AJAX suppression
        document.getElementById('deleteForm').onsubmit = function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('ajax/user_delete.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.message || "Erreur");
            });
        };

        // Charger les droits du groupe sélectionné
        function loadGroupRights(groupe_id) {
            if (!groupe_id) {
                document.getElementById('groupRightsBox').style.display = 'none';
                document.getElementById('groupRightsList').innerHTML = '';
                return;
            }
            fetch('request/group_rights.php?id=' + groupe_id)
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    if (data.droits && data.droits.length) {
                        data.droits.forEach(function(droit) {
                            html += '<span class="badge bg-info text-dark me-1 mb-1">' +
                                droit.ressource + ' : ' + droit.droit +
                                '</span>';
                        });
                    } else {
                        html = '<span class="text-muted">Aucun droit attribué</span>';
                    }
                    document.getElementById('groupRightsList').innerHTML = html;
                    document.getElementById('groupRightsBox').style.display = 'block';
                });
        }
    </script>
</body>

</html>