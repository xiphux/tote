SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE `conferences` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `conference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `abbreviation` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `divisions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `division` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `conference_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `conference_id` (`conference_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` smallint(5) unsigned NOT NULL,
  `week` smallint(5) unsigned NOT NULL,
  `home_team_id` smallint(5) unsigned NOT NULL,
  `away_team_id` smallint(5) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `home_score` smallint(5) unsigned DEFAULT NULL,
  `away_score` smallint(5) unsigned DEFAULT NULL,
  `favorite_id` smallint(5) unsigned DEFAULT NULL,
  `point_spread` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `home_team_id` (`home_team_id`),
  KEY `away_team_id` (`away_team_id`),
  KEY `favorite_id` (`favorite_id`),
  KEY `season_week` (`season_id`,`week`,`start`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `migrations` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `version` int(10) unsigned NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pools` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` smallint(5) unsigned NOT NULL,
  `fee` float DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `record_last_materialized` datetime DEFAULT NULL,
  `record_needs_materialize` tinyint(1) NOT NULL,
  `record_next_materialize` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pool_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pool_id` int(10) unsigned NOT NULL,
  `action` smallint(5) unsigned NOT NULL,
  `time` datetime NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `admin_username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `week` smallint(5) unsigned DEFAULT NULL,
  `team_id` smallint(5) unsigned DEFAULT NULL,
  `old_team_id` smallint(5) unsigned DEFAULT NULL,
  `admin_type` smallint(5) unsigned DEFAULT NULL,
  `old_admin_type` smallint(5) unsigned DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `admin_id` (`admin_id`),
  KEY `team_id` (`team_id`),
  KEY `old_team_id` (`old_team_id`),
  KEY `pool_id` (`pool_id`,`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pool_administrators` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pool_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_type` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pool_id` (`pool_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pool_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pool_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pool_id` (`pool_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pool_entry_picks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pool_entry_id` int(10) unsigned NOT NULL,
  `week` smallint(5) unsigned NOT NULL,
  `team_id` smallint(5) unsigned NOT NULL,
  `placed` datetime DEFAULT NULL,
  `edited` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pool_entry_week` (`pool_entry_id`,`week`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pool_payouts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pool_id` int(10) unsigned NOT NULL,
  `minimum` smallint(5) unsigned DEFAULT NULL,
  `maximum` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pool_id` (`pool_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pool_payout_percents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payout_id` int(10) unsigned NOT NULL,
  `place` smallint(5) unsigned NOT NULL,
  `percent` decimal(3,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payout_id` (`payout_id`,`place`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pool_records` (
  `pool_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `week` smallint(5) unsigned NOT NULL,
  `team_id` smallint(5) unsigned NOT NULL,
  `game_id` int(10) unsigned NOT NULL,
  `win` smallint(5) unsigned NOT NULL,
  `loss` smallint(5) unsigned NOT NULL,
  `tie` smallint(5) unsigned NOT NULL,
  `spread` smallint(6) DEFAULT NULL,
  `open` tinyint(1) NOT NULL,
  UNIQUE KEY `id` (`pool_id`,`user_id`,`week`),
  KEY `pool_id` (`pool_id`),
  KEY `user_id` (`user_id`),
  KEY `team_id` (`team_id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `seasons` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `year` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `teams` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `team` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `home` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `abbreviation` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `division_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abbreviation` (`abbreviation`),
  KEY `division_id` (`division_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `recovery_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recovery_key_expiration` datetime DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role` smallint(5) unsigned NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_password_change` datetime DEFAULT NULL,
  `reminder` tinyint(1) NOT NULL DEFAULT '0',
  `reminder_time` int(10) unsigned DEFAULT NULL,
  `last_reminder` datetime DEFAULT NULL,
  `result_notification` tinyint(1) NOT NULL DEFAULT '0',
  `timezone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `style` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE VIEW `season_weeks_view` AS select distinct `games`.`season_id` AS `season_id`,`games`.`week` AS `week` from `games`;

CREATE VIEW `pool_records_view` AS select `pools`.`id` AS `pool_id`,`pool_entries`.`user_id` AS `user_id`,`season_weeks_view`.`week` AS `week`,`pool_entry_picks`.`team_id` AS `team_id`,`games`.`id` AS `game_id`,((`pool_entry_picks`.`team_id` is not null) and (`games`.`home_score` is not null) and (`games`.`away_score` is not null) and (((`pool_entry_picks`.`team_id` = `games`.`home_team_id`) and (`games`.`home_score` > `games`.`away_score`)) or ((`pool_entry_picks`.`team_id` = `games`.`away_team_id`) and (`games`.`away_score` > `games`.`home_score`)))) AS `win`,(((`pool_entry_picks`.`team_id` is not null) and (`games`.`home_score` is not null) and (`games`.`away_score` is not null) and (((`pool_entry_picks`.`team_id` = `games`.`home_team_id`) and (`games`.`home_score` < `games`.`away_score`)) or ((`pool_entry_picks`.`team_id` = `games`.`away_team_id`) and (`games`.`away_score` < `games`.`home_score`)))) or (isnull(`pool_entry_picks`.`team_id`) and (not(`season_weeks_view`.`week` in (select distinct `opengames`.`week` from `games` `opengames` where ((`opengames`.`season_id` = `pools`.`season_id`) and (`opengames`.`start` > utc_timestamp()))))))) AS `loss`,((`pool_entry_picks`.`team_id` is not null) and (`games`.`home_score` is not null) and (`games`.`away_score` is not null) and (`games`.`home_score` = `games`.`away_score`)) AS `tie`,(case when ((`pool_entry_picks`.`team_id` is not null) and (`games`.`home_score` is not null) and (`games`.`away_score` is not null) and (`pool_entry_picks`.`team_id` = `games`.`home_team_id`)) then (cast(`games`.`home_score` as signed) - cast(`games`.`away_score` as signed)) when ((`pool_entry_picks`.`team_id` is not null) and (`games`.`home_score` is not null) and (`games`.`away_score` is not null) and (`pool_entry_picks`.`team_id` = `games`.`away_team_id`)) then (cast(`games`.`away_score` as signed) - cast(`games`.`home_score` as signed)) when (isnull(`pool_entry_picks`.`team_id`) and (`season_weeks_view`.`week` > ((select max(`spreadgames`.`week`) from `games` `spreadgames` where (`spreadgames`.`season_id` = `pools`.`season_id`)) - 4)) and (not(`season_weeks_view`.`week` in (select distinct `opengames`.`week` from `games` `opengames` where ((`opengames`.`season_id` = `pools`.`season_id`) and (`opengames`.`start` > utc_timestamp())))))) then -(10) else NULL end) AS `spread`,coalesce((`season_weeks_view`.`week` >= (select min(`opengames`.`week`) from `games` `opengames` where ((`opengames`.`season_id` = `pools`.`season_id`) and (`opengames`.`start` > utc_timestamp())))),0) AS `open` from ((((`pools` join `season_weeks_view` on((`pools`.`season_id` = `season_weeks_view`.`season_id`))) join `pool_entries` on((`pool_entries`.`pool_id` = `pools`.`id`))) left join `pool_entry_picks` on(((`pool_entries`.`id` = `pool_entry_picks`.`pool_entry_id`) and (`season_weeks_view`.`week` = `pool_entry_picks`.`week`)))) left join `games` on(((`games`.`season_id` = `season_weeks_view`.`season_id`) and (`games`.`week` = `season_weeks_view`.`week`) and ((`pool_entry_picks`.`team_id` = `games`.`away_team_id`) or (`pool_entry_picks`.`team_id` = `games`.`home_team_id`)))));


ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`);

ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`),
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `games_ibfk_3` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `games_ibfk_4` FOREIGN KEY (`favorite_id`) REFERENCES `teams` (`id`);

ALTER TABLE `pools`
  ADD CONSTRAINT `pools_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`);

ALTER TABLE `pool_actions`
  ADD CONSTRAINT `pool_actions_ibfk_1` FOREIGN KEY (`pool_id`) REFERENCES `pools` (`id`),
  ADD CONSTRAINT `pool_actions_ibfk_4` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `pool_actions_ibfk_5` FOREIGN KEY (`old_team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `pool_actions_ibfk_6` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pool_actions_ibfk_7` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `pool_administrators`
  ADD CONSTRAINT `pool_administrators_ibfk_1` FOREIGN KEY (`pool_id`) REFERENCES `pools` (`id`),
  ADD CONSTRAINT `pool_administrators_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `pool_entries`
  ADD CONSTRAINT `pool_entries_ibfk_1` FOREIGN KEY (`pool_id`) REFERENCES `pools` (`id`),
  ADD CONSTRAINT `pool_entries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `pool_entry_picks`
  ADD CONSTRAINT `pool_entry_picks_ibfk_1` FOREIGN KEY (`pool_entry_id`) REFERENCES `pool_entries` (`id`),
  ADD CONSTRAINT `pool_entry_picks_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

ALTER TABLE `pool_payouts`
  ADD CONSTRAINT `pool_payouts_ibfk_1` FOREIGN KEY (`pool_id`) REFERENCES `pools` (`id`);

ALTER TABLE `pool_payout_percents`
  ADD CONSTRAINT `pool_payout_percents_ibfk_1` FOREIGN KEY (`payout_id`) REFERENCES `pool_payouts` (`id`);

ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
