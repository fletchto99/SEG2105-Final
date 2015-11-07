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

-- Dumping structure for table mlanglois_SEG2105_Final.Matches
CREATE TABLE IF NOT EXISTS `Matches` (
  `Match_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Tournament_ID` int(10) unsigned NOT NULL,
  `Team_A_ID` int(10) unsigned NOT NULL,
  `Team_B_ID` int(10) unsigned NOT NULL,
  `Last_Update_Datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Match_ID`),
  UNIQUE KEY `Tournament_ID` (`Tournament_ID`,`Team_A_ID`,`Team_B_ID`),
  KEY `Team_A_ID` (`Team_A_ID`),
  KEY `Team_B_ID` (`Team_B_ID`),
  CONSTRAINT `Matches_ibfk_1` FOREIGN KEY (`Tournament_ID`) REFERENCES `Tournaments` (`Tournament_ID`),
  CONSTRAINT `Matches_ibfk_2` FOREIGN KEY (`Team_A_ID`) REFERENCES `Teams` (`Team_ID`),
  CONSTRAINT `Matches_ibfk_3` FOREIGN KEY (`Team_B_ID`) REFERENCES `Teams` (`Team_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
