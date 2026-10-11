-- MySQL dump 10.13  Distrib 26.7.0, for Win64 (x86_64)
--
-- Host: localhost    Database: salud_usm
-- ------------------------------------------------------
-- Server version	26.7.0

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'fcbab60c-c12b-11f1-a250-0a002700000b:1-323';

--
-- Table structure for table `atencion`
--

DROP TABLE IF EXISTS `atencion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atencion` (
  `id_atencion` int NOT NULL AUTO_INCREMENT,
  `id_cita` int NOT NULL,
  `motivo_consulta` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones_clinicas` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_atencion`),
  UNIQUE KEY `uq_atencion_cita` (`id_cita`),
  CONSTRAINT `fk_atencion_cita` FOREIGN KEY (`id_cita`) REFERENCES `cita` (`id_cita`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atencion`
--

LOCK TABLES `atencion` WRITE;
/*!40000 ALTER TABLE `atencion` DISABLE KEYS */;
INSERT INTO `atencion` VALUES (1,1,'Control cardiologico','Paciente evaluado durante la consulta.'),(2,2,'Consulta medicina general','Paciente evaluado durante la consulta.'),(3,3,'Control cardiologico','Paciente evaluado durante la consulta.'),(4,4,'Consulta medicina general','Paciente evaluado durante la consulta.'),(5,5,'Control cardiologico','Paciente evaluado durante la consulta.'),(6,6,'Consulta medicina general','Paciente evaluado durante la consulta.'),(7,7,'Control cardiologico','Paciente evaluado durante la consulta.'),(8,15,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(9,16,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(10,17,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(11,18,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(12,19,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(13,20,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(14,21,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(15,22,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(16,28,'Dolor traumatologico','Paciente evaluado durante la consulta.'),(17,29,'Consulta medicina general','Paciente evaluado durante la consulta.'),(18,30,'Dolor traumatologico','Paciente evaluado durante la consulta.'),(19,31,'Consulta medicina general','Paciente evaluado durante la consulta.'),(20,32,'Dolor traumatologico','Paciente evaluado durante la consulta.'),(21,33,'Consulta medicina general','Paciente evaluado durante la consulta.'),(22,34,'Dolor traumatologico','Paciente evaluado durante la consulta.'),(23,35,'Consulta medicina general','Paciente evaluado durante la consulta.'),(24,40,'Consulta medicina general','Paciente evaluado durante la consulta.'),(25,41,'Consulta medicina general','Paciente evaluado durante la consulta.'),(26,42,'Consulta medicina general','Paciente evaluado durante la consulta.'),(27,43,'Consulta medicina general','Paciente evaluado durante la consulta.'),(28,44,'Consulta medicina general','Paciente evaluado durante la consulta.'),(29,45,'Consulta medicina general','Paciente evaluado durante la consulta.'),(30,46,'Consulta medicina general','Paciente evaluado durante la consulta.'),(31,47,'Consulta medicina general','Paciente evaluado durante la consulta.'),(32,51,'Consulta dermatologica','Paciente evaluado durante la consulta.'),(33,52,'Consulta dermatologica','Paciente evaluado durante la consulta.'),(34,53,'Consulta medicina general','Paciente evaluado durante la consulta.'),(35,54,'Consulta dermatologica','Paciente evaluado durante la consulta.'),(36,55,'Consulta medicina general','Paciente evaluado durante la consulta.'),(37,56,'Consulta dermatologica','Paciente evaluado durante la consulta.'),(38,61,'Consulta neurologica','Paciente evaluado durante la consulta.'),(39,62,'Consulta neurologica','Paciente evaluado durante la consulta.'),(40,63,'Consulta neurologica','Paciente evaluado durante la consulta.'),(41,64,'Consulta neurologica','Paciente evaluado durante la consulta.'),(42,65,'Consulta neurologica','Paciente evaluado durante la consulta.'),(43,70,'Consulta oftalmologica','Paciente evaluado durante la consulta.'),(44,71,'Consulta oftalmologica','Paciente evaluado durante la consulta.'),(45,72,'Consulta oftalmologica','Paciente evaluado durante la consulta.'),(46,73,'Consulta oftalmologica','Paciente evaluado durante la consulta.'),(47,74,'Consulta oftalmologica','Paciente evaluado durante la consulta.'),(48,78,'Consulta gastroenterologica','Paciente evaluado durante la consulta.'),(49,79,'Consulta medicina general','Paciente evaluado durante la consulta.'),(50,80,'Consulta gastroenterologica','Paciente evaluado durante la consulta.'),(51,81,'Consulta medicina general','Paciente evaluado durante la consulta.'),(52,85,'Control cardiologico','Paciente evaluado durante la consulta.'),(53,86,'Control cardiologico','Paciente evaluado durante la consulta.'),(54,87,'Control cardiologico','Paciente evaluado durante la consulta.'),(55,88,'Control cardiologico','Paciente evaluado durante la consulta.'),(56,92,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(57,93,'Consulta medicina general','Paciente evaluado durante la consulta.'),(58,94,'Consulta pediatrica','Paciente evaluado durante la consulta.'),(59,95,'Consulta medicina general','Paciente evaluado durante la consulta.'),(60,98,'Dolor traumatologico','Paciente evaluado durante la consulta.'),(61,99,'Dolor traumatologico','Paciente evaluado durante la consulta.'),(62,100,'Dolor traumatologico','Paciente evaluado durante la consulta.'),(63,104,'Consulta dermatologica','Paciente evaluado durante la consulta.'),(64,105,'Consulta dermatologica','Paciente evaluado durante la consulta.'),(65,106,'Consulta dermatologica','Paciente evaluado durante la consulta.'),(66,109,'Consulta neurologica','Paciente evaluado durante la consulta.'),(67,110,'Consulta medicina general','Paciente evaluado durante la consulta.'),(68,113,'Consulta oftalmologica','Paciente evaluado durante la consulta.'),(69,114,'Consulta medicina general','Paciente evaluado durante la consulta.'),(70,117,'Consulta gastroenterologica','Paciente evaluado durante la consulta.'),(71,118,'Consulta gastroenterologica','Paciente evaluado durante la consulta.'),(129,121,'Control cardiológico','Paciente estable. Se realiza evaluación general, control de presión arterial y se indican medidas preventivas.');
/*!40000 ALTER TABLE `atencion` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_validar_atencion` BEFORE INSERT ON `atencion` FOR EACH ROW BEGIN

    DECLARE v_estado VARCHAR(20);

    -- Obtener el estado de la cita asociada
    SELECT ec.nombre
    INTO v_estado
    FROM cita c
    JOIN estado_cita ec
        ON c.id_estado_cita = ec.id_estado_cita
    WHERE c.id_cita = NEW.id_cita;


    -- Solo se puede crear una atencion
    -- si la cita se encuentra Atendida
    IF v_estado <> 'Atendida' THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'No se puede registrar una atención porque la cita no está Atendida';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `atencion_diagnostico`
--

DROP TABLE IF EXISTS `atencion_diagnostico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atencion_diagnostico` (
  `id_atencion` int NOT NULL,
  `codigo_diagnostico` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_atencion`,`codigo_diagnostico`),
  KEY `fk_ad_diagnostico` (`codigo_diagnostico`),
  CONSTRAINT `fk_ad_atencion` FOREIGN KEY (`id_atencion`) REFERENCES `atencion` (`id_atencion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ad_diagnostico` FOREIGN KEY (`codigo_diagnostico`) REFERENCES `diagnostico` (`codigo_diagnostico`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atencion_diagnostico`
--

LOCK TABLES `atencion_diagnostico` WRITE;
/*!40000 ALTER TABLE `atencion_diagnostico` DISABLE KEYS */;
INSERT INTO `atencion_diagnostico` VALUES (1,'D001'),(3,'D001'),(5,'D001'),(7,'D001'),(52,'D001'),(53,'D001'),(54,'D001'),(55,'D001'),(8,'D002'),(9,'D002'),(10,'D002'),(11,'D002'),(12,'D002'),(13,'D002'),(14,'D002'),(15,'D002'),(56,'D002'),(58,'D002'),(16,'D003'),(18,'D003'),(20,'D003'),(22,'D003'),(60,'D003'),(61,'D003'),(62,'D003'),(32,'D004'),(33,'D004'),(35,'D004'),(37,'D004'),(63,'D004'),(64,'D004'),(65,'D004'),(38,'D005'),(39,'D005'),(40,'D005'),(41,'D005'),(42,'D005'),(66,'D005'),(43,'D006'),(44,'D006'),(45,'D006'),(46,'D006'),(47,'D006'),(68,'D006'),(48,'D007'),(50,'D007'),(70,'D007'),(71,'D007'),(2,'D008'),(4,'D008'),(6,'D008'),(17,'D008'),(19,'D008'),(21,'D008'),(23,'D008'),(24,'D008'),(25,'D008'),(26,'D008'),(27,'D008'),(28,'D008'),(29,'D008'),(30,'D008'),(31,'D008'),(34,'D008'),(36,'D008'),(49,'D008'),(51,'D008'),(57,'D008'),(59,'D008'),(67,'D008'),(69,'D008'),(6,'D009'),(12,'D009'),(18,'D009'),(24,'D009'),(30,'D009'),(36,'D009'),(42,'D009'),(48,'D009'),(54,'D009'),(60,'D009'),(66,'D009'),(7,'D010'),(14,'D010'),(21,'D010'),(28,'D010'),(35,'D010'),(42,'D010'),(49,'D010'),(56,'D010'),(63,'D010'),(70,'D010'),(129,'I10');
/*!40000 ALTER TABLE `atencion_diagnostico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `centro_medico`
--

DROP TABLE IF EXISTS `centro_medico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `centro_medico` (
  `codigo_centro` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comuna` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`codigo_centro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `centro_medico`
--

LOCK TABLES `centro_medico` WRITE;
/*!40000 ALTER TABLE `centro_medico` DISABLE KEYS */;
INSERT INTO `centro_medico` VALUES ('CM001','SaludUSM Providencia','Providencia','Región Metropolitana'),('CM002','SaludUSM Maipú','Maipú','Región Metropolitana'),('CM003','SaludUSM Viña del Mar','Viña del Mar','Región de Valparaíso'),('CM004','SaludUSM Valparaíso','Valparaíso','Región de Valparaíso');
/*!40000 ALTER TABLE `centro_medico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cita`
--

DROP TABLE IF EXISTS `cita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cita` (
  `id_cita` int NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL,
  `id_paciente` int NOT NULL,
  `id_medico` int NOT NULL,
  `codigo_centro` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_especialidad` int NOT NULL,
  `id_estado_cita` int NOT NULL,
  `id_medico_bloqueo` int GENERATED ALWAYS AS ((case when (`id_estado_cita` in (1,2)) then `id_medico` else NULL end)) STORED,
  `id_paciente_bloqueo` int GENERATED ALWAYS AS ((case when (`id_estado_cita` in (1,2)) then `id_paciente` else NULL end)) STORED,
  PRIMARY KEY (`id_cita`),
  UNIQUE KEY `uq_cita_medico_horario_activo` (`id_medico_bloqueo`,`fecha_hora`),
  UNIQUE KEY `uq_cita_paciente_horario_activo` (`id_paciente_bloqueo`,`fecha_hora`),
  KEY `idx_cita_paciente_fk` (`id_paciente`),
  KEY `idx_cita_medico_centro_fk` (`id_medico`,`codigo_centro`),
  KEY `fk_cita_medico_especialidad` (`id_medico`,`id_especialidad`),
  KEY `fk_cita_estado` (`id_estado_cita`),
  CONSTRAINT `fk_cita_estado` FOREIGN KEY (`id_estado_cita`) REFERENCES `estado_cita` (`id_estado_cita`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_cita_medico_centro` FOREIGN KEY (`id_medico`, `codigo_centro`) REFERENCES `medico_centro` (`id_medico`, `codigo_centro`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_cita_medico_especialidad` FOREIGN KEY (`id_medico`, `id_especialidad`) REFERENCES `medico_especialidad` (`id_medico`, `id_especialidad`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_cita_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `paciente` (`id_paciente`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cita`
--

LOCK TABLES `cita` WRITE;
/*!40000 ALTER TABLE `cita` DISABLE KEYS */;
INSERT INTO `cita` (`id_cita`, `fecha_hora`, `id_paciente`, `id_medico`, `codigo_centro`, `id_especialidad`, `id_estado_cita`) VALUES (1,'2026-06-01 09:00:00',1,1,'CM001',1,3),(2,'2026-06-01 09:30:00',2,1,'CM003',4,3),(3,'2026-06-01 10:00:00',3,1,'CM001',1,3),(4,'2026-06-01 10:30:00',4,1,'CM003',4,3),(5,'2026-06-01 11:00:00',5,1,'CM001',1,3),(6,'2026-06-01 11:30:00',6,1,'CM003',4,3),(7,'2026-06-01 14:00:00',7,1,'CM001',1,3),(8,'2026-06-01 14:30:00',8,1,'CM003',4,4),(9,'2026-06-01 15:00:00',9,1,'CM001',1,4),(10,'2026-06-01 15:30:00',10,1,'CM003',4,4),(11,'2026-06-01 16:00:00',11,1,'CM001',1,4),(12,'2026-06-01 16:30:00',12,1,'CM003',4,4),(13,'2026-06-02 09:00:00',13,1,'CM001',1,4),(14,'2026-06-02 09:30:00',14,1,'CM003',4,4),(15,'2026-06-03 09:00:00',1,2,'CM001',2,3),(16,'2026-06-03 09:30:00',2,2,'CM001',2,3),(17,'2026-06-03 10:00:00',3,2,'CM001',2,3),(18,'2026-06-03 10:30:00',4,2,'CM001',2,3),(19,'2026-06-03 11:00:00',5,2,'CM001',2,3),(20,'2026-06-03 11:30:00',6,2,'CM001',2,3),(21,'2026-06-03 14:00:00',7,2,'CM001',2,3),(22,'2026-06-03 14:30:00',8,2,'CM001',2,3),(23,'2026-06-03 15:00:00',9,2,'CM001',2,4),(24,'2026-06-03 15:30:00',10,2,'CM001',2,4),(25,'2026-06-03 16:00:00',11,2,'CM001',2,5),(26,'2026-06-03 16:30:00',12,2,'CM001',2,5),(27,'2026-06-03 17:00:00',13,2,'CM001',2,5),(28,'2026-06-04 09:00:00',14,3,'CM002',3,3),(29,'2026-06-04 09:30:00',15,3,'CM004',4,3),(30,'2026-06-04 10:00:00',16,3,'CM002',3,3),(31,'2026-06-04 10:30:00',17,3,'CM004',4,3),(32,'2026-06-04 11:00:00',18,3,'CM002',3,3),(33,'2026-06-04 11:30:00',19,3,'CM004',4,3),(34,'2026-06-04 14:00:00',20,3,'CM002',3,3),(35,'2026-06-04 14:30:00',21,3,'CM004',4,3),(36,'2026-06-04 15:00:00',22,3,'CM002',3,4),(37,'2026-06-04 15:30:00',23,3,'CM004',4,4),(38,'2026-06-04 16:00:00',24,3,'CM002',3,5),(39,'2026-06-04 16:30:00',25,3,'CM004',4,5),(40,'2026-06-05 09:00:00',26,4,'CM001',4,3),(41,'2026-06-05 09:30:00',27,4,'CM002',4,3),(42,'2026-06-05 10:00:00',28,4,'CM001',4,3),(43,'2026-06-05 10:30:00',29,4,'CM002',4,3),(44,'2026-06-05 11:00:00',30,4,'CM001',4,3),(45,'2026-06-05 11:30:00',31,4,'CM002',4,3),(46,'2026-06-05 14:00:00',32,4,'CM001',4,3),(47,'2026-06-05 14:30:00',33,4,'CM002',4,3),(48,'2026-06-05 15:00:00',34,4,'CM001',4,5),(49,'2026-06-05 15:30:00',35,4,'CM002',4,5),(50,'2026-06-05 16:00:00',36,4,'CM001',4,5),(51,'2026-06-08 09:00:00',1,5,'CM003',5,3),(52,'2026-06-08 09:30:00',2,5,'CM003',5,3),(53,'2026-06-08 10:00:00',3,5,'CM003',4,3),(54,'2026-06-08 10:30:00',4,5,'CM003',5,3),(55,'2026-06-08 11:00:00',5,5,'CM003',4,3),(56,'2026-06-08 11:30:00',6,5,'CM003',5,3),(57,'2026-06-08 14:00:00',7,5,'CM003',5,4),(58,'2026-06-08 14:30:00',8,5,'CM003',4,5),(59,'2026-06-08 15:00:00',9,5,'CM003',5,5),(60,'2026-06-08 15:30:00',10,5,'CM003',4,5),(61,'2026-06-09 09:00:00',11,6,'CM001',6,3),(62,'2026-06-09 09:30:00',12,6,'CM004',6,3),(63,'2026-06-09 10:00:00',13,6,'CM001',6,3),(64,'2026-06-09 10:30:00',14,6,'CM004',6,3),(65,'2026-06-09 11:00:00',15,6,'CM001',6,3),(66,'2026-06-09 11:30:00',16,6,'CM004',6,4),(67,'2026-06-09 14:00:00',17,6,'CM001',6,5),(68,'2026-06-09 14:30:00',18,6,'CM004',6,5),(69,'2026-06-09 15:00:00',19,6,'CM001',6,5),(70,'2026-06-10 09:00:00',20,7,'CM002',7,3),(71,'2026-06-10 09:30:00',21,7,'CM002',7,3),(72,'2026-06-10 10:00:00',22,7,'CM002',7,3),(73,'2026-06-10 10:30:00',23,7,'CM002',7,3),(74,'2026-06-10 11:00:00',24,7,'CM002',7,3),(75,'2026-06-10 11:30:00',25,7,'CM002',7,5),(76,'2026-06-10 14:00:00',26,7,'CM002',7,5),(77,'2026-06-10 14:30:00',27,7,'CM002',7,5),(78,'2026-06-11 09:00:00',28,8,'CM003',8,3),(79,'2026-06-11 09:30:00',29,8,'CM004',4,3),(80,'2026-06-11 10:00:00',30,8,'CM003',8,3),(81,'2026-06-11 10:30:00',31,8,'CM004',4,3),(82,'2026-06-11 11:00:00',32,8,'CM003',8,5),(83,'2026-06-11 11:30:00',33,8,'CM004',4,5),(84,'2026-06-11 14:00:00',34,8,'CM003',8,5),(85,'2026-06-12 09:00:00',35,9,'CM001',1,3),(86,'2026-06-12 09:30:00',36,9,'CM001',1,3),(87,'2026-06-12 10:00:00',37,9,'CM001',1,3),(88,'2026-06-12 10:30:00',38,9,'CM001',1,3),(89,'2026-06-12 11:00:00',39,9,'CM001',1,4),(90,'2026-06-12 11:30:00',40,9,'CM001',1,5),(91,'2026-06-12 14:00:00',1,9,'CM001',1,5),(92,'2026-06-15 09:00:00',2,10,'CM002',2,3),(93,'2026-06-15 09:30:00',3,10,'CM003',4,3),(94,'2026-06-15 10:00:00',4,10,'CM002',2,3),(95,'2026-06-15 10:30:00',5,10,'CM003',4,3),(96,'2026-06-15 11:00:00',6,10,'CM002',2,5),(97,'2026-06-15 11:30:00',7,10,'CM003',4,5),(98,'2026-06-16 09:00:00',8,11,'CM004',3,3),(99,'2026-06-16 09:30:00',9,11,'CM004',3,3),(100,'2026-06-16 10:00:00',10,11,'CM004',3,3),(101,'2026-06-16 10:30:00',11,11,'CM004',3,4),(102,'2026-06-16 11:00:00',12,11,'CM004',3,5),(103,'2026-06-16 11:30:00',13,11,'CM004',3,5),(104,'2026-06-17 09:00:00',14,12,'CM003',5,3),(105,'2026-06-17 09:30:00',15,12,'CM003',5,3),(106,'2026-06-17 10:00:00',16,12,'CM003',5,3),(107,'2026-06-17 10:30:00',17,12,'CM003',5,4),(108,'2026-06-17 11:00:00',18,12,'CM003',5,5),(109,'2026-06-18 09:00:00',19,13,'CM001',6,3),(110,'2026-06-18 09:30:00',20,13,'CM004',4,3),(111,'2026-06-18 10:00:00',21,13,'CM001',6,5),(112,'2026-06-18 10:30:00',22,13,'CM004',4,5),(113,'2026-06-19 09:00:00',23,14,'CM002',7,3),(114,'2026-06-19 09:30:00',24,14,'CM002',4,3),(115,'2026-06-19 10:00:00',25,14,'CM002',7,4),(116,'2026-06-19 10:30:00',26,14,'CM002',4,5),(117,'2026-06-22 09:00:00',27,15,'CM003',8,3),(118,'2026-06-22 09:30:00',28,15,'CM004',8,3),(119,'2026-06-22 10:00:00',29,15,'CM003',8,5),(120,'2026-06-22 10:30:00',30,15,'CM004',8,5),(121,'2027-01-12 09:00:00',1,1,'CM001',1,3),(122,'2027-01-12 09:30:00',2,1,'CM003',4,4),(123,'2027-01-13 09:00:00',3,2,'CM001',2,1),(124,'2027-01-13 09:30:00',4,2,'CM001',2,2),(125,'2027-01-14 09:00:00',5,3,'CM002',3,1),(126,'2027-01-14 09:30:00',6,3,'CM004',4,2),(127,'2027-01-15 09:00:00',7,4,'CM001',4,1),(128,'2027-01-15 09:30:00',8,4,'CM002',4,2),(129,'2027-01-16 09:00:00',9,5,'CM003',5,1),(130,'2027-01-16 09:30:00',10,5,'CM003',4,2),(131,'2027-01-19 09:00:00',11,6,'CM001',6,1),(132,'2027-01-19 09:30:00',12,6,'CM004',6,2),(134,'2027-02-02 10:00:00',41,1,'CM001',1,5),(135,'2027-02-04 11:30:00',41,1,'CM001',1,5),(136,'2027-02-10 10:00:00',42,1,'CM001',1,5),(137,'2027-02-10 10:00:00',42,1,'CM001',1,5),(138,'2027-02-10 10:00:00',42,1,'CM001',1,1);
/*!40000 ALTER TABLE `cita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `diagnostico`
--

DROP TABLE IF EXISTS `diagnostico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diagnostico` (
  `codigo_diagnostico` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`codigo_diagnostico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `diagnostico`
--

LOCK TABLES `diagnostico` WRITE;
/*!40000 ALTER TABLE `diagnostico` DISABLE KEYS */;
INSERT INTO `diagnostico` VALUES ('D001','Hipertension arterial'),('D002','Resfrio comun'),('D003','Dolor lumbar'),('D004','Dermatitis'),('D005','Migraña'),('D006','Conjuntivitis'),('D007','Gastritis'),('D008','Control medico general'),('D009','Alergia estacional'),('D010','Dolor articular'),('I10','Hipertensión esencial (primaria)'),('R00.2','Palpitaciones cardíacas');
/*!40000 ALTER TABLE `diagnostico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `especialidad`
--

DROP TABLE IF EXISTS `especialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `especialidad` (
  `id_especialidad` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_especialidad`),
  UNIQUE KEY `uq_especialidad_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `especialidad`
--

LOCK TABLES `especialidad` WRITE;
/*!40000 ALTER TABLE `especialidad` DISABLE KEYS */;
INSERT INTO `especialidad` VALUES (1,'Cardiología'),(5,'Dermatología'),(8,'Gastroenterología'),(4,'Medicina General'),(6,'Neurología'),(7,'Oftalmología'),(2,'Pediatría'),(3,'Traumatología');
/*!40000 ALTER TABLE `especialidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estado_cita`
--

DROP TABLE IF EXISTS `estado_cita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado_cita` (
  `id_estado_cita` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_estado_cita`),
  UNIQUE KEY `uq_estado_cita_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado_cita`
--

LOCK TABLES `estado_cita` WRITE;
/*!40000 ALTER TABLE `estado_cita` DISABLE KEYS */;
INSERT INTO `estado_cita` VALUES (3,'Atendida'),(5,'Cancelada'),(2,'Confirmada'),(4,'No Asistió'),(1,'Reservada');
/*!40000 ALTER TABLE `estado_cita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medico`
--

DROP TABLE IF EXISTS `medico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medico` (
  `id_medico` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `nombre_completo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_medico`),
  UNIQUE KEY `uq_medico_usuario` (`id_usuario`),
  CONSTRAINT `fk_medico_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medico`
--

LOCK TABLES `medico` WRITE;
/*!40000 ALTER TABLE `medico` DISABLE KEYS */;
INSERT INTO `medico` VALUES (1,41,'Medico01 Apellido01'),(2,42,'Medico02 Apellido02'),(3,43,'Medico03 Apellido03'),(4,44,'Medico04 Apellido04'),(5,45,'Medico05 Apellido05'),(6,46,'Medico06 Apellido06'),(7,47,'Medico07 Apellido07'),(8,48,'Medico08 Apellido08'),(9,49,'Medico09 Apellido09'),(10,50,'Medico10 Apellido10'),(11,51,'Medico11 Apellido11'),(12,52,'Medico12 Apellido12'),(13,53,'Medico13 Apellido13'),(14,54,'Medico14 Apellido14'),(15,55,'Medico15 Apellido15'),(16,62,'Medico Prueba Admin');
/*!40000 ALTER TABLE `medico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medico_centro`
--

DROP TABLE IF EXISTS `medico_centro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medico_centro` (
  `id_medico` int NOT NULL,
  `codigo_centro` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_medico`,`codigo_centro`),
  KEY `fk_mc_centro` (`codigo_centro`),
  CONSTRAINT `fk_mc_centro` FOREIGN KEY (`codigo_centro`) REFERENCES `centro_medico` (`codigo_centro`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_mc_medico` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medico_centro`
--

LOCK TABLES `medico_centro` WRITE;
/*!40000 ALTER TABLE `medico_centro` DISABLE KEYS */;
INSERT INTO `medico_centro` VALUES (1,'CM001',1),(1,'CM003',1),(2,'CM001',1),(3,'CM002',1),(3,'CM004',1),(4,'CM001',1),(4,'CM002',1),(5,'CM003',1),(6,'CM001',1),(6,'CM004',1),(7,'CM002',1),(8,'CM003',1),(8,'CM004',1),(9,'CM001',1),(10,'CM002',1),(10,'CM003',1),(11,'CM004',1),(12,'CM003',1),(13,'CM001',1),(13,'CM004',1),(14,'CM002',1),(15,'CM003',1),(15,'CM004',1),(16,'CM001',1),(16,'CM003',0);
/*!40000 ALTER TABLE `medico_centro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medico_especialidad`
--

DROP TABLE IF EXISTS `medico_especialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medico_especialidad` (
  `id_medico` int NOT NULL,
  `id_especialidad` int NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_medico`,`id_especialidad`),
  KEY `fk_me_especialidad` (`id_especialidad`),
  CONSTRAINT `fk_me_especialidad` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidad` (`id_especialidad`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_me_medico` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medico_especialidad`
--

LOCK TABLES `medico_especialidad` WRITE;
/*!40000 ALTER TABLE `medico_especialidad` DISABLE KEYS */;
INSERT INTO `medico_especialidad` VALUES (1,1,1),(1,4,1),(2,2,1),(3,3,1),(3,4,1),(4,4,1),(5,4,1),(5,5,1),(6,6,1),(7,7,1),(8,4,1),(8,8,1),(9,1,1),(10,2,1),(10,4,1),(11,3,1),(12,5,1),(13,4,1),(13,6,1),(14,4,1),(14,7,1),(15,8,1),(16,1,1),(16,4,0);
/*!40000 ALTER TABLE `medico_especialidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paciente`
--

DROP TABLE IF EXISTS `paciente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paciente` (
  `id_paciente` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `nombre_completo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_prevision` int NOT NULL,
  `telefono_contacto` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comuna_residencia` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_paciente`),
  UNIQUE KEY `uq_paciente_usuario` (`id_usuario`),
  KEY `fk_paciente_prevision` (`id_prevision`),
  CONSTRAINT `fk_paciente_prevision` FOREIGN KEY (`id_prevision`) REFERENCES `prevision` (`id_prevision`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_paciente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_paciente_sexo` CHECK ((`sexo` in (_utf8mb4'M',_utf8mb4'F',_utf8mb4'Otro')))
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paciente`
--

LOCK TABLES `paciente` WRITE;
/*!40000 ALTER TABLE `paciente` DISABLE KEYS */;
INSERT INTO `paciente` VALUES (1,1,'Nombre01 Apellido01','1970-01-01','F',2,'900000001','Providencia'),(2,2,'Nombre02 Apellido02','1971-01-01','M',1,'900000002','Maipú'),(3,3,'Nombre03 Apellido03','1972-01-01','F',2,'900000003','Viña del Mar'),(4,4,'Nombre04 Apellido04','1973-01-01','M',3,'900000004','Valparaíso'),(5,5,'Nombre05 Apellido05','1974-01-01','F',2,NULL,'Santiago'),(6,6,'Nombre06 Apellido06','1975-01-01','M',1,'900000006','Providencia'),(7,7,'Nombre07 Apellido07','1976-01-01','F',2,'900000007','Maipú'),(8,8,'Nombre08 Apellido08','1977-01-01','M',1,'900000008','Viña del Mar'),(9,9,'Nombre09 Apellido09','1978-01-01','F',2,'900000009','Valparaíso'),(10,10,'Nombre10 Apellido10','1979-01-01','M',3,NULL,'Santiago'),(11,11,'Nombre11 Apellido11','1980-01-01','F',2,'900000011','Providencia'),(12,12,'Nombre12 Apellido12','1981-01-01','M',1,'900000012','Maipú'),(13,13,'Nombre13 Apellido13','1982-01-01','F',2,'900000013','Viña del Mar'),(14,14,'Nombre14 Apellido14','1983-01-01','M',1,'900000014','Valparaíso'),(15,15,'Nombre15 Apellido15','1984-01-01','F',3,NULL,'Santiago'),(16,16,'Nombre16 Apellido16','1985-01-01','M',2,'900000016','Providencia'),(17,17,'Nombre17 Apellido17','1986-01-01','F',1,'900000017','Maipú'),(18,18,'Nombre18 Apellido18','1987-01-01','M',2,'900000018','Viña del Mar'),(19,19,'Nombre19 Apellido19','1988-01-01','F',1,'900000019','Valparaíso'),(20,20,'Nombre20 Apellido20','1989-01-01','M',3,NULL,'Santiago'),(21,21,'Nombre21 Apellido21','1990-01-01','F',2,'900000021','Providencia'),(22,22,'Nombre22 Apellido22','1991-01-01','M',1,'900000022','Maipú'),(23,23,'Nombre23 Apellido23','1992-01-01','Otro',2,'900000023','Viña del Mar'),(24,24,'Nombre24 Apellido24','1993-01-01','M',1,'900000024','Valparaíso'),(25,25,'Nombre25 Apellido25','1994-01-01','F',3,NULL,'Santiago'),(26,26,'Nombre26 Apellido26','2000-01-01','M',2,'900000026','Providencia'),(27,27,'Nombre27 Apellido27','2001-01-01','F',1,'900000027','Maipú'),(28,28,'Nombre28 Apellido28','2002-01-01','M',2,'900000028','Viña del Mar'),(29,29,'Nombre29 Apellido29','2003-01-01','F',1,'900000029','Valparaíso'),(30,30,'Nombre30 Apellido30','2004-01-01','M',3,NULL,'Santiago'),(31,31,'Nombre31 Apellido31','2005-01-01','F',2,'900000031','Providencia'),(32,32,'Nombre32 Apellido32','2006-01-01','M',1,'900000032','Maipú'),(33,33,'Nombre33 Apellido33','2007-01-01','F',2,'900000033','Viña del Mar'),(34,34,'Nombre34 Apellido34','2008-01-01','M',1,'900000034','Valparaíso'),(35,35,'Nombre35 Apellido35','2009-01-01','F',3,NULL,'Santiago'),(36,36,'Nombre36 Apellido36','2010-01-01','M',2,'900000036','Providencia'),(37,37,'Nombre37 Apellido37','2011-01-01','F',1,'900000037','Maipú'),(38,38,'Nombre38 Apellido38','2012-01-01','M',2,'900000038','Viña del Mar'),(39,39,'Nombre39 Apellido39','2013-01-01','F',1,'900000039','Valparaíso'),(40,40,'Nombre40 Apellido40','2014-01-01','M',3,NULL,'Santiago'),(41,61,'Paciente Prueba','2001-01-01','M',1,'987654321','Viña del Mar'),(42,63,'Paciente Disponibilidad','2000-01-01','M',1,'912345678','Santiago');
/*!40000 ALTER TABLE `paciente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prevision`
--

DROP TABLE IF EXISTS `prevision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prevision` (
  `id_prevision` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_prevision`),
  UNIQUE KEY `uq_prevision_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prevision`
--

LOCK TABLES `prevision` WRITE;
/*!40000 ALTER TABLE `prevision` DISABLE KEYS */;
INSERT INTO `prevision` VALUES (1,'Fonasa'),(2,'Isapre'),(3,'Particular');
/*!40000 ALTER TABLE `prevision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receta`
--

DROP TABLE IF EXISTS `receta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `receta` (
  `id_receta` int NOT NULL AUTO_INCREMENT,
  `id_atencion` int NOT NULL,
  `medicamento` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosis` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dias_tratamiento` int NOT NULL,
  PRIMARY KEY (`id_receta`),
  KEY `fk_receta_atencion` (`id_atencion`),
  CONSTRAINT `fk_receta_atencion` FOREIGN KEY (`id_atencion`) REFERENCES `atencion` (`id_atencion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_receta_dias` CHECK ((`dias_tratamiento` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receta`
--

LOCK TABLES `receta` WRITE;
/*!40000 ALTER TABLE `receta` DISABLE KEYS */;
INSERT INTO `receta` VALUES (1,1,'Losartan','1 comprimido cada 24 horas',30),(2,2,'Paracetamol','1 comprimido cada 8 horas',5),(3,3,'Losartan','1 comprimido cada 24 horas',30),(4,4,'Paracetamol','1 comprimido cada 8 horas',5),(5,5,'Losartan','1 comprimido cada 24 horas',30),(6,6,'Paracetamol','1 comprimido cada 8 horas',5),(7,7,'Losartan','1 comprimido cada 24 horas',30),(8,8,'Paracetamol','1 comprimido cada 8 horas',5),(9,9,'Paracetamol','1 comprimido cada 8 horas',5),(10,10,'Paracetamol','1 comprimido cada 8 horas',5),(11,11,'Paracetamol','1 comprimido cada 8 horas',5),(12,12,'Paracetamol','1 comprimido cada 8 horas',5),(13,13,'Paracetamol','1 comprimido cada 8 horas',5),(14,14,'Paracetamol','1 comprimido cada 8 horas',5),(15,15,'Paracetamol','1 comprimido cada 8 horas',5),(16,16,'Ibuprofeno','1 comprimido cada 8 horas',7),(17,17,'Paracetamol','1 comprimido cada 8 horas',5),(18,18,'Ibuprofeno','1 comprimido cada 8 horas',7),(19,19,'Paracetamol','1 comprimido cada 8 horas',5),(20,20,'Ibuprofeno','1 comprimido cada 8 horas',7),(21,21,'Paracetamol','1 comprimido cada 8 horas',5),(22,22,'Ibuprofeno','1 comprimido cada 8 horas',7),(23,23,'Paracetamol','1 comprimido cada 8 horas',5),(24,24,'Paracetamol','1 comprimido cada 8 horas',5),(25,25,'Paracetamol','1 comprimido cada 8 horas',5),(26,26,'Paracetamol','1 comprimido cada 8 horas',5),(27,27,'Paracetamol','1 comprimido cada 8 horas',5),(28,28,'Paracetamol','1 comprimido cada 8 horas',5),(29,29,'Paracetamol','1 comprimido cada 8 horas',5),(30,30,'Paracetamol','1 comprimido cada 8 horas',5),(31,31,'Paracetamol','1 comprimido cada 8 horas',5),(32,32,'Loratadina','1 comprimido cada 24 horas',10),(33,33,'Loratadina','1 comprimido cada 24 horas',10),(34,34,'Paracetamol','1 comprimido cada 8 horas',5),(35,35,'Loratadina','1 comprimido cada 24 horas',10),(36,36,'Paracetamol','1 comprimido cada 8 horas',5),(37,37,'Loratadina','1 comprimido cada 24 horas',10),(38,38,'Sumatriptan','1 comprimido si presenta sintomas',5),(39,39,'Sumatriptan','1 comprimido si presenta sintomas',5),(40,40,'Sumatriptan','1 comprimido si presenta sintomas',5),(41,41,'Sumatriptan','1 comprimido si presenta sintomas',5),(42,42,'Sumatriptan','1 comprimido si presenta sintomas',5),(43,43,'Lagrimas artificiales','2 gotas cada 8 horas',7),(44,44,'Lagrimas artificiales','2 gotas cada 8 horas',7),(45,45,'Lagrimas artificiales','2 gotas cada 8 horas',7),(46,46,'Lagrimas artificiales','2 gotas cada 8 horas',7),(47,47,'Lagrimas artificiales','2 gotas cada 8 horas',7),(48,48,'Omeprazol','1 capsula cada 24 horas',14),(49,49,'Paracetamol','1 comprimido cada 8 horas',5),(50,50,'Omeprazol','1 capsula cada 24 horas',14),(51,51,'Paracetamol','1 comprimido cada 8 horas',5),(52,52,'Losartan','1 comprimido cada 24 horas',30),(53,53,'Losartan','1 comprimido cada 24 horas',30),(54,54,'Losartan','1 comprimido cada 24 horas',30),(55,55,'Losartan','1 comprimido cada 24 horas',30),(56,56,'Paracetamol','1 comprimido cada 8 horas',5),(57,57,'Paracetamol','1 comprimido cada 8 horas',5),(58,58,'Paracetamol','1 comprimido cada 8 horas',5),(59,59,'Paracetamol','1 comprimido cada 8 horas',5),(60,60,'Ibuprofeno','1 comprimido cada 8 horas',7),(61,61,'Ibuprofeno','1 comprimido cada 8 horas',7),(62,62,'Ibuprofeno','1 comprimido cada 8 horas',7),(63,63,'Loratadina','1 comprimido cada 24 horas',10),(64,64,'Loratadina','1 comprimido cada 24 horas',10),(65,65,'Loratadina','1 comprimido cada 24 horas',10),(66,66,'Sumatriptan','1 comprimido si presenta sintomas',5),(67,67,'Paracetamol','1 comprimido cada 8 horas',5),(68,68,'Lagrimas artificiales','2 gotas cada 8 horas',7),(69,69,'Paracetamol','1 comprimido cada 8 horas',5),(70,70,'Omeprazol','1 capsula cada 24 horas',14),(71,71,'Omeprazol','1 capsula cada 24 horas',14),(128,8,'Ibuprofeno','1 comprimido cada 8 horas',5),(129,9,'Loratadina','1 comprimido cada 24 horas',7),(130,10,'Amoxicilina','1 comprimido cada 8 horas',7),(131,11,'Salbutamol','2 inhalaciones cada 8 horas',5),(132,5,'Paracetamol','1 comprimido cada 8 horas',5),(133,10,'Paracetamol','1 comprimido cada 8 horas',5),(134,15,'Paracetamol','1 comprimido cada 8 horas',5),(135,20,'Paracetamol','1 comprimido cada 8 horas',5),(136,25,'Paracetamol','1 comprimido cada 8 horas',5),(137,30,'Paracetamol','1 comprimido cada 8 horas',5),(138,35,'Paracetamol','1 comprimido cada 8 horas',5),(139,40,'Paracetamol','1 comprimido cada 8 horas',5),(140,45,'Paracetamol','1 comprimido cada 8 horas',5),(141,50,'Paracetamol','1 comprimido cada 8 horas',5),(142,55,'Paracetamol','1 comprimido cada 8 horas',5),(143,60,'Paracetamol','1 comprimido cada 8 horas',5),(144,65,'Paracetamol','1 comprimido cada 8 horas',5),(145,70,'Paracetamol','1 comprimido cada 8 horas',5),(147,129,'Losartán','1 comprimido cada 24 horas',30);
/*!40000 ALTER TABLE `receta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `rut` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('PACIENTE','MEDICO','ADMIN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_usuario_rut` (`rut`),
  UNIQUE KEY `uq_usuario_email` (`email`),
  CONSTRAINT `chk_usuario_rut` CHECK (regexp_like(`rut`,_utf8mb4'^[0-9]{8}-[0-9Kk]$'))
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'10000000-8','paciente01@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(2,'10000001-6','paciente02@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(3,'10000002-4','paciente03@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(4,'10000003-2','paciente04@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(5,'10000004-0','paciente05@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(6,'10000005-9','paciente06@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(7,'10000006-7','paciente07@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(8,'10000007-5','paciente08@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(9,'10000008-3','paciente09@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(10,'10000009-1','paciente10@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(11,'10000010-5','paciente11@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(12,'10000011-3','paciente12@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(13,'10000012-1','paciente13@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(14,'10000013-K','paciente14@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(15,'10000014-8','paciente15@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(16,'10000015-6','paciente16@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(17,'10000016-4','paciente17@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(18,'10000017-2','paciente18@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(19,'10000018-0','paciente19@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(20,'10000019-9','paciente20@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(21,'10000020-2','paciente21@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(22,'10000021-0','paciente22@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(23,'10000022-9','paciente23@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(24,'10000023-7','paciente24@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(25,'10000024-5','paciente25@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(26,'10000025-3','paciente26@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(27,'10000026-1','paciente27@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(28,'10000027-K','paciente28@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(29,'10000028-8','paciente29@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(30,'10000029-6','paciente30@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(31,'10000030-K','paciente31@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(32,'10000031-8','paciente32@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(33,'10000032-6','paciente33@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(34,'10000033-4','paciente34@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(35,'10000034-2','paciente35@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(36,'10000035-0','paciente36@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(37,'10000036-9','paciente37@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(38,'10000037-7','paciente38@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(39,'10000038-5','paciente39@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(40,'10000039-3','paciente40@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','PACIENTE',1,'2026-10-06 03:33:34'),(41,'20000001-3','medico01@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(42,'20000002-1','medico02@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(43,'20000003-K','medico03@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(44,'20000004-8','medico04@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(45,'20000005-6','medico05@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(46,'20000006-4','medico06@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(47,'20000007-2','medico07@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(48,'20000008-0','medico08@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(49,'20000009-9','medico09@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(50,'20000010-2','medico10@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(51,'20000011-0','medico11@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(52,'20000012-9','medico12@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(53,'20000013-7','medico13@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(54,'20000014-5','medico14@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(55,'20000015-3','medico15@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','MEDICO',1,'2026-10-06 03:33:34'),(56,'30000000-5','admin@saludusm.cl','$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa','ADMIN',1,'2026-10-06 03:33:34'),(61,'88888888-8','prueba2@saludusm.cl','$2y$12$PquN1mOQrkCMmApnNEczhe1EEMVVScWB2qHIOKly.UsmMHX76vfb2','PACIENTE',0,'2026-10-07 02:32:14'),(62,'77777777-7','medicoprueba@saludusm.cl','$2y$12$9KqZwV.XEIbvO7sy/OlCcuKemQGFPzEuu.AY74yoXRQJKbSeYrKg6','MEDICO',1,'2026-10-11 01:48:10'),(63,'66666666-6','disponibilidad@saludusm.cl','$2y$12$jfOQtM5OuViePt5QuhUGGOoDQPl2ZxVdgDa.2S35OboWAJ8iJiFoe','PACIENTE',1,'2026-10-11 02:41:13');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `vw_citas_detalle`
--

DROP TABLE IF EXISTS `vw_citas_detalle`;
/*!50001 DROP VIEW IF EXISTS `vw_citas_detalle`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_citas_detalle` AS SELECT 
 1 AS `id_cita`,
 1 AS `fecha_hora`,
 1 AS `id_paciente`,
 1 AS `rut_paciente`,
 1 AS `paciente`,
 1 AS `prevision`,
 1 AS `id_medico`,
 1 AS `rut_medico`,
 1 AS `medico`,
 1 AS `id_especialidad`,
 1 AS `especialidad`,
 1 AS `codigo_centro`,
 1 AS `centro`,
 1 AS `comuna`,
 1 AS `region`,
 1 AS `id_estado_cita`,
 1 AS `estado`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'salud_usm'
--
/*!50003 DROP FUNCTION IF EXISTS `fn_porcentaje_inasistencia` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_porcentaje_inasistencia`(
    p_codigo_centro VARCHAR(20)
) RETURNS decimal(5,2)
    READS SQL DATA
BEGIN

    DECLARE v_total_citas INT DEFAULT 0;
    DECLARE v_no_asistio INT DEFAULT 0;
    DECLARE v_porcentaje DECIMAL(5,2) DEFAULT 0.00;


    -- Cantidad total de citas del centro
    SELECT COUNT(*)
    INTO v_total_citas
    FROM cita
    WHERE codigo_centro = p_codigo_centro;


    -- Cantidad de citas en estado "No Asistió"
    SELECT COUNT(*)
    INTO v_no_asistio
    FROM cita c
    JOIN estado_cita ec
        ON c.id_estado_cita = ec.id_estado_cita
    WHERE c.codigo_centro = p_codigo_centro
      AND ec.nombre = 'No Asistió';


    -- Evita una división por cero
    IF v_total_citas = 0 THEN
        RETURN 0.00;
    END IF;


    SET v_porcentaje =
        ROUND(
            (v_no_asistio * 100.0) / v_total_citas,
            2
        );


    RETURN v_porcentaje;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cancelar_citas_vencidas` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cancelar_citas_vencidas`()
BEGIN

    DECLARE v_id_cancelada INT;

    -- Obtener el ID correspondiente al estado Cancelada
    SELECT id_estado_cita
    INTO v_id_cancelada
    FROM estado_cita
    WHERE nombre = 'Cancelada';


    -- Cambiar a Cancelada las citas vencidas que todavía
    -- se encuentren Reservadas o Confirmadas
    UPDATE cita
    SET id_estado_cita = v_id_cancelada
    WHERE fecha_hora < CURRENT_TIMESTAMP
      AND id_estado_cita IN (
            SELECT id_estado_cita
            FROM estado_cita
            WHERE nombre IN ('Reservada', 'Confirmada')
      );


    -- Informar cuántas citas fueron modificadas
    SELECT ROW_COUNT() AS citas_canceladas;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `vw_citas_detalle`
--

/*!50001 DROP VIEW IF EXISTS `vw_citas_detalle`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_citas_detalle` AS select `c`.`id_cita` AS `id_cita`,`c`.`fecha_hora` AS `fecha_hora`,`p`.`id_paciente` AS `id_paciente`,`up`.`rut` AS `rut_paciente`,`p`.`nombre_completo` AS `paciente`,`pr`.`nombre` AS `prevision`,`m`.`id_medico` AS `id_medico`,`um`.`rut` AS `rut_medico`,`m`.`nombre_completo` AS `medico`,`e`.`id_especialidad` AS `id_especialidad`,`e`.`nombre` AS `especialidad`,`cm`.`codigo_centro` AS `codigo_centro`,`cm`.`nombre` AS `centro`,`cm`.`comuna` AS `comuna`,`cm`.`region` AS `region`,`ec`.`id_estado_cita` AS `id_estado_cita`,`ec`.`nombre` AS `estado` from ((((((((`cita` `c` join `paciente` `p` on((`c`.`id_paciente` = `p`.`id_paciente`))) join `usuario` `up` on((`p`.`id_usuario` = `up`.`id_usuario`))) join `prevision` `pr` on((`p`.`id_prevision` = `pr`.`id_prevision`))) join `medico` `m` on((`c`.`id_medico` = `m`.`id_medico`))) join `usuario` `um` on((`m`.`id_usuario` = `um`.`id_usuario`))) join `especialidad` `e` on((`c`.`id_especialidad` = `e`.`id_especialidad`))) join `centro_medico` `cm` on((`c`.`codigo_centro` = `cm`.`codigo_centro`))) join `estado_cita` `ec` on((`c`.`id_estado_cita` = `ec`.`id_estado_cita`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-11  0:25:13
