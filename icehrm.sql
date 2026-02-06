-- MySQL dump 10.13  Distrib 5.7.30, for Linux (x86_64)
--
-- Host: localhost    Database: icehrm
-- ------------------------------------------------------
-- Server version	5.7.30

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
-- Table structure for table `Applications`
--

DROP TABLE IF EXISTS `Applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Applications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `job` bigint(20) NOT NULL,
  `candidate` bigint(20) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `referredByEmail` varchar(200) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job` (`job`,`candidate`),
  KEY `Fk_Applications_Candidates` (`candidate`),
  CONSTRAINT `Fk_Applications_Candidates` FOREIGN KEY (`candidate`) REFERENCES `Candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_Applications_Job` FOREIGN KEY (`job`) REFERENCES `Job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Applications`
--

LOCK TABLES `Applications` WRITE;
/*!40000 ALTER TABLE `Applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `Applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ArchivedEmployees`
--

DROP TABLE IF EXISTS `ArchivedEmployees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ArchivedEmployees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ref_id` bigint(20) NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `gender` varchar(15) DEFAULT NULL,
  `ssn_num` varchar(100) DEFAULT '',
  `nic_num` varchar(100) DEFAULT '',
  `other_id` varchar(100) DEFAULT '',
  `work_email` varchar(100) DEFAULT NULL,
  `joined_date` datetime DEFAULT NULL,
  `confirmation_date` datetime DEFAULT NULL,
  `supervisor` bigint(20) DEFAULT NULL,
  `department` bigint(20) DEFAULT NULL,
  `termination_date` datetime DEFAULT NULL,
  `notes` text,
  `data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ArchivedEmployees`
--

LOCK TABLES `ArchivedEmployees` WRITE;
/*!40000 ALTER TABLE `ArchivedEmployees` DISABLE KEYS */;
/*!40000 ALTER TABLE `ArchivedEmployees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AssetTypes`
--

DROP TABLE IF EXISTS `AssetTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AssetTypes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `description` text,
  `attachment` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AssetTypes`
--

LOCK TABLES `AssetTypes` WRITE;
/*!40000 ALTER TABLE `AssetTypes` DISABLE KEYS */;
INSERT INTO `AssetTypes` VALUES (1,'Test1','test','8aKr5iAx8aqN3E1607161608283','2020-12-05 15:17:04','2020-12-05 15:17:04');
/*!40000 ALTER TABLE `AssetTypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Attendance`
--

DROP TABLE IF EXISTS `Attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Attendance` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `in_time` datetime DEFAULT NULL,
  `out_time` datetime DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `image_in` longtext,
  `image_out` longtext,
  `map_lat` decimal(10,8) DEFAULT NULL,
  `map_lng` decimal(10,8) DEFAULT NULL,
  `map_snapshot` longtext,
  `map_out_lat` decimal(10,8) DEFAULT NULL,
  `map_out_lng` decimal(10,8) DEFAULT NULL,
  `map_out_snapshot` longtext,
  `in_ip` varchar(25) DEFAULT NULL,
  `out_ip` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `in_time` (`in_time`),
  KEY `out_time` (`out_time`),
  KEY `employee_in_time` (`employee`,`in_time`),
  KEY `employee_out_time` (`employee`,`out_time`),
  CONSTRAINT `Fk_Attendance_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Attendance`
--

LOCK TABLES `Attendance` WRITE;
/*!40000 ALTER TABLE `Attendance` DISABLE KEYS */;
INSERT INTO `Attendance` VALUES (1,1,'2020-05-10 08:08:36','2020-05-10 19:07:46','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,1,'2020-05-11 08:10:40','2020-05-11 17:57:07','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,1,'2020-05-12 08:05:33','2020-05-12 18:55:22','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,1,'2020-05-13 08:09:28','2020-05-13 17:58:05','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,1,'2020-05-14 08:09:58','2020-05-14 21:05:12','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,1,'2020-05-15 08:44:23','2020-05-15 16:56:07','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,1,'2020-05-16 08:33:36','2020-05-16 17:59:38','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,1,'2020-05-17 08:58:53','2020-05-17 21:27:53','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,1,'2020-05-18 08:36:33','2020-05-18 17:53:11','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,1,'2020-05-19 08:44:38','2020-05-19 20:39:35','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,1,'2020-05-20 08:39:04','2020-05-20 21:33:28','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(12,1,'2020-05-21 08:40:40','2020-05-21 19:39:40','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(13,1,'2020-05-22 08:01:03','2020-05-22 17:32:37','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(14,1,'2020-05-23 08:14:00','2020-05-23 18:22:37','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(15,1,'2020-05-24 08:16:48','2020-05-24 17:16:18','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(16,1,'2020-05-25 08:13:10','2020-05-25 18:46:11','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(17,2,'2020-05-10 08:38:58','2020-05-10 21:10:45','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(18,2,'2020-05-11 08:22:01','2020-05-11 17:28:36','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(19,2,'2020-05-12 08:07:44','2020-05-12 21:26:39','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(20,2,'2020-05-13 08:08:57','2020-05-13 16:09:00','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(21,2,'2020-05-14 08:40:36','2020-05-14 17:51:10','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(22,2,'2020-05-15 08:30:49','2020-05-15 19:51:21','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(23,2,'2020-05-16 08:40:18','2020-05-16 18:35:17','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(24,2,'2020-05-17 08:08:28','2020-05-17 18:28:21','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(25,2,'2020-05-18 08:38:12','2020-05-18 21:41:06','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(26,3,'2020-05-10 08:49:38','2020-05-10 17:45:59','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(27,3,'2020-05-11 08:59:23','2020-05-11 16:23:47','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(28,3,'2020-05-12 08:21:38','2020-05-12 19:38:00','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(29,3,'2020-05-13 08:56:43','2020-05-13 21:27:56','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(30,3,'2020-05-14 08:43:05','2020-05-14 21:09:01','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(31,3,'2020-05-15 08:08:14','2020-05-15 16:40:03','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(32,3,'2020-05-16 08:08:39','2020-05-16 18:19:40','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(33,3,'2020-05-17 08:30:25','2020-05-17 18:13:29','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(34,3,'2020-05-18 08:19:59','2020-05-18 19:19:03','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(35,1,'2021-02-01 23:57:00','2021-02-01 23:58:00','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'192.168.10.1','192.168.10.1'),(36,3,'2021-02-01 23:58:00','2021-02-01 23:59:00','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'192.168.10.1','192.168.10.1');
/*!40000 ALTER TABLE `Attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AuditLog`
--

DROP TABLE IF EXISTS `AuditLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AuditLog` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `time` datetime DEFAULT NULL,
  `user` bigint(20) NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `employee` varchar(300) DEFAULT NULL,
  `details` text,
  PRIMARY KEY (`id`),
  KEY `Fk_AuditLog_Users` (`user`),
  CONSTRAINT `Fk_AuditLog_Users` FOREIGN KEY (`user`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AuditLog`
--

LOCK TABLES `AuditLog` WRITE;
/*!40000 ALTER TABLE `AuditLog` DISABLE KEYS */;
INSERT INTO `AuditLog` VALUES (1,'2020-06-09 16:53:50',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(2,'2020-06-09 18:07:23',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Setting [id:54]'),(3,'2020-06-09 18:07:37',1,'192.168.10.1','User Action','IceHrm Employee [EmpId = EMP001]','Punch In \\ time:2020-06-09 20:07:00'),(4,'2020-06-09 18:08:33',1,'192.168.10.1','Delete','IceHrm Employee [EmpId = EMP001]','Deleted an object in Attendance [id:35]'),(5,'2020-11-02 08:06:52',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(6,'2020-11-02 08:07:11',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(7,'2020-11-02 21:33:07',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(8,'2020-11-02 22:24:12',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(9,'2020-11-02 22:32:55',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to EmployeeTimeEntry [id:1]'),(10,'2020-11-02 22:38:14',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(11,'2020-11-03 06:46:06',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(12,'2020-11-03 11:40:08',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(13,'2020-11-03 17:09:30',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(14,'2020-11-03 20:24:35',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(15,'2020-11-03 21:11:40',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(16,'2020-11-04 06:08:54',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(17,'2020-11-04 06:25:12',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in EmployeeDocument [id:3]'),(18,'2020-11-04 07:11:22',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(19,'2020-11-04 08:02:03',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:1]'),(20,'2020-11-04 08:02:09',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:2]'),(21,'2020-11-04 22:03:22',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(22,'2020-11-04 23:22:30',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(23,'2020-11-05 06:45:56',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(24,'2020-11-05 10:26:25',1,'192.168.10.1','Error','IceHrm Employee [EmpId = EMP001]','Error occurred while adding an object to Employee \\ Error: Incorrect date value: \'0000-00-00\' for column \'confirmation_date\' at row 1'),(25,'2020-11-05 10:27:02',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to Employee [id:5]'),(26,'2020-11-05 10:27:20',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(27,'2020-11-05 10:31:24',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:5]'),(28,'2020-11-05 10:38:08',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:5]'),(29,'2020-11-05 10:38:15',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:5]'),(30,'2020-11-05 10:38:42',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:5]'),(31,'2020-11-05 10:44:54',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:5]'),(32,'2020-11-05 10:51:18',1,'192.168.10.1','Delete','IceHrm Employee [EmpId = EMP001]','Deleted an object in Employee [id:5]'),(33,'2020-11-06 05:57:49',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(34,'2020-11-06 06:40:09',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(35,'2020-11-06 06:53:54',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:1]'),(36,'2020-11-06 07:39:51',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(37,'2020-11-06 07:52:47',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:1]'),(38,'2020-11-06 07:52:54',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:2]'),(39,'2020-11-06 08:01:51',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(40,'2020-11-06 08:02:05',2,'192.168.10.1','Authentication','Lala Lamees [EmpId = EMP002]','User Login'),(41,'2020-11-06 08:02:21',2,'192.168.10.1','Edit','Lala Lamees [EmpId = EMP002]','Edited an object in Employee [id:4]'),(42,'2020-11-06 08:02:37',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(43,'2020-11-06 17:52:31',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(44,'2020-11-06 18:46:54',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(45,'2020-11-07 12:20:13',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(46,'2020-11-08 05:06:59',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(47,'2020-11-08 05:30:37',1,'192.168.10.1','User Action','IceHrm Employee [EmpId = EMP001]','Leave applied \\ start:2020-11-01\\ end:2020-11-02'),(48,'2020-11-08 07:37:43',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(49,'2020-11-08 12:35:11',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(50,'2020-11-08 13:26:37',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(51,'2020-11-14 18:48:06',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(52,'2020-11-14 18:50:20',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to ReviewTemplate [id:1]'),(53,'2020-11-14 18:51:39',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to PerformanceReview [id:1]'),(54,'2020-11-14 18:56:00',2,'192.168.10.1','Authentication','Lala Lamees [EmpId = EMP002]','User Login'),(55,'2020-11-14 19:15:05',2,'192.168.10.1','Error','Lala Lamees [EmpId = EMP002]','Error occurred while adding an object to Employee \\ Error: Duplicate entry \'EMP004\' for key \'employee_id\''),(56,'2020-11-14 19:15:10',2,'192.168.10.1','Add','Lala Lamees [EmpId = EMP002]','Added an object to Employee [id:6]'),(57,'2020-11-14 19:16:13',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(58,'2020-11-14 19:17:11',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in User [id:5]'),(59,'2020-11-14 19:18:08',2,'192.168.10.1','Authentication','Lala Lamees [EmpId = EMP002]','User Login'),(60,'2020-11-14 19:20:58',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(61,'2020-11-14 19:21:26',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:3]'),(62,'2020-11-14 19:22:51',2,'192.168.10.1','Add','Lala Lamees [EmpId = EMP002]','Added an object to ReviewTemplate [id:2]'),(63,'2020-11-14 19:23:39',2,'192.168.10.1','Add','Lala Lamees [EmpId = EMP002]','Added an object to ReviewFeedback [id:1]'),(64,'2020-11-14 19:24:17',5,'192.168.10.1','Authentication','Jane Welington [EmpId = EMP005]','User Login'),(65,'2020-11-14 19:24:38',5,'192.168.10.1','Edit','Jane Welington [EmpId = EMP005]','Edited an object in ReviewFeedback [id:1]'),(66,'2020-11-14 19:24:45',5,'192.168.10.1','Edit','Jane Welington [EmpId = EMP005]','Edited an object in ReviewFeedback [id:1]'),(67,'2020-11-14 19:28:30',2,'192.168.10.1','Add','Lala Lamees [EmpId = EMP002]','Added an object to ReviewFeedback [id:2]'),(68,'2020-11-14 19:28:50',5,'192.168.10.1','Edit','Jane Welington [EmpId = EMP005]','Edited an object in ReviewFeedback [id:2]'),(69,'2020-11-14 19:31:49',5,'192.168.10.1','Edit','Jane Welington [EmpId = EMP005]','Edited an object in ReviewFeedback [id:2]'),(70,'2020-11-15 08:42:37',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(71,'2020-11-15 09:08:02',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in ReviewTemplate [id:1]'),(72,'2020-11-15 13:39:35',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(73,'2020-11-19 07:41:21',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(74,'2020-11-19 16:53:54',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(75,'2020-11-21 06:12:43',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(76,'2020-11-28 07:37:03',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(77,'2020-11-30 09:08:49',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(78,'2020-12-03 06:35:12',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(79,'2020-12-03 07:16:40',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(80,'2020-12-03 07:16:58',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in PerformanceReview [id:1]'),(81,'2020-12-03 07:17:11',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in PerformanceReview [id:1]'),(82,'2020-12-03 07:17:21',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in PerformanceReview [id:1]'),(83,'2020-12-03 07:17:52',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in PerformanceReview [id:1]'),(84,'2020-12-03 07:31:07',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in PerformanceReview [id:1]'),(85,'2020-12-03 07:31:13',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to PerformanceReview [id:2]'),(86,'2020-12-03 07:31:25',1,'192.168.10.1','Delete','IceHrm Employee [EmpId = EMP001]','Deleted an object in PerformanceReview [id:2]'),(87,'2020-12-03 07:35:34',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in PerformanceReview [id:1]'),(88,'2020-12-03 08:51:27',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(89,'2020-12-03 08:52:09',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in PerformanceReview [id:1]'),(90,'2020-12-03 12:17:40',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(91,'2020-12-05 09:32:38',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(92,'2020-12-05 09:47:04',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to AssetType [id:1]'),(93,'2020-12-05 09:56:28',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in UserRole [id:1]'),(94,'2020-12-27 09:13:17',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(95,'2021-01-08 07:30:43',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(96,'2021-01-08 07:51:59',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to LeaveType [id:4]'),(97,'2021-01-08 07:52:13',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in LeaveType [id:4]'),(98,'2021-01-08 07:54:10',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to LeaveRule [id:3]'),(99,'2021-01-08 07:55:30',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:1]'),(100,'2021-01-08 07:56:13',1,'192.168.10.1','User Action','IceHrm Employee [EmpId = EMP001]','Leave applied \\ start:2021-01-06\\ end:2021-01-06'),(101,'2021-01-08 07:58:03',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:1]'),(102,'2021-01-08 07:58:13',1,'192.168.10.1','Delete','IceHrm Employee [EmpId = EMP001]','Deleted an object in EmployeeLeave [id:2]'),(103,'2021-01-08 07:59:44',1,'192.168.10.1','User Action','IceHrm Employee [EmpId = EMP001]','Leave applied \\ start:2020-12-02\\ end:2020-12-02'),(104,'2021-01-08 07:59:51',1,'192.168.10.1','Delete','IceHrm Employee [EmpId = EMP001]','Deleted an object in EmployeeLeave [id:3]'),(105,'2021-01-08 08:01:30',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Employee [id:3]'),(106,'2021-01-08 08:03:16',1,'192.168.10.1','User Action','Sofia O\'Sullivan [EmpId = EMP003]','Leave applied \\ start:2021-02-03\\ end:2021-02-03'),(107,'2021-01-08 08:03:22',1,'192.168.10.1','Delete','Sofia O\'Sullivan [EmpId = EMP003]','Deleted an object in EmployeeLeave [id:4]'),(108,'2021-01-08 08:03:36',3,'192.168.10.1','Authentication','Sofia O\'Sullivan [EmpId = EMP003]','User Login'),(109,'2021-01-08 08:04:01',3,'192.168.10.1','User Action','Sofia O\'Sullivan [EmpId = EMP003]','Leave applied \\ start:2021-01-01\\ end:2021-01-01'),(110,'2021-01-08 08:04:09',3,'192.168.10.1','Delete','Sofia O\'Sullivan [EmpId = EMP003]','Deleted an object in EmployeeLeave [id:5]'),(111,'2021-01-26 08:17:59',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(112,'2021-01-26 08:18:43',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to PayslipTemplate [id:1]'),(113,'2021-01-26 08:18:49',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to DeductionGroup [id:1]'),(114,'2021-01-26 08:21:20',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to Payroll [id:1]'),(115,'2021-01-26 08:22:04',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to PayrollColumn [id:1]'),(116,'2021-01-26 08:23:52',1,'192.168.10.1','Add','IceHrm Employee [EmpId = EMP001]','Added an object to PayrollColumn [id:2]'),(117,'2021-01-26 08:24:12',1,'192.168.10.1','Edit','IceHrm Employee [EmpId = EMP001]','Edited an object in Payroll [id:1]'),(118,'2021-01-26 18:25:34',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(119,'2021-02-01 22:47:48',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(120,'2021-02-01 22:50:23',1,'192.168.10.1','User Action','IceHrm Employee [EmpId = EMP001]','Punch In \\ time:2021-02-01 23:57:00'),(121,'2021-02-01 22:50:32',1,'192.168.10.1','User Action','IceHrm Employee [EmpId = EMP001]','Punch Out \\ time:2021-02-01 23:58:00'),(122,'2021-02-01 22:50:46',3,'192.168.10.1','Authentication','Sofia O\'Sullivan [EmpId = EMP003]','User Login'),(123,'2021-02-01 22:51:03',3,'192.168.10.1','User Action','Sofia O\'Sullivan [EmpId = EMP003]','Punch In \\ time:2021-02-01 23:58:00'),(124,'2021-02-01 22:51:27',3,'192.168.10.1','User Action','Sofia O\'Sullivan [EmpId = EMP003]','Punch Out \\ time:2021-02-01 23:59:00'),(125,'2021-02-04 05:15:09',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login'),(126,'2021-02-04 05:17:45',1,'192.168.10.1','Authentication','IceHrm Employee [EmpId = EMP001]','User Login');
/*!40000 ALTER TABLE `AuditLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Benifits`
--

DROP TABLE IF EXISTS `Benifits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Benifits` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Benifits`
--

LOCK TABLES `Benifits` WRITE;
/*!40000 ALTER TABLE `Benifits` DISABLE KEYS */;
INSERT INTO `Benifits` VALUES (1,'Retirement plan'),(2,'Health plan'),(3,'Life insurance'),(4,'Paid vacations');
/*!40000 ALTER TABLE `Benifits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Calls`
--

DROP TABLE IF EXISTS `Calls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Calls` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `job` bigint(20) NOT NULL,
  `candidate` bigint(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `Fk_Calls_Job` (`job`),
  KEY `Fk_Calls_Candidates` (`candidate`),
  CONSTRAINT `Fk_Calls_Candidates` FOREIGN KEY (`candidate`) REFERENCES `Candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_Calls_Job` FOREIGN KEY (`job`) REFERENCES `Job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Calls`
--

LOCK TABLES `Calls` WRITE;
/*!40000 ALTER TABLE `Calls` DISABLE KEYS */;
/*!40000 ALTER TABLE `Calls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Candidates`
--

DROP TABLE IF EXISTS `Candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Candidates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `nationality` bigint(20) DEFAULT NULL,
  `birthday` datetime DEFAULT NULL,
  `gender` varchar(15) DEFAULT NULL,
  `marital_status` enum('Married','Single','Divorced','Widowed','Other') DEFAULT NULL,
  `address1` varchar(100) DEFAULT '',
  `address2` varchar(100) DEFAULT '',
  `city` varchar(150) DEFAULT '',
  `country` char(2) DEFAULT NULL,
  `province` bigint(20) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `home_phone` varchar(50) DEFAULT NULL,
  `mobile_phone` varchar(50) DEFAULT NULL,
  `cv_title` varchar(200) NOT NULL DEFAULT '',
  `cv` varchar(150) DEFAULT NULL,
  `cvtext` text,
  `industry` text,
  `profileImage` varchar(150) DEFAULT NULL,
  `head_line` text,
  `objective` text,
  `work_history` text,
  `education` text,
  `skills` text,
  `referees` text,
  `linkedInUrl` varchar(500) DEFAULT NULL,
  `linkedInData` text,
  `totalYearsOfExperience` int(11) DEFAULT NULL,
  `totalMonthsOfExperience` int(11) DEFAULT NULL,
  `htmlCVData` longtext,
  `generatedCVFile` varchar(150) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `expectedSalary` int(11) DEFAULT NULL,
  `preferedPositions` text,
  `preferedJobtype` varchar(60) DEFAULT NULL,
  `preferedCountries` text,
  `tags` text,
  `notes` text,
  `calls` text,
  `age` int(11) DEFAULT NULL,
  `hash` varchar(100) DEFAULT NULL,
  `linkedInProfileLink` varchar(250) DEFAULT NULL,
  `linkedInProfileId` varchar(50) DEFAULT NULL,
  `facebookProfileLink` varchar(250) DEFAULT NULL,
  `facebookProfileId` varchar(50) DEFAULT NULL,
  `twitterProfileLink` varchar(250) DEFAULT NULL,
  `twitterProfileId` varchar(50) DEFAULT NULL,
  `googleProfileLink` varchar(250) DEFAULT NULL,
  `googleProfileId` varchar(50) DEFAULT NULL,
  `hiringStage` bigint(20) DEFAULT NULL,
  `jobId` bigint(20) DEFAULT NULL,
  `source` enum('Sourced','Applied') DEFAULT 'Sourced',
  `emailSent` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `source_emailSent` (`source`,`emailSent`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Candidates`
--

LOCK TABLES `Candidates` WRITE;
/*!40000 ALTER TABLE `Candidates` DISABLE KEYS */;
INSERT INTO `Candidates` VALUES (1,'Jhon','Doe',4,NULL,'Male',NULL,NULL,NULL,'New York','US',NULL,NULL,'icehrm+jhon@web-stalk.com','+1 455565656',NULL,'Software Engineer','cv_rYwHphV7xD5dOe1444302569136',NULL,NULL,NULL,'','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,'2015-10-08 16:59:20','2015-10-08 16:59:20',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'663fd20d1859344585f678a0f87b23522b8f9fce8c67c5290a609ce342b81442',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Sourced',0);
/*!40000 ALTER TABLE `Candidates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Certifications`
--

DROP TABLE IF EXISTS `Certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Certifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Certifications`
--

LOCK TABLES `Certifications` WRITE;
/*!40000 ALTER TABLE `Certifications` DISABLE KEYS */;
INSERT INTO `Certifications` VALUES (1,'Red Hat Certified Architect (RHCA)','Red Hat Certified Architect (RHCA)'),(2,'GIAC Secure Software Programmer -Java','GIAC Secure Software Programmer -Java'),(3,'Risk Management Professional (PMI)','Risk Management Professional (PMI)'),(4,'IT Infrastructure Library (ITIL) Expert Certification','IT Infrastructure Library (ITIL) Expert Certification'),(5,'Microsoft Certified Architect','Microsoft Certified Architect'),(6,'Oracle Exadata 11g Certified Implementation Specialist','Oracle Exadata 11g Certified Implementation Specialist'),(7,'Cisco Certified Design Professional (CCDP)','Cisco Certified Design Professional (CCDP)'),(8,'Cisco Certified Internetwork Expert (CCIE)','Cisco Certified Internetwork Expert (CCIE)'),(9,'Cisco Certified Network Associate','Cisco Certified Network Associate'),(10,'HP/Master Accredited Solutions Expert (MASE)','HP/Master Accredited Solutions Expert (MASE)'),(11,'HP/Master Accredited Systems Engineer (Master ASE)','HP/Master Accredited Systems Engineer (Master ASE)'),(12,'Certified Information Security Manager (CISM)','Certified Information Security Manager (CISM)'),(13,'Certified Information Systems Auditor (CISA)','Certified Information Systems Auditor (CISA)'),(14,'CyberSecurity Forensic Analyst (CSFA)','CyberSecurity Forensic Analyst (CSFA)'),(15,'Open Group Certified Architect (OpenCA)','Open Group Certified Architect (OpenCA)'),(16,'Oracle DBA Administrator Certified Master OCM','Oracle DBA Administrator Certified Master OCM'),(17,'Project Management Professional','Project Management Professional'),(18,'Apple Certified Support Professional','Apple Certified Support Professional'),(19,'Certified Public Accountant (CPA)','Certified Public Accountant (CPA)'),(20,'Chartered Financial Analyst','Chartered Financial Analyst'),(21,'Professional in Human Resources (PHR)','Professional in Human Resources (PHR)');
/*!40000 ALTER TABLE `Certifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Clients`
--

DROP TABLE IF EXISTS `Clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Clients` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text,
  `first_contact_date` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `address` text,
  `contact_number` varchar(25) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `company_url` varchar(500) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Clients`
--

LOCK TABLES `Clients` WRITE;
/*!40000 ALTER TABLE `Clients` DISABLE KEYS */;
INSERT INTO `Clients` VALUES (1,'IceHrm Sample Client 1',NULL,'2012-01-04','2013-01-03 05:47:33','001, Sample Road,\nSample City, USA','678-894-1047','icehrm+client1@web-stalk.com','http://icehrm.com','Active'),(2,'IceHrm Sample Client 2',NULL,'2012-01-04','2013-01-03 05:47:33','001, Sample Road,\nSample City, USA','678-894-1047','icehrm+client1@web-stalk.com','http://icehrm.com','Active'),(3,'IceHrm Sample Client 3',NULL,'2012-01-04','2013-01-03 05:47:33','001, Sample Road,\nSample City, USA','678-894-1047','icehrm+client1@web-stalk.com','http://icehrm.com','Active');
/*!40000 ALTER TABLE `Clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CompanyAssets`
--

DROP TABLE IF EXISTS `CompanyAssets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CompanyAssets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `type` bigint(20) DEFAULT NULL,
  `attachment` varchar(100) DEFAULT NULL,
  `employee` bigint(20) DEFAULT NULL,
  `department` bigint(20) DEFAULT NULL,
  `description` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_CompanyAssets_AssetTypes` (`type`),
  KEY `Fk_CompanyAssets_Employees` (`employee`),
  KEY `Fk_CompanyAssets_CompanyStructures` (`department`),
  CONSTRAINT `Fk_CompanyAssets_AssetTypes` FOREIGN KEY (`type`) REFERENCES `AssetTypes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_CompanyAssets_CompanyStructures` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_CompanyAssets_Employees` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CompanyAssets`
--

LOCK TABLES `CompanyAssets` WRITE;
/*!40000 ALTER TABLE `CompanyAssets` DISABLE KEYS */;
/*!40000 ALTER TABLE `CompanyAssets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CompanyDocuments`
--

DROP TABLE IF EXISTS `CompanyDocuments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CompanyDocuments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text,
  `valid_until` date DEFAULT NULL,
  `status` enum('Active','Inactive','Draft') DEFAULT 'Active',
  `notify_employees` enum('Yes','No') DEFAULT 'Yes',
  `attachment` varchar(100) DEFAULT NULL,
  `share_departments` varchar(100) DEFAULT NULL,
  `share_employees` varchar(100) DEFAULT NULL,
  `share_userlevel` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CompanyDocuments`
--

LOCK TABLES `CompanyDocuments` WRITE;
/*!40000 ALTER TABLE `CompanyDocuments` DISABLE KEYS */;
/*!40000 ALTER TABLE `CompanyDocuments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CompanyLoans`
--

DROP TABLE IF EXISTS `CompanyLoans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CompanyLoans` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CompanyLoans`
--

LOCK TABLES `CompanyLoans` WRITE;
/*!40000 ALTER TABLE `CompanyLoans` DISABLE KEYS */;
INSERT INTO `CompanyLoans` VALUES (1,'Personal loan','Personal loans'),(2,'Educational loan','Educational loan');
/*!40000 ALTER TABLE `CompanyLoans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CompanyStructures`
--

DROP TABLE IF EXISTS `CompanyStructures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CompanyStructures` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` tinytext NOT NULL,
  `description` text NOT NULL,
  `address` text,
  `type` enum('Company','Head Office','Regional Office','Department','Unit','Sub Unit','Other') DEFAULT NULL,
  `country` varchar(2) NOT NULL DEFAULT '0',
  `parent` bigint(20) DEFAULT NULL,
  `timezone` varchar(100) NOT NULL DEFAULT 'Europe/London',
  `heads` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_CompanyStructures_Own` (`parent`),
  CONSTRAINT `Fk_CompanyStructures_Own` FOREIGN KEY (`parent`) REFERENCES `CompanyStructures` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CompanyStructures`
--

LOCK TABLES `CompanyStructures` WRITE;
/*!40000 ALTER TABLE `CompanyStructures` DISABLE KEYS */;
INSERT INTO `CompanyStructures` VALUES (1,'Your Company','Please update your company name here. You can update, delete or add units according to your needs','','Company','US',NULL,'Europe/London',NULL),(2,'Head Office','US Head office','PO Box 001002\nSample Road, Sample Town','Head Office','US',1,'Europe/London',NULL),(3,'Marketing Department','Marketing Department','PO Box 001002\nSample Road, Sample Town','Department','US',2,'Europe/London',NULL),(4,'Development Center','Development Center','PO Box 001002\nSample Road, Sample Town','Regional Office','SG',1,'Europe/London',NULL),(5,'Engineering Department','Engineering Department','PO Box 001002\nSample Road, Sample Town,  341234','Department','SG',4,'Europe/London',NULL),(6,'Development Team','Development Team','','Unit','SG',5,'Europe/London',NULL),(7,'QA Team','QA Team','','Unit','SG',5,'Europe/London',NULL),(8,'Server Administration','Server Administration','','Unit','SG',5,'Europe/London',NULL),(9,'Administration & HR','Administration and Human Resource','','Department','SG',4,'Europe/London',NULL);
/*!40000 ALTER TABLE `CompanyStructures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ConversationUserStatus`
--

DROP TABLE IF EXISTS `ConversationUserStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ConversationUserStatus` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `status` varchar(15) DEFAULT NULL,
  `seen_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `KEY_ConversationLastSeen_employee` (`employee`),
  KEY `KEY_ConversationLastSeen_seen_at` (`seen_at`),
  KEY `KEY_ConversationLastSeen_status` (`seen_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ConversationUserStatus`
--

LOCK TABLES `ConversationUserStatus` WRITE;
/*!40000 ALTER TABLE `ConversationUserStatus` DISABLE KEYS */;
/*!40000 ALTER TABLE `ConversationUserStatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Conversations`
--

DROP TABLE IF EXISTS `Conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Conversations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `message` longtext NOT NULL,
  `type` varchar(35) NOT NULL,
  `attachment` varchar(100) DEFAULT NULL,
  `employee` bigint(20) NOT NULL,
  `target` bigint(20) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `timeint` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `KEY_Conversations_attachment` (`attachment`),
  KEY `KEY_Conversations_type` (`type`),
  KEY `KEY_Conversations_employee` (`employee`),
  KEY `KEY_Conversations_target` (`target`),
  KEY `KEY_Conversations_target_type` (`target`,`type`),
  KEY `KEY_Conversations_timeint` (`timeint`),
  KEY `KEY_Conversations_timeint_type` (`timeint`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Conversations`
--

LOCK TABLES `Conversations` WRITE;
/*!40000 ALTER TABLE `Conversations` DISABLE KEYS */;
/*!40000 ALTER TABLE `Conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Country`
--

DROP TABLE IF EXISTS `Country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Country` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` char(2) NOT NULL DEFAULT '',
  `namecap` varchar(80) DEFAULT '',
  `name` varchar(80) NOT NULL DEFAULT '',
  `iso3` char(3) DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Country`
--

LOCK TABLES `Country` WRITE;
/*!40000 ALTER TABLE `Country` DISABLE KEYS */;
INSERT INTO `Country` VALUES (1,'AF','AFGHANISTAN','Afghanistan','AFG',4),(2,'AL','ALBANIA','Albania','ALB',8),(3,'DZ','ALGERIA','Algeria','DZA',12),(4,'AS','AMERICAN SAMOA','American Samoa','ASM',16),(5,'AD','ANDORRA','Andorra','AND',20),(6,'AO','ANGOLA','Angola','AGO',24),(7,'AI','ANGUILLA','Anguilla','AIA',660),(8,'AQ','ANTARCTICA','Antarctica',NULL,NULL),(9,'AG','ANTIGUA AND BARBUDA','Antigua and Barbuda','ATG',28),(10,'AR','ARGENTINA','Argentina','ARG',32),(11,'AM','ARMENIA','Armenia','ARM',51),(12,'AW','ARUBA','Aruba','ABW',533),(13,'AU','AUSTRALIA','Australia','AUS',36),(14,'AT','AUSTRIA','Austria','AUT',40),(15,'AZ','AZERBAIJAN','Azerbaijan','AZE',31),(16,'BS','BAHAMAS','Bahamas','BHS',44),(17,'BH','BAHRAIN','Bahrain','BHR',48),(18,'BD','BANGLADESH','Bangladesh','BGD',50),(19,'BB','BARBADOS','Barbados','BRB',52),(20,'BY','BELARUS','Belarus','BLR',112),(21,'BE','BELGIUM','Belgium','BEL',56),(22,'BZ','BELIZE','Belize','BLZ',84),(23,'BJ','BENIN','Benin','BEN',204),(24,'BM','BERMUDA','Bermuda','BMU',60),(25,'BT','BHUTAN','Bhutan','BTN',64),(26,'BO','BOLIVIA','Bolivia','BOL',68),(27,'BA','BOSNIA AND HERZEGOVINA','Bosnia and Herzegovina','BIH',70),(28,'BW','BOTSWANA','Botswana','BWA',72),(29,'BV','BOUVET ISLAND','Bouvet Island',NULL,NULL),(30,'BR','BRAZIL','Brazil','BRA',76),(31,'IO','BRITISH INDIAN OCEAN TERRITORY','British Indian Ocean Territory',NULL,NULL),(32,'BN','BRUNEI DARUSSALAM','Brunei Darussalam','BRN',96),(33,'BG','BULGARIA','Bulgaria','BGR',100),(34,'BF','BURKINA FASO','Burkina Faso','BFA',854),(35,'BI','BURUNDI','Burundi','BDI',108),(36,'KH','CAMBODIA','Cambodia','KHM',116),(37,'CM','CAMEROON','Cameroon','CMR',120),(38,'CA','CANADA','Canada','CAN',124),(39,'CV','CAPE VERDE','Cape Verde','CPV',132),(40,'KY','CAYMAN ISLANDS','Cayman Islands','CYM',136),(41,'CF','CENTRAL AFRICAN REPUBLIC','Central African Republic','CAF',140),(42,'TD','CHAD','Chad','TCD',148),(43,'CL','CHILE','Chile','CHL',152),(44,'CN','CHINA','China','CHN',156),(45,'CX','CHRISTMAS ISLAND','Christmas Island',NULL,NULL),(46,'CC','COCOS (KEELING) ISLANDS','Cocos (Keeling) Islands',NULL,NULL),(47,'CO','COLOMBIA','Colombia','COL',170),(48,'KM','COMOROS','Comoros','COM',174),(49,'CG','CONGO','Congo','COG',178),(50,'CD','CONGO, THE DEMOCRATIC REPUBLIC OF THE','Congo, the Democratic Republic of the','COD',180),(51,'CK','COOK ISLANDS','Cook Islands','COK',184),(52,'CR','COSTA RICA','Costa Rica','CRI',188),(53,'CI','COTE D\'IVOIRE','Cote D\'Ivoire','CIV',384),(54,'HR','CROATIA','Croatia','HRV',191),(55,'CU','CUBA','Cuba','CUB',192),(56,'CY','CYPRUS','Cyprus','CYP',196),(57,'CZ','CZECH REPUBLIC','Czech Republic','CZE',203),(58,'DK','DENMARK','Denmark','DNK',208),(59,'DJ','DJIBOUTI','Djibouti','DJI',262),(60,'DM','DOMINICA','Dominica','DMA',212),(61,'DO','DOMINICAN REPUBLIC','Dominican Republic','DOM',214),(62,'EC','ECUADOR','Ecuador','ECU',218),(63,'EG','EGYPT','Egypt','EGY',818),(64,'SV','EL SALVADOR','El Salvador','SLV',222),(65,'GQ','EQUATORIAL GUINEA','Equatorial Guinea','GNQ',226),(66,'ER','ERITREA','Eritrea','ERI',232),(67,'EE','ESTONIA','Estonia','EST',233),(68,'ET','ETHIOPIA','Ethiopia','ETH',231),(69,'FK','FALKLAND ISLANDS (MALVINAS)','Falkland Islands (Malvinas)','FLK',238),(70,'FO','FAROE ISLANDS','Faroe Islands','FRO',234),(71,'FJ','FIJI','Fiji','FJI',242),(72,'FI','FINLAND','Finland','FIN',246),(73,'FR','FRANCE','France','FRA',250),(74,'GF','FRENCH GUIANA','French Guiana','GUF',254),(75,'PF','FRENCH POLYNESIA','French Polynesia','PYF',258),(76,'TF','FRENCH SOUTHERN TERRITORIES','French Southern Territories',NULL,NULL),(77,'GA','GABON','Gabon','GAB',266),(78,'GM','GAMBIA','Gambia','GMB',270),(79,'GE','GEORGIA','Georgia','GEO',268),(80,'DE','GERMANY','Germany','DEU',276),(81,'GH','GHANA','Ghana','GHA',288),(82,'GI','GIBRALTAR','Gibraltar','GIB',292),(83,'GR','GREECE','Greece','GRC',300),(84,'GL','GREENLAND','Greenland','GRL',304),(85,'GD','GRENADA','Grenada','GRD',308),(86,'GP','GUADELOUPE','Guadeloupe','GLP',312),(87,'GU','GUAM','Guam','GUM',316),(88,'GT','GUATEMALA','Guatemala','GTM',320),(89,'GN','GUINEA','Guinea','GIN',324),(90,'GW','GUINEA-BISSAU','Guinea-Bissau','GNB',624),(91,'GY','GUYANA','Guyana','GUY',328),(92,'HT','HAITI','Haiti','HTI',332),(93,'HM','HEARD ISLAND AND MCDONALD ISLANDS','Heard Island and Mcdonald Islands',NULL,NULL),(94,'VA','HOLY SEE (VATICAN CITY STATE)','Holy See (Vatican City State)','VAT',336),(95,'HN','HONDURAS','Honduras','HND',340),(96,'HK','HONG KONG','Hong Kong','HKG',344),(97,'HU','HUNGARY','Hungary','HUN',348),(98,'IS','ICELAND','Iceland','ISL',352),(99,'IN','INDIA','India','IND',356),(100,'ID','INDONESIA','Indonesia','IDN',360),(101,'IR','IRAN, ISLAMIC REPUBLIC OF','Iran, Islamic Republic of','IRN',364),(102,'IQ','IRAQ','Iraq','IRQ',368),(103,'IE','IRELAND','Ireland','IRL',372),(104,'IL','ISRAEL','Israel','ISR',376),(105,'IT','ITALY','Italy','ITA',380),(106,'JM','JAMAICA','Jamaica','JAM',388),(107,'JP','JAPAN','Japan','JPN',392),(108,'JO','JORDAN','Jordan','JOR',400),(109,'KZ','KAZAKHSTAN','Kazakhstan','KAZ',398),(110,'KE','KENYA','Kenya','KEN',404),(111,'KI','KIRIBATI','Kiribati','KIR',296),(112,'KP','KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF','Korea, Democratic People\'s Republic of','PRK',408),(113,'KR','KOREA, REPUBLIC OF','Korea, Republic of','KOR',410),(114,'KW','KUWAIT','Kuwait','KWT',414),(115,'KG','KYRGYZSTAN','Kyrgyzstan','KGZ',417),(116,'LA','LAO PEOPLE\'S DEMOCRATIC REPUBLIC','Lao People\'s Democratic Republic','LAO',418),(117,'LV','LATVIA','Latvia','LVA',428),(118,'LB','LEBANON','Lebanon','LBN',422),(119,'LS','LESOTHO','Lesotho','LSO',426),(120,'LR','LIBERIA','Liberia','LBR',430),(121,'LY','LIBYAN ARAB JAMAHIRIYA','Libyan Arab Jamahiriya','LBY',434),(122,'LI','LIECHTENSTEIN','Liechtenstein','LIE',438),(123,'LT','LITHUANIA','Lithuania','LTU',440),(124,'LU','LUXEMBOURG','Luxembourg','LUX',442),(125,'MO','MACAO','Macao','MAC',446),(126,'MK','MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','Macedonia, the Former Yugoslav Republic of','MKD',807),(127,'MG','MADAGASCAR','Madagascar','MDG',450),(128,'MW','MALAWI','Malawi','MWI',454),(129,'MY','MALAYSIA','Malaysia','MYS',458),(130,'MV','MALDIVES','Maldives','MDV',462),(131,'ML','MALI','Mali','MLI',466),(132,'MT','MALTA','Malta','MLT',470),(133,'MH','MARSHALL ISLANDS','Marshall Islands','MHL',584),(134,'MQ','MARTINIQUE','Martinique','MTQ',474),(135,'MR','MAURITANIA','Mauritania','MRT',478),(136,'MU','MAURITIUS','Mauritius','MUS',480),(137,'YT','MAYOTTE','Mayotte',NULL,NULL),(138,'MX','MEXICO','Mexico','MEX',484),(139,'FM','MICRONESIA, FEDERATED STATES OF','Micronesia, Federated States of','FSM',583),(140,'MD','MOLDOVA, REPUBLIC OF','Moldova, Republic of','MDA',498),(141,'MC','MONACO','Monaco','MCO',492),(142,'MN','MONGOLIA','Mongolia','MNG',496),(143,'MS','MONTSERRAT','Montserrat','MSR',500),(144,'MA','MOROCCO','Morocco','MAR',504),(145,'MZ','MOZAMBIQUE','Mozambique','MOZ',508),(146,'MM','MYANMAR','Myanmar','MMR',104),(147,'NA','NAMIBIA','Namibia','NAM',516),(148,'NR','NAURU','Nauru','NRU',520),(149,'NP','NEPAL','Nepal','NPL',524),(150,'NL','NETHERLANDS','Netherlands','NLD',528),(151,'AN','NETHERLANDS ANTILLES','Netherlands Antilles','ANT',530),(152,'NC','NEW CALEDONIA','New Caledonia','NCL',540),(153,'NZ','NEW ZEALAND','New Zealand','NZL',554),(154,'NI','NICARAGUA','Nicaragua','NIC',558),(155,'NE','NIGER','Niger','NER',562),(156,'NG','NIGERIA','Nigeria','NGA',566),(157,'NU','NIUE','Niue','NIU',570),(158,'NF','NORFOLK ISLAND','Norfolk Island','NFK',574),(159,'MP','NORTHERN MARIANA ISLANDS','Northern Mariana Islands','MNP',580),(160,'NO','NORWAY','Norway','NOR',578),(161,'OM','OMAN','Oman','OMN',512),(162,'PK','PAKISTAN','Pakistan','PAK',586),(163,'PW','PALAU','Palau','PLW',585),(164,'PS','PALESTINIAN TERRITORY, OCCUPIED','Palestinian Territory, Occupied',NULL,NULL),(165,'PA','PANAMA','Panama','PAN',591),(166,'PG','PAPUA NEW GUINEA','Papua New Guinea','PNG',598),(167,'PY','PARAGUAY','Paraguay','PRY',600),(168,'PE','PERU','Peru','PER',604),(169,'PH','PHILIPPINES','Philippines','PHL',608),(170,'PN','PITCAIRN','Pitcairn','PCN',612),(171,'PL','POLAND','Poland','POL',616),(172,'PT','PORTUGAL','Portugal','PRT',620),(173,'PR','PUERTO RICO','Puerto Rico','PRI',630),(174,'QA','QATAR','Qatar','QAT',634),(175,'RE','REUNION','Reunion','REU',638),(176,'RO','ROMANIA','Romania','ROM',642),(177,'RU','RUSSIAN FEDERATION','Russian Federation','RUS',643),(178,'RW','RWANDA','Rwanda','RWA',646),(179,'SH','SAINT HELENA','Saint Helena','SHN',654),(180,'KN','SAINT KITTS AND NEVIS','Saint Kitts and Nevis','KNA',659),(181,'LC','SAINT LUCIA','Saint Lucia','LCA',662),(182,'PM','SAINT PIERRE AND MIQUELON','Saint Pierre and Miquelon','SPM',666),(183,'VC','SAINT VINCENT AND THE GRENADINES','Saint Vincent and the Grenadines','VCT',670),(184,'WS','SAMOA','Samoa','WSM',882),(185,'SM','SAN MARINO','San Marino','SMR',674),(186,'ST','SAO TOME AND PRINCIPE','Sao Tome and Principe','STP',678),(187,'SA','SAUDI ARABIA','Saudi Arabia','SAU',682),(188,'SN','SENEGAL','Senegal','SEN',686),(189,'CS','SERBIA AND MONTENEGRO','Serbia and Montenegro',NULL,NULL),(190,'SC','SEYCHELLES','Seychelles','SYC',690),(191,'SL','SIERRA LEONE','Sierra Leone','SLE',694),(192,'SG','SINGAPORE','Singapore','SGP',702),(193,'SK','SLOVAKIA','Slovakia','SVK',703),(194,'SI','SLOVENIA','Slovenia','SVN',705),(195,'SB','SOLOMON ISLANDS','Solomon Islands','SLB',90),(196,'SO','SOMALIA','Somalia','SOM',706),(197,'ZA','SOUTH AFRICA','South Africa','ZAF',710),(198,'GS','SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS','South Georgia and the South Sandwich Islands',NULL,NULL),(199,'ES','SPAIN','Spain','ESP',724),(200,'LK','SRI LANKA','Sri Lanka','LKA',144),(201,'SD','SUDAN','Sudan','SDN',736),(202,'SR','SURINAME','Suriname','SUR',740),(203,'SJ','SVALBARD AND JAN MAYEN','Svalbard and Jan Mayen','SJM',744),(204,'SZ','SWAZILAND','Swaziland','SWZ',748),(205,'SE','SWEDEN','Sweden','SWE',752),(206,'CH','SWITZERLAND','Switzerland','CHE',756),(207,'SY','SYRIAN ARAB REPUBLIC','Syrian Arab Republic','SYR',760),(208,'TW','TAIWAN, PROVINCE OF CHINA','Taiwan','TWN',158),(209,'TJ','TAJIKISTAN','Tajikistan','TJK',762),(210,'TZ','TANZANIA, UNITED REPUBLIC OF','Tanzania, United Republic of','TZA',834),(211,'TH','THAILAND','Thailand','THA',764),(212,'TL','TIMOR-LESTE','Timor-Leste',NULL,NULL),(213,'TG','TOGO','Togo','TGO',768),(214,'TK','TOKELAU','Tokelau','TKL',772),(215,'TO','TONGA','Tonga','TON',776),(216,'TT','TRINIDAD AND TOBAGO','Trinidad and Tobago','TTO',780),(217,'TN','TUNISIA','Tunisia','TUN',788),(218,'TR','TURKEY','Turkey','TUR',792),(219,'TM','TURKMENISTAN','Turkmenistan','TKM',795),(220,'TC','TURKS AND CAICOS ISLANDS','Turks and Caicos Islands','TCA',796),(221,'TV','TUVALU','Tuvalu','TUV',798),(222,'UG','UGANDA','Uganda','UGA',800),(223,'UA','UKRAINE','Ukraine','UKR',804),(224,'AE','UNITED ARAB EMIRATES','United Arab Emirates','ARE',784),(225,'GB','UNITED KINGDOM','United Kingdom','GBR',826),(226,'US','UNITED STATES','United States','USA',840),(227,'UM','UNITED STATES MINOR OUTLYING ISLANDS','United States Minor Outlying Islands',NULL,NULL),(228,'UY','URUGUAY','Uruguay','URY',858),(229,'UZ','UZBEKISTAN','Uzbekistan','UZB',860),(230,'VU','VANUATU','Vanuatu','VUT',548),(231,'VE','VENEZUELA','Venezuela','VEN',862),(232,'VN','VIET NAM','Viet Nam','VNM',704),(233,'VG','VIRGIN ISLANDS, BRITISH','Virgin Islands, British','VGB',92),(234,'VI','VIRGIN ISLANDS, U.S.','Virgin Islands, U.s.','VIR',850),(235,'WF','WALLIS AND FUTUNA','Wallis and Futuna','WLF',876),(236,'EH','WESTERN SAHARA','Western Sahara','ESH',732),(237,'YE','YEMEN','Yemen','YEM',887),(238,'ZM','ZAMBIA','Zambia','ZMB',894),(239,'ZW','ZIMBABWE','Zimbabwe','ZWE',716);
/*!40000 ALTER TABLE `Country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Courses`
--

DROP TABLE IF EXISTS `Courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Courses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(300) NOT NULL,
  `name` varchar(300) NOT NULL,
  `description` text,
  `coordinator` bigint(20) DEFAULT NULL,
  `trainer` varchar(300) DEFAULT NULL,
  `trainer_info` text,
  `paymentType` enum('Company Sponsored','Paid by Employee') DEFAULT 'Company Sponsored',
  `currency` varchar(3) DEFAULT NULL,
  `cost` decimal(12,2) DEFAULT '0.00',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_Courses_Employees` (`coordinator`),
  CONSTRAINT `Fk_Courses_Employees` FOREIGN KEY (`coordinator`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Courses`
--

LOCK TABLES `Courses` WRITE;
/*!40000 ALTER TABLE `Courses` DISABLE KEYS */;
INSERT INTO `Courses` VALUES (1,'C0001','Info Marketing','Learn how to Create and Outsource Info Marketing Products',1,'Tim Jhon','Tim Jhon has a background in business management and has been working with small business to establish their online presence','Company Sponsored','USD',55.00,'Active','2020-06-09 16:05:20','2020-06-09 16:05:20'),(2,'C0002','People Management','Learn how to Manage People',1,'Tim Jhon','Tim Jhon has a background in business management and has been working with small business to establish their online presence','Company Sponsored','USD',59.00,'Active','2020-06-09 16:05:20','2020-06-09 16:05:20');
/*!40000 ALTER TABLE `Courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Crons`
--

DROP TABLE IF EXISTS `Crons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Crons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `class` varchar(100) NOT NULL,
  `lastrun` datetime DEFAULT NULL,
  `frequency` int(4) NOT NULL,
  `time` varchar(50) NOT NULL,
  `type` enum('Minutely','Hourly','Daily','Weekly','Monthly','Yearly') DEFAULT 'Hourly',
  `status` enum('Enabled','Disabled') DEFAULT 'Enabled',
  PRIMARY KEY (`id`),
  UNIQUE KEY `KEY_Crons_name` (`name`),
  KEY `KEY_Crons_frequency` (`frequency`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Crons`
--

LOCK TABLES `Crons` WRITE;
/*!40000 ALTER TABLE `Crons` DISABLE KEYS */;
INSERT INTO `Crons` VALUES (1,'Email Sender Task','EmailSenderTask','2020-06-09 23:07:18',1,'1','Minutely','Enabled'),(2,'Document Expire Alert','DocumentExpiryNotificationTask','2020-06-09 23:07:18',1,'50','Hourly','Enabled'),(3,'Candidate Email Sender','NewCandidateEmailTask','2020-06-09 23:07:18',1,'1','Minutely','Enabled'),(4,'Recruitment Email Sender','RecruitmentEmailTask','2020-06-09 23:07:18',1,'1','Minutely','Enabled');
/*!40000 ALTER TABLE `Crons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CurrencyTypes`
--

DROP TABLE IF EXISTS `CurrencyTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CurrencyTypes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL DEFAULT '',
  `name` varchar(70) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `CurrencyTypes_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CurrencyTypes`
--

LOCK TABLES `CurrencyTypes` WRITE;
/*!40000 ALTER TABLE `CurrencyTypes` DISABLE KEYS */;
INSERT INTO `CurrencyTypes` VALUES (3,'AED','Utd. Arab Emir. Dirham'),(4,'AFN','Afghanistan Afghani'),(5,'ALL','Albanian Lek'),(6,'ANG','NL Antillian Guilder'),(7,'AOR','Angolan New Kwanza'),(8,'ARS','Argentine Peso'),(10,'AUD','Australian Dollar'),(11,'AWG','Aruban Florin'),(12,'BBD','Barbados Dollar'),(13,'BDT','Bangladeshi Taka'),(15,'BGL','Bulgarian Lev'),(16,'BHD','Bahraini Dinar'),(17,'BIF','Burundi Franc'),(18,'BMD','Bermudian Dollar'),(19,'BND','Brunei Dollar'),(20,'BOB','Bolivian Boliviano'),(21,'BRL','Brazilian Real'),(22,'BSD','Bahamian Dollar'),(23,'BTN','Bhutan Ngultrum'),(24,'BWP','Botswana Pula'),(25,'BZD','Belize Dollar'),(26,'CAD','Canadian Dollar'),(27,'CHF','Swiss Franc'),(28,'CLP','Chilean Peso'),(29,'CNY','Chinese Yuan Renminbi'),(30,'COP','Colombian Peso'),(31,'CRC','Costa Rican Colon'),(32,'CUP','Cuban Peso'),(33,'CVE','Cape Verde Escudo'),(34,'CYP','Cyprus Pound'),(37,'DJF','Djibouti Franc'),(38,'DKK','Danish Krona'),(39,'DOP','Dominican Peso'),(40,'DZD','Algerian Dinar'),(41,'ECS','Ecuador Sucre'),(42,'EUR','Euro'),(43,'EEK','Estonian Krona'),(44,'EGP','Egyptian Pound'),(46,'ETB','Ethiopian Birr'),(48,'FJD','Fiji Dollar'),(49,'FKP','Falkland Islands Pound'),(51,'GBP','Pound Sterling'),(52,'GHC','Ghanaian Cedi'),(53,'GIP','Gibraltar Pound'),(54,'GMD','Gambian Dalasi'),(55,'GNF','Guinea Franc'),(57,'GTQ','Guatemalan Quetzal'),(58,'GYD','Guyanan Dollar'),(59,'HKD','Hong Kong Dollar'),(60,'HNL','Honduran Lempira'),(61,'HRK','Croatian Kuna'),(62,'HTG','Haitian Gourde'),(63,'HUF','Hungarian Forint'),(64,'IDR','Indonesian Rupiah'),(66,'ILS','Israeli New Shekel'),(67,'INR','Indian Rupee'),(68,'IQD','Iraqi Dinar'),(69,'IRR','Iranian Rial'),(70,'ISK','Iceland Krona'),(72,'JMD','Jamaican Dollar'),(73,'JOD','Jordanian Dinar'),(74,'JPY','Japanese Yen'),(75,'KES','Kenyan Shilling'),(76,'KHR','Kampuchean Riel'),(77,'KMF','Comoros Franc'),(78,'KPW','North Korean Won'),(79,'KRW','Korean Won'),(80,'KWD','Kuwaiti Dinar'),(81,'KYD','Cayman Islands Dollar'),(82,'KZT','Kazakhstan Tenge'),(83,'LAK','Lao Kip'),(84,'LBP','Lebanese Pound'),(85,'LKR','Sri Lanka Rupee'),(86,'LRD','Liberian Dollar'),(87,'LSL','Lesotho Loti'),(88,'LTL','Lithuanian Litas'),(90,'LVL','Latvian Lats'),(91,'LYD','Libyan Dinar'),(92,'MAD','Moroccan Dirham'),(93,'MGF','Malagasy Franc'),(94,'MMK','Myanmar Kyat'),(95,'MNT','Mongolian Tugrik'),(96,'MOP','Macau Pataca'),(97,'MRO','Mauritanian Ouguiya'),(98,'MTL','Maltese Lira'),(99,'MUR','Mauritius Rupee'),(100,'MVR','Maldive Rufiyaa'),(101,'MWK','Malawi Kwacha'),(102,'MXN','Mexican New Peso'),(103,'MYR','Malaysian Ringgit'),(104,'MZM','Mozambique Metical'),(105,'NAD','Namibia Dollar'),(106,'NGN','Nigerian Naira'),(107,'NIO','Nicaraguan Cordoba Oro'),(109,'NOK','Norwegian Krona'),(110,'NPR','Nepalese Rupee'),(111,'NZD','New Zealand Dollar'),(112,'OMR','Omani Rial'),(113,'PAB','Panamanian Balboa'),(114,'PEN','Peruvian Nuevo Sol'),(115,'PGK','Papua New Guinea Kina'),(116,'PHP','Philippine Peso'),(117,'PKR','Pakistan Rupee'),(118,'PLN','Polish Zloty'),(120,'PYG','Paraguay Guarani'),(121,'QAR','Qatari Rial'),(122,'ROL','Romanian Leu'),(123,'RUB','Russian Rouble'),(125,'SBD','Solomon Islands Dollar'),(126,'SCR','Seychelles Rupee'),(127,'SDD','Sudanese Dinar'),(128,'SDP','Sudanese Pound'),(129,'SEK','Swedish Krona'),(130,'SKK','Slovak Koruna'),(131,'SGD','Singapore Dollar'),(132,'SHP','St. Helena Pound'),(135,'SLL','Sierra Leone Leone'),(136,'SOS','Somali Shilling'),(137,'SRD','Surinamese Dollar'),(138,'STD','Sao Tome/Principe Dobra'),(139,'SVC','El Salvador Colon'),(140,'SYP','Syrian Pound'),(141,'SZL','Swaziland Lilangeni'),(142,'THB','Thai Baht'),(143,'TND','Tunisian Dinar'),(144,'TOP','Tongan Pa\'anga'),(145,'TRL','Turkish Lira'),(146,'TTD','Trinidad/Tobago Dollar'),(147,'TWD','Taiwan Dollar'),(148,'TZS','Tanzanian Shilling'),(149,'UAH','Ukraine Hryvnia'),(150,'UGX','Uganda Shilling'),(151,'USD','United States Dollar'),(152,'UYP','Uruguayan Peso'),(153,'VEB','Venezuelan Bolivar'),(154,'VND','Vietnamese Dong'),(155,'VUV','Vanuatu Vatu'),(156,'WST','Samoan Tala'),(158,'XAF','CFA Franc BEAC'),(159,'XAG','Silver (oz.)'),(160,'XAU','Gold (oz.)'),(161,'XCD','Eastern Caribbean Dollars'),(162,'XOF','CFA Franc BCEAO'),(163,'XPD','Palladium (oz.)'),(164,'XPF','CFP Franc'),(165,'XPT','Platinum (oz.)'),(166,'YER','Yemeni Riyal'),(167,'YUM','Yugoslavian Dinar'),(168,'ZAR','South African Rand'),(169,'ZRN','New Zaire'),(170,'ZWD','Zimbabwe Dollar'),(171,'CZK','Czech Koruna'),(172,'MXP','Mexican Peso'),(173,'SAR','Saudi Arabia Riyal'),(175,'YUN','Yugoslav Dinar'),(176,'ZMK','Zambian Kwacha'),(177,'ARP','Argentina Pesos'),(179,'XDR','IMF Special Drawing Right'),(180,'RUR','Russia Rubles');
/*!40000 ALTER TABLE `CurrencyTypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CustomFieldValues`
--

DROP TABLE IF EXISTS `CustomFieldValues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CustomFieldValues` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `object_id` varchar(60) NOT NULL,
  `value` text,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CustomFields_type_name_object_id` (`type`,`name`,`object_id`),
  KEY `CustomFields_type_object_id` (`type`,`object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CustomFieldValues`
--

LOCK TABLES `CustomFieldValues` WRITE;
/*!40000 ALTER TABLE `CustomFieldValues` DISABLE KEYS */;
INSERT INTO `CustomFieldValues` VALUES (1,'EmployeeTimeEntry','date_select','1','2020-06-07','2020-11-03 04:02:55','2020-11-03 04:02:55'),(2,'User','csrf','5','5a0def444192086708310fe66c297d7b6a4ad769','2020-11-15 00:47:11','2020-11-15 00:47:11'),(3,'ReviewFeedback','q003','1','Good one','2020-11-15 00:54:45','2020-11-15 00:54:38'),(4,'ReviewFeedback','q003','2','Well good enough','2020-11-15 01:01:49','2020-11-15 00:58:50');
/*!40000 ALTER TABLE `CustomFieldValues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CustomFields`
--

DROP TABLE IF EXISTS `CustomFields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CustomFields` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `data` text,
  `display` enum('Form','Table and Form','Hidden') DEFAULT 'Form',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `field_type` varchar(20) DEFAULT NULL,
  `field_label` varchar(50) DEFAULT NULL,
  `field_validation` varchar(50) DEFAULT NULL,
  `field_options` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT '0',
  `display_section` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CustomFields_name` (`type`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CustomFields`
--

LOCK TABLES `CustomFields` WRITE;
/*!40000 ALTER TABLE `CustomFields` DISABLE KEYS */;
/*!40000 ALTER TABLE `CustomFields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DataEntryBackups`
--

DROP TABLE IF EXISTS `DataEntryBackups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DataEntryBackups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tableType` varchar(200) DEFAULT NULL,
  `data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DataEntryBackups`
--

LOCK TABLES `DataEntryBackups` WRITE;
/*!40000 ALTER TABLE `DataEntryBackups` DISABLE KEYS */;
INSERT INTO `DataEntryBackups` VALUES (1,'Employee','{\"oldObj\":null,\"oldObjOrig\":null,\"historyUpdateList\":[],\"historyFieldsToTrack\":{\"employee_id\":\"employee_id\",\"first_name\":\"first_name\",\"middle_name\":\"middle_name\",\"last_name\":\"last_name\",\"nationality\":\"nationality_Name\",\"birthday\":\"birthday\",\"gender\":\"gender\",\"marital_status\":\"marital_status\",\"ssn_num\":\"ssn_num\",\"nic_num\":\"nic_num\",\"other_id\":\"other_id\",\"employment_status\":\"employment_status_Name\",\"job_title\":\"job_title_Name\",\"pay_grade\":\"pay_grade_Name\",\"work_station_id\":\"work_station_id\",\"address1\":\"address1\",\"address2\":\"address2\",\"city\":\"city_Name\",\"country\":\"country_Name\",\"province\":\"province_Name\",\"postal_code\":\"postal_code\",\"home_phone\":\"home_phone\",\"mobile_phone\":\"mobile_phone\",\"work_phone\":\"work_phone\",\"work_email\":\"work_email\",\"private_email\":\"private_email\",\"joined_date\":\"joined_date\",\"confirmation_date\":\"confirmation_date\",\"supervisor\":\"supervisor_Name\",\"indirect_supervisors\":\"indirect_supervisors\",\"department\":\"department_Name\"},\"keysToIgnore\":[\"_table\",\"_dbat\",\"_tableat\",\"_where\",\"_saved\",\"_lasterr\",\"_original\",\"foreignName\",\"a\",\"t\"],\"id\":\"5\",\"employee_id\":\"D1012\",\"first_name\":\"IceHrm 11\",\"middle_name\":\"Sample Ex1\",\"last_name\":\"Employee\",\"nationality\":\"35\",\"birthday\":\"1984-03-17\",\"gender\":\"Male\",\"marital_status\":\"Married\",\"ssn_num\":\"\",\"nic_num\":\"294-38-3535\",\"other_id\":\"294-38-3535\",\"driving_license\":\"\",\"driving_license_exp_date\":null,\"employment_status\":\"3\",\"job_title\":\"11\",\"pay_grade\":\"2\",\"work_station_id\":\"\",\"address1\":\"2772 Flynn Street\",\"address2\":\"Willoughby\",\"city\":\"Willoughby\",\"country\":\"US\",\"province\":\"41\",\"postal_code\":\"44094\",\"home_phone\":\"440-953-4578\",\"mobile_phone\":\"440-953-4578\",\"work_phone\":\"440-953-4578\",\"work_email\":\"icehrm+admin@web-stalk.com\",\"private_email\":\"icehrm+admin@web-stalk.com\",\"joined_date\":\"2005-08-03\",\"confirmation_date\":null,\"supervisor\":\"2\",\"indirect_supervisors\":\"[\\\"3\\\",\\\"4\\\"]\",\"department\":\"1\",\"custom1\":null,\"custom2\":null,\"custom3\":null,\"custom4\":null,\"custom5\":null,\"custom6\":null,\"custom7\":null,\"custom8\":null,\"custom9\":null,\"custom10\":null,\"termination_date\":null,\"notes\":null,\"status\":\"Active\",\"ethnicity\":null,\"immigration_status\":null,\"approver1\":\"5\",\"approver2\":\"6\",\"approver3\":\"7\"}');
/*!40000 ALTER TABLE `DataEntryBackups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DataImport`
--

DROP TABLE IF EXISTS `DataImport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DataImport` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `dataType` varchar(60) NOT NULL,
  `details` text,
  `columns` text,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `objectType` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `KEY_DataImport_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DataImport`
--

LOCK TABLES `DataImport` WRITE;
/*!40000 ALTER TABLE `DataImport` DISABLE KEYS */;
INSERT INTO `DataImport` VALUES (1,'Employee Data Import','EmployeeDataImporter','','[{\"name\":\"employee_id\",\"title\":\"Employee ID\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"EMP05011\",\"help\":\"Employee ID\",\"id\":\"columns_7\"},{\"name\":\"first_name\",\"title\":\"First name\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"Chris\",\"help\":\"First name\",\"id\":\"columns_3\"},{\"name\":\"last_name\",\"title\":\"Last name\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"Doe\",\"help\":\"Last name\",\"id\":\"columns_6\"},{\"name\":\"address1\",\"title\":\"Address line 1\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"Abc Street\",\"help\":\"Address line 1\",\"id\":\"columns_8\"},{\"name\":\"address2\",\"title\":\"Address line 2\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"10\",\"help\":\"Address line 2\",\"id\":\"columns_9\"},{\"name\":\"home_phone\",\"title\":\"Home phone\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"+409 782324434\",\"help\":\"Home phone\",\"id\":\"columns_14\"},{\"name\":\"mobile_phone\",\"title\":\"Mobile phone\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"+49 176 4545454545\",\"help\":\"Mobile phone\",\"id\":\"columns_15\"},{\"name\":\"work_email\",\"title\":\"Work email\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"user@icehrm.com\",\"help\":\"Work email\",\"id\":\"columns_16\"},{\"name\":\"gender\",\"title\":\"Gender\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"Male\",\"help\":\"Allowed values (Male, Female)\",\"id\":\"columns_17\"},{\"name\":\"marital_status\",\"title\":\"Marital status\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"Single\",\"help\":\"Marital status\",\"id\":\"columns_18\"},{\"name\":\"birthday\",\"title\":\"Birthday\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"2003-12-15\",\"help\":\"Birthday\",\"id\":\"columns_20\"},{\"name\":\"nationality\",\"title\":\"Nationality\",\"type\":\"Reference\",\"dependOn\":\"Nationality\",\"dependOnField\":\"name\",\"idField\":\"No\",\"sampleValue\":\"Austrian\",\"help\":\"Name of a Nationality defined under System->Metadata\",\"id\":\"columns_22\"},{\"name\":\"ethnicity\",\"title\":\"Ethnicity\",\"type\":\"Reference\",\"dependOn\":\"Ethnicity\",\"dependOnField\":\"name\",\"idField\":\"No\",\"sampleValue\":\"Asian American\",\"help\":\"Name of an Ethnicity defined under System -> Metadata\",\"id\":\"columns_23\"},{\"name\":\"ssn_num\",\"title\":\"Social security number\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"34324903955WS\",\"help\":\"Social security number\",\"id\":\"columns_31\"},{\"name\":\"job_title\",\"title\":\"Job title\",\"type\":\"Reference\",\"dependOn\":\"JobTitle\",\"dependOnField\":\"name\",\"idField\":\"No\",\"sampleValue\":\"Software Engineer\",\"help\":\"A Job title defined under Admin -> Job Details Setup\",\"id\":\"columns_32\"},{\"name\":\"employment_status\",\"title\":\"Employment status\",\"type\":\"Reference\",\"dependOn\":\"EmploymentStatus\",\"dependOnField\":\"name\",\"idField\":\"No\",\"sampleValue\":\"Full Time\",\"help\":\"Employment status defined under Admin -> Job Details\",\"id\":\"columns_33\"},{\"name\":\"joined_date\",\"title\":\"Joined date\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"2015-04-17\",\"help\":\"Joined date (YYYY-MM-DD format)\",\"id\":\"columns_36\"},{\"name\":\"department\",\"title\":\"Department\",\"type\":\"Reference\",\"dependOn\":\"CompanyStructure\",\"dependOnField\":\"title\",\"idField\":\"No\",\"sampleValue\":\"Head Office\",\"help\":\"Name of a Department\",\"id\":\"columns_38\"}]','2020-06-09 22:23:52','2016-06-02 18:56:32',NULL),(2,'Attendance Data Import','AttendanceDataImporter','','[{\"name\":\"employee\",\"title\":\"Employee\",\"type\":\"Reference\",\"dependOn\":\"Employee\",\"dependOnField\":\"employee_id\",\"idField\":\"Yes\",\"sampleValue\":\"EMP050\",\"help\":\"Employee id of the employee of the attendance record\",\"id\":\"columns_1\"},{\"name\":\"in_time\",\"title\":\"In time\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"2019-11-06 08:15:00\",\"help\":\"Time in format YYYY-MM-DD hh:mm:ss (use 24 hour time)\",\"id\":\"columns_2\"},{\"name\":\"out_time\",\"title\":\"Out time\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"2019-11-06 15:18:00\",\"help\":\"Time in format YYYY-MM-DD hh:mm:ss (use 24 hour time)\",\"id\":\"columns_3\"},{\"name\":\"note\",\"title\":\"Note\",\"type\":\"Normal\",\"dependOn\":\"NULL\",\"dependOnField\":\"NULL\",\"idField\":\"No\",\"sampleValue\":\"Leaving a bit early today\",\"help\":\"Free text (optional)\",\"id\":\"columns_4\"}]','2020-06-09 22:23:52','2016-08-14 02:51:56',NULL),(3,'Payroll Data Import','PayrollDataImporter','','[]','2016-08-14 02:51:56','2016-08-14 02:51:56',NULL),(4,'Supervisor and Approver Import','EmployeeDataImporter','','[{\"name\":\"employee_id\",\"title\":\"Employee\",\"type\":\"Reference\",\"dependOn\":\"Employee\",\"dependOnField\":\"employee_id\",\"idField\":\"Yes\",\"sampleValue\":\"EMP050\",\"help\":\"Id of the employee to update approver details\",\"id\":\"columns_1\"},{\"name\":\"supervisor\",\"title\":\"Supervisor\",\"type\":\"Reference\",\"dependOn\":\"Employee\",\"dependOnField\":\"employee_id\",\"idField\":\"No\",\"sampleValue\":\"EMP004\",\"help\":\"Employee id of the supervisor\",\"id\":\"columns_6\"},{\"name\":\"approver1\",\"title\":\"Approver 1\",\"type\":\"Reference\",\"dependOn\":\"Employee\",\"dependOnField\":\"employee_id\",\"idField\":\"No\",\"sampleValue\":\"EMP001\",\"help\":\"Employee id of the first approver (can be empty)\",\"id\":\"columns_4\"},{\"name\":\"approver2\",\"title\":\"Approver 2\",\"type\":\"Reference\",\"dependOn\":\"Employee\",\"dependOnField\":\"employee_id\",\"idField\":\"No\",\"sampleValue\":\"EMP002\",\"help\":\"Employee id of the second approver (can be empty)\",\"id\":\"columns_3\"},{\"name\":\"approver3\",\"title\":\"Approver 3\",\"type\":\"Reference\",\"dependOn\":\"Employee\",\"dependOnField\":\"employee_id\",\"idField\":\"No\",\"sampleValue\":\"EMP003\",\"help\":\"Employee id of the third approver (can be empty)\",\"id\":\"columns_5\"}]','2020-06-09 22:23:52','2020-06-09 22:23:52',NULL);
/*!40000 ALTER TABLE `DataImport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DataImportFiles`
--

DROP TABLE IF EXISTS `DataImportFiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DataImportFiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `data_import_definition` varchar(200) NOT NULL,
  `status` varchar(15) DEFAULT NULL,
  `file` varchar(100) DEFAULT NULL,
  `details` text,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DataImportFiles`
--

LOCK TABLES `DataImportFiles` WRITE;
/*!40000 ALTER TABLE `DataImportFiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `DataImportFiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DeductionGroup`
--

DROP TABLE IF EXISTS `DeductionGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DeductionGroup` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DeductionGroup`
--

LOCK TABLES `DeductionGroup` WRITE;
/*!40000 ALTER TABLE `DeductionGroup` DISABLE KEYS */;
INSERT INTO `DeductionGroup` VALUES (1,'qw','');
/*!40000 ALTER TABLE `DeductionGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Deductions`
--

DROP TABLE IF EXISTS `Deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Deductions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `componentType` varchar(250) DEFAULT NULL,
  `component` varchar(250) DEFAULT NULL,
  `payrollColumn` int(11) DEFAULT NULL,
  `rangeAmounts` text,
  `deduction_group` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_Deductions_DeductionGroup` (`deduction_group`),
  CONSTRAINT `Fk_Deductions_DeductionGroup` FOREIGN KEY (`deduction_group`) REFERENCES `DeductionGroup` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Deductions`
--

LOCK TABLES `Deductions` WRITE;
/*!40000 ALTER TABLE `Deductions` DISABLE KEYS */;
/*!40000 ALTER TABLE `Deductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Documents`
--

DROP TABLE IF EXISTS `Documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Documents` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text,
  `expire_notification` enum('Yes','No') DEFAULT 'Yes',
  `expire_notification_month` enum('Yes','No') DEFAULT 'Yes',
  `expire_notification_week` enum('Yes','No') DEFAULT 'Yes',
  `expire_notification_day` enum('Yes','No') DEFAULT 'Yes',
  `sign` enum('Yes','No') DEFAULT 'Yes',
  `sign_label` varchar(500) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Documents`
--

LOCK TABLES `Documents` WRITE;
/*!40000 ALTER TABLE `Documents` DISABLE KEYS */;
INSERT INTO `Documents` VALUES (1,'ID Copy','Your ID copy','Yes','Yes','Yes','Yes','No',NULL,'2020-06-09 16:05:20','2020-06-09 16:05:20'),(2,'Degree Certificate','Degree Certificate','Yes','Yes','Yes','Yes','Yes',NULL,'2020-06-09 16:05:20','2020-06-09 16:05:20'),(3,'Driving License','Driving License','Yes','Yes','Yes','Yes','Yes',NULL,'2020-06-09 16:05:20','2020-06-09 16:05:20');
/*!40000 ALTER TABLE `Documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EducationLevel`
--

DROP TABLE IF EXISTS `EducationLevel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EducationLevel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EducationLevel`
--

LOCK TABLES `EducationLevel` WRITE;
/*!40000 ALTER TABLE `EducationLevel` DISABLE KEYS */;
INSERT INTO `EducationLevel` VALUES (1,'Unspecified'),(2,'High School or equivalent'),(3,'Certification'),(4,'Vocational'),(5,'Associate Degree'),(6,'Bachelor\'s Degree'),(7,'Master\'s Degree'),(8,'Doctorate'),(9,'Professional'),(10,'Some College Coursework Completed'),(11,'Vocational - HS Diploma'),(12,'Vocational - Degree'),(13,'Some High School Coursework');
/*!40000 ALTER TABLE `EducationLevel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Educations`
--

DROP TABLE IF EXISTS `Educations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Educations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Educations`
--

LOCK TABLES `Educations` WRITE;
/*!40000 ALTER TABLE `Educations` DISABLE KEYS */;
INSERT INTO `Educations` VALUES (1,'Bachelors Degree','Bachelors Degree'),(2,'Diploma','Diploma'),(3,'Masters Degree','Masters Degree'),(4,'Doctorate','Doctorate');
/*!40000 ALTER TABLE `Educations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmailLog`
--

DROP TABLE IF EXISTS `EmailLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmailLog` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subject` varchar(300) NOT NULL,
  `toEmail` varchar(300) NOT NULL,
  `body` text,
  `cclist` varchar(500) DEFAULT NULL,
  `bcclist` varchar(500) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` enum('Pending','Sent','Failed') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `KEY_EmailLog_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmailLog`
--

LOCK TABLES `EmailLog` WRITE;
/*!40000 ALTER TABLE `EmailLog` DISABLE KEYS */;
INSERT INTO `EmailLog` VALUES (1,'Leave Application Received','icehrm+manager@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear Lala Lamees,<br/><br/>\nYour Direct Subordinate IceHrm Employee, has submitted a leave application.<br/><br/>\nDepartment: Your Company  <br/>\nLeave Type: Annual leave <br/>\nLeave Start Date:  2020-11-01                 Leave End Date: 2020-11-02 <br/>\nLeave Days:  1 <br/>\nReason for the leave request:  <br/>\n<table>\n                   <thead><tr><th>Leave Date</th><th>Leave Type</th></tr></thead><tbody><tr><td>2020-11-02</td><td>Full Day</td></tr></tbody></table>  <br>\nPlease login and take necessary actions.\n<br/>\n<br/>\nIceHrm Application Url: <a href=\"http://icehrm.test/app/\">http://icehrm.test/app/</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2020-11-08 11:00:37','2020-11-08 11:00:37','Failed'),(2,'Leave Application Submitted','icehrm+admin@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear IceHrm Employee,<br/><br/>\nYour leave application has been submitted for review.<br/> \nYou will be informed once it is approved or rejected.\n\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2020-11-08 11:00:37','2020-11-08 11:00:37','Failed'),(3,'IceHrm Notification from Performance Review','icehrm+user2@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Hello Taylor<br/><br/>\nIceHrm Employee started a performance review for you. Please fill in and submit the self-assessment before the due date\n<br/>\n<br/>\nVisit IceHrm: <a href=\"http://icehrm.test/app/?g=modules&n=performance&m=module_Performance\">http://icehrm.test/app/?g=modules&n=performance&m=module_Performance</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2020-11-15 00:21:39','2020-11-15 00:21:39','Failed'),(4,'IceHrm Notification from Feedback','icehrm+user3@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Hello Jane<br/><br/>\nLala Lamees requested you to provide a feedback for a performance review\n<br/>\n<br/>\nVisit IceHrm: <a href=\"http://icehrm.test/app/?g=modules&n=performance&m=module_Performance#tabReviewFeedback\">http://icehrm.test/app/?g=modules&n=performance&m=module_Performance#tabReviewFeedback</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2020-11-15 00:53:39','2020-11-15 00:53:39','Failed'),(5,'IceHrm Notification from Feedback','icehrm+manager@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Hello Lala<br/><br/>\nJane Welington submitted the performance review feedback\n<br/>\n<br/>\nVisit IceHrm: <a href=\"http://icehrm.test/app/?g=modules&n=performance&m=module_Performance#tabCoordinatedPerformanceReview\">http://icehrm.test/app/?g=modules&n=performance&m=module_Performance#tabCoordinatedPerformanceReview</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2020-11-15 00:54:45','2020-11-15 00:54:45','Failed'),(6,'IceHrm Notification from Feedback','icehrm+user3@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Hello Jane<br/><br/>\nLala Lamees requested you to provide a feedback for a performance review\n<br/>\n<br/>\nVisit IceHrm: <a href=\"http://icehrm.test/app/?g=modules&n=performance&m=module_Performance#tabReviewFeedback\">http://icehrm.test/app/?g=modules&n=performance&m=module_Performance#tabReviewFeedback</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2020-11-15 00:58:30','2020-11-15 00:58:30','Failed'),(7,'IceHrm Notification from Feedback','icehrm+manager@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Hello Lala<br/><br/>\nJane Welington submitted the performance review feedback\n<br/>\n<br/>\nVisit IceHrm: <a href=\"http://icehrm.test/app/?g=modules&n=performance&m=module_Performance#tabCoordinatedPerformanceReview\">http://icehrm.test/app/?g=modules&n=performance&m=module_Performance#tabCoordinatedPerformanceReview</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2020-11-15 00:58:50','2020-11-15 00:58:50','Failed'),(8,'IceHrm Notification from Performance Review','icehrm+user2@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Hello Taylor<br/><br/>\nIceHrm Employee started a performance review for you. Please fill in and submit the self-assessment before the due date\n<br/>\n<br/>\nVisit IceHrm: <a href=\"http://icehrm.test/app/?g=modules&n=performance&m=module_Performance\">http://icehrm.test/app/?g=modules&n=performance&m=module_Performance</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2020-12-03 13:01:13','2020-12-03 13:01:13','Failed'),(9,'Leave Application Received','icehrm+manager@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear Lala Lamees,<br/><br/>\nYour Direct Subordinate IceHrm Employee, has submitted a leave application.<br/><br/>\nDepartment: Your Company  <br/>\nLeave Type: Employee Leave <br/>\nLeave Start Date:  2021-01-06                 Leave End Date: 2021-01-06 <br/>\nLeave Days:  1 <br/>\nReason for the leave request:  <br/>\n<table>\n                   <thead><tr><th>Leave Date</th><th>Leave Type</th></tr></thead><tbody><tr><td>2021-01-06</td><td>Full Day</td></tr></tbody></table>  <br>\nPlease login and take necessary actions.\n<br/>\n<br/>\nIceHrm Application Url: <a href=\"http://icehrm.test/app/\">http://icehrm.test/app/</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2021-01-08 13:26:13','2021-01-08 13:26:13','Failed'),(10,'Leave Application Submitted','icehrm+admin@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear IceHrm Employee,<br/><br/>\nYour leave application has been submitted for review.<br/> \nYou will be informed once it is approved or rejected.\n\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2021-01-08 13:26:13','2021-01-08 13:26:13','Failed'),(11,'Leave Application Received','icehrm+manager@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear Lala Lamees,<br/><br/>\nYour Direct Subordinate IceHrm Employee, has submitted a leave application.<br/><br/>\nDepartment: Your Company  <br/>\nLeave Type: Employee Leave <br/>\nLeave Start Date:  2020-12-02                 Leave End Date: 2020-12-02 <br/>\nLeave Days:  1 <br/>\nReason for the leave request:  <br/>\n<table>\n                   <thead><tr><th>Leave Date</th><th>Leave Type</th></tr></thead><tbody><tr><td>2020-12-02</td><td>Full Day</td></tr></tbody></table>  <br>\nPlease login and take necessary actions.\n<br/>\n<br/>\nIceHrm Application Url: <a href=\"http://icehrm.test/app/\">http://icehrm.test/app/</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2021-01-08 13:29:44','2021-01-08 13:29:44','Failed'),(12,'Leave Application Submitted','icehrm+admin@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear IceHrm Employee,<br/><br/>\nYour leave application has been submitted for review.<br/> \nYou will be informed once it is approved or rejected.\n\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2021-01-08 13:29:44','2021-01-08 13:29:44','Failed'),(13,'Leave Application Received','icehrm+user3@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear Jane Welington,<br/><br/>\nYour Direct Subordinate Sofia O\'Sullivan, has submitted a leave application.<br/><br/>\nDepartment: Head Office  <br/>\nLeave Type: Employee Leave <br/>\nLeave Start Date:  2021-02-03                 Leave End Date: 2021-02-03 <br/>\nLeave Days:  1 <br/>\nReason for the leave request:  <br/>\n<table>\n                   <thead><tr><th>Leave Date</th><th>Leave Type</th></tr></thead><tbody><tr><td>2021-02-03</td><td>Full Day</td></tr></tbody></table>  <br>\nPlease login and take necessary actions.\n<br/>\n<br/>\nIceHrm Application Url: <a href=\"http://icehrm.test/app/\">http://icehrm.test/app/</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2021-01-08 13:33:16','2021-01-08 13:33:16','Failed'),(14,'Leave Application Submitted','icehrm+user1@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear Sofia O\'Sullivan,<br/><br/>\nYour leave application has been submitted for review.<br/> \nYou will be informed once it is approved or rejected.\n\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2021-01-08 13:33:16','2021-01-08 13:33:16','Failed'),(15,'Leave Application Received','icehrm+user3@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear Jane Welington,<br/><br/>\nYour Direct Subordinate Sofia O\'Sullivan, has submitted a leave application.<br/><br/>\nDepartment: Head Office  <br/>\nLeave Type: Employee Leave <br/>\nLeave Start Date:  2021-01-01                 Leave End Date: 2021-01-01 <br/>\nLeave Days:  1 <br/>\nReason for the leave request:  <br/>\n<table>\n                   <thead><tr><th>Leave Date</th><th>Leave Type</th></tr></thead><tbody><tr><td>2021-01-01</td><td>Full Day</td></tr></tbody></table>  <br>\nPlease login and take necessary actions.\n<br/>\n<br/>\nIceHrm Application Url: <a href=\"http://icehrm.test/app/\">http://icehrm.test/app/</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2021-01-08 13:34:01','2021-01-08 13:34:01','Failed'),(16,'Leave Application Submitted','icehrm+user1@web-stalk.com','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n    <style type=\"text/css\" rel=\"stylesheet\" media=\"all\">\n        @media only screen and (max-width: 600px) {\n            .email-body_inner,\n            .email-footer {\n                width: 100% !important;\n            }\n        }\n        @media only screen and (max-width: 500px) {\n            .button {\n                width: 100% !important;\n            }\n        }\n        table {\n            border-collapse: collapse;\n            width: 100%;\n        }\n        th, td {\n            padding: 8px;\n            text-align: left;\n            border-bottom: 1px solid #ddd;\n        }\n        tr:nth-child(even) {\n            background-color: #F2F2F2;\n        }\n    </style>\n</head>\n<body dir=\"ltr\" style=\"height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%;\">\n<!-- Visually Hidden Preheader Text : BEGIN -->\n<div style=\"display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;\">\n    Notification from IceHrm.com\n</div>\n<!-- Visually Hidden Preheader Text : END -->\n<table class=\"email-wrapper\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;\" bgcolor=\"#F2F4F6\">\n    <tr>\n        <td align=\"center\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n            <table class=\"email-content\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0;\">\n                <!-- Logo -->\n                <tr>\n                    <td class=\"email-masthead\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 25px 0; text-align: center;\" align=\"center\">\n                        <a class=\"email-masthead_name\" href=\"https://icehrm.com\" target=\"_blank\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;\">\n\n                            <img src=\"http://icehrm.test/web/images/logo.png\" class=\"email-logo\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; max-height: 50px;\">\n\n                        </a>\n                    </td>\n                </tr>\n                <!-- Email Body -->\n                <tr>\n                    <td class=\"email-body\" width=\"100%\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;\" bgcolor=\"#FFF\">\n                        <table class=\"email-body_inner\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0;\">\n                            <!-- Body content -->\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p style=\"margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                                        Dear Sofia O\'Sullivan,<br/><br/>\nYour leave application has been submitted for review.<br/> \nYou will be informed once it is approved or rejected.\n\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n                <tr>\n                    <td style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">\n                        <table class=\"email-footer\" align=\"center\" width=\"570\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; width: 570px; margin: 0 auto; padding: 0; text-align: center;\">\n                            <tr>\n                                <td class=\"content-cell\" style=\"font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; padding: 35px;\">\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        You are receiving this email because your organization has added you as an employee <a href=\"http://icehrm.test/app/\"><strong><font color=\"405A6A\">http://icehrm.test/app/</font></strong></a>. If you are not the intended recipient please inform application admin icehrm+admin@web-stalk.com.\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        IceHrm.com</span>\n                                    </p>\n                                    <p class=\"sub center\" style=\"margin-top: 0; line-height: 1.5em; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box; color: #AEAEAE; font-size: 12px; text-align: center;\">\n                                        &copy; 2018 <a href=\"https://icehrm.com\" target=\"_blank\" style=\"color: #3869D4; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; -webkit-box-sizing: border-box; box-sizing: border-box;\">IceHrm</a>\n                                    </p>\n                                </td>\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </td>\n    </tr>\n</table>\n</body>\n</html>\n','','','2021-01-08 13:34:01','2021-01-08 13:34:01','Failed');
/*!40000 ALTER TABLE `EmailLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Emails`
--

DROP TABLE IF EXISTS `Emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Emails` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subject` varchar(300) NOT NULL,
  `toEmail` varchar(300) NOT NULL,
  `template` text,
  `params` text,
  `cclist` varchar(500) DEFAULT NULL,
  `bcclist` varchar(500) DEFAULT NULL,
  `error` varchar(500) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` enum('Pending','Sent','Error') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `KEY_Emails_status` (`status`),
  KEY `KEY_Emails_created` (`created`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Emails`
--

LOCK TABLES `Emails` WRITE;
/*!40000 ALTER TABLE `Emails` DISABLE KEYS */;
INSERT INTO `Emails` VALUES (1,'IceHrm Employee Document Expiry Reminder','icehrm+admin@web-stalk.com','Dear IceHrm,<br/><br/>\nFollowing documents listed under your profile will be soon be expired<br/>\nPlease login and take necessary actions.\n<br/>\n<hr/>\n<p style=\"background-color: #EEE;padding: 5px;font-size: 0.9em;font-weight: bold;\"><span style=\"font-size: 1em;font-weight: bold;\">ID Copy</span> - Expire in 30 day(s)</p><br/><span style=\"font-size: 0.8em;font-weight: bold;\"></span><hr/><p style=\"background-color: #EEE;padding: 5px;font-size: 0.9em;font-weight: bold;\"><span style=\"font-size: 1em;font-weight: bold;\">Degree Certificate</span> - Expire in 7 day(s)</p><br/><span style=\"font-size: 0.8em;font-weight: bold;\"></span><hr/><p style=\"background-color: #EEE;padding: 5px;font-size: 0.9em;font-weight: bold;\"><span style=\"font-size: 1em;font-weight: bold;\">Driving License</span> - Expire in 1 day(s)</p><br/><span style=\"font-size: 0.8em;font-weight: bold;\"></span><hr/>\nIceHrm Url: <a href=\"#_url_#\">#_url_#</a>\n<br/>','[]','[\"icehrm+manager@web-stalk.com\"]','[]',NULL,'2020-06-09 23:07:18','2020-06-09 23:07:18','Pending');
/*!40000 ALTER TABLE `Emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmergencyContacts`
--

DROP TABLE IF EXISTS `EmergencyContacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmergencyContacts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` varchar(100) DEFAULT NULL,
  `home_phone` varchar(15) DEFAULT NULL,
  `work_phone` varchar(15) DEFAULT NULL,
  `mobile_phone` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmergencyContacts_Employee` (`employee`),
  CONSTRAINT `Fk_EmergencyContacts_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmergencyContacts`
--

LOCK TABLES `EmergencyContacts` WRITE;
/*!40000 ALTER TABLE `EmergencyContacts` DISABLE KEYS */;
INSERT INTO `EmergencyContacts` VALUES (1,1,'Emma Owns','Mother','+874463422','+874463422','+874463422'),(2,2,'Casey Watson','Sister','231-453-876','231-453-876','231-453-876');
/*!40000 ALTER TABLE `EmergencyContacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeApprovals`
--

DROP TABLE IF EXISTS `EmployeeApprovals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeApprovals` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `element` bigint(20) NOT NULL,
  `approver` bigint(20) DEFAULT NULL,
  `level` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `active` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `EmployeeApprovals_type_element_level` (`type`,`element`,`level`),
  KEY `EmployeeApprovals_type_element_status_level` (`type`,`element`,`status`,`level`),
  KEY `EmployeeApprovals_type_element` (`type`,`element`),
  KEY `EmployeeApprovals_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeApprovals`
--

LOCK TABLES `EmployeeApprovals` WRITE;
/*!40000 ALTER TABLE `EmployeeApprovals` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeApprovals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeAttendanceSheets`
--

DROP TABLE IF EXISTS `EmployeeAttendanceSheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeAttendanceSheets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('Approved','Pending','Rejected','Submitted') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `EmployeeAttendanceSheetsKey` (`employee`,`date_start`,`date_end`),
  KEY `EmployeeAttendanceSheets_date_end` (`date_end`),
  CONSTRAINT `Fk_EmployeeAttendanceSheets_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeAttendanceSheets`
--

LOCK TABLES `EmployeeAttendanceSheets` WRITE;
/*!40000 ALTER TABLE `EmployeeAttendanceSheets` DISABLE KEYS */;
INSERT INTO `EmployeeAttendanceSheets` VALUES (1,1,'2020-06-07','2020-06-13','Pending'),(2,1,'2020-11-01','2020-11-07','Pending'),(3,2,'2020-11-01','2020-11-07','Pending'),(4,1,'2020-11-08','2020-11-14','Pending'),(5,1,'2020-11-15','2020-11-21','Pending'),(6,2,'2020-11-15','2020-11-21','Pending'),(7,6,'2020-11-15','2020-11-21','Pending'),(8,1,'2020-11-22','2020-11-28','Pending'),(9,1,'2020-11-29','2020-12-05','Pending'),(10,1,'2020-12-27','2021-01-02','Pending'),(11,1,'2021-01-03','2021-01-09','Pending'),(12,3,'2021-01-03','2021-01-09','Pending'),(13,1,'2021-01-24','2021-01-30','Pending'),(14,1,'2021-01-31','2021-02-06','Pending'),(15,3,'2021-01-31','2021-02-06','Pending');
/*!40000 ALTER TABLE `EmployeeAttendanceSheets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeCertifications`
--

DROP TABLE IF EXISTS `EmployeeCertifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeCertifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `certification_id` bigint(20) DEFAULT NULL,
  `employee` bigint(20) NOT NULL,
  `institute` varchar(400) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee` (`employee`,`certification_id`),
  KEY `Fk_EmployeeCertifications_Certifications` (`certification_id`),
  CONSTRAINT `Fk_EmployeeCertifications_Certifications` FOREIGN KEY (`certification_id`) REFERENCES `Certifications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeCertifications_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeCertifications`
--

LOCK TABLES `EmployeeCertifications` WRITE;
/*!40000 ALTER TABLE `EmployeeCertifications` DISABLE KEYS */;
INSERT INTO `EmployeeCertifications` VALUES (1,21,1,'PHR','2012-06-04','2016-06-13'),(2,19,1,'CPA','2010-02-16','2019-02-28'),(3,17,2,'PMP','2011-06-14','2019-10-20'),(4,3,2,'PMI','2004-06-08','2017-09-14');
/*!40000 ALTER TABLE `EmployeeCertifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeCompanyLoans`
--

DROP TABLE IF EXISTS `EmployeeCompanyLoans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeCompanyLoans` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `loan` bigint(20) DEFAULT NULL,
  `start_date` date NOT NULL,
  `last_installment_date` date NOT NULL,
  `period_months` bigint(20) DEFAULT NULL,
  `currency` bigint(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `monthly_installment` decimal(10,2) NOT NULL,
  `status` enum('Approved','Repayment','Paid','Suspended') DEFAULT 'Approved',
  `details` text,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeCompanyLoans_CompanyLoans` (`loan`),
  KEY `Fk_EmployeeCompanyLoans_Employee` (`employee`),
  CONSTRAINT `Fk_EmployeeCompanyLoans_CompanyLoans` FOREIGN KEY (`loan`) REFERENCES `CompanyLoans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeCompanyLoans_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeCompanyLoans`
--

LOCK TABLES `EmployeeCompanyLoans` WRITE;
/*!40000 ALTER TABLE `EmployeeCompanyLoans` DISABLE KEYS */;
INSERT INTO `EmployeeCompanyLoans` VALUES (1,2,2,'2013-02-05','2016-02-05',12,NULL,12000.00,1059.45,'Approved','');
/*!40000 ALTER TABLE `EmployeeCompanyLoans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeDataHistory`
--

DROP TABLE IF EXISTS `EmployeeDataHistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeDataHistory` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `employee` bigint(20) NOT NULL,
  `field` varchar(100) NOT NULL,
  `old_value` varchar(500) DEFAULT NULL,
  `new_value` varchar(500) DEFAULT NULL,
  `description` varchar(800) DEFAULT NULL,
  `user` bigint(20) DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeDataHistory_Employee` (`employee`),
  KEY `Fk_EmployeeDataHistory_Users` (`user`),
  CONSTRAINT `Fk_EmployeeDataHistory_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeDataHistory_Users` FOREIGN KEY (`user`) REFERENCES `Users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeDataHistory`
--

LOCK TABLES `EmployeeDataHistory` WRITE;
/*!40000 ALTER TABLE `EmployeeDataHistory` DISABLE KEYS */;
INSERT INTO `EmployeeDataHistory` VALUES (4,'Employee',1,'marital_status','Married','Single',NULL,1,'2020-11-06 12:23:54','2020-11-06 12:23:54'),(5,'Employee',1,'birthday','1984-03-17','1984-03-16',NULL,1,'2020-11-06 13:22:47','2020-11-06 13:22:47'),(6,'Employee',2,'nationality','Tajik','Surinamer',NULL,1,'2020-11-06 13:22:54','2020-11-06 13:22:54'),(7,'Employee',4,'nationality','Australian','American',NULL,2,'2020-11-06 13:32:21','2020-11-06 13:32:21'),(8,'Employee',3,'supervisor','Lala Lamees','Jane Welington',NULL,1,'2020-11-15 00:51:26','2020-11-15 00:51:26'),(9,'Employee',1,'joined_date','2005-08-03','2020-01-01',NULL,1,'2021-01-08 13:25:30','2021-01-08 13:25:30'),(10,'Employee',1,'employment_status','Full Time Permanent','Full Time Contract',NULL,1,'2021-01-08 13:25:30','2021-01-08 13:25:30'),(11,'Employee',1,'joined_date','2020-01-01','2019-10-07',NULL,1,'2021-01-08 13:28:03','2021-01-08 13:28:03'),(12,'Employee',3,'joined_date','2010-02-08','2019-10-07',NULL,1,'2021-01-08 13:31:30','2021-01-08 13:31:30'),(13,'Employee',3,'employment_status','Full Time Permanent','Full Time Contract',NULL,1,'2021-01-08 13:31:30','2021-01-08 13:31:30');
/*!40000 ALTER TABLE `EmployeeDataHistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeDependents`
--

DROP TABLE IF EXISTS `EmployeeDependents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeDependents` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` enum('Child','Spouse','Parent','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `id_number` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeDependents_Employee` (`employee`),
  CONSTRAINT `Fk_EmployeeDependents_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeDependents`
--

LOCK TABLES `EmployeeDependents` WRITE;
/*!40000 ALTER TABLE `EmployeeDependents` DISABLE KEYS */;
INSERT INTO `EmployeeDependents` VALUES (1,1,'Emma Owns','Parent','1940-06-11','475209UHB'),(2,1,'Mica Singroo','Other','2000-06-13','');
/*!40000 ALTER TABLE `EmployeeDependents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeDocuments`
--

DROP TABLE IF EXISTS `EmployeeDocuments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeDocuments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `document` bigint(20) DEFAULT NULL,
  `date_added` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `status` enum('Active','Inactive','Draft') DEFAULT 'Active',
  `details` text,
  `attachment` varchar(100) DEFAULT NULL,
  `signature` text,
  `expire_notification_last` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeDocuments_Documents` (`document`),
  KEY `Fk_EmployeeDocuments_Employee` (`employee`),
  KEY `KEY_EmployeeDocuments_valid_until` (`valid_until`),
  KEY `KEY_EmployeeDocuments_valid_until_status` (`valid_until`,`status`,`expire_notification_last`),
  CONSTRAINT `Fk_EmployeeDocuments_Documents` FOREIGN KEY (`document`) REFERENCES `Documents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeDocuments_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeDocuments`
--

LOCK TABLES `EmployeeDocuments` WRITE;
/*!40000 ALTER TABLE `EmployeeDocuments` DISABLE KEYS */;
INSERT INTO `EmployeeDocuments` VALUES (1,1,1,'2020-06-09','2020-07-09','Active','',NULL,NULL,30),(2,1,2,'2020-06-09','2020-06-16','Active','',NULL,NULL,7),(3,2,3,'2020-06-09','2020-06-10','Active','','attachment_Gqrmn2Jio6Dx5q1604471741077',NULL,-1);
/*!40000 ALTER TABLE `EmployeeDocuments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeEducations`
--

DROP TABLE IF EXISTS `EmployeeEducations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeEducations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `education_id` bigint(20) DEFAULT NULL,
  `employee` bigint(20) NOT NULL,
  `institute` varchar(400) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeEducations_Educations` (`education_id`),
  KEY `Fk_EmployeeEducations_Employee` (`employee`),
  CONSTRAINT `Fk_EmployeeEducations_Educations` FOREIGN KEY (`education_id`) REFERENCES `Educations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeEducations_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeEducations`
--

LOCK TABLES `EmployeeEducations` WRITE;
/*!40000 ALTER TABLE `EmployeeEducations` DISABLE KEYS */;
INSERT INTO `EmployeeEducations` VALUES (1,1,1,'National University of Turky','2004-02-03','2006-06-13'),(2,1,2,'MIT','1995-02-21','1999-10-12');
/*!40000 ALTER TABLE `EmployeeEducations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeEthnicity`
--

DROP TABLE IF EXISTS `EmployeeEthnicity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeEthnicity` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `ethnicity` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeEthnicity_Employee` (`employee`),
  KEY `Fk_EmployeeEthnicity_Ethnicity` (`ethnicity`),
  CONSTRAINT `Fk_EmployeeEthnicity_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeEthnicity_Ethnicity` FOREIGN KEY (`ethnicity`) REFERENCES `Ethnicity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeEthnicity`
--

LOCK TABLES `EmployeeEthnicity` WRITE;
/*!40000 ALTER TABLE `EmployeeEthnicity` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeEthnicity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeExpenses`
--

DROP TABLE IF EXISTS `EmployeeExpenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeExpenses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `expense_date` date DEFAULT NULL,
  `payment_method` bigint(20) NOT NULL,
  `transaction_no` varchar(300) NOT NULL,
  `payee` varchar(500) NOT NULL,
  `category` bigint(20) NOT NULL,
  `notes` text,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` bigint(20) DEFAULT NULL,
  `attachment1` varchar(100) DEFAULT NULL,
  `attachment2` varchar(100) DEFAULT NULL,
  `attachment3` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeExpenses_Employee` (`employee`),
  KEY `Fk_EmployeeExpenses_pm` (`payment_method`),
  KEY `Fk_EmployeeExpenses_category` (`category`),
  CONSTRAINT `Fk_EmployeeExpenses_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeExpenses_category` FOREIGN KEY (`category`) REFERENCES `ExpensesCategories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeExpenses_pm` FOREIGN KEY (`payment_method`) REFERENCES `ExpensesPaymentMethods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeExpenses`
--

LOCK TABLES `EmployeeExpenses` WRITE;
/*!40000 ALTER TABLE `EmployeeExpenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeExpenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeForms`
--

DROP TABLE IF EXISTS `EmployeeForms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeForms` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `form` bigint(20) NOT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeForms_Employee` (`employee`),
  KEY `Fk_EmployeeForms_Forms` (`form`),
  CONSTRAINT `Fk_EmployeeForms_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeForms_Forms` FOREIGN KEY (`form`) REFERENCES `Forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeForms`
--

LOCK TABLES `EmployeeForms` WRITE;
/*!40000 ALTER TABLE `EmployeeForms` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeForms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeImmigrationStatus`
--

DROP TABLE IF EXISTS `EmployeeImmigrationStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeImmigrationStatus` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `status` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeImmigrationStatus_Employee` (`employee`),
  KEY `Fk_EmployeeImmigrationStatus_Type` (`status`),
  CONSTRAINT `Fk_EmployeeImmigrationStatus_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeImmigrationStatus_Type` FOREIGN KEY (`status`) REFERENCES `ImmigrationStatus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeImmigrationStatus`
--

LOCK TABLES `EmployeeImmigrationStatus` WRITE;
/*!40000 ALTER TABLE `EmployeeImmigrationStatus` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeImmigrationStatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeImmigrations`
--

DROP TABLE IF EXISTS `EmployeeImmigrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeImmigrations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `document` bigint(20) DEFAULT NULL,
  `documentname` varchar(150) NOT NULL,
  `valid_until` date NOT NULL,
  `status` enum('Active','Inactive','Draft') DEFAULT 'Active',
  `details` text,
  `attachment1` varchar(100) DEFAULT NULL,
  `attachment2` varchar(100) DEFAULT NULL,
  `attachment3` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeImmigrations_Employee` (`employee`),
  KEY `Fk_EmployeeImmigrations_ImmigrationDocuments` (`document`),
  CONSTRAINT `Fk_EmployeeImmigrations_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeImmigrations_ImmigrationDocuments` FOREIGN KEY (`document`) REFERENCES `ImmigrationDocuments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeImmigrations`
--

LOCK TABLES `EmployeeImmigrations` WRITE;
/*!40000 ALTER TABLE `EmployeeImmigrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeImmigrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeLanguages`
--

DROP TABLE IF EXISTS `EmployeeLanguages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeLanguages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) DEFAULT NULL,
  `employee` bigint(20) NOT NULL,
  `reading` enum('Elementary Proficiency','Limited Working Proficiency','Professional Working Proficiency','Full Professional Proficiency','Native or Bilingual Proficiency') DEFAULT NULL,
  `speaking` enum('Elementary Proficiency','Limited Working Proficiency','Professional Working Proficiency','Full Professional Proficiency','Native or Bilingual Proficiency') DEFAULT NULL,
  `writing` enum('Elementary Proficiency','Limited Working Proficiency','Professional Working Proficiency','Full Professional Proficiency','Native or Bilingual Proficiency') DEFAULT NULL,
  `understanding` enum('Elementary Proficiency','Limited Working Proficiency','Professional Working Proficiency','Full Professional Proficiency','Native or Bilingual Proficiency') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee` (`employee`,`language_id`),
  KEY `Fk_EmployeeLanguages_Languages` (`language_id`),
  CONSTRAINT `Fk_EmployeeLanguages_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeLanguages_Languages` FOREIGN KEY (`language_id`) REFERENCES `Languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeLanguages`
--

LOCK TABLES `EmployeeLanguages` WRITE;
/*!40000 ALTER TABLE `EmployeeLanguages` DISABLE KEYS */;
INSERT INTO `EmployeeLanguages` VALUES (1,1,1,'Full Professional Proficiency','Full Professional Proficiency','Full Professional Proficiency','Native or Bilingual Proficiency'),(2,1,2,'Native or Bilingual Proficiency','Native or Bilingual Proficiency','Native or Bilingual Proficiency','Native or Bilingual Proficiency'),(3,2,2,'Limited Working Proficiency','Professional Working Proficiency','Limited Working Proficiency','Professional Working Proficiency');
/*!40000 ALTER TABLE `EmployeeLanguages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeLeaveDays`
--

DROP TABLE IF EXISTS `EmployeeLeaveDays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeLeaveDays` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_leave` bigint(20) NOT NULL,
  `leave_date` date DEFAULT NULL,
  `leave_type` enum('Full Day','Half Day - Morning','Half Day - Afternoon','1 Hour - Morning','2 Hours - Morning','3 Hours - Morning','1 Hour - Afternoon','2 Hours - Afternoon','3 Hours - Afternoon') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeLeaveDays_EmployeeLeaves` (`employee_leave`),
  CONSTRAINT `Fk_EmployeeLeaveDays_EmployeeLeaves` FOREIGN KEY (`employee_leave`) REFERENCES `EmployeeLeaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeLeaveDays`
--

LOCK TABLES `EmployeeLeaveDays` WRITE;
/*!40000 ALTER TABLE `EmployeeLeaveDays` DISABLE KEYS */;
INSERT INTO `EmployeeLeaveDays` VALUES (1,1,'2020-11-02','Full Day');
/*!40000 ALTER TABLE `EmployeeLeaveDays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeLeaveLog`
--

DROP TABLE IF EXISTS `EmployeeLeaveLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeLeaveLog` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_leave` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `data` varchar(500) NOT NULL,
  `status_from` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') DEFAULT 'Pending',
  `status_to` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') DEFAULT 'Pending',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeLeaveLog_EmployeeLeaves` (`employee_leave`),
  KEY `Fk_EmployeeLeaveLog_Users` (`user_id`),
  CONSTRAINT `Fk_EmployeeLeaveLog_EmployeeLeaves` FOREIGN KEY (`employee_leave`) REFERENCES `EmployeeLeaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeLeaveLog_Users` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeLeaveLog`
--

LOCK TABLES `EmployeeLeaveLog` WRITE;
/*!40000 ALTER TABLE `EmployeeLeaveLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeLeaveLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeLeaves`
--

DROP TABLE IF EXISTS `EmployeeLeaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeLeaves` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `leave_type` bigint(20) NOT NULL,
  `leave_period` bigint(20) NOT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `details` text,
  `status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') DEFAULT 'Pending',
  `attachment` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeLeaves_Employee` (`employee`),
  KEY `Fk_EmployeeLeaves_LeaveTypes` (`leave_type`),
  KEY `Fk_EmployeeLeaves_LeavePeriods` (`leave_period`),
  CONSTRAINT `Fk_EmployeeLeaves_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeLeaves_LeavePeriods` FOREIGN KEY (`leave_period`) REFERENCES `LeavePeriods` (`id`),
  CONSTRAINT `Fk_EmployeeLeaves_LeaveTypes` FOREIGN KEY (`leave_type`) REFERENCES `LeaveTypes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeLeaves`
--

LOCK TABLES `EmployeeLeaves` WRITE;
/*!40000 ALTER TABLE `EmployeeLeaves` DISABLE KEYS */;
INSERT INTO `EmployeeLeaves` VALUES (1,1,1,8,'2020-11-01','2020-11-02','','Pending','');
/*!40000 ALTER TABLE `EmployeeLeaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeOvertime`
--

DROP TABLE IF EXISTS `EmployeeOvertime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeOvertime` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `category` bigint(20) NOT NULL,
  `project` bigint(20) DEFAULT NULL,
  `notes` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeOvertime_Employee` (`employee`),
  KEY `Fk_EmployeeOvertime_Category` (`category`),
  CONSTRAINT `Fk_EmployeeOvertime_Category` FOREIGN KEY (`category`) REFERENCES `OvertimeCategories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeOvertime_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeOvertime`
--

LOCK TABLES `EmployeeOvertime` WRITE;
/*!40000 ALTER TABLE `EmployeeOvertime` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeOvertime` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeProjects`
--

DROP TABLE IF EXISTS `EmployeeProjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeProjects` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `project` bigint(20) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `status` enum('Current','Inactive','Completed') DEFAULT 'Current',
  `details` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `EmployeeProjectsKey` (`employee`,`project`),
  KEY `Fk_EmployeeProjects_Projects` (`project`),
  CONSTRAINT `Fk_EmployeeProjects_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeProjects_Projects` FOREIGN KEY (`project`) REFERENCES `Projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeProjects`
--

LOCK TABLES `EmployeeProjects` WRITE;
/*!40000 ALTER TABLE `EmployeeProjects` DISABLE KEYS */;
INSERT INTO `EmployeeProjects` VALUES (1,2,1,'2010-03-18','2014-03-06','Inactive',''),(3,2,2,'2013-02-05','2013-02-11','Current',''),(5,2,3,'2013-02-24',NULL,'Current','');
/*!40000 ALTER TABLE `EmployeeProjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeSalary`
--

DROP TABLE IF EXISTS `EmployeeSalary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeSalary` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `component` bigint(20) NOT NULL,
  `pay_frequency` enum('Hourly','Daily','Bi Weekly','Weekly','Semi Monthly','Monthly') DEFAULT NULL,
  `currency` bigint(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `details` text,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeSalary_Employee` (`employee`),
  KEY `Fk_EmployeeSalary_Currency` (`currency`),
  CONSTRAINT `Fk_EmployeeSalary_Currency` FOREIGN KEY (`currency`) REFERENCES `CurrencyTypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeSalary_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeSalary`
--

LOCK TABLES `EmployeeSalary` WRITE;
/*!40000 ALTER TABLE `EmployeeSalary` DISABLE KEYS */;
INSERT INTO `EmployeeSalary` VALUES (1,1,1,NULL,NULL,50000.00,''),(2,1,2,NULL,NULL,20000.00,''),(3,1,3,NULL,NULL,30000.00,''),(4,1,4,NULL,NULL,2000.00,''),(5,2,1,NULL,NULL,90500.00,''),(6,2,2,NULL,NULL,40000.00,''),(7,2,3,NULL,NULL,50000.00,''),(8,3,1,NULL,NULL,131409.00,''),(9,3,2,NULL,NULL,143471.00,''),(10,3,3,NULL,NULL,50000.00,''),(11,3,4,NULL,NULL,30000.00,''),(12,4,5,NULL,NULL,1432.00,''),(13,4,6,NULL,NULL,2100.00,''),(18,6,1,NULL,NULL,77757.22,''),(19,7,1,NULL,NULL,5258.48,''),(20,8,1,NULL,NULL,61233.75,''),(21,9,1,NULL,NULL,16720.79,''),(22,10,1,NULL,NULL,24352.06,''),(23,11,1,NULL,NULL,56704.01,''),(24,12,1,NULL,NULL,38815.43,''),(25,13,1,NULL,NULL,94226.03,''),(26,14,1,NULL,NULL,70066.35,'');
/*!40000 ALTER TABLE `EmployeeSalary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeSkills`
--

DROP TABLE IF EXISTS `EmployeeSkills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeSkills` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `skill_id` bigint(20) DEFAULT NULL,
  `employee` bigint(20) NOT NULL,
  `details` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee` (`employee`,`skill_id`),
  KEY `Fk_EmployeeSkills_Skills` (`skill_id`),
  CONSTRAINT `Fk_EmployeeSkills_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeSkills_Skills` FOREIGN KEY (`skill_id`) REFERENCES `Skills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeSkills`
--

LOCK TABLES `EmployeeSkills` WRITE;
/*!40000 ALTER TABLE `EmployeeSkills` DISABLE KEYS */;
INSERT INTO `EmployeeSkills` VALUES (1,9,1,'Creating web sites'),(2,6,2,'Certified Business Intelligence Professional');
/*!40000 ALTER TABLE `EmployeeSkills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeTeamMembers`
--

DROP TABLE IF EXISTS `EmployeeTeamMembers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeTeamMembers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `team` bigint(20) DEFAULT NULL,
  `member` bigint(20) DEFAULT NULL,
  `role` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeTeamMembers_Team` (`team`),
  KEY `Fk_EmployeeTeamMembers_Member` (`member`),
  CONSTRAINT `Fk_EmployeeTeamMembers_Member` FOREIGN KEY (`member`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeTeamMembers_Team` FOREIGN KEY (`team`) REFERENCES `EmployeeTeams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeTeamMembers`
--

LOCK TABLES `EmployeeTeamMembers` WRITE;
/*!40000 ALTER TABLE `EmployeeTeamMembers` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeTeamMembers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeTeams`
--

DROP TABLE IF EXISTS `EmployeeTeams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeTeams` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` text,
  `lead` bigint(20) DEFAULT NULL,
  `department` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeTeams_Lead` (`lead`),
  KEY `Fk_EmployeeTeams_Department` (`department`),
  CONSTRAINT `Fk_EmployeeTeams_Department` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeTeams_Lead` FOREIGN KEY (`lead`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeTeams`
--

LOCK TABLES `EmployeeTeams` WRITE;
/*!40000 ALTER TABLE `EmployeeTeams` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeTeams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeTimeEntry`
--

DROP TABLE IF EXISTS `EmployeeTimeEntry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeTimeEntry` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project` bigint(20) DEFAULT NULL,
  `employee` bigint(20) NOT NULL,
  `timesheet` bigint(20) NOT NULL,
  `details` text,
  `created` datetime DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `time_start` varchar(10) NOT NULL,
  `date_end` datetime DEFAULT NULL,
  `time_end` varchar(10) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeTimeEntry_Projects` (`project`),
  KEY `Fk_EmployeeTimeEntry_EmployeeTimeSheets` (`timesheet`),
  KEY `employee_project` (`employee`,`project`),
  KEY `employee_project_date_start` (`employee`,`project`,`date_start`),
  CONSTRAINT `Fk_EmployeeTimeEntry_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeTimeEntry_EmployeeTimeSheets` FOREIGN KEY (`timesheet`) REFERENCES `EmployeeTimeSheets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeTimeEntry_Projects` FOREIGN KEY (`project`) REFERENCES `Projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeTimeEntry`
--

LOCK TABLES `EmployeeTimeEntry` WRITE;
/*!40000 ALTER TABLE `EmployeeTimeEntry` DISABLE KEYS */;
INSERT INTO `EmployeeTimeEntry` VALUES (1,1,1,1,'adsasdad','2020-11-03 04:02:55','2020-06-07 23:32:41','23:32:41','2020-06-07 23:35:41','23:35:41',NULL);
/*!40000 ALTER TABLE `EmployeeTimeEntry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeTimeSheets`
--

DROP TABLE IF EXISTS `EmployeeTimeSheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeTimeSheets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('Approved','Pending','Rejected','Submitted') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `EmployeeTimeSheetsKey` (`employee`,`date_start`,`date_end`),
  KEY `EmployeeTimeSheets_date_end` (`date_end`),
  CONSTRAINT `Fk_EmployeeTimeSheets_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeTimeSheets`
--

LOCK TABLES `EmployeeTimeSheets` WRITE;
/*!40000 ALTER TABLE `EmployeeTimeSheets` DISABLE KEYS */;
INSERT INTO `EmployeeTimeSheets` VALUES (1,1,'2020-06-07','2020-06-13','Pending'),(2,1,'2020-11-01','2020-11-07','Pending'),(3,2,'2020-11-01','2020-11-07','Pending'),(4,1,'2020-11-08','2020-11-14','Pending'),(5,1,'2020-11-15','2020-11-21','Pending'),(6,2,'2020-11-15','2020-11-21','Pending'),(7,6,'2020-11-15','2020-11-21','Pending'),(8,1,'2020-11-22','2020-11-28','Pending'),(9,1,'2020-11-29','2020-12-05','Pending'),(10,1,'2020-12-27','2021-01-02','Pending'),(11,1,'2021-01-03','2021-01-09','Pending'),(12,3,'2021-01-03','2021-01-09','Pending'),(13,1,'2021-01-24','2021-01-30','Pending'),(14,1,'2021-01-31','2021-02-06','Pending'),(15,3,'2021-01-31','2021-02-06','Pending');
/*!40000 ALTER TABLE `EmployeeTimeSheets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeTrainingSessions`
--

DROP TABLE IF EXISTS `EmployeeTrainingSessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeTrainingSessions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `trainingSession` bigint(20) DEFAULT NULL,
  `feedBack` varchar(1500) DEFAULT NULL,
  `status` enum('Scheduled','Attended','Not-Attended','Completed') DEFAULT 'Scheduled',
  `proof` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeTrainingSessions_TrainingSessions` (`trainingSession`),
  KEY `Fk_EmployeeTrainingSessions_Employee` (`employee`),
  CONSTRAINT `Fk_EmployeeTrainingSessions_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeTrainingSessions_TrainingSessions` FOREIGN KEY (`trainingSession`) REFERENCES `TrainingSessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeTrainingSessions`
--

LOCK TABLES `EmployeeTrainingSessions` WRITE;
/*!40000 ALTER TABLE `EmployeeTrainingSessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeTrainingSessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployeeTravelRecords`
--

DROP TABLE IF EXISTS `EmployeeTravelRecords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployeeTravelRecords` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `type` varchar(200) DEFAULT '',
  `purpose` varchar(200) NOT NULL,
  `travel_from` varchar(200) NOT NULL,
  `travel_to` varchar(200) NOT NULL,
  `travel_date` datetime DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `details` varchar(500) DEFAULT NULL,
  `funding` decimal(10,2) DEFAULT NULL,
  `currency` bigint(20) DEFAULT NULL,
  `attachment1` varchar(100) DEFAULT NULL,
  `attachment2` varchar(100) DEFAULT NULL,
  `attachment3` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeTravelRecords_Employee` (`employee`),
  CONSTRAINT `Fk_EmployeeTravelRecords_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployeeTravelRecords`
--

LOCK TABLES `EmployeeTravelRecords` WRITE;
/*!40000 ALTER TABLE `EmployeeTravelRecords` DISABLE KEYS */;
/*!40000 ALTER TABLE `EmployeeTravelRecords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Employees`
--

DROP TABLE IF EXISTS `Employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Employees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `nationality` bigint(20) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(15) DEFAULT NULL,
  `marital_status` enum('Married','Single','Divorced','Widowed','Other') DEFAULT NULL,
  `ssn_num` varchar(100) DEFAULT NULL,
  `nic_num` varchar(100) DEFAULT NULL,
  `other_id` varchar(100) DEFAULT NULL,
  `driving_license` varchar(100) DEFAULT NULL,
  `driving_license_exp_date` date DEFAULT NULL,
  `employment_status` bigint(20) DEFAULT NULL,
  `job_title` bigint(20) DEFAULT NULL,
  `pay_grade` bigint(20) DEFAULT NULL,
  `work_station_id` varchar(100) DEFAULT NULL,
  `address1` varchar(100) DEFAULT NULL,
  `address2` varchar(100) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `country` char(2) DEFAULT NULL,
  `province` bigint(20) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `home_phone` varchar(50) DEFAULT NULL,
  `mobile_phone` varchar(50) DEFAULT NULL,
  `work_phone` varchar(50) DEFAULT NULL,
  `work_email` varchar(100) DEFAULT NULL,
  `private_email` varchar(100) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `confirmation_date` date DEFAULT NULL,
  `supervisor` bigint(20) DEFAULT NULL,
  `indirect_supervisors` varchar(250) DEFAULT NULL,
  `department` bigint(20) DEFAULT NULL,
  `custom1` varchar(250) DEFAULT NULL,
  `custom2` varchar(250) DEFAULT NULL,
  `custom3` varchar(250) DEFAULT NULL,
  `custom4` varchar(250) DEFAULT NULL,
  `custom5` varchar(250) DEFAULT NULL,
  `custom6` varchar(250) DEFAULT NULL,
  `custom7` varchar(250) DEFAULT NULL,
  `custom8` varchar(250) DEFAULT NULL,
  `custom9` varchar(250) DEFAULT NULL,
  `custom10` varchar(250) DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `notes` text,
  `status` enum('Active','Terminated') DEFAULT 'Active',
  `ethnicity` bigint(20) DEFAULT NULL,
  `immigration_status` bigint(20) DEFAULT NULL,
  `approver1` bigint(20) DEFAULT NULL,
  `approver2` bigint(20) DEFAULT NULL,
  `approver3` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `Fk_Employee_Nationality` (`nationality`),
  KEY `Fk_Employee_JobTitle` (`job_title`),
  KEY `Fk_Employee_EmploymentStatus` (`employment_status`),
  KEY `Fk_Employee_Country` (`country`),
  KEY `Fk_Employee_Province` (`province`),
  KEY `Fk_Employee_Supervisor` (`supervisor`),
  KEY `Fk_Employee_CompanyStructures` (`department`),
  KEY `Fk_Employee_PayGrades` (`pay_grade`),
  CONSTRAINT `Fk_Employee_CompanyStructures` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_Employee_Country` FOREIGN KEY (`country`) REFERENCES `Country` (`code`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_Employee_EmploymentStatus` FOREIGN KEY (`employment_status`) REFERENCES `EmploymentStatus` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_Employee_JobTitle` FOREIGN KEY (`job_title`) REFERENCES `JobTitles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_Employee_Nationality` FOREIGN KEY (`nationality`) REFERENCES `Nationality` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_Employee_PayGrades` FOREIGN KEY (`pay_grade`) REFERENCES `PayGrades` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_Employee_Province` FOREIGN KEY (`province`) REFERENCES `Province` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_Employee_Supervisor` FOREIGN KEY (`supervisor`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Employees`
--

LOCK TABLES `Employees` WRITE;
/*!40000 ALTER TABLE `Employees` DISABLE KEYS */;
INSERT INTO `Employees` VALUES (1,'EMP001','IceHrm','Sample','Employee',35,'1984-03-16','Male','Single','','294-38-3535','294-38-3535','',NULL,1,11,2,'','2772 Flynn Street','Willoughby','Willoughby','US',41,'44094','440-953-4578','440-953-4578','440-953-4578','icehrm+admin@web-stalk.com','icehrm+admin@web-stalk.com','2019-10-07',NULL,2,'[3,4]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','Active',NULL,NULL,5,6,7),(2,'EMP002','Lala','Nadila ','Lamees',169,'1984-03-12','Female','Single','','4594567WE3','4595567WE3','349-066-YUO','2012-03-01',1,8,2,'W001','Green War Rd, 00123','','Istanbul','TR',NULL,'909066','+960112345','+960112345','+960112345','icehrm+manager@web-stalk.com','icehrm+manager@web-stalk.com','2011-03-07','2012-02-14',1,'[3,4]',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','Active',NULL,NULL,5,6,NULL),(3,'EMP003','Sofia','','O\'Sullivan',4,'1975-08-28','Female','Married','','768-20-4394','768-20-4394','',NULL,1,10,2,'','2792 Trails End Road','Fort Lauderdale','Fort Lauderdale','US',12,'33308','954-388-3340','954-388-3340','954-388-3340','icehrm+user1@web-stalk.com','icehrm+user1@web-stalk.com','2019-10-07',NULL,6,'[1,4]',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','Active',NULL,NULL,5,NULL,NULL),(4,'EMP004','Taylor','','Holmes',4,'1979-07-15','Male','Single','158-06-2292','158-06-2292','','',NULL,1,5,2,'','1164','Walnut Avenue','Rochelle Park','US',35,'7662','201-474-8048','201-474-8048','201-474-8048','icehrm+user2@web-stalk.com','icehrm+user2@web-stalk.com','2006-07-12',NULL,2,'',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','Active',NULL,NULL,NULL,NULL,NULL),(6,'EMP005','Jane','','Welington',4,'1979-07-15','Female','Single','158-06-2292','158-06-2292','','',NULL,1,5,2,'','1164','Walnut Avenue','Rochelle Park','US',35,'7662','201-474-8048','201-474-8048','201-474-8048','icehrm+user2@web-stalk.com','icehrm+user2@web-stalk.com','2006-07-12',NULL,2,'',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','Active',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `Employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmployementType`
--

DROP TABLE IF EXISTS `EmployementType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmployementType` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmployementType`
--

LOCK TABLES `EmployementType` WRITE;
/*!40000 ALTER TABLE `EmployementType` DISABLE KEYS */;
INSERT INTO `EmployementType` VALUES (1,'Full-time'),(2,'Part-time'),(3,'Contract'),(4,'Temporary'),(5,'Other');
/*!40000 ALTER TABLE `EmployementType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EmploymentStatus`
--

DROP TABLE IF EXISTS `EmploymentStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EmploymentStatus` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EmploymentStatus`
--

LOCK TABLES `EmploymentStatus` WRITE;
/*!40000 ALTER TABLE `EmploymentStatus` DISABLE KEYS */;
INSERT INTO `EmploymentStatus` VALUES (1,'Full Time Contract','Full Time Contract'),(2,'Full Time Internship','Full Time Internship'),(3,'Full Time Permanent','Full Time Permanent'),(4,'Part Time Contract','Part Time Contract'),(5,'Part Time Internship','Part Time Internship'),(6,'Part Time Permanent','Part Time Permanent');
/*!40000 ALTER TABLE `EmploymentStatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Ethnicity`
--

DROP TABLE IF EXISTS `Ethnicity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ethnicity` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Ethnicity`
--

LOCK TABLES `Ethnicity` WRITE;
/*!40000 ALTER TABLE `Ethnicity` DISABLE KEYS */;
INSERT INTO `Ethnicity` VALUES (1,'White American'),(2,'Black or African American'),(3,'Native American'),(4,'Alaska Native'),(5,'Asian American'),(6,'Native Hawaiian'),(7,'Pacific Islander');
/*!40000 ALTER TABLE `Ethnicity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ExpensesCategories`
--

DROP TABLE IF EXISTS `ExpensesCategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ExpensesCategories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `pre_approve` enum('Yes','No') DEFAULT 'Yes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ExpensesCategories`
--

LOCK TABLES `ExpensesCategories` WRITE;
/*!40000 ALTER TABLE `ExpensesCategories` DISABLE KEYS */;
INSERT INTO `ExpensesCategories` VALUES (1,'Auto - Gas',NULL,NULL,'Yes'),(2,'Auto - Insurance',NULL,NULL,'Yes'),(3,'Auto - Maintenance',NULL,NULL,'Yes'),(4,'Auto - Payment',NULL,NULL,'Yes'),(5,'Transportation',NULL,NULL,'Yes'),(6,'Bank Fees',NULL,NULL,'Yes'),(7,'Dining Out',NULL,NULL,'Yes'),(8,'Entertainment',NULL,NULL,'Yes'),(9,'Hotel / Motel',NULL,NULL,'Yes'),(10,'Insurance',NULL,NULL,'Yes'),(11,'Interest Charges',NULL,NULL,'Yes'),(12,'Loan Payment',NULL,NULL,'Yes'),(13,'Medical',NULL,NULL,'Yes'),(14,'Mileage',NULL,NULL,'Yes'),(15,'Rent',NULL,NULL,'Yes'),(16,'Rental Car',NULL,NULL,'Yes'),(17,'Utility',NULL,NULL,'Yes');
/*!40000 ALTER TABLE `ExpensesCategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ExpensesPaymentMethods`
--

DROP TABLE IF EXISTS `ExpensesPaymentMethods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ExpensesPaymentMethods` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ExpensesPaymentMethods`
--

LOCK TABLES `ExpensesPaymentMethods` WRITE;
/*!40000 ALTER TABLE `ExpensesPaymentMethods` DISABLE KEYS */;
INSERT INTO `ExpensesPaymentMethods` VALUES (1,'Cash',NULL,NULL),(2,'Check',NULL,NULL),(3,'Credit Card',NULL,NULL),(4,'Debit Card',NULL,NULL);
/*!40000 ALTER TABLE `ExpensesPaymentMethods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ExperienceLevel`
--

DROP TABLE IF EXISTS `ExperienceLevel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ExperienceLevel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ExperienceLevel`
--

LOCK TABLES `ExperienceLevel` WRITE;
/*!40000 ALTER TABLE `ExperienceLevel` DISABLE KEYS */;
INSERT INTO `ExperienceLevel` VALUES (1,'Not Applicable'),(2,'Internship'),(3,'Entry level'),(4,'Associate'),(5,'Mid-Senior level'),(6,'Director'),(7,'Executive');
/*!40000 ALTER TABLE `ExperienceLevel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FieldNameMappings`
--

DROP TABLE IF EXISTS `FieldNameMappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FieldNameMappings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `textOrig` varchar(200) DEFAULT NULL,
  `textMapped` varchar(200) DEFAULT NULL,
  `display` enum('Form','Table and Form','Hidden') DEFAULT 'Form',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FieldNameMappings`
--

LOCK TABLES `FieldNameMappings` WRITE;
/*!40000 ALTER TABLE `FieldNameMappings` DISABLE KEYS */;
INSERT INTO `FieldNameMappings` VALUES (1,'Employee','employee_id','Employee Number','Employee Number','Table and Form',NULL,NULL),(2,'Employee','first_name','First Name','First Name','Table and Form',NULL,NULL),(3,'Employee','middle_name','Middle Name','Middle Name','Form',NULL,NULL),(4,'Employee','last_name','Last Name','Last Name','Table and Form',NULL,NULL),(5,'Employee','nationality','Nationality','Nationality','Form',NULL,NULL),(6,'Employee','ethnicity','Ethnicity','Ethnicity','Form',NULL,NULL),(7,'Employee','immigration_status','Immigration Status','Immigration Status','Form',NULL,NULL),(8,'Employee','birthday','Date of Birth','Date of Birth','Form',NULL,NULL),(9,'Employee','gender','Gender','Gender','Form',NULL,NULL),(10,'Employee','marital_status','Marital Status','Marital Status','Form',NULL,NULL),(11,'Employee','ssn_num','SSN/NRIC','SSN/NRIC','Form',NULL,NULL),(12,'Employee','nic_num','NIC','NIC','Form',NULL,NULL),(13,'Employee','other_id','Other ID','Other ID','Form',NULL,NULL),(14,'Employee','driving_license','Driving License No','Driving License No','Form',NULL,NULL),(15,'Employee','employment_status','Employment Status','Employment Status','Form',NULL,NULL),(16,'Employee','job_title','Job Title','Job Title','Form',NULL,NULL),(17,'Employee','pay_grade','Pay Grade','Pay Grade','Form',NULL,NULL),(18,'Employee','work_station_id','Work Station Id','Work Station Id','Form',NULL,NULL),(19,'Employee','address1','Address Line 1','Address Line 1','Form',NULL,NULL),(20,'Employee','address2','Address Line 2','Address Line 2','Form',NULL,NULL),(21,'Employee','city','City','City','Form',NULL,NULL),(22,'Employee','country','Country','Country','Form',NULL,NULL),(23,'Employee','province','Province','Province','Form',NULL,NULL),(24,'Employee','postal_code','Postal/Zip Code','Postal/Zip Code','Form',NULL,NULL),(25,'Employee','home_phone','Home Phone','Home Phone','Form',NULL,NULL),(26,'Employee','mobile_phone','Mobile Phone','Mobile Phone','Table and Form',NULL,NULL),(27,'Employee','work_phone','Work Phone','Work Phone','Form',NULL,NULL),(28,'Employee','work_email','Work Email','Work Email','Form',NULL,NULL),(29,'Employee','private_email','Private Email','Private Email','Form',NULL,NULL),(30,'Employee','joined_date','Joined Date','Joined Date','Form',NULL,NULL),(31,'Employee','confirmation_date','Confirmation Date','Confirmation Date','Form',NULL,NULL),(32,'Employee','termination_date','Termination Date','Termination Date','Form',NULL,NULL),(33,'Employee','supervisor','Supervisor','Supervisor','Table and Form',NULL,NULL),(34,'Employee','department','Department','Department','Table and Form',NULL,NULL),(35,'Employee','indirect_supervisors','Indirect Supervisors','Indirect Supervisors','Form',NULL,NULL),(36,'Employee','notes','Notes','Notes','Form',NULL,NULL);
/*!40000 ALTER TABLE `FieldNameMappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Files`
--

DROP TABLE IF EXISTS `Files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `employee` bigint(20) DEFAULT NULL,
  `file_group` varchar(100) NOT NULL,
  `size` bigint(20) DEFAULT NULL,
  `size_text` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Files`
--

LOCK TABLES `Files` WRITE;
/*!40000 ALTER TABLE `Files` DISABLE KEYS */;
INSERT INTO `Files` VALUES (6,'attachment_BI5XQCYFxZO12W1447383181684','attachment_BI5XQCYFxZO12W1447383181684.png',1,'Job',2000,'2MB'),(7,'attachment_Gqrmn2Jio6Dx5q1604471741077','attachment_Gqrmn2Jio6Dx5q1604471741077.pdf',1,'EmployeeDocument',3028,'2.96 K'),(8,'8aKr5iAx8aqN3E1607161608283','8aKr5iAx8aqN3E1607161608283.pdf',1,'AssetType',156245,'152.58 K');
/*!40000 ALTER TABLE `Files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Forms`
--

DROP TABLE IF EXISTS `Forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Forms` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `items` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Forms`
--

LOCK TABLES `Forms` WRITE;
/*!40000 ALTER TABLE `Forms` DISABLE KEYS */;
/*!40000 ALTER TABLE `Forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HiringPipeline`
--

DROP TABLE IF EXISTS `HiringPipeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HiringPipeline` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `type` enum('Short Listed','Phone Screen','Assessment','Interview','Offer','Hired','Rejected','Archived') DEFAULT 'Short Listed',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HiringPipeline`
--

LOCK TABLES `HiringPipeline` WRITE;
/*!40000 ALTER TABLE `HiringPipeline` DISABLE KEYS */;
INSERT INTO `HiringPipeline` VALUES (1,'Sourced','Short Listed',NULL),(2,'Applied','Short Listed',NULL),(3,'Phone Screen','Phone Screen',NULL),(4,'Assessment','Assessment',NULL),(5,'First Interview','Interview',NULL),(6,'Second Interview','Interview',NULL),(7,'Final Interview','Interview',NULL),(8,'Offer Sent','Offer',NULL),(9,'Offer Accepted','Offer',NULL),(10,'Offer Rejected','Offer',NULL),(11,'Not Qualified','Rejected',NULL),(12,'Archived','Archived',NULL),(13,'Hired','Hired',NULL);
/*!40000 ALTER TABLE `HiringPipeline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HoliDays`
--

DROP TABLE IF EXISTS `HoliDays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HoliDays` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `dateh` date DEFAULT NULL,
  `status` enum('Full Day','Half Day') DEFAULT 'Full Day',
  `country` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `holidays_dateh_country` (`dateh`,`country`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HoliDays`
--

LOCK TABLES `HoliDays` WRITE;
/*!40000 ALTER TABLE `HoliDays` DISABLE KEYS */;
INSERT INTO `HoliDays` VALUES (1,'New Year\'s Day','2015-01-01','Full Day',NULL),(2,'Christmas Day','2015-12-25','Full Day',NULL);
/*!40000 ALTER TABLE `HoliDays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ImmigrationDocuments`
--

DROP TABLE IF EXISTS `ImmigrationDocuments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ImmigrationDocuments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text,
  `required` enum('Yes','No') DEFAULT 'Yes',
  `alert_on_missing` enum('Yes','No') DEFAULT 'Yes',
  `alert_before_expiry` enum('Yes','No') DEFAULT 'Yes',
  `alert_before_day_number` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ImmigrationDocuments`
--

LOCK TABLES `ImmigrationDocuments` WRITE;
/*!40000 ALTER TABLE `ImmigrationDocuments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ImmigrationDocuments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ImmigrationStatus`
--

DROP TABLE IF EXISTS `ImmigrationStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ImmigrationStatus` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ImmigrationStatus`
--

LOCK TABLES `ImmigrationStatus` WRITE;
/*!40000 ALTER TABLE `ImmigrationStatus` DISABLE KEYS */;
INSERT INTO `ImmigrationStatus` VALUES (1,'Citizen'),(2,'Permanent Resident'),(3,'Work Permit Holder'),(4,'Dependant Pass Holder');
/*!40000 ALTER TABLE `ImmigrationStatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Industry`
--

DROP TABLE IF EXISTS `Industry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Industry` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Industry`
--

LOCK TABLES `Industry` WRITE;
/*!40000 ALTER TABLE `Industry` DISABLE KEYS */;
/*!40000 ALTER TABLE `Industry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Interviews`
--

DROP TABLE IF EXISTS `Interviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Interviews` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `job` bigint(20) NOT NULL,
  `candidate` bigint(20) DEFAULT NULL,
  `level` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `scheduled` datetime DEFAULT NULL,
  `location` varchar(500) DEFAULT NULL,
  `mapId` bigint(20) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `notes` text,
  `scheduleUpdated` int(11) DEFAULT '0',
  `interviewers` text,
  PRIMARY KEY (`id`),
  KEY `Fk_Interviews_Job` (`job`),
  KEY `Fk_Interviews_Candidates` (`candidate`),
  CONSTRAINT `Fk_Interviews_Candidates` FOREIGN KEY (`candidate`) REFERENCES `Candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_Interviews_Job` FOREIGN KEY (`job`) REFERENCES `Job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Interviews`
--

LOCK TABLES `Interviews` WRITE;
/*!40000 ALTER TABLE `Interviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `Interviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Job`
--

DROP TABLE IF EXISTS `Job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Job` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `shortDescription` text,
  `description` text,
  `requirements` text,
  `benefits` text,
  `country` bigint(20) DEFAULT NULL,
  `company` bigint(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `employementType` bigint(20) DEFAULT NULL,
  `industry` bigint(20) DEFAULT NULL,
  `experienceLevel` bigint(20) DEFAULT NULL,
  `jobFunction` bigint(20) DEFAULT NULL,
  `educationLevel` bigint(20) DEFAULT NULL,
  `currency` bigint(20) DEFAULT NULL,
  `showSalary` enum('Yes','No') DEFAULT NULL,
  `salaryMin` bigint(20) DEFAULT NULL,
  `salaryMax` bigint(20) DEFAULT NULL,
  `keywords` text,
  `status` enum('Active','On hold','Closed') DEFAULT NULL,
  `closingDate` datetime DEFAULT NULL,
  `attachment` varchar(100) DEFAULT NULL,
  `display` varchar(200) NOT NULL,
  `postedBy` bigint(20) DEFAULT NULL,
  `location` varchar(500) DEFAULT NULL,
  `postalCode` varchar(20) DEFAULT NULL,
  `hiringManager` bigint(20) DEFAULT NULL,
  `companyName` varchar(100) DEFAULT NULL,
  `showHiringManager` enum('Yes','No') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Job_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Job`
--

LOCK TABLES `Job` WRITE;
/*!40000 ALTER TABLE `Job` DISABLE KEYS */;
INSERT INTO `Job` VALUES (1,'Software Engineer','More than 375,000 users world-wide rely on our software for their daily business as it makes creating graphical presentations so much easier, faster and more enjoyable. Among our customers are many renowned consulting companies and large international corporations.','More than 375,000 users world-wide rely on our software for their daily business as it makes creating graphical presentations so much easier, faster and more enjoyable. Among our customers are many renowned consulting companies and large international corporations.\n\nWe follow our own strategy and do not have to make compromises with regard to code quality and beauty, because think-cell is profitable and has no outside investors. We are flourishing without program managers, meetings, and marketing-driven deadlines. Our code quality is extraordinarily high because we only release software when it is ready. We are willing to do the leg work of developing sophisticated algorithms and refining our user interface, which makes working with think-cells software so satisfying.','Challenging C++ coding with high personal responsibility\nWork with a competent and creative team in a modern loft office in Berlin\nFamily-friendly working hours, no deadlines\nAbove-average salary (we offer our developers EUR 120,000 annually after one year of employment)\nFree supply of drinks, fruits, sweets and snacks\nFlat hierarchies and plenty of room for your ideas\nA full-time company nanny who is available for free when children are sick, or when you just feel like spending an evening out','[\"Health plan\",\"Paid vacations\"]',226,2,NULL,'JC001',1,NULL,7,14,9,151,'Yes',3500,5500,'job, engineer','Active',NULL,NULL,'Text Only',1,NULL,NULL,NULL,NULL,NULL),(2,'QA Senior Test Automation Engineer','As a QA Senior Test Automation Engineer at Rocket you will help us launch the most successful startup companies around the world.','As a QA Senior Test Automation Engineer at Rocket you will help us launch the most successful startup companies around the world.','Responsibilities:\n\nAutomated testing of web and mobile applications\nDevelop automated scenarios/scripts using Cucumber (for web applications) and Calabash (for mobile applications)\nOptimize existing test cases to get more stability and efficiency\nRun automated functional tests as well as performance and load tests\nAnalyze automated test results and report bugs to responsible employees\nSupport the test automation team during the whole development process (starting from the analysis of requirements up to the integration of automated test cases into the CI system (Jenkins)\n\n\nRequirements:\n\nSeveral years of experience as a Test Automation Engineer ( 5+ years )\nExperience with automated solutions such as Cucumber/Calabash, Gherkin, Selenium or similar tools/frameworks\nExperience with Ruby, Python, PHP, JAVA or similar programming languages\nExperience with source code controls like SVN, GIT, CVS\nFamiliarity with Continuous Integration and Delivery\nExperience in Agile Methodologies like Scrum and Kanban or extreme programming\nFluency in speaking & writing English skills\nISTQB certification\n Technology stack we use:\n\nTools: GitHub, Jira, Confluence, Bamboo, Jenkins, Testlink\nScrum, Kanban\nCucumber, Calabash, Capybara, JMeter','[\"Life insurance\"]',80,3,NULL,'JC002',3,NULL,5,14,6,151,'Yes',4000,4500,'','Active',NULL,NULL,'Text Only',1,NULL,NULL,NULL,NULL,NULL),(3,'Online Editor','Online Editors required for a reputed news agency','Online Editors required for a reputed news agency','','[]',129,NULL,NULL,'J0003',1,NULL,7,23,9,103,'No',0,0,'','Active','0001-01-01 00:00:00','attachment_BI5XQCYFxZO12W1447383181684','Image and Full Text',1,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `Job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `JobFunction`
--

DROP TABLE IF EXISTS `JobFunction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `JobFunction` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `JobFunction`
--

LOCK TABLES `JobFunction` WRITE;
/*!40000 ALTER TABLE `JobFunction` DISABLE KEYS */;
INSERT INTO `JobFunction` VALUES (1,'Accounting/Auditing'),(2,'Administrative'),(3,'Advertising'),(4,'Business Analyst'),(5,'Financial Analyst'),(6,'Data Analyst'),(7,'Art/Creative'),(8,'Business Development'),(9,'Consulting'),(10,'Customer Service'),(11,'Distribution'),(12,'Design'),(13,'Education'),(14,'Engineering'),(15,'Finance'),(16,'General Business'),(17,'Health Care Provider'),(18,'Human Resources'),(19,'Information Technology'),(20,'Legal'),(21,'Management'),(22,'Manufacturing'),(23,'Marketing'),(24,'Other'),(25,'Public Relations'),(26,'Purchasing'),(27,'Product Management'),(28,'Project Management'),(29,'Production'),(30,'Quality Assurance'),(31,'Research'),(32,'Sales'),(33,'Science'),(34,'Strategy/Planning'),(35,'Supply Chain'),(36,'Training'),(37,'Writing/Editing');
/*!40000 ALTER TABLE `JobFunction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `JobTitles`
--

DROP TABLE IF EXISTS `JobTitles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `JobTitles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `specification` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `JobTitles`
--

LOCK TABLES `JobTitles` WRITE;
/*!40000 ALTER TABLE `JobTitles` DISABLE KEYS */;
INSERT INTO `JobTitles` VALUES (1,'SE','Software Engineer','The work of a software engineer typically includes designing and programming system-level software: operating systems, database systems, embedded systems and so on. They understand how both software a','Software Engineer'),(2,'ASE','Assistant Software Engineer','Assistant Software Engineer','Assistant Software Engineer'),(3,'PM','Project Manager','Project Manager','Project Manager'),(4,'QAE','QA Engineer','Quality Assurance Engineer ','Quality Assurance Engineer '),(5,'PRM','Product Manager','Product Manager','Product Manager'),(6,'AQAE','Assistant QA Engineer ','Assistant QA Engineer ','Assistant QA Engineer '),(7,'TPM','Technical Project Manager','Technical Project Manager','Technical Project Manager'),(8,'PRS','Pre-Sales Executive','Pre-Sales Executive','Pre-Sales Executive'),(9,'ME','Marketing Executive','Marketing Executive','Marketing Executive'),(10,'DH','Department Head','Department Head','Department Head'),(11,'CEO','Chief Executive Officer','Chief Executive Officer','Chief Executive Officer'),(12,'DBE','Database Engineer','Database Engineer','Database Engineer'),(13,'SA','Server Admin','Server Admin','Server Admin');
/*!40000 ALTER TABLE `JobTitles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Languages`
--

DROP TABLE IF EXISTS `Languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Languages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Languages`
--

LOCK TABLES `Languages` WRITE;
/*!40000 ALTER TABLE `Languages` DISABLE KEYS */;
INSERT INTO `Languages` VALUES (1,'en','English'),(2,'fr','French'),(3,'de','German'),(4,'zh','Chinese'),(5,'aa','Afar'),(6,'ab','Abkhaz'),(7,'ae','Avestan'),(8,'af','Afrikaans'),(9,'ak','Akan'),(10,'am','Amharic'),(11,'an','Aragonese'),(12,'ar','Arabic'),(13,'as','Assamese'),(14,'av','Avaric'),(15,'ay','Aymara'),(16,'az','Azerbaijani'),(17,'ba','Bashkir'),(18,'be','Belarusian'),(19,'bg','Bulgarian'),(20,'bh','Bihari'),(21,'bi','Bislama'),(22,'bm','Bambara'),(23,'bn','Bengali'),(24,'bo','Tibetan Standard, Tibetan, Central'),(25,'br','Breton'),(26,'bs','Bosnian'),(27,'ca','Catalan; Valencian'),(28,'ce','Chechen'),(29,'ch','Chamorro'),(30,'co','Corsican'),(31,'cr','Cree'),(32,'cs','Czech'),(33,'cu','Old Church Slavonic, Church Slavic, Church Slavonic, Old Bulgarian, Old Slavonic'),(34,'cv','Chuvash'),(35,'cy','Welsh'),(36,'da','Danish'),(37,'dv','Divehi; Dhivehi; Maldivian;'),(38,'dz','Dzongkha'),(39,'ee','Ewe'),(40,'el','Greek, Modern'),(41,'eo','Esperanto'),(42,'es','Spanish; Castilian'),(43,'et','Estonian'),(44,'eu','Basque'),(45,'fa','Persian'),(46,'ff','Fula; Fulah; Pulaar; Pular'),(47,'fi','Finnish'),(48,'fj','Fijian'),(49,'fo','Faroese'),(50,'fy','Western Frisian'),(51,'ga','Irish'),(52,'gd','Scottish Gaelic; Gaelic'),(53,'gl','Galician'),(54,'gn','Guaran'),(55,'gu','Gujarati'),(56,'gv','Manx'),(57,'ha','Hausa'),(58,'he','Hebrew (modern)'),(59,'hi','Hindi'),(60,'ho','Hiri Motu'),(61,'hr','Croatian'),(62,'ht','Haitian; Haitian Creole'),(63,'hu','Hungarian'),(64,'hy','Armenian'),(65,'hz','Herero'),(66,'ia','Interlingua'),(67,'id','Indonesian'),(68,'ie','Interlingue'),(69,'ig','Igbo'),(70,'ii','Nuosu'),(71,'ik','Inupiaq'),(72,'io','Ido'),(73,'is','Icelandic'),(74,'it','Italian'),(75,'iu','Inuktitut'),(76,'ja','Japanese (ja)'),(77,'jv','Javanese (jv)'),(78,'ka','Georgian'),(79,'kg','Kongo'),(80,'ki','Kikuyu, Gikuyu'),(81,'kj','Kwanyama, Kuanyama'),(82,'kk','Kazakh'),(83,'kl','Kalaallisut, Greenlandic'),(84,'km','Khmer'),(85,'kn','Kannada'),(86,'ko','Korean'),(87,'kr','Kanuri'),(88,'ks','Kashmiri'),(89,'ku','Kurdish'),(90,'kv','Komi'),(91,'kw','Cornish'),(92,'ky','Kirghiz, Kyrgyz'),(93,'la','Latin'),(94,'lb','Luxembourgish, Letzeburgesch'),(95,'lg','Luganda'),(96,'li','Limburgish, Limburgan, Limburger'),(97,'ln','Lingala'),(98,'lo','Lao'),(99,'lt','Lithuanian'),(100,'lu','Luba-Katanga'),(101,'lv','Latvian'),(102,'mg','Malagasy'),(103,'mh','Marshallese'),(104,'mi','Maori'),(105,'mk','Macedonian'),(106,'ml','Malayalam'),(107,'mn','Mongolian'),(108,'mr','Marathi (Mara?hi)'),(109,'ms','Malay'),(110,'mt','Maltese'),(111,'my','Burmese'),(112,'na','Nauru'),(113,'nb','Norwegian Bokml'),(114,'nd','North Ndebele'),(115,'ne','Nepali'),(116,'ng','Ndonga'),(117,'nl','Dutch'),(118,'nn','Norwegian Nynorsk'),(119,'no','Norwegian'),(120,'nr','South Ndebele'),(121,'nv','Navajo, Navaho'),(122,'ny','Chichewa; Chewa; Nyanja'),(123,'oc','Occitan'),(124,'oj','Ojibwe, Ojibwa'),(125,'om','Oromo'),(126,'or','Oriya'),(127,'os','Ossetian, Ossetic'),(128,'pa','Panjabi, Punjabi'),(129,'pi','Pali'),(130,'pl','Polish'),(131,'ps','Pashto, Pushto'),(132,'pt','Portuguese'),(133,'qu','Quechua'),(134,'rm','Romansh'),(135,'rn','Kirundi'),(136,'ro','Romanian, Moldavian, Moldovan'),(137,'ru','Russian'),(138,'rw','Kinyarwanda'),(139,'sa','Sanskrit (Sa?sk?ta)'),(140,'sc','Sardinian'),(141,'sd','Sindhi'),(142,'se','Northern Sami'),(143,'sg','Sango'),(144,'si','Sinhala, Sinhalese'),(145,'sk','Slovak'),(146,'sl','Slovene'),(147,'sm','Samoan'),(148,'sn','Shona'),(149,'so','Somali'),(150,'sq','Albanian'),(151,'sr','Serbian'),(152,'ss','Swati'),(153,'st','Southern Sotho'),(154,'su','Sundanese'),(155,'sv','Swedish'),(156,'sw','Swahili'),(157,'ta','Tamil'),(158,'te','Telugu'),(159,'tg','Tajik'),(160,'th','Thai'),(161,'ti','Tigrinya'),(162,'tk','Turkmen'),(163,'tl','Tagalog'),(164,'tn','Tswana'),(165,'to','Tonga (Tonga Islands)'),(166,'tr','Turkish'),(167,'ts','Tsonga'),(168,'tt','Tatar'),(169,'tw','Twi'),(170,'ty','Tahitian'),(171,'ug','Uighur, Uyghur'),(172,'uk','Ukrainian'),(173,'ur','Urdu'),(174,'uz','Uzbek'),(175,'ve','Venda'),(176,'vi','Vietnamese'),(177,'vo','Volapk'),(178,'wa','Walloon'),(179,'wo','Wolof'),(180,'xh','Xhosa'),(181,'yi','Yiddish'),(182,'yo','Yoruba'),(183,'za','Zhuang, Chuang'),(184,'zu','Zulu');
/*!40000 ALTER TABLE `Languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LeaveGroupEmployees`
--

DROP TABLE IF EXISTS `LeaveGroupEmployees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LeaveGroupEmployees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `leave_group` bigint(20) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_LeaveGroupEmployees_LeaveGroups` (`leave_group`),
  KEY `Fk_LeaveGroupEmployees_Employee` (`employee`),
  CONSTRAINT `Fk_LeaveGroupEmployees_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_LeaveGroupEmployees_LeaveGroups` FOREIGN KEY (`leave_group`) REFERENCES `LeaveGroups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LeaveGroupEmployees`
--

LOCK TABLES `LeaveGroupEmployees` WRITE;
/*!40000 ALTER TABLE `LeaveGroupEmployees` DISABLE KEYS */;
/*!40000 ALTER TABLE `LeaveGroupEmployees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LeaveGroups`
--

DROP TABLE IF EXISTS `LeaveGroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LeaveGroups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LeaveGroups`
--

LOCK TABLES `LeaveGroups` WRITE;
/*!40000 ALTER TABLE `LeaveGroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `LeaveGroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LeavePeriods`
--

DROP TABLE IF EXISTS `LeavePeriods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LeavePeriods` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Inactive',
  `country` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LeavePeriods`
--

LOCK TABLES `LeavePeriods` WRITE;
/*!40000 ALTER TABLE `LeavePeriods` DISABLE KEYS */;
INSERT INTO `LeavePeriods` VALUES (3,'Year 2015','2015-01-01','2015-12-31','Active',NULL),(4,'Year 2016','2016-01-01','2016-12-31','Active',NULL),(5,'Year 2017','2017-01-01','2017-12-31','Active',NULL),(6,'Period 2018-01-01 to 2018-12-31','2018-01-01','2018-12-31','Active',NULL),(7,'Period 2019-01-01 to 2019-12-31','2019-01-01','2019-12-31','Active',NULL),(8,'Period 2020-01-01 to 2020-12-31','2020-01-01','2020-12-31','Active',NULL),(9,'Period 2021-01-01 to 2021-12-31','2021-01-01','2021-12-31','Active',NULL);
/*!40000 ALTER TABLE `LeavePeriods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LeaveRules`
--

DROP TABLE IF EXISTS `LeaveRules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LeaveRules` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `leave_type` bigint(20) NOT NULL,
  `job_title` bigint(20) DEFAULT NULL,
  `employment_status` bigint(20) DEFAULT NULL,
  `employee` bigint(20) DEFAULT NULL,
  `supervisor_leave_assign` enum('Yes','No') DEFAULT 'Yes',
  `employee_can_apply` enum('Yes','No') DEFAULT 'Yes',
  `apply_beyond_current` enum('Yes','No') DEFAULT 'Yes',
  `leave_accrue` enum('No','Yes') DEFAULT 'No',
  `carried_forward` enum('No','Yes') DEFAULT 'No',
  `default_per_year` decimal(10,3) NOT NULL,
  `carried_forward_percentage` int(11) DEFAULT '0',
  `carried_forward_leave_availability` int(11) DEFAULT '365',
  `propotionate_on_joined_date` enum('No','Yes') DEFAULT 'No',
  `leave_group` bigint(20) DEFAULT NULL,
  `max_carried_forward_amount` int(11) DEFAULT '0',
  `exp_days` int(11) DEFAULT NULL,
  `leave_period` bigint(20) DEFAULT NULL,
  `department` bigint(20) DEFAULT NULL,
  `employee_leave_period` enum('Yes','No') DEFAULT 'No',
  PRIMARY KEY (`id`),
  KEY `Fk_LeaveRules_leave_period` (`leave_period`),
  KEY `Fk_LeaveRules_department` (`department`),
  CONSTRAINT `Fk_LeaveRules_department` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`),
  CONSTRAINT `Fk_LeaveRules_leave_period` FOREIGN KEY (`leave_period`) REFERENCES `LeavePeriods` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LeaveRules`
--

LOCK TABLES `LeaveRules` WRITE;
/*!40000 ALTER TABLE `LeaveRules` DISABLE KEYS */;
INSERT INTO `LeaveRules` VALUES (1,1,11,NULL,NULL,'No','Yes','Yes','No','No',25.000,0,365,'No',NULL,0,NULL,NULL,NULL,'No'),(2,2,NULL,NULL,2,'No','Yes','Yes','No','No',10.000,0,365,'No',NULL,0,NULL,NULL,NULL,'No'),(3,4,NULL,1,NULL,'Yes','Yes','No','No','Yes',10.000,100,30,'Yes',NULL,0,365,NULL,NULL,'Yes');
/*!40000 ALTER TABLE `LeaveRules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LeaveStartingBalance`
--

DROP TABLE IF EXISTS `LeaveStartingBalance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LeaveStartingBalance` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `leave_type` bigint(20) NOT NULL,
  `employee` bigint(20) DEFAULT NULL,
  `leave_period` bigint(20) NOT NULL,
  `amount` decimal(10,3) NOT NULL,
  `note` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LeaveStartingBalance`
--

LOCK TABLES `LeaveStartingBalance` WRITE;
/*!40000 ALTER TABLE `LeaveStartingBalance` DISABLE KEYS */;
/*!40000 ALTER TABLE `LeaveStartingBalance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LeaveTypes`
--

DROP TABLE IF EXISTS `LeaveTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LeaveTypes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `supervisor_leave_assign` enum('Yes','No') DEFAULT 'Yes',
  `employee_can_apply` enum('Yes','No') DEFAULT 'Yes',
  `apply_beyond_current` enum('Yes','No') DEFAULT 'Yes',
  `leave_accrue` enum('No','Yes') DEFAULT 'No',
  `carried_forward` enum('No','Yes') DEFAULT 'No',
  `default_per_year` decimal(10,3) NOT NULL,
  `carried_forward_percentage` int(11) DEFAULT '0',
  `carried_forward_leave_availability` int(11) DEFAULT '365',
  `propotionate_on_joined_date` enum('No','Yes') DEFAULT 'No',
  `send_notification_emails` enum('Yes','No') DEFAULT 'Yes',
  `leave_group` bigint(20) DEFAULT NULL,
  `leave_color` varchar(10) DEFAULT NULL,
  `max_carried_forward_amount` int(11) DEFAULT '0',
  `employee_leave_period` enum('Yes','No') DEFAULT 'No',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LeaveTypes`
--

LOCK TABLES `LeaveTypes` WRITE;
/*!40000 ALTER TABLE `LeaveTypes` DISABLE KEYS */;
INSERT INTO `LeaveTypes` VALUES (1,'Annual leave','No','Yes','No','No','No',14.000,0,365,'No','Yes',NULL,NULL,0,'No'),(2,'Casual leave','Yes','Yes','No','No','No',7.000,0,365,'No','Yes',NULL,NULL,0,'No'),(3,'Medical leave','Yes','Yes','Yes','No','No',7.000,0,365,'No','Yes',NULL,NULL,0,'No'),(4,'Employee Leave','Yes','Yes','No','No','No',0.000,100,30,'Yes','Yes',NULL,'#2ad19f',0,'Yes');
/*!40000 ALTER TABLE `LeaveTypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Migrations`
--

DROP TABLE IF EXISTS `Migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Migrations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file` varchar(50) NOT NULL,
  `version` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` enum('Pending','Up','Down','UpError','DownError') DEFAULT 'Pending',
  `last_error` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `KEY_Migrations_file` (`file`),
  KEY `KEY_Migrations_status` (`status`),
  KEY `KEY_Migrations_version` (`version`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Migrations`
--

LOCK TABLES `Migrations` WRITE;
/*!40000 ALTER TABLE `Migrations` DISABLE KEYS */;
INSERT INTO `Migrations` VALUES (1,'v20161116_190001_unique_index_cron_name.php',190001,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(2,'v20170310_190401_add_timesheet_changes.php',190401,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(3,'v20170621_190401_report_modifications.php',190401,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(4,'v20170702_190500_add_attendance_image.php',190500,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(5,'v20170908_200000_payroll_group.php',200000,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(6,'v20170918_200000_add_attendance_image_out.php',200000,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(7,'v20171001_200201_update_attendance_out.php',200201,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(8,'v20171003_200301_add_deduction_group_payroll.php',200301,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(9,'v20171003_200302_payroll_meta_export.php',200302,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(10,'v20171126_200303_swift_mail.php',200303,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(11,'v20180305_210100_drop_si_hi_languages.php',210100,'2020-06-09 22:23:50','2020-06-09 22:23:50','Up',NULL),(12,'v20180317_210200_leave_rule_experience.php',210200,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(13,'v20180325_210101_delete_leave_group_employee.php',210101,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(14,'v20180417_210501_update_menu_names.php',210501,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(15,'v20180507_230001_update_travel_record_type.php',230001,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(16,'v20180514_230002_add_conversation_tables.php',230002,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(17,'v20180527_230003_update_menu_names.php',230003,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(18,'v20180602_230004_add_gsuite_fields.php',230004,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(19,'v20180615_230402_remove_eh_manager.php',230402,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(20,'v20180622_240001_set_valid_until_null.php',240001,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(21,'v20180623_240002_update_employee_report.php',240002,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(22,'v20180801_240003_asset_management.php',240003,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(23,'v20180808_250004_add_languages.php',250004,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(24,'v20180810_250005_performance_review.php',250005,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(25,'v20180912_250006_remove_null_users.php',250006,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(26,'v20181025_260001_dept_based_leave_periods.php',260001,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(27,'v20181106_260002_add_arabic_lang.php',260002,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(28,'v20190125_260003_attendance_with_map.php',260003,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(29,'v20190508_260004_update_time_zones.php',260004,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(30,'v20190509_260004_add_location_filed_to_job.php',260004,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(31,'v20190510_260004_add_hiring_manager_job.php',260004,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(32,'v20190616_260501_candidate_email_cron.php',260501,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(33,'v20190630_260501_recruitment_email_cron.php',260501,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(34,'v20190630_260601_update_module_names.php',260601,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(35,'v20190630_260602_add_leave_period_to_rule.php',260602,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(36,'v20190630_260603_add_dept_leave_to_rule.php',260603,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(37,'v20190707_260004_attendance_out_map.php',260004,'2020-06-09 22:23:50','2020-06-09 22:23:51','Up',NULL),(38,'v20190707_260005_attendance_location.php',260005,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(39,'v20190707_260006_google_map_api.php',260006,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(40,'v20190805_260007_fix_expense_table.php',260007,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(41,'v20190919_260008_employee_leave_periods.php',260008,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(42,'v20191022_270001_joined_date_settings.php',270001,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(43,'v20191024_270002_report_migrations.php',270002,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(44,'v20191024_270003_payroll_column_function.php',270003,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(45,'v20191024_270004_add_object_type_import.php',270004,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(46,'v20191024_270005_update_data_importers.php',270005,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(47,'v20191118_270006_update_data_importers.php',270006,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(48,'v20191121_270007_team_management.php',270007,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(49,'v20191121_270008_custom_user_roles.php',270008,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(50,'v20200224_270004_update_module_names.php',270004,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(51,'v20200311_270005_update_gender.php',270005,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(52,'v20200316_270006_add_provinces.php',270006,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(53,'v20200404_270006_password_rate_limit.php',270006,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(54,'v20200411_270009_email_log.php',270009,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(55,'v20200429_270010_setting_groups.php',270010,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(56,'v20200430_270008_partial_leave_setting.php',270008,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(57,'v20200518_270011_add_al_language.php',270011,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(58,'v20200530_270009_update_module_names.php',270009,'2020-06-09 22:23:50','2020-06-09 22:23:52','Up',NULL),(59,'v20200609_270012_partial_leave_setting.php',270012,'2020-11-02 13:36:52','2020-11-02 13:36:52','Up',NULL),(60,'v20200620_270014_update_module_names.php',270014,'2020-11-02 13:36:52','2020-11-02 13:36:52','Up',NULL),(61,'v20200828_270101_user_role_additional.php',270101,'2020-11-02 13:36:52','2020-11-02 13:36:52','Up',NULL),(62,'v20200828_270102_module_user_role_blacklist.php',270102,'2020-11-02 13:36:52','2020-11-02 13:36:52','Up',NULL),(63,'v20200828_270103_remove_country_leave.php',270103,'2020-11-02 13:36:52','2020-11-02 13:36:52','Up',NULL),(64,'v20201017_271101_switch_off_photo_att.php',271101,'2020-11-02 13:36:52','2020-11-02 13:36:52','Up',NULL),(65,'v20201028_280001_update_module_names.php',280001,'2020-11-02 13:36:52','2020-11-02 13:36:52','Up',NULL),(66,'v20201028_280002_update_gender.php',280002,'2020-11-02 13:36:52','2020-11-02 13:36:52','Up',NULL);
/*!40000 ALTER TABLE `Migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Modules`
--

DROP TABLE IF EXISTS `Modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Modules` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `menu` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `mod_group` varchar(30) NOT NULL,
  `mod_order` int(11) DEFAULT NULL,
  `status` enum('Enabled','Disabled') DEFAULT 'Enabled',
  `version` varchar(10) DEFAULT '',
  `update_path` varchar(500) DEFAULT '',
  `user_levels` varchar(500) NOT NULL,
  `user_roles` text,
  `user_roles_blacklist` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Modules_name_modgroup` (`name`,`mod_group`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Modules`
--

LOCK TABLES `Modules` WRITE;
/*!40000 ALTER TABLE `Modules` DISABLE KEYS */;
INSERT INTO `Modules` VALUES (1,'Admin','assets','Company Assets','fa-archive','admin',891,'Enabled','','admin>assets','[\"Admin\",\"Manager\"]','',NULL),(2,'Employees','attendance','Monitor Attendance','fa-clock','admin',8,'Enabled','','admin>attendance','[\"Admin\",\"Manager\"]','',NULL),(3,'Admin','audit','Audit Log','fa-compass','admin',92,'Enabled','','admin>audit','[\"Admin\"]','',NULL),(4,'Recruitment','candidates','Candidates','fa-users','admin',3,'Enabled','','admin>candidates','[\"Admin\",\"Manager\"]','',NULL),(5,'Insights','charts','Time and Attendance','fa-user-clock','admin',1,'Enabled','','admin>charts','[\"Admin\",\"Manager\"]','',NULL),(6,'Admin','clients','Clients','fa-user-circle','admin',52,'Enabled','','admin>clients','[\"Admin\",\"Manager\"]','',NULL),(7,'Admin','company_structure','Company Structure','fa-building','admin',2,'Enabled','','admin>company_structure','[\"Admin\",\"Manager\"]','',NULL),(8,'Admin','dashboard','Dashboard','fa-desktop','admin',1,'Enabled','','admin>dashboard','[\"Admin\",\"Other\"]','',NULL),(9,'System','data','Data','fa-database','admin',8,'Enabled','','admin>data','[\"Admin\"]','',NULL),(10,'Employees','documents','Document Management','fa-file-alt','admin',2,'Enabled','','admin>documents','[\"Admin\",\"Manager\"]','',NULL),(11,'Employees','employeehistory','Employee History','fa-tasks','admin',11,'Enabled','','admin>employeehistory','[\"Admin\"]','',NULL),(12,'Employees','employees','Employees','fa-users','admin',1,'Enabled','','admin>employees','[\"Admin\",\"Manager\"]','',NULL),(13,'Admin','expenses','Expenses','fa-bars','admin',81,'Enabled','','admin>expenses','[\"Admin\",\"Manager\"]','',NULL),(14,'Admin','fieldnames','Employee Fields','fa-ruler-horizontal','admin',83,'Enabled','','admin>fieldnames','[\"Admin\"]','',NULL),(15,'Employees','forms','HR Form Management','fa-folder','admin',81,'Enabled','','admin>forms','[\"Admin\",\"Manager\"]','',NULL),(16,'Recruitment','jobpositions','Job Positions','fa-columns','admin',2,'Enabled','','admin>jobpositions','[\"Admin\",\"Manager\"]','',NULL),(17,'Admin','jobs','Job Details Setup','fa-columns','admin',3,'Enabled','','admin>jobs','[\"Admin\"]','',NULL),(18,'Recruitment','jobsetup','Recruitment Setup','fa-random','admin',1,'Enabled','','admin>jobsetup','[\"Admin\"]','',NULL),(19,'Insights','leave_charts','Leave Timeline','fa-calendar-alt','admin',2,'Enabled','','admin>leave_charts','[\"Admin\",\"Manager\"]','',NULL),(20,'Admin','leaves','Leave Settings','fa-pause','admin',8,'Enabled','','admin>leaves','[\"Admin\"]','',NULL),(21,'Admin','loans','Company Loans','fa-money-check','admin',89,'Enabled','','admin>loans','[\"Admin\"]','',NULL),(22,'System','metadata','Manage Metadata','fa-microchip','admin',6,'Enabled','','admin>metadata','[\"Admin\"]','',NULL),(23,'System','modules','Manage Modules','fa-folder-open','admin',3,'Enabled','','admin>modules','[\"Admin\"]','',NULL),(24,'Admin','overtime','Overtime','fa-align-center','admin',82,'Enabled','','admin>overtime','[\"Admin\",\"Manager\"]','',NULL),(25,'Payroll','payroll','Payroll Reports','fa-cogs','admin',6,'Enabled','','admin>payroll','[\"Admin\"]','',NULL),(26,'Employees','performance','Performance Reviews','fa-bezier-curve','admin',82,'Enabled','','admin>performance','[\"Admin\",\"Manager\"]','',NULL),(27,'System','permissions','Manage Permissions','fa-unlock','admin',4,'Enabled','','admin>permissions','[\"Admin\"]','',NULL),(28,'Admin','projects','Projects','fa-list-alt','admin',51,'Enabled','','admin>projects','[\"Admin\",\"Manager\"]','',NULL),(29,'Admin','qualifications','Qualifications Setup','fa-check-square','admin',4,'Enabled','','admin>qualifications','[\"Admin\",\"Manager\"]','',NULL),(30,'Admin Reports','report_files','Report Files','fa-file-export','admin',2,'Enabled','','admin>report_files','[\"Admin\",\"Manager\"]','',NULL),(31,'Admin Reports','reports','Reports','fa-window-maximize','admin',1,'Enabled','','admin>reports','[\"Admin\",\"Manager\"]','',NULL),(32,'Payroll','salary','Salary','fa-money-check-alt','admin',1,'Enabled','','admin>salary','[\"Admin\"]','',NULL),(33,'System','settings','Settings','fa-cogs','admin',1,'Enabled','','admin>settings','[\"Admin\"]','',NULL),(34,'Employees','teams','Company Teams','fa-users','admin',83,'Enabled','','admin>teams','[\"Admin\",\"Manager\"]','',NULL),(35,'Admin','training','Training Setup','fa-briefcase','admin',5,'Enabled','','admin>training','[\"Admin\",\"Manager\"]','',NULL),(36,'Employees','travel','Travel Requests','fa-plane','admin',6,'Enabled','','admin>travel','[\"Admin\",\"Manager\"]','',NULL),(37,'System','users','Users','fa-user','admin',2,'Enabled','','admin>users','[\"Admin\"]','',NULL),(38,'Discussions','announcements','Announcements','fa-bullhorn','user',1,'Enabled','','modules>announcements','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(39,'Time Management','attendance','Attendance','fa-clock','user',2,'Enabled','','modules>attendance','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(40,'Time Management','attendance_sheets','Attendance Sheets','fa-calendar-check','user',4,'Enabled','','modules>attendance_sheets','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(41,'Discussions','conversations','Conversations','fa-comment','user',2,'Enabled','','modules>conversations','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(42,'Personal Information','dashboard','Dashboard','fa-desktop','user',1,'Enabled','','modules>dashboard','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(43,'Personal Information','dependents','Dependents','fa-expand','user',5,'Enabled','','modules>dependents','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(44,'Documents','documents','My Documents','fa-file','user',1,'Enabled','','modules>documents','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(45,'Personal Information','emergency_contact','Emergency Contacts','fa-phone-square','user',6,'Enabled','','modules>emergency_contact','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(46,'Personal Information','employees','Basic Information','fa-user','user',2,'Enabled','','modules>employees','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(47,'Finance','expenses','Expenses','fa-bars','user',1,'Enabled','','modules>expenses','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(48,'Documents','forms','HR Forms','fa-folder','user',2,'Enabled','','modules>forms','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(49,'Leave','leavecal','Leave Calendar','fa-calendar','user',2,'Enabled','','modules>leavecal','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(50,'Leave','leaves','Leave Management','fa-share-alt','user',1,'Enabled','','modules>leaves','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(51,'Finance','loans','Loans','fa-money-check','user',3,'Enabled','','modules>loans','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(52,'Time Management','overtime','Overtime Requests','fa-calendar-plus','user',5,'Enabled','','modules>overtime','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(53,'Performance','performance','Reviews','fa-compress-arrows-alt','user',1,'Enabled','','modules>performance','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(54,'Time Management','projects','Projects','fa-project-diagram','user',1,'Enabled','','modules>projects','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(55,'Personal Information','qualifications','Qualifications','fa-graduation-cap','user',3,'Enabled','','modules>qualifications','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(56,'User Reports','report_files','Report Files','fa-file-export','user',2,'Enabled','','modules>report_files','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(57,'User Reports','reports','Reports','fa-window-maximize','user',1,'Enabled','','modules>reports','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(58,'Finance','salary','Salary','fa-calculator','user',2,'Enabled','','modules>salary','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(59,'Company','staffdirectory','Staff Directory','fa-user','user',1,'Enabled','','modules>staffdirectory','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(60,'Time Management','time_sheets','Time Sheets','fa-stopwatch','user',3,'Enabled','','modules>time_sheets','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(61,'Training','training','Training','fa-briefcase','user',1,'Enabled','','modules>training','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(62,'Travel Management','travel','Travel','fa-plane','user',1,'Enabled','','modules>travel','[\"Admin\",\"Manager\",\"Employee\"]','',NULL),(70,'Tasks','tasks','My Tasks','fa-tasks','extension',0,'Enabled','','extension>tasks','[\"Admin\",\"Manager\",\"User\"]','',NULL);
/*!40000 ALTER TABLE `Modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Nationality`
--

DROP TABLE IF EXISTS `Nationality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Nationality` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Nationality`
--

LOCK TABLES `Nationality` WRITE;
/*!40000 ALTER TABLE `Nationality` DISABLE KEYS */;
INSERT INTO `Nationality` VALUES (1,'Afghan'),(2,'Albanian'),(3,'Algerian'),(4,'American'),(5,'Andorran'),(6,'Angolan'),(7,'Antiguans'),(8,'Argentinean'),(9,'Armenian'),(10,'Australian'),(11,'Austrian'),(12,'Azerbaijani'),(13,'Bahamian'),(14,'Bahraini'),(15,'Bangladeshi'),(16,'Barbadian'),(17,'Barbudans'),(18,'Batswana'),(19,'Belarusian'),(20,'Belgian'),(21,'Belizean'),(22,'Beninese'),(23,'Bhutanese'),(24,'Bolivian'),(25,'Bosnian'),(26,'Brazilian'),(27,'British'),(28,'Bruneian'),(29,'Bulgarian'),(30,'Burkinabe'),(31,'Burmese'),(32,'Burundian'),(33,'Cambodian'),(34,'Cameroonian'),(35,'Canadian'),(36,'Cape Verdean'),(37,'Central African'),(38,'Chadian'),(39,'Chilean'),(40,'Chinese'),(41,'Colombian'),(42,'Comoran'),(43,'Congolese'),(44,'Costa Rican'),(45,'Croatian'),(46,'Cuban'),(47,'Cypriot'),(48,'Czech'),(49,'Danish'),(50,'Djibouti'),(51,'Dominican'),(52,'Dutch'),(53,'East Timorese'),(54,'Ecuadorean'),(55,'Egyptian'),(56,'Emirian'),(57,'Equatorial Guinean'),(58,'Eritrean'),(59,'Estonian'),(60,'Ethiopian'),(61,'Fijian'),(62,'Filipino'),(63,'Finnish'),(64,'French'),(65,'Gabonese'),(66,'Gambian'),(67,'Georgian'),(68,'German'),(69,'Ghanaian'),(70,'Greek'),(71,'Grenadian'),(72,'Guatemalan'),(73,'Guinea-Bissauan'),(74,'Guinean'),(75,'Guyanese'),(76,'Haitian'),(77,'Herzegovinian'),(78,'Honduran'),(79,'Hungarian'),(80,'I-Kiribati'),(81,'Icelander'),(82,'Indian'),(83,'Indonesian'),(84,'Iranian'),(85,'Iraqi'),(86,'Irish'),(87,'Israeli'),(88,'Italian'),(89,'Ivorian'),(90,'Jamaican'),(91,'Japanese'),(92,'Jordanian'),(93,'Kazakhstani'),(94,'Kenyan'),(95,'Kittian and Nevisian'),(96,'Kuwaiti'),(97,'Kyrgyz'),(98,'Laotian'),(99,'Latvian'),(100,'Lebanese'),(101,'Liberian'),(102,'Libyan'),(103,'Liechtensteiner'),(104,'Lithuanian'),(105,'Luxembourger'),(106,'Macedonian'),(107,'Malagasy'),(108,'Malawian'),(109,'Malaysian'),(110,'Maldivan'),(111,'Malian'),(112,'Maltese'),(113,'Marshallese'),(114,'Mauritanian'),(115,'Mauritian'),(116,'Mexican'),(117,'Micronesian'),(118,'Moldovan'),(119,'Monacan'),(120,'Mongolian'),(121,'Moroccan'),(122,'Mosotho'),(123,'Motswana'),(124,'Mozambican'),(125,'Namibian'),(126,'Nauruan'),(127,'Nepalese'),(128,'New Zealander'),(129,'Nicaraguan'),(130,'Nigerian'),(131,'Nigerien'),(132,'North Korean'),(133,'Northern Irish'),(134,'Norwegian'),(135,'Omani'),(136,'Pakistani'),(137,'Palauan'),(138,'Panamanian'),(139,'Papua New Guinean'),(140,'Paraguayan'),(141,'Peruvian'),(142,'Polish'),(143,'Portuguese'),(144,'Qatari'),(145,'Romanian'),(146,'Russian'),(147,'Rwandan'),(148,'Saint Lucian'),(149,'Salvadoran'),(150,'Samoan'),(151,'San Marinese'),(152,'Sao Tomean'),(153,'Saudi'),(154,'Scottish'),(155,'Senegalese'),(156,'Serbian'),(157,'Seychellois'),(158,'Sierra Leonean'),(159,'Singaporean'),(160,'Slovakian'),(161,'Slovenian'),(162,'Solomon Islander'),(163,'Somali'),(164,'South African'),(165,'South Korean'),(166,'Spanish'),(167,'Sri Lankan'),(168,'Sudanese'),(169,'Surinamer'),(170,'Swazi'),(171,'Swedish'),(172,'Swiss'),(173,'Syrian'),(174,'Taiwanese'),(175,'Tajik'),(176,'Tanzanian'),(177,'Thai'),(178,'Togolese'),(179,'Tongan'),(180,'Trinidadian or Tobagonian'),(181,'Tunisian'),(182,'Turkish'),(183,'Tuvaluan'),(184,'Ugandan'),(185,'Ukrainian'),(186,'Uruguayan'),(187,'Uzbekistani'),(188,'Venezuelan'),(189,'Vietnamese'),(190,'Welsh'),(191,'Yemenite'),(192,'Zambian'),(193,'Zimbabwean');
/*!40000 ALTER TABLE `Nationality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Notifications`
--

DROP TABLE IF EXISTS `Notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `time` datetime DEFAULT NULL,
  `fromUser` bigint(20) DEFAULT NULL,
  `fromEmployee` bigint(20) DEFAULT NULL,
  `toUser` bigint(20) NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `message` text,
  `action` text,
  `type` varchar(100) DEFAULT NULL,
  `status` enum('Unread','Read') DEFAULT 'Unread',
  `employee` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `toUser_time` (`toUser`,`time`),
  KEY `toUser_status_time` (`toUser`,`status`,`time`),
  CONSTRAINT `Fk_Notifications_Users` FOREIGN KEY (`toUser`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Notifications`
--

LOCK TABLES `Notifications` WRITE;
/*!40000 ALTER TABLE `Notifications` DISABLE KEYS */;
INSERT INTO `Notifications` VALUES (1,'2020-11-08 11:00:37',1,1,2,'https://avatars.dicebear.com/api/initials/:IEe5eab.svg','IceHrm Employee applied for a leave. Visit leave module to approve or reject','{\"type\":\"url\",\"url\":\"g=modules&n=leaves&m=module_Leaves#tabSubEmployeeLeaveAll\"}','Leave Module','Unread',2),(2,'2020-11-15 00:21:39',1,1,4,'https://avatars.dicebear.com/api/initials/:IEe5eab.svg','IceHrm Employee started a performance review for you. Please fill in and submit the self-assessment before the due date','{\"type\":\"url\",\"url\":\"g=modules&n=performance&m=module_Performance\"}','Performance Review','Unread',4),(3,'2020-11-15 00:53:39',2,2,5,'https://avatars.dicebear.com/api/initials/:LLa93c5.svg','Lala Lamees requested you to provide a feedback for a performance review','{\"type\":\"url\",\"url\":\"g=modules&n=performance&m=module_Performance#tabReviewFeedback\"}','Feedback','Read',6),(4,'2020-11-15 00:54:45',5,6,2,'https://avatars.dicebear.com/api/initials/:JW453d2.svg','Jane Welington submitted the performance review feedback','{\"type\":\"url\",\"url\":\"g=modules&n=performance&m=module_Performance#tabCoordinatedPerformanceReview\"}','Feedback','Unread',2),(5,'2020-11-15 00:58:30',2,2,5,'https://avatars.dicebear.com/api/initials/:LLa93c5.svg','Lala Lamees requested you to provide a feedback for a performance review','{\"type\":\"url\",\"url\":\"g=modules&n=performance&m=module_Performance#tabReviewFeedback\"}','Feedback','Unread',6),(6,'2020-11-15 00:58:50',5,6,2,'https://avatars.dicebear.com/api/initials/:JW453d2.svg','Jane Welington submitted the performance review feedback','{\"type\":\"url\",\"url\":\"g=modules&n=performance&m=module_Performance#tabCoordinatedPerformanceReview\"}','Feedback','Unread',2),(7,'2020-12-03 13:01:13',1,1,4,'https://avatars.dicebear.com/api/initials/:IEe5eab.svg','IceHrm Employee started a performance review for you. Please fill in and submit the self-assessment before the due date','{\"type\":\"url\",\"url\":\"g=modules&n=performance&m=module_Performance\"}','Performance Review','Unread',4),(8,'2021-01-08 13:26:13',1,1,2,'https://avatars.dicebear.com/api/initials/:IEe5eab.svg','IceHrm Employee applied for a leave. Visit leave module to approve or reject','{\"type\":\"url\",\"url\":\"g=modules&n=leaves&m=module_Leaves#tabSubEmployeeLeaveAll\"}','Leave Module','Unread',2),(9,'2021-01-08 13:29:44',1,1,2,'https://avatars.dicebear.com/api/initials/:IEe5eab.svg','IceHrm Employee applied for a leave. Visit leave module to approve or reject','{\"type\":\"url\",\"url\":\"g=modules&n=leaves&m=module_Leaves#tabSubEmployeeLeaveAll\"}','Leave Module','Unread',2),(10,'2021-01-08 13:33:16',1,1,5,'https://avatars.dicebear.com/api/initials/:IEe5eab.svg','Sofia O\'Sullivan applied for a leave. Visit leave module to approve or reject','{\"type\":\"url\",\"url\":\"g=modules&n=leaves&m=module_Leaves#tabSubEmployeeLeaveAll\"}','Leave Module','Unread',6),(11,'2021-01-08 13:34:01',3,3,5,'https://avatars.dicebear.com/api/initials/:SO4da9d.svg','Sofia O\'Sullivan applied for a leave. Visit leave module to approve or reject','{\"type\":\"url\",\"url\":\"g=modules&n=leaves&m=module_Leaves#tabSubEmployeeLeaveAll\"}','Leave Module','Unread',6);
/*!40000 ALTER TABLE `Notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `OvertimeCategories`
--

DROP TABLE IF EXISTS `OvertimeCategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `OvertimeCategories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `OvertimeCategories`
--

LOCK TABLES `OvertimeCategories` WRITE;
/*!40000 ALTER TABLE `OvertimeCategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `OvertimeCategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PayFrequency`
--

DROP TABLE IF EXISTS `PayFrequency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PayFrequency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PayFrequency`
--

LOCK TABLES `PayFrequency` WRITE;
/*!40000 ALTER TABLE `PayFrequency` DISABLE KEYS */;
INSERT INTO `PayFrequency` VALUES (1,'Bi Weekly'),(2,'Weekly'),(3,'Semi Monthly'),(4,'Monthly'),(5,'Yearly');
/*!40000 ALTER TABLE `PayFrequency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PayGrades`
--

DROP TABLE IF EXISTS `PayGrades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PayGrades` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `currency` varchar(3) NOT NULL,
  `min_salary` decimal(12,2) DEFAULT '0.00',
  `max_salary` decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `Fk_PayGrades_CurrencyTypes` (`currency`),
  CONSTRAINT `Fk_PayGrades_CurrencyTypes` FOREIGN KEY (`currency`) REFERENCES `CurrencyTypes` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PayGrades`
--

LOCK TABLES `PayGrades` WRITE;
/*!40000 ALTER TABLE `PayGrades` DISABLE KEYS */;
INSERT INTO `PayGrades` VALUES (1,'Manager','SGD',5000.00,15000.00),(2,'Executive','SGD',3500.00,7000.00),(3,'Assistant ','SGD',2000.00,4000.00),(4,'Administrator','SGD',2000.00,6000.00);
/*!40000 ALTER TABLE `PayGrades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Payroll`
--

DROP TABLE IF EXISTS `Payroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Payroll` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `pay_period` bigint(20) NOT NULL,
  `department` bigint(20) NOT NULL,
  `column_template` bigint(20) DEFAULT NULL,
  `columns` varchar(500) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `status` enum('Draft','Completed','Processing') DEFAULT 'Draft',
  `payslipTemplate` bigint(20) DEFAULT NULL,
  `deduction_group` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Payroll`
--

LOCK TABLES `Payroll` WRITE;
/*!40000 ALTER TABLE `Payroll` DISABLE KEYS */;
INSERT INTO `Payroll` VALUES (1,'R1',4,1,NULL,'[\"1\",\"2\"]','2021-01-01','2021-01-31','Draft',1,1);
/*!40000 ALTER TABLE `Payroll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PayrollColumnTemplates`
--

DROP TABLE IF EXISTS `PayrollColumnTemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PayrollColumnTemplates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `columns` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PayrollColumnTemplates`
--

LOCK TABLES `PayrollColumnTemplates` WRITE;
/*!40000 ALTER TABLE `PayrollColumnTemplates` DISABLE KEYS */;
/*!40000 ALTER TABLE `PayrollColumnTemplates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PayrollColumns`
--

DROP TABLE IF EXISTS `PayrollColumns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PayrollColumns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `calculation_hook` varchar(200) DEFAULT NULL,
  `salary_components` varchar(500) DEFAULT NULL,
  `deductions` varchar(500) DEFAULT NULL,
  `add_columns` varchar(500) DEFAULT NULL,
  `sub_columns` varchar(500) DEFAULT NULL,
  `colorder` int(11) DEFAULT NULL,
  `editable` enum('Yes','No') DEFAULT 'Yes',
  `enabled` enum('Yes','No') DEFAULT 'Yes',
  `default_value` varchar(25) DEFAULT NULL,
  `calculation_columns` varchar(500) DEFAULT NULL,
  `calculation_function` text,
  `deduction_group` bigint(20) DEFAULT NULL,
  `function_type` enum('Simple','Advanced') DEFAULT 'Simple',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PayrollColumns`
--

LOCK TABLES `PayrollColumns` WRITE;
/*!40000 ALTER TABLE `PayrollColumns` DISABLE KEYS */;
INSERT INTO `PayrollColumns` VALUES (1,'Test1',NULL,'[]','[]','[]','[]',1,'Yes','Yes','0','','',NULL,'Advanced'),(2,'Test2',NULL,'[]','[]','[]','[]',2,'Yes','Yes','0','[{\"name\":\"t1\",\"column\":\"1\",\"id\":\"calculation_columns_1\"}]','function main() {\nreturn 2;\n}\nmain();',NULL,'Advanced');
/*!40000 ALTER TABLE `PayrollColumns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PayrollData`
--

DROP TABLE IF EXISTS `PayrollData`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PayrollData` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll` bigint(20) NOT NULL,
  `employee` bigint(20) NOT NULL,
  `payroll_item` int(11) NOT NULL,
  `amount` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `PayrollDataUniqueKey` (`payroll`,`employee`,`payroll_item`),
  CONSTRAINT `Fk_PayrollData_Payroll` FOREIGN KEY (`payroll`) REFERENCES `Payroll` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PayrollData`
--

LOCK TABLES `PayrollData` WRITE;
/*!40000 ALTER TABLE `PayrollData` DISABLE KEYS */;
/*!40000 ALTER TABLE `PayrollData` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PayrollEmployees`
--

DROP TABLE IF EXISTS `PayrollEmployees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PayrollEmployees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `pay_frequency` int(11) DEFAULT NULL,
  `currency` bigint(20) DEFAULT NULL,
  `deduction_exemptions` varchar(250) DEFAULT NULL,
  `deduction_allowed` varchar(250) DEFAULT NULL,
  `deduction_group` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `PayrollEmployees_employee` (`employee`),
  KEY `Fk_PayrollEmployees_DeductionGroup` (`deduction_group`),
  CONSTRAINT `Fk_PayrollEmployee_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_PayrollEmployees_DeductionGroup` FOREIGN KEY (`deduction_group`) REFERENCES `DeductionGroup` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PayrollEmployees`
--

LOCK TABLES `PayrollEmployees` WRITE;
/*!40000 ALTER TABLE `PayrollEmployees` DISABLE KEYS */;
INSERT INTO `PayrollEmployees` VALUES (1,1,4,151,'[]','[]',1),(2,2,4,151,'[]','[]',1),(3,3,4,151,'[]','[]',1),(4,4,2,151,'[]','[]',1),(6,6,4,151,'[]','[]',1),(7,7,4,151,'[]','[]',1),(8,8,4,151,'[]','[]',1),(9,9,4,151,'[]','[]',1),(10,10,4,151,'[]','[]',1);
/*!40000 ALTER TABLE `PayrollEmployees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PayslipTemplates`
--

DROP TABLE IF EXISTS `PayslipTemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PayslipTemplates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `data` longtext,
  `status` enum('Show','Hide') DEFAULT 'Show',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PayslipTemplates`
--

LOCK TABLES `PayslipTemplates` WRITE;
/*!40000 ALTER TABLE `PayslipTemplates` DISABLE KEYS */;
INSERT INTO `PayslipTemplates` VALUES (1,'te','',NULL,'2021-01-26 13:48:43','2021-01-26 13:48:43');
/*!40000 ALTER TABLE `PayslipTemplates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PerformanceReviews`
--

DROP TABLE IF EXISTS `PerformanceReviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PerformanceReviews` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `employee` bigint(20) DEFAULT NULL,
  `coordinator` bigint(20) DEFAULT NULL,
  `attendees` varchar(50) NOT NULL,
  `form` bigint(20) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `review_date` datetime DEFAULT NULL,
  `review_period_start` datetime DEFAULT NULL,
  `review_period_end` datetime DEFAULT NULL,
  `self_assessment_due` datetime DEFAULT NULL,
  `notes` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_PerformanceReviews_ReviewTemplates` (`form`),
  KEY `Fk_PerformanceReviews_Employees1` (`employee`),
  KEY `Fk_PerformanceReviews_Employees2` (`coordinator`),
  CONSTRAINT `Fk_PerformanceReviews_Employees1` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_PerformanceReviews_Employees2` FOREIGN KEY (`coordinator`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_PerformanceReviews_ReviewTemplates` FOREIGN KEY (`form`) REFERENCES `ReviewTemplates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PerformanceReviews`
--

LOCK TABLES `PerformanceReviews` WRITE;
/*!40000 ALTER TABLE `PerformanceReviews` DISABLE KEYS */;
INSERT INTO `PerformanceReviews` VALUES (1,'Review for Taylor Holmes / from 2020-05-01 to 2020-12-31',1,2,'[\"1\"]',1,'Pending','2020-12-04 10:31:51','2020-05-01 00:00:00','2020-12-31 00:00:00','2020-11-26 00:00:00','[{\"id\":\"notes_1\",\"note\":\"Test note 1\",\"date\":\"3-Dec-2020 08:17 AM\"},{\"id\":\"notes_2\",\"note\":\"fdgfdgfdgdfg\",\"date\":\"3-Dec-2020 08:31 AM\"}]','2020-11-15 00:21:39','2020-11-15 00:21:39');
/*!40000 ALTER TABLE `PerformanceReviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Permissions`
--

DROP TABLE IF EXISTS `Permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Permissions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_level` enum('Admin','Employee','Manager') DEFAULT NULL,
  `module_id` bigint(20) NOT NULL,
  `permission` varchar(200) DEFAULT NULL,
  `meta` varchar(500) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Module_Permission` (`user_level`,`module_id`,`permission`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Permissions`
--

LOCK TABLES `Permissions` WRITE;
/*!40000 ALTER TABLE `Permissions` DISABLE KEYS */;
INSERT INTO `Permissions` VALUES (1,'Manager',6,'Add Clients','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(2,'Manager',6,'Edit Clients','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(3,'Manager',6,'Delete Clients','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(4,'Manager',7,'Add Company Structure','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(5,'Manager',7,'Edit Company Structure','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(6,'Manager',7,'Delete Company Structure','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(7,'Manager',28,'Add Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(8,'Manager',28,'Edit Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(9,'Manager',28,'Delete Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(10,'Manager',29,'Add Skills','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(11,'Manager',29,'Edit Skills','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(12,'Manager',29,'Delete Skills','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(13,'Manager',29,'Add Education','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(14,'Manager',29,'Edit Education','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(15,'Manager',29,'Delete Education','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(16,'Manager',29,'Add Certifications','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(17,'Manager',29,'Edit Certifications','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(18,'Manager',29,'Delete Certifications','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(19,'Manager',29,'Add Languages','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(20,'Manager',29,'Edit Languages','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(21,'Manager',29,'Delete Languages','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(22,'Manager',38,'Post Announcements','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(23,'Employee',38,'Post Announcements','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(24,'Manager',43,'Add Dependents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(25,'Manager',43,'Edit Dependents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(26,'Manager',43,'Delete Dependents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(27,'Employee',43,'Add Dependents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(28,'Employee',43,'Edit Dependents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(29,'Employee',43,'Delete Dependents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(30,'Manager',44,'Add Documents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(31,'Manager',44,'Edit Documents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(32,'Manager',44,'Delete Documents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(33,'Employee',44,'Add Documents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(34,'Employee',44,'Edit Documents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(35,'Employee',44,'Delete Documents','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(36,'Manager',45,'Add Emergency Contacts','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(37,'Manager',45,'Edit Emergency Contacts','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(38,'Manager',45,'Delete Emergency Contacts','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(39,'Employee',45,'Add Emergency Contacts','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(40,'Employee',45,'Edit Emergency Contacts','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(41,'Employee',45,'Delete Emergency Contacts','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(42,'Manager',46,'Edit Employee Number','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(43,'Manager',46,'Edit EPF/CPF Number','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(44,'Manager',46,'Edit Employment Status','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(45,'Manager',46,'Edit Job Title','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(46,'Manager',46,'Edit Pay Grade','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(47,'Manager',46,'Edit Joined Date','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(48,'Manager',46,'Edit Department','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(49,'Manager',46,'Edit Work Email','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(50,'Manager',46,'Edit Country','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(51,'Manager',46,'Upload/Delete Profile Image','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(52,'Manager',46,'Edit Employee Details','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(53,'Employee',46,'Edit Employee Number','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(54,'Employee',46,'Edit EPF/CPF Number','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(55,'Employee',46,'Edit Employment Status','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(56,'Employee',46,'Edit Job Title','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(57,'Employee',46,'Edit Pay Grade','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(58,'Employee',46,'Edit Joined Date','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(59,'Employee',46,'Edit Department','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(60,'Employee',46,'Edit Work Email','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(61,'Employee',46,'Edit Country','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(62,'Employee',46,'Upload/Delete Profile Image','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(63,'Employee',46,'Edit Employee Details','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(64,'Employee',47,'Add Expenses','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(65,'Employee',47,'Edit Expenses','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(66,'Employee',47,'Delete Expenses','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(67,'Manager',54,'Add Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(68,'Manager',54,'Edit Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(69,'Manager',54,'Delete Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(70,'Employee',54,'Add Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(71,'Employee',54,'Edit Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(72,'Employee',54,'Delete Projects','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(73,'Manager',58,'Add Salary','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(74,'Manager',58,'Edit Salary','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(75,'Manager',58,'Delete Salary','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(76,'Employee',58,'Add Salary','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(77,'Employee',58,'Edit Salary','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(78,'Employee',58,'Delete Salary','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(79,'Employee',61,'Delete Assigned Training Sessions','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','No'),(80,'Manager',61,'Delete Training Sessions of Direct Reports','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(81,'Manager',62,'Add Travel Request','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(82,'Manager',62,'Edit Travel Request','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(83,'Manager',62,'Delete Travel Request','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(84,'Employee',62,'Add Travel Request','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(85,'Employee',62,'Edit Travel Request','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes'),(86,'Employee',62,'Delete Travel Request','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Yes\",\"Yes\"],[\"No\",\"No\"]]}]','Yes');
/*!40000 ALTER TABLE `Permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Projects`
--

DROP TABLE IF EXISTS `Projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Projects` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `client` bigint(20) DEFAULT NULL,
  `details` text,
  `created` datetime DEFAULT NULL,
  `status` enum('Active','On Hold','Completed','Dropped') DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `Fk_Projects_Client` (`client`),
  CONSTRAINT `Fk_Projects_Client` FOREIGN KEY (`client`) REFERENCES `Clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Projects`
--

LOCK TABLES `Projects` WRITE;
/*!40000 ALTER TABLE `Projects` DISABLE KEYS */;
INSERT INTO `Projects` VALUES (1,'Project 1',3,NULL,'2013-01-03 05:53:38','Active'),(2,'Project 2',3,NULL,'2013-01-03 05:54:22','Active'),(3,'Project 3',1,NULL,'2013-01-03 05:55:02','Active'),(4,'Project 4',2,NULL,'2013-01-03 05:56:16','Active');
/*!40000 ALTER TABLE `Projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Province`
--

DROP TABLE IF EXISTS `Province`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Province` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL DEFAULT '',
  `code` char(2) NOT NULL DEFAULT '',
  `country` char(2) NOT NULL DEFAULT 'US',
  PRIMARY KEY (`id`),
  KEY `Fk_Province_Country` (`country`),
  CONSTRAINT `Fk_Province_Country` FOREIGN KEY (`country`) REFERENCES `Country` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Province`
--

LOCK TABLES `Province` WRITE;
/*!40000 ALTER TABLE `Province` DISABLE KEYS */;
INSERT INTO `Province` VALUES (1,'Alaska','AK','US'),(2,'Alabama','AL','US'),(3,'American Samoa','AS','US'),(4,'Arizona','AZ','US'),(5,'Arkansas','AR','US'),(6,'California','CA','US'),(7,'Colorado','CO','US'),(8,'Connecticut','CT','US'),(9,'Delaware','DE','US'),(10,'District of Columbia','DC','US'),(11,'Federated States of Micronesia','FM','US'),(12,'Florida','FL','US'),(13,'Georgia','GA','US'),(14,'Guam','GU','US'),(15,'Hawaii','HI','US'),(16,'Idaho','ID','US'),(17,'Illinois','IL','US'),(18,'Indiana','IN','US'),(19,'Iowa','IA','US'),(20,'Kansas','KS','US'),(21,'Kentucky','KY','US'),(22,'Louisiana','LA','US'),(23,'Maine','ME','US'),(24,'Marshall Islands','MH','US'),(25,'Maryland','MD','US'),(26,'Massachusetts','MA','US'),(27,'Michigan','MI','US'),(28,'Minnesota','MN','US'),(29,'Mississippi','MS','US'),(30,'Missouri','MO','US'),(31,'Montana','MT','US'),(32,'Nebraska','NE','US'),(33,'Nevada','NV','US'),(34,'New Hampshire','NH','US'),(35,'New Jersey','NJ','US'),(36,'New Mexico','NM','US'),(37,'New York','NY','US'),(38,'North Carolina','NC','US'),(39,'North Dakota','ND','US'),(40,'Northern Mariana Islands','MP','US'),(41,'Ohio','OH','US'),(42,'Oklahoma','OK','US'),(43,'Oregon','OR','US'),(44,'Palau','PW','US'),(45,'Pennsylvania','PA','US'),(46,'Puerto Rico','PR','US'),(47,'Rhode Island','RI','US'),(48,'South Carolina','SC','US'),(49,'South Dakota','SD','US'),(50,'Tennessee','TN','US'),(51,'Texas','TX','US'),(52,'Utah','UT','US'),(53,'Vermont','VT','US'),(54,'Virgin Islands','VI','US'),(55,'Virginia','VA','US'),(56,'Washington','WA','US'),(57,'West Virginia','WV','US'),(58,'Wisconsin','WI','US'),(59,'Wyoming','WY','US'),(60,'Armed Forces Africa','AE','US'),(61,'Armed Forces Americas (except Canada)','AA','US'),(62,'Armed Forces Canada','AE','US'),(63,'Armed Forces Europe','AE','US'),(64,'Armed Forces Middle East','AE','US'),(65,'Armed Forces Pacific','AP','US'),(66,'Andaman and Nicobar Islands','AN','IN'),(67,'Andhra Pradesh','AP','IN'),(68,'Arunachal Pradesh','AR','IN'),(69,'Assam','AS','IN'),(70,'Bihar','BR','IN'),(71,'Chandigarh','CH','IN'),(72,'Chhattisgarh','CG','IN'),(73,'Dadra and Nagar Haveli','DN','IN'),(74,'Daman and Diu','DD','IN'),(75,'Delhi','DL','IN'),(76,'Goa','GA','IN'),(77,'Gujarat','GJ','IN'),(78,'Haryana','HR','IN'),(79,'Himachal Pradesh','HP','IN'),(80,'Jammu and Kashmir','JK','IN'),(81,'Karnataka','KA','IN'),(82,'Kerala','KL','IN'),(83,'Lakshadweep Islands','LD','IN'),(84,'Madhya Pradesh','MP','IN'),(85,'Maharashtra','MH','IN'),(86,'Manipur','MN','IN'),(87,'Meghalaya','ML','IN'),(88,'Mizoram','MZ','IN'),(89,'Nagaland','NL','IN'),(90,'Nagaland','NL','IN'),(91,'Odisha','OR','IN'),(92,'Puducherry','PY','IN'),(93,'Punjab','PB','IN'),(94,'Rajasthan','RJ','IN'),(95,'Sikkim','SK','IN'),(96,'Tamil Nadu','TN','IN'),(97,'Telangana','TS','IN'),(98,'Tripura','TR','IN'),(99,'Uttar Pradesh','UP','IN'),(100,'Uttarakhand','UK','IN'),(101,'West Bengal','WB','IN');
/*!40000 ALTER TABLE `Province` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ReportFiles`
--

DROP TABLE IF EXISTS `ReportFiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ReportFiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `attachment` varchar(100) NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ReportFiles_attachment` (`attachment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ReportFiles`
--

LOCK TABLES `ReportFiles` WRITE;
/*!40000 ALTER TABLE `ReportFiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `ReportFiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Reports`
--

DROP TABLE IF EXISTS `Reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Reports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text,
  `parameters` text,
  `query` text,
  `paramOrder` varchar(500) NOT NULL,
  `type` enum('Query','Class') DEFAULT 'Query',
  `report_group` varchar(500) DEFAULT NULL,
  `output` varchar(15) NOT NULL DEFAULT 'CSV',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Reports_Name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Reports`
--

LOCK TABLES `Reports` WRITE;
/*!40000 ALTER TABLE `Reports` DISABLE KEYS */;
INSERT INTO `Reports` VALUES (1,'Employee Details Report','This report list all employee details and you can filter employees by department, employment status or job title','[[\"department\", {\"label\":\"Department\",\"type\":\"select2\",\"remote-source\":[\"CompanyStructure\",\"id\",\"title\"],\"allow-null\":true}],[\"employment_status\", {\"label\":\"Employment Status\",\"type\":\"select2\",\"remote-source\":[\"EmploymentStatus\",\"id\",\"name\"],\"allow-null\":true}],[\"job_title\", {\"label\":\"Job Title\",\"type\":\"select2\",\"remote-source\":[\"JobTitle\",\"id\",\"name\"],\"allow-null\":true}]]','EmployeeDetailsReport','[\"department\",\"employment_status\",\"job_title\"]','Class','Employee Information','CSV'),(2,'Employee Attendance Report','This report list all employee attendance entries by employee and date range','[\r\n[ \"employee\", {\"label\":\"Employee\",\"type\":\"select2multi\",\"allow-null\":true,\"null-label\":\"All Employees\",\"remote-source\":[\"Employee\",\"id\",\"first_name+last_name\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','EmployeeAttendanceReport','[\"employee\",\"date_start\",\"date_end\"]','Class','Time Management','CSV'),(3,'Employee Time Tracking Report','This report list employee working hours and attendance details for each day for a given period ','[\r\n[ \"employee\", {\"label\":\"Employee\",\"type\":\"select2\",\"allow-null\":false,\"remote-source\":[\"Employee\",\"id\",\"first_name+last_name\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','EmployeeTimeTrackReport','[\"employee\",\"date_start\",\"date_end\"]','Class','Time Management','CSV'),(4,'Employee Time Entry Report','View employee time entries by date range and project','[\r\n[ \"employee\", {\"label\":\"Employee\",\"type\":\"select2multi\",\"allow-null\":true,\"null-label\":\"All Employees\",\"remote-source\":[\"Employee\",\"id\",\"first_name+last_name\"]}],\r\n[ \"client\", {\"label\":\"Select Client\",\"type\":\"select\",\"allow-null\":true,\"null-label\":\"Not Selected\",\"remote-source\":[\"Client\",\"id\",\"name\"]}],\r\n[ \"project\", {\"label\":\"Or Project\",\"type\":\"select\",\"allow-null\":true,\"null-label\":\"All Projects\",\"remote-source\":[\"Project\",\"id\",\"name\",\"getAllProjects\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','EmployeeTimesheetReport','[\"employee\",\"client\",\"project\",\"date_start\",\"date_end\",\"status\"]','Class','Time Management','CSV'),(5,'Active Employee Report','This report list employees who are currently active based on joined date and termination date ','[\r\n[ \"department\", {\"label\":\"Department\",\"type\":\"select2\",\"remote-source\":[\"CompanyStructure\",\"id\",\"title\"],\"allow-null\":true}]\r\n]','ActiveEmployeeReport','[\"department\"]','Class','Employee Information','CSV'),(6,'New Hires Employee Report','This report list employees who are joined between given two dates ','[[ \"department\", {\"label\":\"Department\",\"type\":\"select2\",\"remote-source\":[\"CompanyStructure\",\"id\",\"title\"],\"allow-null\":true}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','NewHiresEmployeeReport','[\"department\",\"date_start\",\"date_end\"]','Class','Employee Information','CSV'),(7,'Terminated Employee Report','This report list employees who are terminated between given two dates ','[[ \"department\", {\"label\":\"Department\",\"type\":\"select2\",\"remote-source\":[\"CompanyStructure\",\"id\",\"title\"],\"allow-null\":true}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','TerminatedEmployeeReport','[\"department\",\"date_start\",\"date_end\"]','Class','Employee Information','CSV'),(8,'Travel Request Report','This report list employees travel requests for a specified period','[\r\n[ \"employee\", {\"label\":\"Employee\",\"type\":\"select2multi\",\"allow-null\":true,\"null-label\":\"All Employees\",\"remote-source\":[\"Employee\",\"id\",\"first_name+last_name\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}],\r\n[ \"status\", {\"label\":\"Status\",\"type\":\"select\",\"source\":[[\"NULL\",\"All Statuses\"],[\"Approved\",\"Approved\"],[\"Pending\",\"Pending\"],[\"Rejected\",\"Rejected\"],[\"Cancellation Requested\",\"Cancellation Requested\"],[\"Cancelled\",\"Cancelled\"]]}]\r\n]','TravelRequestReport','[\"employee\",\"date_start\",\"date_end\",\"status\"]','Class','Travel and Expense Management','CSV'),(9,'Employee Time Sheet Report','This report list all employee time sheets by employee and date range','[\r\n[ \"employee\", {\"label\":\"Employee\",\"type\":\"select2multi\",\"allow-null\":true,\"null-label\":\"All Employees\",\"remote-source\":[\"Employee\",\"id\",\"first_name+last_name\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}],\r\n[ \"status\", {\"label\":\"Status\",\"allow-null\":true,\"null-label\":\"All Status\",\"type\":\"select\",\"source\":[[\"Approved\",\"Approved\"],[\"Pending\",\"Pending\"],[\"Rejected\",\"Rejected\"]]}]\r\n]','EmployeeTimeSheetData','[\"employee\",\"date_start\",\"date_end\",\"status\"]','Class','Time Management','CSV'),(11,'Payroll Meta Data Export','Export payroll module configurations','[\r\n[ \"deduction_group\", {\"label\":\"Calculation Group\",\"type\":\"select2\",\"allow-null\":false,\"remote-source\":[\"DeductionGroup\",\"id\",\"name\"]}],\r\n[ \"payroll\", {\"label\":\"Sample Payroll\",\"type\":\"select2\",\"allow-null\":false,\"remote-source\":[\"Payroll\",\"id\",\"name\"]}]]','PayrollDataExport','[\"deduction_group\",\"payroll\"]','Class','Payroll','JSON'),(12,'Company Asset Report','List company assets assigned to employees and departments','[[\"department\", {\"label\":\"Department\",\"type\":\"select2\",\"remote-source\":[\"CompanyStructure\",\"id\",\"title\"],\"allow-null\":true}],[\"type\", {\"label\":\"Asset Type\",\"type\":\"select2\",\"remote-source\":[\"AssetType\",\"id\",\"name\"],\"allow-null\":true}]]','AssetUsageReport','[\"department\",\"type\"]','Class','Resources','CSV'),(13,'Employee Leaves Report','This report list all employee leaves by employee, date range and leave status','[\r\n[ \"employee\", {\"label\":\"Employee\",\"type\":\"select2multi\",\"allow-null\":true,\"null-label\":\"All Employees\",\"remote-source\":[\"Employee\",\"id\",\"first_name+last_name\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}],\r\n[ \"status\", {\"label\":\"Leave Status\",\"type\":\"select\",\"source\":[[\"NULL\",\"All Statuses\"],[\"Approved\",\"Approved\"],[\"Pending\",\"Pending\"],[\"Rejected\",\"Rejected\"],[\"Cancellation Requested\",\"Cancellation Requested\"],[\"Cancelled\",\"Cancelled\"]]}]\r\n]','EmployeeLeavesReport','[\"employee\",\"date_start\",\"date_end\",\"status\"]','Class','Leave Management','CSV'),(14,'Employee Leave Entitlement','This report list employees leave entitlement for current leave period by department or by employee ','[[ \"department\", {\"label\":\"Department\",\"type\":\"select2\",\"remote-source\":[\"CompanyStructure\",\"id\",\"title\"],\"allow-null\":true,\"validation\":\"none\"}],\r\n[ \"employee\", {\"label\":\"Employee\",\"type\":\"select2\",\"allow-null\":true,\"validation\":\"none\",\"remote-source\":[\"Employee\",\"id\",\"first_name+last_name\"]}]]','EmployeeLeaveEntitlementReport','[\"department\",\"employee\"]','Class','Leave Management','CSV'),(15,'Expense Report','This report list employees expenses for a specified period','[\r\n[ \"employee\", {\"label\":\"Employee\",\"type\":\"select2multi\",\"allow-null\":true,\"null-label\":\"All Employees\",\"remote-source\":[\"Employee\",\"id\",\"first_name+last_name\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}],\r\n[ \"status\", {\"label\":\"Status\",\"type\":\"select\",\"source\":[[\"NULL\",\"All Statuses\"],[\"Approved\",\"Approved\"],[\"Pending\",\"Pending\"],[\"Rejected\",\"Rejected\"],[\"Cancellation Requested\",\"Cancellation Requested\"],[\"Cancelled\",\"Cancelled\"]]}]\r\n]','ExpenseReport','[\"employee\",\"date_start\",\"date_end\",\"status\"]','Class','Travel and Expense Management','CSV');
/*!40000 ALTER TABLE `Reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RestAccessTokens`
--

DROP TABLE IF EXISTS `RestAccessTokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RestAccessTokens` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userId` bigint(20) NOT NULL,
  `hash` varchar(32) DEFAULT NULL,
  `token` varchar(500) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RestAccessTokens`
--

LOCK TABLES `RestAccessTokens` WRITE;
/*!40000 ALTER TABLE `RestAccessTokens` DISABLE KEYS */;
INSERT INTO `RestAccessTokens` VALUES (1,1,'3a2941647e47222c78fd669e76c43dde','ogB3hjKFGGCKOfUDtX+LpW/EPdRzJF7bQYoxwM40kLdjsXb9PZAwo/IWz6ej7sFsAKG8YYM+sb2hDpEpfa0zpUBz4CciDw==','2020-06-09 22:23:50','2021-02-02 04:18:18'),(2,2,'3e0e67510aa8898b510176404729ab7f','GgDcjP4CpV/Hsr8KTdqZAio72XHQiYJTtdMfweh2Zbh7OFRjZrx+wfzchHLnyLw89EOnztgUqKJL3mlMk8/R5HRYqWbPEQ==','2020-11-06 13:32:06','2020-11-06 13:32:06'),(3,5,'e813d98dc9fd6085ffc40d2318d74dbc','NAGVaeIusF/RGKgk+zJE5KZiAWj9vN/lfQr0qSrvUq/HTGZ9OdtpDykwNZTmq+3SZ+vCECOkxT1BdqTgeoSKHS6ameW2uw==','2020-11-15 00:54:18','2020-11-15 00:54:18'),(4,3,'d92d8103b4252dc69269c66365fb4724','iwB2fdkR+F/wkQFKUCVzA9E5Fj8MIcftO8vc70VBgKzxBPmIWvRQQgTv/0MLkF86lWyFHd+zZETP9kUcu0z6jp3a/HKwag==','2021-01-08 13:33:37','2021-01-08 13:33:37');
/*!40000 ALTER TABLE `RestAccessTokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ReviewFeedbacks`
--

DROP TABLE IF EXISTS `ReviewFeedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ReviewFeedbacks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) DEFAULT NULL,
  `review` bigint(20) DEFAULT NULL,
  `subject` bigint(20) DEFAULT NULL,
  `form` bigint(20) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `dueon` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_ReviewFeedbacks_ReviewTemplates` (`form`),
  KEY `Fk_ReviewFeedbacks_PerformanceReviews` (`review`),
  KEY `Fk_ReviewFeedbacks_Employees1` (`employee`),
  KEY `Fk_ReviewFeedbacks_Employees2` (`subject`),
  CONSTRAINT `Fk_ReviewFeedbacks_Employees1` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_ReviewFeedbacks_Employees2` FOREIGN KEY (`subject`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_ReviewFeedbacks_PerformanceReviews` FOREIGN KEY (`review`) REFERENCES `PerformanceReviews` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_ReviewFeedbacks_ReviewTemplates` FOREIGN KEY (`form`) REFERENCES `ReviewTemplates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ReviewFeedbacks`
--

LOCK TABLES `ReviewFeedbacks` WRITE;
/*!40000 ALTER TABLE `ReviewFeedbacks` DISABLE KEYS */;
INSERT INTO `ReviewFeedbacks` VALUES (1,6,1,4,2,'Submitted','2020-11-19 00:00:00','2020-11-15 00:53:39','2020-11-15 00:53:39'),(2,6,1,4,2,'Pending','2020-11-25 00:00:00','2020-11-15 00:58:30','2020-11-15 00:58:30');
/*!40000 ALTER TABLE `ReviewFeedbacks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ReviewTemplates`
--

DROP TABLE IF EXISTS `ReviewTemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ReviewTemplates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `items` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ReviewTemplates`
--

LOCK TABLES `ReviewTemplates` WRITE;
/*!40000 ALTER TABLE `ReviewTemplates` DISABLE KEYS */;
INSERT INTO `ReviewTemplates` VALUES (1,'Probation Period Performance Review','Probation Period Performance Review','[{\"name\":\"q1\",\"field_label\":\"How effective were you?\",\"field_type\":\"text\",\"field_validation\":\"\",\"field_options\":\"\",\"field_help\":\"\",\"data\":\"[\\\"q1\\\",{\\\"label\\\":\\\"How effective were you?\\\",\\\"type\\\":\\\"text\\\",\\\"validation\\\":\\\"\\\"}]\",\"id\":\"items_1\"},{\"id\":\"items_2\",\"name\":\"q3\",\"field_label\":\"What do you think about the company?\",\"field_type\":\"select2\",\"field_validation\":\"\",\"field_options\":\"Good\\nAverage\\nBad\",\"data\":\"[\\\"q3\\\",{\\\"label\\\":\\\"What do you think about the company?\\\",\\\"type\\\":\\\"select2\\\",\\\"validation\\\":\\\"\\\",\\\"source\\\":[[\\\"Good\\\",\\\"Good\\\"],[\\\"Average\\\",\\\"Average\\\"],[\\\"Bad\\\",\\\"Bad\\\"]]}]\"}]','2020-11-15 00:20:20','2020-11-15 00:20:20'),(2,'Peer feedback','Peer feedback','[{\"name\":\"q003\",\"field_label\":\"What do you think about your peer\",\"field_type\":\"text\",\"field_validation\":\"\",\"field_options\":\"\",\"field_help\":\"\",\"data\":\"[\\\"q003\\\",{\\\"label\\\":\\\"What do you think about your peer\\\",\\\"type\\\":\\\"text\\\",\\\"validation\\\":\\\"\\\"}]\",\"id\":\"items_1\"}]','2020-11-15 00:52:51','2020-11-15 00:52:51');
/*!40000 ALTER TABLE `ReviewTemplates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SalaryComponent`
--

DROP TABLE IF EXISTS `SalaryComponent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SalaryComponent` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `componentType` bigint(20) DEFAULT NULL,
  `details` text,
  PRIMARY KEY (`id`),
  KEY `Fk_SalaryComponent_SalaryComponentType` (`componentType`),
  CONSTRAINT `Fk_SalaryComponent_SalaryComponentType` FOREIGN KEY (`componentType`) REFERENCES `SalaryComponentType` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SalaryComponent`
--

LOCK TABLES `SalaryComponent` WRITE;
/*!40000 ALTER TABLE `SalaryComponent` DISABLE KEYS */;
INSERT INTO `SalaryComponent` VALUES (1,'Basic Salary',1,NULL),(2,'Fixed Allowance',1,NULL),(3,'Car Allowance',2,NULL),(4,'Telephone Allowance',2,NULL),(5,'Regular Hourly Pay',3,NULL),(6,'Overtime Hourly Pay',3,NULL),(7,'Double Time Hourly Pay',3,NULL);
/*!40000 ALTER TABLE `SalaryComponent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SalaryComponentType`
--

DROP TABLE IF EXISTS `SalaryComponentType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SalaryComponentType` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SalaryComponentType`
--

LOCK TABLES `SalaryComponentType` WRITE;
/*!40000 ALTER TABLE `SalaryComponentType` DISABLE KEYS */;
INSERT INTO `SalaryComponentType` VALUES (1,'B001','Basic'),(2,'B002','Allowance'),(3,'B003','Hourly');
/*!40000 ALTER TABLE `SalaryComponentType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Settings`
--

DROP TABLE IF EXISTS `Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `value` text,
  `description` text,
  `meta` text,
  `category` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Settings`
--

LOCK TABLES `Settings` WRITE;
/*!40000 ALTER TABLE `Settings` DISABLE KEYS */;
INSERT INTO `Settings` VALUES (1,'Company: Logo','','','[ \"value\", {\"label\":\"Logo\",\"type\":\"fileupload\",\"validation\":\"none\"}]','Company'),(2,'Company: Name','Sample Company Pvt Ltd','Update your company name - For updating company logo copy a file named logo.png to /app/data/ folder','','Company'),(3,'Company: Description','This is a company using icehrm.com','','','Company'),(4,'Email: Enable','1','0 will disable all outgoing emails from modules. Value 1 will enable outgoing emails','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Email'),(5,'Email: Mode','SMTP','Update email sender','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"SMTP\",\"SMTP\"],[\"Swift SMTP\",\"Swift SMTP\"],[\"PHP Mailer\",\"PHP Mailer\"],[\"SES\",\"Amazon SES\"]]}]','Email'),(6,'Email: SMTP Host','localhost','SMTP host IP','','Email'),(7,'Email: SMTP Authentication Required','0','Is authentication required by this SMTP server','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Email'),(8,'Email: SMTP User','none','SMTP user','','Email'),(9,'Email: SMTP Password','none','SMTP password','','Email'),(10,'Email: SMTP Port','none','25','','Email'),(11,'Email: Amazon Access Key ID','','If email mode is Amazon SNS please provide SNS Key','','Email'),(12,'Email: Amazon Secret Access Key','','If email mode is Amazon SNS please provide SNS Secret','','Email'),(13,'Email: Email From','icehrm@mydomain.com','','','Email'),(14,'System: Do not pass JSON in request','0','Select Yes if you are having trouble loading data for some tables','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','System'),(15,'System: Reset Modules and Permissions','0','Select this to reset module and permission information in Database (If you have done any changes to meta files)','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','System'),(16,'System: Reset Module Names','0','Select this to reset module names in Database','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','System'),(17,'System: Add New Permissions','0','Select this to add new permission changes done to meta.json file of any module','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','System'),(18,'System: Debug Mode','0','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','System'),(19,'Projects: Make All Projects Available to Employees','1','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Projects'),(20,'Leave: Share Calendar to Whole Company','1','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Leave'),(21,'Leave: CC Emails','','Every email sent though leave module will be CC to these comma seperated list of emails addresses','','Leave'),(22,'Leave: BCC Emails','','Every email sent though leave module will be BCC to these comma seperated list of emails addresses','','Leave'),(23,'Attendance: Time-sheet Cross Check','0','Only allow users to add an entry to a timesheet only if they have marked atteandance for the selected period','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Attendance'),(24,'Api: REST Api Enabled','1','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"0\",\"No\"],[\"1\",\"Yes\"]]}]','Api'),(25,'Api: REST Api Token','Click on edit icon','','[\"value\", {\"label\":\"Value\",\"type\":\"placeholder\"}]','Api'),(26,'LDAP: Enabled','0','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"0\",\"No\"],[\"1\",\"Yes\"]]}]','LDAP'),(27,'LDAP: Server','','LDAP Server IP or DNS','','LDAP'),(28,'LDAP: Port','389','LDAP Server Port','','LDAP'),(29,'LDAP: Root DN','','e.g: dc=mycompany,dc=net','','LDAP'),(30,'LDAP: Manager DN','','e.g: cn=admin,dc=mycompany,dc=net','','LDAP'),(31,'LDAP: Manager Password','','Password of the manager user','','LDAP'),(32,'LDAP: Version 3','1','Are you using LDAP v3','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','LDAP'),(33,'LDAP: User Filter','','e.g: uid={}, we will replace {} with actual username provided by the user at the time of login','','LDAP'),(34,'Recruitment: Show Quick Apply','1','Show quick apply button when candidates are applying for jobs. Quick apply allow candidates to apply with minimum amount of information','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Recruitment'),(35,'Recruitment: Show Apply','1','Show apply button when candidates are applying for jobs','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Recruitment'),(36,'Notifications: Send Document Expiry Emails','1','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Notifications'),(37,'Notifications: Copy Document Expiry Emails to Manager','1','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Notifications'),(38,'Expense: Pre-Approve Expenses','0','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Expense'),(39,'Travel: Pre-Approve Travel Request','0','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Travel'),(40,'Attendance: Use Department Time Zone','0','','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Attendance'),(41,'Travel: Allow Indirect Admins to Approve','0','Allow indirect admins to approve travel requests','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Travel'),(42,'Attendance: Overtime Calculation Class','BasicOvertimeCalculator','Set the method used to calculate overtime','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"BasicOvertimeCalculator\",\"BasicOvertimeCalculator\"],[\"CaliforniaOvertimeCalculator\",\"CaliforniaOvertimeCalculator\"]]}]','Attendance'),(43,'Attendance: Overtime Calculation Period','Daily','Set the period for overtime calculation. (Affects attendance sheets)','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"Daily\",\"Daily\"],[\"Weekly\",\"Weekly\"]]}]','Attendance'),(44,'Attendance: Overtime Start Hour','8','Overtime calculation will start after an employee work this number of hours per day, 0 to indicate no overtime','','Attendance'),(45,'Attendance: Double time Start Hour','12','Double time calculation will start after an employee work this number of hours per day, 0 to indicate no double time','','Attendance'),(46,'Attendance: Work Week Start Day','0','Set the starting day of the work week','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"0\",\"Sunday\"],[\"1\",\"Monday\"],[\"2\",\"Tuesday\"],[\"3\",\"Wednesday\"],[\"4\",\"Thursday\"],[\"5\",\"Friday\"],[\"6\",\"Saturday\"]]}]','Attendance'),(47,'System: Allowed Countries','','Only these countries will be allowed in select boxes','[\"value\", {\"label\":\"Value\",\"type\":\"select2multi\",\"remote-source\":[\"Country\",\"id\",\"name\"]}]','System'),(48,'System: Allowed Currencies','','Only these currencies will be allowed in select boxes','[\"value\", {\"label\":\"Value\",\"type\":\"select2multi\",\"remote-source\":[\"CurrencyType\",\"id\",\"code+name\"]}]','System'),(49,'System: Allowed Nationality','','Only these nationalities will be allowed in select boxes','[\"value\", {\"label\":\"Value\",\"type\":\"select2multi\",\"remote-source\":[\"Nationality\",\"id\",\"name\"]}]','System'),(50,'System: Language','en','Current Language','[\"value\", {\"label\":\"Value\",\"type\":\"select2\",\"allow-null\":false,\"remote-source\":[\"SupportedLanguage\",\"name\",\"description\"]}]','System'),(51,'Instance : ID','0847429146712c108e23c435e8f93b4d','','','Instance'),(52,'Instance: Key','UQHEYBx9H1eNR66nhNCNCz1WCDDhkjtx1OuJbO3ZQMt+8tfSGvuOH/YEHntRajY=','','','Instance'),(53,'System: Time-sheet Entry Start and End time Required','1','Select 0 if you only need to store the time spend in time sheets','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','System'),(54,'Attendance: Photo Attendance','0','Require submitting a photo using web cam when marking attendance','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Attendance'),(55,'System: G Suite Enabled','0','If you want to allow users to login via G Suite accounts','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','System'),(56,'System: G Suite Disable Password Login','0','If you want to allow users to login only via G Suite accounts','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','System'),(58,'Attendance: Request Attendance Location on Mobile','1','Push attendance location when marking attendance via mobile app','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"1\",\"Yes\"],[\"0\",\"No\"]]}]','Attendance'),(60,'System: Google Maps Api Key','','Google Map Api Key','','System'),(61,'Leave: Use Confirmation Date Instead of Joined Date','0','Use confirmation date instead of joined date for leave calculations','[\"value\", {\"label\":\"Value\",\"type\":\"select\",\"source\":[[\"0\",\"No\"], [\"1\",\"Yes\"]]}]','Leave'),(62,'Leave: Limit Allowed Partial Leave Days','','Allow only a selected set of partial leave days (if empty we allow all partial leave day types)','[\"value\", {\"label\":\"Value\",\"type\":\"select2multi\",\"source\": [[\"Full Day\",\"Full Day\"], [\"Half Day - Morning\",\"Half Day - Morning\"], [\"Half Day - Afternoon\",\"Half Day - Afternoon\"]]}]','Leave');
/*!40000 ALTER TABLE `Settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Skills`
--

DROP TABLE IF EXISTS `Skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Skills` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Skills`
--

LOCK TABLES `Skills` WRITE;
/*!40000 ALTER TABLE `Skills` DISABLE KEYS */;
INSERT INTO `Skills` VALUES (1,'Programming and Application Development','Programming and Application Development'),(2,'Project Management','Project Management'),(3,'Help Desk/Technical Support','Help Desk/Technical Support'),(4,'Networking','Networking'),(5,'Databases','Databases'),(6,'Business Intelligence','Business Intelligence'),(7,'Cloud Computing','Cloud Computing'),(8,'Information Security','Information Security'),(9,'HTML Skills','HTML Skills'),(10,'Graphic Designing','Graphic Designing');
/*!40000 ALTER TABLE `Skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `StatusChangeLogs`
--

DROP TABLE IF EXISTS `StatusChangeLogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `StatusChangeLogs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `element` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `data` varchar(500) NOT NULL,
  `status_from` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') DEFAULT 'Pending',
  `status_to` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') DEFAULT 'Pending',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `EmployeeApprovals_type_element` (`type`,`element`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `StatusChangeLogs`
--

LOCK TABLES `StatusChangeLogs` WRITE;
/*!40000 ALTER TABLE `StatusChangeLogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `StatusChangeLogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SupportedLanguages`
--

DROP TABLE IF EXISTS `SupportedLanguages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SupportedLanguages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SupportedLanguages`
--

LOCK TABLES `SupportedLanguages` WRITE;
/*!40000 ALTER TABLE `SupportedLanguages` DISABLE KEYS */;
INSERT INTO `SupportedLanguages` VALUES (1,'en','English'),(2,'de','German'),(3,'fr','French'),(4,'pl','Polish'),(5,'it','Italian'),(7,'zh','Chinese'),(8,'ja','Japanese'),(10,'es','Spanish'),(11,'fi','Finnish'),(12,'sr','Serbian'),(13,'sv','Swedish'),(14,'no','Norwegian'),(15,'pt','Portuguese'),(16,'nl','Dutch'),(17,'ar','Arabic'),(18,'al','Albanian');
/*!40000 ALTER TABLE `SupportedLanguages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Tags`
--

DROP TABLE IF EXISTS `Tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Tags` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Tags`
--

LOCK TABLES `Tags` WRITE;
/*!40000 ALTER TABLE `Tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `Tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Tasks`
--

DROP TABLE IF EXISTS `Tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Tasks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) DEFAULT NULL,
  `name` varchar(250) NOT NULL,
  `description` text,
  `attachment` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Fk_EmployeeTasks_Employees` (`employee`),
  CONSTRAINT `Fk_EmployeeTasks_Employees` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Tasks`
--

LOCK TABLES `Tasks` WRITE;
/*!40000 ALTER TABLE `Tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `Tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Timezones`
--

DROP TABLE IF EXISTS `Timezones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Timezones` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `details` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `TimezoneNameKey` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=538 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Timezones`
--

LOCK TABLES `Timezones` WRITE;
/*!40000 ALTER TABLE `Timezones` DISABLE KEYS */;
INSERT INTO `Timezones` VALUES (2,'US/Samoa','(GMT-11:00) Samoa'),(3,'US/Hawaii','(GMT-10:00) Hawaii'),(4,'US/Alaska','(GMT-09:00) Alaska'),(5,'US/Pacific','(GMT-08:00) Pacific Time (US, Canada)'),(7,'US/Arizona','(GMT-07:00) Arizona'),(8,'US/Mountain','(GMT-07:00) Mountain Time (US, Canada)'),(13,'Canada/Saskatchewan','(GMT-06:00) Saskatchewan'),(14,'US/Central','(GMT-06:00) Central Time (US , Canada)'),(15,'US/Eastern','(GMT-05:00) Eastern Time (US , Canada)'),(16,'US/East-Indiana','(GMT-05:00) Indiana (East)'),(20,'Canada/Atlantic','(GMT-04:00) Atlantic Time (Canada)'),(23,'Canada/Newfoundland','(GMT-03:30) Newfoundland'),(24,'America/Buenos_Aires','(GMT-03:00) Buenos Aires'),(88,'Asia/Chongqing','(GMT+08:00) Chongqing'),(103,'Australia/Canberra','(GMT+10:00) Canberra'),(113,'Africa/Abidjan','Africa/Abidjan'),(114,'Africa/Accra','Africa/Accra'),(115,'Africa/Addis_Ababa','Africa/Addis_Ababa'),(116,'Africa/Algiers','Africa/Algiers'),(117,'Africa/Asmara','Africa/Asmara'),(118,'Africa/Bamako','Africa/Bamako'),(119,'Africa/Bangui','Africa/Bangui'),(120,'Africa/Banjul','Africa/Banjul'),(121,'Africa/Bissau','Africa/Bissau'),(122,'Africa/Blantyre','Africa/Blantyre'),(123,'Africa/Brazzaville','Africa/Brazzaville'),(124,'Africa/Bujumbura','Africa/Bujumbura'),(125,'Africa/Cairo','Africa/Cairo'),(126,'Africa/Casablanca','Africa/Casablanca'),(127,'Africa/Ceuta','Africa/Ceuta'),(128,'Africa/Conakry','Africa/Conakry'),(129,'Africa/Dakar','Africa/Dakar'),(130,'Africa/Dar_es_Salaam','Africa/Dar_es_Salaam'),(131,'Africa/Djibouti','Africa/Djibouti'),(132,'Africa/Douala','Africa/Douala'),(133,'Africa/El_Aaiun','Africa/El_Aaiun'),(134,'Africa/Freetown','Africa/Freetown'),(135,'Africa/Gaborone','Africa/Gaborone'),(136,'Africa/Harare','Africa/Harare'),(137,'Africa/Johannesburg','Africa/Johannesburg'),(138,'Africa/Juba','Africa/Juba'),(139,'Africa/Kampala','Africa/Kampala'),(140,'Africa/Khartoum','Africa/Khartoum'),(141,'Africa/Kigali','Africa/Kigali'),(142,'Africa/Kinshasa','Africa/Kinshasa'),(143,'Africa/Lagos','Africa/Lagos'),(144,'Africa/Libreville','Africa/Libreville'),(145,'Africa/Lome','Africa/Lome'),(146,'Africa/Luanda','Africa/Luanda'),(147,'Africa/Lubumbashi','Africa/Lubumbashi'),(148,'Africa/Lusaka','Africa/Lusaka'),(149,'Africa/Malabo','Africa/Malabo'),(150,'Africa/Maputo','Africa/Maputo'),(151,'Africa/Maseru','Africa/Maseru'),(152,'Africa/Mbabane','Africa/Mbabane'),(153,'Africa/Mogadishu','Africa/Mogadishu'),(154,'Africa/Monrovia','Africa/Monrovia'),(155,'Africa/Nairobi','Africa/Nairobi'),(156,'Africa/Ndjamena','Africa/Ndjamena'),(157,'Africa/Niamey','Africa/Niamey'),(158,'Africa/Nouakchott','Africa/Nouakchott'),(159,'Africa/Ouagadougou','Africa/Ouagadougou'),(160,'Africa/Porto-Novo','Africa/Porto-Novo'),(161,'Africa/Sao_Tome','Africa/Sao_Tome'),(162,'Africa/Tripoli','Africa/Tripoli'),(163,'Africa/Tunis','Africa/Tunis'),(164,'Africa/Windhoek','Africa/Windhoek'),(165,'America/Adak','America/Adak'),(166,'America/Anchorage','America/Anchorage'),(167,'America/Anguilla','America/Anguilla'),(168,'America/Antigua','America/Antigua'),(169,'America/Araguaina','America/Araguaina'),(170,'America/Argentina/Buenos_Aires','America/Argentina/Buenos_Aires'),(171,'America/Argentina/Catamarca','America/Argentina/Catamarca'),(172,'America/Argentina/Cordoba','America/Argentina/Cordoba'),(173,'America/Argentina/Jujuy','America/Argentina/Jujuy'),(174,'America/Argentina/La_Rioja','America/Argentina/La_Rioja'),(175,'America/Argentina/Mendoza','America/Argentina/Mendoza'),(176,'America/Argentina/Rio_Gallegos','America/Argentina/Rio_Gallegos'),(177,'America/Argentina/Salta','America/Argentina/Salta'),(178,'America/Argentina/San_Juan','America/Argentina/San_Juan'),(179,'America/Argentina/San_Luis','America/Argentina/San_Luis'),(180,'America/Argentina/Tucuman','America/Argentina/Tucuman'),(181,'America/Argentina/Ushuaia','America/Argentina/Ushuaia'),(182,'America/Aruba','America/Aruba'),(183,'America/Asuncion','America/Asuncion'),(184,'America/Atikokan','America/Atikokan'),(185,'America/Bahia','America/Bahia'),(186,'America/Bahia_Banderas','America/Bahia_Banderas'),(187,'America/Barbados','America/Barbados'),(188,'America/Belem','America/Belem'),(189,'America/Belize','America/Belize'),(190,'America/Blanc-Sablon','America/Blanc-Sablon'),(191,'America/Boa_Vista','America/Boa_Vista'),(192,'America/Bogota','America/Bogota'),(193,'America/Boise','America/Boise'),(194,'America/Cambridge_Bay','America/Cambridge_Bay'),(195,'America/Campo_Grande','America/Campo_Grande'),(196,'America/Cancun','America/Cancun'),(197,'America/Caracas','America/Caracas'),(198,'America/Cayenne','America/Cayenne'),(199,'America/Cayman','America/Cayman'),(200,'America/Chicago','America/Chicago'),(201,'America/Chihuahua','America/Chihuahua'),(202,'America/Costa_Rica','America/Costa_Rica'),(203,'America/Creston','America/Creston'),(204,'America/Cuiaba','America/Cuiaba'),(205,'America/Curacao','America/Curacao'),(206,'America/Danmarkshavn','America/Danmarkshavn'),(207,'America/Dawson','America/Dawson'),(208,'America/Dawson_Creek','America/Dawson_Creek'),(209,'America/Denver','America/Denver'),(210,'America/Detroit','America/Detroit'),(211,'America/Dominica','America/Dominica'),(212,'America/Edmonton','America/Edmonton'),(213,'America/Eirunepe','America/Eirunepe'),(214,'America/El_Salvador','America/El_Salvador'),(215,'America/Fort_Nelson','America/Fort_Nelson'),(216,'America/Fortaleza','America/Fortaleza'),(217,'America/Glace_Bay','America/Glace_Bay'),(218,'America/Godthab','America/Godthab'),(219,'America/Goose_Bay','America/Goose_Bay'),(220,'America/Grand_Turk','America/Grand_Turk'),(221,'America/Grenada','America/Grenada'),(222,'America/Guadeloupe','America/Guadeloupe'),(223,'America/Guatemala','America/Guatemala'),(224,'America/Guayaquil','America/Guayaquil'),(225,'America/Guyana','America/Guyana'),(226,'America/Halifax','America/Halifax'),(227,'America/Havana','America/Havana'),(228,'America/Hermosillo','America/Hermosillo'),(229,'America/Indiana/Indianapolis','America/Indiana/Indianapolis'),(230,'America/Indiana/Knox','America/Indiana/Knox'),(231,'America/Indiana/Marengo','America/Indiana/Marengo'),(232,'America/Indiana/Petersburg','America/Indiana/Petersburg'),(233,'America/Indiana/Tell_City','America/Indiana/Tell_City'),(234,'America/Indiana/Vevay','America/Indiana/Vevay'),(235,'America/Indiana/Vincennes','America/Indiana/Vincennes'),(236,'America/Indiana/Winamac','America/Indiana/Winamac'),(237,'America/Inuvik','America/Inuvik'),(238,'America/Iqaluit','America/Iqaluit'),(239,'America/Jamaica','America/Jamaica'),(240,'America/Juneau','America/Juneau'),(241,'America/Kentucky/Louisville','America/Kentucky/Louisville'),(242,'America/Kentucky/Monticello','America/Kentucky/Monticello'),(243,'America/Kralendijk','America/Kralendijk'),(244,'America/La_Paz','America/La_Paz'),(245,'America/Lima','America/Lima'),(246,'America/Los_Angeles','America/Los_Angeles'),(247,'America/Lower_Princes','America/Lower_Princes'),(248,'America/Maceio','America/Maceio'),(249,'America/Managua','America/Managua'),(250,'America/Manaus','America/Manaus'),(251,'America/Marigot','America/Marigot'),(252,'America/Martinique','America/Martinique'),(253,'America/Matamoros','America/Matamoros'),(254,'America/Mazatlan','America/Mazatlan'),(255,'America/Menominee','America/Menominee'),(256,'America/Merida','America/Merida'),(257,'America/Metlakatla','America/Metlakatla'),(258,'America/Mexico_City','America/Mexico_City'),(259,'America/Miquelon','America/Miquelon'),(260,'America/Moncton','America/Moncton'),(261,'America/Monterrey','America/Monterrey'),(262,'America/Montevideo','America/Montevideo'),(263,'America/Montserrat','America/Montserrat'),(264,'America/Nassau','America/Nassau'),(265,'America/New_York','America/New_York'),(266,'America/Nipigon','America/Nipigon'),(267,'America/Nome','America/Nome'),(268,'America/Noronha','America/Noronha'),(269,'America/North_Dakota/Beulah','America/North_Dakota/Beulah'),(270,'America/North_Dakota/Center','America/North_Dakota/Center'),(271,'America/North_Dakota/New_Salem','America/North_Dakota/New_Salem'),(272,'America/Ojinaga','America/Ojinaga'),(273,'America/Panama','America/Panama'),(274,'America/Pangnirtung','America/Pangnirtung'),(275,'America/Paramaribo','America/Paramaribo'),(276,'America/Phoenix','America/Phoenix'),(277,'America/Port-au-Prince','America/Port-au-Prince'),(278,'America/Port_of_Spain','America/Port_of_Spain'),(279,'America/Porto_Velho','America/Porto_Velho'),(280,'America/Puerto_Rico','America/Puerto_Rico'),(281,'America/Punta_Arenas','America/Punta_Arenas'),(282,'America/Rainy_River','America/Rainy_River'),(283,'America/Rankin_Inlet','America/Rankin_Inlet'),(284,'America/Recife','America/Recife'),(285,'America/Regina','America/Regina'),(286,'America/Resolute','America/Resolute'),(287,'America/Rio_Branco','America/Rio_Branco'),(288,'America/Santarem','America/Santarem'),(289,'America/Santiago','America/Santiago'),(290,'America/Santo_Domingo','America/Santo_Domingo'),(291,'America/Sao_Paulo','America/Sao_Paulo'),(292,'America/Scoresbysund','America/Scoresbysund'),(293,'America/Sitka','America/Sitka'),(294,'America/St_Barthelemy','America/St_Barthelemy'),(295,'America/St_Johns','America/St_Johns'),(296,'America/St_Kitts','America/St_Kitts'),(297,'America/St_Lucia','America/St_Lucia'),(298,'America/St_Thomas','America/St_Thomas'),(299,'America/St_Vincent','America/St_Vincent'),(300,'America/Swift_Current','America/Swift_Current'),(301,'America/Tegucigalpa','America/Tegucigalpa'),(302,'America/Thule','America/Thule'),(303,'America/Thunder_Bay','America/Thunder_Bay'),(304,'America/Tijuana','America/Tijuana'),(305,'America/Toronto','America/Toronto'),(306,'America/Tortola','America/Tortola'),(307,'America/Vancouver','America/Vancouver'),(308,'America/Whitehorse','America/Whitehorse'),(309,'America/Winnipeg','America/Winnipeg'),(310,'America/Yakutat','America/Yakutat'),(311,'America/Yellowknife','America/Yellowknife'),(312,'Antarctica/Casey','Antarctica/Casey'),(313,'Antarctica/Davis','Antarctica/Davis'),(314,'Antarctica/DumontDUrville','Antarctica/DumontDUrville'),(315,'Antarctica/Macquarie','Antarctica/Macquarie'),(316,'Antarctica/Mawson','Antarctica/Mawson'),(317,'Antarctica/McMurdo','Antarctica/McMurdo'),(318,'Antarctica/Palmer','Antarctica/Palmer'),(319,'Antarctica/Rothera','Antarctica/Rothera'),(320,'Antarctica/Syowa','Antarctica/Syowa'),(321,'Antarctica/Troll','Antarctica/Troll'),(322,'Antarctica/Vostok','Antarctica/Vostok'),(323,'Arctic/Longyearbyen','Arctic/Longyearbyen'),(324,'Asia/Aden','Asia/Aden'),(325,'Asia/Almaty','Asia/Almaty'),(326,'Asia/Amman','Asia/Amman'),(327,'Asia/Anadyr','Asia/Anadyr'),(328,'Asia/Aqtau','Asia/Aqtau'),(329,'Asia/Aqtobe','Asia/Aqtobe'),(330,'Asia/Ashgabat','Asia/Ashgabat'),(331,'Asia/Atyrau','Asia/Atyrau'),(332,'Asia/Baghdad','Asia/Baghdad'),(333,'Asia/Bahrain','Asia/Bahrain'),(334,'Asia/Baku','Asia/Baku'),(335,'Asia/Bangkok','Asia/Bangkok'),(336,'Asia/Barnaul','Asia/Barnaul'),(337,'Asia/Beirut','Asia/Beirut'),(338,'Asia/Bishkek','Asia/Bishkek'),(339,'Asia/Brunei','Asia/Brunei'),(340,'Asia/Chita','Asia/Chita'),(341,'Asia/Choibalsan','Asia/Choibalsan'),(342,'Asia/Colombo','Asia/Colombo'),(343,'Asia/Damascus','Asia/Damascus'),(344,'Asia/Dhaka','Asia/Dhaka'),(345,'Asia/Dili','Asia/Dili'),(346,'Asia/Dubai','Asia/Dubai'),(347,'Asia/Dushanbe','Asia/Dushanbe'),(348,'Asia/Famagusta','Asia/Famagusta'),(349,'Asia/Gaza','Asia/Gaza'),(350,'Asia/Hebron','Asia/Hebron'),(351,'Asia/Ho_Chi_Minh','Asia/Ho_Chi_Minh'),(352,'Asia/Hong_Kong','Asia/Hong_Kong'),(353,'Asia/Hovd','Asia/Hovd'),(354,'Asia/Irkutsk','Asia/Irkutsk'),(355,'Asia/Jakarta','Asia/Jakarta'),(356,'Asia/Jayapura','Asia/Jayapura'),(357,'Asia/Jerusalem','Asia/Jerusalem'),(358,'Asia/Kabul','Asia/Kabul'),(359,'Asia/Kamchatka','Asia/Kamchatka'),(360,'Asia/Karachi','Asia/Karachi'),(361,'Asia/Kathmandu','Asia/Kathmandu'),(362,'Asia/Khandyga','Asia/Khandyga'),(363,'Asia/Kolkata','Asia/Kolkata'),(364,'Asia/Krasnoyarsk','Asia/Krasnoyarsk'),(365,'Asia/Kuala_Lumpur','Asia/Kuala_Lumpur'),(366,'Asia/Kuching','Asia/Kuching'),(367,'Asia/Kuwait','Asia/Kuwait'),(368,'Asia/Macau','Asia/Macau'),(369,'Asia/Magadan','Asia/Magadan'),(370,'Asia/Makassar','Asia/Makassar'),(371,'Asia/Manila','Asia/Manila'),(372,'Asia/Muscat','Asia/Muscat'),(373,'Asia/Nicosia','Asia/Nicosia'),(374,'Asia/Novokuznetsk','Asia/Novokuznetsk'),(375,'Asia/Novosibirsk','Asia/Novosibirsk'),(376,'Asia/Omsk','Asia/Omsk'),(377,'Asia/Oral','Asia/Oral'),(378,'Asia/Phnom_Penh','Asia/Phnom_Penh'),(379,'Asia/Pontianak','Asia/Pontianak'),(380,'Asia/Pyongyang','Asia/Pyongyang'),(381,'Asia/Qatar','Asia/Qatar'),(382,'Asia/Qyzylorda','Asia/Qyzylorda'),(383,'Asia/Riyadh','Asia/Riyadh'),(384,'Asia/Sakhalin','Asia/Sakhalin'),(385,'Asia/Samarkand','Asia/Samarkand'),(386,'Asia/Seoul','Asia/Seoul'),(387,'Asia/Shanghai','Asia/Shanghai'),(388,'Asia/Singapore','Asia/Singapore'),(389,'Asia/Srednekolymsk','Asia/Srednekolymsk'),(390,'Asia/Taipei','Asia/Taipei'),(391,'Asia/Tashkent','Asia/Tashkent'),(392,'Asia/Tbilisi','Asia/Tbilisi'),(393,'Asia/Tehran','Asia/Tehran'),(394,'Asia/Thimphu','Asia/Thimphu'),(395,'Asia/Tokyo','Asia/Tokyo'),(396,'Asia/Tomsk','Asia/Tomsk'),(397,'Asia/Ulaanbaatar','Asia/Ulaanbaatar'),(398,'Asia/Urumqi','Asia/Urumqi'),(399,'Asia/Ust-Nera','Asia/Ust-Nera'),(400,'Asia/Vientiane','Asia/Vientiane'),(401,'Asia/Vladivostok','Asia/Vladivostok'),(402,'Asia/Yakutsk','Asia/Yakutsk'),(403,'Asia/Yangon','Asia/Yangon'),(404,'Asia/Yekaterinburg','Asia/Yekaterinburg'),(405,'Asia/Yerevan','Asia/Yerevan'),(406,'Atlantic/Azores','Atlantic/Azores'),(407,'Atlantic/Bermuda','Atlantic/Bermuda'),(408,'Atlantic/Canary','Atlantic/Canary'),(409,'Atlantic/Cape_Verde','Atlantic/Cape_Verde'),(410,'Atlantic/Faroe','Atlantic/Faroe'),(411,'Atlantic/Madeira','Atlantic/Madeira'),(412,'Atlantic/Reykjavik','Atlantic/Reykjavik'),(413,'Atlantic/South_Georgia','Atlantic/South_Georgia'),(414,'Atlantic/St_Helena','Atlantic/St_Helena'),(415,'Atlantic/Stanley','Atlantic/Stanley'),(416,'Australia/Adelaide','Australia/Adelaide'),(417,'Australia/Brisbane','Australia/Brisbane'),(418,'Australia/Broken_Hill','Australia/Broken_Hill'),(419,'Australia/Currie','Australia/Currie'),(420,'Australia/Darwin','Australia/Darwin'),(421,'Australia/Eucla','Australia/Eucla'),(422,'Australia/Hobart','Australia/Hobart'),(423,'Australia/Lindeman','Australia/Lindeman'),(424,'Australia/Lord_Howe','Australia/Lord_Howe'),(425,'Australia/Melbourne','Australia/Melbourne'),(426,'Australia/Perth','Australia/Perth'),(427,'Australia/Sydney','Australia/Sydney'),(428,'Europe/Amsterdam','Europe/Amsterdam'),(429,'Europe/Andorra','Europe/Andorra'),(430,'Europe/Astrakhan','Europe/Astrakhan'),(431,'Europe/Athens','Europe/Athens'),(432,'Europe/Belgrade','Europe/Belgrade'),(433,'Europe/Berlin','Europe/Berlin'),(434,'Europe/Bratislava','Europe/Bratislava'),(435,'Europe/Brussels','Europe/Brussels'),(436,'Europe/Bucharest','Europe/Bucharest'),(437,'Europe/Budapest','Europe/Budapest'),(438,'Europe/Busingen','Europe/Busingen'),(439,'Europe/Chisinau','Europe/Chisinau'),(440,'Europe/Copenhagen','Europe/Copenhagen'),(441,'Europe/Dublin','Europe/Dublin'),(442,'Europe/Gibraltar','Europe/Gibraltar'),(443,'Europe/Guernsey','Europe/Guernsey'),(444,'Europe/Helsinki','Europe/Helsinki'),(445,'Europe/Isle_of_Man','Europe/Isle_of_Man'),(446,'Europe/Istanbul','Europe/Istanbul'),(447,'Europe/Jersey','Europe/Jersey'),(448,'Europe/Kaliningrad','Europe/Kaliningrad'),(449,'Europe/Kiev','Europe/Kiev'),(450,'Europe/Kirov','Europe/Kirov'),(451,'Europe/Lisbon','Europe/Lisbon'),(452,'Europe/Ljubljana','Europe/Ljubljana'),(453,'Europe/London','Europe/London'),(454,'Europe/Luxembourg','Europe/Luxembourg'),(455,'Europe/Madrid','Europe/Madrid'),(456,'Europe/Malta','Europe/Malta'),(457,'Europe/Mariehamn','Europe/Mariehamn'),(458,'Europe/Minsk','Europe/Minsk'),(459,'Europe/Monaco','Europe/Monaco'),(460,'Europe/Moscow','Europe/Moscow'),(461,'Europe/Oslo','Europe/Oslo'),(462,'Europe/Paris','Europe/Paris'),(463,'Europe/Podgorica','Europe/Podgorica'),(464,'Europe/Prague','Europe/Prague'),(465,'Europe/Riga','Europe/Riga'),(466,'Europe/Rome','Europe/Rome'),(467,'Europe/Samara','Europe/Samara'),(468,'Europe/San_Marino','Europe/San_Marino'),(469,'Europe/Sarajevo','Europe/Sarajevo'),(470,'Europe/Saratov','Europe/Saratov'),(471,'Europe/Simferopol','Europe/Simferopol'),(472,'Europe/Skopje','Europe/Skopje'),(473,'Europe/Sofia','Europe/Sofia'),(474,'Europe/Stockholm','Europe/Stockholm'),(475,'Europe/Tallinn','Europe/Tallinn'),(476,'Europe/Tirane','Europe/Tirane'),(477,'Europe/Ulyanovsk','Europe/Ulyanovsk'),(478,'Europe/Uzhgorod','Europe/Uzhgorod'),(479,'Europe/Vaduz','Europe/Vaduz'),(480,'Europe/Vatican','Europe/Vatican'),(481,'Europe/Vienna','Europe/Vienna'),(482,'Europe/Vilnius','Europe/Vilnius'),(483,'Europe/Volgograd','Europe/Volgograd'),(484,'Europe/Warsaw','Europe/Warsaw'),(485,'Europe/Zagreb','Europe/Zagreb'),(486,'Europe/Zaporozhye','Europe/Zaporozhye'),(487,'Europe/Zurich','Europe/Zurich'),(488,'Indian/Antananarivo','Indian/Antananarivo'),(489,'Indian/Chagos','Indian/Chagos'),(490,'Indian/Christmas','Indian/Christmas'),(491,'Indian/Cocos','Indian/Cocos'),(492,'Indian/Comoro','Indian/Comoro'),(493,'Indian/Kerguelen','Indian/Kerguelen'),(494,'Indian/Mahe','Indian/Mahe'),(495,'Indian/Maldives','Indian/Maldives'),(496,'Indian/Mauritius','Indian/Mauritius'),(497,'Indian/Mayotte','Indian/Mayotte'),(498,'Indian/Reunion','Indian/Reunion'),(499,'Pacific/Apia','Pacific/Apia'),(500,'Pacific/Auckland','Pacific/Auckland'),(501,'Pacific/Bougainville','Pacific/Bougainville'),(502,'Pacific/Chatham','Pacific/Chatham'),(503,'Pacific/Chuuk','Pacific/Chuuk'),(504,'Pacific/Easter','Pacific/Easter'),(505,'Pacific/Efate','Pacific/Efate'),(506,'Pacific/Enderbury','Pacific/Enderbury'),(507,'Pacific/Fakaofo','Pacific/Fakaofo'),(508,'Pacific/Fiji','Pacific/Fiji'),(509,'Pacific/Funafuti','Pacific/Funafuti'),(510,'Pacific/Galapagos','Pacific/Galapagos'),(511,'Pacific/Gambier','Pacific/Gambier'),(512,'Pacific/Guadalcanal','Pacific/Guadalcanal'),(513,'Pacific/Guam','Pacific/Guam'),(514,'Pacific/Honolulu','Pacific/Honolulu'),(515,'Pacific/Kiritimati','Pacific/Kiritimati'),(516,'Pacific/Kosrae','Pacific/Kosrae'),(517,'Pacific/Kwajalein','Pacific/Kwajalein'),(518,'Pacific/Majuro','Pacific/Majuro'),(519,'Pacific/Marquesas','Pacific/Marquesas'),(520,'Pacific/Midway','Pacific/Midway'),(521,'Pacific/Nauru','Pacific/Nauru'),(522,'Pacific/Niue','Pacific/Niue'),(523,'Pacific/Norfolk','Pacific/Norfolk'),(524,'Pacific/Noumea','Pacific/Noumea'),(525,'Pacific/Pago_Pago','Pacific/Pago_Pago'),(526,'Pacific/Palau','Pacific/Palau'),(527,'Pacific/Pitcairn','Pacific/Pitcairn'),(528,'Pacific/Pohnpei','Pacific/Pohnpei'),(529,'Pacific/Port_Moresby','Pacific/Port_Moresby'),(530,'Pacific/Rarotonga','Pacific/Rarotonga'),(531,'Pacific/Saipan','Pacific/Saipan'),(532,'Pacific/Tahiti','Pacific/Tahiti'),(533,'Pacific/Tarawa','Pacific/Tarawa'),(534,'Pacific/Tongatapu','Pacific/Tongatapu'),(535,'Pacific/Wake','Pacific/Wake'),(536,'Pacific/Wallis','Pacific/Wallis'),(537,'UTC','UTC');
/*!40000 ALTER TABLE `Timezones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TrainingSessions`
--

DROP TABLE IF EXISTS `TrainingSessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TrainingSessions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `course` bigint(20) NOT NULL,
  `description` text,
  `scheduled` datetime DEFAULT NULL,
  `dueDate` datetime DEFAULT NULL,
  `deliveryMethod` enum('Classroom','Self Study','Online') DEFAULT 'Classroom',
  `deliveryLocation` varchar(500) DEFAULT NULL,
  `status` enum('Pending','Approved','Completed','Cancelled') DEFAULT 'Pending',
  `attendanceType` enum('Sign Up','Assign') DEFAULT 'Sign Up',
  `attachment` varchar(300) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `requireProof` enum('Yes','No') DEFAULT 'Yes',
  PRIMARY KEY (`id`),
  KEY `Fk_TrainingSessions_Courses` (`course`),
  CONSTRAINT `Fk_TrainingSessions_Courses` FOREIGN KEY (`course`) REFERENCES `Courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TrainingSessions`
--

LOCK TABLES `TrainingSessions` WRITE;
/*!40000 ALTER TABLE `TrainingSessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `TrainingSessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UserReports`
--

DROP TABLE IF EXISTS `UserReports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UserReports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text,
  `parameters` text,
  `query` text,
  `paramOrder` varchar(500) NOT NULL,
  `type` enum('Query','Class') DEFAULT 'Query',
  `report_group` varchar(500) DEFAULT NULL,
  `output` varchar(15) NOT NULL DEFAULT 'CSV',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UserReports_Name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UserReports`
--

LOCK TABLES `UserReports` WRITE;
/*!40000 ALTER TABLE `UserReports` DISABLE KEYS */;
INSERT INTO `UserReports` VALUES (1,'Time Entry Report','View your time entries by date range and project','[\r\n[ \"client\", {\"label\":\"Select Client\",\"type\":\"select\",\"allow-null\":true,\"null-label\":\"Not Selected\",\"remote-source\":[\"Client\",\"id\",\"name\"]}],\r\n[ \"project\", {\"label\":\"Or Project\",\"type\":\"select\",\"allow-null\":true,\"null-label\":\"All Projects\",\"remote-source\":[\"Project\",\"id\",\"name\",\"getAllProjects\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','EmployeeTimesheetReport','[\"client\",\"project\",\"date_start\",\"date_end\",\"status\"]','Class','Time Management','CSV'),(2,'Attendance Report','View your attendance entries by date range','[\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','EmployeeAttendanceReport','[\"date_start\",\"date_end\"]','Class','Time Management','CSV'),(3,'Time Tracking Report','View your working hours and attendance details for each day for a given period ','[\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','EmployeeTimeTrackReport','[\"date_start\",\"date_end\"]','Class','Time Management','CSV'),(4,'Travel Request Report','View travel requests for a specified period','[\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}],\r\n[ \"status\", {\"label\":\"Status\",\"type\":\"select\",\"source\":[[\"NULL\",\"All Statuses\"],[\"Approved\",\"Approved\"],[\"Pending\",\"Pending\"],[\"Rejected\",\"Rejected\"],[\"Cancellation Requested\",\"Cancellation Requested\"],[\"Cancelled\",\"Cancelled\"]]}]\r\n]','TravelRequestReport','[\"date_start\",\"date_end\",\"status\"]','Class','Travel and Expense Management','CSV'),(5,'Time Sheet Report','This report list all employee time sheets by employee and date range','[\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}],\r\n[ \"status\", {\"label\":\"Status\",\"allow-null\":true,\"null-label\":\"All Status\",\"type\":\"select\",\"source\":[[\"Approved\",\"Approved\"],[\"Pending\",\"Pending\"],[\"Rejected\",\"Rejected\"]]}]\r\n]','EmployeeTimeSheetData','[\"date_start\",\"date_end\",\"status\"]','Class','Time Management','CSV'),(6,'Client Project Time Report','View your time entries for projects under a given client','[\r\n[ \"client\", {\"label\":\"Select Client\",\"type\":\"select\",\"allow-null\":false,\"remote-source\":[\"Client\",\"id\",\"name\"]}],\r\n[ \"date_start\", {\"label\":\"Start Date\",\"type\":\"date\"}],\r\n[ \"date_end\", {\"label\":\"End Date\",\"type\":\"date\"}]\r\n]','ClientProjectTimeReport','[\"client\",\"date_start\",\"date_end\",\"status\"]','Class','Time Management','PDF'),(7,'Download Payslips','Download your payslips','[\r\n[ \"payroll\", {\"label\":\"Select Payroll\",\"type\":\"select\",\"allow-null\":false,\"remote-source\":[\"Payroll\",\"id\",\"name\",\"getEmployeePayrolls\"]}]]','PayslipReport','[\"payroll\"]','Class','Finance','PDF');
/*!40000 ALTER TABLE `UserReports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UserRoles`
--

DROP TABLE IF EXISTS `UserRoles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UserRoles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `additional_permissions` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UserRoles`
--

LOCK TABLES `UserRoles` WRITE;
/*!40000 ALTER TABLE `UserRoles` DISABLE KEYS */;
INSERT INTO `UserRoles` VALUES (1,'Report Manager','[{\"id\":\"additional_permissions_1\",\"table\":\"CompanyAsset\",\"permissions\":\"[\\\"get\\\"]\"}]'),(2,'Attendance Manager',NULL);
/*!40000 ALTER TABLE `UserRoles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `employee` bigint(20) DEFAULT NULL,
  `default_module` bigint(20) DEFAULT NULL,
  `user_level` enum('Admin','Employee','Manager','Restricted Admin','Restricted Manager','Restricted Employee') DEFAULT NULL,
  `user_roles` text,
  `last_login` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `login_hash` varchar(64) DEFAULT NULL,
  `lang` bigint(20) DEFAULT NULL,
  `googleUserData` text,
  `wrong_password_count` int(11) DEFAULT '0',
  `last_wrong_attempt_at` datetime DEFAULT NULL,
  `last_password_requested_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `Fk_User_Employee` (`employee`),
  KEY `Fk_User_SupportedLanguages` (`lang`),
  KEY `login_hash_index` (`login_hash`),
  CONSTRAINT `Fk_User_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_User_SupportedLanguages` FOREIGN KEY (`lang`) REFERENCES `SupportedLanguages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` VALUES (1,'admin','icehrm+admin@web-stalk.com','$2y$13$Q5XSQnwt4xJTOqTSORSJ.uvlS3KhhZejN7U/ixWkef3XVQ5iiYD02',1,NULL,'Admin','','2021-02-04 10:47:45',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(2,'manager','icehrm+manager@web-stalk.com','$2y$13$PZlQdf2jEB1TSESWmersru6CEYkMPBpfdUJTp.WiYsKqQ4WTxfMKC',2,NULL,'Manager','','2020-11-15 00:48:08','2013-01-03 02:47:37','2013-01-03 02:47:37',NULL,NULL,NULL,0,NULL,NULL),(3,'user1','icehrm+user1@web-stalk.com','4048bb914a704a0728549a26b92d8550',3,NULL,'Employee','','2021-02-02 04:20:46','2013-01-03 02:48:32','2013-01-03 02:48:32',NULL,NULL,NULL,0,NULL,NULL),(4,'user2','icehrm+user2@web-stalk.com','4048bb914a704a0728549a26b92d8550',4,NULL,'Employee','','2013-01-03 02:58:55','2013-01-03 02:58:55','2013-01-03 02:58:55',NULL,NULL,NULL,0,NULL,NULL),(5,'user3','icehrm+user3@web-stalk.com','4048bb914a704a0728549a26b92d8550',6,NULL,'Employee','[]','2020-11-15 00:54:17','2013-01-03 02:58:55','2013-01-03 02:58:55',NULL,NULL,NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `WorkDays`
--

DROP TABLE IF EXISTS `WorkDays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `WorkDays` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `status` enum('Full Day','Half Day','Non-working Day') DEFAULT 'Full Day',
  `country` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workdays_name_country` (`name`,`country`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `WorkDays`
--

LOCK TABLES `WorkDays` WRITE;
/*!40000 ALTER TABLE `WorkDays` DISABLE KEYS */;
INSERT INTO `WorkDays` VALUES (1,'Monday','Full Day',NULL),(2,'Tuesday','Full Day',NULL),(3,'Wednesday','Full Day',NULL),(4,'Thursday','Full Day',NULL),(5,'Friday','Full Day',NULL),(6,'Saturday','Non-working Day',NULL),(7,'Sunday','Non-working Day',NULL);
/*!40000 ALTER TABLE `WorkDays` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-02-04  5:41:51
