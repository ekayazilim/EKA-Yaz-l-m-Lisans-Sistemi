<?php
class EkaVeritabani {
    private static $baglanti = null;
    private static $sunucu = 'localhost';
    private static $kullanici = 'root';
    private static $sifre = '';
    private static $veritabani = 'eka_lisans_sistemi';
    
    public static function baglantiGetir() {
        if (self::$baglanti === null) {
            try {
                self::$baglanti = new PDO(
                    'mysql:host=' . self::$sunucu . ';dbname=' . self::$veritabani . ';charset=utf8',
                    self::$kullanici,
                    self::$sifre,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die('Veritabanı bağlantı hatası: ' . $e->getMessage());
            }
        }
        return self::$baglanti;
    }
    
    public static function veritabaniOlustur() {
        try {
            $baglanti = new PDO(
                'mysql:host=' . self::$sunucu . ';charset=utf8',
                self::$kullanici,
                self::$sifre
            );
            
            $baglanti->exec("CREATE DATABASE IF NOT EXISTS " . self::$veritabani . " CHARACTER SET utf8 COLLATE utf8_turkish_ci");
            
            $baglanti = null;
            
            $db = self::baglantiGetir();
            
            $tablolar = [
                "CREATE TABLE IF NOT EXISTS kullanicilar (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ad VARCHAR(100) NOT NULL,
                    soyad VARCHAR(100) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    sifre VARCHAR(255) NOT NULL,
                    telefon VARCHAR(20),
                    sirket VARCHAR(255),
                    rol ENUM('admin', 'kullanici') DEFAULT 'kullanici',
                    durum ENUM('aktif', 'pasif') DEFAULT 'aktif',
                    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci",
                
                "CREATE TABLE IF NOT EXISTS urunler (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ad VARCHAR(255) NOT NULL,
                    aciklama TEXT,
                    versiyon VARCHAR(50) NOT NULL,
                    fiyat DECIMAL(10,2) NOT NULL,
                    durum ENUM('aktif', 'pasif') DEFAULT 'aktif',
                    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci",
                
                "CREATE TABLE IF NOT EXISTS lisanslar (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    lisans_anahtari VARCHAR(255) UNIQUE NOT NULL,
                    kullanici_id INT NOT NULL,
                    urun_id INT NOT NULL,
                    baslangic_tarihi DATE NOT NULL,
                    bitis_tarihi DATE NOT NULL,
                    max_kullanim INT DEFAULT 1,
                    kullanim_sayisi INT DEFAULT 0,
                    durum ENUM('aktif', 'pasif', 'suresi_dolmus') DEFAULT 'aktif',
                    ip_adresi VARCHAR(45),
                    mac_adresi VARCHAR(17),
                    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
                    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci",
                
                "CREATE TABLE IF NOT EXISTS lisans_loglar (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    lisans_id INT NOT NULL,
                    islem_tipi ENUM('dogrulama', 'aktivasyon', 'deaktivasyon', 'hata') NOT NULL,
                    ip_adresi VARCHAR(45),
                    mac_adresi VARCHAR(17),
                    detay TEXT,
                    tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (lisans_id) REFERENCES lisanslar(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci",
                
                "CREATE TABLE IF NOT EXISTS ayarlar (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    anahtar VARCHAR(100) UNIQUE NOT NULL,
                    deger TEXT,
                    aciklama VARCHAR(255),
                    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci"
            ];
            
            foreach ($tablolar as $tablo) {
                $db->exec($tablo);
            }
            
            $adminVarMi = $db->query("SELECT COUNT(*) FROM kullanicilar WHERE rol = 'admin'")->fetchColumn();
            
            if ($adminVarMi == 0) {
                $adminSifre = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO kullanicilar (ad, soyad, email, sifre, rol) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['Admin', 'Kullanıcı', 'admin@ekayazilim.com', $adminSifre, 'admin']);
            }
            
            return true;
        } catch (PDOException $e) {
            die('Veritabanı oluşturma hatası: ' . $e->getMessage());
        }
    }
}

EkaVeritabani::veritabaniOlustur();
?>
