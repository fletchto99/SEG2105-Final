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

-- Dumping structure for table mlanglois_SEG2105_Final.TournamentTeams
CREATE TABLE IF NOT EXISTS `TournamentTeams` (
  `Tournament_Team_ID` int(10) unsigned NOT NULL,
  `Tournament_ID` int(10) unsigned NOT NULL,
  `Team_ID` int(10) unsigned NOT NULL,
  `Withdrawen` bit(1) NOT NULL DEFAULT b'0',
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `Tournament_ID` (`Tournament_ID`,`Team_ID`),
  KEY `Team_ID` (`Team_ID`),
  CONSTRAINT `TournamentTeams_ibfk_1` FOREIGN KEY (`Tournament_ID`) REFERENCES `Tournaments` (`Tournament_ID`),
  CONSTRAINT `TournamentTeams_ibfk_2` FOREIGN KEY (`Team_ID`) REFERENCES `Teams` (`Team_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.TournamentTeams: ~0 rows (approximately)
/*!40000 ALTER TABLE `TournamentTeams` DISABLE KEYS */;
/*!40000 ALTER TABLE `TournamentTeams` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
