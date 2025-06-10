<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/veritabani.php';
require_once '../classes/EkaKullaniciYoneticisi.php';
require_once '../classes/EkaLisansYoneticisi.php';
require_once '../classes/EkaUrunYoneticisi.php';

$veritabani = new EkaVeritabani();
$baglanti = $veritabani->baglantiGetir();
$kullaniciYoneticisi = new EkaKullaniciYoneticisi($baglanti);
$lisansYoneticisi = new EkaLisansYoneticisi($baglanti);
$urunYoneticisi = new EkaUrunYoneticisi($baglanti);

$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
if (!$kullaniciYoneticisi->adminMi($_SESSION['kullanici_id'])) {
    header('Location: dashboard.php');
    exit;
}

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_id = $_POST['kullanici_id'] ?? '';
    $urun_id = $_POST['urun_id'] ?? '';
    $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? '';
    $bitis_tarihi = $_POST['bitis_tarihi'] ?? '';
    $max_kullanim = $_POST['max_kullanim'] ?? 1;
    $ip_kisitlama = isset($_POST['ip_kisitlama']) ? 1 : 0;
    $mac_kisitlama = isset($_POST['mac_kisitlama']) ? 1 : 0;
    $aciklama = $_POST['aciklama'] ?? '';
    $domain = $_POST['domain'] ?? '';
    $lisans_tipi = $_POST['lisans_tipi'] ?? 'sureli';
    
    if (empty($kullanici_id) || empty($urun_id) || empty($baslangic_tarihi) || empty($domain)) {
        $hata = 'Kullanıcı, ürün, başlangıç tarihi ve domain alanları zorunludur!';
    } elseif ($lisans_tipi === 'sureli' && empty($bitis_tarihi)) {
        $hata = 'Süreli lisans için bitiş tarihi zorunludur!';
    } else {
        if ($lisans_tipi === 'suresiz') {
            $bitis_tarihi = '2099-12-31';
        }
        $sonuc = $lisansYoneticisi->lisansOlustur(
            $kullanici_id,
            $urun_id,
            $baslangic_tarihi,
            $bitis_tarihi,
            $max_kullanim,
            $ip_kisitlama,
            $mac_kisitlama,
            $aciklama,
            $domain
        );
        
        if ($sonuc['durum']) {
            $mesaj = 'Lisans başarıyla oluşturuldu! Lisans Anahtarı: ' . $sonuc['lisans_anahtari'];
        } else {
            $hata = $sonuc['mesaj'];
        }
    }
}

$kullanicilar = $kullaniciYoneticisi->tumKullanicilariGetir();
$urunler = $urunYoneticisi->tumUrunleriGetir();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisans Ekle - EKA Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="fade-in">
                <div class="container-fluid p-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="h3 mb-0">Yeni Lisans Ekle</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="lisanslar.php">Lisanslar</a></li>
                                <li class="breadcrumb-item active">Yeni Lisans</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <?php if ($mesaj): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($mesaj); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($hata): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($hata); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-plus-circle me-2"></i>
                                        Lisans Bilgileri
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="kullanici_id" class="form-label">Kullanıcı *</label>
                                                <select class="form-select" id="kullanici_id" name="kullanici_id" required>
                                                    <option value="">Kullanıcı Seçin</option>
                                                    <?php foreach ($kullanicilar as $k): ?>
                                                        <option value="<?php echo $k['id']; ?>">
                                                            <?php echo htmlspecialchars($k['ad'] . ' ' . $k['soyad'] . ' (' . $k['email'] . ')'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="urun_id" class="form-label">Ürün *</label>
                                                <select class="form-select" id="urun_id" name="urun_id" required>
                                                    <option value="">Ürün Seçin</option>
                                                    <?php foreach ($urunler as $u): ?>
                                                        <option value="<?php echo $u['id']; ?>">
                                                            <?php echo htmlspecialchars($u['ad'] . ' v' . $u['versiyon']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi *</label>
                                                <input type="date" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi" 
                                                       value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="lisans_tipi" class="form-label">Lisans Tipi *</label>
                                                <select class="form-select" id="lisans_tipi" name="lisans_tipi" onchange="toggleBitisTarihi()" required>
                                                    <option value="sureli">Süreli Lisans</option>
                                                    <option value="suresiz">Süresiz Lisans</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row" id="bitis_tarihi_row">
                                            <div class="col-md-12 mb-3">
                                                <label for="bitis_tarihi" class="form-label">Bitiş Tarihi *</label>
                                                <input type="date" class="form-control" id="bitis_tarihi" name="bitis_tarihi" 
                                                       value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="max_kullanim" class="form-label">Maksimum Kullanım Sayısı</label>
                                                <input type="number" class="form-control" id="max_kullanim" name="max_kullanim" 
                                                       value="1" min="1" max="999">
                                                <div class="form-text">Lisansın kaç kez kullanılabileceğini belirler</div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Kısıtlamalar</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="ip_kisitlama" name="ip_kisitlama">
                                                    <label class="form-check-label" for="ip_kisitlama">
                                                        IP Adresi Kısıtlaması
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="mac_kisitlama" name="mac_kisitlama">
                                                    <label class="form-check-label" for="mac_kisitlama">
                                                        MAC Adresi Kısıtlaması
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="domain" class="form-label">Domain *</label>
                                            <input type="text" class="form-control" id="domain" name="domain" 
                                                   placeholder="örnek: example.com" required>
                                            <div class="form-text">Lisansın çalışacağı domain adresini giriniz</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="aciklama" class="form-label">Açıklama</label>
                                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3" 
                                                      placeholder="Lisans hakkında ek bilgiler..."></textarea>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>
                                                Lisans Oluştur
                                            </button>
                                            <a href="lisanslar.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left me-2"></i>
                                                Geri Dön
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Bilgilendirme
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-lightbulb me-2"></i>Lisans Oluşturma İpuçları</h6>
                                        <ul class="mb-0 small">
                                            <li>Lisans anahtarı otomatik olarak oluşturulur</li>
                                            <li>IP kısıtlaması aktifse, lisans sadece belirli IP'den kullanılabilir</li>
                                            <li>MAC kısıtlaması aktifse, lisans sadece belirli cihazdan kullanılabilir</li>
                                            <li>Maksimum kullanım sayısı aşıldığında lisans deaktif olur</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Dikkat</h6>
                                        <p class="mb-0 small">
                                            Oluşturulan lisans anahtarını güvenli bir yerde saklayın. 
                                            Bu anahtar müşterinizle paylaşılacaktır.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function toggleBitisTarihi() {
            const lisansTipi = document.getElementById('lisans_tipi').value;
            const bitisTarihiRow = document.getElementById('bitis_tarihi_row');
            const bitisTarihiInput = document.getElementById('bitis_tarihi');
            
            if (lisansTipi === 'suresiz') {
                bitisTarihiRow.style.display = 'none';
                bitisTarihiInput.removeAttribute('required');
            } else {
                bitisTarihiRow.style.display = 'block';
                bitisTarihiInput.setAttribute('required', 'required');
            }
        }
        
        document.getElementById('baslangic_tarihi').addEventListener('change', function() {
            const baslangic = new Date(this.value);
            const bitis = document.getElementById('bitis_tarihi');
            
            if (baslangic) {
                const minBitis = new Date(baslangic);
                minBitis.setDate(minBitis.getDate() + 1);
                bitis.min = minBitis.toISOString().split('T')[0];
                
                if (new Date(bitis.value) <= baslangic) {
                    const defaultBitis = new Date(baslangic);
                    defaultBitis.setFullYear(defaultBitis.getFullYear() + 1);
                    bitis.value = defaultBitis.toISOString().split('T')[0];
                }
            }
        });
    </script>
</body>
</html>