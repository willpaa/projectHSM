-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: hosptalms
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` varchar(50) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `visit_type` enum('OPD','IPD') NOT NULL,
  `reason` text DEFAULT NULL,
  `token_no` varchar(30) DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `duration_minutes` smallint(5) unsigned NOT NULL DEFAULT 30,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appointments_appointment_id_unique` (`appointment_id`),
  KEY `appointments_doctor_id_date_status_index` (`doctor_id`,`date`,`status`),
  KEY `appointments_patient_id_date_status_index` (`patient_id`,`date`,`status`),
  CONSTRAINT `appointments_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `appointments_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,'APT-20261001-MBFZ0T',1,1,'2026-10-02','17:00:00','OPD','eye',NULL,'cancelled','i readd','1','2026-10-01 11:01:02','2026-10-01 11:04:51',30),(2,'APT-20261001-PNFWA5',2,2,'2026-10-01','17:27:00','IPD','see now','001','completed',' i am care','1','2026-10-01 11:28:04','2026-10-01 11:29:55',45);
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beds`
--

DROP TABLE IF EXISTS `beds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ward_id` varchar(50) DEFAULT NULL,
  `ward_name` varchar(120) NOT NULL,
  `bed_id` varchar(50) NOT NULL,
  `bed_no` varchar(50) NOT NULL,
  `bed_type` enum('general','semi_private','private','icu','emergency') NOT NULL DEFAULT 'general',
  `charge_per_day` decimal(10,2) NOT NULL DEFAULT 0.00,
  `occupancy_status` enum('available','occupied','maintenance','reserved') NOT NULL DEFAULT 'available',
  `current_patient_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_nurse` varchar(50) DEFAULT NULL,
  `last_cleaned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `beds_bed_id_unique` (`bed_id`),
  KEY `beds_current_patient_id_foreign` (`current_patient_id`),
  KEY `beds_ward_id_index` (`ward_id`),
  CONSTRAINT `beds_current_patient_id_foreign` FOREIGN KEY (`current_patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beds`
--

LOCK TABLES `beds` WRITE;
/*!40000 ALTER TABLE `beds` DISABLE KEYS */;
/*!40000 ALTER TABLE `beds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(120) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `head_nurse_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_department_id_unique` (`department_id`),
  UNIQUE KEY `departments_code_unique` (`code`),
  KEY `departments_head_nurse_id_index` (`head_nurse_id`),
  CONSTRAINT `departments_head_nurse_id_foreign` FOREIGN KEY (`head_nurse_id`) REFERENCES `nurses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'222','Madicine','777','i am  madicen','chiro','074478345','madice123@gmail.com',NULL,'active','2026-09-30 16:42:16','2026-09-30 16:42:16');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctors`
--

DROP TABLE IF EXISTS `doctors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `specialization` varchar(120) DEFAULT NULL,
  `license_no` varchar(80) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `department` varchar(120) DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `availability_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`availability_schedule`)),
  `consultation_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctors_doctor_id_unique` (`doctor_id`),
  UNIQUE KEY `doctors_license_no_unique` (`license_no`),
  UNIQUE KEY `doctors_email_unique` (`email`),
  KEY `doctors_department_id_index` (`department_id`),
  KEY `doctors_user_id_foreign` (`user_id`),
  CONSTRAINT `doctors_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `doctors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctors`
--

LOCK TABLES `doctors` WRITE;
/*!40000 ALTER TABLE `doctors` DISABLE KEYS */;
INSERT INTO `doctors` VALUES (1,'11','Dr.Ahmad Abdu','eye','3333','098878745','ahmad123@gmail.com',NULL,1,'{\"notes\":\"full tiem\"}',455500.00,'active','2026-09-30 16:43:48','2026-09-30 16:43:48',NULL),(2,'99','Urjii Aliyyi','heart','8888','0936358488','urji123@gmail.com',NULL,1,'{\"notes\":\"day to night\"}',500000.00,'active','2026-09-30 18:09:40','2026-09-30 18:09:40',NULL);
/*!40000 ALTER TABLE `doctors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `i_p_d_s`
--

DROP TABLE IF EXISTS `i_p_d_s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `i_p_d_s` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `encounter_id` varchar(50) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `type` enum('OPD','IPD') NOT NULL DEFAULT 'IPD',
  `doctor_id` bigint(20) unsigned NOT NULL,
  `department` varchar(120) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `discharge_date` date DEFAULT NULL,
  `bed_id` varchar(50) DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `prescription_id` varchar(50) DEFAULT NULL,
  `status` enum('admitted','discharged','transferred') NOT NULL DEFAULT 'admitted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_p_d_s_encounter_id_unique` (`encounter_id`),
  KEY `i_p_d_s_patient_id_foreign` (`patient_id`),
  KEY `i_p_d_s_doctor_id_foreign` (`doctor_id`),
  CONSTRAINT `i_p_d_s_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `i_p_d_s_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `i_p_d_s`
--

LOCK TABLES `i_p_d_s` WRITE;
/*!40000 ALTER TABLE `i_p_d_s` DISABLE KEYS */;
INSERT INTO `i_p_d_s` VALUES (1,'IPD-20260930-6YPJ5K',1,'IPD',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admitted','2026-09-30 18:10:11','2026-09-30 18:10:11'),(2,'IPD-20261001-UYGUYD',2,'IPD',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admitted','2026-10-01 12:16:12','2026-10-01 12:16:12');
/*!40000 ALTER TABLE `i_p_d_s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `insurances`
--

DROP TABLE IF EXISTS `insurances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `policy_id` varchar(50) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `provider_name` varchar(120) NOT NULL,
  `policy_no` varchar(80) NOT NULL,
  `coverage_limit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `co_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `claim_id` varchar(50) DEFAULT NULL,
  `claim_status` enum('submitted','under_review','approved','rejected','settled') NOT NULL DEFAULT 'submitted',
  `approved_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `insurances_policy_id_unique` (`policy_id`),
  UNIQUE KEY `insurances_policy_no_unique` (`policy_no`),
  KEY `insurances_patient_id_foreign` (`patient_id`),
  CONSTRAINT `insurances_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insurances`
--

LOCK TABLES `insurances` WRITE;
/*!40000 ALTER TABLE `insurances` DISABLE KEYS */;
/*!40000 ALTER TABLE `insurances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventories`
--

DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `unit` varchar(30) DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `reorder_level` int(10) unsigned NOT NULL DEFAULT 0,
  `supplier_id` varchar(50) DEFAULT NULL,
  `purchase_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `store_location` varchar(120) DEFAULT NULL,
  `stock_movement_log` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`stock_movement_log`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventories_item_id_unique` (`item_id`),
  KEY `inventories_category_index` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventories`
--

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
INSERT INTO `inventories` VALUES (1,'2','panadol','1','gm',99,23,'44',5000.00,6000.00,'2026-10-10','left place','5','2026-09-30 19:07:00','2026-09-30 20:02:35');
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `sku_code` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `inventory_sku_code_unique` (`sku_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `operation_type` varchar(30) NOT NULL,
  `reference_id` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_transactions_user_id_foreign` (`user_id`),
  KEY `inventory_transactions_inventory_id_foreign` (`inventory_id`),
  KEY `inv_tx_created_operation_idx` (`created_at`,`operation_type`),
  CONSTRAINT `inventory_transactions_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`item_id`) ON UPDATE CASCADE,
  CONSTRAINT `inventory_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transactions`
--

LOCK TABLES `inventory_transactions` WRITE;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
INSERT INTO `inventory_transactions` VALUES (1,'2',3,'purchase','333','n',1,'2026-09-30 19:13:04','2026-09-30 19:13:04'),(2,'2',-4,'dispense','333','Pharmacist dispensed medicine to patient',1,'2026-09-30 20:02:35','2026-09-30 20:02:35');
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'default','{\"uuid\":\"e24526ba-29be-4bca-aff3-142f2c358200\",\"displayName\":\"App\\\\Events\\\\PaymentRequested\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:27:\\\"App\\\\Events\\\\PaymentRequested\\\":2:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"patient\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:55:\\\"Payment request created for Abdi Ahmad - Amount: $50.00\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790798248,\"delay\":null}',0,NULL,1790798248,1790798248),(2,'default','{\"uuid\":\"82928479-6375-4e1a-a1c8-b719fc45d33f\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:81:\\\"Abdi Ahmad needs lab payment for blood. Patient share: ETB 50.00. Sent by nejash.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:20:\\\"heroicon-o-banknotes\\\";s:9:\\\"iconColor\\\";s:7:\\\"warning\\\";s:6:\\\"status\\\";s:7:\\\"warning\\\";s:5:\\\"title\\\";s:19:\\\"New payment request\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"56c86a67-8786-4b81-aacb-dab534691fd0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790798248,\"delay\":null}',0,NULL,1790798248,1790798248),(3,'default','{\"uuid\":\"34ad8764-7af8-4d33-be53-c2b4202a8e3d\",\"displayName\":\"App\\\\Events\\\\PaymentVerified\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:26:\\\"App\\\\Events\\\\PaymentVerified\\\":3:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"patient\\\";i:1;s:7:\\\"service\\\";i:2;s:8:\\\"payments\\\";i:3;s:8:\\\"verifier\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:49:\\\"Payment verified by Cashier. Service can proceed.\\\";s:10:\\\"verifiedBy\\\";s:7:\\\"Cashier\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790798306,\"delay\":null}',0,NULL,1790798306,1790798306),(4,'default','{\"uuid\":\"5c1775ec-72b6-459b-afc8-d7b5d4516999\",\"displayName\":\"App\\\\Events\\\\PaymentRequested\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:27:\\\"App\\\\Events\\\\PaymentRequested\\\":2:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"patient\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:55:\\\"Payment request created for Abdi Ahmad - Amount: $50.00\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790802675,\"delay\":null}',0,NULL,1790802675,1790802675),(5,'default','{\"uuid\":\"c7c6687b-ffde-4baa-b90a-b63087c82c87\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:81:\\\"Abdi Ahmad needs lab payment for blood. Patient share: ETB 50.00. Sent by nejash.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:20:\\\"heroicon-o-banknotes\\\";s:9:\\\"iconColor\\\";s:7:\\\"warning\\\";s:6:\\\"status\\\";s:7:\\\"warning\\\";s:5:\\\"title\\\";s:19:\\\"New payment request\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"5c1930d5-c2b0-4407-b5d4-56fc01c2c8dc\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790802676,\"delay\":null}',0,NULL,1790802676,1790802676),(6,'default','{\"uuid\":\"313d093b-b06a-4c55-bf3a-f6ade7871e94\",\"displayName\":\"App\\\\Events\\\\PaymentVerified\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:26:\\\"App\\\\Events\\\\PaymentVerified\\\":3:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"patient\\\";i:1;s:7:\\\"service\\\";i:2;s:8:\\\"payments\\\";i:3;s:8:\\\"verifier\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:49:\\\"Payment verified by Cashier. Service can proceed.\\\";s:10:\\\"verifiedBy\\\";s:7:\\\"Cashier\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790802725,\"delay\":null}',0,NULL,1790802725,1790802725),(7,'default','{\"uuid\":\"3837dfe1-311d-4eb3-89f5-ea5415fe2057\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:29:\\\"Lab completed for Abdi Ahmad.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:21:\\\"heroicon-o-bell-alert\\\";s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:16:\\\"New Doctor Queue\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"207c44f9-858f-4e15-a575-bea920271a1a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790802818,\"delay\":null}',0,NULL,1790802818,1790802818),(8,'default','{\"uuid\":\"cff05542-d264-4085-9991-9bd651d6611b\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:29:\\\"Lab completed for Abdi Ahmad.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:21:\\\"heroicon-o-bell-alert\\\";s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:16:\\\"New Doctor Queue\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"170eccfb-5d49-49b4-99fe-aedc8d03ad37\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790802818,\"delay\":null}',0,NULL,1790802818,1790802818),(9,'default','{\"uuid\":\"01092387-2c1b-4c3b-9d1d-3941d902d2ea\",\"displayName\":\"App\\\\Events\\\\PaymentRequested\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:27:\\\"App\\\\Events\\\\PaymentRequested\\\":2:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"patient\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:55:\\\"Payment request created for Abdi Ahmad - Amount: $50.00\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790804413,\"delay\":null}',0,NULL,1790804413,1790804413),(10,'default','{\"uuid\":\"02e577ea-5952-4da9-a621-7160488ea5d0\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:81:\\\"Abdi Ahmad needs lab payment for blood. Patient share: ETB 50.00. Sent by nejash.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:20:\\\"heroicon-o-banknotes\\\";s:9:\\\"iconColor\\\";s:7:\\\"warning\\\";s:6:\\\"status\\\";s:7:\\\"warning\\\";s:5:\\\"title\\\";s:19:\\\"New payment request\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"f593cd0c-ea32-446d-be73-43a45b10c258\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790804413,\"delay\":null}',0,NULL,1790804413,1790804413),(11,'default','{\"uuid\":\"e50b3b36-a564-4766-ae9b-e17b3dcbf356\",\"displayName\":\"App\\\\Events\\\\PaymentVerified\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:26:\\\"App\\\\Events\\\\PaymentVerified\\\":3:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"patient\\\";i:1;s:7:\\\"service\\\";i:2;s:8:\\\"payments\\\";i:3;s:8:\\\"verifier\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:49:\\\"Payment verified by Cashier. Service can proceed.\\\";s:10:\\\"verifiedBy\\\";s:7:\\\"Cashier\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790804440,\"delay\":null}',0,NULL,1790804440,1790804440),(12,'default','{\"uuid\":\"ccfdc794-da97-4272-bb74-689397591d32\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:44:\\\"Result for Abdi Ahmad is available in OPD #.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:23:\\\"heroicon-o-check-circle\\\";s:9:\\\"iconColor\\\";s:7:\\\"success\\\";s:6:\\\"status\\\";s:7:\\\"success\\\";s:5:\\\"title\\\";s:23:\\\"Lab Result Added to OPD\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"034fd0f2-1e26-4f04-937c-af8654e58016\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790804500,\"delay\":null}',0,NULL,1790804500,1790804500),(13,'default','{\"uuid\":\"a7006d52-41d0-4bcf-aa37-423b0e8758b1\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:44:\\\"Result for Abdi Ahmad is available in OPD #.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:23:\\\"heroicon-o-check-circle\\\";s:9:\\\"iconColor\\\";s:7:\\\"success\\\";s:6:\\\"status\\\";s:7:\\\"success\\\";s:5:\\\"title\\\";s:23:\\\"Lab Result Added to OPD\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"344ce164-99d5-4ac0-9c21-17995504a236\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790804500,\"delay\":null}',0,NULL,1790804500,1790804500),(14,'default','{\"uuid\":\"ca0a86b3-045d-42e6-8f76-997c7deafc9f\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:52:\\\"Result for Abdi Ahmad (LAB-20260930-T4JOD) is ready.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:17:\\\"heroicon-o-beaker\\\";s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:34:\\\"Laboratory result ready for review\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"9b64ed6c-d44d-4da9-8bb4-39778fbdb649\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790804513,\"delay\":null}',0,NULL,1790804513,1790804513),(15,'default','{\"uuid\":\"053e1b20-2b3d-4a0c-a289-4c6547014dd3\",\"displayName\":\"App\\\\Events\\\\PaymentRequested\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:27:\\\"App\\\\Events\\\\PaymentRequested\\\":2:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:4;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"patient\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:59:\\\"Payment request created for faxe Umar abdi - Amount: $50.00\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790805031,\"delay\":null}',0,NULL,1790805031,1790805031),(16,'default','{\"uuid\":\"caca7cb1-4376-4ff2-845a-9e8dc226149c\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:85:\\\"faxe Umar abdi needs lab payment for blood. Patient share: ETB 50.00. Sent by nejash.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:20:\\\"heroicon-o-banknotes\\\";s:9:\\\"iconColor\\\";s:7:\\\"warning\\\";s:6:\\\"status\\\";s:7:\\\"warning\\\";s:5:\\\"title\\\";s:19:\\\"New payment request\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"746b0c3c-24cc-4fa6-96f2-92ef4d04309d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790805031,\"delay\":null}',0,NULL,1790805031,1790805031),(17,'default','{\"uuid\":\"c43b3083-2af8-4db6-b50c-3c6cc385f111\",\"displayName\":\"App\\\\Events\\\\PaymentVerified\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:26:\\\"App\\\\Events\\\\PaymentVerified\\\":3:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:4;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"patient\\\";i:1;s:7:\\\"service\\\";i:2;s:8:\\\"payments\\\";i:3;s:8:\\\"verifier\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:49:\\\"Payment verified by Cashier. Service can proceed.\\\";s:10:\\\"verifiedBy\\\";s:7:\\\"Cashier\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790805082,\"delay\":null}',0,NULL,1790805082,1790805082),(18,'default','{\"uuid\":\"cf000020-4217-4389-bb3d-076d32098062\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:48:\\\"Result for faxe Umar abdi is available in OPD #.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:23:\\\"heroicon-o-check-circle\\\";s:9:\\\"iconColor\\\";s:7:\\\"success\\\";s:6:\\\"status\\\";s:7:\\\"success\\\";s:5:\\\"title\\\";s:23:\\\"Lab Result Added to OPD\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"df0bc961-423d-4a6a-b785-955bd1a74353\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790805176,\"delay\":null}',0,NULL,1790805176,1790805176),(19,'default','{\"uuid\":\"73fde50a-7ee1-4760-8fa9-cca90f537e8d\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:48:\\\"Result for faxe Umar abdi is available in OPD #.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:23:\\\"heroicon-o-check-circle\\\";s:9:\\\"iconColor\\\";s:7:\\\"success\\\";s:6:\\\"status\\\";s:7:\\\"success\\\";s:5:\\\"title\\\";s:23:\\\"Lab Result Added to OPD\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"8b432db9-1163-416d-b759-6ff5dad35178\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790805176,\"delay\":null}',0,NULL,1790805176,1790805176),(20,'default','{\"uuid\":\"fd4301b3-e039-4fc7-87c1-09eeee596c07\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:56:\\\"Result for faxe Umar abdi (LAB-20260930-XFKNJ) is ready.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:17:\\\"heroicon-o-beaker\\\";s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:34:\\\"Laboratory result ready for review\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"fad3ad15-2439-4199-acd5-4d96f69f68d4\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790805190,\"delay\":null}',0,NULL,1790805190,1790805190),(21,'default','{\"uuid\":\"aa98f371-19b9-484f-9adb-e0badcba4670\",\"displayName\":\"App\\\\Events\\\\PaymentRequested\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:27:\\\"App\\\\Events\\\\PaymentRequested\\\":2:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"patient\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:60:\\\"Payment request created for faxe Umar abdi - Amount: $494.00\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790811474,\"delay\":null}',0,NULL,1790811474,1790811474),(22,'default','{\"uuid\":\"02bcaf01-6017-4dc3-b35e-be9fc4158588\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:91:\\\"faxe Umar abdi needs radiology payment for bone. Patient share: ETB 494.00. Sent by nejash.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:20:\\\"heroicon-o-banknotes\\\";s:9:\\\"iconColor\\\";s:7:\\\"warning\\\";s:6:\\\"status\\\";s:7:\\\"warning\\\";s:5:\\\"title\\\";s:19:\\\"New payment request\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"f77d71bd-2a0c-4c69-ac0c-77b06bd06238\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790811474,\"delay\":null}',0,NULL,1790811474,1790811474),(23,'default','{\"uuid\":\"b4b80adc-dd31-4981-a8f5-476c1b2134a2\",\"displayName\":\"App\\\\Events\\\\PaymentVerified\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:26:\\\"App\\\\Events\\\\PaymentVerified\\\":3:{s:14:\\\"serviceRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ServiceRequest\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"patient\\\";i:1;s:7:\\\"service\\\";i:2;s:8:\\\"payments\\\";i:3;s:8:\\\"verifier\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:7:\\\"message\\\";s:49:\\\"Payment verified by Cashier. Service can proceed.\\\";s:10:\\\"verifiedBy\\\";s:7:\\\"Cashier\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1790811516,\"delay\":null}',0,NULL,1790811517,1790811517),(24,'default','{\"uuid\":\"f184fca6-2507-4783-a009-939671488415\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:42:\\\"Triage patient faxe Umar abdi sent to IPD.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:21:\\\"heroicon-o-bell-alert\\\";s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:13:\\\"New IPD Queue\\\";s:4:\\\"view\\\";N;s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"3acbbbcc-be7b-4185-8991-f7da067156e7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1790867772,\"delay\":null}',0,NULL,1790867772,1790867772);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laboratories`
--

DROP TABLE IF EXISTS `laboratories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laboratories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lab_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `patient_id` bigint(20) unsigned DEFAULT NULL,
  `doctor_id` bigint(20) unsigned DEFAULT NULL,
  `laboratorist_id` bigint(20) unsigned DEFAULT NULL,
  `service_request_id` bigint(20) unsigned DEFAULT NULL,
  `encounter_type` varchar(20) DEFAULT NULL,
  `encounter_id` varchar(50) DEFAULT NULL,
  `test_name` varchar(255) DEFAULT NULL,
  `test_type` varchar(120) DEFAULT NULL,
  `sample_type` varchar(80) DEFAULT NULL,
  `test_date` date DEFAULT NULL,
  `result_date` date DEFAULT NULL,
  `result_value` text DEFAULT NULL,
  `result_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result_data`)),
  `result_status` enum('normal','abnormal','critical','pending') DEFAULT NULL,
  `normal_range` varchar(120) DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `report_path` varchar(255) DEFAULT NULL,
  `lab_report_path` varchar(255) DEFAULT NULL,
  `status` enum('ordered','sample_collected','processing','completed','cancelled') NOT NULL DEFAULT 'ordered',
  PRIMARY KEY (`id`),
  UNIQUE KEY `laboratories_lab_id_unique` (`lab_id`),
  KEY `laboratories_patient_id_foreign` (`patient_id`),
  KEY `laboratories_doctor_id_foreign` (`doctor_id`),
  KEY `laboratories_service_request_id_foreign` (`service_request_id`),
  KEY `laboratories_laboratorist_id_foreign` (`laboratorist_id`),
  CONSTRAINT `laboratories_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `laboratories_laboratorist_id_foreign` FOREIGN KEY (`laboratorist_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `laboratories_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `laboratories_service_request_id_foreign` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laboratories`
--

LOCK TABLES `laboratories` WRITE;
/*!40000 ALTER TABLE `laboratories` DISABLE KEYS */;
INSERT INTO `laboratories` VALUES (1,'LAB-20260930-3JBYL','2026-09-30 16:44:54','2026-09-30 17:00:36',1,1,NULL,1,'OPD','OPD-20260930-KSSJ7T','Initial OPD Lab Panel','General','Blood','2026-09-30','2026-09-30','{\"blood\":\"45\",\"sugar\":\"32\"}','{\"blood\":\"45\",\"sugar\":\"32\"}','normal',NULL,50.00,'fished','lab_reports/LAB-20260930-3JBYL_results.json','lab_reports/LAB-20260930-3JBYL_results.json','completed'),(2,'LAB-20260930-96VJI','2026-09-30 18:10:44','2026-09-30 18:13:23',1,2,NULL,2,'IPD','IPD-20260930-6YPJ5K','Initial IPD Lab Panel','General','Blood','2026-09-30','2026-09-30','{\"glucose \":\"78gm\",\"hb\":\"89gm\"}','{\"glucose \":\"78gm\",\"hb\":\"89gm\"}','abnormal',NULL,50.00,'be careful','lab_reports/LAB-20260930-96VJI_results.json','lab_reports/LAB-20260930-96VJI_results.json','completed'),(3,'LAB-20260930-T4JOD','2026-09-30 18:39:29','2026-09-30 18:41:40',1,1,NULL,3,'OPD','IPD-20260930-6YPJ5K','Initial OPD Lab Panel','General','Blood','2026-09-30','2026-09-30','{\"heart rate\":\"34\",\"blood pressure\":\"44\"}','{\"heart rate\":\"34\",\"blood pressure\":\"44\"}','normal',NULL,50.00,'noromal','lab_reports/LAB-20260930-T4JOD_results.json','lab_reports/LAB-20260930-T4JOD_results.json','completed'),(4,'LAB-20260930-XFKNJ','2026-09-30 18:49:59','2026-09-30 18:52:56',2,2,NULL,4,'OPD','OPD-20260930-MUCEBP','Initial OPD Lab Panel','General','Blood','2026-09-30','2026-09-30','{\"heart rate \":\"68\",\"headeach\":\"90\"}','{\"heart rate \":\"68\",\"headeach\":\"90\"}','normal',NULL,50.00,'inn','lab_reports/LAB-20260930-XFKNJ_results.json','lab_reports/LAB-20260930-XFKNJ_results.json','completed');
/*!40000 ALTER TABLE `laboratories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_05_20_000000_create_inventory_transactions_table',2),(5,'2024_05_21_000000_create_inventory_table',2),(6,'2026_03_03_210910_create_patients_table',2),(7,'2026_03_03_211106_create_doctors_table',2),(8,'2026_03_03_211138_create_appointments_table',2),(9,'2026_03_03_211224_create_o_p_d_s_table',2),(10,'2026_03_03_211339_create_i_p_d_s_table',2),(11,'2026_03_03_211401_create_beds_table',2),(12,'2026_03_03_211430_create_pharmacies_table',2),(13,'2026_03_03_211453_create_laboratories_table',2),(14,'2026_03_03_211525_create_staff_table',2),(15,'2026_03_03_211557_create_inventories_table',2),(16,'2026_03_03_211558_add_inventory_id_foreign_key_to_inventory_transactions',2),(17,'2026_03_03_211628_create_reports_table',2),(18,'2026_03_03_211712_create_settings_table',2),(19,'2026_03_03_211740_create_insurances_table',2),(20,'2026_03_03_223258_create_permission_tables',2),(21,'2026_03_04_013646_create_nurses_table',2),(22,'2026_03_04_013836_create_departments_table',2),(23,'2026_03_04_020000_add_department_nurse_foreign_keys',2),(24,'2026_03_04_030000_add_columns_to_laboratories_table',2),(25,'2026_03_04_040000_create_prescriptions_table',2),(26,'2026_03_05_052122_create_notifications_table',2),(27,'2026_03_05_120000_create_pharmacy_movements_table',2),(28,'2026_03_05_123000_create_patient_histories_table',2),(29,'2026_03_05_124000_alter_patients_status_enum_add_today',2),(30,'2026_03_05_130000_create_triages_table',2),(31,'2026_03_05_140000_create_radiologies_table',2),(32,'2026_03_05_141000_add_clinical_columns_to_opd_table',2),(33,'2026_03_05_150000_add_received_at_to_pharmacies_table',2),(34,'2026_03_07_000001_add_avatar_url_to_users_table',2),(35,'2026_04_15_000000_add_user_id_to_patients_table',2),(36,'2026_04_15_000001_add_status_to_users_table',2),(37,'2026_04_18_000000_add_department_foreign_keys_to_doctors_and_staff',2),(38,'2026_04_18_000001_add_user_id_to_doctors_table',2),(39,'2026_04_18_000002_create_password_reset_tokens_table',2),(40,'2026_04_18_130211_add_department_foreign_keys_to_doctors_and_staff',2),(41,'2026_04_18_130211_add_department_id_to_doctors_and_staff_tables',2),(42,'2026_04_23_000002_create_pharmacy_sales_table',2),(43,'2026_04_30_145048_create_service_requests_table',2),(44,'2026_04_30_145816_create_services_table',2),(45,'2026_04_30_145835_create_payments_table',2),(46,'2026_04_30_160000_add_service_request_workflow_fields',2),(47,'2026_04_30_170000_add_laboratory_result_artifacts',2),(48,'2026_04_30_171000_add_service_request_service_foreign_key',2),(49,'2026_10_01_000001_create_pharmacy_batches_and_batch_movements',3),(50,'2026_10_01_000002_expand_appointment_workflow',4),(51,'2026_10_02_004508_add_dashboard_report_performance_indexes',5),(52,'2026_10_08_232231_create_patient_identifier_sequences_table',5),(53,'2026_10_08_232233_seed_patient_identifier_sequences',5),(54,'2026_10_11_000001_add_result_fields_to_radiologies_table',6),(55,'2026_10_12_000001_ensure_patients_table_schema',6),(56,'2026_10_12_000002_repair_patient_identifier_sequence',7),(57,'2026_10_13_000000_add_laboratorist_to_laboratories_table',7);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',2),(1,'App\\Models\\User',5),(2,'App\\Models\\User',1),(3,'App\\Models\\User',10),(4,'App\\Models\\User',6),(5,'App\\Models\\User',7),(6,'App\\Models\\User',8),(7,'App\\Models\\User',9),(9,'App\\Models\\User',3),(13,'App\\Models\\User',4);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('03db3fa7-7be1-4221-bde2-650f37660478','App\\Notifications\\PatientPortalWelcomeNotification','App\\Models\\User',5,'{\"title\":\"Patient portal ready\",\"body\":\"Welcome faxe Umar abdi, your private portal account is now active.\",\"action_url\":\"\\/admin\"}',NULL,'2026-09-30 18:48:43','2026-09-30 18:48:43'),('6032e043-58a6-473d-b49c-a4ba101e1521','App\\Notifications\\PatientPortalWelcomeNotification','App\\Models\\User',2,'{\"title\":\"Patient portal ready\",\"body\":\"Welcome Abdi Ahmad, your private portal account is now active.\",\"action_url\":\"\\/admin\"}',NULL,'2026-09-30 16:38:16','2026-09-30 16:38:16');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nurses`
--

DROP TABLE IF EXISTS `nurses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nurses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nurse_id` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `license_no` varchar(80) DEFAULT NULL,
  `shift` enum('day','night','rotational','custom') NOT NULL DEFAULT 'day',
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `emergency_contact` varchar(120) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nurses_nurse_id_unique` (`nurse_id`),
  UNIQUE KEY `nurses_email_unique` (`email`),
  UNIQUE KEY `nurses_license_no_unique` (`license_no`),
  KEY `nurses_department_id_index` (`department_id`),
  CONSTRAINT `nurses_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nurses`
--

LOCK TABLES `nurses` WRITE;
/*!40000 ALTER TABLE `nurses` DISABLE KEYS */;
/*!40000 ALTER TABLE `nurses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `o_p_d_s`
--

DROP TABLE IF EXISTS `o_p_d_s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `o_p_d_s` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `encounter_id` varchar(50) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `type` enum('OPD','IPD') NOT NULL DEFAULT 'OPD',
  `doctor_id` bigint(20) unsigned NOT NULL,
  `department` varchar(120) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `icd10_code` varchar(20) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `discharge_date` date DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `bed_id` varchar(50) DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `discharge_summary` text DEFAULT NULL,
  `prescription_id` varchar(50) DEFAULT NULL,
  `status` enum('open','closed','transferred') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `o_p_d_s_encounter_id_unique` (`encounter_id`),
  KEY `o_p_d_s_patient_id_foreign` (`patient_id`),
  KEY `o_p_d_s_doctor_id_foreign` (`doctor_id`),
  CONSTRAINT `o_p_d_s_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `o_p_d_s_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `o_p_d_s`
--

LOCK TABLES `o_p_d_s` WRITE;
/*!40000 ALTER TABLE `o_p_d_s` DISABLE KEYS */;
INSERT INTO `o_p_d_s` VALUES (1,'IPD-20260930-6YPJ5K',1,'OPD',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'transferred','2026-09-30 16:44:14','2026-09-30 18:10:12'),(2,'IPD-20261001-UYGUYD',2,'OPD',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'transferred','2026-09-30 18:49:23','2026-10-01 12:16:12');
/*!40000 ALTER TABLE `o_p_d_s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_histories`
--

DROP TABLE IF EXISTS `patient_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint(20) unsigned NOT NULL,
  `encounter_id` varchar(50) DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'pharmacy',
  `activity` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `occurred_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_histories_patient_id_foreign` (`patient_id`),
  KEY `patient_histories_created_by_foreign` (`created_by`),
  KEY `patient_histories_encounter_id_index` (`encounter_id`),
  CONSTRAINT `patient_histories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `patient_histories_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_histories`
--

LOCK TABLES `patient_histories` WRITE;
/*!40000 ALTER TABLE `patient_histories` DISABLE KEYS */;
INSERT INTO `patient_histories` VALUES (1,2,'333','pharmacy','Medication received','Medicine: panadol, Qty: 2','2026-09-30 19:11:23',1,'2026-09-30 19:11:23','2026-09-30 19:11:23'),(2,2,'333','pharmacy','Medication dispensed','Medicine: panadol, Qty: 4, Sale: PS-20260930230235-CUDDK','2026-09-30 20:02:35',1,'2026-09-30 20:02:35','2026-09-30 20:02:35');
/*!40000 ALTER TABLE `patient_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_identifier_sequences`
--

DROP TABLE IF EXISTS `patient_identifier_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_identifier_sequences` (
  `id` tinyint(3) unsigned NOT NULL,
  `last_number` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_identifier_sequences`
--

LOCK TABLES `patient_identifier_sequences` WRITE;
/*!40000 ALTER TABLE `patient_identifier_sequences` DISABLE KEYS */;
INSERT INTO `patient_identifier_sequences` VALUES (1,2);
/*!40000 ALTER TABLE `patient_identifier_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `patient_id` varchar(255) NOT NULL,
  `mrn` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `insurance_id` varchar(255) DEFAULT NULL,
  `registered_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','today','inactive','deceased') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_patient_id_unique` (`patient_id`),
  UNIQUE KEY `patients_mrn_unique` (`mrn`),
  UNIQUE KEY `patients_email_unique` (`email`),
  KEY `patients_user_id_foreign` (`user_id`),
  KEY `patients_created_at_idx` (`created_at`),
  CONSTRAINT `patients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES (1,2,'1','12','Abdi Ahmad','male','2026-09-23','098787877','abdi123@gmail.com','chiro','A+','rrrr','3443','44444','2026-09-25 19:38:10','today','2026-09-30 16:38:16','2026-09-30 16:38:25'),(2,5,'1111','21212','faxe Umar abdi','female','2026-10-22','09878787','faxe123@gmail.com','ffff','A+','ddddd','4444','7878','2026-09-28 21:46:17','today','2026-09-30 18:48:42','2026-09-30 18:49:03');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) NOT NULL,
  `service_request_id` bigint(20) unsigned NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `collected_by` bigint(20) unsigned NOT NULL,
  `payment_mode` enum('cash','card','upi','netbanking','insurance','mixed') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `split_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`split_details`)),
  `remarks` text DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_invoice_number_unique` (`invoice_number`),
  KEY `payments_service_request_id_foreign` (`service_request_id`),
  KEY `payments_patient_id_foreign` (`patient_id`),
  KEY `payments_collected_by_foreign` (`collected_by`),
  KEY `payments_payment_mode_payment_date_index` (`payment_mode`,`payment_date`),
  KEY `payments_invoice_number_index` (`invoice_number`),
  KEY `payments_payment_date_idx` (`payment_date`),
  CONSTRAINT `payments_collected_by_foreign` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_service_request_id_foreign` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'INV-20260930195826-1-671',1,1,1,'cash',50.00,NULL,NULL,NULL,'2026-09-30 16:58:26','2026-09-30 16:58:26','2026-09-30 16:58:26'),(2,'INV-20260930211205-2-183',2,1,1,'cash',50.00,NULL,NULL,NULL,'2026-09-30 18:12:05','2026-09-30 18:12:05','2026-09-30 18:12:05'),(3,'INV-20260930214040-3-295',3,1,1,'cash',50.00,NULL,NULL,NULL,'2026-09-30 18:40:40','2026-09-30 18:40:40','2026-09-30 18:40:40'),(4,'INV-20260930215122-4-193',4,2,1,'cash',50.00,NULL,NULL,NULL,'2026-09-30 18:51:22','2026-09-30 18:51:22','2026-09-30 18:51:22'),(5,'INV-20260930233837-5-657',5,2,1,'cash',494.00,NULL,NULL,NULL,'2026-09-30 20:38:37','2026-09-30 20:38:37','2026-09-30 20:38:37');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'ViewAny:Patients','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(2,'View:Patients','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(3,'Update:Patients','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(4,'ViewAny:Appointments','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(5,'View:Appointments','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(6,'Create:Appointments','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(7,'ViewAny:BillingPayment','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(8,'View:BillingPayment','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(9,'ViewAny:Prescription','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(10,'View:Prescription','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(11,'ViewAny:Laboratory','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(12,'View:Laboratory','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(13,'ViewAny:Radiology','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(14,'View:Radiology','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(15,'ViewAny:Pharmacy','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(16,'View:Pharmacy','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(17,'ViewAny:Insurance','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(18,'View:Insurance','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(19,'ViewAny:OPD','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(20,'View:OPD','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(21,'ViewAny:IPD','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(22,'View:IPD','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(23,'ViewAny:Triage','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(24,'View:Triage','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(25,'Update:Appointments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(26,'Delete:Appointments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(27,'Restore:Appointments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(28,'ForceDelete:Appointments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(29,'ForceDeleteAny:Appointments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(30,'RestoreAny:Appointments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(31,'Replicate:Appointments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(32,'Reorder:Appointments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(33,'ViewAny:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(34,'View:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(35,'Create:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(36,'Update:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(37,'Delete:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(38,'Restore:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(39,'ForceDelete:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(40,'ForceDeleteAny:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(41,'RestoreAny:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(42,'Replicate:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(43,'Reorder:Department','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(44,'ViewAny:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(45,'View:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(46,'Create:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(47,'Update:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(48,'Delete:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(49,'Restore:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(50,'ForceDelete:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(51,'ForceDeleteAny:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(52,'RestoreAny:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(53,'Replicate:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(54,'Reorder:Doctors','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(55,'Create:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(56,'Update:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(57,'Delete:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(58,'Restore:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(59,'ForceDelete:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(60,'ForceDeleteAny:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(61,'RestoreAny:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(62,'Replicate:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(63,'Reorder:IPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(64,'Create:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(65,'Update:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(66,'Delete:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(67,'Restore:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(68,'ForceDelete:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(69,'ForceDeleteAny:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(70,'RestoreAny:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(71,'Replicate:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(72,'Reorder:Insurance','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(73,'ViewAny:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(74,'View:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(75,'Create:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(76,'Update:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(77,'Delete:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(78,'Restore:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(79,'ForceDelete:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(80,'ForceDeleteAny:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(81,'RestoreAny:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(82,'Replicate:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(83,'Reorder:Inventory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(84,'ViewAny:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(85,'View:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(86,'Create:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(87,'Update:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(88,'Delete:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(89,'Restore:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(90,'ForceDelete:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(91,'ForceDeleteAny:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(92,'RestoreAny:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(93,'Replicate:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(94,'Reorder:InventoryTransaction','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(95,'Create:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(96,'Update:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(97,'Delete:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(98,'Restore:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(99,'ForceDelete:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(100,'ForceDeleteAny:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(101,'RestoreAny:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(102,'Replicate:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(103,'Reorder:Laboratory','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(104,'ViewAny:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(105,'View:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(106,'Create:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(107,'Update:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(108,'Delete:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(109,'Restore:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(110,'ForceDelete:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(111,'ForceDeleteAny:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(112,'RestoreAny:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(113,'Replicate:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(114,'Reorder:Nurse','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(115,'Create:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(116,'Update:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(117,'Delete:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(118,'Restore:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(119,'ForceDelete:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(120,'ForceDeleteAny:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(121,'RestoreAny:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(122,'Replicate:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(123,'Reorder:OPD','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(124,'Create:Patients','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(125,'Delete:Patients','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(126,'Restore:Patients','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(127,'ForceDelete:Patients','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(128,'ForceDeleteAny:Patients','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(129,'RestoreAny:Patients','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(130,'Replicate:Patients','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(131,'Reorder:Patients','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(132,'ViewAny:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(133,'View:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(134,'Create:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(135,'Update:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(136,'Delete:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(137,'Restore:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(138,'ForceDelete:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(139,'ForceDeleteAny:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(140,'RestoreAny:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(141,'Replicate:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(142,'Reorder:Payments','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(143,'Create:Pharmacy','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(144,'Update:Pharmacy','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(145,'Delete:Pharmacy','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(146,'Restore:Pharmacy','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(147,'ForceDelete:Pharmacy','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(148,'ForceDeleteAny:Pharmacy','web','2026-09-29 17:55:58','2026-09-29 17:55:58'),(149,'RestoreAny:Pharmacy','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(150,'Replicate:Pharmacy','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(151,'Reorder:Pharmacy','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(152,'ViewAny:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(153,'View:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(154,'Create:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(155,'Update:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(156,'Delete:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(157,'Restore:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(158,'ForceDelete:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(159,'ForceDeleteAny:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(160,'RestoreAny:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(161,'Replicate:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(162,'Reorder:PharmacySale','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(163,'Create:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(164,'Update:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(165,'Delete:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(166,'Restore:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(167,'ForceDelete:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(168,'ForceDeleteAny:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(169,'RestoreAny:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(170,'Replicate:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(171,'Reorder:Prescription','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(172,'Create:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(173,'Update:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(174,'Delete:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(175,'Restore:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(176,'ForceDelete:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(177,'ForceDeleteAny:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(178,'RestoreAny:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(179,'Replicate:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(180,'Reorder:Radiology','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(181,'ViewAny:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(182,'View:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(183,'Create:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(184,'Update:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(185,'Delete:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(186,'Restore:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(187,'ForceDelete:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(188,'ForceDeleteAny:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(189,'RestoreAny:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(190,'Replicate:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(191,'Reorder:Reports','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(192,'ViewAny:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(193,'View:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(194,'Create:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(195,'Update:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(196,'Delete:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(197,'Restore:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(198,'ForceDelete:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(199,'ForceDeleteAny:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(200,'RestoreAny:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(201,'Replicate:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(202,'Reorder:ServiceRequest','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(203,'ViewAny:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(204,'View:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(205,'Create:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(206,'Update:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(207,'Delete:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(208,'Restore:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(209,'ForceDelete:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(210,'ForceDeleteAny:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(211,'RestoreAny:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(212,'Replicate:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(213,'Reorder:Service','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(214,'ViewAny:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(215,'View:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(216,'Create:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(217,'Update:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(218,'Delete:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(219,'Restore:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(220,'ForceDelete:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(221,'ForceDeleteAny:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(222,'RestoreAny:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(223,'Replicate:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(224,'Reorder:Settings','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(225,'ViewAny:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(226,'View:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(227,'Create:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(228,'Update:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(229,'Delete:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(230,'Restore:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(231,'ForceDelete:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(232,'ForceDeleteAny:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(233,'RestoreAny:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(234,'Replicate:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(235,'Reorder:Staff','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(236,'Create:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(237,'Update:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(238,'Delete:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(239,'Restore:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(240,'ForceDelete:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(241,'ForceDeleteAny:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(242,'RestoreAny:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(243,'Replicate:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(244,'Reorder:Triage','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(245,'ViewAny:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(246,'View:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(247,'Create:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(248,'Update:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(249,'Delete:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(250,'Restore:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(251,'ForceDelete:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(252,'ForceDeleteAny:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(253,'RestoreAny:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(254,'Replicate:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(255,'Reorder:User','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(256,'ViewAny:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(257,'View:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(258,'Create:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(259,'Update:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(260,'Delete:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(261,'Restore:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(262,'ForceDelete:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(263,'ForceDeleteAny:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(264,'RestoreAny:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(265,'Replicate:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(266,'Reorder:Role','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(267,'View:Dashboard','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(268,'View:FinanceReport','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(269,'View:PatientPortal','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(270,'View:PharmacyReportDashboard','web','2026-09-29 17:55:59','2026-09-29 17:55:59'),(271,'ViewAny:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(272,'View:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(273,'Create:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(274,'Update:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(275,'Delete:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(276,'Restore:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(277,'ForceDelete:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(278,'ForceDeleteAny:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(279,'RestoreAny:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(280,'Replicate:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(281,'Reorder:Beds','web','2026-09-29 18:40:21','2026-09-29 18:40:21'),(282,'ViewAny:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(283,'View:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(284,'Create:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(285,'Update:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(286,'Delete:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(287,'Restore:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(288,'ForceDelete:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(289,'ForceDeleteAny:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(290,'RestoreAny:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(291,'Replicate:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(292,'Reorder:PatientHistory','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(293,'ViewAny:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(294,'View:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(295,'Create:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(296,'Update:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(297,'Delete:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(298,'Restore:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(299,'ForceDelete:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(300,'ForceDeleteAny:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(301,'RestoreAny:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(302,'Replicate:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(303,'Reorder:PharmacyMovement','web','2026-09-29 18:40:56','2026-09-29 18:40:56'),(304,'ViewAny:PharmacyBatchMovement','web','2026-09-30 20:09:26','2026-09-30 20:09:26'),(305,'View:PharmacyBatchMovement','web','2026-09-30 20:09:26','2026-09-30 20:09:26'),(306,'Update:PharmacyBatchMovement','web','2026-09-30 20:09:26','2026-09-30 20:09:26'),(307,'ViewAny:PharmacyBatch','web','2026-09-30 20:09:26','2026-09-30 20:09:26'),(308,'View:PharmacyBatch','web','2026-09-30 20:09:26','2026-09-30 20:09:26'),(309,'Update:PharmacyBatch','web','2026-09-30 20:09:26','2026-09-30 20:09:26'),(310,'View:HospitalStatsOverview','web','2026-10-01 10:56:56','2026-10-01 10:56:56'),(311,'View:HospitalIncomeExpenseChart','web','2026-10-01 10:56:57','2026-10-01 10:56:57'),(312,'View:HospitalAppointmentsPieChart','web','2026-10-01 10:56:57','2026-10-01 10:56:57'),(313,'View:HospitalReportsTypeChart','web','2026-10-01 10:56:57','2026-10-01 10:56:57'),(314,'Create:BillingPayment','web','2026-10-01 10:56:57','2026-10-01 10:56:57'),(315,'Update:BillingPayment','web','2026-10-01 10:56:57','2026-10-01 10:56:57');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacies`
--

DROP TABLE IF EXISTS `pharmacies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medicine_id` bigint(20) unsigned NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `batch_no` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `stock_qty` int(10) unsigned NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `supplier_id` varchar(50) DEFAULT NULL,
  `reorder_level` int(10) unsigned NOT NULL DEFAULT 0,
  `prescription_sale_id` varchar(50) DEFAULT NULL,
  `issued_to_patient_id` bigint(20) unsigned DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `inventory_item_id` varchar(50) DEFAULT NULL,
  `prescribed_medicine` varchar(255) DEFAULT NULL,
  `dosage_instructions` text DEFAULT NULL,
  `queue_status` varchar(30) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `pharmacies_issued_to_patient_id_foreign` (`issued_to_patient_id`),
  KEY `pharmacies_medicine_id_index` (`medicine_id`),
  KEY `pharmacies_batch_no_index` (`batch_no`),
  KEY `pharmacies_inventory_item_id_index` (`inventory_item_id`),
  KEY `pharmacies_queue_status_index` (`queue_status`),
  CONSTRAINT `pharmacies_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventories` (`item_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pharmacies_issued_to_patient_id_foreign` FOREIGN KEY (`issued_to_patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacies`
--

LOCK TABLES `pharmacies` WRITE;
/*!40000 ALTER TABLE `pharmacies` DISABLE KEYS */;
INSERT INTO `pharmacies` VALUES (1,2,'panadol',NULL,NULL,0,6000.00,'44',23,'333',2,'2026-09-30 19:11:23','2026-09-30 19:08:53','2026-09-30 20:02:35','2',NULL,NULL,'dispensed');
/*!40000 ALTER TABLE `pharmacies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_batch_movements`
--

DROP TABLE IF EXISTS `pharmacy_batch_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacy_batch_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pharmacy_batch_id` bigint(20) unsigned NOT NULL,
  `operation_type` varchar(30) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `reference` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pharmacy_batch_movements_pharmacy_batch_id_foreign` (`pharmacy_batch_id`),
  KEY `pharmacy_batch_movements_user_id_foreign` (`user_id`),
  CONSTRAINT `pharmacy_batch_movements_pharmacy_batch_id_foreign` FOREIGN KEY (`pharmacy_batch_id`) REFERENCES `pharmacy_batches` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `pharmacy_batch_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_batch_movements`
--

LOCK TABLES `pharmacy_batch_movements` WRITE;
/*!40000 ALTER TABLE `pharmacy_batch_movements` DISABLE KEYS */;
INSERT INTO `pharmacy_batch_movements` VALUES (1,1,'opening_stock',103,'Opening stock migration','Migrated from the existing inventory balance.',NULL,'2026-09-30 19:07:00','2026-09-30 19:13:04'),(2,1,'dispense',-4,'333','Pharmacist dispensed medicine to patient',1,'2026-09-30 20:02:35','2026-09-30 20:02:35');
/*!40000 ALTER TABLE `pharmacy_batch_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_batches`
--

DROP TABLE IF EXISTS `pharmacy_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacy_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_item_id` varchar(50) NOT NULL,
  `batch_no` varchar(80) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quantity_received` int(10) unsigned NOT NULL,
  `quantity_remaining` int(10) unsigned NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `supplier_id` varchar(50) DEFAULT NULL,
  `reference` varchar(120) DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pharmacy_batches_item_batch_unique` (`inventory_item_id`,`batch_no`),
  KEY `pharmacy_batches_received_by_foreign` (`received_by`),
  KEY `pharmacy_batches_fefo_idx` (`inventory_item_id`,`expiry_date`,`quantity_remaining`),
  KEY `pharmacy_batches_expiry_date_index` (`expiry_date`),
  KEY `pharmacy_batches_quantity_remaining_index` (`quantity_remaining`),
  CONSTRAINT `pharmacy_batches_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventories` (`item_id`) ON UPDATE CASCADE,
  CONSTRAINT `pharmacy_batches_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_batches`
--

LOCK TABLES `pharmacy_batches` WRITE;
/*!40000 ALTER TABLE `pharmacy_batches` DISABLE KEYS */;
INSERT INTO `pharmacy_batches` VALUES (1,'2','OPENING-da4b9237bacc','2026-10-10',103,99,5000.00,'44','Opening stock migration','2026-09-30 19:07:00',NULL,'2026-09-30 19:07:00','2026-09-30 20:02:35');
/*!40000 ALTER TABLE `pharmacy_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_movements`
--

DROP TABLE IF EXISTS `pharmacy_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacy_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pharmacy_id` bigint(20) unsigned NOT NULL,
  `movement_type` enum('dispense','receive','loss_adjustment','transfer','disposal') NOT NULL,
  `direction` enum('in','out','adjust') NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `patient_id` bigint(20) unsigned DEFAULT NULL,
  `prescription_id` varchar(50) DEFAULT NULL,
  `reference` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pharmacy_movements_pharmacy_id_foreign` (`pharmacy_id`),
  KEY `pharmacy_movements_patient_id_foreign` (`patient_id`),
  KEY `pharmacy_movements_created_by_foreign` (`created_by`),
  KEY `pharmacy_movements_prescription_id_index` (`prescription_id`),
  CONSTRAINT `pharmacy_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pharmacy_movements_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pharmacy_movements_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_movements`
--

LOCK TABLES `pharmacy_movements` WRITE;
/*!40000 ALTER TABLE `pharmacy_movements` DISABLE KEYS */;
INSERT INTO `pharmacy_movements` VALUES (1,1,'receive','in',2,NULL,NULL,'dd','vv',1,'2026-09-30 19:11:23','2026-09-30 19:11:23'),(2,1,'dispense','out',4,2,'333','PS-20260930230235-CUDDK',NULL,1,'2026-09-30 20:02:35','2026-09-30 20:02:35');
/*!40000 ALTER TABLE `pharmacy_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_sales`
--

DROP TABLE IF EXISTS `pharmacy_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacy_sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` varchar(50) NOT NULL,
  `sale_type` varchar(30) NOT NULL DEFAULT 'prescription',
  `pharmacy_id` bigint(20) unsigned DEFAULT NULL,
  `inventory_item_id` varchar(50) NOT NULL,
  `prescription_id` varchar(50) DEFAULT NULL,
  `patient_id` bigint(20) unsigned DEFAULT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `sold_by` bigint(20) unsigned DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pharmacy_sales_sale_id_unique` (`sale_id`),
  KEY `pharmacy_sales_pharmacy_id_foreign` (`pharmacy_id`),
  KEY `pharmacy_sales_patient_id_foreign` (`patient_id`),
  KEY `pharmacy_sales_sold_by_foreign` (`sold_by`),
  KEY `pharmacy_sales_sale_type_index` (`sale_type`),
  KEY `pharmacy_sales_inventory_item_id_index` (`inventory_item_id`),
  KEY `pharmacy_sales_prescription_id_index` (`prescription_id`),
  KEY `pharmacy_sales_sold_type_idx` (`sold_at`,`sale_type`),
  CONSTRAINT `pharmacy_sales_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventories` (`item_id`) ON UPDATE CASCADE,
  CONSTRAINT `pharmacy_sales_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pharmacy_sales_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pharmacy_sales_sold_by_foreign` FOREIGN KEY (`sold_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_sales`
--

LOCK TABLES `pharmacy_sales` WRITE;
/*!40000 ALTER TABLE `pharmacy_sales` DISABLE KEYS */;
INSERT INTO `pharmacy_sales` VALUES (1,'PS-20260930230235-CUDDK','prescription',1,'2','333',2,'panadol',4,6000.00,24000.00,'paid',NULL,1,'2026-09-30 20:02:35','2026-09-30 20:02:35','2026-09-30 20:02:35');
/*!40000 ALTER TABLE `pharmacy_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prescription_id` varchar(50) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `encounter_type` enum('OPD','IPD') DEFAULT NULL,
  `encounter_id` varchar(50) DEFAULT NULL,
  `prescribed_date` date NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `medications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`medications`)),
  `instructions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','dispensed','expired','cancelled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prescriptions_prescription_id_unique` (`prescription_id`),
  KEY `prescriptions_patient_id_foreign` (`patient_id`),
  KEY `prescriptions_doctor_id_foreign` (`doctor_id`),
  KEY `prescriptions_encounter_id_index` (`encounter_id`),
  KEY `prescriptions_created_at_idx` (`created_at`),
  CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
INSERT INTO `prescriptions` VALUES (1,'PRX-20260930-1RNR6',1,1,'OPD','OPD-20260930-KSSJ7T','2026-09-30','fished',NULL,NULL,NULL,'active','2026-09-30 17:00:53','2026-09-30 17:00:53'),(2,'PRX-20260930-ZEO87',1,2,'OPD','IPD-20260930-6YPJ5K','2026-09-30','be careful',NULL,NULL,NULL,'active','2026-09-30 18:13:38','2026-09-30 18:13:38'),(3,'333',2,2,'OPD','33','2026-10-01','i diagnatd','{\"panadol\":\"4\",\"body\":\"3\"}','dirink 1 a day','bee','active','2026-09-30 19:04:23','2026-09-30 19:08:53');
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radiologies`
--

DROP TABLE IF EXISTS `radiologies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radiologies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `radiology_id` varchar(50) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `doctor_id` bigint(20) unsigned DEFAULT NULL,
  `service_request_id` bigint(20) unsigned DEFAULT NULL,
  `encounter_type` varchar(20) DEFAULT NULL,
  `encounter_id` varchar(50) DEFAULT NULL,
  `exam_name` varchar(120) DEFAULT NULL,
  `modality` varchar(60) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `result_summary` text DEFAULT NULL,
  `result_status` enum('normal','abnormal','critical','pending') DEFAULT NULL,
  `status` enum('ordered','in_progress','completed','cancelled') NOT NULL DEFAULT 'ordered',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `findings` longtext DEFAULT NULL,
  `conclusion` longtext DEFAULT NULL,
  `report_image` varchar(255) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `radiologies_radiology_id_unique` (`radiology_id`),
  KEY `radiologies_patient_id_foreign` (`patient_id`),
  KEY `radiologies_doctor_id_foreign` (`doctor_id`),
  KEY `radiologies_service_request_id_foreign` (`service_request_id`),
  CONSTRAINT `radiologies_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `radiologies_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `radiologies_service_request_id_foreign` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radiologies`
--

LOCK TABLES `radiologies` WRITE;
/*!40000 ALTER TABLE `radiologies` DISABLE KEYS */;
INSERT INTO `radiologies` VALUES (1,'RAD-20260930-CJRMY',2,1,5,'OPD','OPD-20260930-MUCEBP','OPD Requested Imaging','X-Ray','2026-09-30',NULL,'pending','ordered','2026-09-30 20:36:10','2026-09-30 20:37:54',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `radiologies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` varchar(50) NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `date_range` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`date_range`)),
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `generated_by` varchar(50) DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `format` enum('pdf','excel','csv') NOT NULL DEFAULT 'pdf',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reports_report_id_unique` (`report_id`),
  KEY `reports_report_type_index` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(1,2),(1,7),(1,9),(1,10),(1,11),(1,12),(2,1),(2,2),(2,7),(2,9),(2,10),(2,11),(2,12),(3,1),(3,2),(3,7),(4,1),(4,2),(4,3),(4,7),(4,8),(4,9),(4,13),(5,1),(5,2),(5,3),(5,7),(5,8),(5,9),(5,13),(6,1),(6,2),(6,3),(6,7),(6,8),(6,9),(6,13),(7,1),(7,2),(7,7),(8,1),(8,2),(8,7),(9,1),(9,2),(9,3),(9,7),(9,8),(9,12),(9,13),(10,1),(10,2),(10,3),(10,7),(10,8),(10,12),(10,13),(11,1),(11,2),(11,3),(11,8),(11,9),(11,10),(11,13),(12,1),(12,2),(12,3),(12,8),(12,9),(12,10),(12,13),(13,1),(13,2),(13,5),(13,7),(13,9),(13,11),(14,1),(14,2),(14,5),(14,7),(14,9),(14,11),(15,1),(15,2),(15,4),(15,12),(16,1),(16,2),(16,4),(16,12),(17,1),(17,2),(18,1),(18,2),(19,1),(19,2),(19,3),(19,9),(20,1),(20,2),(20,3),(20,9),(21,1),(21,2),(21,3),(21,7),(21,13),(22,1),(22,2),(22,3),(22,7),(22,13),(23,1),(23,2),(23,7),(23,8),(23,9),(24,1),(24,2),(24,7),(24,8),(24,9),(25,2),(25,3),(25,7),(25,8),(25,9),(25,13),(26,2),(27,2),(28,2),(29,2),(30,2),(31,2),(32,2),(33,2),(34,2),(35,2),(36,2),(37,2),(38,2),(39,2),(40,2),(41,2),(42,2),(43,2),(44,2),(45,2),(46,2),(47,2),(48,2),(49,2),(50,2),(51,2),(52,2),(53,2),(54,2),(55,2),(55,7),(56,2),(56,7),(57,2),(58,2),(59,2),(60,2),(61,2),(62,2),(63,2),(64,2),(65,2),(66,2),(67,2),(68,2),(69,2),(70,2),(71,2),(72,2),(73,2),(74,2),(75,2),(76,2),(77,2),(78,2),(79,2),(80,2),(81,2),(82,2),(83,2),(84,2),(85,2),(86,2),(87,2),(88,2),(89,2),(90,2),(91,2),(92,2),(93,2),(94,2),(95,2),(95,9),(96,2),(96,10),(97,2),(98,2),(99,2),(100,2),(101,2),(102,2),(103,2),(104,2),(105,2),(106,2),(107,2),(108,2),(109,2),(110,2),(111,2),(112,2),(113,2),(114,2),(115,2),(116,2),(116,9),(117,2),(118,2),(119,2),(120,2),(121,2),(122,2),(123,2),(124,2),(124,7),(125,2),(126,2),(127,2),(128,2),(129,2),(130,2),(131,2),(132,2),(132,6),(133,2),(133,6),(134,2),(134,6),(135,2),(135,6),(136,2),(137,2),(138,2),(139,2),(140,2),(141,2),(142,2),(143,2),(143,12),(144,2),(144,12),(145,2),(145,12),(146,2),(147,2),(148,2),(149,2),(150,2),(151,2),(152,2),(152,4),(153,2),(153,4),(154,2),(155,2),(156,2),(157,2),(158,2),(159,2),(160,2),(161,2),(162,2),(163,2),(163,3),(163,7),(163,8),(163,13),(164,2),(164,3),(164,7),(164,13),(165,2),(166,2),(167,2),(168,2),(169,2),(170,2),(171,2),(172,2),(172,8),(172,9),(173,2),(173,5),(173,11),(174,2),(175,2),(176,2),(177,2),(178,2),(179,2),(180,2),(181,2),(181,3),(181,5),(181,6),(181,8),(182,2),(182,3),(182,5),(182,6),(182,8),(183,2),(183,5),(183,6),(183,8),(184,2),(184,5),(184,6),(185,2),(186,2),(187,2),(188,2),(189,2),(190,2),(191,2),(192,2),(192,5),(192,6),(193,2),(193,5),(193,6),(194,2),(195,2),(195,5),(196,2),(197,2),(198,2),(199,2),(200,2),(201,2),(202,2),(203,2),(203,6),(204,2),(204,6),(205,2),(205,6),(206,2),(206,6),(207,2),(208,2),(209,2),(210,2),(211,2),(212,2),(213,2),(214,2),(214,3),(214,5),(214,6),(214,8),(214,13),(215,2),(215,3),(215,5),(215,6),(215,8),(215,13),(216,2),(217,2),(217,3),(217,5),(217,6),(217,8),(217,13),(218,2),(219,2),(220,2),(221,2),(222,2),(223,2),(224,2),(225,2),(226,2),(227,2),(228,2),(229,2),(230,2),(231,2),(232,2),(233,2),(234,2),(235,2),(236,2),(236,7),(237,2),(237,7),(237,8),(238,2),(239,2),(240,2),(241,2),(242,2),(243,2),(244,2),(245,2),(246,2),(247,2),(248,2),(249,2),(250,2),(251,2),(252,2),(253,2),(254,2),(255,2),(256,2),(257,2),(258,2),(259,2),(260,2),(261,2),(262,2),(263,2),(264,2),(265,2),(266,2),(267,2),(267,3),(267,7),(267,8),(267,9),(267,10),(267,11),(267,12),(268,2),(269,2),(270,2),(271,2),(272,2),(273,2),(274,2),(275,2),(276,2),(277,2),(278,2),(279,2),(280,2),(281,2),(282,2),(283,2),(284,2),(285,2),(286,2),(287,2),(288,2),(289,2),(290,2),(291,2),(292,2),(293,2),(293,4),(294,2),(294,4),(295,2),(296,2),(296,4),(297,2),(298,2),(299,2),(300,2),(301,2),(302,2),(303,2),(304,4),(305,4),(306,4),(307,4),(308,4),(309,4),(310,7),(311,7),(312,7),(313,7),(314,7),(315,7);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'patient','web','2026-09-29 17:33:26','2026-09-29 17:33:26'),(2,'super_admin','web','2026-09-29 18:44:38','2026-09-29 18:44:38'),(3,'doctor','web','2026-09-29 18:58:00','2026-09-29 18:58:00'),(4,'pharmcy','web','2026-09-30 20:09:26','2026-09-30 20:22:17'),(5,'radiology_staff','web','2026-10-01 08:57:44','2026-10-01 08:57:44'),(6,'billing_staff','web','2026-10-01 09:06:47','2026-10-01 09:06:47'),(7,'receptionist','web','2026-10-01 09:32:13','2026-10-01 09:32:13'),(8,'triage_nurse','web','2026-10-01 10:56:55','2026-10-01 10:56:55'),(9,'opd_staff','web','2026-10-01 10:56:55','2026-10-01 10:56:55'),(10,'lab_technician','web','2026-10-01 10:56:55','2026-10-01 10:56:55'),(11,'radiology_technician','web','2026-10-01 10:56:55','2026-10-01 10:56:55'),(12,'pharmacist','web','2026-10-01 10:56:56','2026-10-01 10:56:56'),(13,'ipd_staff','web','2026-10-01 11:36:37','2026-10-01 11:36:37');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_number` varchar(255) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `encounter_type` varchar(20) DEFAULT NULL,
  `encounter_id` varchar(50) DEFAULT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `collected_by` bigint(20) unsigned DEFAULT NULL,
  `payment_status` enum('pending','verified','paid','insurance','waived','cancelled') NOT NULL DEFAULT 'pending',
  `fulfillment_status` enum('requested','in_progress','completed','delivered','cancelled') NOT NULL DEFAULT 'requested',
  `total_amount` decimal(10,2) NOT NULL,
  `patient_share` decimal(10,2) NOT NULL,
  `insurance_share` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_requests_request_number_unique` (`request_number`),
  KEY `service_requests_patient_id_foreign` (`patient_id`),
  KEY `service_requests_requested_by_foreign` (`requested_by`),
  KEY `service_requests_verified_by_foreign` (`verified_by`),
  KEY `service_requests_collected_by_foreign` (`collected_by`),
  KEY `service_requests_payment_status_fulfillment_status_index` (`payment_status`,`fulfillment_status`),
  KEY `service_requests_encounter_type_encounter_id_index` (`encounter_type`,`encounter_id`),
  KEY `service_requests_request_number_index` (`request_number`),
  KEY `service_requests_requested_at_index` (`requested_at`),
  KEY `service_requests_service_id_foreign` (`service_id`),
  KEY `service_requests_status_requested_idx` (`payment_status`,`requested_at`),
  KEY `service_requests_status_paid_idx` (`payment_status`,`paid_at`),
  CONSTRAINT `service_requests_collected_by_foreign` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_requests_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_requests_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_requests_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_requests`
--

LOCK TABLES `service_requests` WRITE;
/*!40000 ALTER TABLE `service_requests` DISABLE KEYS */;
INSERT INTO `service_requests` VALUES (1,'SR-20260930195728-PW3Q',1,'OPD','OPD-20260930-KSSJ7T',1,1,NULL,1,'paid','completed',50.00,50.00,0.00,0.00,50.00,'2026-09-30 20:00:36',NULL,'2026-09-30 16:58:26','Laboratory request. Patient must clear payment before sample processing.','2026-09-30 16:57:28','2026-09-30 17:00:36'),(2,'SR-20260930211115-GZZA',1,'IPD','IPD-20260930-6YPJ5K',1,1,NULL,1,'paid','completed',50.00,50.00,0.00,0.00,50.00,'2026-09-30 21:13:23',NULL,'2026-09-30 18:12:05','Laboratory request. Patient must clear payment before sample processing.','2026-09-30 18:11:15','2026-09-30 18:13:23'),(3,'SR-20260930214013-54V8',1,'OPD','IPD-20260930-6YPJ5K',1,1,NULL,1,'paid','completed',50.00,50.00,0.00,0.00,50.00,'2026-09-30 21:41:40',NULL,'2026-09-30 18:40:40','Laboratory request. Patient must clear payment before sample processing.','2026-09-30 18:40:13','2026-09-30 18:41:40'),(4,'SR-20260930215031-EFII',2,'OPD','OPD-20260930-MUCEBP',1,1,NULL,1,'paid','completed',50.00,50.00,0.00,0.00,50.00,'2026-09-30 21:52:56',NULL,'2026-09-30 18:51:22','Laboratory request. Patient must clear payment before sample processing.','2026-09-30 18:50:31','2026-09-30 18:52:56'),(5,'SR-20260930233753-LQEU',2,'OPD','OPD-20260930-MUCEBP',2,1,NULL,1,'paid','in_progress',494.00,494.00,0.00,0.00,494.00,'2026-09-30 23:38:36',NULL,'2026-09-30 20:38:36','Radiology request. Patient must clear payment before imaging.','2026-09-30 20:37:53','2026-09-30 20:38:36');
/*!40000 ALTER TABLE `service_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `service_type` enum('lab','radiology','pharmacy','consultation','physiotherapy','surgery','bed_charges','emergency') NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `insurance_coverage_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `requires_pre_auth` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `services_code_unique` (`code`),
  KEY `services_department_id_foreign` (`department_id`),
  KEY `services_service_type_is_active_index` (`service_type`,`is_active`),
  CONSTRAINT `services_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'33','blood','lab',1,50.00,0.00,0,1,NULL,'2026-09-30 16:56:42','2026-09-30 16:56:42'),(2,'22','bone','radiology',1,494.00,0.00,0,1,NULL,'2026-09-30 20:37:26','2026-09-30 20:37:26');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('exBzKERjXwQeiUo4ipp8RFmBrShCbuLbgAjM9UtE',NULL,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:154.0) Gecko/20100101 Firefox/154.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiTXJzN21yRFlxZ3U5Q1UyQzMyNjBOTm04dUdjWnBuTVZ0S2cxeU54SSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790867165),('gyTObJ4GpiHC1YtrPwDelN87feaS1F14WjWqf26C',NULL,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:154.0) Gecko/20100101 Firefox/154.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVHo3NDRxNTBQTWRjWUE4NktwRnZvMEZvVmx3QkJDUjhBREpWOFpQaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790876934),('hcgRtNwnnrctNcm5BhEUMj8FxNUhGzAgIGNfw8Hn',NULL,'::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.140.0 Chrome/150.0.7871.250 Electron/43.7.3 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSWxxN2VobU5BeTV4Y0tjWG9KOW03Rlo0cHNWUFROdUdxQ096anNoWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTc6Imh0dHA6Ly9sb2NhbGhvc3QvSG9zcGl0YWxNYW5hZ2VtZW50L0hvc3B0YWxNYW5hZ2VtZW50U3lzdGVtL0hvc3B0YWxNU1VQREFURS1tYWluJTIwJTI4MyUyOS9wdWJsaWMiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1791469653),('lFCzbGfxrtP5bOXE97BUmIVEoOBOJhqvqZAFiGOp',NULL,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:154.0) Gecko/20100101 Firefox/154.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoib0lWNnhaZkppVnZ3VzRmWnZ0OHcwOWNadWFRREk2V0V6MTBKWmNVYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790865666),('LPW7A60oa7XgJe5jQNLnCgYnCFvLFpmUSsJz9Eov',10,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:154.0) Gecko/20100101 Firefox/154.0','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiMmZsQkR2cmp5cHpFMWpRMjF4OXpmUk5BZFk3aVMwVWNtd1gxdlZ1cSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sYWJvcmF0b3JpZXMiO3M6NToicm91dGUiO3M6NDM6ImZpbGFtZW50LmFkbWluLnJlc291cmNlcy5sYWJvcmF0b3JpZXMuaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiN2M1NTI4MmIwYjM1ZDRmZGQ4MjU1NGJkY2QxOGM4Y2U1N2VjNmIwMWVjZTBiNDY4ZWJmZmVhM2ZmMDZhYTM0YSI7czo2OiJ0YWJsZXMiO2E6Mjp7czo0MDoiOWFjZGJhMWNkZmJkYWU3NzkxODg4NDkyMzYzNzQ5NmZfY29sdW1ucyI7YToxODp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjI6Im5vIjtzOjU6ImxhYmVsIjtzOjE6IiMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJhcnJpdmFsX2ZsYWciO3M6NToibGFiZWwiO3M6NzoiQXJyaXZhbCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6ImVuY291bnRlcl9pZCI7czo1OiJsYWJlbCI7czoxMjoiRW5jb3VudGVyIGlkIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxODoicGF0aWVudC5wYXRpZW50X2lkIjtzOjU6ImxhYmVsIjtzOjEwOiJQYXRpZW50IElEIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNzoicGF0aWVudC5mdWxsX25hbWUiO3M6NToibGFiZWwiO3M6MTI6IlBhdGllbnQgTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6InBhdGllbnQucGhvbmUiO3M6NToibGFiZWwiO3M6NToiUGhvbmUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6InR5cGUiO3M6NToibGFiZWwiO3M6NDoiVHlwZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjc7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6ImRvY3Rvci5mdWxsX25hbWUiO3M6NToibGFiZWwiO3M6NjoiRG9jdG9yIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6ODthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiZGVwYXJ0bWVudCI7czo1OiJsYWJlbCI7czoxMDoiRGVwYXJ0bWVudCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjk7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImljZDEwX2NvZGUiO3M6NToibGFiZWwiO3M6NjoiSUNELTEwIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9aToxMDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoiYWRtaXNzaW9uX2RhdGUiO3M6NToibGFiZWwiO3M6MTQ6IkFkbWlzc2lvbiBkYXRlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6ImRpc2NoYXJnZV9kYXRlIjtzOjU6ImxhYmVsIjtzOjE0OiJEaXNjaGFyZ2UgZGF0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjEyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE0OiJmb2xsb3dfdXBfZGF0ZSI7czo1OiJsYWJlbCI7czoxNDoiRm9sbG93IHVwIGRhdGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjEzO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJiZWQuYmVkX2lkIjtzOjU6ImxhYmVsIjtzOjM6IkJlZCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJwcmVzY3JpcHRpb25faWQiO3M6NToibGFiZWwiO3M6MTU6IlByZXNjcmlwdGlvbiBpZCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6InN0YXR1cyI7czo1OiJsYWJlbCI7czo2OiJTdGF0dXMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxNjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czoxMDoiQ3JlYXRlZCBhdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fWk6MTc7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InVwZGF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTA6IlVwZGF0ZWQgYXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO319czo0MDoiZWRhNWU4ZGY1ZGE2ODhjMmJjZTNmZGIwOGU4YjJiZDhfY29sdW1ucyI7YToyMTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjI6Im5vIjtzOjU6ImxhYmVsIjtzOjE6IiMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJhcnJpdmFsX2ZsYWciO3M6NToibGFiZWwiO3M6NzoiQXJyaXZhbCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToidGVzdF9kYXRlIjtzOjU6ImxhYmVsIjtzOjQ6IkRhdGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6ImxhYl9pZCI7czo1OiJsYWJlbCI7czo2OiJMYWIgaWQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjEwOiJDcmVhdGVkIGF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjEwOiJVcGRhdGVkIGF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJwYXRpZW50LmlkIjtzOjU6ImxhYmVsIjtzOjEwOiJQYXRpZW50IElEIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNzoicGF0aWVudC5mdWxsX25hbWUiO3M6NToibGFiZWwiO3M6NzoiUGF0aWVudCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjg7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6ImRvY3Rvci5mdWxsX25hbWUiO3M6NToibGFiZWwiO3M6NjoiRG9jdG9yIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6OTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJ0ZXN0X25hbWUiO3M6NToibGFiZWwiO3M6OToiVGVzdCBuYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToidGVzdF90eXBlIjtzOjU6ImxhYmVsIjtzOjk6IlRlc3QgdHlwZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjExO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjExOiJzYW1wbGVfdHlwZSI7czo1OiJsYWJlbCI7czoxMToiU2FtcGxlIHR5cGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxMjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJ0ZXN0X2RhdGUiO3M6NToibGFiZWwiO3M6OToiVGVzdCBkYXRlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTE6InJlc3VsdF9kYXRlIjtzOjU6ImxhYmVsIjtzOjExOiJSZXN1bHQgZGF0ZSI7czo4OiJpc0hpZGRlbiI7YjoxO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJyZXN1bHRfdmFsdWUiO3M6NToibGFiZWwiO3M6NjoiUmVzdWx0IjtzOjg6ImlzSGlkZGVuIjtiOjE7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6InJlc3VsdF9zdGF0dXMiO3M6NToibGFiZWwiO3M6MTM6IlJlc3VsdCBzdGF0dXMiO3M6ODoiaXNIaWRkZW4iO2I6MTtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxNjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoibm9ybWFsX3JhbmdlIjtzOjU6ImxhYmVsIjtzOjEyOiJOb3JtYWwgcmFuZ2UiO3M6ODoiaXNIaWRkZW4iO2I6MTtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxNzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo0OiJjb3N0IjtzOjU6ImxhYmVsIjtzOjQ6IkNvc3QiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxODthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyOToic2VydmljZVJlcXVlc3QucGF5bWVudF9zdGF0dXMiO3M6NToibGFiZWwiO3M6NzoiUGF5bWVudCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE5O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6InN0YXR1cyI7czo1OiJsYWJlbCI7czo2OiJTdGF0dXMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyMDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToicmVwb3J0X3BhdGgiO3M6NToibGFiZWwiO3M6MTA6IkxhYiBSZXBvcnQiO3M6ODoiaXNIaWRkZW4iO2I6MTtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fX19',1790869072),('qwU5bvBkiZLNMtHMXCnId155mcMH7IRJ4bfil7Ll',NULL,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:157.0) Gecko/20100101 Firefox/157.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRlE2TzB6UGdQUUh3eUdhS01XSDNrRUZDUWljdExaNUdyR0FSUm1tVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTc6Imh0dHA6Ly9sb2NhbGhvc3QvSG9zcGl0YWxNYW5hZ2VtZW50L0hvc3B0YWxNYW5hZ2VtZW50U3lzdGVtL0hvc3B0YWxNU1VQREFURS1tYWluJTIwJTI4MyUyOS9wdWJsaWMiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1791472143);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hospital_profile` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`hospital_profile`)),
  `departments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`departments`)),
  `service_charges` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_charges`)),
  `tax_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tax_rules`)),
  `roles_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`roles_permissions`)),
  `email_sms_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`email_sms_settings`)),
  `backup_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`backup_settings`)),
  `audit_id` varchar(50) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(120) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `record_id` varchar(50) DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `settings_user_id_foreign` (`user_id`),
  KEY `settings_audit_id_index` (`audit_id`),
  CONSTRAINT `settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` varchar(120) NOT NULL,
  `department` varchar(120) DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `shift` enum('day','night','rotational','custom') NOT NULL DEFAULT 'day',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive','on_leave','terminated') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_staff_id_unique` (`staff_id`),
  UNIQUE KEY `staff_email_unique` (`email`),
  KEY `staff_department_id_index` (`department_id`),
  CONSTRAINT `staff_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triages`
--

DROP TABLE IF EXISTS `triages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `triage_id` varchar(50) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `nurse_id` bigint(20) unsigned DEFAULT NULL,
  `encounter_id` varchar(50) DEFAULT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `chief_complaint` varchar(255) DEFAULT NULL,
  `temperature` varchar(20) DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `pulse_rate` smallint(5) unsigned DEFAULT NULL,
  `respiratory_rate` smallint(5) unsigned DEFAULT NULL,
  `oxygen_saturation` smallint(5) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('waiting','in_progress','completed','sent_to_opd') NOT NULL DEFAULT 'waiting',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `triages_triage_id_unique` (`triage_id`),
  KEY `triages_patient_id_foreign` (`patient_id`),
  KEY `triages_nurse_id_foreign` (`nurse_id`),
  KEY `triages_encounter_id_index` (`encounter_id`),
  CONSTRAINT `triages_nurse_id_foreign` FOREIGN KEY (`nurse_id`) REFERENCES `nurses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `triages_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triages`
--

LOCK TABLES `triages` WRITE;
/*!40000 ALTER TABLE `triages` DISABLE KEYS */;
INSERT INTO `triages` VALUES (1,'TRG-20260930-JWVIW1',1,NULL,'OPD-20260930-KSSJ7T','medium',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'completed','2026-09-30 16:38:25','2026-09-30 16:44:14'),(2,'TRG-20260930-Y2QVCT',2,NULL,'OPD-20260930-MUCEBP','medium',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'completed','2026-09-30 18:49:03','2026-09-30 18:49:23');
/*!40000 ALTER TABLE `triages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'nejash','nejash123@gmail.com',NULL,NULL,'$2y$12$niWX.t4B7U/a7Q4xCAWrd.JAjUW2twc17kWomUMxE/U2xKrOxQI6y',NULL,'active','2026-09-29 17:53:09','2026-09-29 17:53:09'),(2,'Abdi Ahmad','abdi123@gmail.com','2026-09-30 16:38:16',NULL,'$2y$12$dSmu7yoOxKdty9p8sYfSmuZ/.RrIMpi7hmeKT1JjbXcFJ6ixYzO9O',NULL,'active','2026-09-30 16:38:16','2026-10-01 11:02:38'),(3,'dr.Ahamad Abdi','ahmad123@gmail.com','2026-09-30 17:42:22','avatars/01M3VZVP8C7DYDVWHFD82SAW78.jpg','$2y$12$LlClbusnOySGSrQH/lBOOe7dCgnVCiUYZ8KjVSbLk9dETCVJxwzUi',NULL,'active','2026-09-30 17:42:22','2026-10-01 12:03:23'),(4,'urji Aliyyi','urji123@gmail.com','2026-09-30 18:07:22',NULL,'$2y$12$osFD5nK2FrfRacE6UNMhReBgDE53MJxNRl6Bt1vTiEw9NwpnOJWlq',NULL,'active','2026-09-30 18:07:22','2026-10-01 11:40:47'),(5,'faxe Umar abdi','faxe123@gmail.com','2026-09-30 18:48:42',NULL,'$2y$12$yIubR04NegNqXsagFNHMGOFafpzeaynTZDkcVzww2vr8fULtTb7lG',NULL,'new patient','2026-09-30 18:48:42','2026-09-30 18:48:42'),(6,'pharmacist nejoo','pharm123@gmail.com','2026-09-30 20:11:08',NULL,'$2y$12$oZrf1vUpIDrju13Z1UXwEeFCpRNu30LxDCqsJhXbILZ5mT1d1CZN.',NULL,'active','2026-09-30 20:11:07','2026-09-30 20:11:08'),(7,'radio Abas','radio123@gmail.com','2026-10-01 08:59:06',NULL,'$2y$12$juW71spiEDRNatlMvlavQ.WpdnUMFxN1STPmxkoAM0uNKktawnxmy',NULL,'active','2026-10-01 08:59:06','2026-10-01 08:59:06'),(8,'casher cash','cash123@gmail.com','2026-10-01 09:08:27',NULL,'$2y$12$Uqbyzl929nZiVJKhj9ogwOi.u5o5sSyis6Tk9r.M5R6XN7Y.uQPfq',NULL,'active','2026-10-01 09:08:27','2026-10-01 09:08:27'),(9,'recitionist','receip123@gmail.com','2026-10-01 09:33:52',NULL,'$2y$12$JRPKDWYkEhjnFEhqIU5iZuFxMMR4CZXszVfAZEYhwyJb6nhGSTUzK',NULL,'active','2026-10-01 09:33:52','2026-10-01 09:33:52'),(10,'Mahi Anawr yusuf','mahi123@gmail.com','2026-10-01 12:07:55',NULL,'$2y$12$gnA19nco4QA2XRB1cdJtu.XhZVccMyyd3FVXWR1zK3fy2D5B.8Ejy',NULL,'active','2026-10-01 12:07:55','2026-10-01 12:07:55');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-13 22:12:47
