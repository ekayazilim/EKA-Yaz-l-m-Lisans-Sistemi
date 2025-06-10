<?php
session_start();
require_once '../config/veritabani.php';
require_once '../classes/EkaKullaniciYoneticisi.php';

if (!isset($_SESSION['kullanici_id'])) {
    header('Location: ../index.php');
    exit;
}

$veritabani = new EkaVeritabani();
$baglanti = $veritabani->baglantiGetir();
$kullaniciYoneticisi = new EkaKullaniciYoneticisi($baglanti);

$mesaj = '';
$mesajTuru = '';

// Profil güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['profil_guncelle'])) {
    $ad = trim($_POST['ad'] ?? '');
    $soyad = trim($_POST['soyad'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $sirket = trim($_POST['sirket'] ?? '');
    
    if (empty($ad) || empty($soyad)) {
        $mesaj = 'Ad ve soyad alanları zorunludur.';
        $mesajTuru = 'danger';
    } else {
        try {
            $stmt = $baglanti->prepare("UPDATE kullanicilar SET ad = ?, soyad = ?, telefon = ?, sirket = ? WHERE id = ?");
            if ($stmt->execute([$ad, $soyad, $telefon, $sirket, $_SESSION['kullanici_id']])) {
                $mesaj = 'Profil bilgileriniz başarıyla güncellendi.';
                $mesajTuru = 'success';
            } else {
                $mesaj = 'Profil güncelleme işlemi başarısız.';
                $mesajTuru = 'danger';
            }
        } catch (PDOException $e) {
            $mesaj = 'Veritabanı hatası: ' . $e->getMessage();
            $mesajTuru = 'danger';
        }
    }
}

// Kullanıcı bilgilerini getir
$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
$adminMi = $kullaniciYoneticisi->adminMi($_SESSION['kullanici_id']);

// Kullanıcının istatistiklerini getir
try {
    // Kullanıcının lisans sayısı
    $stmt = $baglanti->prepare("SELECT COUNT(*) as toplam_lisans FROM lisanslar WHERE kullanici_id = ?");
    $stmt->execute([$_SESSION['kullanici_id']]);
    $lisansStats = $stmt->fetch();
    
    // Aktif lisans sayısı
    $stmt = $baglanti->prepare("SELECT COUNT(*) as aktif_lisans FROM lisanslar WHERE kullanici_id = ? AND durum = 'aktif' AND bitis_tarihi > CURDATE()");
    $stmt->execute([$_SESSION['kullanici_id']]);
    $aktifLisansStats = $stmt->fetch();
    
    // Süresi dolan lisans sayısı
    $stmt = $baglanti->prepare("SELECT COUNT(*) as suresi_dolan FROM lisanslar WHERE kullanici_id = ? AND (durum = 'suresi_dolmus' OR bitis_tarihi <= CURDATE())");
    $stmt->execute([$_SESSION['kullanici_id']]);
    $suaresiDolanStats = $stmt->fetch();
    
    // Son lisanslar
    $stmt = $baglanti->prepare("
        SELECT l.*, u.ad as urun_adi 
        FROM lisanslar l 
        LEFT JOIN urunler u ON l.urun_id = u.id 
        WHERE l.kullanici_id = ? 
        ORDER BY l.olusturma_tarihi DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['kullanici_id']]);
    $sonLisanslar = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $lisansStats = ['toplam_lisans' => 0];
    $aktifLisansStats = ['aktif_lisans' => 0];
    $suaresiDolanStats = ['suresi_dolan' => 0];
    $sonLisanslar = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .profile-card {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border: none;
            margin-bottom: 20px;
        }
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .stats-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stats-label {
            color: #6c757d;
            font-size: 0.9em;
            font-weight: 500;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-1px);
        }
        .profile-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        .profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .security-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .security-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.4);
            color: white;
            text-decoration: none;
        }
        .license-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .license-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .license-key {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.9em;
            border: 1px solid #e9ecef;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .info-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            width: 120px;
        }
        .info-value {
            color: #6c757d;
            flex: 1;
        }
        .quick-actions {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
        }
        .action-btn {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            color: #495057;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        .action-btn:hover {
            border-color: #667eea;
            color: #667eea;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="fade-in">
                <!-- Profil Header -->
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="profile-avatar me-4">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h2 class="mb-2">
                                        <?php echo htmlspecialchars(($kullanici['ad'] ?? '') . ' ' . ($kullanici['soyad'] ?? '')); ?>
                                        <?php if ($adminMi): ?>
                                            <span class="badge bg-warning ms-2">
                                                <i class="fas fa-crown me-1"></i>Admin
                                            </span>
                                        <?php endif; ?>
                                    </h2>
                                    <p class="mb-1">
                                        <i class="fas fa-envelope me-2"></i>
                                        <?php echo htmlspecialchars($kullanici['email'] ?? 'Bilinmeyen'); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar me-2"></i>
                                        Üye olma: <?php echo date('d.m.Y', strtotime($kullanici['kayit_tarihi'] ?? 'now')); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="sifre-degistir.php" class="security-btn">
                                <i class="fas fa-key me-2"></i>Şifre Değiştir
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if ($mesaj): ?>
                    <div class="alert alert-<?php echo $mesajTuru; ?> alert-dismissible fade show" role="alert">
                        <?php if ($mesajTuru == 'success'): ?>
                            <i class="fas fa-check-circle me-2"></i>
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php endif; ?>
                        <strong><?php echo $mesajTuru == 'success' ? 'Başarılı!' : 'Hata!'; ?></strong>
                        <?php echo htmlspecialchars($mesaj); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- İstatistikler -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-primary"><?php echo $lisansStats['toplam_lisans'] ?? 0; ?></div>
                                    <div class="stats-label">Toplam Lisans</div>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-key fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-success"><?php echo $aktifLisansStats['aktif_lisans'] ?? 0; ?></div>
                                    <div class="stats-label">Aktif Lisans</div>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-warning"><?php echo $suaresiDolanStats['suresi_dolan'] ?? 0; ?></div>
                                    <div class="stats-label">Süresi Dolan</div>
                                </div>
                                <div class="text-warning">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-info"><?php echo ($kullanici['rol'] ?? 'kullanici') == 'admin' ? 'Admin' : 'Kullanıcı'; ?></div>
                                    <div class="stats-label">Hesap Türü</div>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-user-tag fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Profil Bilgileri -->
                    <div class="col-lg-8">
                        <div class="profile-card card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-edit me-2"></i>Profil Bilgileri
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="ad" class="form-label">
                                                <i class="fas fa-user me-1"></i>Ad *
                                            </label>
                                            <input type="text" class="form-control" id="ad" name="ad" 
                                                   value="<?php echo htmlspecialchars($kullanici['ad'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="soyad" class="form-label">
                                                <i class="fas fa-user me-1"></i>Soyad *
                                            </label>
                                            <input type="text" class="form-control" id="soyad" name="soyad" 
                                                   value="<?php echo htmlspecialchars($kullanici['soyad'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-1"></i>E-posta
                                            </label>
                                            <input type="email" class="form-control" id="email" 
                                                   value="<?php echo htmlspecialchars($kullanici['email'] ?? ''); ?>" disabled>
                                            <div class="form-text">E-posta adresi değiştirilemez.</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="telefon" class="form-label">
                                                <i class="fas fa-phone me-1"></i>Telefon
                                            </label>
                                            <input type="tel" class="form-control" id="telefon" name="telefon" 
                                                   value="<?php echo htmlspecialchars($kullanici['telefon'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="sirket" class="form-label">
                                            <i class="fas fa-building me-1"></i>Şirket
                                        </label>
                                        <input type="text" class="form-control" id="sirket" name="sirket" 
                                               value="<?php echo htmlspecialchars($kullanici['sirket'] ?? ''); ?>">
                                    </div>
                                    
                                    <button type="submit" name="profil_guncelle" class="profile-btn">
                                        <i class="fas fa-save me-2"></i>Profili Güncelle
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hesap Bilgileri ve Hızlı İşlemler -->
                    <div class="col-lg-4">
                        <!-- Hesap Bilgileri -->
                        <div class="profile-card card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Hesap Bilgileri
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <div class="info-label">Durum:</div>
                                    <div class="info-value">
                                        <span class="status-badge badge-success">
                                            <i class="fas fa-check-circle me-1"></i>Aktif
                                        </span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Rol:</div>
                                    <div class="info-value">
                                        <?php if ($adminMi): ?>
                                            <span class="status-badge badge-warning">
                                                <i class="fas fa-crown me-1"></i>Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge badge-primary">
                                                <i class="fas fa-user me-1"></i>Kullanıcı
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Üyelik:</div>
                                    <div class="info-value">
                                        <?php 
                                        $kayitTarihi = new DateTime($kullanici['kayit_tarihi'] ?? 'now');
                                        $simdi = new DateTime();
                                        $fark = $simdi->diff($kayitTarihi);
                                        echo $fark->days . ' gün önce';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hızlı İşlemler -->
                        <div class="profile-card card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>Hızlı İşlemler
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <a href="lisanslar.php" class="action-btn">
                                        <i class="fas fa-key me-2"></i>
                                        <strong>Lisanslarım</strong><br>
                                        <small>Tüm lisansları görüntüle</small>
                                    </a>
                                    <a href="sifre-degistir.php" class="action-btn">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        <strong>Güvenlik</strong><br>
                                        <small>Şifre değiştir</small>
                                    </a>
                                    <?php if ($adminMi): ?>
                                        <a href="dashboard.php" class="action-btn">
                                            <i class="fas fa-tachometer-alt me-2"></i>
                                            <strong>Dashboard</strong><br>
                                            <small>Yönetim paneli</small>
                                        </a>
                                    <?php endif; ?>
                                    <a href="lisans-dogrula.php" class="action-btn">
                                        <i class="fas fa-search me-2"></i>
                                        <strong>Lisans Doğrula</strong><br>
                                        <small>Lisans kontrolü yap</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Son Lisanslar -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="profile-card card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Son Lisanslarım
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($sonLisanslar)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-key"></i>
                                        <h6>Henüz lisansınız bulunmuyor</h6>
                                        <p class="text-muted">Sistem yöneticisinden lisans talep edebilirsiniz.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($sonLisanslar as $lisans): ?>
                                            <div class="col-lg-6 mb-3">
                                                <div class="license-card">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($lisans['urun_adi'] ?? 'Bilinmeyen Ürün'); ?></h6>
                                                        <?php
                                                        $durum = 'secondary';
                                                        $durumText = 'Bilinmeyen';
                                                        $icon = 'fas fa-question';
                                                        
                                                        if ($lisans['durum'] == 'aktif' && strtotime($lisans['bitis_tarihi']) > time()) {
                                                            $durum = 'success';
                                                            $durumText = 'Aktif';
                                                            $icon = 'fas fa-check-circle';
                                                        } elseif ($lisans['durum'] == 'suresi_dolmus' || strtotime($lisans['bitis_tarihi']) <= time()) {
                                                            $durum = 'warning';
                                                            $durumText = 'Süresi Dolmuş';
                                                            $icon = 'fas fa-clock';
                                                        } elseif ($lisans['durum'] == 'pasif') {
                                                            $durum = 'danger';
                                                            $durumText = 'Pasif';
                                                            $icon = 'fas fa-times-circle';
                                                        }
                                                        ?>
                                                        <span class="status-badge badge-<?php echo $durum; ?>">
                                                            <i class="<?php echo $icon; ?> me-1"></i><?php echo $durumText; ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="license-key mb-2">
                                                        <?php echo htmlspecialchars($lisans['lisans_anahtari']); ?>
                                                    </div>
                                                    
                                                    <div class="row text-muted small">
                                                        <div class="col-6">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <?php echo date('d.m.Y', strtotime($lisans['bitis_tarihi'])); ?>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <i class="fas fa-globe me-1"></i>
                                                            <?php echo htmlspecialchars($lisans['domain'] ?: 'Sınırsız'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <a href="lisanslar.php" class="profile-btn">
                                            <i class="fas fa-list me-2"></i>Tüm Lisansları Görüntüle
                                        </a>
                                    </div>
                                <?php endif; ?>
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
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const ad = document.getElementById('ad').value.trim();
            const soyad = document.getElementById('soyad').value.trim();
            
            if (!ad || !soyad) {
                e.preventDefault();
                alert('Ad ve soyad alanları zorunludur!');
                return false;
            }
        });
        
        // Telefon formatı
        document.getElementById('telefon').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Sadece rakamlar
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + ' ' + value.slice(3);
                } else if (value.length <= 8) {
                    value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6);
                } else {
                    value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 8) + ' ' + value.slice(8, 10);
                }
            }
            this.value = value;
        });
        
        // License card hover effects
        document.querySelectorAll('.license-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.borderLeftColor = '#667eea';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.borderLeftColor = '#667eea';
            });
        });
        
        // Stats card animation
        document.querySelectorAll('.stats-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const number = this.querySelector('.stats-number');
                number.style.transform = 'scale(1.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                const number = this.querySelector('.stats-number');
                number.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>