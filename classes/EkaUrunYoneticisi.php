<?php
require_once __DIR__ . '/../config/veritabani.php';

class EkaUrunYoneticisi {
    private $db;
    
    public function __construct() {
        $this->db = EkaVeritabani::baglantiGetir();
    }
    
    public function urunEkle($ad, $aciklama, $versiyon, $fiyat) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO urunler (ad, aciklama, versiyon, fiyat) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([$ad, $aciklama, $versiyon, $fiyat]);
            
            return ['durum' => true, 'mesaj' => 'Ürün başarıyla eklendi', 'id' => $this->db->lastInsertId()];
            
        } catch (PDOException $e) {
            return ['durum' => false, 'mesaj' => 'Sistem hatası'];
        }
    }
    
    public function urunGuncelle($id, $ad, $aciklama, $versiyon, $fiyat) {
        try {
            $stmt = $this->db->prepare("
                UPDATE urunler 
                SET ad = ?, aciklama = ?, versiyon = ?, fiyat = ? 
                WHERE id = ?
            ");
            
            $stmt->execute([$ad, $aciklama, $versiyon, $fiyat, $id]);
            
            return ['durum' => true, 'mesaj' => 'Ürün başarıyla güncellendi'];
            
        } catch (PDOException $e) {
            return ['durum' => false, 'mesaj' => 'Sistem hatası'];
        }
    }
    
    public function urunSil($id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lisanslar WHERE urun_id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->fetchColumn() > 0) {
                return ['durum' => false, 'mesaj' => 'Bu ürüne ait lisanslar bulunduğu için silinemez'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM urunler WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['durum' => true, 'mesaj' => 'Ürün başarıyla silindi'];
            
        } catch (PDOException $e) {
            return ['durum' => false, 'mesaj' => 'Sistem hatası'];
        }
    }
    
    public function urunGetir($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM urunler WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function tumUrunleriGetir($sayfa = 1, $sayfaBasina = 20) {
        try {
            $offset = ($sayfa - 1) * $sayfaBasina;
            
            $stmt = $this->db->prepare("
                SELECT * FROM urunler 
                ORDER BY olusturma_tarihi DESC 
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$sayfaBasina, $offset]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function aktifUrunleriGetir() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM urunler WHERE durum = 'aktif' ORDER BY ad");
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function urunDurumGuncelle($id, $durum) {
        try {
            $stmt = $this->db->prepare("UPDATE urunler SET durum = ? WHERE id = ?");
            return $stmt->execute([$durum, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function urunIstatistikleriGetir() {
        try {
            $stats = [];
            
            $stmt = $this->db->query("SELECT COUNT(*) as toplam FROM urunler");
            $stats['toplam'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) as aktif FROM urunler WHERE durum = 'aktif'");
            $stats['aktif'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) as pasif FROM urunler WHERE durum = 'pasif'");
            $stats['pasif'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) as toplam_lisans FROM lisanslar");
            $stats['toplam_lisans'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("
                SELECT u.ad, COUNT(l.id) as lisans_sayisi 
                FROM urunler u 
                LEFT JOIN lisanslar l ON u.id = l.urun_id 
                GROUP BY u.id, u.ad 
                ORDER BY lisans_sayisi DESC 
                LIMIT 5
            ");
            $stats['populer_urunler'] = $stmt->fetchAll();
            
            return $stats;
        } catch (PDOException $e) {
            return [
                'toplam' => 0,
                'aktif' => 0,
                'pasif' => 0,
                'toplam_lisans' => 0,
                'populer_urunler' => []
            ];
        }
    }
    
    public function urunAra($arama) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM urunler 
                WHERE ad LIKE ? OR aciklama LIKE ? OR versiyon LIKE ? 
                ORDER BY ad
            ");
            
            $aramaTermi = '%' . $arama . '%';
            $stmt->execute([$aramaTermi, $aramaTermi, $aramaTermi]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function urunLisansSayisiGetir($urunId) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lisanslar WHERE urun_id = ?");
            $stmt->execute([$urunId]);
            return $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>