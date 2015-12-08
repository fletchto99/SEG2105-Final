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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table mlanglois_SEG2105_Final.Tournaments: ~0 rows (approximately)
/*!40000 ALTER TABLE `Tournaments` DISABLE KEYS */;
/*!40000 ALTER TABLE `Tournaments` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
