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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .logo-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: rgba(255,255,255,0.9);
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-2px);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            font-weight: 500;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
        }
        
        .register-link {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .register-link:hover {
            color: #764ba2;
            transform: translateY(-1px);
        }
        
        .register-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        
        .register-link:hover::after {
            width: 100%;
        }
        
        .demo-section {
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0;
        }
        
        .demo-btn {
            border-radius: 12px;
            border: 2px solid #6c757d;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .demo-btn:hover {
            background: linear-gradient(135deg, #6c757d, #495057);
            border-color: #495057;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
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
            width: 100px;
            height: 100px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-elements::after {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 10%;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .form-animation {
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
    
    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-5">
                <div class="card login-card form-animation">
                    <div class="card-header text-white text-center">
                        <i class="fas fa-shield-alt logo-icon"></i>
                        <h3>EKA Yazılım</h3>
                        <p class="mb-0 opacity-75">Lisans Yönetim Sistemi</p>
                    </div>
                    <div class="card-body">
                        <?php if (isset($hata)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($hata); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>E-posta Adresi
                                </label>
                                <div class="input-group">
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           placeholder="ornek@email.com">
                                    <i class="fas fa-user input-icon"></i>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="sifre" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Şifre
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="sifre" name="sifre" required 
                                           placeholder="••••••••">
                                    <i class="fas fa-key input-icon"></i>
                                </div>
                            </div>
                            
                            <button type="submit" name="giris" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Giriş Yap
                            </button>
                        </form>
                        
                        <div class="demo-section mb-3">
                            <div class="text-center mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-flask me-1"></i>Demo Hesabı ile Giriş
                                </small>
                            </div>
                            <button type="button" class="btn btn-outline-secondary w-100 demo-btn" 
                                    onclick="demoGiris()">
                                <i class="fas fa-play me-2"></i>
                                Demo Hesabı Kullan
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <span class="text-muted">Hesabınız yok mu? </span>
                            <a href="kayit.php" class="register-link">
                                <i class="fas fa-user-plus me-1"></i>
                                Hesap Oluştur
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-white-50 small">
                        <i class="fas fa-shield-alt me-1"></i>
                        Güvenli bağlantı ile korunmaktasınız
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form animasyonları için basit JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });
        
        // Demo giriş fonksiyonu
        function demoGiris() {
            document.getElementById('email').value = 'ekasunucu@gmail.com';
            document.getElementById('sifre').value = 'ekasunucu@gmail.com';
            
            // Güzel animasyon efekti
            const emailInput = document.getElementById('email');
            const sifreInput = document.getElementById('sifre');
            
            emailInput.style.background = 'linear-gradient(135deg, #e3f2fd, #bbdefb)';
            sifreInput.style.background = 'linear-gradient(135deg, #e3f2fd, #bbdefb)';
            
            setTimeout(() => {
                emailInput.style.background = '';
                sifreInput.style.background = '';
            }, 1500);
            
            // Başarı mesajı
            const demoBtn = document.querySelector('.demo-btn');
            const originalText = demoBtn.innerHTML;
            demoBtn.innerHTML = '<i class="fas fa-check me-2"></i>Demo Bilgileri Yüklendi!';
            demoBtn.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            demoBtn.style.borderColor = '#28a745';
            demoBtn.style.color = 'white';
            
            setTimeout(() => {
                demoBtn.innerHTML = originalText;
                demoBtn.style.background = '';
                demoBtn.style.borderColor = '';
                demoBtn.style.color = '';
            }, 2000);
        }
    </script>
</body>
</html>