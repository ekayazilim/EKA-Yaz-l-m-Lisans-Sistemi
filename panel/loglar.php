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
$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
$adminMi = $kullaniciYoneticisi->adminMi($_SESSION['kullanici_id']);

if (!$adminMi) {
    header('Location: dashboard.php');
    exit;
}

if (isset($_POST['log_temizle'])) {
    try {
        $stmt = $baglanti->prepare("DELETE FROM lisans_loglari");
        $stmt->execute();
        header('Location: loglar.php?temizlendi=1');
        exit;
    } catch (PDOException $e) {
        $hata = 'Log temizleme hatası: ' . $e->getMessage();
    }
}

$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$sayfaBasinaSatir = 50;
$baslangic = ($sayfa - 1) * $sayfaBasinaSatir;

$durum_filtre = isset($_GET['durum']) ? $_GET['durum'] : '';
$tarih_filtre = isset($_GET['tarih']) ? $_GET['tarih'] : '';

$where_kosullari = [];
$params = [];

if ($durum_filtre && in_array($durum_filtre, ['BASARILI', 'HATA', 'GECERSIZ', 'UYARI'])) {
    $where_kosullari[] = "durum = ?";
    $params[] = $durum_filtre;
}

if ($tarih_filtre) {
    $where_kosullari[] = "DATE(tarih) = ?";
    $params[] = $tarih_filtre;
}

$where_sql = !empty($where_kosullari) ? 'WHERE ' . implode(' AND ', $where_kosullari) : '';

try {
    $stmt = $baglanti->prepare("SELECT COUNT(*) FROM lisans_loglari $where_sql");
    $stmt->execute($params);
    $toplamSatir = $stmt->fetchColumn();
    
    $toplamSayfa = ceil($toplamSatir / $sayfaBasinaSatir);
    
    $stmt = $baglanti->prepare("
        SELECT * FROM lisans_loglari 
        $where_sql 
        ORDER BY tarih DESC 
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $sayfaBasinaSatir;
    $params[] = $baslangic;
    $stmt->execute($params);
    $loglar = $stmt->fetchAll();
    
    $stmt = $baglanti->query("SELECT COUNT(*) FROM lisans_loglari WHERE durum = 'BASARILI'");
    $basarili_sayisi = $stmt->fetchColumn();
    
    $stmt = $baglanti->query("SELECT COUNT(*) FROM lisans_loglari WHERE durum IN ('HATA', 'GECERSIZ')");
    $hatali_sayisi = $stmt->fetchColumn();
    
    $stmt = $baglanti->query("SELECT COUNT(*) FROM lisans_loglari WHERE durum = 'UYARI'");
    $uyari_sayisi = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $loglar = [];
    $toplamSatir = 0;
    $toplamSayfa = 0;
    $basarili_sayisi = 0;
    $hatali_sayisi = 0;
    $uyari_sayisi = 0;
    $hata = 'Veritabanı hatası: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisans Logları - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .log-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .log-entry {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 4px;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .log-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }
        .log-error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .log-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .log-info {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
        }
        .log-container {
            max-height: 600px;
            overflow-y: auto;
        }
        .filter-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border: none;
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
        .status-badge {
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 0.85em;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-badge i {
            font-size: 0.9em;
        }
        .detail-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }
        .log-table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .log-table .table {
            margin-bottom: 0;
        }
        .log-table .table thead th {
            background: linear-gradient(135deg, #495057, #6c757d);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px 12px;
        }
        .log-table .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        .log-table .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .filter-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .clear-btn {
            background: #6c757d;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .clear-btn:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
        .danger-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .danger-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }
        .detail-btn {
            background: #17a2b8;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            color: white;
            transition: all 0.3s ease;
        }
        .detail-btn:hover {
            background: #138496;
            transform: scale(1.05);
        }
        .pagination .page-link {
            border-radius: 8px;
            margin: 0 2px;
            border: none;
            color: #495057;
            font-weight: 500;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .refresh-badge {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="fade-in">
                <div class="log-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2><i class="fas fa-file-alt me-2"></i>Lisans Logları</h2>
                            <p class="mb-2">Sistemdeki tüm lisans doğrulama işlemlerinin detaylı kaydı</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="fas fa-database me-1"></i>Toplam: <?php echo $toplamSatir; ?> kayıt
                                </span>
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-check me-1"></i>Başarılı: <?php echo $basarili_sayisi; ?>
                                </span>
                                <span class="badge bg-danger me-2">
                                    <i class="fas fa-times me-1"></i>Hatalı: <?php echo $hatali_sayisi; ?>
                                </span>
                                <span class="badge bg-warning me-2">
                                    <i class="fas fa-exclamation me-1"></i>Uyarı: <?php echo $uyari_sayisi; ?>
                                </span>
                                <?php if (isset($_GET['auto_refresh'])): ?>
                                    <span class="refresh-badge">
                                        <i class="fas fa-sync-alt me-1"></i>Otomatik Yenileme
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <form method="post" class="d-inline" onsubmit="return confirm('Tüm logları silmek istediğinizden emin misiniz?')">
                                <button type="submit" name="log_temizle" class="danger-btn">
                                    <i class="fas fa-trash me-1"></i>Logları Temizle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($_GET['temizlendi'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Başarılı!</strong> Loglar başarıyla temizlendi.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($hata)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Hata!</strong> <?php echo htmlspecialchars($hata); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filtre Kartı -->
                <div class="filter-card card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filtrele ve Ara
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-flag me-1"></i>Durum Filtresi
                                </label>
                                <select name="durum" class="form-select">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="BASARILI" <?php echo $durum_filtre == 'BASARILI' ? 'selected' : ''; ?>>
                                        ✅ Başarılı
                                    </option>
                                    <option value="HATA" <?php echo $durum_filtre == 'HATA' ? 'selected' : ''; ?>>
                                        ❌ Hata
                                    </option>
                                    <option value="GECERSIZ" <?php echo $durum_filtre == 'GECERSIZ' ? 'selected' : ''; ?>>
                                        ⚠️ Geçersiz
                                    </option>
                                    <option value="UYARI" <?php echo $durum_filtre == 'UYARI' ? 'selected' : ''; ?>>
                                        🔶 Uyarı
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Tarih Filtresi
                                </label>
                                <input type="date" name="tarih" class="form-control" value="<?php echo htmlspecialchars($tarih_filtre); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="filter-btn">
                                        <i class="fas fa-search me-1"></i>Filtrele
                                    </button>
                                    <a href="loglar.php" class="clear-btn">
                                        <i class="fas fa-times me-1"></i>Temizle
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- İstatistik Kartları -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-primary"><?php echo $toplamSatir; ?></div>
                                    <div class="stats-label">Toplam Log Kaydı</div>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-database fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-success"><?php echo $basarili_sayisi ?? 0; ?></div>
                                    <div class="stats-label">Başarılı İşlemler</div>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-danger"><?php echo $hatali_sayisi ?? 0; ?></div>
                                    <div class="stats-label">Hatalı İşlemler</div>
                                </div>
                                <div class="text-danger">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-info"><?php echo count($loglar); ?></div>
                                    <div class="stats-label">Bu Sayfadaki Kayıt</div>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-list fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Log Tablosu -->
                <div class="log-table card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list-alt me-2"></i>Log Kayıtları
                            </h5>
                            <?php if ($toplamSayfa > 1): ?>
                                <small class="text-muted">
                                    Sayfa <?php echo $sayfa; ?> / <?php echo $toplamSayfa; ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($loglar)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <h5>Henüz log kaydı bulunmuyor</h5>
                                <p class="text-muted">Sistem kullanıldıkça log kayıtları burada görünecektir.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-clock me-1"></i>Tarih/Saat</th>
                                            <th><i class="fas fa-flag me-1"></i>Durum</th>
                                            <th><i class="fas fa-key me-1"></i>Lisans Anahtarı</th>
                                            <th><i class="fas fa-globe me-1"></i>Domain</th>
                                            <th><i class="fas fa-network-wired me-1"></i>IP</th>
                                            <th><i class="fas fa-exclamation-triangle me-1"></i>Hata Mesajı</th>
                                            <th><i class="fas fa-info-circle me-1"></i>Detaylar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($loglar as $log): ?>
                                            <?php
                                            $durum = 'secondary';
                                            $durumText = 'Bilinmeyen';
                                            $icon = 'fas fa-question-circle';
                                            
                                            switch($log['durum']) {
                                                case 'BASARILI':
                                                    $durum = 'success';
                                                    $durumText = 'Başarılı';
                                                    $icon = 'fas fa-check-circle';
                                                    break;
                                                case 'HATA':
                                                    $durum = 'danger';
                                                    $durumText = 'Hata';
                                                    $icon = 'fas fa-times-circle';
                                                    break;
                                                case 'GECERSIZ':
                                                    $durum = 'warning';
                                                    $durumText = 'Geçersiz';
                                                    $icon = 'fas fa-exclamation-triangle';
                                                    break;
                                                case 'UYARI':
                                                    $durum = 'warning';
                                                    $durumText = 'Uyarı';
                                                    $icon = 'fas fa-exclamation-triangle';
                                                    break;
                                            }
                                            
                                            $ekstra_veri = !empty($log['ekstra_veri']) ? json_decode($log['ekstra_veri'], true) : [];
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold"><?php echo date('d.m.Y', strtotime($log['tarih'])); ?></span>
                                                        <small class="text-muted"><?php echo date('H:i:s', strtotime($log['tarih'])); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="status-badge bg-<?php echo $durum; ?>">
                                                        <i class="<?php echo $icon; ?>"></i>
                                                        <?php echo $durumText; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <code class="d-block" style="font-size: 0.85em;">
                                                        <?php 
                                                        $anahtar = $log['lisans_anahtari'] ?? '-';
                                                        echo htmlspecialchars(strlen($anahtar) > 25 ? substr($anahtar, 0, 25) . '...' : $anahtar); 
                                                        ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <code style="font-size: 0.85em;">
                                                        <?php echo htmlspecialchars($log['domain'] ?? '-'); ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <code style="font-size: 0.85em;">
                                                        <?php echo htmlspecialchars($log['ip_adresi'] ?? '-'); ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <small class="text-danger">
                                                        <?php 
                                                        $mesaj = $log['hata_mesaji'] ?? '-';
                                                        echo htmlspecialchars(strlen($mesaj) > 30 ? substr($mesaj, 0, 30) . '...' : $mesaj); 
                                                        ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <button class="detail-btn" type="button" data-bs-toggle="collapse" data-bs-target="#detay<?php echo $log['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="collapse" id="detay<?php echo $log['id']; ?>">
                                                <td colspan="7">
                                                    <div class="detail-card">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6><i class="fas fa-server me-2"></i>Sistem Bilgileri</h6>
                                                                <table class="table table-sm table-borderless">
                                                                    <tr>
                                                                        <th width="30%">MAC Adresi:</th>
                                                                        <td><code><?php echo htmlspecialchars($log['mac_adresi'] ?? '-'); ?></code></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>User Agent:</th>
                                                                        <td><small><?php echo htmlspecialchars($log['user_agent'] ?? '-'); ?></small></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Sunucu:</th>
                                                                        <td><code><?php echo htmlspecialchars($log['sunucu_bilgisi'] ?? '-'); ?></code></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>İstek URI:</th>
                                                                        <td><code><?php echo htmlspecialchars($log['istek_uri'] ?? '-'); ?></code></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6><i class="fas fa-info-circle me-2"></i>Ek Bilgiler</h6>
                                                                <?php if (!empty($ekstra_veri)): ?>
                                                                    <table class="table table-sm table-borderless">
                                                                        <?php foreach ($ekstra_veri as $anahtar => $deger): ?>
                                                                            <tr>
                                                                                <th width="40%"><?php echo htmlspecialchars($anahtar); ?>:</th>
                                                                                <td><code><?php echo htmlspecialchars($deger); ?></code></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </table>
                                                                <?php else: ?>
                                                                    <p class="text-muted mb-0">
                                                                        <i class="fas fa-info-circle me-1"></i>
                                                                        Ek bilgi bulunmuyor
                                                                    </p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
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
                
                <!-- Sayfalama -->
                <?php if ($toplamSayfa > 1): ?>
                    <nav aria-label="Log sayfalama" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($sayfa > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?sayfa=<?php echo $sayfa - 1; ?><?php echo $durum_filtre ? '&durum=' . $durum_filtre : ''; ?><?php echo $tarih_filtre ? '&tarih=' . $tarih_filtre : ''; ?>">
                                        <i class="fas fa-chevron-left me-1"></i>Önceki
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $baslangicSayfa = max(1, $sayfa - 2);
                            $bitisSayfa = min($toplamSayfa, $sayfa + 2);
                            
                            for ($i = $baslangicSayfa; $i <= $bitisSayfa; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $sayfa ? 'active' : ''; ?>">
                                    <a class="page-link" href="?sayfa=<?php echo $i; ?><?php echo $durum_filtre ? '&durum=' . $durum_filtre : ''; ?><?php echo $tarih_filtre ? '&tarih=' . $tarih_filtre : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($sayfa < $toplamSayfa): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?sayfa=<?php echo $sayfa + 1; ?><?php echo $durum_filtre ? '&durum=' . $durum_filtre : ''; ?><?php echo $tarih_filtre ? '&tarih=' . $tarih_filtre : ''; ?>">
                                        Sonraki<i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Otomatik yenileme
        setInterval(function() {
            if (window.location.search.indexOf('auto_refresh=1') !== -1) {
                location.reload();
            }
        }, 30000);
        
        // Sayfa yüklendiğinde scroll pozisyonunu ayarla
        document.addEventListener('DOMContentLoaded', function() {
            const logContainer = document.querySelector('.log-container');
            if (logContainer && logContainer.children.length > 0) {
                logContainer.scrollTop = 0;
            }
            
            // Collapse durumunu hatırla
            const collapseElements = document.querySelectorAll('[data-bs-toggle="collapse"]');
            collapseElements.forEach(function(element) {
                element.addEventListener('click', function() {
                    const target = document.querySelector(this.getAttribute('data-bs-target'));
                    if (target) {
                        // Scroll to view
                        setTimeout(() => {
                            target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 350);
                    }
                });
            });
        });
        
        // Real-time log updates (if needed)
        function checkForNewLogs() {
            // Bu fonksiyon gerçek zamanlı log güncellemeleri için kullanılabilir
            // AJAX ile yeni logları kontrol edebilir
        }
        
        // Status filter change event
        document.querySelector('select[name="durum"]').addEventListener('change', function() {
            if (this.value) {
                this.style.borderColor = '#667eea';
                this.style.backgroundColor = '#f8f9ff';
            } else {
                this.style.borderColor = '';
                this.style.backgroundColor = '';
            }
        });
        
        // Date filter change event
        document.querySelector('input[name="tarih"]').addEventListener('change', function() {
            if (this.value) {
                this.style.borderColor = '#667eea';
                this.style.backgroundColor = '#f8f9ff';
            } else {
                this.style.borderColor = '';
                this.style.backgroundColor = '';
            }
        });
    </script>
</body>
</html>