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
  `Assist_ID` int(10) unsigned NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.Goals: ~0 rows (approximately)
/*!40000 ALTER TABLE `Goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `Goals` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
