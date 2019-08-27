-- MySQL dump 10.13  Distrib 5.6.43, for Linux (x86_64)
--
-- Host: localhost    Database: mobicoop_db
-- ------------------------------------------------------
-- Server version	5.6.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `given_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `family_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` smallint(6) NOT NULL,
  `nationality` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `telephone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` smallint(6) NOT NULL,
  `any_route_as_passenger` tinyint(1) DEFAULT NULL,
  `multi_transport_mode` tinyint(1) DEFAULT NULL,
  `max_detour_duration` int(11) DEFAULT NULL,
  `max_detour_distance` int(11) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `pupdtime` int(11) DEFAULT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'Guillaume','Faucheur','gfaucheur@ausy-group.com','$2y$12$vEqZxME6dOzRvhbLUEDXAuoerZO1HKKnDaOei4zjw2i.RWZ9SWKTu',2,NULL,'1987-01-01','0601020304',1,0,0,NULL,NULL,'2019-07-04 12:25:09',NULL,NULL),(2,'fred','marechal','fmarechal@ausy.fr','$2y$12$Ao1Gj2UZINx5JhXE6udrAuyVQKtNmk7EM8SXt/uakLxYSPrcrs1jS',2,NULL,'1987-01-01','0672290053',1,0,0,NULL,NULL,'2019-07-04 13:55:54',NULL,NULL),(4,'Frederik2','Marechal2','fredmarechal@hotmail.fr','$2y$12$dmR5xsEp.g1pbJNrgkbrieyLvqygUCTKkPjrUSLtrbEKxjy1ZmK6i',2,NULL,'1975-01-01','0672290053',1,0,0,NULL,NULL,'2019-07-09 07:13:31',NULL,NULL),(5,'Fabien','TSHITEYA','ftshiteya@grandlyon.fr','$2y$12$DtKY5S9tln3opii6ZHDlAuY/8EhEMeWoHtDk1HF6YnOv0uZ3v.VR6',2,NULL,'1987-01-01','06 26 88 90 01',1,0,0,NULL,NULL,'2019-07-15 09:23:15',NULL,NULL),(6,'Doric','Ngouffo','ngouffodoric@gmail.com','$2y$12$UNqiOvCJ5YlaC6ZMx5JSbe31i6FpoBszCl/1CbS9ULr1.AcCtlgsa',2,NULL,'1993-01-01','0782933388',1,0,0,NULL,NULL,'2019-07-19 11:51:38',1566313353,'$2y$12$5FeECCO8RVWeM9jNzQEOx.RT.HkCcnITHu93N64fwEtRw55lLxuk6'),(7,'Doric','Ngouffo','dngouffo@ausy-group.com','$2y$12$geJXJJJDe0TwXOOeGTYJ3.WWvD1uZE2w0ChSXvJNGl/sgL0B5/JOO',2,NULL,'1993-01-01','0782933388',1,0,0,NULL,NULL,'2019-07-19 13:28:21',-1,''),(9,'Doric','Dr','ngouffo3@gmail.com','$2y$12$/DqMX0RLn24uvqGBMYbzn.9Oob4SKvv8ZePEkWRtwDNnQAbC6e67C',2,NULL,'1993-01-01','0782933388',1,0,0,NULL,NULL,'2019-07-25 14:05:13',NULL,NULL),(10,'Doric','Ngouffo','dodo@ausy.fr','$2y$12$EpkjkouSY/2pJcl9z2nNYOZ0Xud5tWEpuxM.k.p/I6qHIU8c2DGYC',2,NULL,'1993-01-01','0782933388',1,0,0,NULL,NULL,'2019-07-26 10:59:13',NULL,NULL),(11,'doric','ngouffo','dodo_test@ausy.fr','$2y$12$auMq0RB0uZx7dnr6LTiqMO9fAvUT7S8aPrnL98EU88DV24RNjoY2a',2,NULL,'1993-01-01','0782933388',1,0,0,NULL,NULL,'2019-07-26 11:01:31',NULL,NULL),(12,'Fabien','TSHITEYA','ftshiteya@grandlyon.com','$2y$12$SYg4lfAf0qO/JQy0SceDtOqEsa1hqarNg2kQ0zVteLr9FphxysvIW',2,NULL,'1986-01-01','0616449075',1,0,0,NULL,NULL,'2019-07-26 11:16:44',NULL,NULL),(13,'Idylle','MAI','IMAI@grandlyon.com','$2y$12$.h4PFj7kcHb7hl5Mk7i5U.eCNglN7LQZSqN3mEbjUYxKYEWJXh9EW',1,NULL,'1910-01-01','04 26 99 37 97',1,0,0,NULL,NULL,'2019-07-30 16:03:19',NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-08-23 13:52:06
