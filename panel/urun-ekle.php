<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/veritabani.php';
require_once '../classes/EkaUrunYoneticisi.php';
require_once '../classes/EkaKullaniciYoneticisi.php';

$veritabani = new EkaVeritabani();
$baglanti = $veritabani->baglantiGetir();
$urunYoneticisi = new EkaUrunYoneticisi($baglanti);
$kullaniciYoneticisi = new EkaKullaniciYoneticisi($baglanti);

$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
if (!$kullaniciYoneticisi->adminMi($_SESSION['kullanici_id'])) {
    header('Location: dashboard.php');
    exit;
}

$mesaj = '';
$mesaj_tipi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');
    $versiyon = trim($_POST['versiyon'] ?? '');
    $fiyat = floatval($_POST['fiyat'] ?? 0);
    $durum = $_POST['durum'] ?? 'aktif';
    
    if (empty($ad) || empty($versiyon) || $fiyat <= 0) {
        $mesaj = 'Tüm alanları doldurunuz ve geçerli bir fiyat giriniz.';
        $mesaj_tipi = 'danger';
    } else {
        $sonuc = $urunYoneticisi->urunEkle($ad, $aciklama, $versiyon, $fiyat, $durum);
        if ($sonuc['durum']) {
            $mesaj = 'Ürün başarıyla eklendi!';
            $mesaj_tipi = 'success';
            $_POST = [];
        } else {
            $mesaj = 'Bir hata oluştu: ' . $sonuc['mesaj'];
            $mesaj_tipi = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Ekle - EKA Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="main-content">
        <?php include 'includes/sidebar.php'; ?>
        <?php include 'includes/header.php'; ?>
        
        <main class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><i class="fas fa-plus-circle me-2"></i>Yeni Ürün Ekle</h2>
                            <a href="urunler.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Geri Dön
                            </a>
                        </div>
                        
                        <?php if ($mesaj): ?>
                            <div class="alert alert-<?php echo $mesaj_tipi; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($mesaj); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ürün Bilgileri</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ad" class="form-label">Ürün Adı *</label>
                                                <input type="text" class="form-control" id="ad" name="ad" 
                                                       value="<?php echo htmlspecialchars($_POST['ad'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="versiyon" class="form-label">Versiyon *</label>
                                                <input type="text" class="form-control" id="versiyon" name="versiyon" 
                                                       value="<?php echo htmlspecialchars($_POST['versiyon'] ?? ''); ?>" 
                                                       placeholder="Örn: 1.0.0" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fiyat" class="form-label">Fiyat (TL) *</label>
                                                <input type="number" class="form-control" id="fiyat" name="fiyat" 
                                                       value="<?php echo $_POST['fiyat'] ?? ''; ?>" 
                                                       step="0.01" min="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="durum" class="form-label">Durum</label>
                                                <select class="form-select" id="durum" name="durum">
                                                    <option value="aktif" <?php echo ($_POST['durum'] ?? 'aktif') === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                                    <option value="pasif" <?php echo ($_POST['durum'] ?? '') === 'pasif' ? 'selected' : ''; ?>>Pasif</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="aciklama" class="form-label">Açıklama</label>
                                        <textarea class="form-control" id="aciklama" name="aciklama" rows="4" 
                                                  placeholder="Ürün hakkında detaylı bilgi..."><?php echo htmlspecialchars($_POST['aciklama'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="urunler.php" class="btn btn-secondary">İptal</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Ürünü Kaydet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Bilgilendirme</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-0">
                                    <h6><i class="fas fa-info-circle me-2"></i>Ürün Ekleme Hakkında</h6>
                                    <ul class="mb-0">
                                        <li>Ürün adı benzersiz olmalıdır</li>
                                        <li>Versiyon formatı: X.Y.Z şeklinde olmalıdır</li>
                                        <li>Fiyat pozitif bir değer olmalıdır</li>
                                        <li>Eklenen ürün için lisans oluşturabilirsiniz</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>