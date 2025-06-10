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
$mesaj_tipi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad'] ?? '');
    $soyad = trim($_POST['soyad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    $telefon = trim($_POST['telefon'] ?? '');
    $sirket = trim($_POST['sirket'] ?? '');
    $rol = $_POST['rol'] ?? 'kullanici';
    $durum = $_POST['durum'] ?? 'aktif';
    
    if (empty($ad) || empty($soyad) || empty($email) || empty($sifre)) {
        $mesaj = 'Ad, soyad, e-posta ve şifre alanları zorunludur.';
        $mesaj_tipi = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mesaj = 'Geçerli bir e-posta adresi giriniz.';
        $mesaj_tipi = 'danger';
    } elseif (strlen($sifre) < 6) {
        $mesaj = 'Şifre en az 6 karakter olmalıdır.';
        $mesaj_tipi = 'danger';
    } else {
        try {
            $stmt = $baglanti->prepare("SELECT id FROM kullanicilar WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $mesaj = 'Bu e-posta adresi zaten kullanılıyor.';
                $mesaj_tipi = 'danger';
            } else {
                $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
                $stmt = $baglanti->prepare("
                    INSERT INTO kullanicilar (ad, soyad, email, sifre, telefon, sirket, rol, durum) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$ad, $soyad, $email, $sifre_hash, $telefon, $sirket, $rol, $durum]);
                $mesaj = 'Kullanıcı başarıyla eklendi!';
                $mesaj_tipi = 'success';
                $_POST = [];
            }
        } catch (PDOException $e) {
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
    <title>Kullanıcı Ekle - EKA Lisans Sistemi</title>
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
                            <h2><i class="fas fa-user-plus me-2"></i>Yeni Kullanıcı Ekle</h2>
                            <a href="kullanicilar.php" class="btn btn-secondary">
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
                                <h5 class="card-title mb-0">Kullanıcı Bilgileri</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ad" class="form-label">Ad *</label>
                                                <input type="text" class="form-control" id="ad" name="ad" 
                                                       value="<?php echo htmlspecialchars($_POST['ad'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="soyad" class="form-label">Soyad *</label>
                                                <input type="text" class="form-control" id="soyad" name="soyad" 
                                                       value="<?php echo htmlspecialchars($_POST['soyad'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">E-posta *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sifre" class="form-label">Şifre *</label>
                                                <input type="password" class="form-control" id="sifre" name="sifre" 
                                                       minlength="6" required>
                                                <div class="form-text">En az 6 karakter olmalıdır</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefon" class="form-label">Telefon</label>
                                                <input type="tel" class="form-control" id="telefon" name="telefon" 
                                                       value="<?php echo htmlspecialchars($_POST['telefon'] ?? ''); ?>" 
                                                       placeholder="+90 555 123 45 67">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sirket" class="form-label">Şirket</label>
                                                <input type="text" class="form-control" id="sirket" name="sirket" 
                                                       value="<?php echo htmlspecialchars($_POST['sirket'] ?? ''); ?>" 
                                                       placeholder="Şirket adı">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="rol" class="form-label">Rol</label>
                                                <select class="form-select" id="rol" name="rol">
                                                    <option value="kullanici" <?php echo ($_POST['rol'] ?? 'kullanici') === 'kullanici' ? 'selected' : ''; ?>>Kullanıcı</option>
                                                    <option value="admin" <?php echo ($_POST['rol'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
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
                                    
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="kullanicilar.php" class="btn btn-secondary">İptal</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Kullanıcıyı Kaydet
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
                                    <h6><i class="fas fa-info-circle me-2"></i>Kullanıcı Ekleme Hakkında</h6>
                                    <ul class="mb-0">
                                        <li>E-posta adresi benzersiz olmalıdır</li>
                                        <li>Şifre güvenli olmalı ve en az 6 karakter içermelidir</li>
                                        <li>Admin rolü tüm yetkilere sahiptir</li>
                                        <li>Kullanıcı rolü sadece kendi lisanslarını görebilir</li>
                                        <li>Pasif kullanıcılar sisteme giriş yapamaz</li>
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