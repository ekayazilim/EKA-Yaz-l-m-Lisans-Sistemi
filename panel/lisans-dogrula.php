<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/veritabani.php';
require_once '../classes/EkaKullaniciYoneticisi.php';
require_once '../classes/EkaLisansYoneticisi.php';

$veritabani = new EkaVeritabani();
$baglanti = $veritabani->baglantiGetir();
$kullaniciYoneticisi = new EkaKullaniciYoneticisi($baglanti);
$lisansYoneticisi = new EkaLisansYoneticisi($baglanti);

$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);

$mesaj = '';
$mesaj_tipi = '';
$dogrulama_sonucu = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lisans_anahtari = trim($_POST['lisans_anahtari'] ?? '');
    $mac_adresi = trim($_POST['mac_adresi'] ?? '');
    $islem_tipi = $_POST['islem_tipi'] ?? 'dogrulama';
    
    if (empty($lisans_anahtari)) {
        $mesaj = 'Lisans anahtarı gereklidir.';
        $mesaj_tipi = 'danger';
    } else {
        try {
            if ($islem_tipi === 'aktivasyon') {
                if (empty($mac_adresi)) {
                    $mesaj = 'Aktivasyon için MAC adresi gereklidir.';
                    $mesaj_tipi = 'danger';
                } else {
                    $dogrulama_sonucu = $lisansYoneticisi->lisansAktivasyon($lisans_anahtari, $mac_adresi);
                    if ($dogrulama_sonucu['durum']) {
                        $mesaj = 'Lisans başarıyla aktive edildi!';
                        $mesaj_tipi = 'success';
                    } else {
                        $mesaj = $dogrulama_sonucu['mesaj'];
                        $mesaj_tipi = 'danger';
                    }
                }
            } else {
                $dogrulama_sonucu = $lisansYoneticisi->lisansDogrula($lisans_anahtari, $mac_adresi);
                if ($dogrulama_sonucu['durum']) {
                    $mesaj = 'Lisans geçerli ve aktif!';
                    $mesaj_tipi = 'success';
                } else {
                    $mesaj = $dogrulama_sonucu['mesaj'];
                    $mesaj_tipi = 'danger';
                }
            }
        } catch (Exception $e) {
            $mesaj = 'Bir hata oluştu: ' . $e->getMessage();
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
    <title>Lisans Doğrula - EKA Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="main-content">
        <?php include 'includes/sidebar.php'; ?>
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid p-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="h3 mb-0">Lisans Doğrulama</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Lisans Doğrula</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <?php if ($mesaj): ?>
                        <div class="alert alert-<?php echo $mesaj_tipi; ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?php echo $mesaj_tipi === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo htmlspecialchars($mesaj); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        Lisans Doğrulama ve Aktivasyon
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="dogrulamaForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="lisans_anahtari" class="form-label">
                                                        <i class="fas fa-key me-1"></i>
                                                        Lisans Anahtarı *
                                                    </label>
                                                    <input type="text" class="form-control" id="lisans_anahtari" 
                                                           name="lisans_anahtari" required
                                                           placeholder="XXXX-XXXX-XXXX-XXXX"
                                                           value="<?php echo htmlspecialchars($_POST['lisans_anahtari'] ?? ''); ?>">
                                                    <div class="form-text">Doğrulanacak lisans anahtarını girin</div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="mac_adresi" class="form-label">
                                                        <i class="fas fa-network-wired me-1"></i>
                                                        MAC Adresi
                                                    </label>
                                                    <input type="text" class="form-control" id="mac_adresi" 
                                                           name="mac_adresi"
                                                           placeholder="00:00:00:00:00:00"
                                                           value="<?php echo htmlspecialchars($_POST['mac_adresi'] ?? ''); ?>">
                                                    <div class="form-text">Aktivasyon için gerekli (opsiyonel)</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-cogs me-1"></i>
                                                İşlem Tipi
                                            </label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="islem_tipi" 
                                                               id="dogrulama" value="dogrulama" 
                                                               <?php echo (!isset($_POST['islem_tipi']) || $_POST['islem_tipi'] === 'dogrulama') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="dogrulama">
                                                            <i class="fas fa-search text-primary me-2"></i>
                                                            <strong>Doğrulama</strong>
                                                            <br><small class="text-muted">Lisansın geçerliliğini kontrol et</small>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="islem_tipi" 
                                                               id="aktivasyon" value="aktivasyon"
                                                               <?php echo (isset($_POST['islem_tipi']) && $_POST['islem_tipi'] === 'aktivasyon') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="aktivasyon">
                                                            <i class="fas fa-play text-success me-2"></i>
                                                            <strong>Aktivasyon</strong>
                                                            <br><small class="text-muted">Lisansı aktive et (MAC gerekli)</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <button type="button" class="btn btn-outline-secondary me-md-2" onclick="temizleForm()">
                                                <i class="fas fa-eraser me-2"></i>
                                                Temizle
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-check me-2"></i>
                                                İşlemi Gerçekleştir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <?php if ($dogrulama_sonucu && $dogrulama_sonucu['durum']): ?>
                                <div class="card mt-4">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Doğrulama Sonucu
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-info-circle text-primary me-2"></i>Lisans Bilgileri</h6>
                                                <ul class="list-unstyled">
                                                    <li><strong>Anahtar:</strong> <?php echo htmlspecialchars($dogrulama_sonucu['lisans']['anahtar']); ?></li>
                                                    <li><strong>Ürün:</strong> <?php echo htmlspecialchars($dogrulama_sonucu['lisans']['urun_ad']); ?></li>
                                                    <li><strong>Versiyon:</strong> <?php echo htmlspecialchars($dogrulama_sonucu['lisans']['urun_versiyon']); ?></li>
                                                    <li><strong>Durum:</strong> 
                                                        <span class="badge bg-<?php echo $dogrulama_sonucu['lisans']['durum'] === 'aktif' ? 'success' : 'warning'; ?>">
                                                            <?php echo ucfirst($dogrulama_sonucu['lisans']['durum']); ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-calendar text-info me-2"></i>Tarih Bilgileri</h6>
                                                <ul class="list-unstyled">
                                                    <li><strong>Başlangıç:</strong> <?php echo date('d.m.Y H:i', strtotime($dogrulama_sonucu['lisans']['baslangic_tarihi'])); ?></li>
                                                    <li><strong>Bitiş:</strong> <?php echo date('d.m.Y H:i', strtotime($dogrulama_sonucu['lisans']['bitis_tarihi'])); ?></li>
                                                    <li><strong>Son Kullanım:</strong> 
                                                        <?php echo $dogrulama_sonucu['lisans']['son_kullanim_tarihi'] ? 
                                                            date('d.m.Y H:i', strtotime($dogrulama_sonucu['lisans']['son_kullanim_tarihi'])) : 'Hiç'; ?>
                                                    </li>
                                                    <li><strong>Kullanım Sayısı:</strong> 
                                                        <span class="badge bg-info"><?php echo $dogrulama_sonucu['lisans']['kullanim_sayisi']; ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($dogrulama_sonucu['lisans']['mac_adresi'])): ?>
                                            <div class="mt-3">
                                                <h6><i class="fas fa-network-wired text-warning me-2"></i>Bağlı Cihaz</h6>
                                                <p class="mb-0">
                                                    <code><?php echo htmlspecialchars($dogrulama_sonucu['lisans']['mac_adresi']); ?></code>
                                                    <?php if (!empty($dogrulama_sonucu['lisans']['son_ip'])): ?>
                                                        <span class="text-muted">- IP: <?php echo htmlspecialchars($dogrulama_sonucu['lisans']['son_ip']); ?></span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($dogrulama_sonucu['lisans']['kisitlamalar'])): ?>
                                            <div class="mt-3">
                                                <h6><i class="fas fa-exclamation-triangle text-danger me-2"></i>Kısıtlamalar</h6>
                                                <div class="alert alert-warning mb-0">
                                                    <?php echo nl2br(htmlspecialchars($dogrulama_sonucu['lisans']['kisitlamalar'])); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Kullanım Kılavuzu
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="accordion" id="kilavuzAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="dogrulamaBaslik">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#dogrulamaIcerik">
                                                    <i class="fas fa-search me-2"></i>
                                                    Lisans Doğrulama
                                                </button>
                                            </h2>
                                            <div id="dogrulamaIcerik" class="accordion-collapse collapse show" 
                                                 data-bs-parent="#kilavuzAccordion">
                                                <div class="accordion-body">
                                                    <p><strong>Doğrulama işlemi:</strong></p>
                                                    <ol>
                                                        <li>Lisans anahtarını girin</li>
                                                        <li>"Doğrulama" seçeneğini işaretleyin</li>
                                                        <li>"İşlemi Gerçekleştir" butonuna tıklayın</li>
                                                    </ol>
                                                    <p class="text-muted small">MAC adresi opsiyoneldir ve sadece o cihaza özel doğrulama yapar.</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="aktivasyonBaslik">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#aktivasyonIcerik">
                                                    <i class="fas fa-play me-2"></i>
                                                    Lisans Aktivasyonu
                                                </button>
                                            </h2>
                                            <div id="aktivasyonIcerik" class="accordion-collapse collapse" 
                                                 data-bs-parent="#kilavuzAccordion">
                                                <div class="accordion-body">
                                                    <p><strong>Aktivasyon işlemi:</strong></p>
                                                    <ol>
                                                        <li>Lisans anahtarını girin</li>
                                                        <li>MAC adresini girin (zorunlu)</li>
                                                        <li>"Aktivasyon" seçeneğini işaretleyin</li>
                                                        <li>"İşlemi Gerçekleştir" butonuna tıklayın</li>
                                                    </ol>
                                                    <div class="alert alert-info small mb-0">
                                                        <i class="fas fa-lightbulb me-1"></i>
                                                        Aktivasyon sonrası lisans sadece belirtilen cihazda çalışır.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="hataBaslik">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#hataIcerik">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    Hata Kodları
                                                </button>
                                            </h2>
                                            <div id="hataIcerik" class="accordion-collapse collapse" 
                                                 data-bs-parent="#kilavuzAccordion">
                                                <div class="accordion-body">
                                                    <ul class="list-unstyled small">
                                                        <li><code>LISANS_BULUNAMADI</code> - Geçersiz anahtar</li>
                                                        <li><code>LISANS_PASIF</code> - Lisans deaktif</li>
                                                        <li><code>LISANS_SURESI_DOLMUS</code> - Süresi geçmiş</li>
                                                        <li><code>MAC_UYUMSUZ</code> - Farklı cihaz</li>
                                                        <li><code>ZATEN_AKTIF</code> - Önceden aktive</li>
                                                        <li><code>KULLANIM_LIMITI</code> - Limit aşıldı</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tools me-2"></i>
                                        Hızlı Araçlar
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="macAdresiniAl()">
                                            <i class="fas fa-network-wired me-2"></i>
                                            MAC Adresini Al
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="ornekLisansOlustur()">
                                            <i class="fas fa-magic me-2"></i>
                                            Örnek Lisans
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="formatKontrol()">
                                            <i class="fas fa-check me-2"></i>
                                            Format Kontrol
                                        </button>
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
        function temizleForm() {
            document.getElementById('dogrulamaForm').reset();
            document.getElementById('dogrulama').checked = true;
        }
        
        function macAdresiniAl() {
            if (navigator.userAgent.indexOf('Windows') !== -1) {
                alert('Windows için:\nKomut İstemi açın ve "getmac" komutunu çalıştırın.');
            } else if (navigator.userAgent.indexOf('Mac') !== -1) {
                alert('macOS için:\nTerminal açın ve "ifconfig en0 | grep ether" komutunu çalıştırın.');
            } else {
                alert('Linux için:\nTerminal açın ve "ip link show" veya "ifconfig" komutunu çalıştırın.');
            }
        }
        
        function ornekLisansOlustur() {
            const ornekAnahtar = 'DEMO-' + Math.random().toString(36).substr(2, 4).toUpperCase() + 
                               '-' + Math.random().toString(36).substr(2, 4).toUpperCase() + 
                               '-' + Math.random().toString(36).substr(2, 4).toUpperCase();
            document.getElementById('lisans_anahtari').value = ornekAnahtar;
            
            const ornekMac = Array.from({length: 6}, () => 
                Math.floor(Math.random() * 256).toString(16).padStart(2, '0')
            ).join(':');
            document.getElementById('mac_adresi').value = ornekMac;
        }
        
        function formatKontrol() {
            const lisansAnahtari = document.getElementById('lisans_anahtari').value;
            const macAdresi = document.getElementById('mac_adresi').value;
            
            let mesajlar = [];
            
            if (lisansAnahtari) {
                const lisansRegex = /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/;
                if (lisansRegex.test(lisansAnahtari)) {
                    mesajlar.push('✓ Lisans anahtarı formatı doğru');
                } else {
                    mesajlar.push('✗ Lisans anahtarı formatı hatalı (XXXX-XXXX-XXXX-XXXX)');
                }
            }
            
            if (macAdresi) {
                const macRegex = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
                if (macRegex.test(macAdresi)) {
                    mesajlar.push('✓ MAC adresi formatı doğru');
                } else {
                    mesajlar.push('✗ MAC adresi formatı hatalı (XX:XX:XX:XX:XX:XX)');
                }
            }
            
            if (mesajlar.length === 0) {
                mesajlar.push('Kontrol edilecek veri bulunamadı.');
            }
            
            alert(mesajlar.join('\n'));
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const aktivasyonRadio = document.getElementById('aktivasyon');
            const dogrulamaRadio = document.getElementById('dogrulama');
            const macInput = document.getElementById('mac_adresi');
            
            function toggleMacRequired() {
                if (aktivasyonRadio.checked) {
                    macInput.required = true;
                    macInput.parentElement.querySelector('.form-text').textContent = 'Aktivasyon için gerekli (zorunlu)';
                } else {
                    macInput.required = false;
                    macInput.parentElement.querySelector('.form-text').textContent = 'Aktivasyon için gerekli (opsiyonel)';
                }
            }
            
            aktivasyonRadio.addEventListener('change', toggleMacRequired);
            dogrulamaRadio.addEventListener('change', toggleMacRequired);
            
            toggleMacRequired();
        });
    </script>
</body>
</html>