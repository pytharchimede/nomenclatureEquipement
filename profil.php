<?php

session_start();
require_once 'model/Utilisateur.php';

// Supposons que l'ID utilisateur est stocké en session
$user_logged =  $_SESSION['user'] ?? null;
if (!$user_logged) {
    header('Location: login.php');
    exit;
}
$user = Utilisateur::getById($user_logged['id']);

// Gestion de l'alerte profil incomplet
$infos_incompletes = [];
if (empty($user['telephone'])) $infos_incompletes[] = "Téléphone";
if (empty($user['photo_profil'])) $infos_incompletes[] = "Photo de profil";
if (empty($user['empreinte_numerique'])) $infos_incompletes[] = "Empreinte numérique";

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        .profile-box {
            max-width: 520px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(60, 72, 88, 0.13);
            padding: 2.5rem 2rem 2rem 2rem;
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e3eafc;
            background: #f8fafc;
            display: block;
            margin: 0 auto 1rem auto;
        }

        .profile-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .alert-incomplete {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'menu.php'; ?>
        <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="min-height:100vh;">
            <div class="profile-box">
                <h2 class="text-center mb-4" style="color:#1976d2;font-weight:700;">Mon profil</h2>
                <?php if (!empty($infos_incompletes)): ?>
                    <div class="alert-incomplete">
                        <span class="material-icons" style="vertical-align:middle;">warning</span>
                        Votre profil est incomplet : <?= implode(', ', $infos_incompletes) ?>.<br>
                        <span style="font-size:0.98em;">Merci de compléter les informations manquantes.</span>
                    </div>
                <?php endif; ?>
                <form id="profilForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="profile-upload mb-3">
                        <img src="<?= $user['photo_profil'] ? htmlspecialchars($user['photo_profil'] ?? '') : 'img/avatar_default.png' ?>"
                            id="avatarPreview" class="profile-avatar" alt="Photo de profil">
                        <label class="btn btn-outline-primary btn-sm mt-2">
                            <span class="material-icons">photo_camera</span> Changer la photo
                            <input type="file" name="photo_profil" id="photoProfilInput" accept="image/*" hidden onchange="previewPhoto(event)">
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" class="form-control" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                    </div>
                    <?php $showGen = empty($user['empreinte_numerique']); ?>
                    <div class="mb-3">
                        <label class="form-label">Empreinte numérique</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="empreinte_numerique" id="empreinteNumerique"
                                value="<?= htmlspecialchars($user['empreinte_numerique'] ?? '') ?>" readonly>
                            <?php if ($showGen): ?>
                                <button class="btn btn-outline-secondary" type="button" id="btnGenEmpreinte"
                                    onclick="genererEmpreinteUnique ();">Générer</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="mot_de_passe" id="newPassword" placeholder="Laisser vide pour ne pas changer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Confirmer le mot de passe">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Enregistrer les modifications</button>
                    </div>
                </form>
                <div id="profilMsg" class="mt-3"></div>
            </div>
        </div>
    </div>
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Aperçu photo de profil
        function previewPhoto(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Validation mot de passe
        document.getElementById('profilForm').onsubmit = function(e) {
            e.preventDefault();
            let pwd = document.getElementById('newPassword').value;
            let conf = document.getElementById('confirmPassword').value;
            if (pwd && pwd !== conf) {
                document.getElementById('profilMsg').innerHTML = '<div class="alert alert-warning">Les mots de passe ne correspondent pas.</div>';
                return;
            }
            let formData = new FormData(this);
            fetch('request/profil_save.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    document.getElementById('profilMsg').innerHTML = '<div class="alert alert-success">Profil mis à jour !</div>';
                    setTimeout(() => location.reload(), 1200);
                } else {
                    document.getElementById('profilMsg').innerHTML = '<div class="alert alert-danger">' + (data.message || 'Erreur lors de la mise à jour') + '</div>';
                }
            });
        };

        // Fonction pour générer une nouvelle empreinte numérique
        function genererEmpreinteUnique() {
            let nom = document.querySelector('[name=nom]').value;
            let email = "<?= htmlspecialchars($user['email']) ?>";
            fetch('request/generer_empreinte_unique.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        nom: nom,
                        email: email
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.empreinte) {
                        document.getElementById('empreinteNumerique').value = data.empreinte;
                    }
                });
        }
    </script>
</body>

</html>