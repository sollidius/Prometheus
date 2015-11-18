SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `dedicated` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `os` varchar(50) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `port` int(6) NOT NULL,
  `user` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `language` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dedicated_games` (
  `id` int(11) NOT NULL,
  `dedi_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `status` int(5) NOT NULL,
  `status_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `gameservers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dedi_id` int(15) NOT NULL DEFAULT '0',
  `game` varchar(50) NOT NULL,
  `slots` int(2) NOT NULL,
  `map` varchar(30) NOT NULL,
  `parameter` varchar(150) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `port` varchar(6) NOT NULL,
  `gs_login` varchar(25) NOT NULL,
  `gs_password` varchar(50) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `status_update` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `dedicated_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(25) NOT NULL,
  `type_id` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `name_internal` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rank` int(11) NOT NULL DEFAULT '2',
  `u_count` int(3) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `dedicated`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `ip` (`ip`);

ALTER TABLE `dedicated_games`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `gameservers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `dedicated`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
ALTER TABLE `dedicated_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
ALTER TABLE `gameservers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
