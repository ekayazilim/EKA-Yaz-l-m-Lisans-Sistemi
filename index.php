<?php
session_start();
require_once 'config/veritabani.php';
require_once 'classes/EkaLisansYoneticisi.php';
require_once 'classes/EkaKullaniciYoneticisi.php';

$lisansYoneticisi = new EkaLisansYoneticisi();
$kullaniciYoneticisi = new EkaKullaniciYoneticisi();

if (isset($_POST['giris'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $sifre = $_POST['sifre'];
    
    if ($kullaniciYoneticisi->girisYap($email, $sifre)) {
        $_SESSION['kullanici_id'] = $kullaniciYoneticisi->kullaniciIdGetir($email);
        header('Location: panel/dashboard.php');
        exit;
    } else {
        $hata = 'Geçersiz giriş bilgileri!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EKA Yazılım - Lisans Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>EKA Yazılım Lisans Sistemi</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($hata)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($hata); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="sifre" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="sifre" name="sifre" required>
                            </div>
                            <button type="submit" name="giris" class="btn btn-primary w-100">Giriş Yap</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="kayit.php" class="text-decoration-none">Hesap Oluştur</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>