-- Script de création des tables pour le système d'authentification moderne

-- Table pour les tentatives de connexion (sécurité)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, created_at)
);

-- Table pour les réinitialisations de mot de passe
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at)
);

-- Mise à jour de la table utilisateurs pour les nouvelles fonctionnalités
ALTER TABLE utilisateurs 
ADD COLUMN IF NOT EXISTS verification_token VARCHAR(64) NULL,
ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) NULL,
ADD COLUMN IF NOT EXISTS created_ip VARCHAR(45) NULL,
ADD COLUMN IF NOT EXISTS two_factor_secret VARCHAR(32) NULL,
ADD COLUMN IF NOT EXISTS two_factor_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS profile_completed BOOLEAN DEFAULT FALSE;

-- Index pour améliorer les performances
ALTER TABLE utilisateurs 
ADD INDEX IF NOT EXISTS idx_email (email),
ADD INDEX IF NOT EXISTS idx_nom_utilisateur (nom_utilisateur),
ADD INDEX IF NOT EXISTS idx_verification_token (verification_token),
ADD INDEX IF NOT EXISTS idx_actif (actif);

-- Table des sessions utilisateur (optionnel, pour un contrôle avancé)
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    payload TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
);

-- Table pour l'audit des actions utilisateurs
CREATE TABLE IF NOT EXISTS user_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Table pour les notifications utilisateur
CREATE TABLE IF NOT EXISTS user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_read_at (read_at),
    INDEX idx_created_at (created_at)
);

-- Table pour les préférences utilisateur
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    theme VARCHAR(20) DEFAULT 'light',
    language VARCHAR(5) DEFAULT 'fr',
    timezone VARCHAR(50) DEFAULT 'Europe/Paris',
    notifications_email BOOLEAN DEFAULT TRUE,
    notifications_push BOOLEAN DEFAULT TRUE,
    dashboard_layout JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Insertion des préférences par défaut pour les utilisateurs existants
INSERT IGNORE INTO user_preferences (user_id) 
SELECT id FROM utilisateurs WHERE id NOT IN (SELECT user_id FROM user_preferences);

-- Nettoyage automatique des données expirées (à exécuter périodiquement)
-- DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
-- DELETE FROM password_resets WHERE expires_at < NOW();
-- DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Vues utiles pour les statistiques
CREATE OR REPLACE VIEW user_stats AS
SELECT 
    u.id,
    u.nom_utilisateur,
    u.email,
    u.actif,
    u.date_creation,
    u.last_login_at,
    COUNT(DISTINCT la.id) as total_login_attempts,
    COUNT(DISTINCT CASE WHEN la.success = 1 THEN la.id END) as successful_logins,
    COUNT(DISTINCT n.id) as unread_notifications
FROM utilisateurs u
LEFT JOIN login_attempts la ON la.ip_address = u.last_login_ip
LEFT JOIN user_notifications n ON n.user_id = u.id AND n.read_at IS NULL
GROUP BY u.id;

-- Vue pour les connexions récentes
CREATE OR REPLACE VIEW recent_logins AS
SELECT 
    u.nom_utilisateur,
    u.email,
    la.ip_address,
    la.created_at as login_time,
    la.success
FROM login_attempts la
JOIN utilisateurs u ON u.last_login_ip = la.ip_address
WHERE la.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY la.created_at DESC;

-- Triggers pour l'audit automatique
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS tr_user_update_audit
AFTER UPDATE ON utilisateurs
FOR EACH ROW
BEGIN
    INSERT INTO user_audit_log (user_id, action, table_name, record_id, old_values, new_values, created_at)
    VALUES (
        NEW.id,
        'UPDATE',
        'utilisateurs',
        NEW.id,
        JSON_OBJECT(
            'nom_utilisateur', OLD.nom_utilisateur,
            'email', OLD.email,
            'actif', OLD.actif
        ),
        JSON_OBJECT(
            'nom_utilisateur', NEW.nom_utilisateur,
            'email', NEW.email,
            'actif', NEW.actif
        ),
        NOW()
    );
END$$

DELIMITER ;

-- Procédure stockée pour nettoyer les données expirées
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS CleanExpiredData()
BEGIN
    -- Nettoyage des tentatives de connexion anciennes
    DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    -- Nettoyage des tokens de récupération expirés
    DELETE FROM password_resets WHERE expires_at < NOW();
    
    -- Nettoyage des sessions inactives
    DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Nettoyage des logs d'audit anciens (> 1 an)
    DELETE FROM user_audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
    
    SELECT 'Nettoyage terminé' as status;
END$$

DELIMITER ;
