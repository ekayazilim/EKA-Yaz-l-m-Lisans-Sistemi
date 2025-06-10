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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eskiSifre = $_POST['eski_sifre'] ?? '';
    $yeniSifre = $_POST['yeni_sifre'] ?? '';
    $yeniSifreTekrar = $_POST['yeni_sifre_tekrar'] ?? '';
    
    if (empty($eskiSifre) || empty($yeniSifre) || empty($yeniSifreTekrar)) {
        $mesaj = 'Tüm alanları doldurunuz.';
        $mesajTuru = 'danger';
    } elseif ($yeniSifre !== $yeniSifreTekrar) {
        $mesaj = 'Yeni şifreler eşleşmiyor.';
        $mesajTuru = 'danger';
    } elseif (strlen($yeniSifre) < 6) {
        $mesaj = 'Yeni şifre en az 6 karakter olmalıdır.';
        $mesajTuru = 'danger';
    } else {
        $kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
        
        if ($kullanici && password_verify($eskiSifre, $kullanici['sifre'])) {
            $yeniSifreHash = password_hash($yeniSifre, PASSWORD_DEFAULT);
            
            $stmt = $baglanti->prepare("UPDATE kullanicilar SET sifre = ? WHERE id = ?");
            if ($stmt->execute([$yeniSifreHash, $_SESSION['kullanici_id']])) {
                $mesaj = 'Şifreniz başarıyla değiştirildi.';
                $mesajTuru = 'success';
            } else {
                $mesaj = 'Şifre değiştirme işlemi başarısız.';
                $mesajTuru = 'danger';
            }
        } else {
            $mesaj = 'Eski şifre hatalı.';
            $mesajTuru = 'danger';
        }
    }
}

$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .password-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .security-card {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }
        .form-control:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
            transform: translateY(-1px);
        }
        .form-control.is-valid {
            border-color: #28a745;
            background-image: none;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }
        .password-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            color: white;
        }
        .password-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.4);
            color: white;
        }
        .back-btn {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .password-strength {
            height: 6px;
            border-radius: 3px;
            margin-top: 8px;
            transition: all 0.3s ease;
        }
        .strength-weak {
            background: linear-gradient(90deg, #dc3545 0%, #dc3545 33%, #f8f9fa 33%);
        }
        .strength-medium {
            background: linear-gradient(90deg, #ffc107 0%, #ffc107 66%, #f8f9fa 66%);
        }
        .strength-strong {
            background: linear-gradient(90deg, #28a745 0%, #28a745 100%);
        }
        .input-group-password {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s ease;
        }
        .toggle-password:hover {
            color: #e74c3c;
        }
        .security-tips {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .tip-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        .tip-item i {
            margin-right: 10px;
            color: #28a745;
        }
        .security-level {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            margin-left: 10px;
        }
        .level-weak {
            background: #fee;
            color: #dc3545;
        }
        .level-medium {
            background: #fff3cd;
            color: #856404;
        }
        .level-strong {
            background: #d4edda;
            color: #155724;
        }
        .match-indicator {
            position: absolute;
            right: 50px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .match-indicator.show {
            opacity: 1;
        }
        .match-success {
            color: #28a745;
        }
        .match-error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="fade-in">
                <div class="password-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2><i class="fas fa-shield-alt me-2"></i>Güvenlik Ayarları</h2>
                            <p class="mb-2">Hesabınızın güvenliği için şifrenizi düzenli olarak değiştirin</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars(($kullanici['ad'] ?? '') . ' ' . ($kullanici['soyad'] ?? '')); ?>
                                </span>
                                <span class="badge bg-warning me-2">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?php echo htmlspecialchars($kullanici['email'] ?? 'Bilinmeyen'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="h1 mb-0">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="security-card card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-key me-2"></i>Şifre Değiştir
                                </h5>
                            </div>
                            <div class="card-body p-4">
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
                                
                                <form method="POST" id="passwordForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="eski_sifre" class="form-label">
                                                    <i class="fas fa-lock me-1"></i>Mevcut Şifre
                                                </label>
                                                <div class="input-group-password">
                                                    <input type="password" class="form-control" id="eski_sifre" name="eski_sifre" required>
                                                    <button type="button" class="toggle-password" onclick="togglePassword('eski_sifre')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="yeni_sifre" class="form-label">
                                                    <i class="fas fa-key me-1"></i>Yeni Şifre
                                                    <span class="security-level level-weak" id="strengthLevel">Zayıf</span>
                                                </label>
                                                <div class="input-group-password">
                                                    <input type="password" class="form-control" id="yeni_sifre" name="yeni_sifre" required minlength="6" oninput="checkPasswordStrength(); checkPasswordMatch();">
                                                    <button type="button" class="toggle-password" onclick="togglePassword('yeni_sifre')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="password-strength strength-weak" id="strengthBar"></div>
                                                <div class="form-text">Şifre en az 6 karakter olmalıdır. Güçlü şifre için büyük harf, küçük harf, rakam ve özel karakter kullanın.</div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="yeni_sifre_tekrar" class="form-label">
                                                    <i class="fas fa-check-double me-1"></i>Yeni Şifre (Tekrar)
                                                </label>
                                                <div class="input-group-password">
                                                    <input type="password" class="form-control" id="yeni_sifre_tekrar" name="yeni_sifre_tekrar" required minlength="6" oninput="checkPasswordMatch();">
                                                    <button type="button" class="toggle-password" onclick="togglePassword('yeni_sifre_tekrar')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <div class="match-indicator" id="matchIndicator">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-grid gap-3">
                                                <button type="submit" class="password-btn">
                                                    <i class="fas fa-save me-2"></i>Şifreyi Güvenli Şekilde Değiştir
                                                </button>
                                                <a href="profil.php" class="back-btn text-center">
                                                    <i class="fas fa-arrow-left me-2"></i>Profil Sayfasına Geri Dön
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="security-tips">
                                                <h6><i class="fas fa-lightbulb me-2"></i>Güvenli Şifre İpuçları</h6>
                                                <div class="tip-item">
                                                    <i class="fas fa-check"></i>
                                                    En az 8 karakter kullanın
                                                </div>
                                                <div class="tip-item">
                                                    <i class="fas fa-check"></i>
                                                    Büyük ve küçük harf karışımı
                                                </div>
                                                <div class="tip-item">
                                                    <i class="fas fa-check"></i>
                                                    Rakam ve özel karakter ekleyin
                                                </div>
                                                <div class="tip-item">
                                                    <i class="fas fa-check"></i>
                                                    Kişisel bilgilerinizi kullanmayın
                                                </div>
                                                <div class="tip-item">
                                                    <i class="fas fa-check"></i>
                                                    Her platformda farklı şifre
                                                </div>
                                                <div class="tip-item">
                                                    <i class="fas fa-check"></i>
                                                    Düzenli olarak değiştirin
                                                </div>
                                                
                                                <div class="mt-3 p-3 bg-white rounded">
                                                    <h6><i class="fas fa-generator me-2"></i>Örnek Güçlü Şifre:</h6>
                                                    <code id="samplePassword">EKA2025!@#Güvenli</code>
                                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="generatePassword()">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
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
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function checkPasswordStrength() {
            const password = document.getElementById('yeni_sifre').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthLevel = document.getElementById('strengthLevel');
            
            let score = 0;
            
            // Uzunluk kontrolü
            if (password.length >= 8) score += 2;
            else if (password.length >= 6) score += 1;
            
            // Karakter türü kontrolleri
            if (/[a-z]/.test(password)) score += 1;
            if (/[A-Z]/.test(password)) score += 1;
            if (/[0-9]/.test(password)) score += 1;
            if (/[^A-Za-z0-9]/.test(password)) score += 2;
            
            // Görsel güncelleme
            strengthBar.className = 'password-strength';
            strengthLevel.className = 'security-level';
            
            if (score >= 6) {
                strengthBar.classList.add('strength-strong');
                strengthLevel.classList.add('level-strong');
                strengthLevel.textContent = 'Güçlü';
            } else if (score >= 4) {
                strengthBar.classList.add('strength-medium');
                strengthLevel.classList.add('level-medium');
                strengthLevel.textContent = 'Orta';
            } else {
                strengthBar.classList.add('strength-weak');
                strengthLevel.classList.add('level-weak');
                strengthLevel.textContent = 'Zayıf';
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('yeni_sifre').value;
            const confirmPassword = document.getElementById('yeni_sifre_tekrar').value;
            const matchIndicator = document.getElementById('matchIndicator');
            const confirmInput = document.getElementById('yeni_sifre_tekrar');
            
            if (confirmPassword.length > 0) {
                matchIndicator.classList.add('show');
                
                if (password === confirmPassword) {
                    matchIndicator.className = 'match-indicator show match-success';
                    matchIndicator.innerHTML = '<i class="fas fa-check"></i>';
                    confirmInput.classList.remove('is-invalid');
                    confirmInput.classList.add('is-valid');
                    confirmInput.setCustomValidity('');
                } else {
                    matchIndicator.className = 'match-indicator show match-error';
                    matchIndicator.innerHTML = '<i class="fas fa-times"></i>';
                    confirmInput.classList.remove('is-valid');
                    confirmInput.classList.add('is-invalid');
                    confirmInput.setCustomValidity('Şifreler eşleşmiyor');
                }
            } else {
                matchIndicator.classList.remove('show');
                confirmInput.classList.remove('is-valid', 'is-invalid');
                confirmInput.setCustomValidity('');
            }
        }
        
        function generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
            let password = 'EKA2025!';
            
            for (let i = 0; i < 6; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            document.getElementById('samplePassword').textContent = password;
        }
        
        // Form submit kontrolü
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('yeni_sifre').value;
            const confirmPassword = document.getElementById('yeni_sifre_tekrar').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor! Lütfen kontrol edin.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Şifre en az 6 karakter olmalıdır!');
                return false;
            }
        });
        
        // Sayfa yüklendiğinde sample password oluştur
        document.addEventListener('DOMContentLoaded', function() {
            generatePassword();
        });
    </script>
</body>
</html>