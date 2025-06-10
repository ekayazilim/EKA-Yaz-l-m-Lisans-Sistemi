<?php
require_once __DIR__ . '/../config/veritabani.php';

class EkaKullaniciYoneticisi {
    private $db;
    
    public function __construct() {
        $this->db = EkaVeritabani::baglantiGetir();
    }
    
    public function kullaniciKaydet($ad, $soyad, $email, $sifre, $telefon = null, $sirket = null) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM kullanicilar WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetchColumn() > 0) {
                return ['durum' => false, 'mesaj' => 'Bu e-posta adresi zaten kayıtlı'];
            }
            
            $sifreHash = password_hash($sifre, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO kullanicilar (ad, soyad, email, sifre, telefon, sirket) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$ad, $soyad, $email, $sifreHash, $telefon, $sirket]);
            
            return ['durum' => true, 'mesaj' => 'Kullanıcı başarıyla kaydedildi'];
            
        } catch (PDOException $e) {
            return ['durum' => false, 'mesaj' => 'Sistem hatası'];
        }
    }
    
    public function girisYap($email, $sifre) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, sifre, durum FROM kullanicilar 
                WHERE email = ?
            ");
            
            $stmt->execute([$email]);
            $kullanici = $stmt->fetch();
            
            if (!$kullanici) {
                return false;
            }
            
            if ($kullanici['durum'] !== 'aktif') {
                return false;
            }
            
            return password_verify($sifre, $kullanici['sifre']);
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function kullaniciIdGetir($email) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM kullanicilar WHERE email = ?");
            $stmt->execute([$email]);
            $kullanici = $stmt->fetch();
            
            return $kullanici ? $kullanici['id'] : null;
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function kullaniciBilgileriGetir($kullaniciId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, ad, soyad, email, telefon, sirket, rol, durum, kayit_tarihi, guncelleme_tarihi 
                FROM kullanicilar 
                WHERE id = ?
            ");
            
            $stmt->execute([$kullaniciId]);
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function tumKullanicilariGetir($sayfa = 1, $sayfaBasina = 20) {
        try {
            $offset = ($sayfa - 1) * $sayfaBasina;
            
            $stmt = $this->db->prepare("
                SELECT id, ad, soyad, email, telefon, sirket, rol, durum, kayit_tarihi 
                FROM kullanicilar 
                ORDER BY kayit_tarihi DESC 
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$sayfaBasina, $offset]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function kullaniciBilgileriGuncelle($kullaniciId, $ad, $soyad, $telefon = null, $sirket = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE kullanicilar 
                SET ad = ?, soyad = ?, telefon = ?, sirket = ? 
                WHERE id = ?
            ");
            
            return $stmt->execute([$ad, $soyad, $telefon, $sirket, $kullaniciId]);
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function sifreGuncelle($kullaniciId, $eskiSifre, $yeniSifre) {
        try {
            $stmt = $this->db->prepare("SELECT sifre FROM kullanicilar WHERE id = ?");
            $stmt->execute([$kullaniciId]);
            $kullanici = $stmt->fetch();
            
            if (!$kullanici || !password_verify($eskiSifre, $kullanici['sifre'])) {
                return ['durum' => false, 'mesaj' => 'Mevcut şifre yanlış'];
            }
            
            $yeniSifreHash = password_hash($yeniSifre, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("UPDATE kullanicilar SET sifre = ? WHERE id = ?");
            $stmt->execute([$yeniSifreHash, $kullaniciId]);
            
            return ['durum' => true, 'mesaj' => 'Şifre başarıyla güncellendi'];
            
        } catch (PDOException $e) {
            return ['durum' => false, 'mesaj' => 'Sistem hatası'];
        }
    }
    
    public function kullaniciDurumGuncelle($kullaniciId, $durum) {
        try {
            $stmt = $this->db->prepare("UPDATE kullanicilar SET durum = ? WHERE id = ?");
            return $stmt->execute([$durum, $kullaniciId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function kullaniciRolGuncelle($kullaniciId, $rol) {
        try {
            $stmt = $this->db->prepare("UPDATE kullanicilar SET rol = ? WHERE id = ?");
            return $stmt->execute([$rol, $kullaniciId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function kullaniciSil($kullaniciId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM kullanicilar WHERE id = ?");
            return $stmt->execute([$kullaniciId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function adminMi($kullaniciId) {
        try {
            $stmt = $this->db->prepare("SELECT rol FROM kullanicilar WHERE id = ?");
            $stmt->execute([$kullaniciId]);
            $kullanici = $stmt->fetch();
            
            return $kullanici && $kullanici['rol'] === 'admin';
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function kullaniciIstatistikleriGetir() {
        try {
            $stats = [];
            
            $stmt = $this->db->query("SELECT COUNT(*) as toplam FROM kullanicilar");
            $stats['toplam'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) as aktif FROM kullanicilar WHERE durum = 'aktif'");
            $stats['aktif'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) as admin FROM kullanicilar WHERE rol = 'admin'");
            $stats['admin'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) as bu_ay FROM kullanicilar WHERE MONTH(kayit_tarihi) = MONTH(NOW()) AND YEAR(kayit_tarihi) = YEAR(NOW())");
            $stats['bu_ay'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (PDOException $e) {
            return [
                'toplam' => 0,
                'aktif' => 0,
                'admin' => 0,
                'bu_ay' => 0
            ];
        }
    }
    
    public function emailVarMi($email, $haricKullaniciId = null) {
        try {
            if ($haricKullaniciId) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM kullanicilar WHERE email = ? AND id != ?");
                $stmt->execute([$email, $haricKullaniciId]);
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM kullanicilar WHERE email = ?");
                $stmt->execute([$email]);
            }
            
            return $stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>