<?php
require_once __DIR__ . '/../config/veritabani.php';

class EkaLisansYoneticisi {
    private $db;
    
    public function __construct() {
        $this->db = EkaVeritabani::baglantiGetir();
    }
    
    public function lisansOlustur($kullaniciId, $urunId, $baslangicTarihi, $bitisTarihi, $maxKullanim = 1, $ipKisitlama = '', $macKisitlama = '', $aciklama = '', $domain = '') {
        try {
            $lisansAnahtari = $this->benzersizLisansAnahtariOlustur();
            
            $stmt = $this->db->prepare("
                INSERT INTO lisanslar (lisans_anahtari, kullanici_id, urun_id, baslangic_tarihi, bitis_tarihi, max_kullanim, ip_kisitlama, mac_kisitlama, aciklama, domain) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$lisansAnahtari, $kullaniciId, $urunId, $baslangicTarihi, $bitisTarihi, $maxKullanim, $ipKisitlama, $macKisitlama, $aciklama, $domain]);
            
            return [
                'durum' => true,
                'lisans_anahtari' => $lisansAnahtari,
                'mesaj' => 'Lisans başarıyla oluşturuldu'
            ];
        } catch (PDOException $e) {
            return [
                'durum' => false,
                'lisans_anahtari' => '',
                'mesaj' => 'Lisans oluşturulurken hata: ' . $e->getMessage()
            ];
        }
    }
    
    public function lisansDogrula($lisansAnahtari, $ipAdresi = null, $macAdresi = null) {
        try {
            $stmt = $this->db->prepare("
                SELECT l.*, u.ad as urun_adi, k.ad, k.soyad, k.email 
                FROM lisanslar l 
                JOIN urunler u ON l.urun_id = u.id 
                JOIN kullanicilar k ON l.kullanici_id = k.id 
                WHERE l.lisans_anahtari = ?
            ");
            
            $stmt->execute([$lisansAnahtari]);
            $lisans = $stmt->fetch();
            
            if (!$lisans) {
                $this->logEkle(null, 'hata', $ipAdresi, $macAdresi, 'Geçersiz lisans anahtarı');
                return ['durum' => false, 'mesaj' => 'Geçersiz lisans anahtarı'];
            }
            
            if ($lisans['durum'] !== 'aktif') {
                $this->logEkle($lisans['id'], 'hata', $ipAdresi, $macAdresi, 'Pasif lisans');
                return ['durum' => false, 'mesaj' => 'Lisans pasif durumda'];
            }
            
            $bugun = date('Y-m-d');
            if ($bugun < $lisans['baslangic_tarihi']) {
                $this->logEkle($lisans['id'], 'hata', $ipAdresi, $macAdresi, 'Lisans henüz başlamamış');
                return ['durum' => false, 'mesaj' => 'Lisans henüz geçerli değil'];
            }
            
            if ($bugun > $lisans['bitis_tarihi']) {
                $this->lisansDurumGuncelle($lisans['id'], 'suresi_dolmus');
                $this->logEkle($lisans['id'], 'hata', $ipAdresi, $macAdresi, 'Lisans süresi dolmuş');
                return ['durum' => false, 'mesaj' => 'Lisans süresi dolmuş'];
            }
            
            if ($lisans['kullanim_sayisi'] >= $lisans['max_kullanim']) {
                $this->logEkle($lisans['id'], 'hata', $ipAdresi, $macAdresi, 'Maksimum kullanım sayısı aşıldı');
                return ['durum' => false, 'mesaj' => 'Maksimum kullanım sayısı aşıldı'];
            }
            
            if ($ipAdresi && $lisans['ip_adresi'] && $lisans['ip_adresi'] !== $ipAdresi) {
                $this->logEkle($lisans['id'], 'hata', $ipAdresi, $macAdresi, 'IP adresi uyumsuzluğu');
                return ['durum' => false, 'mesaj' => 'IP adresi uyumsuzluğu'];
            }
            
            if ($macAdresi && $lisans['mac_adresi'] && $lisans['mac_adresi'] !== $macAdresi) {
                $this->logEkle($lisans['id'], 'hata', $ipAdresi, $macAdresi, 'MAC adresi uyumsuzluğu');
                return ['durum' => false, 'mesaj' => 'MAC adresi uyumsuzluğu'];
            }
            
            $this->kullanımSayisiArtir($lisans['id']);
            
            if (!$lisans['ip_adresi'] && $ipAdresi) {
                $this->ipAdresiBagla($lisans['id'], $ipAdresi);
            }
            
            if (!$lisans['mac_adresi'] && $macAdresi) {
                $this->macAdresiBagla($lisans['id'], $macAdresi);
            }
            
            $this->logEkle($lisans['id'], 'dogrulama', $ipAdresi, $macAdresi, 'Başarılı doğrulama');
            
            return [
                'durum' => true,
                'mesaj' => 'Lisans geçerli',
                'lisans' => $lisans
            ];
            
        } catch (PDOException $e) {
            return ['durum' => false, 'mesaj' => 'Sistem hatası'];
        }
    }
    
    public function lisansAktive($lisansAnahtari, $ipAdresi = null, $macAdresi = null) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM lisanslar WHERE lisans_anahtari = ?");
            $stmt->execute([$lisansAnahtari]);
            $lisans = $stmt->fetch();
            
            if (!$lisans) {
                return ['durum' => false, 'mesaj' => 'Geçersiz lisans anahtarı'];
            }
            
            $updateData = [];
            $updateSql = "UPDATE lisanslar SET ";
            
            if ($ipAdresi) {
                $updateData[] = "ip_adresi = '$ipAdresi'";
            }
            
            if ($macAdresi) {
                $updateData[] = "mac_adresi = '$macAdresi'";
            }
            
            if (!empty($updateData)) {
                $updateSql .= implode(', ', $updateData) . " WHERE id = ?";
                $stmt = $this->db->prepare($updateSql);
                $stmt->execute([$lisans['id']]);
            }
            
            $this->logEkle($lisans['id'], 'aktivasyon', $ipAdresi, $macAdresi, 'Lisans aktive edildi');
            
            return ['durum' => true, 'mesaj' => 'Lisans başarıyla aktive edildi'];
            
        } catch (PDOException $e) {
            return ['durum' => false, 'mesaj' => 'Sistem hatası'];
        }
    }
    
    public function tumLisanslariGetir($sayfa = 1, $sayfaBasina = 20) {
        try {
            $offset = ($sayfa - 1) * $sayfaBasina;
            
            $stmt = $this->db->prepare("
                SELECT l.*, u.ad as urun_adi, k.ad, k.soyad, k.email 
                FROM lisanslar l 
                JOIN urunler u ON l.urun_id = u.id 
                JOIN kullanicilar k ON l.kullanici_id = k.id 
                ORDER BY l.olusturma_tarihi DESC 
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$sayfaBasina, $offset]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function kullaniciLisanslariGetir($kullaniciId) {
        try {
            $stmt = $this->db->prepare("
                SELECT l.*, u.ad as urun_adi 
                FROM lisanslar l 
                JOIN urunler u ON l.urun_id = u.id 
                WHERE l.kullanici_id = ? 
                ORDER BY l.olusturma_tarihi DESC
            ");
            
            $stmt->execute([$kullaniciId]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function lisansSil($lisansId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM lisanslar WHERE id = ?");
            return $stmt->execute([$lisansId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function lisansDurumGuncelle($lisansId, $durum) {
        try {
            $stmt = $this->db->prepare("UPDATE lisanslar SET durum = ? WHERE id = ?");
            return $stmt->execute([$durum, $lisansId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function benzersizLisansAnahtariOlustur() {
        do {
            $anahtar = 'EKA-' . strtoupper(bin2hex(random_bytes(8))) . '-' . strtoupper(bin2hex(random_bytes(8)));
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lisanslar WHERE lisans_anahtari = ?");
            $stmt->execute([$anahtar]);
        } while ($stmt->fetchColumn() > 0);
        
        return $anahtar;
    }
    
    private function kullanımSayisiArtir($lisansId) {
        $stmt = $this->db->prepare("UPDATE lisanslar SET kullanim_sayisi = kullanim_sayisi + 1 WHERE id = ?");
        $stmt->execute([$lisansId]);
    }
    
    private function ipAdresiBagla($lisansId, $ipAdresi) {
        $stmt = $this->db->prepare("UPDATE lisanslar SET ip_adresi = ? WHERE id = ?");
        $stmt->execute([$ipAdresi, $lisansId]);
    }
    
    private function macAdresiBagla($lisansId, $macAdresi) {
        $stmt = $this->db->prepare("UPDATE lisanslar SET mac_adresi = ? WHERE id = ?");
        $stmt->execute([$macAdresi, $lisansId]);
    }
    
    private function logEkle($lisansId, $islemTipi, $ipAdresi, $macAdresi, $detay, $domain = null, $ekBilgiler = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO lisans_loglari (
                    islem_turu, durum, lisans_id, domain, ip_adresi, mac_adresi, 
                    user_agent, server_name, script_path, document_root, php_version, 
                    os_info, request_uri, http_referer, hata_mesaji, ek_bilgiler
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $durumEnum = strtoupper($islemTipi);
            if (!in_array($durumEnum, ['BASARILI', 'HATA', 'GECERSIZ', 'UYARI'])) {
                $durumEnum = 'HATA';
            }
            
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Bilinmiyor';
            $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
            $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
            $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
            $phpVersion = PHP_VERSION;
            $osInfo = PHP_OS;
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $httpReferer = $_SERVER['HTTP_REFERER'] ?? 'Yok';
            
            $ekBilgilerJson = !empty($ekBilgiler) ? json_encode($ekBilgiler, JSON_UNESCAPED_UNICODE) : null;
            
            $stmt->execute([
                'Lisans Doğrulama',
                $durumEnum,
                $lisansId,
                $domain,
                $ipAdresi,
                $macAdresi,
                $userAgent,
                $serverName,
                $scriptPath,
                $documentRoot,
                $phpVersion,
                $osInfo,
                $requestUri,
                $httpReferer,
                $detay,
                $ekBilgilerJson
            ]);
            
        } catch (PDOException $e) {
            $tarih = date('Y-m-d H:i:s');
            $logMesaji = "[{$tarih}] VERITABANI LOG HATASI: {$e->getMessage()} | Lisans ID: {$lisansId} | İşlem: {$islemTipi} | IP: {$ipAdresi} | MAC: {$macAdresi} | Detay: {$detay}\n";
            file_put_contents(__DIR__ . '/../lisans_log.txt', $logMesaji, FILE_APPEND | LOCK_EX);
        }
    }
    
    public function lisansDomainGuncelle($lisansId, $domain) {
        try {
            $stmt = $this->db->prepare("UPDATE lisanslar SET domain = ? WHERE id = ?");
            return $stmt->execute([$domain, $lisansId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function lisansDogrulaWithDomain($lisansAnahtari, $ipAdresi = null, $macAdresi = null, $domain = null) {
        try {
            $stmt = $this->db->prepare("
                SELECT l.*, u.ad as urun_adi, k.ad, k.soyad, k.email 
                FROM lisanslar l 
                JOIN urunler u ON l.urun_id = u.id 
                JOIN kullanicilar k ON l.kullanici_id = k.id 
                WHERE l.lisans_anahtari = ?
            ");
            
            $stmt->execute([$lisansAnahtari]);
            $lisans = $stmt->fetch();
            
            if (!$lisans) {
                $this->logEkle(null, 'GECERSIZ', $ipAdresi, $macAdresi, 'Geçersiz lisans anahtarı', $domain, ['lisans_anahtari' => $lisansAnahtari]);
                return ['durum' => false, 'mesaj' => 'Geçersiz lisans anahtarı'];
            }
            
            if ($lisans['durum'] !== 'aktif') {
                $this->logEkle($lisans['id'], 'HATA', $ipAdresi, $macAdresi, 'Pasif lisans', $domain, ['lisans_durumu' => $lisans['durum']]);
                return ['durum' => false, 'mesaj' => 'Lisans pasif durumda'];
            }
            
            $bugun = date('Y-m-d');
            if ($bugun < $lisans['baslangic_tarihi']) {
                $this->logEkle($lisans['id'], 'HATA', $ipAdresi, $macAdresi, 'Lisans henüz başlamamış', $domain, ['baslangic_tarihi' => $lisans['baslangic_tarihi'], 'bugun' => $bugun]);
                return ['durum' => false, 'mesaj' => 'Lisans henüz geçerli değil'];
            }
            
            if ($bugun > $lisans['bitis_tarihi']) {
                $this->lisansDurumGuncelle($lisans['id'], 'suresi_dolmus');
                $this->logEkle($lisans['id'], 'HATA', $ipAdresi, $macAdresi, 'Lisans süresi dolmuş', $domain, ['bitis_tarihi' => $lisans['bitis_tarihi'], 'bugun' => $bugun]);
                return ['durum' => false, 'mesaj' => 'Lisans süresi dolmuş'];
            }
            
            if ($lisans['kullanim_sayisi'] >= $lisans['max_kullanim']) {
                $this->logEkle($lisans['id'], 'HATA', $ipAdresi, $macAdresi, 'Maksimum kullanım sayısı aşıldı', $domain, ['kullanim_sayisi' => $lisans['kullanim_sayisi'], 'max_kullanim' => $lisans['max_kullanim']]);
                return ['durum' => false, 'mesaj' => 'Maksimum kullanım sayısı aşıldı'];
            }
            
            if ($domain && !empty($lisans['domain']) && $lisans['domain'] !== $domain) {
                $this->logEkle($lisans['id'], 'HATA', $ipAdresi, $macAdresi, 'Domain uyumsuzluğu', $domain, ['beklenen_domain' => $lisans['domain'], 'gelen_domain' => $domain]);
                return ['durum' => false, 'mesaj' => 'Domain uyumsuzluğu'];
            }
            
            if ($ipAdresi && $lisans['ip_adresi'] && $lisans['ip_adresi'] !== $ipAdresi) {
                $this->logEkle($lisans['id'], 'HATA', $ipAdresi, $macAdresi, 'IP adresi uyumsuzluğu', $domain, ['beklenen_ip' => $lisans['ip_adresi'], 'gelen_ip' => $ipAdresi]);
                return ['durum' => false, 'mesaj' => 'IP adresi uyumsuzluğu'];
            }
            
            if ($macAdresi && $lisans['mac_adresi'] && $lisans['mac_adresi'] !== $macAdresi) {
                $this->logEkle($lisans['id'], 'HATA', $ipAdresi, $macAdresi, 'MAC adresi uyumsuzluğu', $domain, ['beklenen_mac' => $lisans['mac_adresi'], 'gelen_mac' => $macAdresi]);
                return ['durum' => false, 'mesaj' => 'MAC adresi uyumsuzluğu'];
            }
            
            $this->kullanımSayisiArtir($lisans['id']);
            
            if (!$lisans['ip_adresi'] && $ipAdresi) {
                $this->ipAdresiBagla($lisans['id'], $ipAdresi);
            }
            
            if (!$lisans['mac_adresi'] && $macAdresi) {
                $this->macAdresiBagla($lisans['id'], $macAdresi);
            }
            
            $this->logEkle($lisans['id'], 'BASARILI', $ipAdresi, $macAdresi, 'Başarılı doğrulama', $domain, ['urun_adi' => $lisans['urun_adi'], 'kullanici' => $lisans['ad'] . ' ' . $lisans['soyad']]);
            
            return [
                'durum' => true,
                'mesaj' => 'Lisans geçerli',
                'lisans' => $lisans
            ];
            
        } catch (PDOException $e) {
            $this->logEkle(null, 'HATA', $ipAdresi, $macAdresi, 'Veritabanı hatası: ' . $e->getMessage(), $domain, ['hata_kodu' => $e->getCode()]);
            return ['durum' => false, 'mesaj' => 'Sistem hatası'];
        }
    }
    
    public function lisansIstatistikleriGetir() {
        try {
            $stats = [];
            
            $stmt = $this->db->query("SELECT COUNT(*) as toplam FROM lisanslar");
            $stats['toplam'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) as aktif FROM lisanslar WHERE durum = 'aktif'");
            $stats['aktif'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) as pasif FROM lisanslar WHERE durum = 'pasif'");
            $stats['pasif'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM lisanslar WHERE MONTH(olusturma_tarihi) = MONTH(CURDATE()) AND YEAR(olusturma_tarihi) = YEAR(CURDATE())");
            $stats['bu_ay'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (PDOException $e) {
            return [
                'toplam' => 0,
                'aktif' => 0,
                'pasif' => 0,
                'bu_ay' => 0
            ];
        }
    }
}
?>