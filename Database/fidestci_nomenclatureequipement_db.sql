-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : jeu. 29 mai 2025 à 14:23
-- Version du serveur : 10.11.11-MariaDB-cll-lve
-- Version de PHP : 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `fidestci_nomenclatureequipement_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `code_article` varchar(50) NOT NULL,
  `designation_article` varchar(255) DEFAULT NULL,
  `type_article` varchar(100) DEFAULT NULL,
  `temsup_niv_mdt` varchar(100) DEFAULT NULL,
  `ancien_num_article` varchar(100) DEFAULT NULL,
  `uq_base` varchar(50) DEFAULT NULL,
  `fabricant` varchar(255) DEFAULT NULL,
  `numero_piece_fabricant` varchar(100) DEFAULT NULL,
  `groupe_articles` varchar(100) DEFAULT NULL,
  `groupe_marche_externe` varchar(100) DEFAULT NULL,
  `document` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_creation` date DEFAULT NULL,
  `cree_par` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `equipements`
--

CREATE TABLE `equipements` (
  `id` int(11) NOT NULL,
  `code_equipement` varchar(50) NOT NULL,
  `designation_equipement` varchar(255) DEFAULT NULL,
  `repere_equipement` varchar(100) DEFAULT NULL,
  `fabricant` varchar(255) DEFAULT NULL,
  `type_objet` varchar(100) DEFAULT NULL,
  `designation_type` varchar(255) DEFAULT NULL,
  `numero_serie_fabricant` varchar(100) DEFAULT NULL,
  `numero_piece_fabricant` varchar(100) DEFAULT NULL,
  `poste_technique` varchar(100) DEFAULT NULL,
  `designation_poste_technique` varchar(255) DEFAULT NULL,
  `poste_travail_principal` varchar(100) DEFAULT NULL,
  `categorie_equipement` varchar(100) DEFAULT NULL,
  `centre_de_couts` varchar(100) DEFAULT NULL,
  `date_creation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `nomenclatures`
--

CREATE TABLE `nomenclatures` (
  `id` int(11) NOT NULL,
  `code_equipement` varchar(50) DEFAULT NULL,
  `code_article` varchar(50) DEFAULT NULL,
  `repere_equipement` varchar(100) DEFAULT NULL,
  `designation_equipement` varchar(255) DEFAULT NULL,
  `fabricant` varchar(255) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `numero_serie_fabricant` varchar(100) DEFAULT NULL,
  `designation_article` varchar(255) DEFAULT NULL,
  `numero_poste` varchar(100) DEFAULT NULL,
  `quantite` int(11) DEFAULT NULL,
  `unite` varchar(50) DEFAULT NULL,
  `poste_technique` varchar(100) DEFAULT NULL,
  `metier` varchar(100) DEFAULT NULL,
  `date_creation` date DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_article` (`code_article`);

--
-- Index pour la table `equipements`
--
ALTER TABLE `equipements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_equipement` (`code_equipement`);

--
-- Index pour la table `nomenclatures`
--
ALTER TABLE `nomenclatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_equipement` (`code_equipement`),
  ADD KEY `code_article` (`code_article`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `equipements`
--
ALTER TABLE `equipements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `nomenclatures`
--
ALTER TABLE `nomenclatures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `nomenclatures`
--
ALTER TABLE `nomenclatures`
  ADD CONSTRAINT `nomenclatures_ibfk_1` FOREIGN KEY (`code_equipement`) REFERENCES `equipements` (`code_equipement`) ON DELETE CASCADE,
  ADD CONSTRAINT `nomenclatures_ibfk_2` FOREIGN KEY (`code_article`) REFERENCES `articles` (`code_article`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
