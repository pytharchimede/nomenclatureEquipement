<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquiNomTech - Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            --danger-gradient: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 20px 60px rgba(0, 0, 0, 0.2);
            --border-radius: 20px;
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
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
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animations de fond */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            z-index: 0;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
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

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 3rem 2.5rem;
            box-shadow: var(--shadow-strong);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2.5rem;
            animation: slideInUp 0.8s ease-out 0.2s both;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-soft);
            animation: pulse 2s ease-in-out infinite;
        }

        .logo .material-icons {
            font-size: 2.5rem;
            color: white;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .logo-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .auth-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            text-align: center;
            margin-bottom: 2rem;
            animation: slideInUp 0.8s ease-out 0.4s both;
        }

        .form-group {
            margin-bottom: 1.5rem;
            animation: slideInUp 0.8s ease-out 0.6s both;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
            position: relative;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
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
            cursor: pointer;
            transition: var(--transition);
        }

        .input-icon:hover {
            color: #667eea;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out 0.8s both;
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
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            margin-left: 10px;
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            animation: slideInUp 0.8s ease-out 1s both;
        }

        .auth-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .auth-links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            animation: slideInUp 0.8s ease-out 0.9s both;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: none;
            font-weight: 500;
            animation: slideInUp 0.3s ease-out;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.1);
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        .alert-warning {
            background: rgba(255, 152, 0, 0.1);
            color: #e65100;
            border-left: 4px solid #ff9800;
        }

        /* Toggle entre connexion et inscription */
        .auth-toggle {
            text-align: center;
            margin-top: 2rem;
            animation: slideInUp 0.8s ease-out 1.1s both;
        }

        .toggle-link {
            color: #667eea;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .toggle-link:hover {
            color: #764ba2;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .auth-container {
                padding: 15px;
            }

            .auth-card {
                padding: 2rem 1.5rem;
            }

            .logo {
                width: 60px;
                height: 60px;
            }

            .logo .material-icons {
                font-size: 2rem;
            }
        }

        /* Animation d'entrée pour les champs */
        .form-group:nth-child(1) {
            animation-delay: 0.6s;
        }

        .form-group:nth-child(2) {
            animation-delay: 0.7s;
        }

        .form-group:nth-child(3) {
            animation-delay: 0.8s;
        }

        .form-group:nth-child(4) {
            animation-delay: 0.9s;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo-container">
                <div class="logo">
                    <span class="material-icons">precision_manufacturing</span>
                </div>
                <div class="logo-text">EquiNomTech</div>
                <div class="logo-subtitle">Gestion Nomenclature Équipements</div>
            </div>

            <!-- Zone d'alerte -->
            <div id="alertContainer"></div>

            <!-- Formulaire de connexion -->
            <div id="loginForm">
                <h2 class="auth-title">Connexion</h2>
                <form id="loginFormElement">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <input type="email" id="loginEmail" class="form-control" placeholder="votre@email.com" required>
                            <span class="input-icon material-icons">email</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
                            <span class="input-icon material-icons" onclick="togglePassword('loginPassword', this)">visibility_off</span>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" id="loginBtn">
                        <span id="loginBtnText">Se connecter</span>
                        <span id="loginSpinner" class="spinner" style="display: none;"></span>
                    </button>
                </form>

                <div class="auth-links">
                    <a href="#" onclick="showForgotPassword()">Mot de passe oublié ?</a>
                </div>

                <div class="divider">
                    <span>ou</span>
                </div>

                <div class="auth-toggle">
                    <span>Pas encore de compte ? </span>
                    <a href="#" class="toggle-link" onclick="showRegisterForm()">S'inscrire</a>
                </div>
            </div>

            <!-- Formulaire d'inscription -->
            <div id="registerForm" style="display: none;">
                <h2 class="auth-title">Inscription</h2>
                <form id="registerFormElement">
                    <div class="form-group">
                        <label class="form-label">Nom complet</label>
                        <div class="input-group">
                            <input type="text" id="registerName" class="form-control" placeholder="Votre nom complet" required>
                            <span class="input-icon material-icons">person</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <input type="email" id="registerEmail" class="form-control" placeholder="votre@email.com" required>
                            <span class="input-icon material-icons">email</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <div class="input-group">
                            <input type="tel" id="registerPhone" class="form-control" placeholder="+33 6 12 34 56 78">
                            <span class="input-icon material-icons">phone</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" id="registerPassword" class="form-control" placeholder="••••••••" required>
                            <span class="input-icon material-icons" onclick="togglePassword('registerPassword', this)">visibility_off</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <div class="input-group">
                            <input type="password" id="registerConfirmPassword" class="form-control" placeholder="••••••••" required>
                            <span class="input-icon material-icons" onclick="togglePassword('registerConfirmPassword', this)">visibility_off</span>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" id="registerBtn">
                        <span id="registerBtnText">Créer mon compte</span>
                        <span id="registerSpinner" class="spinner" style="display: none;"></span>
                    </button>
                </form>

                <div class="auth-toggle">
                    <span>Déjà un compte ? </span>
                    <a href="#" class="toggle-link" onclick="showLoginForm()">Se connecter</a>
                </div>
            </div>

            <!-- Formulaire mot de passe oublié -->
            <div id="forgotForm" style="display: none;">
                <h2 class="auth-title">Récupération</h2>
                <p style="text-align: center; color: #6b7280; margin-bottom: 2rem;">
                    Entrez votre email pour recevoir un lien de réinitialisation
                </p>
                <form id="forgotFormElement">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <input type="email" id="forgotEmail" class="form-control" placeholder="votre@email.com" required>
                            <span class="input-icon material-icons">email</span>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" id="forgotBtn">
                        <span id="forgotBtnText">Envoyer le lien</span>
                        <span id="forgotSpinner" class="spinner" style="display: none;"></span>
                    </button>
                </form>

                <div class="auth-toggle">
                    <a href="#" class="toggle-link" onclick="showLoginForm()">← Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentForm = 'login';

        // Fonctions de navigation entre les formulaires
        function showLoginForm() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('forgotForm').style.display = 'none';
            currentForm = 'login';
            clearAlerts();
        }

        function showRegisterForm() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
            document.getElementById('forgotForm').style.display = 'none';
            currentForm = 'register';
            clearAlerts();
        }

        function showForgotPassword() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('forgotForm').style.display = 'block';
            currentForm = 'forgot';
            clearAlerts();
        }

        // Fonction pour afficher/masquer le mot de passe
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

        // Fonction pour afficher les alertes
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertHtml = `
                <div class="alert alert-${type}">
                    <span class="material-icons" style="vertical-align: middle; margin-right: 8px;">
                        ${type === 'success' ? 'check_circle' : type === 'warning' ? 'warning' : 'error'}
                    </span>
                    ${message}
                </div>
            `;
            alertContainer.innerHTML = alertHtml;
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function clearAlerts() {
            document.getElementById('alertContainer').innerHTML = '';
        }

        // Validation du mot de passe
        function validatePassword(password) {
            const minLength = 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            if (password.length < minLength) {
                return 'Le mot de passe doit contenir au moins 8 caractères';
            }
            if (!hasUpper || !hasLower) {
                return 'Le mot de passe doit contenir des majuscules et minuscules';
            }
            if (!hasNumber) {
                return 'Le mot de passe doit contenir au moins un chiffre';
            }
            return null;
        }

        // Gestion du formulaire de connexion
        document.getElementById('loginFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('loginBtnText');
            const spinner = document.getElementById('loginSpinner');

            // Affichage du spinner
            btn.disabled = true;
            btnText.textContent = 'Connexion...';
            spinner.style.display = 'inline-block';

            try {
                const response = await fetch('api/auth/login_simple.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email,
                        password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Connexion réussie ! Redirection...', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    showAlert(data.message || 'Erreur de connexion');
                }
            } catch (error) {
                showAlert('Erreur de connexion au serveur');
                console.error('Erreur:', error);
            } finally {
                // Masquage du spinner
                btn.disabled = false;
                btnText.textContent = 'Se connecter';
                spinner.style.display = 'none';
            }
        });

        // Gestion du formulaire d'inscription
        document.getElementById('registerFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();

            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const phone = document.getElementById('registerPhone').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('registerConfirmPassword').value;
            const btn = document.getElementById('registerBtn');
            const btnText = document.getElementById('registerBtnText');
            const spinner = document.getElementById('registerSpinner');

            // Validation côté client
            if (password !== confirmPassword) {
                showAlert('Les mots de passe ne correspondent pas');
                return;
            }

            const passwordError = validatePassword(password);
            if (passwordError) {
                showAlert(passwordError);
                return;
            }

            // Affichage du spinner
            btn.disabled = true;
            btnText.textContent = 'Création...';
            spinner.style.display = 'inline-block';

            try {
                const response = await fetch('api/auth/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        nom_utilisateur: name,
                        email,
                        telephone: phone,
                        mot_de_passe: password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Compte créé avec succès ! Vous pouvez maintenant vous connecter.', 'success');
                    setTimeout(() => {
                        showLoginForm();
                    }, 2000);
                } else {
                    showAlert(data.message || 'Erreur lors de la création du compte');
                }
            } catch (error) {
                showAlert('Erreur de connexion au serveur');
                console.error('Erreur:', error);
            } finally {
                // Masquage du spinner
                btn.disabled = false;
                btnText.textContent = 'Créer mon compte';
                spinner.style.display = 'none';
            }
        });

        // Gestion du formulaire mot de passe oublié
        document.getElementById('forgotFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('forgotEmail').value;
            const btn = document.getElementById('forgotBtn');
            const btnText = document.getElementById('forgotBtnText');
            const spinner = document.getElementById('forgotSpinner');

            // Affichage du spinner
            btn.disabled = true;
            btnText.textContent = 'Envoi...';
            spinner.style.display = 'inline-block';

            try {
                const response = await fetch('api/auth/forgot-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Email de récupération envoyé ! Vérifiez votre boîte mail.', 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de l\'envoi de l\'email');
                }
            } catch (error) {
                showAlert('Erreur de connexion au serveur');
                console.error('Erreur:', error);
            } finally {
                // Masquage du spinner
                btn.disabled = false;
                btnText.textContent = 'Envoyer le lien';
                spinner.style.display = 'none';
            }
        });

        // Gestion des touches clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                clearAlerts();
            }
        });

        // Animation d'entrée au chargement
        window.addEventListener('load', function() {
            document.querySelector('.auth-card').style.opacity = '1';
        });
    </script>
</body>

</html>