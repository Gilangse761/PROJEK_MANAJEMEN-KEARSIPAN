-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: earsip_ksp
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `anggota`
--

DROP TABLE IF EXISTS `anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota` (
  `id_anggota` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` char(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_keanggotaan` enum('aktif','keluar','meninggal') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_anggota`),
  UNIQUE KEY `anggota_nik_unique` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anggota`
--

LOCK TABLES `anggota` WRITE;
/*!40000 ALTER TABLE `anggota` DISABLE KEYS */;
INSERT INTO `anggota` VALUES ('ANG-202601-0001','3216010101900001','Budi Santoso','aktif','2026-11-05 18:07:13'),('ANG-202601-0002','3216010101900002','Siti Rahayu','aktif','2026-11-05 18:07:13'),('ANG-202601-0003','3216010101900003','Agus Wijaya','aktif','2026-11-05 18:07:13'),('ANG-202601-0004','3216010101900004','Dewi Lestari','aktif','2026-11-05 18:07:13'),('ANG-202601-0005','3216010101900005','Hendra Gunawan','aktif','2026-11-05 18:07:13'),('ANG-202601-0006','3216010101900006','Rina Marlina','aktif','2026-11-05 18:07:13'),('ANG-202601-0007','3216010101900007','Joko Prasetyo','keluar','2026-11-05 18:07:13'),('ANG-202601-0008','3216010101900008','Maya Sari','aktif','2026-11-05 18:07:13');
/*!40000 ALTER TABLE `anggota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `arsip_dokumen`
--

DROP TABLE IF EXISTS `arsip_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arsip_dokumen` (
  `id_arsip` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_klasifikasi` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_lokasi` int unsigned NOT NULL,
  `referensi_nomor` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `judul_arsip` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_arsip` date NOT NULL,
  `media_arsip` enum('kertas','digital','hybrid') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_digital_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_arsip` enum('aktif','inaktif','musnah','permanen') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jadwal_retensi_inaktif` date DEFAULT NULL,
  `jadwal_penyusutan` date DEFAULT NULL,
  PRIMARY KEY (`id_arsip`),
  KEY `arsip_dokumen_referensi_nomor_index` (`referensi_nomor`),
  KEY `arsip_dokumen_kode_klasifikasi_foreign` (`kode_klasifikasi`),
  KEY `arsip_dokumen_id_lokasi_foreign` (`id_lokasi`),
  CONSTRAINT `arsip_dokumen_id_lokasi_foreign` FOREIGN KEY (`id_lokasi`) REFERENCES `lokasi_arsip` (`id_lokasi`),
  CONSTRAINT `arsip_dokumen_kode_klasifikasi_foreign` FOREIGN KEY (`kode_klasifikasi`) REFERENCES `klasifikasi_arsip` (`kode_klasifikasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `arsip_dokumen`
--

LOCK TABLES `arsip_dokumen` WRITE;
/*!40000 ALTER TABLE `arsip_dokumen` DISABLE KEYS */;
INSERT INTO `arsip_dokumen` VALUES ('ARC-KP.01/KSP-KRD/2026/0007','KP.01',4,'KRD-2025-0004','Formulir Pengajuan Pinjaman An. Dewi Lestari','2025-05-20','kertas',NULL,'aktif',NULL,NULL),('ARC-KP.02/KSP-KRD/2026/0005','KP.02',3,'KRD-2024-0001','Perjanjian Kredit An. Budi Santoso','2024-03-01','kertas',NULL,'aktif',NULL,NULL),('ARC-KP.02/KSP-KRD/2026/0006','KP.02',3,'KRD-2023-0006','Perjanjian Kredit An. Rina Marlina','2023-08-01','kertas',NULL,'aktif',NULL,NULL),('ARC-KP.03/KSP-KRD/2026/0001','KP.03',1,'KRD-2024-0001','Sertifikat SHM An. Budi Santoso','2019-06-15','kertas',NULL,'aktif',NULL,NULL),('ARC-KP.03/KSP-KRD/2026/0002','KP.03',1,'KRD-2024-0002','BPKB Mobil An. Hendra Gunawan','2022-11-20','kertas',NULL,'aktif',NULL,NULL),('ARC-KP.03/KSP-KRD/2026/0003','KP.03',1,'KRD-2025-0003','Sertifikat SHM An. Siti Rahayu','2018-03-05','kertas',NULL,'aktif',NULL,NULL),('ARC-KP.03/KSP-KRD/2026/0004','KP.03',2,'KRD-2023-0005','Sertifikat SHM An. Agus Wijaya','2017-09-09','kertas',NULL,'aktif',NULL,NULL),('ARC-KS.01/KSP-SMP/2026/0008','KS.01',4,'SMP-0008','Buku Simpanan Pokok & Wajib An. Maya Sari','2020-01-15','kertas',NULL,'aktif',NULL,NULL),('ARC-KS.02/KSP-SMP/2026/0009','KS.02',4,'SMP-0002','Rekening Koran An. Siti Rahayu (Semester I 2026)','2026-07-01','kertas',NULL,'aktif',NULL,NULL),('ARC-KU.01/KSP-KEU/2026/0010','KU.01',4,NULL,'Kuitansi Kas Keluar Oktober 2026','2026-10-31','kertas',NULL,'aktif',NULL,NULL);
/*!40000 ALTER TABLE `arsip_dokumen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `berita_acara_penyusutan`
--

DROP TABLE IF EXISTS `berita_acara_penyusutan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `berita_acara_penyusutan` (
  `no_berita_acara` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_arsip` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_eksekusi` date NOT NULL,
  `jenis_tindakan` enum('pemusnahan','penyerahan_statis') COLLATE utf8mb4_unicode_ci NOT NULL,
  `metode_pemusnahan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `petugas_eksekutor` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`no_berita_acara`),
  KEY `berita_acara_penyusutan_id_arsip_foreign` (`id_arsip`),
  CONSTRAINT `berita_acara_penyusutan_id_arsip_foreign` FOREIGN KEY (`id_arsip`) REFERENCES `arsip_dokumen` (`id_arsip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `berita_acara_penyusutan`
--

LOCK TABLES `berita_acara_penyusutan` WRITE;
/*!40000 ALTER TABLE `berita_acara_penyusutan` DISABLE KEYS */;
/*!40000 ALTER TABLE `berita_acara_penyusutan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
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
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `klasifikasi_arsip`
--

DROP TABLE IF EXISTS `klasifikasi_arsip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `klasifikasi_arsip` (
  `kode_klasifikasi` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_klasifikasi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `retensi_aktif` int NOT NULL,
  `retensi_inaktif` int NOT NULL,
  `keterangan_akhir` enum('musnah','permanen','diserahkan') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`kode_klasifikasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `klasifikasi_arsip`
--

LOCK TABLES `klasifikasi_arsip` WRITE;
/*!40000 ALTER TABLE `klasifikasi_arsip` DISABLE KEYS */;
INSERT INTO `klasifikasi_arsip` VALUES ('KE.01','Akta Pendirian, AD/ART & Notulen RAT',0,0,'permanen'),('KP.01','Formulir & Berkas Pengajuan Pinjaman',2,3,'musnah'),('KP.02','Perjanjian Kredit & Surat Pengakuan Hutang',2,5,'musnah'),('KP.03','Bukti Agunan / Jaminan Vital (Sertifikat/BPKB)',1,10,'diserahkan'),('KS.01','Kearsipan Simpanan Pokok & Wajib',3,5,'permanen'),('KS.02','Rekening Koran & Riwayat Saldo',2,3,'musnah'),('KU.01','Bukti Kas Masuk / Keluar (Kuitansi/Slip)',1,2,'musnah');
/*!40000 ALTER TABLE `klasifikasi_arsip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lokasi_arsip`
--

DROP TABLE IF EXISTS `lokasi_arsip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lokasi_arsip` (
  `id_lokasi` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_lokasi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gedung` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_rak` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_boks` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_lokasi`),
  UNIQUE KEY `lokasi_arsip_kode_lokasi_unique` (`kode_lokasi`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lokasi_arsip`
--

LOCK TABLES `lokasi_arsip` WRITE;
/*!40000 ALTER TABLE `lokasi_arsip` DISABLE KEYS */;
INSERT INTO `lokasi_arsip` VALUES (1,'BK-01.RF-A.OD-01','Brankas Khazanah','BK-01','OD-01'),(2,'BK-01.RF-A.OD-02','Brankas Khazanah','BK-01','OD-02'),(3,'RK-02.RF-A.OD-04','Gedung Depo Utama','RK-02','OD-04'),(4,'RK-02.RF-B.OD-01','Gedung Depo Utama','RK-02','OD-01');
/*!40000 ALTER TABLE `lokasi_arsip` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_03_114513_create_anggota_table',1),(5,'2026_11_03_114654_create_transaksi_pinjaman_table',1),(6,'2026_11_03_114725_create_klasifikasi_arsip_table',1),(7,'2026_11_03_114753_create_lokasi_arsip_table',1),(8,'2026_11_03_114822_create_arsip_dokumen_table',1),(9,'2026_11_03_114847_create_peminjaman_arsip_table',1),(10,'2026_11_03_114914_create_berita_acara_penyusutan_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `peminjaman_arsip`
--

DROP TABLE IF EXISTS `peminjaman_arsip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `peminjaman_arsip` (
  `id_pinjam` int unsigned NOT NULL AUTO_INCREMENT,
  `id_arsip` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_peminjam` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `divisi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keperluan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali_rencana` date NOT NULL,
  `status_sirkulasi` enum('dipinjam','kembali','terlambat') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_pinjam`),
  KEY `peminjaman_arsip_id_arsip_foreign` (`id_arsip`),
  CONSTRAINT `peminjaman_arsip_id_arsip_foreign` FOREIGN KEY (`id_arsip`) REFERENCES `arsip_dokumen` (`id_arsip`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peminjaman_arsip`
--

LOCK TABLES `peminjaman_arsip` WRITE;
/*!40000 ALTER TABLE `peminjaman_arsip` DISABLE KEYS */;
INSERT INTO `peminjaman_arsip` VALUES (1,'ARC-KP.03/KSP-KRD/2026/0001','Rudi Hartono','Marketing','Verifikasi ulang data agunan','2025-10-20','2025-10-27','terlambat');
/*!40000 ALTER TABLE `peminjaman_arsip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
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
INSERT INTO `sessions` VALUES ('CKn0FYLC56o46DafMzgH5snbnLXFMsecx1897NYZ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJqbDM1eXpzOG9xQ0pPR1NXOGMxWTV5cVVhUkk5b1B5cThVMkZ6N29OIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==',1793878657);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_pinjaman`
--

DROP TABLE IF EXISTS `transaksi_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_pinjaman` (
  `no_rekening_pinjaman` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_anggota` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal_pinjaman` decimal(15,2) NOT NULL,
  `tanggal_akad` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `status_pinjaman` enum('berjalan','lunas','macet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_lunas` date DEFAULT NULL,
  PRIMARY KEY (`no_rekening_pinjaman`),
  KEY `transaksi_pinjaman_id_anggota_foreign` (`id_anggota`),
  CONSTRAINT `transaksi_pinjaman_id_anggota_foreign` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_pinjaman`
--

LOCK TABLES `transaksi_pinjaman` WRITE;
/*!40000 ALTER TABLE `transaksi_pinjaman` DISABLE KEYS */;
INSERT INTO `transaksi_pinjaman` VALUES ('KRD-2022-0007','ANG-202601-0007',20000000.00,'2022-04-01','2024-04-01','lunas','2024-03-30'),('KRD-2023-0005','ANG-202601-0003',100000000.00,'2023-01-10','2025-01-10','lunas','2025-01-10'),('KRD-2023-0006','ANG-202601-0006',30000000.00,'2023-08-01','2025-08-01','lunas','2025-07-28'),('KRD-2024-0001','ANG-202601-0001',150000000.00,'2024-03-01','2026-03-01','macet',NULL),('KRD-2024-0002','ANG-202601-0005',75000000.00,'2024-05-10','2026-05-10','macet',NULL),('KRD-2025-0003','ANG-202601-0002',50000000.00,'2025-02-15','2027-02-15','berjalan',NULL),('KRD-2025-0004','ANG-202601-0004',25000000.00,'2025-06-01','2026-12-01','berjalan',NULL);
/*!40000 ALTER TABLE `transaksi_pinjaman` ENABLE KEYS */;
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
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('petugas','pimpinan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'petugas',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Petugas Arsip','petugas@earsip.test',NULL,'$2y$12$oSN8Cui9vfBKxQcKFjQ7KOzNYDeKp.Ky5m80FXWblY18aw0UmJvl6','petugas',NULL,'2026-11-05 11:07:14','2026-11-05 11:07:14'),(2,'Kepala Bagian Kredit','pimpinan@earsip.test',NULL,'$2y$12$jgHuCBWER1kUqY.Fi6QNCeEQeXaRCOgYYpNsnwXevujmVHA6uoF2u','pimpinan',NULL,'2026-11-05 11:07:14','2026-11-05 11:07:14');
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

-- Dump completed on 2026-11-05 18:52:43
