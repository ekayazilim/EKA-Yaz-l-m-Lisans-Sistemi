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

$tarih_baslangic = $_GET['baslangic'] ?? date('Y-m-01');
$tarih_bitis = $_GET['bitis'] ?? date('Y-m-d');
$rapor_tipi = $_GET['tip'] ?? 'genel';

$lisans_istatistikleri = $lisansYoneticisi->lisansIstatistikleriGetir();
$kullanici_istatistikleri = $kullaniciYoneticisi->kullaniciIstatistikleriGetir();
$urun_istatistikleri = $urunYoneticisi->urunIstatistikleriGetir();

$stmt = $baglanti->prepare("
    SELECT 
        DATE(l.olusturma_tarihi) as tarih,
        COUNT(*) as lisans_sayisi,
        COUNT(CASE WHEN l.durum = 'aktif' THEN 1 END) as aktif_lisans,
        COUNT(CASE WHEN l.durum = 'pasif' THEN 1 END) as pasif_lisans
    FROM lisanslar l 
    WHERE l.olusturma_tarihi BETWEEN ? AND ?
    GROUP BY DATE(l.olusturma_tarihi)
    ORDER BY tarih DESC
    LIMIT 30
");
$stmt->execute([$tarih_baslangic, $tarih_bitis . ' 23:59:59']);
$gunluk_lisanslar = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $baglanti->prepare("
    SELECT 
        u.ad as urun_ad,
        u.versiyon,
        COUNT(l.id) as lisans_sayisi,
        COUNT(CASE WHEN l.durum = 'aktif' THEN 1 END) as aktif_lisans,
        AVG(l.kullanim_sayisi) as ortalama_kullanim
    FROM urunler u
    LEFT JOIN lisanslar l ON u.id = l.urun_id
    WHERE l.olusturma_tarihi BETWEEN ? AND ?
    GROUP BY u.id, u.ad, u.versiyon
    ORDER BY lisans_sayisi DESC
");
$stmt->execute([$tarih_baslangic, $tarih_bitis . ' 23:59:59']);
$urun_raporlari = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $baglanti->prepare("
    SELECT 
        k.ad,
        k.soyad,
        k.email,
        k.sirket,
        COUNT(l.id) as lisans_sayisi,
        COUNT(CASE WHEN l.durum = 'aktif' THEN 1 END) as aktif_lisans,
        MAX(l.olusturma_tarihi) as son_aktivite
    FROM kullanicilar k
    LEFT JOIN lisanslar l ON k.id = l.kullanici_id
    WHERE l.olusturma_tarihi BETWEEN ? AND ?
    GROUP BY k.id, k.ad, k.soyad, k.email, k.sirket
    ORDER BY lisans_sayisi DESC
    LIMIT 20
");
$stmt->execute([$tarih_baslangic, $tarih_bitis . ' 23:59:59']);
$kullanici_raporlari = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $baglanti->prepare("
    SELECT 
        ll.islem_tipi,
        COUNT(*) as islem_sayisi,
        DATE(ll.tarih) as tarih
    FROM lisans_loglar ll
    JOIN lisanslar l ON ll.lisans_id = l.id
    WHERE ll.tarih BETWEEN ? AND ?
    GROUP BY ll.islem_tipi, DATE(ll.tarih)
    ORDER BY tarih DESC, islem_sayisi DESC
");
$stmt->execute([$tarih_baslangic, $tarih_bitis . ' 23:59:59']);
$aktivite_raporlari = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - EKA Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="main-content">
        <?php include 'includes/sidebar.php'; ?>
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid p-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="h3 mb-0">Raporlar ve Analizler</h1>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>
                                Yazdır
                            </button>
                            <button class="btn btn-outline-success" onclick="exportToCSV()">
                                <i class="fas fa-file-csv me-2"></i>
                                CSV İndir
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-filter me-2"></i>
                                        Rapor Filtreleri
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="GET" class="row g-3">
                                        <div class="col-md-3">
                                            <label for="baslangic" class="form-label">Başlangıç Tarihi</label>
                                            <input type="date" class="form-control" id="baslangic" name="baslangic" 
                                                   value="<?php echo htmlspecialchars($tarih_baslangic); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="bitis" class="form-label">Bitiş Tarihi</label>
                                            <input type="date" class="form-control" id="bitis" name="bitis" 
                                                   value="<?php echo htmlspecialchars($tarih_bitis); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="tip" class="form-label">Rapor Tipi</label>
                                            <select class="form-select" id="tip" name="tip">
                                                <option value="genel" <?php echo $rapor_tipi === 'genel' ? 'selected' : ''; ?>>Genel Rapor</option>
                                                <option value="detayli" <?php echo $rapor_tipi === 'detayli' ? 'selected' : ''; ?>>Detaylı Analiz</option>
                                                <option value="performans" <?php echo $rapor_tipi === 'performans' ? 'selected' : ''; ?>>Performans Raporu</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-search me-2"></i>
                                                Rapor Oluştur
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Toplam Lisans</h6>
                                            <h3 class="mb-0"><?php echo $lisans_istatistikleri['toplam']; ?></h3>
                                            <small>+<?php echo $lisans_istatistikleri['bu_ay']; ?> bu ay</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-key fa-2x opacity-75"></i>
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
                                            <h6 class="card-title">Aktif Lisans</h6>
                                            <h3 class="mb-0"><?php echo $lisans_istatistikleri['aktif']; ?></h3>
                                            <small><?php echo round(($lisans_istatistikleri['aktif'] / max($lisans_istatistikleri['toplam'], 1)) * 100, 1); ?>% oranı</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                                            <h6 class="card-title">Toplam Kullanıcı</h6>
                                            <h3 class="mb-0"><?php echo $kullanici_istatistikleri['toplam']; ?></h3>
                                            <small>+<?php echo $kullanici_istatistikleri['bu_ay']; ?> bu ay</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x opacity-75"></i>
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
                                            <h6 class="card-title">Toplam Ürün</h6>
                                            <h3 class="mb-0"><?php echo $urun_istatistikleri['toplam']; ?></h3>
                                            <small><?php echo $urun_istatistikleri['aktif']; ?> aktif</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-box fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-line me-2"></i>
                                        Günlük Lisans Oluşturma Trendi
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="gunlukTrendChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>
                                        Lisans Durum Dağılımı
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="durumDagilimChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Ürün Bazlı Lisans Dağılımı
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($urun_raporlari)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Seçilen tarih aralığında veri bulunamadı</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="urunRaporTablosu">
                                                <thead>
                                                    <tr>
                                                        <th>Ürün</th>
                                                        <th>Versiyon</th>
                                                        <th>Toplam Lisans</th>
                                                        <th>Aktif Lisans</th>
                                                        <th>Ortalama Kullanım</th>
                                                        <th>Başarı Oranı</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($urun_raporlari as $urun): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($urun['urun_ad']); ?></strong></td>
                                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($urun['versiyon']); ?></span></td>
                                                            <td><?php echo $urun['lisans_sayisi']; ?></td>
                                                            <td>
                                                                <span class="badge bg-success"><?php echo $urun['aktif_lisans']; ?></span>
                                                            </td>
                                                            <td><?php echo number_format($urun['ortalama_kullanim'], 1); ?></td>
                                                            <td>
                                                                <?php 
                                                                $oran = $urun['lisans_sayisi'] > 0 ? ($urun['aktif_lisans'] / $urun['lisans_sayisi']) * 100 : 0;
                                                                $renk = $oran >= 80 ? 'success' : ($oran >= 60 ? 'warning' : 'danger');
                                                                ?>
                                                                <span class="badge bg-<?php echo $renk; ?>"><?php echo number_format($oran, 1); ?>%</span>
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
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        En Aktif Kullanıcılar
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($kullanici_raporlari)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Veri bulunamadı</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Kullanıcı</th>
                                                        <th>Şirket</th>
                                                        <th>Lisans</th>
                                                        <th>Son Aktivite</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($kullanici_raporlari, 0, 10) as $k): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($k['ad'] . ' ' . $k['soyad']); ?></strong>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($k['email']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($k['sirket'] ?? '-'); ?></td>
                                                            <td>
                                                                <span class="badge bg-primary"><?php echo $k['lisans_sayisi']; ?></span>
                                                                <span class="badge bg-success"><?php echo $k['aktif_lisans']; ?></span>
                                                            </td>
                                                            <td>
                                                                <small><?php echo $k['son_aktivite'] ? date('d.m.Y', strtotime($k['son_aktivite'])) : 'Hiç'; ?></small>
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
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-activity me-2"></i>
                                        Sistem Aktiviteleri
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($aktivite_raporlari)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-activity fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Aktivite bulunamadı</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Tarih</th>
                                                        <th>İşlem Tipi</th>
                                                        <th>Sayı</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($aktivite_raporlari, 0, 15) as $aktivite): ?>
                                                        <tr>
                                                            <td><small><?php echo date('d.m.Y', strtotime($aktivite['tarih'])); ?></small></td>
                                                            <td>
                                                                <?php 
                                                                $islem_ikon = [
                                                                    'dogrulama' => 'fas fa-check text-success',
                                                                    'aktivasyon' => 'fas fa-play text-primary',
                                                                    'deaktivasyon' => 'fas fa-pause text-warning',
                                                                    'hata' => 'fas fa-exclamation-triangle text-danger'
                                                                ];
                                                                $ikon = $islem_ikon[$aktivite['islem_tipi']] ?? 'fas fa-info text-secondary';
                                                                ?>
                                                                <i class="<?php echo $ikon; ?> me-2"></i>
                                                                <?php echo ucfirst($aktivite['islem_tipi']); ?>
                                                            </td>
                                                            <td><span class="badge bg-secondary"><?php echo $aktivite['islem_sayisi']; ?></span></td>
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
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            $('#urunRaporTablosu').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json'
                },
                order: [[2, 'desc']],
                pageLength: 10,
                responsive: true
            });
            
            initCharts();
        });
        
        function initCharts() {
            const gunlukData = <?php echo json_encode(array_reverse($gunluk_lisanslar)); ?>;
            
            const gunlukCtx = document.getElementById('gunlukTrendChart').getContext('2d');
            new Chart(gunlukCtx, {
                type: 'line',
                data: {
                    labels: gunlukData.map(item => {
                        const date = new Date(item.tarih);
                        return date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' });
                    }),
                    datasets: [{
                        label: 'Toplam Lisans',
                        data: gunlukData.map(item => item.lisans_sayisi),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Aktif Lisans',
                        data: gunlukData.map(item => item.aktif_lisans),
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            const durumCtx = document.getElementById('durumDagilimChart').getContext('2d');
            new Chart(durumCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Aktif', 'Pasif', 'Süresi Dolmuş'],
                    datasets: [{
                        data: [
                            <?php echo $lisans_istatistikleri['aktif']; ?>,
                            <?php echo $lisans_istatistikleri['pasif']; ?>,
                            0
                        ],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        function exportToCSV() {
            const data = [];
            const table = document.getElementById('urunRaporTablosu');
            
            if (table) {
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td, th');
                    const rowData = [];
                    cols.forEach(col => {
                        rowData.push(col.textContent.trim());
                    });
                    data.push(rowData.join(','));
                });
                
                const csvContent = data.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                
                if (link.download !== undefined) {
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', 'lisans_raporu_' + new Date().toISOString().split('T')[0] + '.csv');
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        }
    </script>
</body>
</html>