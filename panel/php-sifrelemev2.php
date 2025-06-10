<?php
session_start();

if (!isset($_SESSION['kullanici_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/veritabani.php';
require_once '../classes/EkaKullaniciYoneticisi.php';

$kullaniciYoneticisi = new EkaKullaniciYoneticisi();
$kullanici = $kullaniciYoneticisi->kullaniciBilgileriGetir($_SESSION['kullanici_id']);
$adminMi = $kullaniciYoneticisi->adminMi($_SESSION['kullanici_id']);

// Güçlü PHP Şifreleyici Sınıfı
class SecurePHPEncoder {
    private $key1 = 'EKA_SecretKey2025!@#$%^&*()';
    private $key2 = 'EKA_AnotherSecretKey987654321';
    private $key3 = 'EKA_ThirdSecretKey123ABC';
    
    public function encode($phpCode) {
        $phpCode = $this->cleanCode($phpCode);
        $encrypted = $this->encryptLayers($phpCode);
        return $this->createWorkingDecoder($encrypted);
    }
    
    private function cleanCode($code) {
        $code = trim($code);
        if (strpos($code, '<?php') === 0) {
            $code = substr($code, 5);
        }
        if (substr($code, -2) === '?>') {
            $code = substr($code, 0, -2);
        }
        return trim($code);
    }
    
    private function encryptLayers($data) {
        // 5 katmanlı şifreleme
        $layer1 = base64_encode(gzcompress($data, 9));
        $layer2 = $this->xorEncrypt($layer1, $this->key1);
        $layer3 = $this->substituteChars(base64_encode($layer2));
        $layer4 = $this->xorEncrypt($layer3, $this->key2);
        return $this->fragmentData(base64_encode($layer4));
    }
    
    private function xorEncrypt($data, $key) {
        $result = '';
        $keyLen = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }
        return $result;
    }
    
    private function substituteChars($data) {
        $from = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
        $to   = 'ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210/+=';
        return strtr($data, $from, $to);
    }
    
    private function fragmentData($data) {
        $chunks = str_split($data, 16);
        $result = [];
        
        foreach ($chunks as $i => $chunk) {
            $result[] = base64_encode($chunk) . ':' . ($i + 100);
        }
        
        return implode('|', $result);
    }
    
    private function createWorkingDecoder($encryptedData) {
        return '<?php
/*
 * 🔒 EKA Yazılım - Encrypted PHP File
 * Security Level: HIGH - 5 Layer Encryption
 * Generated: ' . date('Y-m-d H:i:s') . '
 * File Hash: ' . md5($encryptedData . time()) . '
 */

if (function_exists("xdebug_is_enabled") && @xdebug_is_enabled()) {
    die("Debug mode detected!");
}

final class EKASecureDecoder {
    private static $encrypted_data = "' . addslashes($encryptedData) . '";
    private static $key1 = "' . $this->key1 . '";
    private static $key2 = "' . $this->key2 . '";
    
    public static function decode() {
        try {
            $data = self::$encrypted_data;
            $step1 = self::defragmentData($data);
            $step2 = base64_decode($step1);
            if ($step2 === false) return false;
            $step3 = self::xorDecrypt($step2, self::$key2);
            $step4 = self::reverseSubstitution($step3);
            $step5 = base64_decode($step4);
            if ($step5 === false) return false;
            $step6 = self::xorDecrypt($step5, self::$key1);
            $step7 = base64_decode($step6);
            if ($step7 === false) return false;
            $original = @gzuncompress($step7);
            return $original;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private static function defragmentData($data) {
        $parts = explode("|", $data);
        $chunks = [];
        foreach ($parts as $part) {
            if (strpos($part, ":") !== false) {
                list($chunk, $index) = explode(":", $part);
                $chunks[intval($index) - 100] = base64_decode($chunk);
            }
        }
        ksort($chunks);
        return implode("", $chunks);
    }
    
    private static function xorDecrypt($data, $key) {
        $result = "";
        $keyLen = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }
        return $result;
    }
    
    private static function reverseSubstitution($data) {
        $from = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210/+=";
        $to   = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        return strtr($data, $from, $to);
    }
}

if (!function_exists("gzuncompress")) {
    die("Required function not available!");
}

$decoded_code = EKASecureDecoder::decode();
if ($decoded_code !== false && strlen($decoded_code) > 0) {
    eval($decoded_code);
} else {
    die("DECODE_ERROR: File corrupted or unauthorized access!");
}
unset($decoded_code);
?>';
    }
}

$mesaj = '';
$mesajTur = '';

// POST işlemi
if ($_POST && isset($_POST['php_code'])) {
    $phpCode = $_POST['php_code'];
    
    if (!empty($phpCode)) {
        try {
            $encoder = new SecurePHPEncoder();
            $encrypted = $encoder->encode($phpCode);
            
            $dosyaAdi = 'eka_encrypted_' . date('Ymd_His') . '.php';
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $dosyaAdi . '"');
            echo $encrypted;
            exit;
        } catch (Exception $e) {
            $mesaj = 'Şifreleme sırasında hata oluştu: ' . $e->getMessage();
            $mesajTur = 'danger';
        }
    } else {
        $mesaj = 'Lütfen şifrelenecek PHP kodunu girin!';
        $mesajTur = 'warning';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Şifreleyici - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .encoder-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .security-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            margin: 2px;
        }
        .code-editor {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            min-height: 400px;
        }
        .code-editor:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .feature-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .feature-card:hover {
            transform: translateY(-2px);
        }
        .security-level {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="fade-in">
                <h2 class="page-title">
                    <i class="fas fa-shield-alt me-2"></i>PHP Kod Şifreleyici
                </h2>
                
                <?php if ($mesaj): ?>
                    <div class="alert alert-<?php echo $mesajTur; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($mesaj); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="encoder-stats">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><i class="fas fa-lock me-2"></i>EKA Güvenli PHP Şifreleyici</h4>
                            <p class="mb-2">Military-grade encryption ile PHP kodlarınızı koruyun</p>
                            <div>
                                <span class="security-badge">🗜️ Gzip Compression</span>
                                <span class="security-badge">🔐 Dual XOR Encryption</span>
                                <span class="security-badge">🔀 Character Substitution</span>
                                <span class="security-badge">📦 Data Fragmentation</span>
                                <span class="security-badge">🛡️ Anti-Debug</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="h1 mb-0">
                                <i class="fas fa-shield-virus"></i>
                            </div>
                            <small>5-Layer Security</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-code me-2"></i>PHP Kod Şifreleme
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="post" id="encoderForm">
                                    <div class="mb-3">
                                        <label for="php_code" class="form-label">
                                            <i class="fas fa-file-code me-1"></i>PHP Kodunuz:
                                        </label>
                                        <textarea 
                                            name="php_code" 
                                            id="php_code" 
                                            class="form-control code-editor" 
                                            placeholder="Şifrelenecek PHP kodunu buraya yapıştırın..."
                                            required><?php echo isset($_POST['php_code']) ? htmlspecialchars($_POST['php_code']) : '<?php
// EKA Yazılım - Örnek PHP Kodu
echo "Merhaba EKA Yazılım!<br>";

$kullanicilar = [
    "admin" => ["sifre" => "123456", "rol" => "yonetici"],
    "user" => ["sifre" => "password", "rol" => "kullanici"]
];

foreach($kullanicilar as $username => $bilgiler) {
    echo "Kullanıcı: $username<br>";
    echo "Rol: " . $bilgiler["rol"] . "<br><br>";
}

function lisansDogrula($anahtar) {
    $gecerliAnahtarlar = [
        "EKA-2025-XXXX-YYYY",
        "EKA-2025-ABCD-EFGH"
    ];
    
    return in_array($anahtar, $gecerliAnahtarlar);
}

$testAnahtar = "EKA-2025-XXXX-YYYY";
if (lisansDogrula($testAnahtar)) {
    echo "✅ Lisans geçerli!";
} else {
    echo "❌ Geçersiz lisans!";
}
?>'; ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-lock me-2"></i>Şifrele ve İndir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="security-level">
                            🛡️ GÜVENLİK SEVİYESİ: MAKSIMUM
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-shield-alt fa-2x"></i>
                                </div>
                                <h6>5-Katmanlı Koruma</h6>
                                <p class="small text-muted mb-0">Çoklu şifreleme algoritması ile maksimum güvenlik</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="fas fa-bug-slash fa-2x"></i>
                                </div>
                                <h6>Anti-Debug</h6>
                                <p class="small text-muted mb-0">Debug araçlarına karşı koruma sağlar</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-compress-alt fa-2x"></i>
                                </div>
                                <h6>Gzip Compression</h6>
                                <p class="small text-muted mb-0">Dosya boyutunu küçültür ve şifreler</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-info mb-2">
                                    <i class="fas fa-random fa-2x"></i>
                                </div>
                                <h6>XOR Encryption</h6>
                                <p class="small text-muted mb-0">Çift katmanlı XOR şifreleme sistemi</p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Kullanım Bilgileri
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        PHP etiketleri otomatik temizlenir
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Şifrelenmiş dosya normal PHP gibi çalışır
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Orjinal kod tamamen gizlidir
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Tüm PHP versiyonlarıyla uyumlu
                                    </li>
                                </ul>
                                
                                <div class="alert alert-warning small mt-3">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>Uyarı:</strong> Şifrelenmiş dosyalar sadece bu sistem ile çözülebilir.
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
        // Form doğrulama
        document.getElementById('encoderForm').addEventListener('submit', function(e) {
            const code = document.getElementById('php_code').value.trim();
            if (!code) {
                e.preventDefault();
                alert('Lütfen şifrelenecek PHP kodunu girin!');
                return false;
            }
            
            // Loading state
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Şifreleniyor...';
            btn.disabled = true;
        });
        
        // Textarea auto-resize
        const textarea = document.getElementById('php_code');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(400, this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>