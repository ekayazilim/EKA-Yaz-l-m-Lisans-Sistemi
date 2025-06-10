<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/veritabani.php';
require_once '../classes/EkaKullaniciYoneticisi.php';
require_once '../classes/EkaUrunYoneticisi.php';

$veritabani = new EkaVeritabani();
$baglanti = $veritabani->baglantiGetir();
$kullaniciYoneticisi = new EkaKullaniciYoneticisi($baglanti);
$urunYoneticisi = new EkaUrunYoneticisi($baglanti);

$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
if (!$kullaniciYoneticisi->adminMi($_SESSION['kullanici_id'])) {
    header('Location: dashboard.php');
    exit;
}

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['islem'])) {
        switch ($_POST['islem']) {
            case 'durum_guncelle':
                $urun_id = $_POST['urun_id'] ?? 0;
                $durum = $_POST['durum'] ?? 'pasif';
                
                if ($urunYoneticisi->urunDurumGuncelle($urun_id, $durum)) {
                    $mesaj = 'Ürün durumu başarıyla güncellendi!';
                } else {
                    $hata = 'Ürün durumu güncellenirken hata oluştu!';
                }
                break;
                
            case 'sil':
                $urun_id = $_POST['urun_id'] ?? 0;
                
                if ($urunYoneticisi->urunSil($urun_id)) {
                    $mesaj = 'Ürün başarıyla silindi!';
                } else {
                    $hata = 'Ürün silinirken hata oluştu!';
                }
                break;
        }
    }
}

$arama = $_GET['arama'] ?? '';
$durum_filtre = $_GET['durum'] ?? '';

if (!empty($arama)) {
    $urunler = $urunYoneticisi->urunAra($arama);
} else {
    $urunler = $urunYoneticisi->tumUrunleriGetir();
}

if (!empty($durum_filtre)) {
    $urunler = array_filter($urunler, function($urun) use ($durum_filtre) {
        return $urun['durum'] === $durum_filtre;
    });
}

$istatistikler = $urunYoneticisi->urunIstatistikleriGetir();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler - EKA Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="fade-in">
                <div class="container-fluid p-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="h3 mb-0">Ürün Yönetimi</h1>
                        <div>
                            <a href="urun-ekle.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Yeni Ürün Ekle
                            </a>
                        </div>
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
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Toplam Ürün</h6>
                                            <h3 class="mb-0"><?php echo $istatistikler['toplam']; ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-box fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Aktif Ürün</h6>
                                            <h3 class="mb-0"><?php echo $istatistikler['aktif']; ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Pasif Ürün</h6>
                                            <h3 class="mb-0"><?php echo $istatistikler['pasif']; ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-pause-circle fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Toplam Lisans</h6>
                                            <h3 class="mb-0"><?php echo $istatistikler['toplam_lisans']; ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-key fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-list me-2"></i>
                                        Ürün Listesi
                                    </h5>
                                </div>
                                <div class="col-auto">
                                    <form method="GET" class="d-flex gap-2">
                                        <input type="text" class="form-control" name="arama" 
                                               placeholder="Ürün ara..." value="<?php echo htmlspecialchars($arama); ?>">
                                        <select class="form-select" name="durum">
                                            <option value="">Tüm Durumlar</option>
                                            <option value="aktif" <?php echo $durum_filtre === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="pasif" <?php echo $durum_filtre === 'pasif' ? 'selected' : ''; ?>>Pasif</option>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <?php if (!empty($arama) || !empty($durum_filtre)): ?>
                                            <a href="urunler.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($urunler)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Henüz ürün bulunmuyor</h5>
                                    <p class="text-muted">İlk ürününüzü eklemek için yukarıdaki butonu kullanın.</p>
                                    <a href="urun-ekle.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>
                                        İlk Ürünü Ekle
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="urunlerTablosu">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Ürün Adı</th>
                                                <th>Versiyon</th>
                                                <th>Fiyat</th>
                                                <th>Durum</th>
                                                <th>Lisans Sayısı</th>
                                                <th>Oluşturma Tarihi</th>
                                                <th>İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($urunler as $urun): ?>
                                                <tr>
                                                    <td><?php echo $urun['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($urun['ad']); ?></strong>
                                                        <?php if (!empty($urun['aciklama'])): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($urun['aciklama'], 0, 50)); ?>...</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($urun['versiyon']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($urun['fiyat'] > 0): ?>
                                                            <strong><?php echo number_format($urun['fiyat'], 2); ?> ₺</strong>
                                                        <?php else: ?>
                                                            <span class="text-success">Ücretsiz</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($urun['durum'] === 'aktif'): ?>
                                                            <span class="badge bg-success">Aktif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Pasif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?php echo $urunYoneticisi->urunLisansSayisiGetir($urun['id']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small><?php echo date('d.m.Y H:i', strtotime($urun['olusturma_tarihi'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="urun-duzenle.php?id=<?php echo $urun['id']; ?>" 
                                                               class="btn btn-outline-primary" title="Düzenle">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            
                                                            <button type="button" class="btn btn-outline-warning" 
                                                                    onclick="durumDegistir(<?php echo $urun['id']; ?>, '<?php echo $urun['durum'] === 'aktif' ? 'pasif' : 'aktif'; ?>')" 
                                                                    title="<?php echo $urun['durum'] === 'aktif' ? 'Pasif Yap' : 'Aktif Yap'; ?>">
                                                                <i class="fas fa-<?php echo $urun['durum'] === 'aktif' ? 'pause' : 'play'; ?>"></i>
                                                            </button>
                                                            
                                                            <button type="button" class="btn btn-outline-danger" 
                                                                    onclick="urunSil(<?php echo $urun['id']; ?>, '<?php echo htmlspecialchars($urun['ad']); ?>')" 
                                                                    title="Sil">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    <!-- Durum Değiştirme Modal -->
    <div class="modal fade" id="durumModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ürün Durumu Değiştir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu ürünün durumunu değiştirmek istediğinizden emin misiniz?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="durumForm">
                        <input type="hidden" name="islem" value="durum_guncelle">
                        <input type="hidden" name="urun_id" id="durumUrunId">
                        <input type="hidden" name="durum" id="yeniDurum">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-warning">Durumu Değiştir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Silme Modal -->
    <div class="modal fade" id="silModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ürün Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Dikkat!</strong> Bu işlem geri alınamaz.
                    </div>
                    <p><strong id="silinecekUrun"></strong> ürününü silmek istediğinizden emin misiniz?</p>
                    <p class="text-muted small">Bu ürüne ait tüm lisanslar da silinecektir.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="silForm">
                        <input type="hidden" name="islem" value="sil">
                        <input type="hidden" name="urun_id" id="silUrunId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Ürünü Sil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            $('#urunlerTablosu').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json'
                },
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true
            });
        });
        
        function durumDegistir(urunId, yeniDurum) {
            document.getElementById('durumUrunId').value = urunId;
            document.getElementById('yeniDurum').value = yeniDurum;
            
            const modal = new bootstrap.Modal(document.getElementById('durumModal'));
            modal.show();
        }
        
        function urunSil(urunId, urunAd) {
            document.getElementById('silUrunId').value = urunId;
            document.getElementById('silinecekUrun').textContent = urunAd;
            
            const modal = new bootstrap.Modal(document.getElementById('silModal'));
            modal.show();
        }
    </script>
</body>
</html>