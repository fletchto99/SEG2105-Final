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
  KEY `Person_ID` (`Person_ID`),
  CONSTRAINT `Logins_ibfk` FOREIGN KEY (`Person_ID`) REFERENCES `PErsons` (`Person_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table mlanglois_SEG2105_Final.Logins: ~0 rows (approximately)
/*!40000 ALTER TABLE `Logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `Logins` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
