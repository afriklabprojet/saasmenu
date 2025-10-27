-- MySQL dump 10.13  Distrib 9.3.0, for macos15.2 (arm64)
--
-- Host: localhost    Database: restro_saas
-- ------------------------------------------------------
-- Server version	9.3.0

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
-- Table structure for table `about`
--

DROP TABLE IF EXISTS `about`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `about` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `about_content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `about_vendor_id_unique` (`vendor_id`),
  KEY `about_vendor_id_index` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `about`
--

LOCK TABLES `about` WRITE;
/*!40000 ALTER TABLE `about` DISABLE KEYS */;
/*!40000 ALTER TABLE `about` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashed_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions` json DEFAULT NULL,
  `restaurant_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `usage_count` int NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_keys_hashed_key_unique` (`hashed_key`),
  KEY `api_keys_hashed_key_is_active_index` (`hashed_key`,`is_active`),
  KEY `api_keys_restaurant_id_is_active_index` (`restaurant_id`,`is_active`),
  KEY `api_keys_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `api_keys_expires_at_index` (`expires_at`),
  CONSTRAINT `api_keys_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `api_keys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `android_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ios_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_app_on_off` tinyint NOT NULL DEFAULT '2',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `area`
--

DROP TABLE IF EXISTS `area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `area` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reorder_id` int NOT NULL DEFAULT '0',
  `is_available` int NOT NULL DEFAULT '1',
  `is_deleted` int NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `area_city_id_foreign` (`city_id`),
  CONSTRAINT `area_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `area`
--

LOCK TABLES `area` WRITE;
/*!40000 ALTER TABLE `area` DISABLE KEYS */;
/*!40000 ALTER TABLE `area` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `reorder_id` int NOT NULL DEFAULT '0',
  `vendor_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `banner_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1=category,2=service,3=',
  `section` int DEFAULT '1' COMMENT '1=banner1,2=banner2,3=banner3',
  `is_available` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=yes,2=no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint NOT NULL DEFAULT '1',
  `reorder_id` int NOT NULL DEFAULT '0',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blogs_vendor_id_index` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs`
--

LOCK TABLES `blogs` WRITE;
/*!40000 ALTER TABLE `blogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_id` int NOT NULL,
  `service_id` int NOT NULL,
  `service_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `offer_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `offer_amount` double NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` int NOT NULL COMMENT '1=Pending,2=for paid',
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_total` double NOT NULL,
  `tax` double NOT NULL,
  `grand_total` double NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `user_id` int DEFAULT '0',
  `session_id` text COLLATE utf8mb4_unicode_ci,
  `item_id` int DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_price` double DEFAULT NULL,
  `extras_id` int DEFAULT NULL,
  `extras_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extras_price` double DEFAULT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variation_id` int DEFAULT NULL,
  `variation_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `price` double DEFAULT NULL,
  `tax` double DEFAULT NULL,
  `variants_id` int DEFAULT NULL,
  `variants_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variants_price` double DEFAULT NULL,
  `buynow` tinyint(1) NOT NULL DEFAULT '0',
  `product_price` double NOT NULL,
  `product_tax` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reorder_id` int NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1--> yes, 2-->No',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1--> yes, 2-->No',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `city`
--

DROP TABLE IF EXISTS `city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `city` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reorder_id` int NOT NULL DEFAULT '0',
  `is_available` tinyint NOT NULL DEFAULT '1',
  `Is_deleted` tinyint NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `city`
--

LOCK TABLES `city` WRITE;
/*!40000 ALTER TABLE `city` DISABLE KEYS */;
/*!40000 ALTER TABLE `city` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('fixed','percentage') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active_from` datetime NOT NULL,
  `active_to` datetime NOT NULL,
  `limit` int NOT NULL DEFAULT '0',
  `reorder_id` int NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `coupons_vendor_id_index` (`vendor_id`),
  KEY `coupons_reorder_id_index` (`reorder_id`),
  KEY `coupons_is_available_is_deleted_index` (`is_available`,`is_deleted`),
  KEY `coupons_code_index` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_status`
--

DROP TABLE IF EXISTS `custom_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_status` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reorder_id` int NOT NULL DEFAULT '0',
  `vendor_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` int NOT NULL COMMENT '1=default,2=process,3=complete,4=cancel',
  `is_available` int NOT NULL DEFAULT '1',
  `is_deleted` int NOT NULL DEFAULT '2',
  `order_type` int NOT NULL DEFAULT '1' COMMENT '1=delivery,2=pickup,3=dinein,4=pos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_status_vendor_id_index` (`vendor_id`),
  KEY `custom_status_vendor_id_order_type_index` (`vendor_id`,`order_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_status`
--

LOCK TABLES `custom_status` WRITE;
/*!40000 ALTER TABLE `custom_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `address_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_addresses_user_id_index` (`user_id`),
  KEY `customer_addresses_user_id_is_default_index` (`user_id`,`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_addresses`
--

LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_password_resets`
--

DROP TABLE IF EXISTS `customer_password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `customer_password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_password_resets`
--

LOCK TABLES `customer_password_resets` WRITE;
/*!40000 ALTER TABLE `customer_password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `addresses` json DEFAULT NULL,
  `notification_preferences` json DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveryareas`
--

DROP TABLE IF EXISTS `deliveryareas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deliveryareas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reorder_id` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deliveryareas_vendor_id_index` (`vendor_id`),
  KEY `deliveryareas_reorder_id_index` (`reorder_id`),
  CONSTRAINT `deliveryareas_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveryareas`
--

LOCK TABLES `deliveryareas` WRITE;
/*!40000 ALTER TABLE `deliveryareas` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliveryareas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_tokens`
--

DROP TABLE IF EXISTS `device_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `device_token` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `device_info` json DEFAULT NULL,
  `app_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_tokens_user_id_device_token_unique` (`user_id`,`device_token`),
  KEY `device_tokens_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `device_tokens_device_type_index` (`device_type`),
  KEY `device_tokens_last_used_at_index` (`last_used_at`),
  CONSTRAINT `device_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_tokens`
--

LOCK TABLES `device_tokens` WRITE;
/*!40000 ALTER TABLE `device_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `export_jobs`
--

DROP TABLE IF EXISTS `export_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `export_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `format` enum('csv','xlsx','json') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'csv',
  `total_rows` int NOT NULL DEFAULT '0',
  `processed_rows` int NOT NULL DEFAULT '0',
  `filters` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `download_count` int NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `export_jobs_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `export_jobs_user_id_status_index` (`user_id`,`status`),
  KEY `export_jobs_type_status_index` (`type`,`status`),
  KEY `export_jobs_scheduled_at_index` (`scheduled_at`),
  KEY `export_jobs_expires_at_index` (`expires_at`),
  CONSTRAINT `export_jobs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `export_jobs`
--

LOCK TABLES `export_jobs` WRITE;
/*!40000 ALTER TABLE `export_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `export_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci,
  `reorder_id` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faqs_vendor_id_index` (`vendor_id`),
  KEY `faqs_reorder_id_index` (`reorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `features`
--

DROP TABLE IF EXISTS `features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reorder_id` int DEFAULT NULL,
  `vendor_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `features`
--

LOCK TABLES `features` WRITE;
/*!40000 ALTER TABLE `features` DISABLE KEYS */;
/*!40000 ALTER TABLE `features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firebase_analytics`
--

DROP TABLE IF EXISTS `firebase_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firebase_analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `metric_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metric_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metric_value` int NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `firebase_analytics_date_metric_type_metric_key_unique` (`date`,`metric_type`,`metric_key`),
  KEY `firebase_analytics_date_metric_type_index` (`date`,`metric_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firebase_analytics`
--

LOCK TABLES `firebase_analytics` WRITE;
/*!40000 ALTER TABLE `firebase_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `firebase_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firebase_automations`
--

DROP TABLE IF EXISTS `firebase_automations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firebase_automations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `trigger_type` enum('order_created','order_confirmed','user_registered','payment_success','birthday','inactivity','custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_conditions` json DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `delay_minutes` int NOT NULL DEFAULT '0',
  `max_sends_per_user` int NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `firebase_automations_trigger_type_is_active_index` (`trigger_type`,`is_active`),
  KEY `firebase_automations_created_by_index` (`created_by`),
  CONSTRAINT `firebase_automations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firebase_automations`
--

LOCK TABLES `firebase_automations` WRITE;
/*!40000 ALTER TABLE `firebase_automations` DISABLE KEYS */;
/*!40000 ALTER TABLE `firebase_automations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firebase_campaigns`
--

DROP TABLE IF EXISTS `firebase_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firebase_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','scheduled','active','paused','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `recipients_type` enum('users','devices','topics','all','segment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipients_data` json DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `firebase_campaigns_status_scheduled_at_index` (`status`,`scheduled_at`),
  KEY `firebase_campaigns_created_by_index` (`created_by`),
  CONSTRAINT `firebase_campaigns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firebase_campaigns`
--

LOCK TABLES `firebase_campaigns` WRITE;
/*!40000 ALTER TABLE `firebase_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `firebase_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firebase_devices`
--

DROP TABLE IF EXISTS `firebase_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firebase_devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `device_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_type` enum('android','ios','web') COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_os` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `topics` json DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `firebase_devices_device_token_unique` (`device_token`),
  KEY `firebase_devices_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `firebase_devices_device_type_is_active_index` (`device_type`,`is_active`),
  KEY `firebase_devices_last_seen_at_index` (`last_seen_at`),
  CONSTRAINT `firebase_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firebase_devices`
--

LOCK TABLES `firebase_devices` WRITE;
/*!40000 ALTER TABLE `firebase_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `firebase_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firebase_notifications`
--

DROP TABLE IF EXISTS `firebase_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firebase_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipients_type` enum('users','devices','topics','all','segment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipients_data` json DEFAULT NULL,
  `status` enum('pending','scheduled','sent','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `sent_by` bigint unsigned DEFAULT NULL,
  `firebase_response` json DEFAULT NULL,
  `success_count` int NOT NULL DEFAULT '0',
  `failure_count` int NOT NULL DEFAULT '0',
  `read_count` int NOT NULL DEFAULT '0',
  `click_count` int NOT NULL DEFAULT '0',
  `campaign_id` bigint unsigned DEFAULT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `automation_id` bigint unsigned DEFAULT NULL,
  `priority` enum('low','normal','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `ttl` int DEFAULT NULL,
  `sound` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge` int DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `firebase_notifications_sent_by_foreign` (`sent_by`),
  KEY `firebase_notifications_status_scheduled_at_index` (`status`,`scheduled_at`),
  KEY `firebase_notifications_recipients_type_status_index` (`recipients_type`,`status`),
  KEY `firebase_notifications_sent_at_index` (`sent_at`),
  KEY `firebase_notifications_created_at_index` (`created_at`),
  KEY `firebase_notifications_campaign_id_foreign` (`campaign_id`),
  KEY `firebase_notifications_template_id_foreign` (`template_id`),
  KEY `firebase_notifications_automation_id_foreign` (`automation_id`),
  CONSTRAINT `firebase_notifications_automation_id_foreign` FOREIGN KEY (`automation_id`) REFERENCES `firebase_automations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `firebase_notifications_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `firebase_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `firebase_notifications_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `firebase_notifications_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `firebase_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firebase_notifications`
--

LOCK TABLES `firebase_notifications` WRITE;
/*!40000 ALTER TABLE `firebase_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `firebase_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firebase_segments`
--

DROP TABLE IF EXISTS `firebase_segments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firebase_segments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `conditions` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_count` int NOT NULL DEFAULT '0',
  `last_calculated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `firebase_segments_is_active_last_calculated_at_index` (`is_active`,`last_calculated_at`),
  KEY `firebase_segments_created_by_index` (`created_by`),
  CONSTRAINT `firebase_segments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firebase_segments`
--

LOCK TABLES `firebase_segments` WRITE;
/*!40000 ALTER TABLE `firebase_segments` DISABLE KEYS */;
/*!40000 ALTER TABLE `firebase_segments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firebase_templates`
--

DROP TABLE IF EXISTS `firebase_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firebase_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `variables` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `firebase_templates_category_is_active_index` (`category`,`is_active`),
  KEY `firebase_templates_created_by_index` (`created_by`),
  CONSTRAINT `firebase_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firebase_templates`
--

LOCK TABLES `firebase_templates` WRITE;
/*!40000 ALTER TABLE `firebase_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `firebase_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firebase_topics`
--

DROP TABLE IF EXISTS `firebase_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firebase_topics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `subscriber_count` int NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `firebase_topics_name_unique` (`name`),
  KEY `firebase_topics_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firebase_topics`
--

LOCK TABLES `firebase_topics` WRITE;
/*!40000 ALTER TABLE `firebase_topics` DISABLE KEYS */;
/*!40000 ALTER TABLE `firebase_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `footerfeatures`
--

DROP TABLE IF EXISTS `footerfeatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `footerfeatures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `footerfeatures`
--

LOCK TABLES `footerfeatures` WRITE;
/*!40000 ALTER TABLE `footerfeatures` DISABLE KEYS */;
/*!40000 ALTER TABLE `footerfeatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `global_extras`
--

DROP TABLE IF EXISTS `global_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `global_extras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reorder_id` int NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `global_extras_vendor_id_index` (`vendor_id`),
  KEY `global_extras_reorder_id_index` (`reorder_id`),
  CONSTRAINT `global_extras_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `global_extras`
--

LOCK TABLES `global_extras` WRITE;
/*!40000 ALTER TABLE `global_extras` DISABLE KEYS */;
/*!40000 ALTER TABLE `global_extras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_field_mappings`
--

DROP TABLE IF EXISTS `import_export_field_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_field_mappings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_field` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_field` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transformation_rules` json DEFAULT NULL,
  `validation_rules` json DEFAULT NULL,
  `default_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `mapping_id` bigint unsigned NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_field_mappings_mapping_id_sort_order_index` (`mapping_id`,`sort_order`),
  KEY `import_export_field_mappings_data_type_target_field_index` (`data_type`,`target_field`),
  CONSTRAINT `import_export_field_mappings_mapping_id_foreign` FOREIGN KEY (`mapping_id`) REFERENCES `import_export_mappings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_field_mappings`
--

LOCK TABLES `import_export_field_mappings` WRITE;
/*!40000 ALTER TABLE `import_export_field_mappings` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_field_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_files`
--

DROP TABLE IF EXISTS `import_export_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint NOT NULL,
  `hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('import_source','export_result','template','sample') COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `job_id` bigint unsigned DEFAULT NULL,
  `download_count` int NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_files_type_data_type_index` (`type`,`data_type`),
  KEY `import_export_files_user_id_index` (`user_id`),
  KEY `import_export_files_job_id_index` (`job_id`),
  KEY `import_export_files_expires_at_index` (`expires_at`),
  KEY `import_export_files_hash_index` (`hash`),
  CONSTRAINT `import_export_files_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `import_export_jobs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `import_export_files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_files`
--

LOCK TABLES `import_export_files` WRITE;
/*!40000 ALTER TABLE `import_export_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_jobs`
--

DROP TABLE IF EXISTS `import_export_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('import','export') COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `export_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `format` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mapping` json DEFAULT NULL,
  `filters` json DEFAULT NULL,
  `options` json DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `total_records` int NOT NULL DEFAULT '0',
  `processed_records` int NOT NULL DEFAULT '0',
  `successful_records` int NOT NULL DEFAULT '0',
  `failed_records` int NOT NULL DEFAULT '0',
  `errors` json DEFAULT NULL,
  `warnings` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `progress` decimal(5,2) NOT NULL DEFAULT '0.00',
  `estimated_completion` timestamp NULL DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `memory_usage` int DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_jobs_type_status_index` (`type`,`status`),
  KEY `import_export_jobs_data_type_status_index` (`data_type`,`status`),
  KEY `import_export_jobs_user_id_type_index` (`user_id`,`type`),
  KEY `import_export_jobs_status_index` (`status`),
  KEY `import_export_jobs_created_at_index` (`created_at`),
  CONSTRAINT `import_export_jobs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_jobs`
--

LOCK TABLES `import_export_jobs` WRITE;
/*!40000 ALTER TABLE `import_export_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_logs`
--

DROP TABLE IF EXISTS `import_export_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned DEFAULT NULL,
  `level` enum('info','warning','error','debug') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `context` json DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line` int DEFAULT NULL,
  `logged_at` timestamp NOT NULL,
  `metadata` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_logs_job_id_level_index` (`job_id`,`level`),
  KEY `import_export_logs_level_logged_at_index` (`level`,`logged_at`),
  KEY `import_export_logs_logged_at_index` (`logged_at`),
  CONSTRAINT `import_export_logs_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `import_export_jobs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_logs`
--

LOCK TABLES `import_export_logs` WRITE;
/*!40000 ALTER TABLE `import_export_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_mappings`
--

DROP TABLE IF EXISTS `import_export_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_mappings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_fields` json NOT NULL,
  `target_fields` json NOT NULL,
  `field_mappings` json NOT NULL,
  `transformations` json DEFAULT NULL,
  `validation_rules` json DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` bigint unsigned DEFAULT NULL,
  `usage_count` int NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_mappings_data_type_is_active_index` (`data_type`,`is_active`),
  KEY `import_export_mappings_is_default_data_type_index` (`is_default`,`data_type`),
  KEY `import_export_mappings_user_id_index` (`user_id`),
  CONSTRAINT `import_export_mappings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_mappings`
--

LOCK TABLES `import_export_mappings` WRITE;
/*!40000 ALTER TABLE `import_export_mappings` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_schedules`
--

DROP TABLE IF EXISTS `import_export_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('import','export') COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `format` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cron_expression` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `options` json DEFAULT NULL,
  `filters` json DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `export_destination` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `run_count` int NOT NULL DEFAULT '0',
  `success_count` int NOT NULL DEFAULT '0',
  `failure_count` int NOT NULL DEFAULT '0',
  `last_result` json DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_schedules_type_is_active_index` (`type`,`is_active`),
  KEY `import_export_schedules_next_run_at_index` (`next_run_at`),
  KEY `import_export_schedules_user_id_index` (`user_id`),
  CONSTRAINT `import_export_schedules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_schedules`
--

LOCK TABLES `import_export_schedules` WRITE;
/*!40000 ALTER TABLE `import_export_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_templates`
--

DROP TABLE IF EXISTS `import_export_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('import','export') COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fields` json NOT NULL,
  `sample_data` json DEFAULT NULL,
  `validation_rules` json DEFAULT NULL,
  `transformations` json DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `download_count` int NOT NULL DEFAULT '0',
  `user_id` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_templates_type_data_type_index` (`type`,`data_type`),
  KEY `import_export_templates_format_is_active_index` (`format`,`is_active`),
  KEY `import_export_templates_is_system_index` (`is_system`),
  KEY `import_export_templates_user_id_index` (`user_id`),
  CONSTRAINT `import_export_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_templates`
--

LOCK TABLES `import_export_templates` WRITE;
/*!40000 ALTER TABLE `import_export_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_transformations`
--

DROP TABLE IF EXISTS `import_export_transformations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_transformations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` json NOT NULL,
  `input_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `output_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `example_input` text COLLATE utf8mb4_unicode_ci,
  `example_output` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` bigint unsigned DEFAULT NULL,
  `usage_count` int NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_transformations_type_is_active_index` (`type`,`is_active`),
  KEY `import_export_transformations_is_system_index` (`is_system`),
  KEY `import_export_transformations_user_id_index` (`user_id`),
  CONSTRAINT `import_export_transformations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_transformations`
--

LOCK TABLES `import_export_transformations` WRITE;
/*!40000 ALTER TABLE `import_export_transformations` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_transformations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_export_validation_rules`
--

DROP TABLE IF EXISTS `import_export_validation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_export_validation_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `field_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rule_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` json DEFAULT NULL,
  `error_message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `example` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `severity` int NOT NULL DEFAULT '1',
  `user_id` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_export_validation_rules_field_type_rule_type_index` (`field_type`,`rule_type`),
  KEY `import_export_validation_rules_is_system_is_active_index` (`is_system`,`is_active`),
  KEY `import_export_validation_rules_user_id_index` (`user_id`),
  CONSTRAINT `import_export_validation_rules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_export_validation_rules`
--

LOCK TABLES `import_export_validation_rules` WRITE;
/*!40000 ALTER TABLE `import_export_validation_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_export_validation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_jobs`
--

DROP TABLE IF EXISTS `import_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_rows` int NOT NULL DEFAULT '0',
  `processed_rows` int NOT NULL DEFAULT '0',
  `successful_rows` int NOT NULL DEFAULT '0',
  `failed_rows` int NOT NULL DEFAULT '0',
  `errors` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_jobs_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `import_jobs_user_id_status_index` (`user_id`,`status`),
  KEY `import_jobs_type_status_index` (`type`,`status`),
  KEY `import_jobs_scheduled_at_index` (`scheduled_at`),
  CONSTRAINT `import_jobs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_jobs`
--

LOCK TABLES `import_jobs` WRITE;
/*!40000 ALTER TABLE `import_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_images`
--

DROP TABLE IF EXISTS `item_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint unsigned NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_images_item_id_index` (`item_id`),
  CONSTRAINT `item_images_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_images`
--

LOCK TABLES `item_images` WRITE;
/*!40000 ALTER TABLE `item_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `item_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `cat_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `original_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reorder_id` int NOT NULL DEFAULT '0',
  `top_deals` tinyint(1) NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `items_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `layout` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ltr',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` enum('1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2',
  `is_available` enum('1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `is_deleted` enum('1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `languages_code_index` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'English','en','ltr','en.png','1','1','2','2025-10-24 16:24:02','2025-10-24 16:24:02'),(2,'Français','fr','ltr','fr.png','2','1','2','2025-10-24 16:24:02','2025-10-24 16:24:02'),(3,'العربية','ar','rtl','ar.png','2','1','2','2025-10-24 16:24:02','2025-10-24 16:24:02');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_cards`
--

DROP TABLE IF EXISTS `loyalty_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `restaurant_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `card_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `total_spent` decimal(10,2) NOT NULL DEFAULT '0.00',
  `visits_count` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loyalty_cards_card_number_unique` (`card_number`),
  KEY `loyalty_cards_customer_id_foreign` (`customer_id`),
  KEY `loyalty_cards_restaurant_id_foreign` (`restaurant_id`),
  KEY `loyalty_cards_user_id_foreign` (`user_id`),
  CONSTRAINT `loyalty_cards_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_cards_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_cards`
--

LOCK TABLES `loyalty_cards` WRITE;
/*!40000 ALTER TABLE `loyalty_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_members`
--

DROP TABLE IF EXISTS `loyalty_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `member_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points_balance` int NOT NULL DEFAULT '0',
  `lifetime_points` int NOT NULL DEFAULT '0',
  `tier_id` bigint unsigned DEFAULT NULL,
  `referral_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `joined_at` timestamp NOT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `preferences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loyalty_members_restaurant_id_email_unique` (`restaurant_id`,`email`),
  UNIQUE KEY `loyalty_members_restaurant_id_phone_unique` (`restaurant_id`,`phone`),
  UNIQUE KEY `loyalty_members_member_code_unique` (`member_code`),
  UNIQUE KEY `loyalty_members_referral_code_unique` (`referral_code`),
  KEY `loyalty_members_user_id_foreign` (`user_id`),
  KEY `loyalty_members_tier_id_foreign` (`tier_id`),
  KEY `loyalty_members_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `loyalty_members_restaurant_id_email_index` (`restaurant_id`,`email`),
  KEY `loyalty_members_restaurant_id_phone_index` (`restaurant_id`,`phone`),
  KEY `loyalty_members_restaurant_id_points_balance_index` (`restaurant_id`,`points_balance`),
  CONSTRAINT `loyalty_members_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_members_tier_id_foreign` FOREIGN KEY (`tier_id`) REFERENCES `loyalty_tiers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loyalty_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_members`
--

LOCK TABLES `loyalty_members` WRITE;
/*!40000 ALTER TABLE `loyalty_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_programs`
--

DROP TABLE IF EXISTS `loyalty_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_programs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('points','visits','spend') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'points',
  `points_per_currency` decimal(8,2) NOT NULL DEFAULT '1.00',
  `currency_per_point` decimal(8,2) NOT NULL DEFAULT '0.01',
  `min_points_redemption` int NOT NULL DEFAULT '100',
  `points_expiry_months` int DEFAULT NULL,
  `tiers` json DEFAULT NULL,
  `rules` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_programs_restaurant_id_is_active_index` (`restaurant_id`,`is_active`),
  KEY `loyalty_programs_type_index` (`type`),
  CONSTRAINT `loyalty_programs_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_programs`
--

LOCK TABLES `loyalty_programs` WRITE;
/*!40000 ALTER TABLE `loyalty_programs` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_redemptions`
--

DROP TABLE IF EXISTS `loyalty_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_redemptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint unsigned NOT NULL,
  `restaurant_id` bigint unsigned NOT NULL,
  `reward_id` bigint unsigned NOT NULL,
  `order_id` int unsigned DEFAULT NULL,
  `points_used` int NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `redeem_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','used','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `redeemed_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `used_by` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loyalty_redemptions_redeem_code_unique` (`redeem_code`),
  KEY `loyalty_redemptions_order_id_foreign` (`order_id`),
  KEY `loyalty_redemptions_used_by_foreign` (`used_by`),
  KEY `loyalty_redemptions_member_id_status_index` (`member_id`,`status`),
  KEY `loyalty_redemptions_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `loyalty_redemptions_reward_id_index` (`reward_id`),
  KEY `loyalty_redemptions_redeem_code_index` (`redeem_code`),
  KEY `loyalty_redemptions_expires_at_index` (`expires_at`),
  CONSTRAINT `loyalty_redemptions_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `loyalty_members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_redemptions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loyalty_redemptions_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_redemptions_reward_id_foreign` FOREIGN KEY (`reward_id`) REFERENCES `loyalty_rewards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_redemptions_used_by_foreign` FOREIGN KEY (`used_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_redemptions`
--

LOCK TABLES `loyalty_redemptions` WRITE;
/*!40000 ALTER TABLE `loyalty_redemptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_redemptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_rewards`
--

DROP TABLE IF EXISTS `loyalty_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `tier_id` bigint unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reward_type` enum('discount_percentage','discount_fixed','free_item','free_delivery','cashback','special_offer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reward_value` decimal(10,2) NOT NULL,
  `points_required` int NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terms_conditions` json DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `usage_limit_per_member` int DEFAULT NULL,
  `valid_from` timestamp NULL DEFAULT NULL,
  `valid_until` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_rewards_tier_id_foreign` (`tier_id`),
  KEY `loyalty_rewards_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `loyalty_rewards_restaurant_id_tier_id_index` (`restaurant_id`,`tier_id`),
  KEY `loyalty_rewards_points_required_index` (`points_required`),
  KEY `loyalty_rewards_valid_from_valid_until_index` (`valid_from`,`valid_until`),
  CONSTRAINT `loyalty_rewards_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_rewards_tier_id_foreign` FOREIGN KEY (`tier_id`) REFERENCES `loyalty_tiers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_rewards`
--

LOCK TABLES `loyalty_rewards` WRITE;
/*!40000 ALTER TABLE `loyalty_rewards` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_rewards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_tiers`
--

DROP TABLE IF EXISTS `loyalty_tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_tiers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `program_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `min_points` int NOT NULL DEFAULT '0',
  `min_spent` int NOT NULL DEFAULT '0',
  `min_visits` int NOT NULL DEFAULT '0',
  `points_multiplier` decimal(3,2) NOT NULL DEFAULT '1.00',
  `benefits` json DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_tiers_program_id_is_active_index` (`program_id`,`is_active`),
  KEY `loyalty_tiers_sort_order_index` (`sort_order`),
  CONSTRAINT `loyalty_tiers_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `loyalty_programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_tiers`
--

LOCK TABLES `loyalty_tiers` WRITE;
/*!40000 ALTER TABLE `loyalty_tiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_tiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_transactions`
--

DROP TABLE IF EXISTS `loyalty_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint unsigned NOT NULL,
  `restaurant_id` bigint unsigned NOT NULL,
  `order_id` int unsigned DEFAULT NULL,
  `type` enum('welcome_bonus','order_purchase','referral_bonus','birthday_bonus','challenge_completion','admin_adjustment','reward_redemption','points_expiry','tier_upgrade_bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL,
  `balance_after` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_transactions_order_id_foreign` (`order_id`),
  KEY `loyalty_transactions_member_id_created_at_index` (`member_id`,`created_at`),
  KEY `loyalty_transactions_restaurant_id_created_at_index` (`restaurant_id`,`created_at`),
  KEY `loyalty_transactions_type_index` (`type`),
  KEY `loyalty_transactions_expires_at_index` (`expires_at`),
  CONSTRAINT `loyalty_transactions_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `loyalty_members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loyalty_transactions_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_transactions`
--

LOCK TABLES `loyalty_transactions` WRITE;
/*!40000 ALTER TABLE `loyalty_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `category_id` int unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','out_of_stock') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_vegetarian` tinyint(1) NOT NULL DEFAULT '0',
  `is_vegan` tinyint(1) NOT NULL DEFAULT '0',
  `is_gluten_free` tinyint(1) NOT NULL DEFAULT '0',
  `allergens` json DEFAULT NULL,
  `nutritional_info` json DEFAULT NULL,
  `preparation_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `track_inventory` tinyint(1) NOT NULL DEFAULT '0',
  `stock_quantity` int NOT NULL DEFAULT '0',
  `low_stock_threshold` int NOT NULL DEFAULT '0',
  `modifiers` json DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `menu_items_category_id_status_index` (`category_id`,`status`),
  KEY `menu_items_is_featured_index` (`is_featured`),
  KEY `menu_items_barcode_index` (`barcode`),
  KEY `menu_items_sku_index` (`sku`),
  KEY `menu_items_sort_order_index` (`sort_order`),
  CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2022_09_28_105405_create_categories_table',1),(6,'2022_09_29_104135_create_services_table',1),(7,'2022_09_29_110444_create_service_images_table',1),(8,'2022_10_18_121106_create_banners_table',1),(9,'2022_10_22_051717_create_blogs_table',1),(10,'2022_11_07_073848_create_promocodes_table',1),(11,'2022_11_10_000000_update_users_table_for_restro_saas',1),(12,'2022_11_11_000000_create_payments_table',1),(13,'2022_11_11_050805_create_bookings_table',1),(14,'2022_11_11_105804_create_products_table',1),(15,'2022_11_12_000000_seed_default_payment_methods',1),(16,'2022_11_12_105804_update_products_table',1),(17,'2022_11_13_000000_create_payment_methods_table',1),(18,'2022_11_15_000000_create_settings_table',1),(19,'2022_12_01_053128_create_footerfeatures_table',1),(20,'2022_12_01_085740_create_subscribers_table',1),(21,'2022_12_02_034640_create_favorites_table',1),(22,'2022_12_05_114128_create_carts_table',1),(23,'2022_12_10_033631_create_orders_table',1),(24,'2022_12_10_051230_create_order_details_table',1),(25,'2022_12_15_054853_create_contacts_table',1),(26,'2024_01_01_000000_create_firebase_tables',1),(27,'2024_01_01_000001_create_import_jobs_table',1),(28,'2024_01_01_000002_create_export_jobs_table',1),(29,'2024_01_01_000003_create_device_tokens_table',1),(30,'2024_01_02_000000_create_import_export_tables',1),(31,'2024_01_15_000000_create_restaurants_table',1),(32,'2024_01_15_000001_create_customers_table',1),(33,'2024_01_15_000001_create_items_table',1),(34,'2024_01_15_000002_create_tables_table',1),(35,'2024_01_15_000003_create_order_items_table',1),(36,'2024_01_15_000003_create_table_ratings_table',1),(37,'2024_01_15_000004_create_loyalty_cards_table',1),(38,'2024_01_15_000005_create_notifications_table',1),(39,'2024_01_15_000006_add_api_fields_to_orders_table',1),(40,'2024_01_15_000007_create_customer_password_resets_table',1),(41,'2024_01_15_000008_create_loyalty_programs_table',1),(42,'2024_01_15_000009_create_loyalty_tiers_table',1),(43,'2024_01_15_000010_create_loyalty_members_table',1),(44,'2024_01_15_000011_create_loyalty_transactions_table',1),(45,'2024_01_15_000012_create_loyalty_rewards_table',1),(46,'2024_01_15_000013_create_loyalty_redemptions_table',1),(47,'2024_01_15_000014_create_pos_terminals_table',1),(48,'2024_01_15_000015_create_pos_sessions_table',1),(49,'2024_01_15_000018_create_menu_items_table',1),(50,'2024_01_15_000021_create_pos_carts_table',1),(51,'2024_01_15_000023_create_api_keys_table',1),(52,'2024_01_15_100000_create_paypal_tables',1),(53,'2024_01_15_200000_create_social_login_tables',1),(54,'2025_10_17_000001_add_cinetpay_payment_method',1),(55,'2025_10_17_000002_create_wallet_system',1),(56,'2025_10_17_152100_update_app_name_to_emenu',1),(57,'2025_10_17_162141_create_push_subscriptions_table',1),(58,'2025_10_18_195300_create_languages_table',1),(59,'2025_10_18_195659_create_systemaddons_table',1),(60,'2025_10_18_201443_create_pricing_plans_table',1),(61,'2025_10_18_201448_create_transactions_table',1),(62,'2025_10_18_201517_add_plan_id_to_users_table',1),(63,'2025_10_18_204236_add_reorder_id_to_categories_table',1),(64,'2025_10_18_204335_add_reorder_id_to_items_table',1),(65,'2025_10_18_204359_add_reorder_id_to_multiple_tables',1),(66,'2025_10_18_205643_add_banner_image_to_banners_table',1),(67,'2025_10_18_211317_add_vendor_id_to_blogs_table',1),(68,'2025_10_18_211744_add_item_columns_to_carts_table',1),(69,'2025_10_18_213718_create_top_deals_table',1),(70,'2025_10_18_214515_add_buynow_to_carts_table',1),(71,'2025_10_18_215311_create_app_settings_table',1),(72,'2025_10_18_215637_create_social_links_table',1),(73,'2025_10_18_220433_create_timings_table',1),(74,'2025_10_18_220850_add_whatsapp_chat_to_settings_table',1),(75,'2025_10_18_234135_create_city_table',1),(76,'2025_10_19_004113_create_area_table',1),(77,'2025_10_19_075700_add_social_media_links_to_settings_table',1),(78,'2025_10_19_082329_create_features_table',1),(79,'2025_10_19_082914_create_testimonials_table',1),(80,'2025_10_19_083355_add_cover_image_to_settings_table',1),(81,'2025_10_19_083522_add_available_on_landing_to_users_table',1),(82,'2025_10_19_083846_create_store_category_table',1),(83,'2025_10_19_085645_create_promotionalbanner_table',1),(84,'2025_10_19_090041_add_location_columns_to_users_table',1),(85,'2025_10_19_091908_add_purchase_date_to_transactions_table',1),(86,'2025_10_19_092602_create_tax_table',1),(87,'2025_10_19_093148_create_coupons_table',1),(88,'2025_10_19_095148_add_themes_id_to_transactions_table',1),(89,'2025_10_19_095702_create_pixcel_settings_table',1),(90,'2025_10_19_100522_create_about_table',1),(91,'2025_10_19_100920_create_faqs_table',1),(92,'2025_10_19_101651_create_privacypolicy_table',1),(93,'2025_10_19_102228_create_refund_policy_table',1),(94,'2025_10_19_102739_create_terms_table',1),(95,'2025_10_23_000000_add_custom_domain_to_users_table',1),(96,'2025_10_23_003335_create_whatsapp_messages_log_table',1),(97,'2025_10_23_005059_create_customer_addresses_table',1),(98,'2025_10_23_005100_create_wishlists_table',1),(99,'2025_10_23_015418_create_whatsapp_logs_table',1),(100,'2025_10_23_033546_create_custom_status_table',1),(101,'2025_10_23_034256_add_missing_columns_to_settings_table',1),(102,'2025_10_23_041541_add_limits_to_pricing_plans_table',1),(103,'2025_10_23_043312_create_table_qr_scans_table',1),(104,'2025_10_23_043334_add_qr_tracking_to_tables_table',1),(105,'2025_10_23_103000_add_status_columns_to_orders_table',1),(106,'2025_10_23_104500_add_order_details_to_orders_table',1),(107,'2025_10_23_105500_add_notification_sound_to_settings_table',1),(108,'2025_10_23_110000_create_variants_table',1),(109,'2025_10_23_110500_create_item_images_table',1),(110,'2025_10_23_111000_create_global_extras_table',1),(111,'2025_10_23_111500_create_deliveryareas_table',1),(112,'2025_10_23_112000_create_table_book_table',1),(113,'2025_10_23_112500_add_languages_to_settings_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  KEY `notifications_customer_id_read_at_index` (`customer_id`,`read_at`),
  KEY `notifications_type_created_at_index` (`type`,`created_at`),
  CONSTRAINT `notifications_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` text COLLATE utf8mb4_unicode_ci,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variation_id` int DEFAULT NULL,
  `variation_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_price` double NOT NULL,
  `product_tax` double NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_details`
--

LOCK TABLES `order_details` WRITE;
/*!40000 ALTER TABLE `order_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `subtotal` decimal(8,2) NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_options` json DEFAULT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_item_id_foreign` (`item_id`),
  CONSTRAINT `order_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `restaurant_id` bigint unsigned DEFAULT NULL,
  `session_id` text COLLATE utf8mb4_unicode_ci,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_landmark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_landmark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_total` double NOT NULL DEFAULT '0',
  `offer_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offer_amount` double DEFAULT '0',
  `tax_amount` double NOT NULL DEFAULT '0',
  `shipping_area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_charge` double NOT NULL DEFAULT '0',
  `grand_total` double NOT NULL DEFAULT '0',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_type` tinyint(1) NOT NULL DEFAULT '1',
  `status` int NOT NULL COMMENT '1 = order placed , 2 = order confirmed/accepted , 3 = order cancelled/rejected - by admin , 4 = order cancelled/rejected - by user/customer , 5 = order delivered , ',
  `status_type` tinyint NOT NULL DEFAULT '1' COMMENT '1=pending, 2=processing, 3=completed, 4=cancelled',
  `order_type` tinyint NOT NULL DEFAULT '1' COMMENT '1=delivery, 2=dine-in, 3=takeaway',
  `payment_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `delivery_fee` decimal(8,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(8,2) NOT NULL DEFAULT '0.00',
  `delivery_type` enum('delivery','pickup') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'delivery',
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `estimated_delivery_time` timestamp NULL DEFAULT NULL,
  `rating` tinyint DEFAULT NULL,
  `review` text COLLATE utf8mb4_unicode_ci,
  `rated_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_customer_id_foreign` (`customer_id`),
  KEY `orders_restaurant_id_foreign` (`restaurant_id`),
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` text COLLATE utf8mb4_unicode_ci,
  `credentials` json DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `position` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,1,'Cash on Delivery','',NULL,'active',1,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(2,2,'Stripe','',NULL,'inactive',2,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(3,3,'Razorpay','',NULL,'inactive',3,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(4,4,'PayPal','',NULL,'inactive',4,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(5,5,'Mollie','',NULL,'inactive',5,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(6,6,'Flutterwave','',NULL,'inactive',6,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(7,7,'Paystack','',NULL,'inactive',7,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(8,8,'Mercadopago','',NULL,'inactive',8,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(9,9,'Paytab','',NULL,'inactive',9,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(10,10,'MyFatoorah','',NULL,'inactive',10,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(11,11,'ToyyibPay','',NULL,'inactive',11,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(12,12,'PhonePe','',NULL,'inactive',12,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(13,13,'Khalti','',NULL,'inactive',13,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(14,14,'Xendit','',NULL,'inactive',14,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(15,15,'SadadPay','',NULL,'inactive',15,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(16,16,'CinetPay','',NULL,'active',1,'2025-10-24 16:20:35','2025-10-24 16:20:35');
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `payment_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `environment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_key` text COLLATE utf8mb4_unicode_ci,
  `secret_key` text COLLATE utf8mb4_unicode_ci,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_description` text COLLATE utf8mb4_unicode_ci,
  `account_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_ifsc_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `encryption_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_url_by_region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` int NOT NULL DEFAULT '1',
  `is_activate` int NOT NULL DEFAULT '1',
  `reorder_id` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,'Cash On Delivery','1',NULL,NULL,NULL,NULL,'cod.png',NULL,NULL,NULL,NULL,NULL,NULL,1,1,2,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(2,1,'RazorPay','2','sandbox',NULL,NULL,'INR','razorpay.png',NULL,NULL,NULL,NULL,NULL,NULL,2,1,3,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(3,1,'Stripe','3','sandbox',NULL,NULL,'USD','stripe.png',NULL,NULL,NULL,NULL,NULL,NULL,2,1,4,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(4,1,'Bank Transfer','6',NULL,NULL,NULL,NULL,'bank.png','Please transfer money to our bank account',NULL,NULL,NULL,NULL,NULL,2,1,5,'2025-10-24 16:20:35','2025-10-24 16:20:35'),(5,1,'CinetPay','16','sandbox','','','XOF','cinetpay.png',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,'2025-10-24 16:20:36','2025-10-24 16:20:36');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paypal_disputes`
--

DROP TABLE IF EXISTS `paypal_disputes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paypal_disputes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dispute_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` bigint unsigned DEFAULT NULL,
  `status` enum('open','waiting_for_buyer_response','waiting_for_seller_response','under_paypal_review','resolved','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` enum('merchandise_or_service_not_received','merchandise_or_service_not_as_described','unauthorized','credit_not_processed','cancelled_recurring_billing','problem_with_remittance','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `dispute_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `dispute_time` timestamp NOT NULL,
  `respond_by_date` timestamp NULL DEFAULT NULL,
  `messages` json DEFAULT NULL,
  `evidence_documents` json DEFAULT NULL,
  `outcome` enum('resolved_buyer_favour','resolved_seller_favour','resolved_with_payout') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paypal_disputes_dispute_id_unique` (`dispute_id`),
  KEY `paypal_disputes_dispute_id_index` (`dispute_id`),
  KEY `paypal_disputes_status_respond_by_date_index` (`status`,`respond_by_date`),
  KEY `paypal_disputes_transaction_id_index` (`transaction_id`),
  CONSTRAINT `paypal_disputes_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `paypal_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_disputes`
--

LOCK TABLES `paypal_disputes` WRITE;
/*!40000 ALTER TABLE `paypal_disputes` DISABLE KEYS */;
/*!40000 ALTER TABLE `paypal_disputes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paypal_plans`
--

DROP TABLE IF EXISTS `paypal_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paypal_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paypal_plan_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `billing_cycle` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `billing_frequency` int NOT NULL DEFAULT '1',
  `return_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancel_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `setup_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_fail_attempts` int NOT NULL DEFAULT '3',
  `auto_bill_amount` tinyint(1) NOT NULL DEFAULT '1',
  `plan_details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paypal_plans_paypal_plan_id_unique` (`paypal_plan_id`),
  KEY `paypal_plans_paypal_plan_id_index` (`paypal_plan_id`),
  KEY `paypal_plans_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_plans`
--

LOCK TABLES `paypal_plans` WRITE;
/*!40000 ALTER TABLE `paypal_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `paypal_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paypal_settings`
--

DROP TABLE IF EXISTS `paypal_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paypal_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned DEFAULT NULL,
  `client_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_secret` text COLLATE utf8mb4_unicode_ci,
  `mode` enum('sandbox','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `webhook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `express_checkout_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `credit_card_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `subscriptions_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `return_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancel_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_fee_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `transaction_fee_fixed` decimal(10,2) NOT NULL DEFAULT '0.00',
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `sync_status` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paypal_settings_restaurant_id_index` (`restaurant_id`),
  KEY `paypal_settings_enabled_index` (`enabled`),
  CONSTRAINT `paypal_settings_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_settings`
--

LOCK TABLES `paypal_settings` WRITE;
/*!40000 ALTER TABLE `paypal_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `paypal_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paypal_subscriptions`
--

DROP TABLE IF EXISTS `paypal_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paypal_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `restaurant_id` bigint unsigned DEFAULT NULL,
  `paypal_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','cancelled','suspended','expired','pending','approval_pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `billing_cycle` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `billing_frequency` int NOT NULL DEFAULT '1',
  `start_date` timestamp NULL DEFAULT NULL,
  `next_billing_date` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `failure_count` int NOT NULL DEFAULT '0',
  `subscription_details` json DEFAULT NULL,
  `webhook_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paypal_subscriptions_paypal_subscription_id_unique` (`paypal_subscription_id`),
  KEY `paypal_subscriptions_user_id_status_index` (`user_id`,`status`),
  KEY `paypal_subscriptions_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `paypal_subscriptions_paypal_subscription_id_index` (`paypal_subscription_id`),
  KEY `paypal_subscriptions_status_next_billing_date_index` (`status`,`next_billing_date`),
  CONSTRAINT `paypal_subscriptions_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `paypal_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_subscriptions`
--

LOCK TABLES `paypal_subscriptions` WRITE;
/*!40000 ALTER TABLE `paypal_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `paypal_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paypal_transactions`
--

DROP TABLE IF EXISTS `paypal_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paypal_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `paypal_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paypal_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('express_checkout','direct_credit_card','subscription','billing_agreement','refund') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'express_checkout',
  `status` enum('created','approved','completed','failed','cancelled','denied','pending','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'created',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `fee_amount` decimal(10,2) DEFAULT NULL,
  `net_amount` decimal(10,2) DEFAULT NULL,
  `refund_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_details` json DEFAULT NULL,
  `webhook_data` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paypal_transactions_paypal_payment_id_unique` (`paypal_payment_id`),
  KEY `paypal_transactions_order_id_status_index` (`order_id`,`status`),
  KEY `paypal_transactions_paypal_payment_id_index` (`paypal_payment_id`),
  KEY `paypal_transactions_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `paypal_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_transactions`
--

LOCK TABLES `paypal_transactions` WRITE;
/*!40000 ALTER TABLE `paypal_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `paypal_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paypal_webhooks`
--

DROP TABLE IF EXISTS `paypal_webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paypal_webhooks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `webhook_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('received','processed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `event_data` json NOT NULL,
  `event_time` timestamp NOT NULL,
  `processing_result` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `retry_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paypal_webhooks_event_id_unique` (`event_id`),
  KEY `paypal_webhooks_event_id_index` (`event_id`),
  KEY `paypal_webhooks_event_type_status_index` (`event_type`,`status`),
  KEY `paypal_webhooks_status_created_at_index` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_webhooks`
--

LOCK TABLES `paypal_webhooks` WRITE;
/*!40000 ALTER TABLE `paypal_webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `paypal_webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pixcel_settings`
--

DROP TABLE IF EXISTS `pixcel_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pixcel_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `facebook_pixcel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_pixcel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_pixcel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `googletag_pixcel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pixcel_settings_vendor_id_unique` (`vendor_id`),
  KEY `pixcel_settings_vendor_id_index` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pixcel_settings`
--

LOCK TABLES `pixcel_settings` WRITE;
/*!40000 ALTER TABLE `pixcel_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `pixcel_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_carts`
--

DROP TABLE IF EXISTS `pos_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `terminal_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `session_id` bigint unsigned DEFAULT NULL,
  `menu_item_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `modifiers` json DEFAULT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_carts_user_id_foreign` (`user_id`),
  KEY `pos_carts_terminal_id_user_id_index` (`terminal_id`,`user_id`),
  KEY `pos_carts_session_id_index` (`session_id`),
  KEY `pos_carts_menu_item_id_index` (`menu_item_id`),
  CONSTRAINT `pos_carts_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_carts_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `pos_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_carts_terminal_id_foreign` FOREIGN KEY (`terminal_id`) REFERENCES `pos_terminals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_carts`
--

LOCK TABLES `pos_carts` WRITE;
/*!40000 ALTER TABLE `pos_carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `pos_carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_sessions`
--

DROP TABLE IF EXISTS `pos_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `terminal_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('active','closed','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `opening_cash` decimal(10,2) NOT NULL DEFAULT '0.00',
  `closing_cash` decimal(10,2) DEFAULT NULL,
  `expected_cash` decimal(10,2) DEFAULT NULL,
  `cash_difference` decimal(10,2) DEFAULT NULL,
  `total_transactions` int NOT NULL DEFAULT '0',
  `total_sales` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_summary` json DEFAULT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_sessions_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `pos_sessions_terminal_id_status_index` (`terminal_id`,`status`),
  KEY `pos_sessions_user_id_status_index` (`user_id`,`status`),
  KEY `pos_sessions_started_at_index` (`started_at`),
  CONSTRAINT `pos_sessions_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_sessions_terminal_id_foreign` FOREIGN KEY (`terminal_id`) REFERENCES `pos_terminals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_sessions`
--

LOCK TABLES `pos_sessions` WRITE;
/*!40000 ALTER TABLE `pos_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `pos_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_terminals`
--

DROP TABLE IF EXISTS `pos_terminals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_terminals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_user_id` bigint unsigned DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `hardware_info` json DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mac_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_terminals_code_unique` (`code`),
  KEY `pos_terminals_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `pos_terminals_current_user_id_index` (`current_user_id`),
  KEY `pos_terminals_last_activity_index` (`last_activity`),
  CONSTRAINT `pos_terminals_current_user_id_foreign` FOREIGN KEY (`current_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pos_terminals_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_terminals`
--

LOCK TABLES `pos_terminals` WRITE;
/*!40000 ALTER TABLE `pos_terminals` DISABLE KEYS */;
/*!40000 ALTER TABLE `pos_terminals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pricing_plans`
--

DROP TABLE IF EXISTS `pricing_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pricing_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `features` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `products_limit` int NOT NULL DEFAULT '-1' COMMENT '-1 = illimité',
  `order_limit` int NOT NULL DEFAULT '-1' COMMENT '-1 = illimité',
  `categories_limit` int NOT NULL DEFAULT '-1' COMMENT '-1 = illimité',
  `custom_domain` tinyint(1) NOT NULL DEFAULT '0',
  `analytics` tinyint(1) NOT NULL DEFAULT '1',
  `whatsapp_integration` tinyint(1) NOT NULL DEFAULT '1',
  `staff_limit` int NOT NULL DEFAULT '-1' COMMENT '-1 = illimité',
  `duration` int NOT NULL DEFAULT '30',
  `service_limit` int NOT NULL DEFAULT '-1',
  `appoinment_limit` int NOT NULL DEFAULT '-1',
  `type` enum('monthly','yearly','lifetime') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pricing_plans`
--

LOCK TABLES `pricing_plans` WRITE;
/*!40000 ALTER TABLE `pricing_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `pricing_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `privacypolicy`
--

DROP TABLE IF EXISTS `privacypolicy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `privacypolicy` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `privacypolicy_content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `privacypolicy_vendor_id_unique` (`vendor_id`),
  KEY `privacypolicy_vendor_id_index` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privacypolicy`
--

LOCK TABLES `privacypolicy` WRITE;
/*!40000 ALTER TABLE `privacypolicy` DISABLE KEYS */;
/*!40000 ALTER TABLE `privacypolicy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `category_id` int NOT NULL,
  `sub_category_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1="yes",2="no"',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1="yes",2="no"',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocodes`
--

DROP TABLE IF EXISTS `promocodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promocodes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `offer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `offer_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `offer_type` int NOT NULL COMMENT '1=fixed,2=percentage',
  `offer_amount` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_amount` int NOT NULL,
  `usage_type` int NOT NULL COMMENT '1=one time,2=multiple times',
  `usage_limit` int NOT NULL,
  `start_date` date NOT NULL,
  `exp_date` date NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_available` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '1=yes,2=no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocodes`
--

LOCK TABLES `promocodes` WRITE;
/*!40000 ALTER TABLE `promocodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `promocodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotionalbanner`
--

DROP TABLE IF EXISTS `promotionalbanner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotionalbanner` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reorder_id` int DEFAULT NULL,
  `vendor_id` int NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotionalbanner_vendor_id_index` (`vendor_id`),
  KEY `promotionalbanner_reorder_id_index` (`reorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotionalbanner`
--

LOCK TABLES `promotionalbanner` WRITE;
/*!40000 ALTER TABLE `promotionalbanner` DISABLE KEYS */;
/*!40000 ALTER TABLE `promotionalbanner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `push_subscriptions`
--

DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auth_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `p256dh_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_endpoint_unique` (`user_id`,`endpoint`),
  CONSTRAINT `push_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `push_subscriptions`
--

LOCK TABLES `push_subscriptions` WRITE;
/*!40000 ALTER TABLE `push_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refund_policy`
--

DROP TABLE IF EXISTS `refund_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refund_policy` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `refund_policy_content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `refund_policy_vendor_id_unique` (`vendor_id`),
  KEY `refund_policy_vendor_id_index` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refund_policy`
--

LOCK TABLES `refund_policy` WRITE;
/*!40000 ALTER TABLE `refund_policy` DISABLE KEYS */;
/*!40000 ALTER TABLE `refund_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_wallets`
--

DROP TABLE IF EXISTS `restaurant_wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restaurant_wallets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pending_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_earnings` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_withdrawn` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_wallets_vendor_id_unique` (`vendor_id`),
  KEY `restaurant_wallets_vendor_id_balance_index` (`vendor_id`,`balance`),
  CONSTRAINT `restaurant_wallets_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_wallets`
--

LOCK TABLES `restaurant_wallets` WRITE;
/*!40000 ALTER TABLE `restaurant_wallets` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_wallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurants`
--

DROP TABLE IF EXISTS `restaurants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restaurants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `restaurant_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restaurant_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restaurant_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `delivery_fee` decimal(8,2) NOT NULL DEFAULT '0.00',
  `minimum_order` decimal(8,2) NOT NULL DEFAULT '0.00',
  `delivery_time` int NOT NULL DEFAULT '30',
  `opening_time` time NOT NULL DEFAULT '09:00:00',
  `closing_time` time NOT NULL DEFAULT '22:00:00',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `rating` decimal(3,1) NOT NULL DEFAULT '0.0',
  `total_reviews` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurants_restaurant_slug_unique` (`restaurant_slug`),
  KEY `restaurants_user_id_foreign` (`user_id`),
  CONSTRAINT `restaurants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurants`
--

LOCK TABLES `restaurants` WRITE;
/*!40000 ALTER TABLE `restaurants` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_images`
--

DROP TABLE IF EXISTS `service_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_images`
--

LOCK TABLES `service_images` WRITE;
/*!40000 ALTER TABLE `service_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1="yes",2="no"',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1="yes",2="no"',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint NOT NULL DEFAULT '1',
  `currency` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XOF',
  `currency_position` enum('left','right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'left',
  `currency_space` tinyint(1) NOT NULL DEFAULT '1',
  `decimal_separator` int NOT NULL DEFAULT '1',
  `currency_formate` int NOT NULL DEFAULT '2',
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT '0',
  `checkout_login_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_checkout_login_required` tinyint(1) NOT NULL DEFAULT '0',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1,2',
  `item_message` text COLLATE utf8mb4_unicode_ci,
  `interval_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interval_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `address` text COLLATE utf8mb4_unicode_ci,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright` text COLLATE utf8mb4_unicode_ci,
  `website_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RestroSaaS',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RestroSaaS - Restaurant Management System',
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `og_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firebase` longtext COLLATE utf8mb4_unicode_ci,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `languages` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'fr|en',
  `template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `template_type` int NOT NULL DEFAULT '1',
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#181D31',
  `secondary_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6096B4',
  `landing_website_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RestroSaaS',
  `custom_domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_size` int NOT NULL DEFAULT '5',
  `time_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'H:i',
  `date_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y-m-d',
  `order_prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_number_start` int NOT NULL DEFAULT '1001',
  `whatsapp_message` longtext COLLATE utf8mb4_unicode_ci,
  `telegram_message` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `whatsapp_chat_on_off` int NOT NULL DEFAULT '2' COMMENT '1 = Yes, 2 = No',
  `tracking_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tawk_on_off` int NOT NULL DEFAULT '2' COMMENT '1 = Yes, 2 = No',
  `facebook_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default-cover.png',
  `notification_sound` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'notification.mp3',
  PRIMARY KEY (`id`),
  KEY `settings_vendor_id_index` (`vendor_id`),
  KEY `settings_custom_domain_index` (`custom_domain`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,1,'XOF','left',1,1,2,0,0,0,NULL,NULL,'1,2',NULL,NULL,NULL,'UTC',NULL,NULL,NULL,NULL,NULL,'E-menu','RestroSaaS - Restaurant Management System','Complete restaurant management solution with addons',NULL,NULL,'en','fr|en','default',1,'#181D31','#6096B4','E-menu - Digital Menu System',NULL,5,'H:i','Y-m-d',NULL,1001,NULL,NULL,'2025-10-24 16:20:35','2025-10-24 16:20:35',2,NULL,2,NULL,NULL,NULL,NULL,'default-cover.png','notification.mp3');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_access_tokens`
--

DROP TABLE IF EXISTS `social_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `social_account_id` bigint unsigned NOT NULL,
  `token_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bearer',
  `access_token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_token` text COLLATE utf8mb4_unicode_ci,
  `expires_in` int DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `scopes` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `usage_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_access_tokens_social_account_id_is_active_index` (`social_account_id`,`is_active`),
  KEY `social_access_tokens_expires_at_index` (`expires_at`),
  KEY `social_access_tokens_last_used_at_index` (`last_used_at`),
  CONSTRAINT `social_access_tokens_social_account_id_foreign` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_access_tokens`
--

LOCK TABLES `social_access_tokens` WRITE;
/*!40000 ALTER TABLE `social_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_accounts`
--

DROP TABLE IF EXISTS `social_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_token` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_refresh_token` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_expires_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_data` json DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `social_accounts_provider_provider_id_unique` (`provider`,`provider_id`),
  KEY `social_accounts_user_id_provider_index` (`user_id`,`provider`),
  KEY `social_accounts_provider_is_active_index` (`provider`,`is_active`),
  KEY `social_accounts_last_login_at_index` (`last_login_at`),
  CONSTRAINT `social_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_accounts`
--

LOCK TABLES `social_accounts` WRITE;
/*!40000 ALTER TABLE `social_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_invitations`
--

DROP TABLE IF EXISTS `social_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invited_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invited_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('sent','accepted','declined','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` timestamp NULL DEFAULT NULL,
  `invited_user_id` bigint unsigned DEFAULT NULL,
  `reward_given` tinyint(1) NOT NULL DEFAULT '0',
  `reward_amount` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_invitations_invited_user_id_foreign` (`invited_user_id`),
  KEY `social_invitations_user_id_provider_index` (`user_id`,`provider`),
  KEY `social_invitations_status_sent_at_index` (`status`,`sent_at`),
  KEY `social_invitations_invited_email_index` (`invited_email`),
  CONSTRAINT `social_invitations_invited_user_id_foreign` FOREIGN KEY (`invited_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `social_invitations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_invitations`
--

LOCK TABLES `social_invitations` WRITE;
/*!40000 ALTER TABLE `social_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_links`
--

DROP TABLE IF EXISTS `social_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `icon` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_links`
--

LOCK TABLES `social_links` WRITE;
/*!40000 ALTER TABLE `social_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_login_attempts`
--

DROP TABLE IF EXISTS `social_login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_login_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('success','failed','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'failed',
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned DEFAULT NULL,
  `attempt_data` json DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_login_attempts_user_id_foreign` (`user_id`),
  KEY `social_login_attempts_provider_status_index` (`provider`,`status`),
  KEY `social_login_attempts_email_attempted_at_index` (`email`,`attempted_at`),
  KEY `social_login_attempts_ip_address_attempted_at_index` (`ip_address`,`attempted_at`),
  CONSTRAINT `social_login_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_login_attempts`
--

LOCK TABLES `social_login_attempts` WRITE;
/*!40000 ALTER TABLE `social_login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_profile_syncs`
--

DROP TABLE IF EXISTS `social_profile_syncs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_profile_syncs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `social_account_id` bigint unsigned NOT NULL,
  `synced_fields` json NOT NULL,
  `previous_data` json DEFAULT NULL,
  `new_data` json DEFAULT NULL,
  `sync_type` enum('manual','automatic','login') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('success','failed','partial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_profile_syncs_social_account_id_sync_type_index` (`social_account_id`,`sync_type`),
  KEY `social_profile_syncs_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `social_profile_syncs_social_account_id_foreign` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_profile_syncs`
--

LOCK TABLES `social_profile_syncs` WRITE;
/*!40000 ALTER TABLE `social_profile_syncs` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_profile_syncs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_settings`
--

DROP TABLE IF EXISTS `social_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned DEFAULT NULL,
  `google_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `google_client_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_client_secret` text COLLATE utf8mb4_unicode_ci,
  `google_scopes` json DEFAULT NULL,
  `facebook_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `facebook_app_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_app_secret` text COLLATE utf8mb4_unicode_ci,
  `facebook_permissions` json DEFAULT NULL,
  `twitter_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `twitter_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_api_secret` text COLLATE utf8mb4_unicode_ci,
  `auto_sync_profiles` tinyint(1) NOT NULL DEFAULT '1',
  `allow_registration` tinyint(1) NOT NULL DEFAULT '1',
  `link_existing_accounts` tinyint(1) NOT NULL DEFAULT '1',
  `default_user_roles` json DEFAULT NULL,
  `success_redirect_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_redirect_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_settings_restaurant_id_index` (`restaurant_id`),
  CONSTRAINT `social_settings_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_settings`
--

LOCK TABLES `social_settings` WRITE;
/*!40000 ALTER TABLE `social_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_shares`
--

DROP TABLE IF EXISTS `social_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_shares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_id` bigint unsigned NOT NULL,
  `shared_content` text COLLATE utf8mb4_unicode_ci,
  `share_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('success','failed','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider_post_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_data` json DEFAULT NULL,
  `likes_count` int NOT NULL DEFAULT '0',
  `shares_count` int NOT NULL DEFAULT '0',
  `comments_count` int NOT NULL DEFAULT '0',
  `last_stats_update` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_shares_user_id_provider_index` (`user_id`,`provider`),
  KEY `social_shares_content_type_content_id_index` (`content_type`,`content_id`),
  KEY `social_shares_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `social_shares_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_shares`
--

LOCK TABLES `social_shares` WRITE;
/*!40000 ALTER TABLE `social_shares` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_webhooks`
--

DROP TABLE IF EXISTS `social_webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_webhooks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `webhook_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json NOT NULL,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('received','processing','processed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `processing_result` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `retry_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_webhooks_provider_event_type_index` (`provider`,`event_type`),
  KEY `social_webhooks_status_created_at_index` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_webhooks`
--

LOCK TABLES `social_webhooks` WRITE;
/*!40000 ALTER TABLE `social_webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_category`
--

DROP TABLE IF EXISTS `store_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `store_category` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reorder_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_available` int NOT NULL DEFAULT '1' COMMENT '1=Yes,2=No',
  `is_deleted` int NOT NULL DEFAULT '2' COMMENT '1=Yes,2=No',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_category`
--

LOCK TABLES `store_category` WRITE;
/*!40000 ALTER TABLE `store_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `store_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscribers`
--

DROP TABLE IF EXISTS `subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscribers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscribers`
--

LOCK TABLES `subscribers` WRITE;
/*!40000 ALTER TABLE `subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `systemaddons`
--

DROP TABLE IF EXISTS `systemaddons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `systemaddons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unique_identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` int NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `systemaddons_unique_identifier_index` (`unique_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `systemaddons`
--

LOCK TABLES `systemaddons` WRITE;
/*!40000 ALTER TABLE `systemaddons` DISABLE KEYS */;
/*!40000 ALTER TABLE `systemaddons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_book`
--

DROP TABLE IF EXISTS `table_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `table_book` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_members` int NOT NULL DEFAULT '1',
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1=pending, 2=confirmed, 3=cancelled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_book_vendor_id_index` (`vendor_id`),
  KEY `table_book_status_index` (`status`),
  KEY `table_book_booking_date_index` (`booking_date`),
  CONSTRAINT `table_book_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table_book`
--

LOCK TABLES `table_book` WRITE;
/*!40000 ALTER TABLE `table_book` DISABLE KEYS */;
/*!40000 ALTER TABLE `table_book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_qr_scans`
--

DROP TABLE IF EXISTS `table_qr_scans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `table_qr_scans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `table_id` bigint unsigned NOT NULL,
  `restaurant_id` bigint unsigned NOT NULL,
  `scanned_at` timestamp NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `referrer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_qr_scans_table_id_index` (`table_id`),
  KEY `table_qr_scans_restaurant_id_index` (`restaurant_id`),
  KEY `table_qr_scans_scanned_at_index` (`scanned_at`),
  KEY `table_qr_scans_restaurant_id_scanned_at_index` (`restaurant_id`,`scanned_at`),
  CONSTRAINT `table_qr_scans_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `table_qr_scans_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table_qr_scans`
--

LOCK TABLES `table_qr_scans` WRITE;
/*!40000 ALTER TABLE `table_qr_scans` DISABLE KEYS */;
/*!40000 ALTER TABLE `table_qr_scans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_ratings`
--

DROP TABLE IF EXISTS `table_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `table_ratings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `table_id` bigint unsigned NOT NULL,
  `order_id` int unsigned DEFAULT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_ratings_order_id_foreign` (`order_id`),
  KEY `table_ratings_restaurant_id_created_at_index` (`restaurant_id`,`created_at`),
  KEY `table_ratings_table_id_created_at_index` (`table_id`,`created_at`),
  KEY `table_ratings_rating_index` (`rating`),
  CONSTRAINT `table_ratings_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `table_ratings_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `table_ratings_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table_ratings`
--

LOCK TABLES `table_ratings` WRITE;
/*!40000 ALTER TABLE `table_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `table_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint unsigned NOT NULL,
  `table_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` int NOT NULL DEFAULT '4',
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_code_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','maintenance','occupied','free') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `scan_count` int unsigned NOT NULL DEFAULT '0',
  `last_scanned_at` timestamp NULL DEFAULT NULL,
  `qr_color_fg` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#000000' COMMENT 'Couleur avant-plan QR code',
  `qr_color_bg` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#FFFFFF' COMMENT 'Couleur arrière-plan QR code',
  `qr_use_logo` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Utiliser logo restaurant dans QR',
  `qr_size` int unsigned NOT NULL DEFAULT '300' COMMENT 'Taille QR code en pixels',
  `last_accessed` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tables_restaurant_id_table_number_unique` (`restaurant_id`,`table_number`),
  UNIQUE KEY `tables_table_code_unique` (`table_code`),
  KEY `tables_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `tables_restaurant_id_table_number_index` (`restaurant_id`,`table_number`),
  CONSTRAINT `tables_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax`
--

DROP TABLE IF EXISTS `tax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tax` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` decimal(8,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `reorder_id` int NOT NULL DEFAULT '0',
  `is_available` tinyint NOT NULL DEFAULT '1' COMMENT '1=Yes, 2=No',
  `is_deleted` tinyint NOT NULL DEFAULT '2' COMMENT '1=Yes, 2=No',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tax_vendor_id_is_deleted_is_available_index` (`vendor_id`,`is_deleted`,`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax`
--

LOCK TABLES `tax` WRITE;
/*!40000 ALTER TABLE `tax` DISABLE KEYS */;
/*!40000 ALTER TABLE `tax` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terms`
--

DROP TABLE IF EXISTS `terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `terms_content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `terms_vendor_id_unique` (`vendor_id`),
  KEY `terms_vendor_id_index` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terms`
--

LOCK TABLES `terms` WRITE;
/*!40000 ALTER TABLE `terms` DISABLE KEYS */;
/*!40000 ALTER TABLE `terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reorder_id` int DEFAULT NULL,
  `vendor_id` int NOT NULL,
  `star` int NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timings`
--

DROP TABLE IF EXISTS `timings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `day` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `open_time` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `break_start` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `break_end` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `close_time` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_always_close` tinyint NOT NULL COMMENT '1 For Yes, 2 For No',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timings`
--

LOCK TABLES `timings` WRITE;
/*!40000 ALTER TABLE `timings` DISABLE KEYS */;
/*!40000 ALTER TABLE `timings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `top_deals`
--

DROP TABLE IF EXISTS `top_deals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `top_deals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `top_deals`
--

LOCK TABLES `top_deals` WRITE;
/*!40000 ALTER TABLE `top_deals` DISABLE KEYS */;
/*!40000 ALTER TABLE `top_deals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `plan_id` bigint unsigned DEFAULT NULL,
  `themes_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `purchase_date` date DEFAULT NULL,
  `status` enum('1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2',
  `expire_date` date DEFAULT NULL,
  `service_limit` int NOT NULL DEFAULT '-1',
  `appoinment_limit` int NOT NULL DEFAULT '-1',
  `response` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_vendor_id_foreign` (`vendor_id`),
  KEY `transactions_plan_id_foreign` (`plan_id`),
  KEY `transactions_themes_id_index` (`themes_id`),
  CONSTRAINT `transactions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `pricing_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain_verified` tinyint(1) NOT NULL DEFAULT '0',
  `domain_verified_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` int NOT NULL DEFAULT '1',
  `is_deleted` int NOT NULL DEFAULT '2',
  `role_id` int DEFAULT NULL,
  `store_id` int DEFAULT NULL,
  `type` int NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `token` longtext COLLATE utf8mb4_unicode_ci,
  `city_id` int DEFAULT NULL,
  `area_id` int DEFAULT NULL,
  `vendor_id` int DEFAULT NULL,
  `plan_id` bigint unsigned DEFAULT NULL,
  `purchase_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allow_without_subscription` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` tinyint NOT NULL DEFAULT '2' COMMENT '1=Yes,2=No',
  `available_on_landing` int NOT NULL DEFAULT '2' COMMENT '1 = Yes, 2 = No',
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` int DEFAULT NULL,
  `free_plan` int NOT NULL DEFAULT '0',
  `is_delivery` tinyint DEFAULT NULL COMMENT '1=Yes,2=No',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_type` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_city_id_area_id_store_id_index` (`city_id`,`area_id`,`store_id`),
  KEY `users_type_index` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Default Restaurant','default-restaurant',NULL,0,NULL,'admin@restaurant.com','+225 07 00 00 00 00',NULL,1,2,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,2,2,NULL,NULL,0,NULL,NULL,'$2y$10$jvVOPMIO9HEq2KgbxJqMiu5R0NrZozbJFKwi5ylsgdFL6flvIb1qO',NULL,NULL,'manual',NULL,NULL,'2025-10-24 16:20:35','2025-10-24 16:20:35');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variants`
--

DROP TABLE IF EXISTS `variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `original_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty` int NOT NULL DEFAULT '0',
  `min_order` int NOT NULL DEFAULT '1',
  `max_order` int NOT NULL DEFAULT '0',
  `low_qty` int NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `stock_management` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'stck_management in model',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `variants_item_id_index` (`item_id`),
  CONSTRAINT `variants_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variants`
--

LOCK TABLES `variants` WRITE;
/*!40000 ALTER TABLE `variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_transactions`
--

DROP TABLE IF EXISTS `wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallet_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('credit','debit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_transactions_transaction_id_unique` (`transaction_id`),
  KEY `wallet_transactions_vendor_id_type_status_index` (`vendor_id`,`type`,`status`),
  CONSTRAINT `wallet_transactions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_transactions`
--

LOCK TABLES `wallet_transactions` WRITE;
/*!40000 ALTER TABLE `wallet_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_logs`
--

DROP TABLE IF EXISTS `whatsapp_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `to` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Numéro destinataire',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contenu du message',
  `status` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Statut de l''envoi',
  `success` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Succès ou échec',
  `message_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID WhatsApp du message',
  `response` json DEFAULT NULL COMMENT 'Réponse de l''API WhatsApp',
  `context` json DEFAULT NULL COMMENT 'Contexte additionnel (order_id, etc.)',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT 'Date et heure d''envoi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_logs_created_at_index` (`created_at`),
  KEY `whatsapp_logs_success_created_at_index` (`success`,`created_at`),
  KEY `whatsapp_logs_to_index` (`to`),
  KEY `whatsapp_logs_success_index` (`success`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_logs`
--

LOCK TABLES `whatsapp_logs` WRITE;
/*!40000 ALTER TABLE `whatsapp_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_messages_log`
--

DROP TABLE IF EXISTS `whatsapp_messages_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_messages_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `restaurant_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','sent','delivered','read','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error` text COLLATE utf8mb4_unicode_ci,
  `error_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `retry_count` int NOT NULL DEFAULT '0',
  `last_retry_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_messages_log_order_id_index` (`order_id`),
  KEY `whatsapp_messages_log_restaurant_id_index` (`restaurant_id`),
  KEY `whatsapp_messages_log_customer_id_index` (`customer_id`),
  KEY `whatsapp_messages_log_message_id_index` (`message_id`),
  KEY `whatsapp_messages_log_status_index` (`status`),
  KEY `whatsapp_messages_log_message_type_index` (`message_type`),
  KEY `whatsapp_messages_log_phone_created_at_index` (`phone`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_messages_log`
--

LOCK TABLES `whatsapp_messages_log` WRITE;
/*!40000 ALTER TABLE `whatsapp_messages_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_messages_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_id_item_id_unique` (`user_id`,`item_id`),
  KEY `wishlists_user_id_index` (`user_id`),
  KEY `wishlists_item_id_index` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawal_methods`
--

DROP TABLE IF EXISTS `withdrawal_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdrawal_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `additional_info` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawal_methods_vendor_id_type_is_active_index` (`vendor_id`,`type`,`is_active`),
  CONSTRAINT `withdrawal_methods_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawal_methods`
--

LOCK TABLES `withdrawal_methods` WRITE;
/*!40000 ALTER TABLE `withdrawal_methods` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdrawal_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawal_requests`
--

DROP TABLE IF EXISTS `withdrawal_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdrawal_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(15,2) NOT NULL,
  `withdrawal_method_id` bigint unsigned NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `requested_at` timestamp NOT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `withdrawal_requests_request_id_unique` (`request_id`),
  KEY `withdrawal_requests_withdrawal_method_id_foreign` (`withdrawal_method_id`),
  KEY `withdrawal_requests_vendor_id_status_index` (`vendor_id`,`status`),
  CONSTRAINT `withdrawal_requests_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `withdrawal_requests_withdrawal_method_id_foreign` FOREIGN KEY (`withdrawal_method_id`) REFERENCES `withdrawal_methods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawal_requests`
--

LOCK TABLES `withdrawal_requests` WRITE;
/*!40000 ALTER TABLE `withdrawal_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdrawal_requests` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-24 20:28:38
