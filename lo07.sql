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

--
-- Dumping data for table `cursus_element`
--

INSERT INTO `cursus_element` (`id`, `id_cursus`, `id_element`, `sem_seq`, `sem_label`, `profil`, `credit`, `resultat`) VALUES
(1, 5, 18, 4, 'ISI4', 1, 6, 'A'),
(2, 5, 16, 4, 'ISI4', 1, 6, 'ADM'),
(3, 5, 18, 3, 'ISI3', 1, 6, 'B'),
(4, 5, 9, 2, 'ISI2', 0, 1, 'B'),
(5, 5, 13, 4, 'ISI4', 0, 0, 'F');

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

--
-- Dumping data for table `element`
--

INSERT INTO `element` (`id`, `sigle`, `categorie`, `affectation`, `utt`) VALUES
(8, 'CS', 'CS', 'TC', 1),
(9, 'TM', 'TM', 'TC', 1),
(10, 'EC', 'EC', 'TC', 1),
(11, 'HT', 'HT', 'TC', 1),
(12, 'ME', 'ME', 'TC', 1),
(13, 'ST', 'ST', 'TC', 0),
(14, 'SE', 'SE', 'TC', 1),
(15, 'HP', 'HP', 'TC', 1),
(16, 'NPML', 'NPML', 'TC', 1),
(18, 'LO07', 'TM', 'TCBR', 1);

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

--
-- Dumping data for table `etudiant`
--

INSERT INTO `etudiant` (`numero`, `nom`, `prenom`, `admission`, `filiere`) VALUES
(10000, 'oaizbixba', 'ozoxb', 'TC', 'LIB'),
(12546, 'PRIOR', 'beatrice', 'BR', 'MSI'),
(39958, 'avant', 'Juste', 'BR', 'MPL'),
(39959, 'Haingue', 'Rémy', 'BR', 'MPL'),
(39961, 'après', 'Juste', 'BR', 'MPL'),
(40000, 'Haingue', 'Rémy', 'BR', 'MPL'),
(98761, 'arzord', 'ubiubazcrvzdz', 'TC', 'MSI'),
(98762, 'arzordzd', 'ubiubazcrvzdz', 'BR', 'MSI'),
(98763, 'arzordzd', 'ubi', 'TC', 'MSI'),
(98765, 'AAAA', 'ubiub', 'TC', 'MRI'),
(99999, 'et mon nom aussi est plutôt long d''ailleurs', 'Mon prénom est vraiment très très long', 'TC', '?');

-- --------------------------------------------------------

--
-- Table structure for table `reglement`
--

CREATE TABLE IF NOT EXISTS `reglement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=9 ;

--
-- Dumping data for table `reglement`
--

INSERT INTO `reglement` (`id`, `nom`) VALUES
(1, 'Premier règlement'),
(8, 'R_ACTUEL_BR');

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

--
-- Dumping data for table `reglement_element`
--

INSERT INTO `reglement_element` (`id`, `id_reglement`, `id_regle`, `agregat`, `categorie`, `affectation`, `credit`) VALUES
(1, '1', 'R01', 'SUM', 'CS+TM', 'TCBR', 54),
(41, '8', 'R01', 'SUM', 'CS+TM', 'TCBR', 54),
(42, '8', 'R02', 'SUM', 'CS+TM', 'FCBR', 30),
(43, '8', 'R03', 'SUM', 'CS', 'BR', 30),
(44, '8', 'R04', 'SUM', 'TM', 'BR', 30),
(45, '8', 'R05', 'SUM', 'ST', 'TCBR', 30),
(46, '8', 'R06', 'SUM', 'ST', 'FCBR', 30),
(47, '8', 'R07', 'SUM', 'EC', 'BR', 12),
(48, '8', 'R08', 'SUM', 'ME', 'BR', 4),
(49, '8', 'R09', 'SUM', 'CT', 'BR', 4),
(50, '8', 'R10', 'SUM', 'ME+CT', 'BR', 16),
(51, '8', 'R11', 'SUM', 'UTT(CS+TM)', 'BR', 0),
(52, '8', 'R12', 'EXIST', 'SE', 'UTT', 0),
(53, '8', 'R13', 'EXIST', 'NPML', 'UTT', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
