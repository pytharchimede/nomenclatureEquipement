-- Script de mise à jour complète pour le système d'authentification moderne
-- Date: 25 juillet 2025
-- Base de données: fidestci_nomenclatureequipement_db

USE fidestci_nomenclatureequipement_db;

-- 1. Mise à jour de la table utilisateur avec les nouveaux champs
ALTER TABLE `utilisateur` 
ADD COLUMN IF NOT EXISTS `nom_utilisateur` varchar(100) DEFAULT NULL AFTER `nom`,
ADD COLUMN IF NOT EXISTS `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `date_creation`,
ADD COLUMN IF NOT EXISTS `last_login_at` datetime DEFAULT NULL AFTER `updated_at`,
ADD COLUMN IF NOT EXISTS `last_login_ip` varchar(45) DEFAULT NULL AFTER `last_login_at`,
ADD COLUMN IF NOT EXISTS `login_attempts` int DEFAULT 0 AFTER `last_login_ip`,
ADD COLUMN IF NOT EXISTS `locked_until` datetime DEFAULT NULL AFTER `login_attempts`,
ADD COLUMN IF NOT EXISTS `password_reset_token` varchar(255) DEFAULT NULL AFTER `locked_until`,
ADD COLUMN IF NOT EXISTS `password_reset_expires` datetime DEFAULT NULL AFTER `password_reset_token`,
ADD COLUMN IF NOT EXISTS `email_verified` tinyint(1) DEFAULT 1 AFTER `password_reset_expires`,
ADD COLUMN IF NOT EXISTS `email_verification_token` varchar(255) DEFAULT NULL AFTER `email_verified`;

-- 2. Création de la table login_attempts pour la sécurité
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `email` varchar(100) NOT NULL,
  `attempted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_agent` text,
  `success` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_email` (`email`),
  KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. Création de la table password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 4. Création de la table email_verification_tokens
CREATE TABLE IF NOT EXISTS `email_verification_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 5. Ajout des index pour améliorer les performances
ALTER TABLE `utilisateur` 
ADD INDEX IF NOT EXISTS `idx_nom_utilisateur` (`nom_utilisateur`),
ADD INDEX IF NOT EXISTS `idx_last_login_at` (`last_login_at`),
ADD INDEX IF NOT EXISTS `idx_password_reset_token` (`password_reset_token`),
ADD INDEX IF NOT EXISTS `idx_email_verification_token` (`email_verification_token`);

-- 6. Mise à jour des données existantes
UPDATE `utilisateur` SET 
    `nom_utilisateur` = COALESCE(`nom_utilisateur`, `nom`),
    `updated_at` = COALESCE(`updated_at`, `date_creation`),
    `email_verified` = 1,
    `login_attempts` = 0
WHERE `nom_utilisateur` IS NULL OR `updated_at` IS NULL;

-- 7. Rendre nom_utilisateur unique (si pas déjà fait)
-- ALTER TABLE `utilisateur` ADD UNIQUE KEY `unique_nom_utilisateur` (`nom_utilisateur`);

-- 8. Nettoyage des anciennes tentatives de connexion (plus de 24h)
CREATE EVENT IF NOT EXISTS `cleanup_login_attempts`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
  DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- 9. Nettoyage des tokens expirés
CREATE EVENT IF NOT EXISTS `cleanup_expired_tokens`
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
BEGIN
  DELETE FROM password_reset_tokens WHERE expires_at < NOW();
  DELETE FROM email_verification_tokens WHERE expires_at < NOW();
END;

COMMIT;

-- Affichage des résultats
SELECT 'Mise à jour terminée avec succès!' as status;
SELECT COUNT(*) as nombre_utilisateurs FROM utilisateur;
SELECT COUNT(*) as nombre_groupes FROM groupe_utilisateur;
