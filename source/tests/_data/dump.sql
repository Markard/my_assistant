-- MySQL dump 10.13  Distrib 5.6.27, for debian-linux-gnu (x86_64)
--
-- Host: database    Database: site-test
-- ------------------------------------------------------
-- Server version	5.6.27-0ubuntu0.14.04.1

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
-- Table structure for table `email_confirmation`
--

DROP TABLE IF EXISTS `email_confirmation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_confirmation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `confirmation_code` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `udx_email_confirmation_1` (`email`),
  UNIQUE KEY `udx_email_confirmation_2` (`user_id`),
  CONSTRAINT `fk_email_confirmation_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_confirmation`
--

LOCK TABLES `email_confirmation` WRITE;
/*!40000 ALTER TABLE `email_confirmation` DISABLE KEYS */;
INSERT INTO `email_confirmation` VALUES (1,4,'test_user_with_confirmation@gmail.com','123qwe','2015-10-30 20:17:43');
/*!40000 ALTER TABLE `email_confirmation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income`
--

DROP TABLE IF EXISTS `income`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3FA862D0A76ED395` (`user_id`),
  CONSTRAINT `fk_income_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income`
--

LOCK TABLES `income` WRITE;
/*!40000 ALTER TABLE `income` DISABLE KEYS */;
INSERT INTO `income` VALUES (1,7,'salary',10.50,'2015-01-01','2015-10-30 20:17:49','2015-10-30 20:17:49'),(2,7,'salary',10.50,'2015-01-01','2015-10-30 20:17:49','2015-10-30 20:17:49'),(3,7,'salary',10.50,'2015-01-01','2015-10-30 20:17:49','2015-10-30 20:17:49'),(4,7,'salary',10.50,'2015-01-01','2015-10-30 20:17:49','2015-10-30 20:17:49'),(5,7,'salary',10.50,'2015-01-01','2015-10-30 20:17:49','2015-10-30 20:17:49'),(6,7,'salary',100.00,'2015-02-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(7,7,'salary',100.00,'2015-02-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(8,7,'salary',100.00,'2015-02-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(9,7,'salary',100.00,'2015-02-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(10,7,'salary',105.80,'2015-03-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(11,7,'salary',105.80,'2015-03-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(12,7,'salary',105.80,'2015-03-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(13,7,'salary',105.80,'2015-03-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(14,7,'salary',105.80,'2015-03-01','2015-10-30 20:17:50','2015-10-30 20:17:50'),(15,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(16,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(17,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(18,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(19,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(20,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(21,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(22,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(23,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(24,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(25,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(26,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(27,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(28,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(29,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(30,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(31,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(32,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(33,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52'),(34,8,'salary',401.24,'2015-01-01','2015-10-30 20:17:52','2015-10-30 20:17:52');
/*!40000 ALTER TABLE `income` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('20150702213736');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase`
--

DROP TABLE IF EXISTS `purchase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `bought_at` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_purchase_1` (`user_id`),
  CONSTRAINT `fk_purchase_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase`
--

LOCK TABLES `purchase` WRITE;
/*!40000 ALTER TABLE `purchase` DISABLE KEYS */;
INSERT INTO `purchase` VALUES (1,5,'Milk',5,100.99,'2015-01-01','2015-10-30 20:17:45','2015-10-30 20:17:45'),(2,5,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:45','2015-10-30 20:17:45'),(3,5,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:45','2015-10-30 20:17:45'),(4,5,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:45','2015-10-30 20:17:45'),(5,5,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:45','2015-10-30 20:17:45'),(6,5,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:45','2015-10-30 20:17:45'),(7,5,'purchase',10,5.00,'2015-02-03','2015-10-30 20:17:45','2015-10-30 20:17:45'),(8,5,'purchase',10,5.00,'2015-02-03','2015-10-30 20:17:45','2015-10-30 20:17:45'),(9,5,'purchase',10,5.00,'2015-02-03','2015-10-30 20:17:45','2015-10-30 20:17:45'),(10,5,'purchase',10,5.00,'2015-02-03','2015-10-30 20:17:45','2015-10-30 20:17:45'),(11,5,'purchase',10,15.00,'2015-01-02','2015-10-30 20:17:45','2015-10-30 20:17:45'),(12,5,'purchase',10,15.00,'2015-01-02','2015-10-30 20:17:45','2015-10-30 20:17:45'),(13,5,'purchase',10,15.00,'2015-01-02','2015-10-30 20:17:45','2015-10-30 20:17:45'),(14,5,'purchase',10,15.00,'2015-01-02','2015-10-30 20:17:45','2015-10-30 20:17:45'),(15,5,'purchase',10,15.00,'2015-01-02','2015-10-30 20:17:45','2015-10-30 20:17:45'),(16,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(17,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(18,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(19,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(20,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(21,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(22,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(23,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(24,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(25,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(26,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(27,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(28,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(29,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(30,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(31,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(32,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(33,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(34,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47'),(35,6,'purchase',10,10.00,'2015-01-01','2015-10-30 20:17:47','2015-10-30 20:17:47');
/*!40000 ALTER TABLE `purchase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `timezone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `purchases_per_day` int(10) unsigned NOT NULL DEFAULT '0',
  `incomes_per_month` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `udx_user_1` (`username`),
  UNIQUE KEY `udx_user_2` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'TestUser1','test_user1@gmail.com','$2y$15$dSgDxW6ZbBwC2H9SBHKxq.7IapfrGD00xX2RM7xhy48WBYQ6hNtza','UTC',0,0,'2015-10-30 20:17:38','2015-10-30 20:17:38'),(2,'TestUser2','test_user2@gmail.com','$2y$15$QoH9rIUGAMkSs9jO.WUy0ukZy5enx.sVnj9VJQut5jxvZACrin1Oq','UTC',0,0,'2015-10-30 20:17:40','2015-10-30 20:17:40'),(3,'Admin','admin@gmail.com','$2y$15$UGEeZPTUP1oYf..tMghOQu.bN/HgYVdWugVR80iVINOudUvR7G9j2','UTC',0,0,'2015-10-30 20:17:41','2015-10-30 20:17:41'),(4,'TestUserWithConfirmation','test_user_with_confirmation@gmail.com','$2y$15$n2qhlSuM7UZlUNQ9QxVaKeVdMAVApqKvvwgEmwAhWHXd9tfShNrSy','UTC',0,0,'2015-10-30 20:17:43','2015-10-30 20:17:43'),(5,'TestUserWithPurchases1','test_user_with_purchase1@gmail.com','$2y$15$NArfkEph6tshC1PBjSKtXeC5u13MtMukwDnoW0GTwAmLEq4nDbB8K','UTC',15,0,'2015-10-30 20:17:45','2015-10-30 20:17:45'),(6,'TestUserWithPurchases2','test_user_with_purchase2@gmail.com','$2y$15$yPG2PYvm/TKQIO8O7DWJl./3eePthw2/vxlU4Weg41DY.ahikpx/G','UTC',20,0,'2015-10-30 20:17:47','2015-10-30 20:17:47'),(7,'TestUserWithIncome1','test_user_with_income1@gmail.com','$2y$15$ss/ls/V5970t0J686eqHeuRNa6EH5JAzkIpYgNk35PHmJ92Cs0GXm','UTC',0,14,'2015-10-30 20:17:49','2015-10-30 20:17:49'),(8,'TestUserWithIncome2','test_user_with_income2@gmail.com','$2y$15$8KgHNysl/Pv7NHboptVLkuZXs2nsTxxPn4w4aMELTSs3MNGmvElKe','UTC',0,20,'2015-10-30 20:17:52','2015-10-30 20:17:52');
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

-- Dump completed on 2015-10-30 20:17:52
