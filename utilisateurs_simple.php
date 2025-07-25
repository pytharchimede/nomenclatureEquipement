<?php
session_start();
require_once 'model/Utilisateur.php';
require_once 'model/Database.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

// Récupération des utilisateurs et groupes
try {
    $utilisateurs = Utilisateur::getAll();

    // Récupération des groupes
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM groupe_utilisateur");
    $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Erreur lors du chargement des données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - EquiNomTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            --danger-gradient: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            --warning-gradient: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 20px 60px rgba(0, 0, 0, 0.2);
            --border-radius: 20px;
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .page-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            border-left: 5px solid #667eea;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-icon .material-icons {
            color: white;
            font-size: 1.8rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .users-table {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-soft);
            overflow-x: auto;
        }

        .table {
            margin: 0;
        }

        .table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #374151;
            padding: 1rem;
        }

        .table td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            margin-right: 1rem;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .status-inactive {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .btn-action {
            padding: 0.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.2rem;
        }

        .btn-edit {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .btn-edit:hover {
            background: rgba(102, 126, 234, 0.2);
        }

        .btn-delete {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .btn-delete:hover {
            background: rgba(244, 67, 54, 0.2);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            color: white;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
            color: white;
            text-decoration: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: none;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.1);
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        .nav-link {
            color: #667eea;
            text-decoration: none;
            margin-right: 1rem;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #764ba2;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <!-- Navigation simple -->
    <div style="margin-bottom: 2rem;">
        <a href="dashboard.php" class="nav-link">← Retour au tableau de bord</a>
        <a href="debug_users.php" class="nav-link">🔍 Debug</a>
        <a href="setup_database.php" class="nav-link">⚙️ Mettre à jour la BDD</a>
        <a href="logout.php" class="nav-link">Déconnexion</a>
    </div>

    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">Gestion des Utilisateurs</h1>
                <p class="page-subtitle">Administrez les comptes utilisateurs et leurs permissions</p>
            </div>
            <a href="#" class="btn-primary">
                <span class="material-icons me-2">person_add</span>
                Nouvel utilisateur
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <span class="material-icons me-2">error</span>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-icons">people</span>
            </div>
            <div class="stat-number"><?= isset($utilisateurs) ? count($utilisateurs) : '0' ?></div>
            <div class="stat-label">Total Utilisateurs</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-gradient);">
                <span class="material-icons">verified_user</span>
            </div>
            <div class="stat-number">
                <?= isset($utilisateurs) ? count(array_filter($utilisateurs, function ($u) {
                    return $u['actif'];
                })) : '0' ?>
            </div>
            <div class="stat-label">Utilisateurs Actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning-gradient);">
                <span class="material-icons">groups</span>
            </div>
            <div class="stat-number"><?= isset($groupes) ? count($groupes) : '0' ?></div>
            <div class="stat-label">Groupes</div>
        </div>
    </div>

    <!-- Table des utilisateurs -->
    <div class="users-table">
        <h3 style="margin-bottom: 1.5rem; color: #374151;">Liste des utilisateurs</h3>

        <?php if (isset($utilisateurs) && !empty($utilisateurs)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Groupe</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($user['nom'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($user['nom']) ?></strong>
                                        <?php if (!empty($user['telephone'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($user['telephone']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php
                                if (isset($groupes)) {
                                    $groupe = array_filter($groupes, function ($g) use ($user) {
                                        return $g['id'] == $user['groupe_id'];
                                    });
                                    echo $groupe ? htmlspecialchars(reset($groupe)['nom']) : 'Non défini';
                                } else {
                                    echo 'Groupe ' . ($user['groupe_id'] ?? 'N/A');
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $user['actif'] ? 'status-active' : 'status-inactive' ?>">
                                    <?= $user['actif'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($user['date_creation'])) ?></td>
                            <td>
                                <button class="btn-action btn-edit" title="Modifier">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="btn-action btn-delete" title="Supprimer">
                                    <span class="material-icons">delete</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-5">
                <span class="material-icons" style="font-size: 4rem; color: #ccc;">people_outline</span>
                <h3 style="color: #6b7280; margin-top: 1rem;">Aucun utilisateur trouvé</h3>
                <p style="color: #9ca3af;">Les utilisateurs s'afficheront ici une fois la base de données mise à jour.</p>
                <a href="setup_database.php" class="btn-primary mt-3">
                    <span class="material-icons me-2">settings</span>
                    Mettre à jour la base de données
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction simple pour les actions
        function editUser(id) {
            alert('Fonctionnalité d\'édition en cours de développement pour l\'utilisateur ID: ' + id);
        }

        function deleteUser(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                alert('Fonctionnalité de suppression en cours de développement pour l\'utilisateur ID: ' + id);
            }
        }

        // Ajouter les événements click
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des boutons d'édition
            document.querySelectorAll('.btn-edit').forEach(function(btn, index) {
                btn.addEventListener('click', function() {
                    editUser(index + 1);
                });
            });

            // Gestion des boutons de suppression
            document.querySelectorAll('.btn-delete').forEach(function(btn, index) {
                btn.addEventListener('click', function() {
                    deleteUser(index + 1);
                });
            });
        });
    </script>
</body>

</html>