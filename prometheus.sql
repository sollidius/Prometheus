SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `addons` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `folder` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `addons` (`id`, `game_id`, `name`, `url`, `path`, `folder`) VALUES
(1, 2, 'ULIB_2.52', 'http://ulyssesmod.net/archive/ULib/ULib-v2_52.zip', 'game/garrysmod/addons', 'ulib'),
(2, 2, 'ULX_3.62', 'http://ulyssesmod.net/archive/ulx/ulx-v3_62.zip', 'game/garrysmod/addons', 'ulx');

CREATE TABLE `addons_installed` (
  `id` int(11) NOT NULL,
  `dedi_id` int(11) NOT NULL,
  `addons_id` int(11) NOT NULL,
  `gs_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `status_text` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `blacklist` (
  `id` int(11) NOT NULL,
  `ip_remote` varchar(255) NOT NULL,
  `ip_forward` varchar(255) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  `timestamp_expires` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dedicated` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `os` varchar(50) NOT NULL,
  `os_bit` int(2) NOT NULL DEFAULT '64',
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

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `type` int(2) NOT NULL,
  `message` text NOT NULL,
  `timestamp` int(11) NOT NULL
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
  `status_update` int(1) NOT NULL DEFAULT '0',
  `running` int(1) NOT NULL DEFAULT '0',
  `is_running` int(1) NOT NULL DEFAULT '0',
  `parameters_active` int(1) NOT NULL DEFAULT '0',
  `deadline` int(11) NOT NULL,
  `player_online` int(2) NOT NULL DEFAULT '0',
  `restart` int(1) NOT NULL DEFAULT '0',
  `restart_time` int(2) NOT NULL DEFAULT '6'
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
  `type_name` varchar(255) NOT NULL,
  `gameq` varchar(50) NOT NULL,
  `map_path` varchar(255) NOT NULL,
  `app_set_config` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `templates` (`id`, `name`, `name_internal`, `type`, `type_name`, `gameq`, `map_path`, `app_set_config`) VALUES
(2, 'Garrysmod', 'garrysmod', 'steamcmd', '4020', 'gmod', 'garrysmod', ''),
(7, 'CounterStrikeSource', 'cstrike', 'steamcmd', '232330', 'css', '', ''),
(9, 'CounterStrikeGlobalOffensive', 'csgo', 'steamcmd', '740', 'csgo', 'csgo', ''),
(10, 'TeamFortress2', 'tf', 'steamcmd', '232250', 'tf2', '', ''),
(14, 'Left4Dead2', 'left4dead2', 'steamcmd', '222860', 'l4d2', '', ''),
(20, 'Left4Dead', 'left4dead', 'steamcmd', '222840', 'l4d', '', ''),
(21, 'DayofDefeatSource', 'dod', 'steamcmd', '232290', 'dods', '', ''),
(31, 'MinecraftVanilla-1.8.9', 'java -jar minecraft_server.jar', 'image', 'http://it.ath.pw/images/minecraft_server1.8.9.zip', 'minecraft', '', ''),
(32, 'TeamFortressClassic', 'tfc', 'steamcmd', '90', '', '', 'tfc'),
(33, 'DayofDefeat', 'dod', 'steamcmd', '90', '', '', 'dod'),
(34, 'CounterStrikeConditionZero', 'cstrike', 'steamcmd', '90', '', '', 'czero'),
(35, 'Half-Life', 'hl', 'steamcmd', '90', '', '', ''),
(36, 'Half-LifeDeathmatchSource', 'hldm', 'steamcmd', '255470', '', '', ''),
(37, 'Half-Life2Deathmatch', 'hl2dm', 'steamcmd', '232370', '', '', ''),
(38, 'Counter-Strike', 'cs', 'steamcmd', '90', '', '', '');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rank` int(11) NOT NULL DEFAULT '2',
  `u_count` int(3) NOT NULL DEFAULT '1',
  `language` varchar(2) NOT NULL DEFAULT 'de',
  `session_strict` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `wi_settings` (
  `id` int(1) NOT NULL,
  `header_txt` varchar(255) NOT NULL,
  `log_gs_cleanup` int(1) NOT NULL DEFAULT '1',
  `wi_maintance` int(1) NOT NULL DEFAULT '0',
  `cronjob_lastrun` int(11) NOT NULL,
  `gs_check_crash` int(1) NOT NULL DEFAULT '1',
  `gs_check_cpu` int(11) NOT NULL DEFAULT '1',
  `gs_check_cpu_msg` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `wi_settings` (`id`, `header_txt`, `log_gs_cleanup`, `wi_maintance`, `cronjob_lastrun`, `gs_check_crash`, `gs_check_cpu`, `gs_check_cpu_msg`) VALUES
(1, 'Prometheus', 1, 0, 1449846002, 1, 1, 1);


ALTER TABLE `addons`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `addons_installed`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dedicated`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `ip` (`ip`);

ALTER TABLE `dedicated_games`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `events`
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

ALTER TABLE `wi_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);


ALTER TABLE `addons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `addons_installed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `dedicated`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `dedicated_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gameservers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
