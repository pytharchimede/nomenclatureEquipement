<?php
session_start();
require_once 'model/GroupeUtilisateur.php';
require_once 'model/DroitUtilisateur.php';
require_once 'includes/auth.php';

// Récupération des groupes et droits
$groupes = GroupeUtilisateur::getAll();
$droitsDisponibles = [
    'utilisateur' => ['lire', 'creer', 'modifier', 'supprimer'],
    'groupe' => ['lire', 'creer', 'modifier', 'supprimer'],
    'equipement' => ['lire', 'creer', 'modifier', 'supprimer', 'exporter'],
    'article' => ['lire', 'creer', 'modifier', 'supprimer', 'exporter'],
    'nomenclature' => ['lire', 'creer', 'modifier', 'supprimer', 'exporter'],
    'quantitatif' => ['lire', 'creer', 'modifier', 'supprimer', 'importer', 'exporter']
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Groupes - EquiNomTech</title>
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
            --green-gradient: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            --orange-gradient: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            --shadow-soft: 0 15px 45px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 25px 80px rgba(0, 0, 0, 0.15);
            --shadow-hover: 0 30px 100px rgba(0, 0, 0, 0.2);
            --border-radius: 25px;
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            position: relative;
        }

        /* Arrière-plan animé */
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

        .animated-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
            animation: rotate 30s linear infinite;
        }

        .animated-bg::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
            animation: rotate 40s linear infinite reverse;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .groups-container {
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .page-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            backdrop-filter: blur(20px);
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

        .page-title {
            font-size: 2.5rem;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            animation: slideInLeft 0.8s ease-out 0.2s both;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1.2rem;
            font-weight: 500;
            animation: slideInLeft 0.8s ease-out 0.4s both;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .stat-card:nth-child(1) {
            animation-delay: 0.6s;
            border-left: 5px solid #667eea;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.8s;
            border-left: 5px solid #4caf50;
        }

        .stat-card:nth-child(3) {
            animation-delay: 1s;
            border-left: 5px solid #ff9800;
        }

        .stat-card:nth-child(4) {
            animation-delay: 1.2s;
            border-left: 5px solid #9c27b0;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }

        .stat-icon .material-icons {
            color: white;
            font-size: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 900;
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

        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .group-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }

        .group-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .group-card:nth-child(1) {
            animation-delay: 0.2s;
        }

        .group-card:nth-child(2) {
            animation-delay: 0.4s;
        }

        .group-card:nth-child(3) {
            animation-delay: 0.6s;
        }

        .group-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .group-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            animation: rotate 10s linear infinite;
        }

        .group-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.3rem;
        }

        .group-description {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .group-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .mini-stat {
            text-align: center;
            padding: 1rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            transition: var(--transition);
        }

        .mini-stat:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: scale(1.05);
        }

        .mini-stat-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: #667eea;
            display: block;
        }

        .mini-stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
        }

        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .permission-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: var(--transition);
        }

        .permission-badge:hover {
            transform: scale(1.1);
        }

        .permission-read {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .permission-create {
            background: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }

        .permission-update {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .permission-delete {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .permission-export {
            background: rgba(156, 39, 176, 0.1);
            color: #9c27b0;
        }

        .permission-import {
            background: rgba(63, 81, 181, 0.1);
            color: #3f51b5;
        }

        .group-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .btn-action {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .btn-edit {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .btn-edit:hover {
            background: var(--primary-gradient);
            color: white;
            transform: scale(1.1);
        }

        .btn-delete {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .btn-delete:hover {
            background: var(--danger-gradient);
            color: white;
            transform: scale(1.1);
        }

        .btn-rights {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .btn-rights:hover {
            background: var(--warning-gradient);
            color: white;
            transform: scale(1.1);
        }

        .floating-add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--primary-gradient);
            border: none;
            box-shadow: var(--shadow-strong);
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            transition: var(--transition);
            z-index: 1000;
            animation: bounce 2s ease-in-out infinite;
        }

        .floating-add-btn:hover {
            transform: scale(1.2);
            box-shadow: var(--shadow-hover);
        }

        /* Modal moderne */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-strong);
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
            border-bottom: none;
            padding: 2rem;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .btn-close {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            opacity: 1;
            width: 35px;
            height: 35px;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
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

        /* Responsive */
        @media (max-width: 768px) {
            .groups-container {
                padding: 1rem;
            }

            .groups-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .page-header {
                padding: 2rem 1.5rem;
            }

            .page-title {
                font-size: 2rem;
            }
        }

        /* État vide */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            animation: slideInUp 0.8s ease-out;
        }

        .empty-icon {
            font-size: 5rem;
            color: #e5e7eb;
            margin-bottom: 2rem;
            animation: pulse 2s ease-in-out infinite;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .empty-description {
            color: #9ca3af;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <!-- Arrière-plan animé -->
    <div class="animated-bg"></div>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 groups-container">
            <!-- Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Gestion des Groupes</h1>
                        <p class="page-subtitle">Administrez les groupes utilisateurs et leurs permissions</p>
                    </div>
                    <button class="btn btn-primary btn-lg" onclick="openAddGroupModal()">
                        <span class="material-icons me-2">group_add</span>
                        Nouveau groupe
                    </button>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">groups</span>
                    </div>
                    <div class="stat-number"><?= count($groupes) ?></div>
                    <div class="stat-label">Groupes Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--success-gradient);">
                        <span class="material-icons">verified_user</span>
                    </div>
                    <div class="stat-number">127</div>
                    <div class="stat-label">Utilisateurs Actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--warning-gradient);">
                        <span class="material-icons">security</span>
                    </div>
                    <div class="stat-number">42</div>
                    <div class="stat-label">Permissions Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--purple-gradient);">
                        <span class="material-icons">admin_panel_settings</span>
                    </div>
                    <div class="stat-number">3</div>
                    <div class="stat-label">Rôles Actifs</div>
                </div>
            </div>

            <!-- Grille des groupes -->
            <div class="groups-grid" id="groupsGrid">
                <?php if (empty($groupes)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <span class="material-icons">group_off</span>
                        </div>
                        <h3 class="empty-title">Aucun groupe</h3>
                        <p class="empty-description">Commencez par créer votre premier groupe d'utilisateurs</p>
                        <button class="btn btn-primary btn-lg" onclick="openAddGroupModal()">
                            <span class="material-icons me-2">group_add</span>
                            Créer un groupe
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($groupes as $index => $groupe): ?>
                        <div class="group-card" data-group-id="<?= $groupe['id'] ?>">
                            <div class="group-header">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="group-icon" style="background: <?= ['var(--primary-gradient)', 'var(--success-gradient)', 'var(--warning-gradient)', 'var(--purple-gradient)'][$index % 4] ?>;">
                                        <?= strtoupper(substr($groupe['nom'], 0, 2)) ?>
                                    </div>
                                    <div class="group-info">
                                        <h3><?= htmlspecialchars($groupe['nom']) ?></h3>
                                        <div class="group-description"><?= htmlspecialchars($groupe['description'] ?? 'Aucune description') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="group-stats">
                                <div class="mini-stat">
                                    <span class="mini-stat-number">24</span>
                                    <span class="mini-stat-label">Utilisateurs</span>
                                </div>
                                <div class="mini-stat">
                                    <span class="mini-stat-number">12</span>
                                    <span class="mini-stat-label">Permissions</span>
                                </div>
                                <div class="mini-stat">
                                    <span class="mini-stat-number">98%</span>
                                    <span class="mini-stat-label">Activité</span>
                                </div>
                            </div>

                            <div class="permissions-grid">
                                <div class="permission-badge permission-read">Lecture</div>
                                <div class="permission-badge permission-create">Création</div>
                                <div class="permission-badge permission-update">Modification</div>
                                <div class="permission-badge permission-delete">Suppression</div>
                                <div class="permission-badge permission-export">Export</div>
                                <div class="permission-badge permission-import">Import</div>
                            </div>

                            <div class="group-actions">
                                <button class="btn-action btn-rights" title="Gérer les droits" onclick="manageRights(<?= $groupe['id'] ?>)">
                                    <span class="material-icons">admin_panel_settings</span>
                                </button>
                                <button class="btn-action btn-edit" title="Modifier" onclick="editGroup(<?= $groupe['id'] ?>)">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="btn-action btn-delete" title="Supprimer" onclick="deleteGroup(<?= $groupe['id'] ?>)">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Bouton flottant d'ajout -->
            <button class="floating-add-btn" onclick="openAddGroupModal()" title="Ajouter un groupe">
                <span class="material-icons">add</span>
            </button>
        </div>
    </div>

    <!-- Modal d'ajout/édition de groupe -->
    <div class="modal fade" id="groupModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="groupModalTitle">Nouveau Groupe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="groupForm">
                        <input type="hidden" id="groupId" name="id">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nom du groupe</label>
                            <input type="text" class="form-control" id="groupName" required placeholder="Ex: Administrateurs">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="groupDescription" rows="3" placeholder="Description du rôle et des responsabilités..."></textarea>
                        </div>

                        <h6 class="fw-bold mb-3">Permissions</h6>
                        <div id="permissionsContainer">
                            <?php foreach ($droitsDisponibles as $ressource => $actions): ?>
                                <div class="mb-3">
                                    <h6 class="text-capitalize fw-semibold text-primary"><?= ucfirst($ressource) ?></h6>
                                    <div class="row">
                                        <?php foreach ($actions as $action): ?>
                                            <div class="col-md-4 col-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="perm_<?= $ressource ?>_<?= $action ?>"
                                                        name="permissions[<?= $ressource ?>][]"
                                                        value="<?= $action ?>">
                                                    <label class="form-check-label text-capitalize"
                                                        for="perm_<?= $ressource ?>_<?= $action ?>">
                                                        <?= ucfirst($action) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveGroup()">
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
        let currentGroupId = null;

        // Ouverture du modal d'ajout
        function openAddGroupModal() {
            currentGroupId = null;
            document.getElementById('groupModalTitle').textContent = 'Nouveau Groupe';
            document.getElementById('groupForm').reset();
            new bootstrap.Modal(document.getElementById('groupModal')).show();
        }

        // Édition d'un groupe
        function editGroup(groupId) {
            currentGroupId = groupId;
            document.getElementById('groupModalTitle').textContent = 'Modifier le Groupe';

            // Ici, vous devriez charger les données du groupe via AJAX
            // Pour l'exemple, on simule
            fetch(`api/groups/get.php?id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('groupName').value = data.group.nom;
                        document.getElementById('groupDescription').value = data.group.description || '';
                        // Charger les permissions...
                    }
                });

            new bootstrap.Modal(document.getElementById('groupModal')).show();
        }

        // Gestion des droits
        function manageRights(groupId) {
            // Ouvrir un modal spécialisé pour la gestion des droits
            alert(`Gestion des droits pour le groupe ${groupId} - À implémenter`);
        }

        // Suppression d'un groupe
        function deleteGroup(groupId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce groupe ? Cette action est irréversible.')) {
                return;
            }

            fetch('api/groups/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: groupId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Groupe supprimé avec succès', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Erreur lors de la suppression', 'error');
                    }
                })
                .catch(error => {
                    showToast('Erreur de connexion au serveur', 'error');
                    console.error('Erreur:', error);
                });
        }

        // Sauvegarde d'un groupe
        function saveGroup() {
            const formData = new FormData(document.getElementById('groupForm'));
            const data = {
                id: currentGroupId,
                nom: document.getElementById('groupName').value,
                description: document.getElementById('groupDescription').value,
                permissions: {}
            };

            // Récupérer les permissions cochées
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                const [, ressource, action] = checkbox.id.split('_');
                if (!data.permissions[ressource]) {
                    data.permissions[ressource] = [];
                }
                data.permissions[ressource].push(action);
            });

            fetch('api/groups/save.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(currentGroupId ? 'Groupe modifié avec succès' : 'Groupe créé avec succès', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Erreur lors de la sauvegarde', 'error');
                    }
                })
                .catch(error => {
                    showToast('Erreur de connexion au serveur', 'error');
                    console.error('Erreur:', error);
                });
        }

        // Affichage des toasts
        function showToast(message, type = 'info') {
            const toastHtml = `
                <div class="toast show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                    <div class="toast-body d-flex align-items-center p-3">
                        <span class="material-icons me-2" style="color: ${type === 'success' ? '#4caf50' : '#f44336'};">
                            ${type === 'success' ? 'check_circle' : 'error'}
                        </span>
                        ${message}
                        <button type="button" class="btn-close ms-auto" onclick="this.closest('.toast').remove()"></button>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', toastHtml);

            setTimeout(() => {
                const toast = document.querySelector('.toast:last-child');
                if (toast) toast.remove();
            }, 5000);
        }

        // Animation d'entrée au chargement
        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.group-card, .stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>

</html>