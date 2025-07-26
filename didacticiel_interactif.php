<?php
require_once 'includes/auth.php';
require_once 'model/Database.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎓 Didacticiel Interactif - EquiNomTech</title>

    <!-- Bootstrap 5 -->
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        :root {
            --primary-color: #1976d2;
            --secondary-color: #667eea;
            --accent-color: #764ba2;
            --success-color: #43a047;
            --warning-color: #ff8f00;
            --danger-color: #e53935;
            --info-color: #00acc1;
            --dark-color: #212529;
            --light-color: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #43a047 0%, #4caf50 100%);
            --gradient-warning: linear-gradient(135deg, #ff8f00 0%, #ffa726 100%);
            --gradient-danger: linear-gradient(135deg, #e53935 0%, #ef5350 100%);
            --gradient-info: linear-gradient(135deg, #00acc1 0%, #26c6da 100%);
            --shadow-soft: 0 2px 20px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 8px 40px rgba(0, 0, 0, 0.15);
            --border-radius: 15px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header Héro */
        .hero-header {
            background: var(--gradient-primary);
            color: white;
            padding: 60px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,100 1000,0 1000,100"/></svg>');
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.4rem;
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 40px;
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
        }

        .hero-stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Navigation Didacticiel */
        .tutorial-nav {
            background: white;
            padding: 20px 0;
            box-shadow: var(--shadow-soft);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-progress {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .nav-progress-bar {
            height: 100%;
            background: var(--gradient-primary);
            width: 0%;
            transition: width 0.5s ease;
            border-radius: 2px;
        }

        .nav-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            padding: 15px;
            border-radius: var(--border-radius);
            min-width: 120px;
        }

        .nav-step:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .nav-step.active {
            background: var(--gradient-primary);
            color: white;
            transform: scale(1.05);
        }

        .nav-step.completed {
            background: var(--gradient-success);
            color: white;
        }

        .nav-step-icon {
            font-size: 2rem;
            margin-bottom: 8px;
            transition: var(--transition);
        }

        .nav-step.active .nav-step-icon,
        .nav-step.completed .nav-step-icon {
            transform: scale(1.2);
        }

        .nav-step-title {
            font-weight: 600;
            font-size: 0.85rem;
            text-align: center;
        }

        .nav-step-subtitle {
            font-size: 0.7rem;
            opacity: 0.8;
            text-align: center;
        }

        /* Contenu Principal */
        .main-content {
            background: white;
            margin: 40px auto;
            max-width: 1200px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .content-section {
            display: none;
            padding: 60px;
            min-height: 600px;
        }

        .content-section.active {
            display: block;
            animation: fadeInUp 0.6s ease-out;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #666;
            font-weight: 400;
        }

        /* Cards Interactives */
        .feature-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .feature-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-color);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card-icon {
            font-size: 3rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }

        .feature-card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .feature-card-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .feature-card-action {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: var(--transition);
        }

        .feature-card-action:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        /* Contrôles Navigation */
        .nav-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 60px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .nav-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }

        .nav-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .nav-btn.secondary {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .nav-btn.secondary:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Animations personnalisées */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
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

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 30px;
            }

            .nav-steps {
                overflow-x: auto;
                justify-content: flex-start;
                padding-bottom: 10px;
            }

            .nav-step {
                min-width: 100px;
                flex-shrink: 0;
            }

            .content-section {
                padding: 30px 20px;
            }

            .nav-controls {
                padding: 20px;
                flex-direction: column;
                gap: 15px;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        /* Indicateurs d'état */
        .status-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--success-color);
            box-shadow: 0 0 0 3px rgba(67, 160, 71, 0.3);
        }

        .status-indicator.pending {
            background: var(--warning-color);
            box-shadow: 0 0 0 3px rgba(255, 143, 0, 0.3);
        }

        .status-indicator.inactive {
            background: #ccc;
            box-shadow: 0 0 0 3px rgba(204, 204, 204, 0.3);
        }

        /* Tooltips personnalisés */
        .tooltip-custom {
            position: relative;
            cursor: pointer;
        }

        .tooltip-custom::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark-color);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: var(--transition);
            z-index: 1000;
        }

        .tooltip-custom:hover::after {
            opacity: 1;
            transform: translateX(-50%) translateY(-5px);
        }

        /* Styles pour la démonstration d'interface */
        .interface-demo {
            margin: 40px 0;
        }

        .mockup-browser {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            max-width: 100%;
            margin: 0 auto;
        }

        .mockup-header {
            background: #f0f0f0;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .mockup-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-close,
        .btn-minimize,
        .btn-maximize {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: block;
        }

        .btn-close {
            background: #ff5f57;
        }

        .btn-minimize {
            background: #ffbd2e;
        }

        .btn-maximize {
            background: #28ca42;
        }

        .mockup-url {
            background: white;
            padding: 8px 15px;
            border-radius: 6px;
            color: #666;
            font-size: 0.9rem;
            flex: 1;
            border: 1px solid #ddd;
        }

        .mockup-content {
            display: flex;
            min-height: 400px;
        }

        .demo-sidebar {
            width: 280px;
            background: #2c3e50;
            color: white;
            padding: 0;
            flex-shrink: 0;
        }

        .demo-logo {
            padding: 20px;
            background: #34495e;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
            border-bottom: 1px solid #34495e;
        }

        .demo-menu {
            padding: 0;
        }

        .demo-menu-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }

        .demo-menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .demo-menu-item.active {
            background: var(--primary-color);
            box-shadow: inset 4px 0 0 #fff;
        }

        .demo-menu-item.clickable:hover {
            background: rgba(102, 126, 234, 0.2);
        }

        .demo-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .demo-badge {
            margin-left: auto;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .demo-badge-success {
            background: var(--success-color);
            color: white;
        }

        .demo-badge-info {
            background: var(--info-color);
            color: white;
        }

        .demo-badge-primary {
            background: var(--primary-color);
            color: white;
        }

        .demo-badge-secondary {
            background: #6c757d;
            color: white;
        }

        .demo-badge-danger {
            background: var(--danger-color);
            color: white;
        }

        .demo-badge-warning {
            background: var(--warning-color);
            color: white;
        }

        .demo-main-content {
            flex: 1;
            padding: 25px;
            background: #f8f9fa;
        }

        .demo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .demo-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .demo-logout-btn {
            background: var(--gradient-danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .demo-logout-btn:hover {
            transform: scale(1.05);
        }

        .demo-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .demo-metric-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            text-align: center;
            transition: var(--transition);
        }

        .demo-metric-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .demo-metric-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .demo-metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .demo-metric-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
        }

        .demo-metric-change {
            font-size: 0.8rem;
            color: var(--success-color);
            font-weight: 500;
        }

        /* Cards d'explication */
        .explanation-cards {
            margin: 40px 0;
        }

        .explanation-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow-soft);
            height: 100%;
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }

        .explanation-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .explanation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .explanation-header h4 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
        }

        .explanation-badge {
            background: var(--gradient-primary);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .explanation-badge-warning {
            background: var(--gradient-warning);
        }

        .explanation-badge-info {
            background: var(--gradient-info);
        }

        .explanation-badge-success {
            background: var(--gradient-success);
        }

        .explanation-content {
            color: #666;
            line-height: 1.6;
        }

        .explanation-list {
            margin: 15px 0;
            padding-left: 20px;
        }

        .explanation-list li {
            margin-bottom: 8px;
        }

        .explanation-tip {
            background: #f0f7ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            font-size: 0.9rem;
            color: var(--primary-color);
        }

        /* Exemples de badges */
        .badge-examples {
            margin: 15px 0;
        }

        .badge-example {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .badge-example span:last-child {
            font-size: 0.9rem;
            color: #666;
        }

        /* Showcase d'appareils */
        .device-showcase {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }

        .device-item {
            text-align: center;
        }

        .device-icon {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .device-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        /* Boutons d'action demo */
        .action-buttons-demo {
            display: flex;
            gap: 10px;
            margin: 15px 0;
            flex-wrap: wrap;
        }

        .demo-action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .demo-action-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .demo-action-success {
            background: var(--gradient-success);
            color: white;
        }

        .demo-action-info {
            background: var(--gradient-info);
            color: white;
        }

        .demo-action-btn:hover {
            transform: scale(1.05);
        }

        /* Quiz interactif */
        .interactive-quiz {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 30px;
            border: 2px solid #e9ecef;
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .quiz-header h4 {
            color: var(--dark-color);
            margin-bottom: 10px;
        }

        .quiz-question-text {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: var(--dark-color);
        }

        .quiz-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .quiz-option {
            background: white;
            border: 2px solid #e0e0e0;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: left;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
        }

        .quiz-option:hover {
            border-color: var(--primary-color);
            background: rgba(25, 118, 210, 0.05);
        }

        .quiz-option.correct {
            border-color: var(--success-color);
            background: rgba(67, 160, 71, 0.1);
            color: var(--success-color);
        }

        .quiz-option.incorrect {
            border-color: var(--danger-color);
            background: rgba(229, 57, 53, 0.1);
            color: var(--danger-color);
        }

        .quiz-feedback {
            margin-top: 15px;
            padding: 15px;
            border-radius: 8px;
            font-weight: 500;
        }

        .quiz-correct {
            background: rgba(67, 160, 71, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(67, 160, 71, 0.3);
        }

        .quiz-incorrect {
            background: rgba(229, 57, 53, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(229, 57, 53, 0.3);
        }

        /* Démonstration de fonctionnalités */
        .feature-demonstration {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            margin: 30px 0;
        }

        .demo-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .demo-tab {
            flex: 1;
            padding: 15px 20px;
            background: none;
            border: none;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 3px solid transparent;
        }

        .demo-tab.active {
            color: var(--primary-color);
            background: white;
            border-bottom-color: var(--primary-color);
        }

        .demo-tab:hover:not(.active) {
            background: #e9ecef;
        }

        .demo-content {
            display: none;
            padding: 30px;
        }

        .demo-content.active {
            display: block;
            animation: fadeInUp 0.3s ease;
        }

        /* Table simulée */
        .simulated-table {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .table-header {
            background: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
        }

        .table-title {
            font-weight: 600;
            color: var(--dark-color);
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .demo-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .demo-btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .demo-btn-success {
            background: var(--gradient-success);
            color: white;
        }

        .demo-btn:hover {
            transform: scale(1.05);
        }

        .table-filters {
            background: #fafafa;
            padding: 15px 20px;
            display: flex;
            gap: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .demo-search {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .demo-filter {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            background: white;
        }

        .table-content {
            background: white;
        }

        .table-row {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-header-row {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--dark-color);
        }

        .table-data-row {
            transition: var(--transition);
            cursor: pointer;
        }

        .table-data-row:hover {
            background: rgba(25, 118, 210, 0.05);
        }

        .table-cell {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .source-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .source-sap {
            background: var(--success-color);
            color: white;
        }

        .source-rgm {
            background: var(--info-color);
            color: white;
        }

        .source-template {
            background: var(--warning-color);
            color: white;
        }

        /* Equipment Demo Sections */
        .explanation-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .explanation-card h5 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .action-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .action-item {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 15px;
            color: white;
            transition: transform 0.3s ease;
        }

        .action-item:hover {
            transform: translateY(-5px);
        }

        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .import-export-demo {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
        }

        .drag-drop-zone {
            border: 3px dashed var(--border-color);
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .drag-drop-zone:hover {
            border-color: var(--primary-color);
            background: rgba(74, 144, 226, 0.1);
        }

        .upload-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.7;
        }

        .export-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .export-btn {
            padding: 1rem;
            border: 2px solid var(--primary-color);
            background: transparent;
            color: var(--primary-color);
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .export-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Articles Section */
        .classification-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            border: 1px solid var(--border-color);
        }

        .metier-examples {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .metier-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(74, 144, 226, 0.1);
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .metier-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--primary-color);
            background: white;
            padding: 0.5rem;
            border-radius: 6px;
            min-width: 60px;
            text-align: center;
        }

        .metier-name {
            font-weight: 600;
            flex: 1;
        }

        .articles-demo {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
        }

        .articles-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .article-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .article-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            min-width: 120px;
            text-align: center;
        }

        .article-designation {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .article-details {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .metier-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            color: white;
        }

        .metier-1 {
            background: #e74c3c;
        }

        .metier-2 {
            background: #f39c12;
        }

        .fabricant {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Nomenclatures Section */
        .nomenclature-concept {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 3rem;
            margin: 2rem 0;
        }

        .concept-visual {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .equipment-box,
        .article-box {
            background: white;
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            min-width: 200px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .equipment-icon,
        .article-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .relation-arrow {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .arrow {
            font-size: 2rem;
        }

        .relation-info {
            text-align: center;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .sources-hierarchy {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
        }

        .sources-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .source-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            border-left: 5px solid;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .priority-1 {
            border-left-color: #27ae60;
        }

        .priority-2 {
            border-left-color: #f39c12;
        }

        .priority-3 {
            border-left-color: #3498db;
        }

        .priority-4 {
            border-left-color: #95a5a6;
        }

        .source-rank {
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .source-info {
            flex: 1;
        }

        .source-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            color: white;
        }

        .source-sap {
            background: #27ae60;
        }

        .source-rgm {
            background: #f39c12;
        }

        .source-template {
            background: #3498db;
        }

        .source-manual {
            background: #95a5a6;
        }

        /* Import/Export Section */
        .import-export-showcase {
            margin: 2rem 0;
        }

        .feature-demo-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            border: 1px solid var(--border-color);
        }

        .import-features,
        .export-formats {
            margin-top: 1.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: rgba(74, 144, 226, 0.1);
            border-radius: 8px;
        }

        .feature-icon {
            font-size: 1.5rem;
        }

        .format-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .format-icon {
            font-size: 2rem;
        }

        /* Analytics Section */
        .analytics-demo {
            margin: 2rem 0;
        }

        .metrics-overview {
            display: flex;
            gap: 2rem;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .metric-big {
            text-align: center;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            min-width: 200px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .metric-value {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .metric-trend {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .chart-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid var(--border-color);
            height: 100%;
        }

        .chart-placeholder {
            margin-top: 1rem;
            padding: 2rem;
            background: rgba(74, 144, 226, 0.1);
            border-radius: 8px;
        }

        .pie-chart-demo,
        .line-chart-demo,
        .bar-chart-demo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Doublons Section */
        .doublons-demo {
            margin: 2rem 0;
        }

        .conflict-example {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
        }

        .conflict-cards {
            display: flex;
            gap: 2rem;
            align-items: center;
            justify-content: center;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .conflict-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 2px solid;
            min-width: 280px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .conflict-card.source-sap {
            border-color: #27ae60;
        }

        .conflict-card.source-rgm {
            border-color: #f39c12;
        }

        .conflict-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .conflict-content {
            margin-bottom: 1.5rem;
        }

        .conflict-content>div {
            margin-bottom: 0.5rem;
        }

        .vs-indicator {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            background: white;
            border: 3px solid var(--primary-color);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .resolution-rules {
            background: rgba(74, 144, 226, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        /* Administration Section */
        .admin-demo {
            margin: 2rem 0;
        }

        .admin-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            border: 1px solid var(--border-color);
        }

        .users-list {
            margin-top: 1.5rem;
        }

        .user-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
        }

        .user-avatar {
            font-size: 2rem;
            background: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-info {
            flex: 1;
        }

        .user-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .user-status.active {
            background: #d4edda;
            color: #155724;
        }

        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .group-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        /* Completion Section */
        .completion-summary {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .summary-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 3rem 0;
            flex-wrap: wrap;
        }

        .stat-item {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            min-width: 150px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .skills-acquired {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin: 3rem 0;
        }

        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .skill-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
            font-weight: 500;
        }

        .next-steps {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin: 3rem 0;
        }

        .steps-list {
            margin-top: 1.5rem;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: white;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
        }

        .step-number {
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .step-text {
            flex: 1;
            font-weight: 500;
        }

        .certificate {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 3px solid var(--primary-color);
            border-radius: 20px;
            padding: 3rem;
            margin: 3rem 0;
            position: relative;
            overflow: hidden;
        }

        .certificate::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(74, 144, 226, 0.1), transparent);
            animation: shine 3s infinite;
        }

        .certificate-content {
            position: relative;
            z-index: 1;
        }

        .certificate-date {
            margin: 1rem 0;
            color: var(--text-secondary);
            font-style: italic;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        /* Animations supplémentaires */
        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.5);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 5px var(--primary-color);
            }

            50% {
                box-shadow: 0 0 20px var(--primary-color), 0 0 30px var(--primary-color);
            }
        }

        /* Responsive amélioré */
        @media (max-width: 768px) {
            .metrics-overview {
                flex-direction: column;
                align-items: center;
            }

            .concept-visual {
                flex-direction: column;
            }

            .conflict-cards {
                flex-direction: column;
            }

            .vs-indicator {
                transform: rotate(90deg);
            }

            .summary-stats {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <!-- Header Héro -->
    <div class="hero-header">
        <div class="hero-content">
            <div class="container">
                <h1 class="hero-title animate__animated animate__fadeInDown">
                    🎓 Didacticiel Interactif
                </h1>
                <p class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                    Maîtrisez EquiNomTech en quelques minutes avec notre guide pas-à-pas
                </p>
                <div class="hero-stats animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="hero-stat">
                        <span class="hero-stat-number">10</span>
                        <span class="hero-stat-label">Étapes Simples</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">15</span>
                        <span class="hero-stat-label">Minutes</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">100%</span>
                        <span class="hero-stat-label">Interactif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Didacticiel -->
    <div class="tutorial-nav">
        <div class="container">
            <div class="nav-progress">
                <div class="nav-progress-bar" id="progressBar"></div>
            </div>
            <div class="nav-steps" id="navSteps">
                <!-- Les étapes seront générées dynamiquement -->
            </div>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="main-content">
        <!-- Section d'accueil -->
        <div class="content-section active" id="section-0">
            <div class="section-header">
                <h2 class="section-title">🚀 Bienvenue dans EquiNomTech</h2>
                <p class="section-subtitle">
                    Découvrez la puissance de votre système de gestion de nomenclature d'équipements
                </p>
            </div>

            <div class="feature-cards">
                <div class="feature-card tooltip-custom" data-tooltip="Interface moderne et intuitive">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">🎨</div>
                    <h3 class="feature-card-title">Interface Moderne</h3>
                    <p class="feature-card-description">
                        Une interface élégante et intuitive conçue pour optimiser votre productivité
                    </p>
                    <button class="feature-card-action">Explorer</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Gestion complète des équipements">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">🔧</div>
                    <h3 class="feature-card-title">Gestion d'Équipements</h3>
                    <p class="feature-card-description">
                        Gérez vos équipements industriels avec une précision et une efficacité inégalées
                    </p>
                    <button class="feature-card-action">Découvrir</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Classification intelligente des articles">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">📦</div>
                    <h3 class="feature-card-title">Articles Intelligents</h3>
                    <p class="feature-card-description">
                        Classification automatique par métier et gestion avancée des pièces de rechange
                    </p>
                    <button class="feature-card-action">Apprendre</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Imports et exports simplifiés">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">📊</div>
                    <h3 class="feature-card-title">Import/Export Avancé</h3>
                    <p class="feature-card-description">
                        Outils puissants d'import Excel et d'export personnalisé pour tous vos besoins
                    </p>
                    <button class="feature-card-action">Tester</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Analyses et rapports en temps réel">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">📈</div>
                    <h3 class="feature-card-title">Analytics Temps Réel</h3>
                    <p class="feature-card-description">
                        Tableaux de bord dynamiques et rapports intelligents pour vos prises de décision
                    </p>
                    <button class="feature-card-action">Analyser</button>
                </div>

                <div class="feature-card tooltip-custom" data-tooltip="Détection automatique des doublons">
                    <div class="status-indicator"></div>
                    <div class="feature-card-icon">🔍</div>
                    <h3 class="feature-card-title">Détection Doublons</h3>
                    <p class="feature-card-description">
                        Algorithmes intelligents pour détecter et résoudre automatiquement les conflits
                    </p>
                    <button class="feature-card-action">Optimiser</button>
                </div>
            </div>
        </div>

        <!-- Section Navigation et Interface -->
        <div class="content-section" id="section-1">
            <div class="section-header">
                <h2 class="section-title">🧭 Navigation et Interface</h2>
                <p class="section-subtitle">
                    Découvrez l'interface moderne et intuitive d'EquiNomTech
                </p>
            </div>

            <!-- Interface Demo -->
            <div class="interface-demo mb-5">
                <div class="mockup-browser">
                    <div class="mockup-header">
                        <div class="mockup-buttons">
                            <span class="btn-close"></span>
                            <span class="btn-minimize"></span>
                            <span class="btn-maximize"></span>
                        </div>
                        <div class="mockup-url">https://equipnomtech.local/dashboard</div>
                    </div>
                    <div class="mockup-content">
                        <!-- Simulation du menu principal -->
                        <div class="demo-sidebar">
                            <div class="demo-logo">
                                <strong>EquiNomTech</strong>
                            </div>
                            <div class="demo-menu">
                                <div class="demo-menu-item active" data-tooltip="Vue d'ensemble et statistiques">
                                    <span class="demo-icon">🏠</span>
                                    Dashboard
                                    <span class="demo-badge demo-badge-success">Actif</span>
                                </div>
                                <div class="demo-menu-item clickable" data-tooltip="Gestion des équipements industriels">
                                    <span class="demo-icon">🔧</span>
                                    Équipements
                                    <span class="demo-badge demo-badge-info">2,450</span>
                                </div>
                                <div class="demo-menu-item clickable" data-tooltip="Gestion des pièces de rechange">
                                    <span class="demo-icon">📦</span>
                                    Articles
                                    <span class="demo-badge demo-badge-primary">8,750</span>
                                </div>
                                <div class="demo-menu-item clickable" data-tooltip="Relations équipements-articles">
                                    <span class="demo-icon">📋</span>
                                    Nomenclatures
                                    <span class="demo-badge demo-badge-secondary">15,200</span>
                                </div>
                                <div class="demo-menu-item clickable" data-tooltip="Gestion des doublons">
                                    <span class="demo-icon">⚠️</span>
                                    Doublons
                                    <span class="demo-badge demo-badge-danger">12</span>
                                </div>
                                <div class="demo-menu-item clickable" data-tooltip="Éléments non codifiés SAP">
                                    <span class="demo-icon">🔴</span>
                                    Éléments Non SAP
                                    <span class="demo-badge demo-badge-warning">45</span>
                                </div>
                                <div class="demo-menu-item clickable" data-tooltip="Validation imports en attente">
                                    <span class="demo-icon">✅</span>
                                    Validation Import
                                    <span class="demo-badge demo-badge-warning">8</span>
                                </div>
                            </div>
                        </div>

                        <!-- Zone de contenu principale -->
                        <div class="demo-main-content">
                            <div class="demo-header">
                                <h3>Dashboard Principal</h3>
                                <div class="demo-user-info">
                                    <span class="demo-user-name">👤 Utilisateur</span>
                                    <button class="demo-logout-btn">🚪 Déconnexion</button>
                                </div>
                            </div>

                            <!-- Métriques clés -->
                            <div class="demo-metrics">
                                <div class="demo-metric-card">
                                    <div class="demo-metric-icon">📊</div>
                                    <div class="demo-metric-value">2,450</div>
                                    <div class="demo-metric-label">Équipements</div>
                                    <div class="demo-metric-change">+12 ce mois</div>
                                </div>
                                <div class="demo-metric-card">
                                    <div class="demo-metric-icon">📦</div>
                                    <div class="demo-metric-value">8,750</div>
                                    <div class="demo-metric-label">Articles</div>
                                    <div class="demo-metric-change">+45 ce mois</div>
                                </div>
                                <div class="demo-metric-card">
                                    <div class="demo-metric-icon">📋</div>
                                    <div class="demo-metric-value">15,200</div>
                                    <div class="demo-metric-label">Nomenclatures</div>
                                    <div class="demo-metric-change">+89 ce mois</div>
                                </div>
                                <div class="demo-metric-card">
                                    <div class="demo-metric-icon">✅</div>
                                    <div class="demo-metric-value">94.2%</div>
                                    <div class="demo-metric-label">Taux Couverture</div>
                                    <div class="demo-metric-change">+2.1% ce mois</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Explications interactives -->
            <div class="explanation-cards">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="explanation-card">
                            <div class="explanation-header">
                                <h4>🎯 Menu Principal</h4>
                                <span class="explanation-badge">Essentiel</span>
                            </div>
                            <div class="explanation-content">
                                <p>Le menu de gauche vous donne accès à tous les modules :</p>
                                <ul class="explanation-list">
                                    <li><strong>Dashboard</strong> : Vue d'ensemble et statistiques</li>
                                    <li><strong>Équipements</strong> : Gestion des équipements industriels</li>
                                    <li><strong>Articles</strong> : Gestion des pièces de rechange</li>
                                    <li><strong>Nomenclatures</strong> : Relations équipements-articles</li>
                                </ul>
                                <div class="explanation-tip">
                                    💡 <strong>Astuce :</strong> Les badges colorés indiquent le nombre d'éléments ou l'état
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="explanation-card">
                            <div class="explanation-header">
                                <h4>📊 Système d'Alertes</h4>
                                <span class="explanation-badge explanation-badge-warning">Important</span>
                            </div>
                            <div class="explanation-content">
                                <p>Les badges colorés vous informent en temps réel :</p>
                                <div class="badge-examples">
                                    <div class="badge-example">
                                        <span class="demo-badge demo-badge-danger">12</span>
                                        <span>Éléments critiques nécessitant une attention immédiate</span>
                                    </div>
                                    <div class="badge-example">
                                        <span class="demo-badge demo-badge-warning">45</span>
                                        <span>Éléments en attente de traitement</span>
                                    </div>
                                    <div class="badge-example">
                                        <span class="demo-badge demo-badge-success">✓</span>
                                        <span>Statut normal, tout fonctionne</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="explanation-card">
                            <div class="explanation-header">
                                <h4>📱 Interface Responsive</h4>
                                <span class="explanation-badge explanation-badge-info">Adaptable</span>
                            </div>
                            <div class="explanation-content">
                                <p>L'interface s'adapte à tous vos appareils :</p>
                                <div class="device-showcase">
                                    <div class="device-item">
                                        <div class="device-icon">💻</div>
                                        <div class="device-label">Desktop</div>
                                    </div>
                                    <div class="device-item">
                                        <div class="device-icon">📱</div>
                                        <div class="device-label">Mobile</div>
                                    </div>
                                    <div class="device-item">
                                        <div class="device-icon">📟</div>
                                        <div class="device-label">Tablette</div>
                                    </div>
                                </div>
                                <div class="explanation-tip">
                                    📱 Menu hamburger sur mobile pour navigation optimisée
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="explanation-card">
                            <div class="explanation-header">
                                <h4>⚡ Actions Rapides</h4>
                                <span class="explanation-badge explanation-badge-success">Efficace</span>
                            </div>
                            <div class="explanation-content">
                                <p>Boutons d'action en haut à droite de chaque page :</p>
                                <div class="action-buttons-demo">
                                    <button class="demo-action-btn demo-action-primary">
                                        <span class="material-icons">add</span> Ajouter
                                    </button>
                                    <button class="demo-action-btn demo-action-success">
                                        <span class="material-icons">file_download</span> Export
                                    </button>
                                    <button class="demo-action-btn demo-action-info">
                                        <span class="material-icons">upload_file</span> Import
                                    </button>
                                </div>
                                <div class="explanation-tip">
                                    ⚡ Raccourcis clavier disponibles pour les actions fréquentes
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz interactif -->
            <div class="interactive-quiz mt-5">
                <div class="quiz-header">
                    <h4>🧠 Mini-Quiz : Testez vos connaissances</h4>
                    <p>Cliquez sur la bonne réponse !</p>
                </div>

                <div class="quiz-question">
                    <p class="quiz-question-text">
                        <strong>Question :</strong> Que signifie un badge rouge (🔴) dans le menu ?
                    </p>
                    <div class="quiz-options">
                        <button class="quiz-option" data-correct="false">
                            Tout fonctionne normalement
                        </button>
                        <button class="quiz-option" data-correct="true">
                            Éléments nécessitant une attention immédiate
                        </button>
                        <button class="quiz-option" data-correct="false">
                            Nouveaux éléments ajoutés
                        </button>
                    </div>
                    <div class="quiz-feedback" style="display: none;">
                        <div class="quiz-correct" style="display: none;">
                            ✅ <strong>Correct !</strong> Les badges rouges signalent les éléments critiques.
                        </div>
                        <div class="quiz-incorrect" style="display: none;">
                            ❌ <strong>Incorrect.</strong> Les badges rouges indiquent des éléments nécessitant une attention immédiate.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Gestion des Équipements -->
        <div class="content-section" id="section-2">
            <div class="section-header">
                <h2 class="section-title">🔧 Gestion des Équipements</h2>
                <p class="section-subtitle">
                    Maîtrisez la gestion complète de vos équipements industriels
                </p>
            </div>

            <!-- Démonstration interactive -->
            <div class="feature-demonstration">
                <div class="demo-tabs">
                    <button class="demo-tab active" data-tab="overview">Vue d'ensemble</button>
                    <button class="demo-tab" data-tab="search">Recherche & Filtres</button>
                    <button class="demo-tab" data-tab="actions">Actions</button>
                    <button class="demo-tab" data-tab="import">Import/Export</button>
                </div>

                <!-- Vue d'ensemble -->
                <div class="demo-content active" id="demo-overview">
                    <div class="simulated-table">
                        <div class="table-header">
                            <div class="table-title">📋 Liste des Équipements</div>
                            <div class="table-actions">
                                <button class="demo-btn demo-btn-primary">+ Ajouter</button>
                                <button class="demo-btn demo-btn-success">📤 Export</button>
                            </div>
                        </div>
                        <div class="table-filters">
                            <input type="text" placeholder="Rechercher..." class="demo-search">
                            <select class="demo-filter">
                                <option>Tous les fabricants</option>
                                <option>Siemens</option>
                                <option>ABB</option>
                                <option>Schneider</option>
                            </select>
                        </div>
                        <div class="table-content">
                            <div class="table-row table-header-row">
                                <div class="table-cell">Repère</div>
                                <div class="table-cell">Désignation</div>
                                <div class="table-cell">Fabricant</div>
                                <div class="table-cell">Type</div>
                                <div class="table-cell">Source</div>
                            </div>
                            <div class="table-row table-data-row clickable">
                                <div class="table-cell"><strong>MP1080A</strong></div>
                                <div class="table-cell">Pompe centrifuge 50m³/h</div>
                                <div class="table-cell">Grundfos</div>
                                <div class="table-cell">Pompe</div>
                                <div class="table-cell"><span class="source-badge source-sap">SAP</span></div>
                            </div>
                            <div class="table-row table-data-row clickable">
                                <div class="table-cell"><strong>EL2040B</strong></div>
                                <div class="table-cell">Moteur électrique 15kW</div>
                                <div class="table-cell">Siemens</div>
                                <div class="table-cell">Moteur</div>
                                <div class="table-cell"><span class="source-badge source-rgm">RGM</span></div>
                            </div>
                            <div class="table-row table-data-row clickable">
                                <div class="table-cell"><strong>VN3025C</strong></div>
                                <div class="table-cell">Vanne papillon DN150</div>
                                <div class="table-cell">Kitz</div>
                                <div class="table-cell">Vanne</div>
                                <div class="table-cell"><span class="source-badge source-template">Template</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recherche & Filtres -->
                <div class="demo-content" id="demo-search">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="explanation-card">
                                <h5>🔍 Recherche Globale</h5>
                                <p>Recherche en temps réel sur :</p>
                                <ul>
                                    <li>Repère équipement</li>
                                    <li>Désignation</li>
                                    <li>Fabricant</li>
                                </ul>
                                <input type="text" class="form-control mt-3" placeholder="Tapez 'pompe' pour tester...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="explanation-card">
                                <h5>🎯 Filtres Avancés</h5>
                                <p>Filtrage par critères :</p>
                                <div class="mb-2">
                                    <select class="form-select form-select-sm">
                                        <option>Tous fabricants</option>
                                        <option>Grundfos</option>
                                        <option>Siemens</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <select class="form-select form-select-sm">
                                        <option>Tous types</option>
                                        <option>Pompe</option>
                                        <option>Moteur</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="demo-content" id="demo-actions">
                    <div class="action-showcase">
                        <div class="action-item">
                            <div class="action-icon">➕</div>
                            <h5>Ajouter Équipement</h5>
                            <p>Création rapide avec formulaire intelligent</p>
                            <button class="btn btn-primary btn-sm">Tester</button>
                        </div>
                        <div class="action-item">
                            <div class="action-icon">✏️</div>
                            <h5>Modifier</h5>
                            <p>Édition en place ou formulaire détaillé</p>
                            <button class="btn btn-warning btn-sm">Essayer</button>
                        </div>
                        <div class="action-item">
                            <div class="action-icon">🗑️</div>
                            <h5>Supprimer</h5>
                            <p>Suppression sécurisée avec confirmation</p>
                            <button class="btn btn-danger btn-sm">Simuler</button>
                        </div>
                    </div>
                </div>

                <!-- Import/Export -->
                <div class="demo-content" id="demo-import">
                    <div class="import-export-demo">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="demo-card">
                                    <h5>📥 Import Excel</h5>
                                    <div class="drag-drop-zone">
                                        <div class="upload-icon">📁</div>
                                        <p>Glissez votre fichier Excel ici</p>
                                        <small>Format accepté: .xlsx</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="demo-card">
                                    <h5>📤 Export Options</h5>
                                    <div class="export-options">
                                        <button class="export-btn">Excel Complet</button>
                                        <button class="export-btn">PDF Rapport</button>
                                        <button class="export-btn">CSV Données</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Articles -->
        <div class="content-section" id="section-3">
            <div class="section-header">
                <h2 class="section-title">📦 Gestion des Articles</h2>
                <p class="section-subtitle">
                    Classification automatique et gestion intelligente des pièces de rechange
                </p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="classification-card">
                        <h5>🔧 Classification par Métier</h5>
                        <div class="metier-examples">
                            <div class="metier-item">
                                <span class="metier-code">1xxx</span>
                                <span class="metier-name">Mécanique</span>
                                <small>Roulements, joints, visserie</small>
                            </div>
                            <div class="metier-item">
                                <span class="metier-code">2xxx</span>
                                <span class="metier-name">Électrique</span>
                                <small>Moteurs, contacteurs, câbles</small>
                            </div>
                            <div class="metier-item">
                                <span class="metier-code">3xxx</span>
                                <span class="metier-name">Instrumentation</span>
                                <small>Capteurs, transmetteurs</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="articles-demo">
                        <h5>📋 Exemple d'Articles</h5>
                        <div class="articles-list">
                            <div class="article-item">
                                <div class="article-code">123456789</div>
                                <div class="article-info">
                                    <div class="article-designation">Roulement à billes SKF 6308</div>
                                    <div class="article-details">
                                        <span class="metier-badge metier-1">Mécanique</span>
                                        <span class="fabricant">SKF</span>
                                    </div>
                                </div>
                            </div>
                            <div class="article-item">
                                <div class="article-code">287654321</div>
                                <div class="article-info">
                                    <div class="article-designation">Contacteur tripolaire 25A</div>
                                    <div class="article-details">
                                        <span class="metier-badge metier-2">Électrique</span>
                                        <span class="fabricant">Schneider</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="interactive-quiz">
                <div class="quiz-header">
                    <h4>🧠 Quiz Articles</h4>
                    <p>Un article avec le code 345612789 sera classé dans quel métier ?</p>
                </div>
                <div class="quiz-question">
                    <div class="quiz-options">
                        <button class="quiz-option" data-correct="false">Mécanique (1xxx)</button>
                        <button class="quiz-option" data-correct="false">Électrique (2xxx)</button>
                        <button class="quiz-option" data-correct="true">Instrumentation (3xxx)</button>
                        <button class="quiz-option" data-correct="false">Tuyauterie (4xxx)</button>
                    </div>
                    <div class="quiz-feedback" style="display: none;">
                        <div class="quiz-correct" style="display: none;">
                            ✅ <strong>Parfait !</strong> Le code commence par 3, donc c'est bien Instrumentation !
                        </div>
                        <div class="quiz-incorrect" style="display: none;">
                            ❌ <strong>Pas tout à fait.</strong> Regardez le premier chiffre : 3xxx = Instrumentation
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Nomenclatures -->
        <div class="content-section" id="section-4">
            <div class="section-header">
                <h2 class="section-title">📋 Gestion des Nomenclatures</h2>
                <p class="section-subtitle">
                    Relations équipements-articles et hiérarchie des sources
                </p>
            </div>

            <div class="nomenclature-concept mb-5">
                <div class="concept-visual">
                    <div class="equipment-box">
                        <div class="equipment-icon">🔧</div>
                        <div class="equipment-code">MP1080A</div>
                        <div class="equipment-name">Pompe centrifuge</div>
                    </div>
                    <div class="relation-arrow">
                        <span class="arrow">↔️</span>
                        <div class="relation-info">
                            <strong>Nomenclature</strong><br>
                            <small>Quantité: 2</small>
                        </div>
                    </div>
                    <div class="article-box">
                        <div class="article-icon">📦</div>
                        <div class="article-code">123456789</div>
                        <div class="article-name">Roulement SKF</div>
                    </div>
                </div>
            </div>

            <div class="sources-hierarchy">
                <h5>🏆 Hiérarchie des Sources</h5>
                <div class="sources-list">
                    <div class="source-item priority-1">
                        <div class="source-rank">1</div>
                        <div class="source-info">
                            <strong>SAP</strong>
                            <small>Source de référence officielle</small>
                        </div>
                        <div class="source-badge source-sap">SAP</div>
                    </div>
                    <div class="source-item priority-2">
                        <div class="source-rank">2</div>
                        <div class="source-info">
                            <strong>RGM</strong>
                            <small>Source secondaire fiable</small>
                        </div>
                        <div class="source-badge source-rgm">RGM</div>
                    </div>
                    <div class="source-item priority-3">
                        <div class="source-rank">3</div>
                        <div class="source-info">
                            <strong>Template</strong>
                            <small>Import via Template SPL</small>
                        </div>
                        <div class="source-badge source-template">Template</div>
                    </div>
                    <div class="source-item priority-4">
                        <div class="source-rank">4</div>
                        <div class="source-info">
                            <strong>Manuel</strong>
                            <small>Saisie utilisateur</small>
                        </div>
                        <div class="source-badge source-manual">Manuel</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Import/Export -->
        <div class="content-section" id="section-5">
            <div class="section-header">
                <h2 class="section-title">📤 Import/Export Avancé</h2>
                <p class="section-subtitle">
                    Outils puissants pour la gestion de vos données
                </p>
            </div>

            <div class="import-export-showcase">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="feature-demo-card">
                            <h5>📥 Import Intelligent</h5>
                            <div class="import-features">
                                <div class="feature-item">
                                    <span class="feature-icon">🎯</span>
                                    <span>Drag & Drop moderne</span>
                                </div>
                                <div class="feature-item">
                                    <span class="feature-icon">⚡</span>
                                    <span>Validation en temps réel</span>
                                </div>
                                <div class="feature-item">
                                    <span class="feature-icon">🔧</span>
                                    <span>Détection automatique des colonnes</span>
                                </div>
                                <div class="feature-item">
                                    <span class="feature-icon">📊</span>
                                    <span>Barre de progression détaillée</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-demo-card">
                            <h5>📤 Export Professionnel</h5>
                            <div class="export-formats">
                                <div class="format-item">
                                    <span class="format-icon">📊</span>
                                    <div class="format-info">
                                        <strong>Excel (.xlsx)</strong>
                                        <small>Avec graphiques et mise en forme</small>
                                    </div>
                                </div>
                                <div class="format-item">
                                    <span class="format-icon">📄</span>
                                    <div class="format-info">
                                        <strong>PDF Rapport</strong>
                                        <small>Mise en page professionnelle</small>
                                    </div>
                                </div>
                                <div class="format-item">
                                    <span class="format-icon">💾</span>
                                    <div class="format-info">
                                        <strong>CSV Données</strong>
                                        <small>Pour intégration tiers</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Analytics -->
        <div class="content-section" id="section-6">
            <div class="section-header">
                <h2 class="section-title">📊 Analytics & Rapports</h2>
                <p class="section-subtitle">
                    Tableaux de bord intelligents et analyses en temps réel
                </p>
            </div>

            <div class="analytics-demo">
                <div class="metrics-overview">
                    <div class="metric-big">
                        <div class="metric-value" data-value="94.2">0%</div>
                        <div class="metric-label">Taux de Couverture</div>
                        <div class="metric-trend">+2.1% ce mois</div>
                    </div>
                    <div class="metric-big">
                        <div class="metric-value" data-value="87.5">0%</div>
                        <div class="metric-label">Codification SAP</div>
                        <div class="metric-trend">+5.3% ce mois</div>
                    </div>
                </div>

                <div class="charts-demo mt-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="chart-card">
                                <h6>Répartition par Métier</h6>
                                <div class="chart-placeholder">
                                    <div class="pie-chart-demo">🥧</div>
                                    <small>Graphique circulaire interactif</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chart-card">
                                <h6>Évolution Temporelle</h6>
                                <div class="chart-placeholder">
                                    <div class="line-chart-demo">📈</div>
                                    <small>Tendances sur 7 mois</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chart-card">
                                <h6>Top Fabricants</h6>
                                <div class="chart-placeholder">
                                    <div class="bar-chart-demo">📊</div>
                                    <small>Classement des fournisseurs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Doublons -->
        <div class="content-section" id="section-7">
            <div class="section-header">
                <h2 class="section-title">⚠️ Gestion des Doublons</h2>
                <p class="section-subtitle">
                    Détection intelligente et résolution automatique des conflits
                </p>
            </div>

            <div class="doublons-demo">
                <div class="conflict-example">
                    <h5>🔍 Exemple de Conflit Détecté</h5>
                    <div class="conflict-cards">
                        <div class="conflict-card source-sap">
                            <div class="conflict-header">
                                <strong>Version SAP</strong>
                                <span class="source-badge source-sap">SAP</span>
                            </div>
                            <div class="conflict-content">
                                <div><strong>Équipement:</strong> MP1080A</div>
                                <div><strong>Article:</strong> 123456789</div>
                                <div><strong>Quantité:</strong> 2</div>
                                <div><strong>Date:</strong> 15/07/2025</div>
                            </div>
                            <button class="btn btn-success btn-sm">✅ Conserver</button>
                        </div>
                        <div class="vs-indicator">VS</div>
                        <div class="conflict-card source-rgm">
                            <div class="conflict-header">
                                <strong>Version RGM</strong>
                                <span class="source-badge source-rgm">RGM</span>
                            </div>
                            <div class="conflict-content">
                                <div><strong>Équipement:</strong> MP1080A</div>
                                <div><strong>Article:</strong> 123456789</div>
                                <div><strong>Quantité:</strong> 3</div>
                                <div><strong>Date:</strong> 10/07/2025</div>
                            </div>
                            <button class="btn btn-outline-danger btn-sm">❌ Supprimer</button>
                        </div>
                    </div>
                    <div class="resolution-rules">
                        <h6>📋 Règles de Résolution Automatique</h6>
                        <ul>
                            <li><strong>SAP</strong> prime sur les autres sources</li>
                            <li><strong>Quantité la plus récente</strong> en cas de conflit</li>
                            <li><strong>Données les plus complètes</strong> privilégiées</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Administration -->
        <div class="content-section" id="section-8">
            <div class="section-header">
                <h2 class="section-title">👥 Administration</h2>
                <p class="section-subtitle">
                    Gestion des utilisateurs, groupes et configuration système
                </p>
            </div>

            <div class="admin-demo">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="admin-card">
                            <h5>👤 Gestion Utilisateurs</h5>
                            <div class="users-list">
                                <div class="user-item">
                                    <div class="user-avatar">👨‍💼</div>
                                    <div class="user-info">
                                        <strong>Admin Principal</strong>
                                        <small>Administrateur</small>
                                    </div>
                                    <span class="user-status active">Actif</span>
                                </div>
                                <div class="user-item">
                                    <div class="user-avatar">👩‍🔧</div>
                                    <div class="user-info">
                                        <strong>Marie Dupont</strong>
                                        <small>Gestionnaire</small>
                                    </div>
                                    <span class="user-status active">Actif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-card">
                            <h5>🔐 Groupes & Permissions</h5>
                            <div class="groups-grid">
                                <div class="group-item">
                                    <strong>Administrateur</strong>
                                    <small>Tous droits</small>
                                </div>
                                <div class="group-item">
                                    <strong>Gestionnaire</strong>
                                    <small>Lecture/Écriture</small>
                                </div>
                                <div class="group-item">
                                    <strong>Utilisateur</strong>
                                    <small>Lecture + Export</small>
                                </div>
                                <div class="group-item">
                                    <strong>Consultant</strong>
                                    <small>Lecture seule</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Finalisation -->
        <div class="content-section" id="section-9">
            <div class="section-header">
                <h2 class="section-title">✅ Félicitations !</h2>
                <p class="section-subtitle">
                    Vous maîtrisez maintenant EquiNomTech ! 🎉
                </p>
            </div>

            <div class="completion-summary">
                <div class="summary-stats">
                    <div class="stat-item">
                        <div class="stat-icon">📚</div>
                        <div class="stat-value">10</div>
                        <div class="stat-label">Sections Complétées</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">🧠</div>
                        <div class="stat-value">3</div>
                        <div class="stat-label">Quiz Réussis</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">⏱️</div>
                        <div class="stat-value">15</div>
                        <div class="stat-label">Minutes Investies</div>
                    </div>
                </div>

                <div class="skills-acquired">
                    <h5>🎯 Compétences Acquises</h5>
                    <div class="skills-grid">
                        <div class="skill-item">✅ Navigation dans l'interface</div>
                        <div class="skill-item">✅ Gestion des équipements</div>
                        <div class="skill-item">✅ Classification des articles</div>
                        <div class="skill-item">✅ Gestion des nomenclatures</div>
                        <div class="skill-item">✅ Import/Export de données</div>
                        <div class="skill-item">✅ Analyse et rapports</div>
                        <div class="skill-item">✅ Résolution des doublons</div>
                        <div class="skill-item">✅ Administration système</div>
                    </div>
                </div>

                <div class="next-steps">
                    <h5>🚀 Prochaines Étapes</h5>
                    <div class="steps-list">
                        <div class="step-item">
                            <span class="step-number">1</span>
                            <span class="step-text">Accédez au dashboard principal</span>
                            <a href="dashboard.php" class="btn btn-primary btn-sm">Aller au Dashboard</a>
                        </div>
                        <div class="step-item">
                            <span class="step-number">2</span>
                            <span class="step-text">Consultez la documentation complète</span>
                            <a href="GUIDE_UTILISATEUR.md" class="btn btn-info btn-sm">Guide Complet</a>
                        </div>
                        <div class="step-item">
                            <span class="step-number">3</span>
                            <span class="step-text">Contactez le support si besoin</span>
                            <button class="btn btn-warning btn-sm">Support</button>
                        </div>
                    </div>
                </div>

                <div class="certificate">
                    <div class="certificate-content">
                        <h4>🏆 Certificat de Réussite</h4>
                        <p>Vous avez terminé avec succès le didacticiel EquiNomTech</p>
                        <div class="certificate-date">Complété le 26 juillet 2025</div>
                        <button class="btn btn-success" onclick="window.print()">📄 Imprimer le Certificat</button>
                    </div>
                </div>
            </div>
            ">
        </div>

        <!-- Contrôles Navigation -->
        <div class="nav-controls">
            <button class="nav-btn secondary" id="prevBtn" disabled>
                <span class="material-icons">arrow_back</span>
                Précédent
            </button>

            <div class="d-flex align-items-center gap-3">
                <span id="stepIndicator" class="text-muted">Étape 1 sur 10</span>
                <div class="tooltip-custom" data-tooltip="Votre progression dans le didacticiel">
                    <span class="material-icons text-primary pulse-animation">help_outline</span>
                </div>
            </div>

            <button class="nav-btn" id="nextBtn">
                Suivant
                <span class="material-icons">arrow_forward</span>
            </button>
        </div>

        <!-- Scripts -->
        <script src="plugins/js/bootstrap.bundle.min.js"></script>
        <script>
            class TutorialManager {
                constructor() {
                    this.currentStep = 0;
                    this.totalSteps = 10;
                    this.steps = [{
                            icon: '🏠',
                            title: 'Accueil',
                            subtitle: 'Introduction'
                        },
                        {
                            icon: '🧭',
                            title: 'Navigation',
                            subtitle: 'Interface'
                        },
                        {
                            icon: '🔧',
                            title: 'Équipements',
                            subtitle: 'Gestion'
                        },
                        {
                            icon: '📦',
                            title: 'Articles',
                            subtitle: 'Classification'
                        },
                        {
                            icon: '📋',
                            title: 'Nomenclatures',
                            subtitle: 'Relations'
                        },
                        {
                            icon: '📤',
                            title: 'Import/Export',
                            subtitle: 'Données'
                        },
                        {
                            icon: '📊',
                            title: 'Analytics',
                            subtitle: 'Rapports'
                        },
                        {
                            icon: '⚠️',
                            title: 'Doublons',
                            subtitle: 'Résolution'
                        },
                        {
                            icon: '👥',
                            title: 'Administration',
                            subtitle: 'Gestion'
                        },
                        {
                            icon: '✅',
                            title: 'Finalisation',
                            subtitle: 'Récapitulatif'
                        }
                    ];
                    this.init();
                }

                init() {
                    this.createNavSteps();
                    this.updateUI();
                    this.bindEvents();
                    this.animateEntry();
                    this.initInteractiveElements();
                }

                initInteractiveElements() {
                    // Animation des éléments du menu demo
                    document.querySelectorAll('.demo-menu-item.clickable').forEach(item => {
                        item.addEventListener('click', () => {
                            // Retirer l'état actif des autres éléments
                            document.querySelectorAll('.demo-menu-item').forEach(el => el.classList.remove('active'));
                            // Ajouter l'état actif à l'élément cliqué
                            item.classList.add('active');

                            // Animation de feedback
                            item.style.transform = 'scale(0.98)';
                            setTimeout(() => {
                                item.style.transform = '';
                            }, 150);
                        });
                    });

                    // Quiz interactif
                    this.initQuiz();

                    // Tabs de démonstration
                    this.initDemoTabs();

                    // Animation des métriques
                    this.animateMetrics();
                }

                initQuiz() {
                    document.querySelectorAll('.quiz-option').forEach(option => {
                        option.addEventListener('click', (e) => {
                            const isCorrect = e.target.getAttribute('data-correct') === 'true';
                            const feedback = document.querySelector('.quiz-feedback');
                            const correctFeedback = document.querySelector('.quiz-correct');
                            const incorrectFeedback = document.querySelector('.quiz-incorrect');

                            // Désactiver toutes les options
                            document.querySelectorAll('.quiz-option').forEach(opt => {
                                opt.style.pointerEvents = 'none';
                                if (opt.getAttribute('data-correct') === 'true') {
                                    opt.classList.add('correct');
                                } else {
                                    opt.classList.add('incorrect');
                                }
                            });

                            // Afficher le feedback
                            feedback.style.display = 'block';
                            if (isCorrect) {
                                correctFeedback.style.display = 'block';
                                incorrectFeedback.style.display = 'none';
                            } else {
                                correctFeedback.style.display = 'none';
                                incorrectFeedback.style.display = 'block';
                            }

                            // Animation
                            feedback.classList.add('animate__animated', 'animate__fadeInUp');
                        });
                    });
                }

                initDemoTabs() {
                    document.querySelectorAll('.demo-tab').forEach(tab => {
                        tab.addEventListener('click', (e) => {
                            const tabName = e.target.getAttribute('data-tab');

                            // Mettre à jour les tabs
                            document.querySelectorAll('.demo-tab').forEach(t => t.classList.remove('active'));
                            e.target.classList.add('active');

                            // Mettre à jour le contenu
                            document.querySelectorAll('.demo-content').forEach(content => {
                                content.classList.remove('active');
                            });

                            const targetContent = document.getElementById(`demo-${tabName}`);
                            if (targetContent) {
                                targetContent.classList.add('active');
                            }
                        });
                    });
                }

                animateMetrics() {
                    // Animation des chiffres
                    const observerOptions = {
                        threshold: 0.5,
                        rootMargin: '0px 0px -50px 0px'
                    };

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const valueElement = entry.target.querySelector('.demo-metric-value');
                                if (valueElement && !valueElement.classList.contains('animated')) {
                                    this.animateValue(valueElement);
                                    valueElement.classList.add('animated');
                                }
                            }
                        });
                    }, observerOptions);

                    document.querySelectorAll('.demo-metric-card').forEach(card => {
                        observer.observe(card);
                    });
                }

                animateValue(element) {
                    const finalValue = element.textContent.trim();
                    const isPercentage = finalValue.includes('%');
                    const numericValue = parseFloat(finalValue.replace(/[^\d.]/g, ''));

                    let current = 0;
                    const increment = numericValue / 50; // 50 étapes d'animation
                    const duration = 1500; // 1.5 secondes
                    const stepTime = duration / 50;

                    const counter = setInterval(() => {
                        current += increment;
                        if (current >= numericValue) {
                            current = numericValue;
                            clearInterval(counter);
                        }

                        if (isPercentage) {
                            element.textContent = current.toFixed(1) + '%';
                        } else if (finalValue.includes(',')) {
                            element.textContent = Math.floor(current).toLocaleString();
                        } else {
                            element.textContent = Math.floor(current).toString();
                        }
                    }, stepTime);
                }

                createNavSteps() {
                    const navSteps = document.getElementById('navSteps');
                    navSteps.innerHTML = '';

                    this.steps.forEach((step, index) => {
                        const stepElement = document.createElement('div');
                        stepElement.className = `nav-step ${index === 0 ? 'active' : ''}`;
                        stepElement.setAttribute('data-step', index);
                        stepElement.innerHTML = `
                        <div class="nav-step-icon">${step.icon}</div>
                        <div class="nav-step-title">${step.title}</div>
                        <div class="nav-step-subtitle">${step.subtitle}</div>
                    `;
                        stepElement.addEventListener('click', () => this.goToStep(index));
                        navSteps.appendChild(stepElement);
                    });
                }

                updateUI() {
                    // Mise à jour de la barre de progression
                    const progressBar = document.getElementById('progressBar');
                    const progress = ((this.currentStep + 1) / this.totalSteps) * 100;
                    progressBar.style.width = `${progress}%`;

                    // Mise à jour des étapes
                    document.querySelectorAll('.nav-step').forEach((step, index) => {
                        step.classList.remove('active', 'completed');
                        if (index === this.currentStep) {
                            step.classList.add('active');
                        } else if (index < this.currentStep) {
                            step.classList.add('completed');
                        }
                    });

                    // Mise à jour des boutons
                    const prevBtn = document.getElementById('prevBtn');
                    const nextBtn = document.getElementById('nextBtn');

                    prevBtn.disabled = this.currentStep === 0;
                    nextBtn.textContent = this.currentStep === this.totalSteps - 1 ? 'Terminer' : 'Suivant';

                    // Mise à jour de l'indicateur
                    document.getElementById('stepIndicator').textContent =
                        `Étape ${this.currentStep + 1} sur ${this.totalSteps}`;

                    // Animation des sections
                    this.showCurrentSection();
                }

                showCurrentSection() {
                    document.querySelectorAll('.content-section').forEach((section, index) => {
                        section.classList.remove('active');
                        if (index === this.currentStep) {
                            section.classList.add('active');

                            // Réinitialiser les animations pour la section active
                            this.resetSectionAnimations(section);

                            // Déclencher les animations spécifiques à la section
                            setTimeout(() => {
                                this.triggerSectionAnimations(section, index);
                            }, 100);
                        }
                    });
                }

                resetSectionAnimations(section) {
                    // Réinitialiser le quiz
                    const quizOptions = section.querySelectorAll('.quiz-option');
                    quizOptions.forEach(option => {
                        option.classList.remove('correct', 'incorrect');
                        option.style.pointerEvents = 'auto';
                    });

                    const quizFeedback = section.querySelector('.quiz-feedback');
                    if (quizFeedback) {
                        quizFeedback.style.display = 'none';
                        quizFeedback.classList.remove('animate__animated', 'animate__fadeInUp');
                    }

                    // Réinitialiser les métriques animées
                    const metrics = section.querySelectorAll('.demo-metric-value');
                    metrics.forEach(metric => {
                        metric.classList.remove('animated');
                    });
                }

                triggerSectionAnimations(section, sectionIndex) {
                    // Animation des cards d'explication
                    const explanationCards = section.querySelectorAll('.explanation-card');
                    explanationCards.forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('animate__animated', 'animate__fadeInUp');
                        }, index * 200);
                    });

                    // Animation des éléments de démonstration
                    const demoElements = section.querySelectorAll('.demo-metric-card, .feature-card');
                    demoElements.forEach((element, index) => {
                        setTimeout(() => {
                            element.classList.add('animate__animated', 'animate__fadeInUp');
                        }, 300 + index * 100);
                    });

                    // Déclencher l'animation des métriques si présentes
                    if (sectionIndex === 1) { // Section Navigation
                        setTimeout(() => {
                            this.animateMetrics();
                        }, 500);
                    }
                }

                goToStep(step) {
                    if (step >= 0 && step < this.totalSteps) {
                        this.currentStep = step;
                        this.updateUI();
                        this.trackProgress();
                    }
                }

                nextStep() {
                    if (this.currentStep < this.totalSteps - 1) {
                        this.currentStep++;
                        this.updateUI();
                        this.trackProgress();
                    } else {
                        this.completeTutorial();
                    }
                }

                prevStep() {
                    if (this.currentStep > 0) {
                        this.currentStep--;
                        this.updateUI();
                    }
                }

                bindEvents() {
                    document.getElementById('nextBtn').addEventListener('click', () => this.nextStep());
                    document.getElementById('prevBtn').addEventListener('click', () => this.prevStep());

                    // Gestion du clavier
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowRight') this.nextStep();
                        if (e.key === 'ArrowLeft') this.prevStep();
                    });

                    // Animation des cards
                    document.querySelectorAll('.feature-card').forEach(card => {
                        card.addEventListener('click', () => {
                            card.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                card.style.transform = '';
                            }, 150);
                        });
                    });
                }

                animateEntry() {
                    // Animation d'entrée pour les éléments
                    setTimeout(() => {
                        document.querySelectorAll('.feature-card').forEach((card, index) => {
                            setTimeout(() => {
                                card.classList.add('animate__animated', 'animate__fadeInUp');
                            }, index * 100);
                        });
                    }, 500);
                }

                trackProgress() {
                    // Ici on pourrait ajouter un tracking des analytics
                    console.log(`Étape ${this.currentStep + 1} visitée`);

                    // Animations spécifiques par section
                    this.handleSectionAnimations();
                }

                handleSectionAnimations() {
                    const currentSection = document.getElementById(`section-${this.currentStep}`);
                    if (!currentSection) return;

                    switch (this.currentStep) {
                        case 3: // Articles
                            this.animateArticlesSection();
                            break;
                        case 6: // Analytics
                            this.animateMetrics();
                            break;
                        case 7: // Doublons
                            this.setupConflictDemo();
                            break;
                        case 9: // Completion
                            this.animateCompletion();
                            break;
                    }
                }

                animateArticlesSection() {
                    // Animation pour la section Articles
                    setTimeout(() => {
                        const metierItems = document.querySelectorAll('.metier-item');
                        metierItems.forEach((item, index) => {
                            setTimeout(() => {
                                item.style.animation = 'slideInLeft 0.6s ease-out forwards';
                            }, index * 200);
                        });

                        const articleItems = document.querySelectorAll('.article-item');
                        articleItems.forEach((item, index) => {
                            setTimeout(() => {
                                item.style.animation = 'fadeInUp 0.6s ease-out forwards';
                            }, 500 + (index * 150));
                        });
                    }, 300);
                }

                animateMetrics() {
                    // Animation des métriques avec compteur
                    const metricValues = document.querySelectorAll('.metric-value');
                    metricValues.forEach(metric => {
                        const targetValue = parseFloat(metric.dataset.value);
                        const duration = 2000;
                        const startTime = Date.now();

                        const animate = () => {
                            const elapsed = Date.now() - startTime;
                            const progress = Math.min(elapsed / duration, 1);

                            // Fonction d'easing
                            const easeOut = 1 - Math.pow(1 - progress, 3);
                            const currentValue = targetValue * easeOut;

                            metric.textContent = `${currentValue.toFixed(1)}%`;

                            if (progress < 1) {
                                requestAnimationFrame(animate);
                            }
                        };

                        setTimeout(() => animate(), 500);
                    });

                    // Animation des graphiques
                    setTimeout(() => {
                        const charts = document.querySelectorAll('.chart-placeholder');
                        charts.forEach((chart, index) => {
                            setTimeout(() => {
                                chart.style.animation = 'bounceIn 0.8s ease-out forwards';
                            }, 1000 + (index * 200));
                        });
                    }, 1000);
                }

                setupConflictDemo() {
                    // Configuration de la démo des conflits
                    const conflictCards = document.querySelectorAll('.conflict-card');

                    conflictCards.forEach((card, index) => {
                        setTimeout(() => {
                            card.style.animation = `slideIn${index === 0 ? 'Left' : 'Right'} 0.6s ease-out forwards`;
                        }, 500 + (index * 300));
                    });

                    // Animation du VS indicator
                    setTimeout(() => {
                        const vsIndicator = document.querySelector('.vs-indicator');
                        if (vsIndicator) {
                            vsIndicator.style.animation = 'pulse 1s ease-in-out infinite';
                        }
                    }, 1200);
                }

                animateCompletion() {
                    // Animation de la section finale
                    setTimeout(() => {
                        const stats = document.querySelectorAll('.stat-item');
                        stats.forEach((stat, index) => {
                            setTimeout(() => {
                                stat.style.animation = 'bounceIn 0.8s ease-out forwards';
                            }, index * 200);
                        });
                    }, 500);

                    setTimeout(() => {
                        const skills = document.querySelectorAll('.skill-item');
                        skills.forEach((skill, index) => {
                            setTimeout(() => {
                                skill.style.opacity = '0';
                                skill.style.animation = 'fadeInUp 0.6s ease-out forwards';
                            }, 1000 + (index * 100));
                        });
                    }, 1000);

                    // Animation du certificat
                    setTimeout(() => {
                        const certificate = document.querySelector('.certificate');
                        if (certificate) {
                            certificate.style.animation = 'zoomIn 1s ease-out forwards';
                        }
                    }, 2000);
                }

                completeTutorial() {
                    // Animation de completion
                    const confetti = this.createConfetti();
                    alert('🎉 Félicitations ! Vous avez terminé le didacticiel EquiNomTech !');

                    // Redirection optionnelle
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                }

                createConfetti() {
                    // Animation de confettis simple
                    for (let i = 0; i < 50; i++) {
                        setTimeout(() => {
                            const confetti = document.createElement('div');
                            confetti.style.cssText = `
                            position: fixed;
                            top: -10px;
                            left: ${Math.random() * 100}%;
                            width: 10px;
                            height: 10px;
                            background: hsl(${Math.random() * 360}, 70%, 60%);
                            pointer-events: none;
                            z-index: 10000;
                            animation: fall 3s linear forwards;
                        `;
                            document.body.appendChild(confetti);

                            setTimeout(() => confetti.remove(), 3000);
                        }, i * 50);
                    }
                }
            }

            // Style pour l'animation de chute
            const style = document.createElement('style');
            style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
            document.head.appendChild(style);

            // Initialisation
            document.addEventListener('DOMContentLoaded', () => {
                new TutorialManager();

                // Initialisation des fonctionnalités interactives
                initInteractiveFeatures();
            });

            // Fonctionnalités interactives supplémentaires
            function initInteractiveFeatures() {
                // Simulation de drag & drop pour l'import
                const dragZones = document.querySelectorAll('.drag-drop-zone');
                dragZones.forEach(zone => {
                    zone.addEventListener('click', () => {
                        simulateFileUpload(zone);
                    });

                    zone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        zone.style.borderColor = 'var(--primary-color)';
                        zone.style.background = 'rgba(74, 144, 226, 0.1)';
                    });

                    zone.addEventListener('dragleave', () => {
                        zone.style.borderColor = 'var(--border-color)';
                        zone.style.background = '';
                    });
                });

                // Boutons d'export interactifs
                const exportBtns = document.querySelectorAll('.export-btn');
                exportBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        simulateExport(btn);
                    });
                });

                // Boutons d'action dans la section équipements
                const actionBtns = document.querySelectorAll('.action-item button');
                actionBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        simulateAction(btn);
                    });
                });

                // Simulation de recherche en temps réel
                const searchInputs = document.querySelectorAll('input[placeholder*="pompe"]');
                searchInputs.forEach(input => {
                    input.addEventListener('input', (e) => {
                        simulateSearch(e.target);
                    });
                });
            }

            function simulateFileUpload(zone) {
                // Simulation d'upload de fichier
                zone.innerHTML = `
                <div class="upload-icon">⏳</div>
                <p>Upload en cours...</p>
                <div class="progress-bar" style="width: 100%; height: 4px; background: #eee; border-radius: 2px; margin-top: 10px;">
                    <div class="progress-fill" style="width: 0%; height: 100%; background: var(--primary-color); border-radius: 2px; transition: width 0.3s ease;"></div>
                </div>
            `;

                const progressFill = zone.querySelector('.progress-fill');
                let progress = 0;

                const interval = setInterval(() => {
                    progress += Math.random() * 20;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(interval);

                        setTimeout(() => {
                            zone.innerHTML = `
                            <div class="upload-icon">✅</div>
                            <p><strong>Fichier uploadé avec succès !</strong></p>
                            <small>1,247 lignes traitées</small>
                        `;
                            zone.style.borderColor = '#27ae60';
                            zone.style.background = 'rgba(39, 174, 96, 0.1)';
                        }, 500);
                    }
                    progressFill.style.width = progress + '%';
                }, 200);
            }

            function simulateExport(btn) {
                const originalText = btn.textContent;
                btn.textContent = 'Génération...';
                btn.disabled = true;
                btn.style.opacity = '0.7';

                setTimeout(() => {
                    btn.textContent = '✅ Téléchargé !';
                    btn.style.background = '#27ae60';
                    btn.style.borderColor = '#27ae60';
                    btn.style.color = 'white';

                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.disabled = false;
                        btn.style.opacity = '';
                        btn.style.background = '';
                        btn.style.borderColor = '';
                        btn.style.color = '';
                    }, 2000);
                }, 1500);
            }

            function simulateAction(btn) {
                const originalText = btn.textContent;
                const action = btn.closest('.action-item').querySelector('h5').textContent;

                btn.textContent = 'En cours...';
                btn.disabled = true;

                setTimeout(() => {
                    if (action.includes('Ajouter')) {
                        showModal('Nouvel Équipement', 'Formulaire de création d\'équipement ouvert !');
                    } else if (action.includes('Modifier')) {
                        showModal('Modification', 'Équipement MP1080A en cours d\'édition...');
                    } else if (action.includes('Supprimer')) {
                        showModal('Confirmation', 'Êtes-vous sûr de vouloir supprimer cet équipement ?');
                    }

                    btn.textContent = originalText;
                    btn.disabled = false;
                }, 1000);
            }

            function simulateSearch(input) {
                const query = input.value.toLowerCase();

                if (query.includes('pompe')) {
                    // Simulation de résultats de recherche
                    let resultsContainer = input.parentNode.querySelector('.search-results');

                    if (!resultsContainer) {
                        resultsContainer = document.createElement('div');
                        resultsContainer.className = 'search-results';
                        resultsContainer.style.cssText = `
                        position: absolute;
                        top: 100%;
                        left: 0;
                        right: 0;
                        background: white;
                        border: 1px solid var(--border-color);
                        border-radius: 8px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                        z-index: 1000;
                        max-height: 200px;
                        overflow-y: auto;
                    `;
                        input.parentNode.style.position = 'relative';
                        input.parentNode.appendChild(resultsContainer);
                    }

                    resultsContainer.innerHTML = `
                    <div style="padding: 0.5rem; border-bottom: 1px solid #eee; cursor: pointer;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background=''">
                        <strong>MP1080A</strong> - Pompe centrifuge principale
                    </div>
                    <div style="padding: 0.5rem; border-bottom: 1px solid #eee; cursor: pointer;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background=''">
                        <strong>MP1085B</strong> - Pompe de relevage
                    </div>
                    <div style="padding: 0.5rem; cursor: pointer;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background=''">
                        <strong>MP2010C</strong> - Pompe doseuse
                    </div>
                `;

                    setTimeout(() => {
                        if (resultsContainer) {
                            resultsContainer.remove();
                        }
                    }, 3000);
                }
            }

            function showModal(title, content) {
                // Création d'une modal simple
                const modal = document.createElement('div');
                modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;

                modal.innerHTML = `
                <div style="
                    background: white;
                    border-radius: 12px;
                    padding: 2rem;
                    max-width: 400px;
                    width: 90%;
                    text-align: center;
                    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                ">
                    <h4 style="margin-bottom: 1rem; color: var(--primary-color);">${title}</h4>
                    <p style="margin-bottom: 1.5rem;">${content}</p>
                    <button onclick="this.closest('[style*=\\'fixed\\']').remove()" style="
                        background: var(--primary-color);
                        color: white;
                        border: none;
                        padding: 0.75rem 1.5rem;
                        border-radius: 6px;
                        cursor: pointer;
                        font-weight: 500;
                    ">OK</button>
                </div>
            `;

                document.body.appendChild(modal);

                // Fermeture automatique après 3 secondes
                setTimeout(() => {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                }, 3000);
            }

            // Animation de typing pour les textes
            function typeWriter(element, text, speed = 50) {
                let i = 0;
                element.innerHTML = '';

                function type() {
                    if (i < text.length) {
                        element.innerHTML += text.charAt(i);
                        i++;
                        setTimeout(type, speed);
                    }
                }
                type();
            }

            // Effets visuels supplémentaires
            document.addEventListener('mousemove', (e) => {
                const cursor = document.querySelector('.cursor-trail');
                if (!cursor) {
                    const trail = document.createElement('div');
                    trail.className = 'cursor-trail';
                    trail.style.cssText = `
                    position: fixed;
                    width: 20px;
                    height: 20px;
                    background: radial-gradient(circle, rgba(102,126,234,0.3) 0%, transparent 70%);
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 1000;
                    transition: transform 0.1s ease;
                `;
                    document.body.appendChild(trail);
                }

                const trail = document.querySelector('.cursor-trail');
                trail.style.left = e.clientX - 10 + 'px';
                trail.style.top = e.clientY - 10 + 'px';
            });
        </script>
</body>

</html>