-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 10 Haz 2025, 21:56:54
-- Sunucu sürümü: 10.4.21-MariaDB
-- PHP Sürümü: 7.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `eka_lisans_sistemi`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ayarlar`
--

CREATE TABLE `ayarlar` (
  `id` int(11) NOT NULL,
  `anahtar` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `deger` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `aciklama` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL,
  `guncelleme_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `ad` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `soyad` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  `sifre` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  `telefon` varchar(20) COLLATE utf8_turkish_ci DEFAULT NULL,
  `sirket` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL,
  `rol` enum('admin','kullanici') COLLATE utf8_turkish_ci DEFAULT 'kullanici',
  `durum` enum('aktif','pasif') COLLATE utf8_turkish_ci DEFAULT 'aktif',
  `kayit_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `ad`, `soyad`, `email`, `sifre`, `telefon`, `sirket`, `rol`, `durum`, `kayit_tarihi`) VALUES
(1, 'Admin', 'Kullanıcı', 'admin@ekayazilim.com', '$2y$10$sNxNRz/SVe4UaoeMXHhoY.36lfwNk/r60UiemYmdqzzdqvK3IDrjK', '08503073458', 'eka sunucu', 'admin', 'aktif', '2025-06-10 17:54:14'),
(2, 'eka', 'sunucu', 'ekasunucu@gmail.com', '$2y$10$EpmxP48fp8ohRlxMMGGG0elMqYFqdaIrbLkTji4orLm5R9YWOZwoK', '08503073458', 'eka yazılım', 'admin', 'aktif', '2025-06-10 17:54:56');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `lisanslar`
--

CREATE TABLE `lisanslar` (
  `id` int(11) NOT NULL,
  `lisans_anahtari` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  `anahtar` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  `kullanici_id` int(11) NOT NULL,
  `urun_id` int(11) NOT NULL,
  `baslangic_tarihi` date NOT NULL,
  `bitis_tarihi` date NOT NULL,
  `max_kullanim` int(11) DEFAULT 1,
  `ip_kisitlama` tinyint(1) DEFAULT 0,
  `mac_kisitlama` tinyint(1) DEFAULT 0,
  `aciklama` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8_turkish_ci DEFAULT '',
  `kullanim_sayisi` int(11) DEFAULT 0,
  `durum` enum('aktif','pasif','suresi_dolmus') COLLATE utf8_turkish_ci DEFAULT 'aktif',
  `ip_adresi` varchar(45) COLLATE utf8_turkish_ci DEFAULT NULL,
  `mac_adresi` varchar(17) COLLATE utf8_turkish_ci DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `lisanslar`
--

INSERT INTO `lisanslar` (`id`, `lisans_anahtari`, `anahtar`, `kullanici_id`, `urun_id`, `baslangic_tarihi`, `bitis_tarihi`, `max_kullanim`, `ip_kisitlama`, `mac_kisitlama`, `aciklama`, `domain`, `kullanim_sayisi`, `durum`, `ip_adresi`, `mac_adresi`, `olusturma_tarihi`) VALUES
(1, 'EKA-4C357885C4AAB12C-A9A34FD64B0E6A68', '', 2, 1, '2025-06-29', '2026-06-10', 1, 0, 0, NULL, '', 0, 'aktif', NULL, NULL, '2025-06-10 18:25:51'),
(2, 'EKA-28EB2BEEF2F59660-771D5E51B6AD1133', '', 2, 1, '2025-06-29', '2026-06-10', 1, 0, 0, NULL, '', 0, 'aktif', NULL, NULL, '2025-06-10 18:26:02'),
(3, 'EKA-F7C436771596690D-9B93F195DF79C6FA', '', 2, 1, '2025-06-10', '2099-12-31', 1, 0, 0, 'x', 'localhost', 1, 'aktif', '::1', '34-2E-B7-03-B0-33', '2025-06-10 18:30:58');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `lisans_loglar`
--

CREATE TABLE `lisans_loglar` (
  `id` int(11) NOT NULL,
  `lisans_id` int(11) NOT NULL,
  `islem_tipi` enum('dogrulama','aktivasyon','deaktivasyon','hata') COLLATE utf8_turkish_ci NOT NULL,
  `ip_adresi` varchar(45) COLLATE utf8_turkish_ci DEFAULT NULL,
  `mac_adresi` varchar(17) COLLATE utf8_turkish_ci DEFAULT NULL,
  `detay` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `tarih` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `lisans_loglari`
--

CREATE TABLE `lisans_loglari` (
  `id` int(11) NOT NULL,
  `tarih` datetime NOT NULL DEFAULT current_timestamp(),
  `islem_turu` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `durum` enum('BASARILI','HATA','GECERSIZ','UYARI') COLLATE utf8mb4_unicode_ci NOT NULL,
  `lisans_anahtari` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_adresi` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mac_adresi` varchar(17) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `script_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_root` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `php_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os_info` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_uri` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_referer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hata_mesaji` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ek_bilgiler` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ek_bilgiler`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunler`
--

CREATE TABLE `urunler` (
  `id` int(11) NOT NULL,
  `ad` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  `aciklama` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `versiyon` varchar(50) COLLATE utf8_turkish_ci NOT NULL,
  `fiyat` decimal(10,2) NOT NULL,
  `durum` enum('aktif','pasif') COLLATE utf8_turkish_ci DEFAULT 'aktif',
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `urunler`
--

INSERT INTO `urunler` (`id`, `ad`, `aciklama`, `versiyon`, `fiyat`, `durum`, `olusturma_tarihi`) VALUES
(1, 'x', '1', '1', '1.00', 'aktif', '2025-06-10 18:25:35');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `ayarlar`
--
ALTER TABLE `ayarlar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `anahtar` (`anahtar`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `lisanslar`
--
ALTER TABLE `lisanslar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lisans_anahtari` (`lisans_anahtari`),
  ADD KEY `kullanici_id` (`kullanici_id`),
  ADD KEY `urun_id` (`urun_id`);

--
-- Tablo için indeksler `lisans_loglar`
--
ALTER TABLE `lisans_loglar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lisans_id` (`lisans_id`);

--
-- Tablo için indeksler `lisans_loglari`
--
ALTER TABLE `lisans_loglari`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarih` (`tarih`),
  ADD KEY `idx_durum` (`durum`),
  ADD KEY `idx_lisans` (`lisans_anahtari`),
  ADD KEY `idx_domain` (`domain`),
  ADD KEY `idx_ip` (`ip_adresi`);

--
-- Tablo için indeksler `urunler`
--
ALTER TABLE `urunler`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `ayarlar`
--
ALTER TABLE `ayarlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `lisanslar`
--
ALTER TABLE `lisanslar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `lisans_loglar`
--
ALTER TABLE `lisans_loglar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `lisans_loglari`
--
ALTER TABLE `lisans_loglari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Tablo için AUTO_INCREMENT değeri `urunler`
--
ALTER TABLE `urunler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `lisanslar`
--
ALTER TABLE `lisanslar`
  ADD CONSTRAINT `lisanslar_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lisanslar_ibfk_2` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `lisans_loglar`
--
ALTER TABLE `lisans_loglar`
  ADD CONSTRAINT `lisans_loglar_ibfk_1` FOREIGN KEY (`lisans_id`) REFERENCES `lisanslar` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
