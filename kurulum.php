<?php
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kurulum_baslat'])) {
    require_once 'config/veritabani.php';
    
    try {
        $veritabani = new EkaVeritabani();
        $baglanti = $veritabani->baglantiGetir();
        
        // Kullanıcılar tablosu
        $kullanicilarTablosu = "
        CREATE TABLE IF NOT EXISTS kullanicilar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ad VARCHAR(100) NOT NULL,
            soyad VARCHAR(100) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            telefon VARCHAR(20),
            sirket VARCHAR(255),
            sifre VARCHAR(255) NOT NULL,
            rol ENUM('admin', 'kullanici') DEFAULT 'kullanici',
            durum ENUM('aktif', 'pasif') DEFAULT 'aktif',
            kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        // Ürünler tablosu
        $urunlerTablosu = "
        CREATE TABLE IF NOT EXISTS urunler (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ad VARCHAR(255) NOT NULL,
            aciklama TEXT,
            versiyon VARCHAR(50),
            fiyat DECIMAL(10,2) DEFAULT 0.00,
            durum ENUM('aktif', 'pasif') DEFAULT 'aktif',
            olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        // Lisanslar tablosu
        $lisanslarTablosu = "
        CREATE TABLE IF NOT EXISTS lisanslar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lisans_anahtari VARCHAR(255) UNIQUE NOT NULL,
            kullanici_id INT NOT NULL,
            urun_id INT NOT NULL,
            domain VARCHAR(255),
            ip_adresi VARCHAR(45),
            mac_adresi VARCHAR(17),
            baslangic_tarihi DATE NOT NULL,
            bitis_tarihi DATE NOT NULL,
            max_kullanim INT DEFAULT 1,
            kullanim_sayisi INT DEFAULT 0,
            durum ENUM('aktif', 'pasif', 'suresi_dolmus') DEFAULT 'aktif',
            olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
            FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        // Lisans logları tablosu
        $lisansLoglarTablosu = "
        CREATE TABLE IF NOT EXISTS lisans_loglar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lisans_id INT,
            islem_tipi VARCHAR(50) NOT NULL,
            ip_adresi VARCHAR(45),
            mac_adresi VARCHAR(17),
            domain VARCHAR(255),
            durum BOOLEAN DEFAULT FALSE,
            mesaj TEXT,
            islem_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (lisans_id) REFERENCES lisanslar(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        // Ayarlar tablosu
        $ayarlarTablosu = "
        CREATE TABLE IF NOT EXISTS ayarlar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            anahtar VARCHAR(100) UNIQUE NOT NULL,
            deger TEXT,
            aciklama TEXT,
            guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        // Tabloları oluştur
        $baglanti->exec($kullanicilarTablosu);
        $baglanti->exec($urunlerTablosu);
        $baglanti->exec($lisanslarTablosu);
        $baglanti->exec($lisansLoglarTablosu);
        $baglanti->exec($ayarlarTablosu);
        
        // İndeksleri oluştur
        $indeksler = [
            "CREATE INDEX IF NOT EXISTS idx_lisans_anahtari ON lisanslar(lisans_anahtari)",
            "CREATE INDEX IF NOT EXISTS idx_kullanici_email ON kullanicilar(email)",
            "CREATE INDEX IF NOT EXISTS idx_lisans_durum ON lisanslar(durum)",
            "CREATE INDEX IF NOT EXISTS idx_log_tarih ON lisans_loglar(islem_tarihi)"
        ];
        
        foreach ($indeksler as $indeks) {
            $baglanti->exec($indeks);
        }
        
        // Varsayılan admin kullanıcısı kontrol et
        $adminKontrol = $baglanti->prepare("SELECT COUNT(*) FROM kullanicilar WHERE rol = 'admin'");
        $adminKontrol->execute();
        
        if ($adminKontrol->fetchColumn() == 0) {
            // Admin kullanıcısı oluştur
            $adminEkle = $baglanti->prepare("
                INSERT INTO kullanicilar (ad, soyad, email, telefon, sirket, sifre, rol, durum) 
                VALUES (?, ?, ?, ?, ?, ?, 'admin', 'aktif')
            ");
            
            $adminSifre = password_hash('admin123', PASSWORD_DEFAULT);
            $adminEkle->execute([
                'Admin',
                'Kullanıcı',
                'admin@ekayazilim.com',
                '+90 555 123 45 67',
                'EKA Yazılım',
                $adminSifre
            ]);
        }
        
        // Örnek ürün ekle
        $urunKontrol = $baglanti->prepare("SELECT COUNT(*) FROM urunler");
        $urunKontrol->execute();
        
        if ($urunKontrol->fetchColumn() == 0) {
            $ornekUrun = $baglanti->prepare("
                INSERT INTO urunler (ad, aciklama, versiyon, fiyat, durum) 
                VALUES (?, ?, ?, ?, 'aktif')
            ");
            
            $ornekUrun->execute([
                'EKA Yazılım v1.0',
                'Örnek yazılım ürünü - Lisans sistemi demo',
                '1.0.0',
                99.99
            ]);
        }
        
        // Temel ayarları ekle
        $ayarKontrol = $baglanti->prepare("SELECT COUNT(*) FROM ayarlar");
        $ayarKontrol->execute();
        
        if ($ayarKontrol->fetchColumn() == 0) {
            $temelAyarlar = [
                ['site_adi', 'EKA Yazılım Lisans Sistemi', 'Site başlığı'],
                ['site_aciklama', 'Profesyonel lisans yönetim sistemi', 'Site açıklaması'],
                ['admin_email', 'admin@ekayazilim.com', 'Yönetici e-posta adresi'],
                ['lisans_suresi_varsayilan', '365', 'Varsayılan lisans süresi (gün)'],
                ['max_kullanim_varsayilan', '1', 'Varsayılan maksimum kullanım sayısı']
            ];
            
            $ayarEkle = $baglanti->prepare("
                INSERT INTO ayarlar (anahtar, deger, aciklama) VALUES (?, ?, ?)
            ");
            
            foreach ($temelAyarlar as $ayar) {
                $ayarEkle->execute($ayar);
            }
        }
        
        $kurulumBasarili = true;
        $mesaj = "Kurulum başarıyla tamamlandı!";
        
    } catch (Exception $e) {
        $kurulumBasarili = false;
        $mesaj = "Kurulum hatası: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EKA Yazılım Lisans Sistemi - Kurulum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .kurulum-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .kurulum-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .kurulum-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .kurulum-body {
            padding: 40px;
        }
        .gereksinim {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 10px 0;
        }
        .basarili {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        .hata {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        .adim {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="kurulum-container">
        <div class="kurulum-card">
            <div class="kurulum-header">
                <h1><i class="fas fa-cogs me-3"></i>EKA Yazılım Lisans Sistemi</h1>
                <p class="mb-0">Kurulum Sihirbazı</p>
            </div>
            
            <div class="kurulum-body">
                <?php if (isset($kurulumBasarili)): ?>
                    <div class="gereksinim <?php echo $kurulumBasarili ? 'basarili' : 'hata'; ?>">
                        <h5><i class="fas <?php echo $kurulumBasarili ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                            <?php echo $kurulumBasarili ? 'Kurulum Tamamlandı' : 'Kurulum Hatası'; ?>
                        </h5>
                        <p class="mb-0"><?php echo $mesaj; ?></p>
                        
                        <?php if ($kurulumBasarili): ?>
                            <hr>
                            <h6><i class="fas fa-user-shield me-2"></i>Varsayılan Admin Hesabı:</h6>
                            <ul class="mb-0">
                                <li><strong>E-posta:</strong> admin@ekayazilim.com</li>
                                <li><strong>Şifre:</strong> admin123</li>
                            </ul>
                            <div class="mt-3">
                                <a href="panel/dashboard.php" class="btn btn-success me-2">
                                    <i class="fas fa-tachometer-alt me-2"></i>Admin Paneline Git
                                </a>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Ana Sayfaya Git
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <h3><i class="fas fa-info-circle me-2 text-primary"></i>Kurulum Öncesi Kontroller</h3>
                    
                    <div class="adim">
                        <h5><i class="fas fa-server me-2"></i>1. Sistem Gereksinimleri</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="gereksinim <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'basarili' : 'hata'; ?>">
                                    <strong>PHP Sürümü:</strong> <?php echo PHP_VERSION; ?>
                                    <small class="d-block">Gerekli: 7.4 veya üzeri</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="gereksinim <?php echo extension_loaded('pdo') ? 'basarili' : 'hata'; ?>">
                                    <strong>PDO:</strong> <?php echo extension_loaded('pdo') ? 'Yüklü' : 'Yüklü Değil'; ?>
                                    <small class="d-block">Veritabanı bağlantısı için gerekli</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="gereksinim <?php echo extension_loaded('pdo_mysql') ? 'basarili' : 'hata'; ?>">
                                    <strong>PDO MySQL:</strong> <?php echo extension_loaded('pdo_mysql') ? 'Yüklü' : 'Yüklü Değil'; ?>
                                    <small class="d-block">MySQL veritabanı için gerekli</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="gereksinim <?php echo extension_loaded('json') ? 'basarili' : 'hata'; ?>">
                                    <strong>JSON:</strong> <?php echo extension_loaded('json') ? 'Yüklü' : 'Yüklü Değil'; ?>
                                    <small class="d-block">API işlemleri için gerekli</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="adim">
                        <h5><i class="fas fa-database me-2"></i>2. Veritabanı Ayarları</h5>
                        <p>Kurulum başlamadan önce <code>config/veritabani.php</code> dosyasındaki veritabanı ayarlarını kontrol edin:</p>
                        <div class="gereksinim">
                            <pre><code>private static $sunucu = 'localhost';
private static $kullanici = 'root';
private static $sifre = '';
private static $veritabani = 'eka_lisans_sistemi';</code></pre>
                        </div>
                    </div>
                    
                    <div class="adim">
                        <h5><i class="fas fa-list-check me-2"></i>3. Kurulum İşlemleri</h5>
                        <p>Kurulum sırasında aşağıdaki işlemler gerçekleştirilecek:</p>
                        <ul>
                            <li><i class="fas fa-table me-2 text-primary"></i>Veritabanı tabloları oluşturulacak</li>
                            <li><i class="fas fa-index me-2 text-primary"></i>Performans indeksleri eklenecek</li>
                            <li><i class="fas fa-user-shield me-2 text-primary"></i>Varsayılan admin hesabı oluşturulacak</li>
                            <li><i class="fas fa-box me-2 text-primary"></i>Örnek ürün eklenecek</li>
                            <li><i class="fas fa-cog me-2 text-primary"></i>Temel sistem ayarları yapılacak</li>
                        </ul>
                    </div>
                    
                    <div class="text-center mt-4">
                        <form method="POST">
                            <button type="submit" name="kurulum_baslat" class="btn btn-primary btn-lg">
                                <i class="fas fa-play me-2"></i>Kurulumu Başlat
                            </button>
                        </form>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Uyarı:</strong> Kurulum işlemi mevcut verileri etkileyebilir. Devam etmeden önce veritabanınızı yedeklediğinizden emin olun.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-white">
                <i class="fas fa-code me-2"></i>
                EKA Yazılım © 2024 - Profesyonel Yazılım Çözümleri
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>