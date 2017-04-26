-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mer 26 Avril 2017 à 11:35
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `lo07`
--

-- --------------------------------------------------------

--
-- Structure de la table `cursus`
--

CREATE TABLE IF NOT EXISTS `cursus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_etudiant` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `cursus_elements`
--

CREATE TABLE IF NOT EXISTS `cursus_elements` (
  `id_cursus` int(11) NOT NULL,
  `id_element` int(11) NOT NULL,
  PRIMARY KEY (`id_cursus`,`id_element`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `element`
--

CREATE TABLE IF NOT EXISTS `element` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cursus` int(10) unsigned NOT NULL,
  `sem_seq` int(11) NOT NULL,
  `sem_label` varchar(50) COLLATE utf8_bin NOT NULL,
  `sigle` varchar(50) COLLATE utf8_bin NOT NULL,
  `categorie` varchar(50) COLLATE utf8_bin NOT NULL,
  `affectation` varchar(50) COLLATE utf8_bin NOT NULL,
  `utt` varchar(10) COLLATE utf8_bin NOT NULL,
  `profil` varchar(10) COLLATE utf8_bin NOT NULL,
  `credit` int(11) NOT NULL,
  `resultat` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE IF NOT EXISTS `etudiant` (
  `numero` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8_bin NOT NULL,
  `prenom` varchar(255) COLLATE utf8_bin NOT NULL,
  `admission` varchar(50) COLLATE utf8_bin NOT NULL,
  `filiere` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
