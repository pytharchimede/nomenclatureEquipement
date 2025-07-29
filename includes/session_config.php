<?php

/**
 * Configuration et gestion robuste des sessions
 * Ce fichier doit être inclus en premier dans chaque page nécessitant une session
 */

// Ne démarrer une session que si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    // Configuration des sessions pour éviter les erreurs de serveur
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Mettre à 1 si HTTPS

    // Tentative de démarrage de session avec gestion d'erreur
    try {
        // Configurer le chemin de sauvegarde des sessions si nécessaire
        $session_save_path = sys_get_temp_dir();
        if (is_writable($session_save_path)) {
            ini_set('session.save_path', $session_save_path);
        }

        session_start();
    } catch (Exception $e) {
        // Fallback : essayer avec un chemin alternatif
        try {
            ini_set('session.save_path', '/tmp');
            session_start();
        } catch (Exception $e2) {
            // Dernière tentative : utiliser le répertoire par défaut
            session_start();
        }
    }
}
