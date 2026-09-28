USE boardnest;
-- MySQL dump 10.13  Distrib 9.4.0, for Win64 (x86_64)
--
-- Host: localhost    Database: boardnest
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `admin`
--

/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
REPLACE INTO `admin` VALUES (1,1);
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;

--
-- Dumping data for table `agent_tasks`
--

/*!40000 ALTER TABLE `agent_tasks` DISABLE KEYS */;
REPLACE INTO `agent_tasks` VALUES (1,1,'verification',1,'completed','2026-09-27 13:20:27','2026-09-27 15:02:25'),(2,2,'verification',1,'in_progress','2026-09-27 13:20:27',NULL),(3,3,'verification',1,'completed','2026-09-27 13:20:27','2026-09-27 13:20:27'),(4,4,'verification',NULL,'pending','2026-09-27 13:20:27',NULL),(5,3,'verification',NULL,'pending','2026-09-27 14:00:00',NULL);
/*!40000 ALTER TABLE `agent_tasks` ENABLE KEYS */;

--
-- Dumping data for table `announcements`
--

/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;

--
-- Dumping data for table `area_reports`
--

/*!40000 ALTER TABLE `area_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `area_reports` ENABLE KEYS */;

--
-- Dumping data for table `complaint_investigations`
--

/*!40000 ALTER TABLE `complaint_investigations` DISABLE KEYS */;
REPLACE INTO `complaint_investigations` VALUES (1,1,2,'gjfghn',450.00),(2,2,2,NULL,0.00),(3,3,2,NULL,0.00);
/*!40000 ALTER TABLE `complaint_investigations` ENABLE KEYS */;

--
-- Dumping data for table `complaints`
--

/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
REPLACE INTO `complaints` VALUES (1,3,4,'fee_discrepancy','The landlord asked for a 6 month deposit when the listing only said 2 months.','upheld'),(2,2,4,'security_issue','The gate lock is broken and anyone can walk in at night.','under_investigation'),(3,1,4,'maintenance_issue','The roof is leaking constantly when it rains.','assigned');
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;

--
-- Dumping data for table `field_agents`
--

/*!40000 ALTER TABLE `field_agents` DISABLE KEYS */;
REPLACE INTO `field_agents` VALUES (1,2,'199814502890','0775471754','Colombo',1,'self_registered');
/*!40000 ALTER TABLE `field_agents` ENABLE KEYS */;

--
-- Dumping data for table `landlords`
--

/*!40000 ALTER TABLE `landlords` DISABLE KEYS */;
REPLACE INTO `landlords` VALUES (1,3,'123456789V','0770000000','123 Landlord St','standard',NULL,0);
/*!40000 ALTER TABLE `landlords` ENABLE KEYS */;

--
-- Dumping data for table `listing_decisions`
--

/*!40000 ALTER TABLE `listing_decisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_decisions` ENABLE KEYS */;

--
-- Dumping data for table `listings`
--

/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
REPLACE INTO `listings` VALUES (1,1,'pending'),(2,2,'pending'),(3,3,'active');
/*!40000 ALTER TABLE `listings` ENABLE KEYS */;

--
-- Dumping data for table `properties`
--

/*!40000 ALTER TABLE `properties` DISABLE KEYS */;
REPLACE INTO `properties` VALUES (1,1,'10 Galle Road','Colombo','Apartment',6.92710000,79.86120000,NULL,NULL),(2,1,'25 Duplication Rd','Colombo','House',6.90000000,79.85000000,NULL,NULL),(3,1,'88 Havelock Rd','Colombo','Hostel',6.89000000,79.86000000,NULL,NULL),(4,1,'42 Marine Drive','Colombo','Apartment',6.88000000,79.85500000,NULL,NULL);
/*!40000 ALTER TABLE `properties` ENABLE KEYS */;

--
-- Dumping data for table `registration_approvals`
--

/*!40000 ALTER TABLE `registration_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `registration_approvals` ENABLE KEYS */;

--
-- Dumping data for table `rooms`
--

/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;

--
-- Dumping data for table `students`
--

/*!40000 ALTER TABLE `students` DISABLE KEYS */;
REPLACE INTO `students` VALUES (1,4,'987654321V','0711111111',NULL,NULL,'tier1',NULL);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
REPLACE INTO `users` VALUES (1,'Admin','admin@boardnest.lk','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','active','2026-09-27 13:01:43'),(2,'Sobashi Hirushani','agent@boardnest.lk','$2y$10$91WKmKEz9D50Aobtaf2azOj7AL0crg6cpsDFFvsnTB3ftEr5V4s2W','field_agent','active','2026-09-27 13:02:23'),(3,'Dummy Landlord','landlord@test.com','hash','landlord','active','2026-09-27 13:20:27'),(4,'Dummy Student','student@test.com','hash','student','active','2026-09-27 13:20:27');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

--
-- Dumping data for table `verification_reports`
--

/*!40000 ALTER TABLE `verification_reports` DISABLE KEYS */;
REPLACE INTO `verification_reports` VALUES (1,3,2,1,0,0,0,4,0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Looks good.','2026-09-27 13:20:27'),(2,1,2,1,1,1,1,5,1,0,1,1,0,'Bus Transport active in area. Tuk-Tuk stand & rideshare active','Supermarket (500m). Laundromat','Well-lit Main Roads',NULL,'/boardnest/public/uploads/task_1_img1_1790521345.jpg','/boardnest/public/uploads/task_1_img2_1790521345.jpg',NULL,'❌ [Bathroom Access type match Discrepancy]: fdgsgdhsfgs\r\n❌ [Price & Deposit match Discrepancy]: gfdghfhgs\r\n⚠️ [Landlord Listing Photo 3 Discrepancy]: Physical photo does not match listing photo 3.\r\n⚠️ [Landlord Listing Photo 4 Discrepancy]: Physical photo does not match listing photo 4.\r\n\r\nGeneral Remarks:\r\nfgyufdhehdgeyg','2026-09-27 15:02:25');
/*!40000 ALTER TABLE `verification_reports` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-27 20:57:25
