-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  Dim 25 fév. 2018 à 12:34
-- Version du serveur :  10.1.30-MariaDB
-- Version de PHP :  7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `brevets`
--

-- --------------------------------------------------------

--
-- Structure de la table `veillee_autologin`
--

CREATE TABLE `veillee_autologin` (
  `auto_id` mediumint(8) UNSIGNED NOT NULL,
  `user_fk` smallint(5) UNSIGNED NOT NULL,
  `hash` char(32) NOT NULL,
  `expires` int(10) UNSIGNED NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `veillee_games`
--

CREATE TABLE `veillee_games` (
  `game_id` smallint(6) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stop_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL,
  `bonus` smallint(6) NOT NULL DEFAULT '0',
  `video` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `veillee_history`
--

CREATE TABLE `veillee_history` (
  `history_id` smallint(6) NOT NULL,
  `player_id` smallint(6) NOT NULL,
  `mission_id` smallint(6) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stop_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `result` tinyint(1) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `veillee_missions`
--

CREATE TABLE `veillee_missions` (
  `mission_id` smallint(6) NOT NULL,
  `map_point` smallint(6) NOT NULL,
  `type` smallint(6) NOT NULL,
  `sameref` varchar(10) NOT NULL,
  `busy` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `veillee_players`
--

CREATE TABLE `veillee_players` (
  `player_id` smallint(6) NOT NULL,
  `fk_user_id` smallint(6) NOT NULL,
  `game_id` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `veillee_users`
--

CREATE TABLE `veillee_users` (
  `user_id` smallint(5) UNSIGNED NOT NULL,
  `login` varchar(20) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(20) NOT NULL,
  `password` char(32) CHARACTER SET ascii NOT NULL,
  `permissions` smallint(5) UNSIGNED NOT NULL,
  `password_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `veillee_autologin`
--
ALTER TABLE `veillee_autologin`
  ADD PRIMARY KEY (`auto_id`);

--
-- Index pour la table `veillee_games`
--
ALTER TABLE `veillee_games`
  ADD PRIMARY KEY (`game_id`);

--
-- Index pour la table `veillee_history`
--
ALTER TABLE `veillee_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Index pour la table `veillee_missions`
--
ALTER TABLE `veillee_missions`
  ADD PRIMARY KEY (`mission_id`);

--
-- Index pour la table `veillee_players`
--
ALTER TABLE `veillee_players`
  ADD PRIMARY KEY (`player_id`);

--
-- Index pour la table `veillee_users`
--
ALTER TABLE `veillee_users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `veillee_autologin`
--
ALTER TABLE `veillee_autologin`
  MODIFY `auto_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT pour la table `veillee_games`
--
ALTER TABLE `veillee_games`
  MODIFY `game_id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `veillee_history`
--
ALTER TABLE `veillee_history`
  MODIFY `history_id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT pour la table `veillee_missions`
--
ALTER TABLE `veillee_missions`
  MODIFY `mission_id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `veillee_players`
--
ALTER TABLE `veillee_players`
  MODIFY `player_id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `veillee_users`
--
ALTER TABLE `veillee_users`
  MODIFY `user_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
