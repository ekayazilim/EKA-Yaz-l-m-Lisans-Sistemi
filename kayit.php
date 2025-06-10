<?php
require_once 'config/veritabani.php';
require_once 'classes/EkaKullaniciYoneticisi.php';

$kullaniciYoneticisi = new EkaKullaniciYoneticisi();
$mesaj = '';
$mesajTipi = '';

if (isset($_POST['kayit'])) {
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $sifre = $_POST['sifre'];
    $sifreTekrar = $_POST['sifre_tekrar'];
    $telefon = trim($_POST['telefon']);
    $sirket = trim($_POST['sirket']);
    
    if (empty($ad) || empty($soyad) || empty($email) || empty($sifre)) {
        $mesaj = 'Lütfen zorunlu alanları doldurun!';
        $mesajTipi = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mesaj = 'Geçerli bir e-posta adresi girin!';
        $mesajTipi = 'danger';
    } elseif (strlen($sifre) < 6) {
        $mesaj = 'Şifre en az 6 karakter olmalıdır!';
        $mesajTipi = 'danger';
    } elseif ($sifre !== $sifreTekrar) {
        $mesaj = 'Şifreler eşleşmiyor!';
        $mesajTipi = 'danger';
    } else {
        $sonuc = $kullaniciYoneticisi->kullaniciKaydet($ad, $soyad, $email, $sifre, $telefon, $sirket);
        
        if ($sonuc['durum']) {
            $mesaj = $sonuc['mesaj'] . ' Giriş yapabilirsiniz.';
            $mesajTipi = 'success';
        } else {
            $mesaj = $sonuc['mesaj'];
            $mesajTipi = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h3>EKA Yazılım - Hesap Oluştur</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($mesaj)): ?>
                            <div class="alert alert-<?php echo $mesajTipi; ?>"><?php echo htmlspecialchars($mesaj); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ad" class="form-label">Ad *</label>
                                        <input type="text" class="form-control" id="ad" name="ad" value="<?php echo isset($_POST['ad']) ? htmlspecialchars($_POST['ad']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="soyad" class="form-label">Soyad *</label>
                                        <input type="text" class="form-control" id="soyad" name="soyad" value="<?php echo isset($_POST['soyad']) ? htmlspecialchars($_POST['soyad']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sifre" class="form-label">Şifre *</label>
                                        <input type="password" class="form-control" id="sifre" name="sifre" required>
                                        <div class="form-text">En az 6 karakter olmalıdır</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sifre_tekrar" class="form-label">Şifre Tekrar *</label>
                                        <input type="password" class="form-control" id="sifre_tekrar" name="sifre_tekrar" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefon" class="form-label">Telefon</label>
                                        <input type="tel" class="form-control" id="telefon" name="telefon" value="<?php echo isset($_POST['telefon']) ? htmlspecialchars($_POST['telefon']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sirket" class="form-label">Şirket</label>
                                        <input type="text" class="form-control" id="sirket" name="sirket" value="<?php echo isset($_POST['sirket']) ? htmlspecialchars($_POST['sirket']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="kayit" class="btn btn-success w-100">Hesap Oluştur</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-decoration-none">Zaten hesabınız var mı? Giriş yapın</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>