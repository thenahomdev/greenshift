
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `greenshift` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `greenshift`;
DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `adminid` int(11) NOT NULL AUTO_INCREMENT,
  `voornaam` varchar(255) NOT NULL,
  `tussenvoegsel` varchar(255) DEFAULT NULL,
  `achternaam` varchar(255) NOT NULL,
  `loginid` int(11) NOT NULL,
  PRIMARY KEY (`adminid`),
  KEY `loginid` (`loginid`),
  CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`loginid`) REFERENCES `login` (`loginid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `adres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adres` (
  `adresid` int(11) NOT NULL AUTO_INCREMENT,
  `straatnaam` varchar(255) NOT NULL,
  `postcode` varchar(255) NOT NULL,
  `huisnummer` varchar(255) NOT NULL,
  `plaats` varchar(255) NOT NULL,
  PRIMARY KEY (`adresid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `instructeur_beschikbaarheid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instructeur_beschikbaarheid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rijinstructeurid` int(11) NOT NULL,
  `datum` date NOT NULL,
  `tijd` time NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rijinstructeurid` (`rijinstructeurid`),
  CONSTRAINT `instructeur_beschikbaarheid_ibfk_1` FOREIGN KEY (`rijinstructeurid`) REFERENCES `rijinstructeurs` (`rijinstructeurid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leerlingen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leerlingen` (
  `leerlingid` int(11) NOT NULL AUTO_INCREMENT,
  `voornaam` varchar(20) NOT NULL,
  `tussenvoegsel` varchar(255) DEFAULT NULL,
  `achternaam` varchar(255) NOT NULL,
  `adresid` int(11) NOT NULL,
  `telefoonnummer` varchar(255) NOT NULL,
  `loginid` int(11) NOT NULL,
  `rijinstructeurid` int(11) NOT NULL,
  `old_rijinstructeurid` int(11) DEFAULT NULL,
  PRIMARY KEY (`leerlingid`),
  KEY `loginid` (`loginid`),
  KEY `adresid` (`adresid`),
  KEY `rijinstructeurid` (`rijinstructeurid`),
  CONSTRAINT `leerlingen_ibfk_1` FOREIGN KEY (`loginid`) REFERENCES `login` (`loginid`),
  CONSTRAINT `leerlingen_ibfk_2` FOREIGN KEY (`adresid`) REFERENCES `adres` (`adresid`),
  CONSTRAINT `leerlingen_ibfk_3` FOREIGN KEY (`rijinstructeurid`) REFERENCES `rijinstructeurs` (`rijinstructeurid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login` (
  `loginid` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `wachtwoord` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`loginid`),
  UNIQUE KEY `login_email_unique` (`email`),
  KEY `roleid` (`roleid`),
  CONSTRAINT `login_ibfk_1` FOREIGN KEY (`roleid`) REFERENCES `roles` (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pakket_geschiedenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pakket_geschiedenis` (
  `geschiedenisid` int(11) NOT NULL AUTO_INCREMENT,
  `pakketid` int(11) NOT NULL,
  `leerlingid` int(11) NOT NULL,
  `datum` date NOT NULL,
  `prijs` decimal(10,0) NOT NULL,
  PRIMARY KEY (`geschiedenisid`),
  KEY `leerlingid` (`leerlingid`),
  KEY `pakketid` (`pakketid`),
  CONSTRAINT `pakket_geschiedenis_ibfk_1` FOREIGN KEY (`leerlingid`) REFERENCES `leerlingen` (`leerlingid`),
  CONSTRAINT `pakket_geschiedenis_ibfk_2` FOREIGN KEY (`pakketid`) REFERENCES `pakketten` (`pakkettenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pakketten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pakketten` (
  `pakkettenid` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `prijs` decimal(8,2) NOT NULL,
  `aantal_lessen` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`pakkettenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rijinstructeurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rijinstructeurs` (
  `rijinstructeurid` int(11) NOT NULL AUTO_INCREMENT,
  `voornaam` varchar(255) NOT NULL,
  `tussenvoegsel` varchar(255) DEFAULT NULL,
  `achternaam` varchar(255) NOT NULL,
  `loginid` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`rijinstructeurid`),
  KEY `loginid` (`loginid`),
  CONSTRAINT `rijinstructeurs_ibfk_1` FOREIGN KEY (`loginid`) REFERENCES `login` (`loginid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rijlessen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rijlessen` (
  `rijlesid` int(11) NOT NULL AUTO_INCREMENT,
  `leerlingid` int(11) DEFAULT NULL,
  `rijinstructeurid` int(11) NOT NULL,
  `datum` date NOT NULL,
  `tijd` time NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`rijlesid`),
  KEY `leelringid` (`leerlingid`),
  KEY `rijinstructeurid` (`rijinstructeurid`),
  CONSTRAINT `rijlessen_ibfk_1` FOREIGN KEY (`leerlingid`) REFERENCES `leerlingen` (`leerlingid`),
  CONSTRAINT `rijlessen_ibfk_2` FOREIGN KEY (`rijinstructeurid`) REFERENCES `rijinstructeurs` (`rijinstructeurid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `roleid` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(11) NOT NULL,
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


-- Basisgegevens (nodig om de applicatie te gebruiken)

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin');
INSERT INTO `roles` VALUES (2,'Leerlingen');
INSERT INTO `roles` VALUES (3,'Rijinstruct');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `pakketten` WRITE;
/*!40000 ALTER TABLE `pakketten` DISABLE KEYS */;
INSERT INTO `pakketten` VALUES (12,'Standaard',60.00,1,1);
INSERT INTO `pakketten` VALUES (13,'Basis 5',250.00,5,1);
INSERT INTO `pakketten` VALUES (14,'Starter 10',450.00,10,0);
INSERT INTO `pakketten` VALUES (15,'Voordeel 15',585.00,15,1);
INSERT INTO `pakketten` VALUES (16,'Compleet 20',760.00,20,1);
INSERT INTO `pakketten` VALUES (17,'Starter 10',460.00,10,1);
/*!40000 ALTER TABLE `pakketten` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

