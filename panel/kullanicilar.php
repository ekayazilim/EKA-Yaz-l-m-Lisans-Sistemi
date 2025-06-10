<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/veritabani.php';
require_once '../classes/EkaKullaniciYoneticisi.php';

$veritabani = new EkaVeritabani();
$baglanti = $veritabani->baglantiGetir();
$kullaniciYoneticisi = new EkaKullaniciYoneticisi($baglanti);

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
                $kullanici_id = $_POST['kullanici_id'] ?? 0;
                $durum = $_POST['durum'] ?? 'pasif';
                
                if ($kullaniciYoneticisi->kullaniciDurumGuncelle($kullanici_id, $durum)) {
                    $mesaj = 'Kullanıcı durumu başarıyla güncellendi!';
                } else {
                    $hata = 'Kullanıcı durumu güncellenirken hata oluştu!';
                }
                break;
                
            case 'rol_guncelle':
                $kullanici_id = $_POST['kullanici_id'] ?? 0;
                $rol = $_POST['rol'] ?? 'kullanici';
                
                if ($kullanici_id == $_SESSION['kullanici_id'] && $rol !== 'admin') {
                    $hata = 'Kendi admin yetkilerinizi kaldıramazsınız!';
                } else {
                    if ($kullaniciYoneticisi->kullaniciRolGuncelle($kullanici_id, $rol)) {
                        $mesaj = 'Kullanıcı rolü başarıyla güncellendi!';
                    } else {
                        $hata = 'Kullanıcı rolü güncellenirken hata oluştu!';
                    }
                }
                break;
                
            case 'sil':
                $kullanici_id = $_POST['kullanici_id'] ?? 0;
                
                if ($kullanici_id == $_SESSION['kullanici_id']) {
                    $hata = 'Kendi hesabınızı silemezsiniz!';
                } else {
                    if ($kullaniciYoneticisi->kullaniciSil($kullanici_id)) {
                        $mesaj = 'Kullanıcı başarıyla silindi!';
                    } else {
                        $hata = 'Kullanıcı silinirken hata oluştu!';
                    }
                }
                break;
        }
    }
}

$arama = $_GET['arama'] ?? '';
$durum_filtre = $_GET['durum'] ?? '';
$rol_filtre = $_GET['rol'] ?? '';

$kullanicilar = $kullaniciYoneticisi->tumKullanicilariGetir();

if (!empty($arama)) {
    $kullanicilar = array_filter($kullanicilar, function($k) use ($arama) {
        return stripos($k['ad'], $arama) !== false || 
               stripos($k['soyad'], $arama) !== false || 
               stripos($k['email'], $arama) !== false ||
               stripos($k['sirket'], $arama) !== false;
    });
}

if (!empty($durum_filtre)) {
    $kullanicilar = array_filter($kullanicilar, function($k) use ($durum_filtre) {
        return $k['durum'] === $durum_filtre;
    });
}

if (!empty($rol_filtre)) {
    $kullanicilar = array_filter($kullanicilar, function($k) use ($rol_filtre) {
        return $k['rol'] === $rol_filtre;
    });
}

$istatistikler = $kullaniciYoneticisi->kullaniciIstatistikleriGetir();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcılar - EKA Lisans Sistemi</title>
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
                        <h1 class="h3 mb-0">Kullanıcı Yönetimi</h1>
                        <div>
                            <a href="kullanici-ekle.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>
                                Yeni Kullanıcı Ekle
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
                                            <h6 class="card-title">Toplam Kullanıcı</h6>
                                            <h3 class="mb-0"><?php echo $istatistikler['toplam']; ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x opacity-75"></i>
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
                                            <h6 class="card-title">Aktif Kullanıcı</h6>
                                            <h3 class="mb-0"><?php echo $istatistikler['aktif']; ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-check fa-2x opacity-75"></i>
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
                                            <h6 class="card-title">Admin Kullanıcı</h6>
                                            <h3 class="mb-0"><?php echo $istatistikler['admin']; ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-shield fa-2x opacity-75"></i>
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
                                            <h6 class="card-title">Bu Ay Kayıt</h6>
                                            <h3 class="mb-0"><?php echo $istatistikler['bu_ay']; ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-calendar-plus fa-2x opacity-75"></i>
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
                                        Kullanıcı Listesi
                                    </h5>
                                </div>
                                <div class="col-auto">
                                    <form method="GET" class="d-flex gap-2">
                                        <input type="text" class="form-control" name="arama" 
                                               placeholder="Kullanıcı ara..." value="<?php echo htmlspecialchars($arama); ?>">
                                        <select class="form-select" name="durum">
                                            <option value="">Tüm Durumlar</option>
                                            <option value="aktif" <?php echo $durum_filtre === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="pasif" <?php echo $durum_filtre === 'pasif' ? 'selected' : ''; ?>>Pasif</option>
                                        </select>
                                        <select class="form-select" name="rol">
                                            <option value="">Tüm Roller</option>
                                            <option value="admin" <?php echo $rol_filtre === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="kullanici" <?php echo $rol_filtre === 'kullanici' ? 'selected' : ''; ?>>Kullanıcı</option>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <?php if (!empty($arama) || !empty($durum_filtre) || !empty($rol_filtre)): ?>
                                            <a href="kullanicilar.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($kullanicilar)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Kullanıcı bulunamadı</h5>
                                    <p class="text-muted">Arama kriterlerinizi değiştirin veya yeni kullanıcı ekleyin.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="kullanicilarTablosu">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Kullanıcı</th>
                                                <th>E-posta</th>
                                                <th>Telefon</th>
                                                <th>Şirket</th>
                                                <th>Rol</th>
                                                <th>Durum</th>
                                                <th>Kayıt Tarihi</th>
                                                <th>İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kullanicilar as $k): ?>
                                                <tr>
                                                    <td><?php echo $k['id']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-circle me-2">
                                                                <?php echo strtoupper(substr($k['ad'], 0, 1) . substr($k['soyad'], 0, 1)); ?>
                                                            </div>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($k['ad'] . ' ' . $k['soyad']); ?></strong>
                                                                <?php if ($k['id'] == $_SESSION['kullanici_id']): ?>
                                                                    <span class="badge bg-info ms-1">Siz</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($k['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($k['telefon'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($k['sirket'] ?? '-'); ?></td>
                                                    <td>
                                                        <?php if ($k['rol'] === 'admin'): ?>
                                                            <span class="badge bg-danger">Admin</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Kullanıcı</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($k['durum'] === 'aktif'): ?>
                                                            <span class="badge bg-success">Aktif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Pasif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small><?php echo date('d.m.Y H:i', strtotime($k['kayit_tarihi'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="kullanici-detay.php?id=<?php echo $k['id']; ?>" 
                                                               class="btn btn-outline-info" title="Detay">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            
                                                            <button type="button" class="btn btn-outline-warning" 
                                                                    onclick="durumDegistir(<?php echo $k['id']; ?>, '<?php echo $k['durum'] === 'aktif' ? 'pasif' : 'aktif'; ?>')" 
                                                                    title="<?php echo $k['durum'] === 'aktif' ? 'Pasif Yap' : 'Aktif Yap'; ?>">
                                                                <i class="fas fa-<?php echo $k['durum'] === 'aktif' ? 'user-slash' : 'user-check'; ?>"></i>
                                                            </button>
                                                            
                                                            <button type="button" class="btn btn-outline-primary" 
                                                                    onclick="rolDegistir(<?php echo $k['id']; ?>, '<?php echo $k['rol'] === 'admin' ? 'kullanici' : 'admin'; ?>')" 
                                                                    title="<?php echo $k['rol'] === 'admin' ? 'Kullanıcı Yap' : 'Admin Yap'; ?>"
                                                                    <?php echo $k['id'] == $_SESSION['kullanici_id'] ? 'disabled' : ''; ?>>
                                                                <i class="fas fa-<?php echo $k['rol'] === 'admin' ? 'user' : 'user-shield'; ?>"></i>
                                                            </button>
                                                            
                                                            <button type="button" class="btn btn-outline-danger" 
                                                                    onclick="kullaniciSil(<?php echo $k['id']; ?>, '<?php echo htmlspecialchars($k['ad'] . ' ' . $k['soyad']); ?>')" 
                                                                    title="Sil"
                                                                    <?php echo $k['id'] == $_SESSION['kullanici_id'] ? 'disabled' : ''; ?>>
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
                    <h5 class="modal-title">Kullanıcı Durumu Değiştir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu kullanıcının durumunu değiştirmek istediğinizden emin misiniz?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="durumForm">
                        <input type="hidden" name="islem" value="durum_guncelle">
                        <input type="hidden" name="kullanici_id" id="durumKullaniciId">
                        <input type="hidden" name="durum" id="yeniDurum">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-warning">Durumu Değiştir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rol Değiştirme Modal -->
    <div class="modal fade" id="rolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kullanıcı Rolü Değiştir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu kullanıcının rolünü değiştirmek istediğinizden emin misiniz?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Dikkat:</strong> Admin yetkisi verilen kullanıcılar tüm sistem özelliklerine erişebilir.
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="rolForm">
                        <input type="hidden" name="islem" value="rol_guncelle">
                        <input type="hidden" name="kullanici_id" id="rolKullaniciId">
                        <input type="hidden" name="rol" id="yeniRol">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Rolü Değiştir</button>
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
                    <h5 class="modal-title">Kullanıcı Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Dikkat!</strong> Bu işlem geri alınamaz.
                    </div>
                    <p><strong id="silinecekKullanici"></strong> kullanıcısını silmek istediğinizden emin misiniz?</p>
                    <p class="text-muted small">Bu kullanıcıya ait tüm lisanslar da silinecektir.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="silForm">
                        <input type="hidden" name="islem" value="sil">
                        <input type="hidden" name="kullanici_id" id="silKullaniciId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Kullanıcıyı Sil</button>
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
            $('#kullanicilarTablosu').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json'
                },
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true
            });
        });
        
        function durumDegistir(kullaniciId, yeniDurum) {
            document.getElementById('durumKullaniciId').value = kullaniciId;
            document.getElementById('yeniDurum').value = yeniDurum;
            
            const modal = new bootstrap.Modal(document.getElementById('durumModal'));
            modal.show();
        }
        
        function rolDegistir(kullaniciId, yeniRol) {
            document.getElementById('rolKullaniciId').value = kullaniciId;
            document.getElementById('yeniRol').value = yeniRol;
            
            const modal = new bootstrap.Modal(document.getElementById('rolModal'));
            modal.show();
        }
        
        function kullaniciSil(kullaniciId, kullaniciAd) {
            document.getElementById('silKullaniciId').value = kullaniciId;
            document.getElementById('silinecekKullanici').textContent = kullaniciAd;
            
            const modal = new bootstrap.Modal(document.getElementById('silModal'));
            modal.show();
        }
    </script>
    
    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #0056b3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</body>
</html>