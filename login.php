<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style_login.css">
</head>

<body>
    <div class="login-container">
        <!-- Logo et libellé -->
        <div class="login-logo-block">
            <span class="login-logo">
                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" style="margin:8px;" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.03 7.03 0 0 0-1.62-.94l-.36-2.53A.5.5 0 0 0 14 2h-4a.5.5 0 0 0-.5.42l-.36 2.53a7.03 7.03 0 0 0-1.62.94l-2.39-.96a.5.5 0 0 0-.6.22l-1.92 3.32a.5.5 0 0 0 .12.64l2.03 1.58c-.04.3-.06.61-.06.94s.02.64.06.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96c.5.36 1.04.67 1.62.94l.36 2.53A.5.5 0 0 0 10 22h4a.5.5 0 0 0 .5-.42l.36-2.53c.58-.27 1.12-.58 1.62-.94l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8.5a3.5 3.5 0 0 1 0 7z" fill="#1976d2" />
                </svg>
            </span>
            <span class="login-libelle">
                Nomenclature Équipements
            </span>
        </div>
        <div class="login-title">Connexion à votre compte</div>
        <div id="message" class="alert" style="display:none"></div>
        <form id="loginForm" autocomplete="off">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="mot_de_passe" class="form-control" required>
            </div>
            <button type="submit" class="login-btn" id="loginBtn">
                Se connecter
                <span id="spinner" style="display:none;" class="spinner"></span>
            </button>
            <div style="margin-top:1rem;">
                <a href="mot_de_passe_oublie.php" style="color:#4285f4;font-size:0.98rem;text-decoration:none;">
                    Mot de passe oublié ?
                </a>
            </div>
        </form>
    </div>
    <script src="js/login.js"></script>
</body>

</html>