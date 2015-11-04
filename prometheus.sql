-- phpMyAdmin SQL Dump
-- version 4.5.0.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 04. Nov 2015 um 16:14
-- Server-Version: 5.5.46-0+deb7u1
-- PHP-Version: 5.6.15-1~dotdeb+zts+7.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `prometheus`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dedicated`
--

CREATE TABLE `dedicated` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `port` int(6) NOT NULL,
  `user` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `dedicated`
--

INSERT INTO `dedicated` (`id`, `name`, `ip`, `port`, `user`, `password`, `status`) VALUES
(1, 'Test', '127.0.0.1', 22, 'root', 'root', 0),
(2, 'Test', '127.0.0.1', 22, '11', '11', 0),
(3, 'Test', '127.0.0.1', 22, 'rr', 'rr', 0),
(4, 'Test', '127.0.0.1', 22, 'prom', '22', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rank` int(11) NOT NULL DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `rank`) VALUES
(17, 'Test', '123@123.de', '$2y$10$J15ufgFaMcksKAMxd73O.e4hRxySkQoKN3hOoe9abM29uRET0Zcqu', 1);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `dedicated`
--
ALTER TABLE `dedicated`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `dedicated`
--
ALTER TABLE `dedicated`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
