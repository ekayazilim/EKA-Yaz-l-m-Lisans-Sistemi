<?php

class EkaLisansKontrol {
    private $lisansAnahtari;
    private $apiUrl;
    private $domain;
    private $logDosyasi;
    private $dbConfig;
    private $debug = false;
    
    public function __construct($lisansAnahtari, $apiUrl = 'http://localhost/api/lisans-dogrula.php') {
        $this->lisansAnahtari = $lisansAnahtari;
        $this->apiUrl = $apiUrl;
        $this->domain = $this->getDomain();
        $this->logDosyasi = __DIR__ . '/lisans_log.txt';
        
        // Veritabanı konfigürasyonu
        $this->dbConfig = [
            'host' => 'localhost',
            'dbname' => 'eka_lisans_sistemi',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ];
        

    }
    
    public function lisansKontrolEt() {
        $sistemBilgileri = $this->sistemBilgileriTopla();
        
        // Önce veritabanından direkt kontrol et
        $veritabaniSonuc = $this->veritabaniLisansKontrol();
        
        if ($veritabaniSonuc['durum']) {
            $this->basariliLogla($sistemBilgileri);
            $this->veritabaniLogla('DOGRULAMA', 'BASARILI', null, $sistemBilgileri);
            return true;
        }
        
        // Veritabanında bulunamadıysa API'yi dene
        $this->veritabaniBaglantiTest();
        
        $postData = [
            'lisans_anahtari' => $this->lisansAnahtari,
            'domain' => $this->domain,
            'ip_adresi' => $sistemBilgileri['ip'],
            'mac_adresi' => $sistemBilgileri['mac'],
            'sistem_bilgileri' => json_encode($sistemBilgileri)
        ];
        
        $sonuc = $this->apiCagriYap($postData);
        
        if (!$sonuc['durum']) {
            $this->hataLogla($veritabaniSonuc['mesaj'] ?: $sonuc['mesaj'], $sistemBilgileri);
            $this->veritabaniLogla('DOGRULAMA', 'HATA', $veritabaniSonuc['mesaj'] ?: $sonuc['mesaj'], $sistemBilgileri);
            $this->lisansHatasi($veritabaniSonuc['mesaj'] ?: $sonuc['mesaj']);
            return false;
        }
        
        $this->basariliLogla($sistemBilgileri);
        $this->veritabaniLogla('DOGRULAMA', 'BASARILI', null, $sistemBilgileri);
        return true;
    }
    
    // Veritabanından direkt lisans kontrolü
    private function veritabaniLisansKontrol() {
        try {
            $dsn = "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['dbname']};charset={$this->dbConfig['charset']}";
            $pdo = new PDO($dsn, $this->dbConfig['username'], $this->dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->dbConfig['charset']}"
            ]);
            
            // Lisans anahtarını ve domain'i kontrol et
            $sql = "SELECT l.*, u.ad as urun_adi, k.ad as kullanici_adi 
                    FROM lisanslar l 
                    LEFT JOIN urunler u ON l.urun_id = u.id 
                    LEFT JOIN kullanicilar k ON l.kullanici_id = k.id 
                    WHERE l.lisans_anahtari = ? AND l.durum = 'aktif' 
                    AND (l.domain = ? OR l.domain = '*' OR l.domain IS NULL)
                    AND l.baslangic_tarihi <= NOW() 
                    AND l.bitis_tarihi >= NOW()";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$this->lisansAnahtari, $this->domain]);
            $lisans = $stmt->fetch();
            
            if ($lisans) {
                return ['durum' => true, 'mesaj' => 'Lisans geçerli', 'lisans' => $lisans];
            } else {
                // Lisans anahtarı var mı kontrol et
                $sql2 = "SELECT * FROM lisanslar WHERE lisans_anahtari = ?";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([$this->lisansAnahtari]);
                $mevcutLisans = $stmt2->fetch();
                
                if ($mevcutLisans) {
                    if ($mevcutLisans['durum'] !== 'aktif') {
                        return ['durum' => false, 'mesaj' => 'Lisans aktif değil'];
                    }
                    if ($mevcutLisans['domain'] && $mevcutLisans['domain'] !== '*' && $mevcutLisans['domain'] !== $this->domain) {
                        return ['durum' => false, 'mesaj' => 'Domain eşleşmiyor. Beklenen: ' . $mevcutLisans['domain'] . ', Mevcut: ' . $this->domain];
                    }
                    if ($mevcutLisans['baslangic_tarihi'] > date('Y-m-d H:i:s')) {
                        return ['durum' => false, 'mesaj' => 'Lisans henüz başlamamış'];
                    }
                    if ($mevcutLisans['bitis_tarihi'] < date('Y-m-d H:i:s')) {
                        return ['durum' => false, 'mesaj' => 'Lisans süresi dolmuş'];
                    }
                }
                
                return ['durum' => false, 'mesaj' => 'Geçersiz lisans anahtarı'];
            }
            
        } catch (PDOException $e) {
            return ['durum' => false, 'mesaj' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()];
        }
    }
    
    // Veritabanı bağlantı testi
    private function veritabaniBaglantiTest() {
        try {
            $dsn = "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['dbname']};charset={$this->dbConfig['charset']}";
            $pdo = new PDO($dsn, $this->dbConfig['username'], $this->dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->dbConfig['charset']}"
            ]);
            
            // Tablo varlığını kontrol et
            $stmt = $pdo->query("SHOW TABLES LIKE 'lisans_loglari'");
            if ($stmt->rowCount() > 0) {
                // Tablo mevcut
            } else {
                // Tablo yok
            }
            
            return true;
        } catch (PDOException $e) {

            return false;
        }
    }
    
    private function getDomain() {
        if (isset($_SERVER['HTTP_HOST'])) {
            return str_replace('www.', '', $_SERVER['HTTP_HOST']);
        }
        return 'localhost';
    }
    
    private function sistemBilgileriTopla() {
        $bilgiler = [
            'domain' => $this->domain,
            'ip' => $this->getClientIP(),
            'mac' => $this->getMacAddress(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Bilinmiyor',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Bilinmiyor',
            'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'Bilinmiyor',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Bilinmiyor',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'Bilinmiyor',
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Bilinmiyor',
            'http_referer' => $_SERVER['HTTP_REFERER'] ?? 'Yok'
        ];
        

        
        return $bilgiler;
    }
    
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    private function getMacAddress() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec('getmac /fo csv /nh');
            if ($output) {
                $lines = explode("\n", trim($output));
                if (isset($lines[0])) {
                    $parts = str_getcsv($lines[0]);
                    return isset($parts[0]) ? trim($parts[0], '"') : 'Bilinmiyor';
                }
            }
        } else {
            $output = shell_exec('cat /sys/class/net/*/address 2>/dev/null | head -1');
            if ($output) {
                return trim($output);
            }
        }
        
        return 'Bilinmiyor';
    }
    
    private function apiCagriYap($postData) {

        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'EKA Lisans Kontrol v1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        

        
        if (curl_error($ch) || $httpCode !== 200) {
            curl_close($ch);
            return ['durum' => false, 'mesaj' => 'API bağlantı hatası'];
        }
        
        curl_close($ch);
        
        $sonuc = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['durum' => false, 'mesaj' => 'API yanıt formatı hatası'];
        }
        
        return $sonuc;
    }
    
    private function veritabaniLogla($islemTuru, $durum, $hataMesaji = null, $sistemBilgileri = []) {
        
        try {
            $dsn = "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['dbname']};charset={$this->dbConfig['charset']}";
            $pdo = new PDO($dsn, $this->dbConfig['username'], $this->dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->dbConfig['charset']}"
            ]);
            
            $sql = "INSERT INTO lisans_loglari (
                tarih, islem_turu, durum, lisans_anahtari, domain, ip_adresi,
                mac_adresi, user_agent, server_name, script_path, document_root,
                php_version, os_info, request_uri, http_referer, hata_mesaji, ek_bilgiler
            ) VALUES (
                :tarih, :islem_turu, :durum, :lisans_anahtari, :domain, :ip_adresi,
                :mac_adresi, :user_agent, :server_name, :script_path, :document_root,
                :php_version, :os_info, :request_uri, :http_referer, :hata_mesaji, :ek_bilgiler
            )";
            
            $data = [
                'tarih' => date('Y-m-d H:i:s'),
                'islem_turu' => $islemTuru,
                'durum' => $durum,
                'lisans_anahtari' => $this->lisansAnahtari,
                'domain' => $this->domain,
                'ip_adresi' => $sistemBilgileri['ip'] ?? '',
                'mac_adresi' => $sistemBilgileri['mac'] ?? '',
                'user_agent' => $sistemBilgileri['user_agent'] ?? '',
                'server_name' => $sistemBilgileri['server_name'] ?? '',
                'script_path' => $sistemBilgileri['script_name'] ?? '',
                'document_root' => $sistemBilgileri['document_root'] ?? '',
                'php_version' => $sistemBilgileri['php_version'] ?? PHP_VERSION,
                'os_info' => $sistemBilgileri['os'] ?? PHP_OS,
                'request_uri' => $sistemBilgileri['request_uri'] ?? '',
                'http_referer' => $sistemBilgileri['http_referer'] ?? '',
                'hata_mesaji' => $hataMesaji,
                'ek_bilgiler' => !empty($sistemBilgileri) ? json_encode($sistemBilgileri, JSON_UNESCAPED_UNICODE) : null
            ];
            

            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($data);
            

            
        } catch (PDOException $e) {

            // Veritabanı hatası durumunda dosyaya hata logla
            $this->dbHataLogla($e->getMessage());
        }
    }
    
    // Veritabanı hatalarını ayrı dosyaya logla
    private function dbHataLogla($hataMesaji) {
        $dbLogDosyasi = __DIR__ . '/db_error.log';
        $logMesaji = "[" . date('Y-m-d H:i:s') . "] VERİTABANI HATASI: " . $hataMesaji . "\n";
        file_put_contents($dbLogDosyasi, $logMesaji, FILE_APPEND | LOCK_EX);
        

    }
    
    private function hataLogla($hata, $sistemBilgileri) {
        $logMesaji = "[" . date('Y-m-d H:i:s') . "] LISANS HATASI\n";
        $logMesaji .= "Hata: " . $hata . "\n";
        $logMesaji .= "Domain: " . $this->domain . "\n";
        $logMesaji .= "Lisans Anahtarı: " . $this->lisansAnahtari . "\n";
        $logMesaji .= "IP Adresi: " . $sistemBilgileri['ip'] . "\n";
        $logMesaji .= "MAC Adresi: " . $sistemBilgileri['mac'] . "\n";
        $logMesaji .= "User Agent: " . $sistemBilgileri['user_agent'] . "\n";
        $logMesaji .= "Server: " . $sistemBilgileri['server_name'] . "\n";
        $logMesaji .= "Script: " . $sistemBilgileri['script_name'] . "\n";
        $logMesaji .= "Document Root: " . $sistemBilgileri['document_root'] . "\n";
        $logMesaji .= "PHP Version: " . $sistemBilgileri['php_version'] . "\n";
        $logMesaji .= "OS: " . $sistemBilgileri['os'] . "\n";
        $logMesaji .= "Request URI: " . $sistemBilgileri['request_uri'] . "\n";
        $logMesaji .= "HTTP Referer: " . $sistemBilgileri['http_referer'] . "\n";
        $logMesaji .= str_repeat('-', 80) . "\n\n";
        
        file_put_contents($this->logDosyasi, $logMesaji, FILE_APPEND | LOCK_EX);
        

    }
    
    private function basariliLogla($sistemBilgileri) {
        $logMesaji = "[" . date('Y-m-d H:i:s') . "] BAŞARILI DOĞRULAMA\n";
        $logMesaji .= "Domain: " . $this->domain . "\n";
        $logMesaji .= "Lisans Anahtarı: " . $this->lisansAnahtari . "\n";
        $logMesaji .= "IP Adresi: " . $sistemBilgileri['ip'] . "\n";
        $logMesaji .= str_repeat('-', 40) . "\n\n";
        
        file_put_contents($this->logDosyasi, $logMesaji, FILE_APPEND | LOCK_EX);
        

    }
    
    private function lisansHatasi($mesaj) {
        http_response_code(403);
        
        $hataHtml = '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisans Hatası</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 50px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); text-align: center; }
        .error-icon { font-size: 64px; color: #dc3545; margin-bottom: 20px; }
        h1 { color: #dc3545; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; margin-bottom: 30px; }
        .info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .domain { font-weight: bold; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">🔒</div>
        <h1>Lisans Hatası</h1>
        <p><strong>Hata:</strong> ' . htmlspecialchars($mesaj) . '</p>
        <div class="info">
            <p><strong>Domain:</strong> <span class="domain">' . htmlspecialchars($this->domain) . '</span></p>
            <p><strong>Tarih:</strong> ' . date('d.m.Y H:i:s') . '</p>
        </div>
        <p>Bu uygulama geçerli bir lisans gerektirir. Lütfen sistem yöneticinizle iletişime geçin.</p>
    </div>
</body>
</html>';
        
        echo $hataHtml;
        exit;
    }
    
    // Veritabanı ayarlarını güncelleme fonksiyonu
    public function veritabaniAyarlariGuncelle($host, $dbname, $username, $password, $charset = 'utf8mb4') {
        $this->dbConfig = [
            'host' => $host,
            'dbname' => $dbname,
            'username' => $username,
            'password' => $password,
            'charset' => $charset
        ];
        

    }
    
    // Debug modunu açma/kapama
    public function debugModu($durum) {
        $this->debug = $durum;
    }
}

function ekaLisansKontrol($lisansAnahtari, $apiUrl = 'http://localhost/api/lisans-dogrula.php') {
    $kontrol = new EkaLisansKontrol($lisansAnahtari, $apiUrl);
    return $kontrol->lisansKontrolEt();
}

// Veritabanı ayarları ile lisans kontrolü
function ekaLisansKontrolDB($lisansAnahtari, $apiUrl, $dbHost, $dbName, $dbUser, $dbPass) {
    $kontrol = new EkaLisansKontrol($lisansAnahtari, $apiUrl);
    $kontrol->veritabaniAyarlariGuncelle($dbHost, $dbName, $dbUser, $dbPass);
    return $kontrol->lisansKontrolEt();
}

?>