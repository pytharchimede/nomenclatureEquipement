<?php
session_start();
require_once 'model/Utilisateur.php';
require_once 'model/GroupeUtilisateur.php';
require_once 'includes/auth.php';

// Récupération des données utilisateur
$utilisateur = Utilisateur::getById($_SESSION['user_id']);
$groupes = GroupeUtilisateur::getAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - EquiNomTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style_dashboard.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            --warning-gradient: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            --danger-gradient: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            --purple-gradient: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
            --blue-gradient: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            --shadow-soft: 0 15px 45px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 25px 80px rgba(0, 0, 0, 0.2);
            --border-radius: 25px;
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            position: relative;
        }

        /* Particules d'arrière-plan */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(102, 126, 234, 0.3);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }

            50% {
                transform: translateY(-100px) rotate(180deg);
                opacity: 0.8;
            }
        }

        .profile-container {
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .profile-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.8) 100%);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-strong);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            animation: slideInDown 0.8s ease-out;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--primary-gradient);
        }

        .profile-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .profile-avatar {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
        }

        .avatar-container {
            position: relative;
            margin-right: 2rem;
        }

        .avatar-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: 700;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
            animation: pulse 2s ease-in-out infinite;
        }

        .avatar-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .avatar-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 35px;
            height: 35px;
            background: var(--success-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .profile-info h1 {
            font-size: 2.5rem;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            animation: slideInRight 0.8s ease-out 0.2s both;
        }

        .profile-subtitle {
            color: #6b7280;
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 1rem;
            animation: slideInRight 0.8s ease-out 0.4s both;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            transition: var(--transition);
            animation: slideInUp 0.8s ease-out;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-soft);
        }

        .stat-item:nth-child(1) {
            animation-delay: 0.6s;
        }

        .stat-item:nth-child(2) {
            animation-delay: 0.8s;
        }

        .stat-item:nth-child(3) {
            animation-delay: 1s;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #1f2937;
            display: block;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .profile-form {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideInLeft 0.8s ease-out;
        }

        .profile-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .sidebar-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideInRight 0.8s ease-out;
        }

        .sidebar-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .sidebar-card:nth-child(3) {
            animation-delay: 0.4s;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title .material-icons {
            color: #667eea;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid rgba(229, 231, 235, 0.8);
            border-radius: 12px;
            font-size: 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
            transform: translateY(-2px);
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            transition: var(--transition);
        }

        .form-control:focus+.input-icon {
            color: #667eea;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            padding: 1rem 2rem;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-soft);
        }

        .security-card {
            border-left: 4px solid #ff9800;
        }

        .activity-card {
            border-left: 4px solid #4caf50;
        }

        .settings-card {
            border-left: 4px solid #9c27b0;
        }

        .security-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
        }

        .security-item:last-child {
            border-bottom: none;
        }

        .security-label {
            font-weight: 600;
            color: #374151;
        }

        .security-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
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

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(229, 231, 235, 0.3);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--blue-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .activity-text {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.2rem;
        }

        .activity-time {
            color: #6b7280;
            font-size: 0.8rem;
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

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

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .profile-content {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 1rem;
            }

            .profile-header {
                padding: 2rem 1.5rem;
            }

            .profile-avatar {
                flex-direction: column;
                text-align: center;
            }

            .avatar-container {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .profile-info h1 {
                font-size: 2rem;
            }

            .profile-stats {
                grid-template-columns: 1fr;
            }
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-soft);
            border-left: 4px solid #4caf50;
            animation: slideInRight 0.3s ease-out;
            opacity: 0;
            transform: translateX(100%);
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
</head>

<body>
    <!-- Particules d'arrière-plan -->
    <div class="particles">
        <?php for ($i = 0; $i < 20; $i++): ?>
            <div class="particle" style="left: <?= rand(0, 100) ?>%; top: <?= rand(0, 100) ?>%; animation-delay: <?= rand(0, 60) / 10 ?>s;"></div>
        <?php endfor; ?>
    </div>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 profile-container">
            <!-- Header avec avatar et infos -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <div class="avatar-container">
                        <div class="avatar-image">
                            <?php if (!empty($utilisateur['photo_profil'])): ?>
                                <img src="<?= htmlspecialchars($utilisateur['photo_profil']) ?>" alt="Avatar">
                            <?php else: ?>
                                <?= strtoupper(substr($utilisateur['nom'], 0, 2)) ?>
                            <?php endif; ?>
                            <div class="avatar-badge">
                                <span class="material-icons">verified</span>
                            </div>
                        </div>
                    </div>
                    <div class="profile-info">
                        <h1><?= htmlspecialchars($utilisateur['nom']) ?></h1>
                        <div class="profile-subtitle">
                            <span class="material-icons" style="vertical-align: middle; margin-right: 5px;">email</span>
                            <?= htmlspecialchars($utilisateur['email']) ?>
                        </div>
                        <div class="profile-subtitle">
                            <span class="material-icons" style="vertical-align: middle; margin-right: 5px;">group</span>
                            Groupe: <?= htmlspecialchars($utilisateur['groupe_id'] ?? 'Non défini') ?>
                        </div>
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number">127</span>
                        <span class="stat-label">Connexions</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24j</span>
                        <span class="stat-label">Dernière activité</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Sécurité</span>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="profile-content">
                <!-- Formulaire de profil -->
                <div class="profile-form">
                    <form id="profileForm">
                        <div class="form-section">
                            <h3 class="section-title">
                                <span class="material-icons">person</span>
                                Informations personnelles
                            </h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Nom complet</label>
                                        <div class="input-group">
                                            <input type="text" id="nom" class="form-control" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
                                            <span class="input-icon material-icons">person</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email</label>
                                        <div class="input-group">
                                            <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($utilisateur['email']) ?>" required>
                                            <span class="input-icon material-icons">email</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Téléphone</label>
                                        <div class="input-group">
                                            <input type="tel" id="telephone" class="form-control" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>">
                                            <span class="input-icon material-icons">phone</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Groupe</label>
                                        <div class="input-group">
                                            <select id="groupe_id" class="form-control" required>
                                                <?php foreach ($groupes as $groupe): ?>
                                                    <option value="<?= $groupe['id'] ?>" <?= $groupe['id'] == $utilisateur['groupe_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($groupe['nom']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="input-icon material-icons">group</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="section-title">
                                <span class="material-icons">security</span>
                                Sécurité
                            </h3>

                            <div class="form-group">
                                <label class="form-label">Nouveau mot de passe</label>
                                <div class="input-group">
                                    <input type="password" id="nouveau_mot_de_passe" class="form-control" placeholder="Laissez vide pour conserver">
                                    <span class="input-icon material-icons" onclick="togglePassword('nouveau_mot_de_passe', this)">visibility_off</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Confirmer le mot de passe</label>
                                <div class="input-group">
                                    <input type="password" id="confirmer_mot_de_passe" class="form-control" placeholder="Confirmer le nouveau mot de passe">
                                    <span class="input-icon material-icons" onclick="togglePassword('confirmer_mot_de_passe', this)">visibility_off</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary">
                            <span class="material-icons">save</span>
                            Mettre à jour le profil
                        </button>
                    </form>
                </div>

                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <!-- Carte sécurité -->
                    <div class="sidebar-card security-card">
                        <h4 class="section-title">
                            <span class="material-icons">shield</span>
                            Sécurité du compte
                        </h4>

                        <div class="security-item">
                            <span class="security-label">Compte vérifié</span>
                            <span class="security-status status-active">Actif</span>
                        </div>

                        <div class="security-item">
                            <span class="security-label">Authentification 2FA</span>
                            <span class="security-status status-inactive">Désactivé</span>
                        </div>

                        <div class="security-item">
                            <span class="security-label">Sessions actives</span>
                            <span class="security-status status-active">2 sessions</span>
                        </div>
                    </div>

                    <!-- Carte activité récente -->
                    <div class="sidebar-card activity-card">
                        <h4 class="section-title">
                            <span class="material-icons">history</span>
                            Activité récente
                        </h4>

                        <div class="activity-item">
                            <div class="activity-icon">
                                <span class="material-icons">login</span>
                            </div>
                            <div class="activity-text">
                                <div class="activity-title">Connexion réussie</div>
                                <div class="activity-time">Il y a 2 heures</div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon" style="background: var(--success-gradient);">
                                <span class="material-icons">edit</span>
                            </div>
                            <div class="activity-text">
                                <div class="activity-title">Profil mis à jour</div>
                                <div class="activity-time">Hier à 14:30</div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon" style="background: var(--warning-gradient);">
                                <span class="material-icons">security</span>
                            </div>
                            <div class="activity-text">
                                <div class="activity-title">Mot de passe modifié</div>
                                <div class="activity-time">Il y a 3 jours</div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte paramètres -->
                    <div class="sidebar-card settings-card">
                        <h4 class="section-title">
                            <span class="material-icons">settings</span>
                            Paramètres rapides
                        </h4>

                        <button class="btn-primary w-100 mb-3" style="background: var(--purple-gradient);">
                            <span class="material-icons">photo_camera</span>
                            Changer la photo
                        </button>

                        <button class="btn-primary w-100 mb-3" style="background: var(--warning-gradient);">
                            <span class="material-icons">download</span>
                            Exporter les données
                        </button>

                        <button class="btn-primary w-100" style="background: var(--danger-gradient);">
                            <span class="material-icons">logout</span>
                            Déconnexion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Scripts -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="material-icons" style="color: ${type === 'success' ? '#4caf50' : '#f44336'};">
                        ${type === 'success' ? 'check_circle' : 'error'}
                    </span>
                    <span>${message}</span>
                </div>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('show');
            }, 100);

            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        // Handle profile form submission
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = {
                nom: document.getElementById('nom').value,
                email: document.getElementById('email').value,
                telephone: document.getElementById('telephone').value,
                groupe_id: document.getElementById('groupe_id').value,
                nouveau_mot_de_passe: document.getElementById('nouveau_mot_de_passe').value,
                confirmer_mot_de_passe: document.getElementById('confirmer_mot_de_passe').value
            };

            // Validation
            if (formData.nouveau_mot_de_passe && formData.nouveau_mot_de_passe !== formData.confirmer_mot_de_passe) {
                showToast('Les mots de passe ne correspondent pas', 'error');
                return;
            }

            try {
                const response = await fetch('request/profil_save.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Profil mis à jour avec succès !');
                    // Clear password fields
                    document.getElementById('nouveau_mot_de_passe').value = '';
                    document.getElementById('confirmer_mot_de_passe').value = '';
                } else {
                    showToast(data.message || 'Erreur lors de la mise à jour', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion au serveur', 'error');
                console.error('Erreur:', error);
            }
        });

        // Animation d'entrée au chargement
        window.addEventListener('load', function() {
            document.querySelectorAll('.profile-header, .profile-form, .sidebar-card').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';

                setTimeout(() => {
                    el.style.transition = 'all 0.6s ease-out';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>

</html>