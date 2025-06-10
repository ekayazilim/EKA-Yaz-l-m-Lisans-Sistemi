<?php
session_start();

if (!isset($_SESSION['kullanici_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/veritabani.php';
require_once '../classes/EkaKullaniciYoneticisi.php';
require_once '../classes/EkaLisansYoneticisi.php';

$kullaniciYoneticisi = new EkaKullaniciYoneticisi();
$lisansYoneticisi = new EkaLisansYoneticisi();

$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
$adminMi = $kullaniciYoneticisi->adminMi($_SESSION['kullanici_id']);

$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$sayfaBasina = 20;

if ($adminMi) {
    $lisanslar = $lisansYoneticisi->tumLisanslariGetir($sayfa, $sayfaBasina);
} else {
    $lisanslar = $lisansYoneticisi->kullaniciLisanslariGetir($_SESSION['kullanici_id']);
}

if (isset($_POST['durum_guncelle']) && $adminMi) {
    $lisansId = $_POST['lisans_id'];
    $yeniDurum = $_POST['yeni_durum'];
    
    if ($lisansYoneticisi->lisansDurumGuncelle($lisansId, $yeniDurum)) {
        $mesaj = 'Lisans durumu başarıyla güncellendi.';
        $mesajTipi = 'success';
        header('Location: lisanslar.php?mesaj=' . urlencode($mesaj) . '&tip=' . $mesajTipi);
        exit;
    } else {
        $mesaj = 'Durum güncellenirken hata oluştu.';
        $mesajTipi = 'danger';
    }
}

if (isset($_POST['lisans_sil']) && $adminMi) {
    $lisansId = $_POST['lisans_id'];
    
    if ($lisansYoneticisi->lisansSil($lisansId)) {
        $mesaj = 'Lisans başarıyla silindi.';
        $mesajTipi = 'success';
        header('Location: lisanslar.php?mesaj=' . urlencode($mesaj) . '&tip=' . $mesajTipi);
        exit;
    } else {
        $mesaj = 'Lisans silinirken hata oluştu.';
        $mesajTipi = 'danger';
    }
}

if (isset($_POST['domain_guncelle']) && $adminMi) {
    $lisansId = $_POST['lisans_id'];
    $yeniDomain = $_POST['yeni_domain'];
    
    if ($lisansYoneticisi->lisansDomainGuncelle($lisansId, $yeniDomain)) {
        $mesaj = 'Domain başarıyla güncellendi.';
        $mesajTipi = 'success';
        header('Location: lisanslar.php?mesaj=' . urlencode($mesaj) . '&tip=' . $mesajTipi);
        exit;
    } else {
        $mesaj = 'Domain güncellenirken hata oluştu.';
        $mesajTipi = 'danger';
    }
}

if (isset($_GET['mesaj'])) {
    $mesaj = $_GET['mesaj'];
    $mesajTipi = $_GET['tip'] ?? 'info';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisanslar - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
                <?php include 'includes/header.php'; ?>
                
                <div class="fade-in">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="page-title"><?php echo $adminMi ? 'Tüm Lisanslar' : 'Lisanslarım'; ?></h2>
                        <?php if ($adminMi): ?>
                            <a href="lisans-ekle.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Yeni Lisans Oluştur
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($mesaj)): ?>
                        <div class="alert alert-<?php echo $mesajTipi; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mesaj); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($lisanslar)): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Lisans Anahtarı</th>
                                                <?php if ($adminMi): ?>
                                                    <th>Kullanıcı</th>
                                                <?php endif; ?>
                                                <th>Ürün</th>
                                                <th>Başlangıç</th>
                                                <th>Bitiş</th>
                                                <th>Kullanım</th>
                                                <th>Durum</th>
                                                <th>IP Adresi</th>
                                                <th>Domain</th>
                                                <?php if ($adminMi): ?>
                                                    <th>İşlemler</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lisanslar as $lisans): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <code class="license-key me-2"><?php echo htmlspecialchars($lisans['lisans_anahtari']); ?></code>
                                                            <button class="copy-btn" onclick="copyToClipboard('<?php echo $lisans['lisans_anahtari']; ?>')" title="Kopyala">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <?php if ($adminMi): ?>
                                                        <td><?php echo htmlspecialchars($lisans['ad'] . ' ' . $lisans['soyad']); ?></td>
                                                    <?php endif; ?>
                                                    <td><?php echo htmlspecialchars($lisans['urun_adi']); ?></td>
                                                    <td><?php echo date('d.m.Y', strtotime($lisans['baslangic_tarihi'])); ?></td>
                                                    <td><?php echo date('d.m.Y', strtotime($lisans['bitis_tarihi'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo $lisans['kullanim_sayisi']; ?>/<?php echo $lisans['max_kullanim']; ?>
                                                        </span>
                                                    </td>
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
                                                    <td>
                                                        <?php if ($lisans['ip_adresi']): ?>
                                                            <code><?php echo htmlspecialchars($lisans['ip_adresi']); ?></code>
                                                        <?php else: ?>
                                                            <span class="text-muted">Belirlenmemiş</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($lisans['domain'])): ?>
                                                            <code><?php echo htmlspecialchars($lisans['domain']); ?></code>
                                                        <?php else: ?>
                                                            <span class="text-muted">Belirlenmemiş</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($adminMi): ?>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#durumModal<?php echo $lisans['id']; ?>" title="Durum Güncelle">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#domainModal<?php echo $lisans['id']; ?>" title="Domain Güncelle">
                                                                    <i class="fas fa-globe"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#silModal<?php echo $lisans['id']; ?>" title="Sil">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                                
                                                <?php if ($adminMi): ?>
                                                    <div class="modal fade" id="durumModal<?php echo $lisans['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Lisans Durumu Güncelle</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="lisans_id" value="<?php echo $lisans['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Yeni Durum</label>
                                                                            <select name="yeni_durum" class="form-select" required>
                                                                                <option value="aktif" <?php echo $lisans['durum'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                                                                <option value="pasif" <?php echo $lisans['durum'] == 'pasif' ? 'selected' : ''; ?>>Pasif</option>
                                                                                <option value="suresi_dolmus" <?php echo $lisans['durum'] == 'suresi_dolmus' ? 'selected' : ''; ?>>Süresi Dolmuş</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                        <button type="submit" name="durum_guncelle" class="btn btn-primary">Güncelle</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="modal fade" id="domainModal<?php echo $lisans['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Domain Güncelle</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="lisans_id" value="<?php echo $lisans['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Domain Adresi</label>
                                                                            <input type="text" name="yeni_domain" class="form-control" value="<?php echo htmlspecialchars($lisans['domain'] ?? ''); ?>" placeholder="ornek.com">
                                                                            <div class="form-text">Domain adresini www olmadan yazın (örn: ornek.com)</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                        <button type="submit" name="domain_guncelle" class="btn btn-primary">Güncelle</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="modal fade" id="silModal<?php echo $lisans['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Lisansı Sil</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Bu lisansı silmek istediğinizden emin misiniz?</p>
                                                                    <p><strong>Lisans Anahtarı:</strong> <code><?php echo htmlspecialchars($lisans['lisans_anahtari']); ?></code></p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="lisans_id" value="<?php echo $lisans['id']; ?>">
                                                                        <button type="submit" name="lisans_sil" class="btn btn-danger">Sil</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-key fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">Henüz lisans bulunmuyor</h4>
                                <p class="text-muted">İlk lisansınızı oluşturmak için aşağıdaki butona tıklayın.</p>
                                <?php if ($adminMi): ?>
                                    <a href="lisans-ekle.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>İlk Lisansı Oluştur
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const toast = document.createElement('div');
                toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            Lisans anahtarı kopyalandı!
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                document.body.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 3000);
            });
        }
    </script>
</body>
</html>