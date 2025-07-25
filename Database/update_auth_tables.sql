-- Mise à jour de la table utilisateur pour le système d'authentification moderne
-- Date: 25 juillet 2025

-- Ajouter les nouveaux champs nécessaires pour l'authentification moderne
ALTER TABLE `utilisateur` 
ADD COLUMN `nom_utilisateur` varchar(100) DEFAULT NULL AFTER `nom`,
ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `date_creation`,
ADD COLUMN `last_login_at` datetime DEFAULT NULL AFTER `updated_at`,
ADD COLUMN `login_attempts` int DEFAULT 0 AFTER `last_login_at`,
ADD COLUMN `locked_until` datetime DEFAULT NULL AFTER `login_attempts`,
ADD COLUMN `password_reset_token` varchar(255) DEFAULT NULL AFTER `locked_until`,
ADD COLUMN `password_reset_expires` datetime DEFAULT NULL AFTER `password_reset_token`,
ADD COLUMN `email_verified` tinyint(1) DEFAULT 0 AFTER `password_reset_expires`,
ADD COLUMN `email_verification_token` varchar(255) DEFAULT NULL AFTER `email_verified`;

-- Ajouter un index sur nom_utilisateur pour de meilleures performances
ALTER TABLE `utilisateur` ADD INDEX `idx_nom_utilisateur` (`nom_utilisateur`);

-- Ajouter des index pour les tokens de sécurité
ALTER TABLE `utilisateur` ADD INDEX `idx_password_reset_token` (`password_reset_token`);
ALTER TABLE `utilisateur` ADD INDEX `idx_email_verification_token` (`email_verification_token`);

-- Mettre à jour les données existantes
UPDATE `utilisateur` SET 
    `nom_utilisateur` = `nom`,
    `updated_at` = `date_creation`,
    `email_verified` = 1
WHERE `nom_utilisateur` IS NULL;

-- Rendre nom_utilisateur unique
ALTER TABLE `utilisateur` ADD UNIQUE KEY `unique_nom_utilisateur` (`nom_utilisateur`);

-- Optionnel: Renommer la table pour correspondre au code (recommandé)
-- RENAME TABLE `utilisateur` TO `utilisateurs`;

COMMIT;
