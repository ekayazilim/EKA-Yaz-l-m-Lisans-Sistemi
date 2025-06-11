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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.1) 10px,
                rgba(255,255,255,0.1) 20px
            );
            animation: move 20s linear infinite;
        }
        
        @keyframes move {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        .card-header h3 {
            position: relative;
            z-index: 2;
            margin: 0;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .card-header h3::before {
            content: '';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            content: '\f007';
            font-size: 2rem;
            display: block;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 2.5rem;
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            transform: translateY(-2px);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 12px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            font-weight: 500;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .text-decoration-none {
            color: #28a745;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .text-decoration-none:hover {
            color: #20c997;
            transform: translateY(-1px);
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: -1;
        }
        
        .floating-elements::before,
        .floating-elements::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-elements::before {
            width: 120px;
            height: 120px;
            top: 15%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .floating-elements::after {
            width: 180px;
            height: 180px;
            top: 70%;
            right: 5%;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .card {
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="floating-elements"></div>
    
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header text-white text-center">
                        <h3>EKA Yazılım - Hesap Oluştur</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($mesaj)): ?>
                            <div class="alert alert-<?php echo $mesajTipi; ?>">
                                <i class="fas fa-<?php echo $mesajTipi == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($mesaj); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ad" class="form-label">
                                            <i class="fas fa-user me-2"></i>Ad <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="ad" name="ad" value="<?php echo isset($_POST['ad']) ? htmlspecialchars($_POST['ad']) : ''; ?>" required placeholder="Adınız">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="soyad" class="form-label">
                                            <i class="fas fa-user me-2"></i>Soyad <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="soyad" name="soyad" value="<?php echo isset($_POST['soyad']) ? htmlspecialchars($_POST['soyad']) : ''; ?>" required placeholder="Soyadınız">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>E-posta <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required placeholder="ornek@email.com">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sifre" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Şifre <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="password" class="form-control" id="sifre" name="sifre" required placeholder="••••••••">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>En az 6 karakter olmalıdır
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sifre_tekrar" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Şifre Tekrar <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="password" class="form-control" id="sifre_tekrar" name="sifre_tekrar" required placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefon" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Telefon
                                        </label>
                                        <input type="tel" class="form-control" id="telefon" name="telefon" value="<?php echo isset($_POST['telefon']) ? htmlspecialchars($_POST['telefon']) : ''; ?>" placeholder="+90 555 123 45 67">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sirket" class="form-label">
                                            <i class="fas fa-building me-2"></i>Şirket
                                        </label>
                                        <input type="text" class="form-control" id="sirket" name="sirket" value="<?php echo isset($_POST['sirket']) ? htmlspecialchars($_POST['sirket']) : ''; ?>" placeholder="Şirket Adı">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="kayit" class="btn btn-success w-100">
                                <i class="fas fa-user-plus me-2"></i>
                                Hesap Oluştur
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                Zaten hesabınız var mı? Giriş yapın
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form animasyonları
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>