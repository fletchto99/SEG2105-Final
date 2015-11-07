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

-- Dumping structure for table mlanglois_SEG2105_Final.Teams
CREATE TABLE IF NOT EXISTS `Teams` (
  `Team_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Team_Name` int(10) unsigned NOT NULL,
  `Team_Avatar` varchar(255) DEFAULT NULL,
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Captain_ID` int(10) unsigned DEFAULT NULL,
  `Deleted` bit(11) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`Team_ID`),
  UNIQUE KEY `Team_Name` (`Team_Name`),
  KEY `Captain_ID` (`Captain_ID`),
  CONSTRAINT `Teams_ibfk_1` FOREIGN KEY (`Captain_ID`) REFERENCES `Persons` (`Person_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
