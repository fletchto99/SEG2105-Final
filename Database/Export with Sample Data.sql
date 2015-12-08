-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.44-MariaDB-log - FreeBSD Ports
-- Server OS:                    FreeBSD10.1
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table mlanglois_SEG2105_Final.Goals
CREATE TABLE IF NOT EXISTS `Goals` (
  `Goal_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Player_ID` int(10) unsigned NOT NULL,
  `Assist_ID` int(10) unsigned DEFAULT NULL,
  `Match_ID` int(10) unsigned NOT NULL,
  `Team_ID` int(10) unsigned NOT NULL,
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Goal_ID`),
  KEY `Player_ID` (`Player_ID`),
  KEY `Assist_ID` (`Assist_ID`),
  KEY `Match_ID` (`Match_ID`),
  KEY `Team_ID` (`Team_ID`),
  CONSTRAINT `Goals_ibfk_4` FOREIGN KEY (`Team_ID`) REFERENCES `Teams` (`Team_ID`),
  CONSTRAINT `Goals_ibfk_1` FOREIGN KEY (`Player_ID`) REFERENCES `Persons` (`Person_ID`),
  CONSTRAINT `Goals_ibfk_2` FOREIGN KEY (`Assist_ID`) REFERENCES `Persons` (`Person_ID`),
  CONSTRAINT `Goals_ibfk_3` FOREIGN KEY (`Match_ID`) REFERENCES `Matches` (`Match_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.Goals: ~32 rows (approximately)
/*!40000 ALTER TABLE `Goals` DISABLE KEYS */;
INSERT INTO `Goals` (`Goal_ID`, `Player_ID`, `Assist_ID`, `Match_ID`, `Team_ID`, `Last_Update_Datetime`) VALUES
	(1, 3, NULL, 1, 2, '2015-12-05 17:44:36'),
	(2, 5, NULL, 2, 3, '2015-12-05 17:45:12'),
	(3, 2, 4, 3, 1, '2015-12-05 17:45:21'),
	(4, 5, NULL, 4, 3, '2015-12-05 17:45:32'),
	(5, 4, 2, 5, 1, '2015-12-05 17:45:42'),
	(6, 7, NULL, 6, 5, '2015-12-05 17:47:39'),
	(7, 2, NULL, 7, 1, '2015-12-05 17:47:47'),
	(8, 6, NULL, 8, 4, '2015-12-05 17:47:52'),
	(9, 2, NULL, 9, 1, '2015-12-05 17:47:59'),
	(10, 6, NULL, 10, 4, '2015-12-05 17:48:05'),
	(11, 3, NULL, 12, 2, '2015-12-05 17:48:14'),
	(12, 6, NULL, 13, 4, '2015-12-05 17:48:20'),
	(13, 3, NULL, 11, 2, '2015-12-05 17:48:29'),
	(14, 3, NULL, 14, 2, '2015-12-05 17:49:37'),
	(15, 6, NULL, 15, 4, '2015-12-05 17:49:43'),
	(16, 4, NULL, 16, 1, '2015-12-05 17:49:58'),
	(17, 3, NULL, 17, 2, '2015-12-05 17:50:06'),
	(18, 4, NULL, 18, 1, '2015-12-05 17:50:18'),
	(19, 7, NULL, 19, 5, '2015-12-05 17:50:29'),
	(20, 4, NULL, 20, 1, '2015-12-05 17:50:39'),
	(21, 6, NULL, 21, 4, '2015-12-05 17:50:46'),
	(22, 2, NULL, 22, 1, '2015-12-05 17:50:52'),
	(23, 6, NULL, 23, 4, '2015-12-05 17:50:59'),
	(24, 4, NULL, 25, 1, '2015-12-05 17:52:40'),
	(25, 3, NULL, 26, 2, '2015-12-05 17:52:46'),
	(26, 4, NULL, 24, 1, '2015-12-05 17:52:54'),
	(27, 8, 4, 28, 1, '2015-12-05 22:26:49'),
	(28, 5, NULL, 29, 3, '2015-12-05 22:26:53'),
	(29, 4, 2, 27, 1, '2015-12-05 22:27:02'),
	(30, 9, NULL, 36, 8, '2015-12-06 15:56:51'),
	(31, 4, 2, 37, 1, '2015-12-06 15:57:09'),
	(32, 4, NULL, 38, 1, '2015-12-06 15:57:25');
/*!40000 ALTER TABLE `Goals` ENABLE KEYS */;


-- Dumping structure for table mlanglois_SEG2105_Final.Logins
CREATE TABLE IF NOT EXISTS `Logins` (
  `Login_ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Salt` varchar(64) NOT NULL,
  `Person_ID` int(11) unsigned NOT NULL,
  `Last_Update_Date_Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Login_ID`),
  UNIQUE KEY `Username` (`Username`),
  UNIQUE KEY `Person_ID_UQ` (`Person_ID`),
  KEY `Person_ID` (`Person_ID`),
  CONSTRAINT `Logins_ibfk` FOREIGN KEY (`Person_ID`) REFERENCES `Persons` (`Person_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- Dumping data for table mlanglois_SEG2105_Final.Logins: ~3 rows (approximately)
/*!40000 ALTER TABLE `Logins` DISABLE KEYS */;
INSERT INTO `Logins` (`Login_ID`, `Username`, `Password`, `Salt`, `Person_ID`, `Last_Update_Date_Time`) VALUES
	(1, 'Matthew', '9544246453a517107bee423cb834ffa0e8e38559dd57407980651b2f036b0891', '62fdce3778f89d81cd8b2c6381634bbd2b895b97c9af26db2a6a96fb78c97a92', 1, '2015-12-05 01:08:15'),
	(2, 'TestPlayer', '27727c58fd39cac3c6dfa6f2ec9401225b01583fcb931e6bbe3b4fe5cea6d967', 'f9d1f3cfd62c799fafd709058eed695243fe1caaa56f0ccd8ea4364d5bb07f67', 8, '2015-12-05 18:23:59'),
	(3, 'Demouser', '8e580b3aae0b9439794544dfe28d8a53470988dd4148bc5e17522e44c2d486c9', '06334ed843c8d33017d6a39d23afb443e00ee7325a993239f56df1edaf50efe1', 11, '2015-12-07 22:27:49');
/*!40000 ALTER TABLE `Logins` ENABLE KEYS */;


-- Dumping structure for table mlanglois_SEG2105_Final.Matches
CREATE TABLE IF NOT EXISTS `Matches` (
  `Match_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Tournament_ID` int(10) unsigned NOT NULL,
  `Team_A_ID` int(10) unsigned DEFAULT NULL,
  `Team_B_ID` int(10) unsigned DEFAULT NULL,
  `Winning_Team_ID` int(10) unsigned DEFAULT NULL,
  `Next_Match_ID` int(11) unsigned DEFAULT NULL,
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int(11) NOT NULL DEFAULT '0',
  `Round` int(10) DEFAULT NULL,
  PRIMARY KEY (`Match_ID`),
  KEY `Team_A_ID` (`Team_A_ID`),
  KEY `Team_B_ID` (`Team_B_ID`),
  KEY `Next_Match_ID` (`Next_Match_ID`),
  KEY `Winning_Team_ID` (`Winning_Team_ID`),
  KEY `Matches_ibfk_1` (`Tournament_ID`),
  CONSTRAINT `Matches_ibfk_1` FOREIGN KEY (`Tournament_ID`) REFERENCES `Tournaments` (`Tournament_ID`),
  CONSTRAINT `Matches_ibfk_2` FOREIGN KEY (`Team_A_ID`) REFERENCES `Teams` (`Team_ID`),
  CONSTRAINT `Matches_ibfk_3` FOREIGN KEY (`Team_B_ID`) REFERENCES `Teams` (`Team_ID`),
  CONSTRAINT `Matches_ibfk_4` FOREIGN KEY (`Next_Match_ID`) REFERENCES `Matches` (`Match_ID`),
  CONSTRAINT `Matches_iffk_5` FOREIGN KEY (`Winning_Team_ID`) REFERENCES `Teams` (`Team_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.Matches: ~38 rows (approximately)
/*!40000 ALTER TABLE `Matches` DISABLE KEYS */;
INSERT INTO `Matches` (`Match_ID`, `Tournament_ID`, `Team_A_ID`, `Team_B_ID`, `Winning_Team_ID`, `Next_Match_ID`, `Last_Update_Datetime`, `Status`, `Round`) VALUES
	(1, 1, 2, 5, 2, NULL, '2015-12-05 17:44:37', 2, NULL),
	(2, 1, 3, 4, 3, NULL, '2015-12-05 17:45:14', 2, NULL),
	(3, 1, 1, 5, 1, NULL, '2015-12-05 17:45:22', 2, NULL),
	(4, 1, 2, 3, 3, NULL, '2015-12-05 17:45:34', 2, NULL),
	(5, 1, 1, 4, 1, NULL, '2015-12-05 17:45:43', 2, NULL),
	(6, 1, 5, 3, 5, NULL, '2015-12-05 17:47:40', 2, NULL),
	(7, 1, 1, 3, 1, NULL, '2015-12-05 17:47:48', 2, NULL),
	(8, 1, 4, 2, 4, NULL, '2015-12-05 17:47:53', 2, NULL),
	(9, 1, 1, 2, 1, NULL, '2015-12-05 17:48:01', 2, NULL),
	(10, 1, 4, 5, 4, NULL, '2015-12-05 17:48:06', 2, NULL),
	(11, 1, 2, 4, 2, NULL, '2015-12-05 17:48:30', 2, 2),
	(12, 1, 2, 5, 2, 11, '2015-12-05 17:48:15', 2, 1),
	(13, 1, 4, 3, 4, 11, '2015-12-05 17:48:23', 2, 1),
	(14, 2, 2, 5, 2, NULL, '2015-12-05 17:49:44', 2, NULL),
	(15, 2, 3, 4, 4, NULL, '2015-12-05 17:49:46', 2, NULL),
	(16, 2, 1, 5, 1, NULL, '2015-12-05 17:50:08', 2, NULL),
	(17, 2, 2, 3, 2, NULL, '2015-12-05 17:50:09', 2, NULL),
	(18, 2, 1, 4, 1, NULL, '2015-12-05 17:50:21', 2, NULL),
	(19, 2, 5, 3, 5, NULL, '2015-12-05 17:50:31', 2, NULL),
	(20, 2, 1, 3, 1, NULL, '2015-12-05 17:50:41', 2, NULL),
	(21, 2, 4, 2, 4, NULL, '2015-12-05 17:50:47', 2, NULL),
	(22, 2, 1, 2, 1, NULL, '2015-12-05 17:50:53', 2, NULL),
	(23, 2, 4, 5, 4, NULL, '2015-12-05 17:52:30', 2, NULL),
	(24, 2, 1, 2, 1, NULL, '2015-12-05 17:52:55', 2, 2),
	(25, 2, 1, 4, 1, 24, '2015-12-05 17:52:41', 2, 1),
	(26, 2, 2, 5, 2, 24, '2015-12-05 17:52:47', 2, 1),
	(27, 3, 1, 3, 1, NULL, '2015-12-05 22:27:03', 2, 2),
	(28, 3, 1, 2, 1, 27, '2015-12-05 22:26:54', 2, 1),
	(29, 3, 3, 4, 3, 27, '2015-12-05 22:26:55', 2, 1),
	(30, 4, 1, 4, NULL, NULL, '2015-12-06 16:48:12', 1, NULL),
	(31, 4, 2, 3, NULL, NULL, '2015-12-06 03:06:08', 0, NULL),
	(32, 4, 1, 3, NULL, NULL, '2015-12-06 03:06:08', 0, NULL),
	(33, 4, 4, 2, NULL, NULL, '2015-12-06 03:06:08', 0, NULL),
	(34, 4, 1, 2, NULL, NULL, '2015-12-06 03:06:09', 0, NULL),
	(35, 4, 3, 4, NULL, NULL, '2015-12-06 03:06:09', 0, NULL),
	(36, 5, 3, 8, 8, NULL, '2015-12-06 15:56:58', 2, NULL),
	(37, 5, 1, 8, 1, NULL, '2015-12-06 15:57:11', 2, NULL),
	(38, 5, 1, 3, 1, NULL, '2015-12-06 15:57:29', 2, NULL);
/*!40000 ALTER TABLE `Matches` ENABLE KEYS */;


-- Dumping structure for table mlanglois_SEG2105_Final.Persons
CREATE TABLE IF NOT EXISTS `Persons` (
  `Person_ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `First_Name` varchar(30) NOT NULL DEFAULT '',
  `Last_Name` varchar(30) NOT NULL DEFAULT '',
  `Jersey_Number` int(2) DEFAULT NULL,
  `Person_Avatar` varchar(50) DEFAULT NULL,
  `Role_ID` int(10) unsigned NOT NULL,
  `Team_ID` int(10) unsigned DEFAULT NULL,
  `Last_Update_Date_Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Person_ID`),
  KEY `Role_ID` (`Role_ID`),
  KEY `Team_ID` (`Team_ID`),
  CONSTRAINT `Persons_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `Roles` (`Role_ID`),
  CONSTRAINT `Persons_ibfk_2` FOREIGN KEY (`Team_ID`) REFERENCES `Teams` (`Team_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

-- Dumping data for table mlanglois_SEG2105_Final.Persons: ~11 rows (approximately)
/*!40000 ALTER TABLE `Persons` DISABLE KEYS */;
INSERT INTO `Persons` (`Person_ID`, `First_Name`, `Last_Name`, `Jersey_Number`, `Person_Avatar`, `Role_ID`, `Team_ID`, `Last_Update_Date_Time`) VALUES
	(1, 'Matthew', 'Langlois', NULL, NULL, 1, NULL, '2015-12-05 01:08:43'),
	(2, 'Chris', 'Neil', 25, NULL, 2, 1, '2015-12-05 01:09:12'),
	(3, 'Sidney', 'Crosby', 87, NULL, 2, 2, '2015-12-05 01:14:04'),
	(4, 'Daniel', 'Alfredsson', 11, NULL, 2, 1, '2015-12-05 17:25:24'),
	(5, 'Jonathan', 'Towes', 19, NULL, 2, 3, '2015-12-05 17:39:44'),
	(6, 'Max', 'Pacioretty', 67, NULL, 2, 4, '2015-12-05 17:40:35'),
	(7, 'Henrik', 'Zetterberg', 40, NULL, 2, 5, '2015-12-05 17:41:58'),
	(8, 'Milan', 'Michalek', 9, NULL, 2, 1, '2015-12-05 18:35:04'),
	(9, 'hello', 'world', 123, NULL, 2, 8, '2015-12-06 15:55:30'),
	(10, 'player', 'test', 5, NULL, 2, NULL, '2015-12-06 16:53:02'),
	(11, 'test', 'test', NULL, NULL, 2, NULL, '2015-12-07 22:27:49');
/*!40000 ALTER TABLE `Persons` ENABLE KEYS */;


-- Dumping structure for table mlanglois_SEG2105_Final.Roles
CREATE TABLE IF NOT EXISTS `Roles` (
  `Role_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Role_Name` varchar(32) NOT NULL,
  `Role_Description` varchar(255) NOT NULL,
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Role_ID`),
  UNIQUE KEY `Role_Name` (`Role_Name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.Roles: ~2 rows (approximately)
/*!40000 ALTER TABLE `Roles` DISABLE KEYS */;
INSERT INTO `Roles` (`Role_ID`, `Role_Name`, `Role_Description`, `Last_Update_Datetime`) VALUES
	(1, 'Organizer', 'Organizes tournaments', '2015-11-07 14:54:36'),
	(2, 'Player', 'Participates in tournaments', '2015-11-07 14:54:55');
/*!40000 ALTER TABLE `Roles` ENABLE KEYS */;


-- Dumping structure for table mlanglois_SEG2105_Final.Teams
CREATE TABLE IF NOT EXISTS `Teams` (
  `Team_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Team_Name` varchar(50) NOT NULL,
  `Team_Avatar` varchar(50) DEFAULT NULL,
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Captain_ID` int(10) unsigned DEFAULT NULL,
  `Deleted` bit(11) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`Team_ID`),
  UNIQUE KEY `Team_Name` (`Team_Name`),
  KEY `Captain_ID` (`Captain_ID`),
  CONSTRAINT `Teams_ibfk_1` FOREIGN KEY (`Captain_ID`) REFERENCES `Persons` (`Person_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.Teams: ~7 rows (approximately)
/*!40000 ALTER TABLE `Teams` DISABLE KEYS */;
INSERT INTO `Teams` (`Team_ID`, `Team_Name`, `Team_Avatar`, `Last_Update_Datetime`, `Captain_ID`, `Deleted`) VALUES
	(1, 'Ottawa Senators', NULL, '2015-12-05 22:23:07', 8, b'00000000'),
	(2, 'Pittsburgh Penguins', NULL, '2015-12-05 01:14:04', 3, b'00000000'),
	(3, 'Chicago Blackhawks', NULL, '2015-12-05 17:39:44', 5, b'00000000'),
	(4, 'Montreal Canadiens', NULL, '2015-12-05 17:40:35', 6, b'00000000'),
	(5, 'Detroit Redwings', NULL, '2015-12-05 17:41:58', 7, b'00000000'),
	(7, 'Ottawa Senators 2', NULL, '2015-12-05 18:32:39', 8, b'00000001'),
	(8, 'hello world team', NULL, '2015-12-06 15:55:30', 9, b'00000000');
/*!40000 ALTER TABLE `Teams` ENABLE KEYS */;


-- Dumping structure for table mlanglois_SEG2105_Final.Tournaments
CREATE TABLE IF NOT EXISTS `Tournaments` (
  `Tournament_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Tournament_Organizer_ID` int(10) unsigned NOT NULL,
  `Tournament_Type` int(10) unsigned DEFAULT NULL,
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Tournament_Name` varchar(255) NOT NULL DEFAULT '',
  `Status` int(11) NOT NULL DEFAULT '0',
  `Deleted` bit(1) NOT NULL DEFAULT b'0',
  `Winner` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`Tournament_ID`),
  UNIQUE KEY `Tournament_Name` (`Tournament_Name`),
  KEY `Tournament_Organizer_ID` (`Tournament_Organizer_ID`),
  KEY `FK_Winner` (`Winner`),
  CONSTRAINT `FK_Winner` FOREIGN KEY (`Winner`) REFERENCES `Teams` (`Team_ID`),
  CONSTRAINT `Tournaments_ibfk_1` FOREIGN KEY (`Tournament_Organizer_ID`) REFERENCES `Persons` (`Person_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.Tournaments: ~6 rows (approximately)
/*!40000 ALTER TABLE `Tournaments` DISABLE KEYS */;
INSERT INTO `Tournaments` (`Tournament_ID`, `Tournament_Organizer_ID`, `Tournament_Type`, `Last_Update_Datetime`, `Tournament_Name`, `Status`, `Deleted`, `Winner`) VALUES
	(1, 1, 2, '2015-12-07 13:47:07', 'NHL 2015 Season', 2, b'1', NULL),
	(2, 1, 2, '2015-12-07 13:47:02', 'NHL 2016 Season', 2, b'0', NULL),
	(3, 1, 0, '2015-12-05 22:27:14', 'Stanley cup finals', 2, b'0', NULL),
	(4, 1, 1, '2015-12-06 03:06:09', 'Leauge style', 1, b'0', NULL),
	(5, 1, 1, '2015-12-06 15:57:35', 'hello world tournament', 2, b'0', NULL),
	(6, 1, 2, '2015-12-07 22:22:44', 'Hello Keeper', 0, b'0', NULL);
/*!40000 ALTER TABLE `Tournaments` ENABLE KEYS */;


-- Dumping structure for table mlanglois_SEG2105_Final.TournamentTeams
CREATE TABLE IF NOT EXISTS `TournamentTeams` (
  `Tournament_Team_ID` int(10) unsigned NOT NULL,
  `Tournament_ID` int(10) unsigned NOT NULL,
  `Team_ID` int(10) unsigned NOT NULL,
  `Withdrawn` bit(1) NOT NULL DEFAULT b'0',
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `Tournament_ID` (`Tournament_ID`,`Team_ID`),
  KEY `Team_ID` (`Team_ID`),
  CONSTRAINT `TournamentTeams_ibfk_1` FOREIGN KEY (`Tournament_ID`) REFERENCES `Tournaments` (`Tournament_ID`),
  CONSTRAINT `TournamentTeams_ibfk_2` FOREIGN KEY (`Team_ID`) REFERENCES `Teams` (`Team_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.TournamentTeams: ~21 rows (approximately)
/*!40000 ALTER TABLE `TournamentTeams` DISABLE KEYS */;
INSERT INTO `TournamentTeams` (`Tournament_Team_ID`, `Tournament_ID`, `Team_ID`, `Withdrawn`, `Last_Update_Datetime`) VALUES
	(0, 1, 1, b'0', '2015-12-05 17:42:02'),
	(0, 1, 2, b'0', '2015-12-05 17:42:06'),
	(0, 1, 3, b'0', '2015-12-05 17:42:10'),
	(0, 1, 4, b'0', '2015-12-05 17:42:13'),
	(0, 1, 5, b'0', '2015-12-05 17:42:15'),
	(0, 2, 1, b'0', '2015-12-05 17:49:05'),
	(0, 2, 2, b'0', '2015-12-05 17:49:08'),
	(0, 2, 3, b'0', '2015-12-05 17:49:11'),
	(0, 2, 4, b'0', '2015-12-05 17:49:14'),
	(0, 2, 5, b'0', '2015-12-05 17:49:16'),
	(0, 3, 1, b'0', '2015-12-05 22:25:46'),
	(0, 3, 2, b'0', '2015-12-05 22:26:14'),
	(0, 3, 3, b'0', '2015-12-05 22:26:18'),
	(0, 3, 4, b'0', '2015-12-05 22:26:30'),
	(0, 4, 1, b'0', '2015-12-06 03:05:38'),
	(0, 4, 2, b'0', '2015-12-06 03:05:41'),
	(0, 4, 3, b'0', '2015-12-06 03:05:43'),
	(0, 4, 4, b'0', '2015-12-06 03:05:46'),
	(0, 5, 1, b'0', '2015-12-06 15:56:09'),
	(0, 5, 3, b'0', '2015-12-06 15:56:18'),
	(0, 5, 8, b'0', '2015-12-06 15:56:14');
/*!40000 ALTER TABLE `TournamentTeams` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
