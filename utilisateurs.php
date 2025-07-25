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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - EquiNomTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
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
            position: relative;
        }

        /* Arrière-plan animé avec particules */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.4), rgba(118, 75, 162, 0.4));
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.4;
            }

            50% {
                transform: translateY(-120px) rotate(180deg);
                opacity: 0.8;
            }
        }

        .users-container {
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .page-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            backdrop-filter: blur(25px);
            border-radius: var(--border-radius);
            padding: 3rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-strong);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            animation: slideInDown 0.8s ease-out;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--primary-gradient);
        }

        .page-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
            animation: rotate 25s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .users-container {
            padding: 2rem;
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

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .user-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .user-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-strong);
        }

        .user-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: white;
            font-weight: 700;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-info h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.2rem;
        }

        .user-email {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .user-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
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

        .user-details {
            margin-bottom: 1rem;
        }

        .user-detail {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .user-detail .material-icons {
            font-size: 1rem;
            margin-right: 0.5rem;
            color: #9ca3af;
        }

        .user-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn-action {
            padding: 0.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
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

        .btn-toggle {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .btn-toggle:hover {
            background: rgba(255, 152, 0, 0.2);
        }

        .floating-add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            border: none;
            box-shadow: var(--shadow-strong);
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
            z-index: 1000;
        }

        .floating-add-btn:hover {
            transform: scale(1.1);
        }

        /* Modal moderne */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: var(--shadow-strong);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 16px 16px 0 0;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 700;
        }

        .btn-close {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            opacity: 1;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 0.75rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        /* Animation d'entrée */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-card {
            animation: slideInUp 0.6s ease-out;
        }

        .user-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .user-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .user-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .user-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .users-container {
                padding: 1rem;
            }

            .users-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        /* États vides */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .search-bar {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
        }

        .search-input {
            border: none;
            background: #f9fafb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            width: 100%;
        }

        .search-input:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
    </style>
</head>

<body>
    <!-- Arrière-plan animé avec particules -->
    <div class="animated-bg">
        <?php for ($i = 0; $i < 25; $i++): ?>
            <div class="particle" style="left: <?= rand(0, 100) ?>%; top: <?= rand(0, 100) ?>%; animation-delay: <?= rand(0, 80) / 10 ?>s; animation-duration: <?= rand(6, 12) ?>s;"></div>
        <?php endfor; ?>
    </div>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 users-container">
            <!-- Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Gestion des Utilisateurs</h1>
                        <p class="page-subtitle">Administrez les comptes utilisateurs et leurs permissions</p>
                    </div>
                    <button class="btn btn-primary" onclick="openAddUserModal()">
                        <span class="material-icons me-2">person_add</span>
                        Nouvel utilisateur
                    </button>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">people</span>
                    </div>
                    <div class="stat-number" id="totalUsers"><?= count($utilisateurs) ?></div>
                    <div class="stat-label">Total Utilisateurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--success-gradient);">
                        <span class="material-icons">verified_user</span>
                    </div>
                    <div class="stat-number" id="activeUsers">
                        <?= count(array_filter($utilisateurs, function ($u) {
                            return $u['actif'];
                        })) ?>
                    </div>
                    <div class="stat-label">Utilisateurs Actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--warning-gradient);">
                        <span class="material-icons">groups</span>
                    </div>
                    <div class="stat-number"><?= count($groupes) ?></div>
                    <div class="stat-label">Groupes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--danger-gradient);">
                        <span class="material-icons">access_time</span>
                    </div>
                    <div class="stat-number" id="recentLogins">0</div>
                    <div class="stat-label">Connexions 24h</div>
                </div>
            </div>

            <!-- Barre de recherche -->
            <div class="search-bar">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0">
                        <span class="material-icons">search</span>
                    </span>
                    <input type="text" id="searchUsers" class="search-input" placeholder="Rechercher un utilisateur...">
                </div>
            </div>

            <!-- Grille des utilisateurs -->
            <div class="users-grid" id="usersGrid">
                <?php if (empty($utilisateurs)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <span class="material-icons">person_off</span>
                        </div>
                        <h3>Aucun utilisateur</h3>
                        <p>Commencez par créer votre premier utilisateur</p>
                        <button class="btn btn-primary mt-3" onclick="openAddUserModal()">
                            <span class="material-icons me-2">person_add</span>
                            Créer un utilisateur
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($utilisateurs as $user): ?>
                        <div class="user-card" data-user-id="<?= $user['id'] ?>">
                            <div class="user-status">
                                <span class="status-badge <?= $user['actif'] ? 'status-active' : 'status-inactive' ?>">
                                    <?= $user['actif'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </div>

                            <div class="user-header">
                                <div class="user-avatar">
                                    <?php if (!empty($user['photo_profil'])): ?>
                                        <img src="<?= htmlspecialchars($user['photo_profil']) ?>" alt="Avatar">
                                    <?php else: ?>
                                        <?= strtoupper(substr($user['nom'], 0, 2)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <h3><?= htmlspecialchars($user['nom']) ?></h3>
                                    <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>

                            <div class="user-details">
                                <?php if (!empty($user['telephone'])): ?>
                                    <div class="user-detail">
                                        <span class="material-icons">phone</span>
                                        <?= htmlspecialchars($user['telephone']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="user-detail">
                                    <span class="material-icons">event</span>
                                    Créé le <?= date('d/m/Y', strtotime($user['date_creation'])) ?>
                                </div>
                                <?php if (!empty($user['last_login_at'])): ?>
                                    <div class="user-detail">
                                        <span class="material-icons">login</span>
                                        Dernière connexion : <?= date('d/m/Y H:i', strtotime($user['last_login_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="user-actions">
                                <button class="btn-action btn-edit" title="Modifier" onclick="editUser(<?= $user['id'] ?>)">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="btn-action btn-toggle" title="<?= $user['actif'] ? 'Désactiver' : 'Activer' ?>"
                                    onclick="toggleUser(<?= $user['id'] ?>, <?= $user['actif'] ? 'false' : 'true' ?>)">
                                    <span class="material-icons"><?= $user['actif'] ? 'block' : 'check_circle' ?></span>
                                </button>
                                <button class="btn-action btn-delete" title="Supprimer" onclick="deleteUser(<?= $user['id'] ?>)">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Bouton flottant d'ajout -->
            <button class="floating-add-btn" onclick="openAddUserModal()" title="Ajouter un utilisateur">
                <span class="material-icons">add</span>
            </button>
        </div>
    </div>

    <!-- Modal d'ajout/édition utilisateur -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="userId" name="id">
                        <div class="mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="motDePasse">
                            <div class="form-text">Laissez vide pour conserver le mot de passe actuel (édition)</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="actif" checked>
                                <label class="form-check-label" for="actif">
                                    Compte actif
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()">
                        <span class="material-icons me-2">save</span>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let currentUserId = null;
        let users = <?= json_encode($utilisateurs) ?>;

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            loadRecentLoginStats();
            setupSearchFunctionality();
        });

        // Gestion de la recherche
        function setupSearchFunctionality() {
            const searchInput = document.getElementById('searchUsers');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const userCards = document.querySelectorAll('.user-card');

                userCards.forEach(card => {
                    const userName = card.querySelector('.user-info h3').textContent.toLowerCase();
                    const userEmail = card.querySelector('.user-email').textContent.toLowerCase();

                    if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Ouverture du modal d'ajout
        function openAddUserModal() {
            currentUserId = null;
            document.getElementById('userModalTitle').textContent = 'Nouvel Utilisateur';
            document.getElementById('userForm').reset();
            document.getElementById('actif').checked = true;
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }

        // Édition d'un utilisateur
        function editUser(userId) {
            currentUserId = userId;
            const user = users.find(u => u.id == userId);

            if (user) {
                document.getElementById('userModalTitle').textContent = 'Modifier l\'Utilisateur';
                document.getElementById('nom').value = user.nom;
                document.getElementById('email').value = user.email;
                document.getElementById('telephone').value = user.telephone || '';
                document.getElementById('motDePasse').value = '';
                document.getElementById('actif').checked = user.actif == 1;

                new bootstrap.Modal(document.getElementById('userModal')).show();
            }
        }

        // Sauvegarde d'un utilisateur
        async function saveUser() {
            const formData = {
                nom: document.getElementById('nom').value,
                email: document.getElementById('email').value,
                telephone: document.getElementById('telephone').value,
                mot_de_passe: document.getElementById('motDePasse').value,
                actif: document.getElementById('actif').checked ? 1 : 0
            };

            // Validation côté client
            if (!formData.nom || !formData.email) {
                showAlert('Veuillez remplir tous les champs obligatoires', 'warning');
                return;
            }

            try {
                const url = currentUserId ? 'request/user_save.php' : 'api/auth/register.php';
                if (currentUserId) {
                    formData.id = currentUserId;
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(currentUserId ? 'Utilisateur modifié avec succès' : 'Utilisateur créé avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message || 'Erreur lors de la sauvegarde', 'danger');
                }
            } catch (error) {
                showAlert('Erreur de connexion au serveur', 'danger');
                console.error('Erreur:', error);
            }
        }

        // Activation/Désactivation d'un utilisateur
        async function toggleUser(userId, newStatus) {
            if (!confirm(`Êtes-vous sûr de vouloir ${newStatus === 'true' ? 'activer' : 'désactiver'} cet utilisateur ?`)) {
                return;
            }

            try {
                const response = await fetch('request/user_toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: userId,
                        actif: newStatus === 'true'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Statut utilisateur mis à jour', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message || 'Erreur lors de la mise à jour', 'danger');
                }
            } catch (error) {
                showAlert('Erreur de connexion au serveur', 'danger');
                console.error('Erreur:', error);
            }
        }

        // Suppression d'un utilisateur
        async function deleteUser(userId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
                return;
            }

            try {
                const response = await fetch('request/user_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: userId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Utilisateur supprimé avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message || 'Erreur lors de la suppression', 'danger');
                }
            } catch (error) {
                showAlert('Erreur de connexion au serveur', 'danger');
                console.error('Erreur:', error);
            }
        }

        // Chargement des statistiques de connexion
        async function loadRecentLoginStats() {
            try {
                const response = await fetch('api/users/recent-logins.php');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('recentLogins').textContent = data.count;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
            }
        }

        // Affichage des alertes
        function showAlert(message, type = 'info') {
            // Créer une alerte Bootstrap moderne
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <span class="material-icons me-2" style="vertical-align: middle; font-size: 1.2rem;">
                        ${type === 'success' ? 'check_circle' : type === 'warning' ? 'warning' : type === 'danger' ? 'error' : 'info'}
                    </span>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', alertHtml);

            // Auto-suppression après 5 secondes
            setTimeout(() => {
                const alert = document.querySelector('.alert:last-child');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }

        // Responsive sidebar toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
    </script>
</body>

</html>