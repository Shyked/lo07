-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2017 at 09:03 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lo07`
--

-- --------------------------------------------------------

--
-- Table structure for table `cursus`
--

CREATE TABLE IF NOT EXISTS `cursus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_etudiant` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=8 ;

--
-- Dumping data for table `cursus`
--

INSERT INTO `cursus` (`id`, `numero_etudiant`, `nom`) VALUES
(1, 39959, 'Mon cursus'),
(2, 39961, 'Mon cursus'),
(5, 39959, 'CURSUUUUUUUUS');

-- --------------------------------------------------------

--
-- Table structure for table `cursus_element`
--

CREATE TABLE IF NOT EXISTS `cursus_element` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_cursus` int(11) NOT NULL,
  `id_element` int(11) NOT NULL,
  `sem_seq` int(10) unsigned NOT NULL,
  `sem_label` varchar(50) COLLATE utf8_bin NOT NULL,
  `profil` tinyint(1) NOT NULL,
  `credit` int(11) NOT NULL,
  `resultat` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=8 ;


-- --------------------------------------------------------

--
-- Table structure for table `element`
--

CREATE TABLE IF NOT EXISTS `element` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sigle` varchar(50) COLLATE utf8_bin NOT NULL,
  `categorie` varchar(50) COLLATE utf8_bin NOT NULL,
  `affectation` varchar(50) COLLATE utf8_bin NOT NULL,
  `utt` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=19 ;


-- --------------------------------------------------------

--
-- Table structure for table `etudiant`
--

CREATE TABLE IF NOT EXISTS `etudiant` (
  `numero` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8_bin NOT NULL,
  `prenom` varchar(255) COLLATE utf8_bin NOT NULL,
  `admission` varchar(50) COLLATE utf8_bin NOT NULL,
  `filiere` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- --------------------------------------------------------

--
-- Table structure for table `reglement`
--

CREATE TABLE IF NOT EXISTS `reglement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=9 ;


-- --------------------------------------------------------

--
-- Table structure for table `reglement_element`
--

CREATE TABLE IF NOT EXISTS `reglement_element` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_reglement` varchar(255) COLLATE utf8_bin NOT NULL,
  `id_regle` varchar(50) COLLATE utf8_bin NOT NULL,
  `agregat` varchar(50) COLLATE utf8_bin NOT NULL,
  `categorie` varchar(100) COLLATE utf8_bin NOT NULL,
  `affectation` varchar(100) COLLATE utf8_bin NOT NULL,
  `credit` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=54 ;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
