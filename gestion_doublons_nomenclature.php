<?php
require_once 'model/Nomenclature.php';
require_once 'includes/auth.php';

$nomenclatures = Nomenclature::getAll();

// Regroupe les doublons
$dups = [];
$seen = [];
foreach ($nomenclatures as $nom) {
    $key = ($nom['code_equipement'] ?? '') . '|' . ($nom['code_article'] ?? '');
    if (!$nom['code_equipement'] || !$nom['code_article']) continue;
    if (isset($seen[$key])) {
        $dups[$key][] = $nom;
    } else {
        $seen[$key] = $nom;
    }
}
foreach ($seen as $key => $first) {
    if (isset($dups[$key])) {
        array_unshift($dups[$key], $first);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des doublons nomenclatures</title>
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .dups-table {
            margin-bottom: 2.5rem;
        }

        .dups-table th,
        .dups-table td {
            font-size: 0.97em;
        }

        .dups-actions {
            min-width: 120px;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'menu.php'; ?>
        <div class="flex-grow-1 content">
            <div class="container my-5">
                <h2 class="mb-4" style="color:#d32f2f;font-weight:700;">Gestion des doublons de nomenclatures</h2>
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
                    <div class="alert alert-success">Modifications enregistrées avec succès.</div>
                <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'suppression'): ?>
                    <div class="alert alert-info">Ligne supprimée avec succès.</div>
                <?php endif; ?>
                <?php if (!$dups): ?>
                    <div class="alert alert-success">Aucun doublon détecté.</div>
                <?php else: ?>
                    <div class="alert alert-warning mb-4">
                        <b><?= count($dups) ?></b> doublon(s) détecté(s). Pour chaque groupe, conservez une seule ligne ou modifiez les données si besoin.
                    </div>
                    <form id="dupsForm" method="post" action="request/gestion_doublons_save.php">
                        <?php foreach ($dups as $key => $group): ?>
                            <div class="card mb-4">
                                <div class="card-header bg-danger text-white">
                                    <b>Doublon : <?= htmlspecialchars($group[0]['code_equipement']) ?> / <?= htmlspecialchars($group[0]['code_article']) ?></b>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered dups-table mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Conserver</th>
                                                    <?php foreach ($group[0] as $col => $val): ?>
                                                        <?php if ($col === 'id') continue; ?>
                                                        <th><?= htmlspecialchars($col) ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="dups-actions">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($group as $i => $row): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="radio" name="keep[<?= $key ?>]" value="<?= $row['id'] ?>" <?= $i == 0 ? 'checked' : '' ?>>
                                                        </td>
                                                        <?php foreach ($row as $col => $val): ?>
                                                            <?php if ($col === 'id') continue; ?>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm" name="edit[<?= $row['id'] ?>][<?= $col ?>]" value="<?= htmlspecialchars($val ?? '') ?>">
                                                            </td>
                                                        <?php endforeach; ?>
                                                        <td>
                                                            <button type="submit" name="delete" value="<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Supprimer cette ligne ?')">
                                                                <span class="material-icons" style="font-size:1em;">delete</span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary btn-lg">Valider les modifications</button>
                        <a href="nomenclatures.php" class="btn btn-outline-secondary ms-2">Retour</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('dupsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showAlert('Modifications enregistrées avec succès.', 'success');
                        setTimeout(() => location.reload(), 1200);
                    } else if (res.deleted) {
                        showAlert('Ligne supprimée avec succès.', 'info');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        showAlert('Erreur lors du traitement.', 'danger');
                    }
                })
                .catch(() => showAlert('Erreur réseau.', 'danger'));
        });

        // Gestion suppression directe (bouton supprimer)
        document.querySelectorAll('button[name="delete"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Supprimer cette ligne ?')) return;
                const formData = new FormData();
                formData.append('delete', this.value);
                fetch('request/gestion_doublons_save.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.deleted) {
                            showAlert('Ligne supprimée avec succès.', 'info');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            showAlert('Erreur lors de la suppression.', 'danger');
                        }
                    })
                    .catch(() => showAlert('Erreur réseau.', 'danger'));
            });
        });

        // Fonction d'affichage d'alerte
        function showAlert(msg, type) {
            let alert = document.createElement('div');
            alert.className = 'alert alert-' + type + ' mt-3';
            alert.innerHTML = msg;
            document.querySelector('.container').prepend(alert);
            setTimeout(() => alert.remove(), 3000);
        }
    </script>
</body>

</html>