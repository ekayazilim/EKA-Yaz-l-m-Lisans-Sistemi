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
$lisansYoneticisi = new EkaLisansYoneticisi();
$urunYoneticisi = new EkaUrunYoneticisi();

$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
$adminMi = $kullaniciYoneticisi->adminMi($_SESSION['kullanici_id']);

$kullaniciStats = $kullaniciYoneticisi->kullaniciIstatistikleriGetir();
$lisansStats = $lisansYoneticisi->lisansIstatistikleriGetir();
$urunStats = $urunYoneticisi->urunIstatistikleriGetir();

$sonLisanslar = $lisansYoneticisi->tumLisanslariGetir(1, 5);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
                <?php include 'includes/header.php'; ?>
                
                <div class="fade-in">
                    <h2 class="page-title">Dashboard</h2>
                    
                    <div class="row mb-4">
                        <?php if ($adminMi): ?>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="stats-number"><?php echo $kullaniciStats['toplam'] ?? 0; ?></div>
                                            <div class="stats-label">Toplam Kullanıcı</div>
                                        </div>
                                        <div class="text-primary">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="stats-number"><?php echo $urunStats['toplam'] ?? 0; ?></div>
                                            <div class="stats-label">Toplam Ürün</div>
                                        </div>
                                        <div class="text-success">
                                            <i class="fas fa-box fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stats-number"><?php echo $lisansStats['toplam'] ?? 0; ?></div>
                                        <div class="stats-label">Toplam Lisans</div>
                                    </div>
                                    <div class="text-info">
                                        <i class="fas fa-key fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stats-number"><?php echo $lisansStats['aktif'] ?? 0; ?></div>
                                        <div class="stats-label">Aktif Lisans</div>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Son Lisanslar</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($sonLisanslar)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Lisans Anahtarı</th>
                                                        <th>Kullanıcı</th>
                                                        <th>Ürün</th>
                                                        <th>Durum</th>
                                                        <th>Bitiş Tarihi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($sonLisanslar as $lisans): ?>
                                                        <tr>
                                                            <td>
                                                                <code class="license-key"><?php echo htmlspecialchars($lisans['lisans_anahtari']); ?></code>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($lisans['ad'] . ' ' . $lisans['soyad']); ?></td>
                                                            <td><?php echo htmlspecialchars($lisans['urun_adi']); ?></td>
                                                            <td>
                                                                <?php
                                                                $durumClass = '';
                                                                $durumText = '';
                                                                switch ($lisans['durum']) {
                                                                    case 'aktif':
                                                                        $durumClass = 'badge-success';
                                                                        $durumText = 'Aktif';
                                                                        break;
                                                                    case 'pasif':
                                                                        $durumClass = 'badge-secondary';
                                                                        $durumText = 'Pasif';
                                                                        break;
                                                                    case 'suresi_dolmus':
                                                                        $durumClass = 'badge-warning';
                                                                        $durumText = 'Süresi Dolmuş';
                                                                        break;
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $durumClass; ?>"><?php echo $durumText; ?></span>
                                                            </td>
                                                            <td><?php echo date('d.m.Y', strtotime($lisans['bitis_tarihi'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="lisanslar.php" class="btn btn-primary">Tüm Lisansları Görüntüle</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Henüz lisans bulunmuyor.</p>
                                            <?php if ($adminMi): ?>
                                                <a href="lisans-ekle.php" class="btn btn-primary">İlk Lisansı Oluştur</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Hızlı İşlemler</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <?php if ($adminMi): ?>
                                            <a href="lisans-ekle.php" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Yeni Lisans Oluştur
                                            </a>
                                            <a href="urun-ekle.php" class="btn btn-success">
                                                <i class="fas fa-box me-2"></i>Yeni Ürün Ekle
                                            </a>
                                            <a href="kullanicilar.php" class="btn btn-info">
                                                <i class="fas fa-users me-2"></i>Kullanıcıları Yönet
                                            </a>
                                        <?php endif; ?>
                                        <a href="profil.php" class="btn btn-secondary">
                                            <i class="fas fa-user me-2"></i>Profili Düzenle
                                        </a>
                                        <a href="lisans-dogrula.php" class="btn btn-warning">
                                            <i class="fas fa-search me-2"></i>Lisans Doğrula
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($adminMi && !empty($urunStats['populer_urunler'])): ?>
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Popüler Ürünler</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($urunStats['populer_urunler'] as $urun): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span><?php echo htmlspecialchars($urun['ad']); ?></span>
                                                <span class="badge badge-primary"><?php echo $urun['lisans_sayisi']; ?> lisans</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>