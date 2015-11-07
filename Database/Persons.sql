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

-- Dumping structure for table mlanglois_SEG2105_Final.Persons
CREATE TABLE IF NOT EXISTS `Persons` (
  `Person_ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Salt` varchar(64) NOT NULL,
  `Last_Update_Date_Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `First_Name` varchar(30) NOT NULL DEFAULT '',
  `Last_Name` varchar(30) NOT NULL DEFAULT '',
  `Jersey_Number` int(2) DEFAULT NULL,
  `Avatar` varchar(255) DEFAULT NULL,
  `Role_ID` int(10) unsigned NOT NULL,
  `Team_ID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`Person_ID`),
  UNIQUE KEY `Username` (`Username`),
  KEY `Role_ID` (`Role_ID`),
  KEY `Team_ID` (`Team_ID`),
  CONSTRAINT `Persons_ibfk_2` FOREIGN KEY (`Team_ID`) REFERENCES `Teams` (`Team_ID`),
  CONSTRAINT `Persons_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `Roles` (`Role_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
