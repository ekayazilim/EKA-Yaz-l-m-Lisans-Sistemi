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

// ASP.NET Decoder Sınıfı
class SecureASPNetDecoder {
    private $key1 = 'EKA_ASP_SecretKey2025!@#$%^&*()';
    private $key2 = 'EKA_ASP_AnotherKey987654321';
    
    public function decode($encryptedCode) {
        try {
            // Şifrelenmiş ASP.NET dosyasından veriyi çıkar
            $dataMatch = [];
            if (preg_match('/private static string encryptedData = "([^"]+)";/', $encryptedCode, $dataMatch)) {
                $encryptedData = stripslashes($dataMatch[1]);
                return $this->decryptLayers($encryptedData);
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function decryptLayers($encryptedData) {
        try {
            // 6 katmanlı şifre çözme (tersine)
            $step1 = $this->defragmentData($encryptedData);
            $step2 = base64_decode($step1);
            if ($step2 === false) return false;
            
            $step3 = $this->hexDecode($step2);
            $step4 = $this->xorDecrypt($step3, $this->key2);
            $step5 = $this->reverseRotation($step4);
            $step6 = base64_decode($step5);
            if ($step6 === false) return false;
            
            $step7 = $this->xorDecrypt($step6, $this->key1);
            $step8 = base64_decode($step7);
            if ($step8 === false) return false;
            
            $original = @gzuncompress($step8);
            return $original;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function defragmentData($data) {
        $parts = explode("|", $data);
        $chunks = [];
        
        foreach ($parts as $part) {
            if (strpos($part, "#") !== false) {
                list($chunk, $index) = explode("#", $part);
                $chunks[intval($index) - 200] = base64_decode($chunk);
            }
        }
        
        ksort($chunks);
        return implode("", $chunks);
    }
    
    private function hexDecode($data) {
        return hex2bin($data);
    }
    
    private function xorDecrypt($data, $key) {
        $result = "";
        $keyLen = strlen($key);
        
        for ($i = 0; $i < strlen($data); $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }
        
        return $result;
    }
    
    private function reverseRotation($data) {
        $result = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $char = $data[$i];
            $ascii = ord($char);
            if ($ascii >= 65 && $ascii <= 90) { // A-Z
                $result .= chr((($ascii - 65 - 13 + 26) % 26) + 65);
            } elseif ($ascii >= 97 && $ascii <= 122) { // a-z
                $result .= chr((($ascii - 97 - 13 + 26) % 26) + 97);
            } else {
                $result .= $char;
            }
        }
        return $result;
    }
    
    public function analyzeCode($encryptedCode) {
        $analysis = [
            'file_size' => strlen($encryptedCode),
            'encryption_detected' => false,
            'security_level' => 'Unknown',
            'generation_date' => 'Unknown',
            'file_hash' => 'Unknown',
            'decoder_class' => false,
            'anti_debug' => false,
            'language' => 'Unknown',
            'framework_version' => 'Unknown',
            'page_directive' => false,
            'imports_detected' => [],
            'server_controls' => false
        ];
        
        // EKA ASP.NET şifreleme sistemini tespit et
        if (strpos($encryptedCode, 'EKASecureASPDecoder') !== false) {
            $analysis['encryption_detected'] = true;
            $analysis['security_level'] = 'MAXIMUM - 6 Layer Encryption';
            $analysis['decoder_class'] = true;
        }
        
        // Page directive kontrolü
        if (strpos($encryptedCode, '<%@ Page') !== false) {
            $analysis['page_directive'] = true;
            
            // Language detection
            if (preg_match('/Language="([^"]+)"/', $encryptedCode, $langMatch)) {
                $analysis['language'] = $langMatch[1];
            }
        }
        
        // Anti-debug kontrolü
        if (strpos($encryptedCode, 'Debugger.IsAttached') !== false) {
            $analysis['anti_debug'] = true;
        }
        
        // Import/Namespace tespiti
        $imports = [];
        if (strpos($encryptedCode, 'System.IO') !== false) $imports[] = 'System.IO';
        if (strpos($encryptedCode, 'System.Text') !== false) $imports[] = 'System.Text';
        if (strpos($encryptedCode, 'System.IO.Compression') !== false) $imports[] = 'System.IO.Compression';
        if (strpos($encryptedCode, 'System.Data') !== false) $imports[] = 'System.Data';
        if (strpos($encryptedCode, 'System.Web') !== false) $imports[] = 'System.Web';
        $analysis['imports_detected'] = $imports;
        
        // Server controls detection
        if (strpos($encryptedCode, 'runat="server"') !== false) {
            $analysis['server_controls'] = true;
        }
        
        // Tarih bilgisi çıkar
        if (preg_match('/Generated: ([^\n\r]+)/', $encryptedCode, $dateMatch)) {
            $analysis['generation_date'] = trim($dateMatch[1]);
        }
        
        // Hash bilgisi çıkar
        if (preg_match('/File Hash: ([^\n\r]+)/', $encryptedCode, $hashMatch)) {
            $analysis['file_hash'] = trim($hashMatch[1]);
        }
        
        // Framework version detection
        if (strpos($encryptedCode, '.NET Framework') !== false) {
            $analysis['framework_version'] = '.NET Framework';
        } elseif (strpos($encryptedCode, '.NET Core') !== false) {
            $analysis['framework_version'] = '.NET Core';
        } elseif (strpos($encryptedCode, '.NET 5') !== false) {
            $analysis['framework_version'] = '.NET 5+';
        }
        
        return $analysis;
    }
}

$mesaj = '';
$mesajTur = '';
$decodedCode = '';
$analysis = null;

// POST işlemi
if ($_POST) {
    if (isset($_POST['encrypted_code']) && !empty($_POST['encrypted_code'])) {
        $encryptedCode = $_POST['encrypted_code'];
        
        $decoder = new SecureASPNetDecoder();
        $analysis = $decoder->analyzeCode($encryptedCode);
        
        if (isset($_POST['decode_action'])) {
            $decodedCode = $decoder->decode($encryptedCode);
            
            if ($decodedCode !== false) {
                $mesaj = 'ASP.NET kodu başarıyla decode edildi!';
                $mesajTur = 'success';
            } else {
                $mesaj = 'ASP.NET kodu decode edilemedi. Şifrelenmiş dosya bozuk olabilir.';
                $mesajTur = 'danger';
            }
        } elseif (isset($_POST['analyze_action'])) {
            $mesaj = 'ASP.NET dosya analizi tamamlandı!';
            $mesajTur = 'info';
        }
    } else {
        $mesaj = 'Lütfen şifrelenmiş ASP.NET kodunu girin!';
        $mesajTur = 'warning';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASP.NET Decoder - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .decoder-stats {
            background: linear-gradient(135deg, #20bf6b 0%, #26d0ce 100%);
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
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            min-height: 300px;
            background-color: #f8f9fa;
        }
        .code-editor:focus {
            border-color: #20bf6b;
            box-shadow: 0 0 0 0.2rem rgba(32, 191, 107, 0.25);
            background-color: #fff;
        }
        .code-output {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
            background: #2d3748;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            min-height: 300px;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .decode-btn {
            background: linear-gradient(135deg, #20bf6b 0%, #26d0ce 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .decode-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(32, 191, 107, 0.4);
            color: white;
        }
        .analyze-btn {
            background: linear-gradient(135deg, #239b56 0%, #58d68d 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .analyze-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(35, 155, 86, 0.4);
            color: white;
        }
        .copy-btn {
            background: #28a745;
            border: none;
            border-radius: 6px;
            padding: 8px 15px;
            color: white;
            font-size: 0.9em;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .copy-btn:hover {
            background: #218838;
            transform: scale(1.05);
        }
        .analysis-card {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .analysis-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        .analysis-item:last-child {
            border-bottom: none;
        }
        .analysis-label {
            font-weight: 600;
            color: #495057;
        }
        .analysis-value {
            color: #6c757d;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.9em;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-left: 8px;
        }
        .status-success {
            background: #28a745;
        }
        .status-warning {
            background: #ffc107;
        }
        .status-danger {
            background: #dc3545;
        }
        .feature-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
            margin-bottom: 20px;
        }
        .feature-card:hover {
            transform: translateY(-2px);
        }
        .security-level {
            background: linear-gradient(45deg, #20bf6b, #26d0ce);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .file-stats {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .stat-item:last-child {
            margin-bottom: 0;
        }
        .asp-logo {
            background: linear-gradient(45deg, #239b56, #58d68d);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9em;
        }
        .import-badge {
            background: #e7f5f0;
            color: #0c5460;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            margin: 2px;
            display: inline-block;
        }
        .asp-stats {
            background: linear-gradient(135deg, #239b56 0%, #58d68d 100%);
            border-radius: 8px;
            padding: 15px;
            color: white;
            margin-bottom: 15px;
        }
        .lang-badge {
            background: #0d6efd;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="fade-in">
                <div class="decoder-stats">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2><i class="fab fa-microsoft me-2"></i>EKA ASP.NET Decoder</h2>
                            <p class="mb-2">Şifrelenmiş ASP.NET dosyalarını analiz edin ve decode edin</p>
                            <div>
                                <span class="security-badge">🏢 6-Layer Decryption</span>
                                <span class="security-badge">🔍 Code Analysis</span>
                                <span class="security-badge">📊 Namespace Detection</span>
                                <span class="security-badge">🛡️ Anti-Debug Check</span>
                                <span class="security-badge">📋 C# Source Recovery</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="asp-logo">
                                ASP
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($mesaj): ?>
                    <div class="alert alert-<?php echo $mesajTur; ?> alert-dismissible fade show" role="alert">
                        <?php if ($mesajTur == 'success'): ?>
                            <i class="fab fa-microsoft me-2"></i>
                        <?php elseif ($mesajTur == 'info'): ?>
                            <i class="fas fa-info-circle me-2"></i>
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php endif; ?>
                        <strong><?php echo ucfirst($mesajTur == 'success' ? 'Başarılı' : ($mesajTur == 'info' ? 'Bilgi' : 'Hata')); ?>!</strong>
                        <?php echo htmlspecialchars($mesaj); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-file-import me-2"></i>Şifrelenmiş ASP.NET Kodu
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="post" id="decoderForm">
                                    <div class="mb-3">
                                        <label for="encrypted_code" class="form-label">
                                            <i class="fab fa-microsoft me-1"></i>Şifrelenmiş Kod:
                                        </label>
                                        <textarea 
                                            name="encrypted_code" 
                                            id="encrypted_code" 
                                            class="form-control code-editor" 
                                            placeholder="Şifrelenmiş ASP.NET kodunu buraya yapıştırın..."
                                            required><?php echo isset($_POST['encrypted_code']) ? htmlspecialchars($_POST['encrypted_code']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="analyze_action" class="analyze-btn">
                                            <i class="fas fa-search me-2"></i>ASP.NET Analiz Et
                                        </button>
                                        <button type="submit" name="decode_action" class="decode-btn">
                                            <i class="fas fa-unlock me-2"></i>ASP.NET Decode Et
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <?php if ($decodedCode): ?>
                            <div class="card mt-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fab fa-microsoft me-2"></i>Decode Edilmiş ASP.NET Kodu
                                    </h5>
                                    <button class="copy-btn" onclick="copyDecodedCode()">
                                        <i class="fas fa-copy me-1"></i>Kopyala
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="code-output" id="decodedOutput"><?php echo htmlspecialchars($decodedCode); ?></div>
                                </div>
                                <div class="card-footer">
                                    <small class="text-muted">
                                        <i class="fab fa-microsoft me-1"></i>
                                        ASP.NET kodu başarıyla decode edildi. Toplam <?php echo strlen($decodedCode); ?> karakter.
                                        <span class="badge bg-success ms-2">IIS Ready</span>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="security-level">
                            🏢 ASP.NET DECODER: ENTERPRISE
                        </div>
                        
                        <?php if ($analysis): ?>
                            <div class="analysis-card card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-line me-2"></i>ASP.NET Dosya Analizi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="file-stats">
                                        <div class="stat-item">
                                            <span>Dosya Boyutu:</span>
                                            <strong><?php echo number_format($analysis['file_size']); ?> byte</strong>
                                        </div>
                                        <div class="stat-item">
                                            <span>ASP.NET Şifreleme:</span>
                                            <strong>
                                                <?php echo $analysis['encryption_detected'] ? 'Tespit Edildi' : 'Bulunamadı'; ?>
                                                <span class="status-indicator <?php echo $analysis['encryption_detected'] ? 'status-success' : 'status-danger'; ?>"></span>
                                            </strong>
                                        </div>
                                        <div class="stat-item">
                                            <span>Güvenlik Seviyesi:</span>
                                            <strong><?php echo $analysis['security_level']; ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="analysis-item">
                                        <div class="analysis-label">Oluşturma Tarihi:</div>
                                        <div class="analysis-value"><?php echo htmlspecialchars($analysis['generation_date']); ?></div>
                                    </div>
                                    
                                    <div class="analysis-item">
                                        <div class="analysis-label">Programming Language:</div>
                                        <div class="analysis-value">
                                            <?php if ($analysis['language'] != 'Unknown'): ?>
                                                <span class="lang-badge"><?php echo htmlspecialchars($analysis['language']); ?></span>
                                            <?php else: ?>
                                                <?php echo $analysis['language']; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="analysis-item">
                                        <div class="analysis-label">Framework:</div>
                                        <div class="analysis-value"><?php echo htmlspecialchars($analysis['framework_version']); ?></div>
                                    </div>
                                    
                                    <div class="analysis-item">
                                        <div class="analysis-label">Page Directive:</div>
                                        <div class="analysis-value">
                                            <?php echo $analysis['page_directive'] ? 'Mevcut' : 'Bulunamadı'; ?>
                                            <span class="status-indicator <?php echo $analysis['page_directive'] ? 'status-success' : 'status-warning'; ?>"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="analysis-item">
                                        <div class="analysis-label">Decoder Sınıfı:</div>
                                        <div class="analysis-value">
                                            <?php echo $analysis['decoder_class'] ? 'EKASecureASPDecoder' : 'Bulunamadı'; ?>
                                            <span class="status-indicator <?php echo $analysis['decoder_class'] ? 'status-success' : 'status-danger'; ?>"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="analysis-item">
                                        <div class="analysis-label">Anti-Debug:</div>
                                        <div class="analysis-value">
                                            <?php echo $analysis['anti_debug'] ? 'Aktif' : 'Pasif'; ?>
                                            <span class="status-indicator <?php echo $analysis['anti_debug'] ? 'status-warning' : 'status-success'; ?>"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="analysis-item">
                                        <div class="analysis-label">Server Controls:</div>
                                        <div class="analysis-value">
                                            <?php echo $analysis['server_controls'] ? 'Mevcut' : 'Bulunamadı'; ?>
                                            <span class="status-indicator <?php echo $analysis['server_controls'] ? 'status-success' : 'status-warning'; ?>"></span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($analysis['imports_detected'])): ?>
                                        <div class="mt-3">
                                            <h6><i class="fas fa-download me-2"></i>Tespit Edilen Namespace'ler:</h6>
                                            <div>
                                                <?php foreach ($analysis['imports_detected'] as $import): ?>
                                                    <span class="import-badge"><?php echo htmlspecialchars($import); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="asp-stats">
                            <h6><i class="fab fa-microsoft me-2"></i>ASP.NET Decoder Özellikleri</h6>
                            <small>🏢 Enterprise grade • 🔓 6-layer decryption • 🛡️ IIS compatible</small>
                        </div>
                        
                        <div class="feature-card card">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="fab fa-microsoft fa-2x"></i>
                                </div>
                                <h6>6-Katmanlı ASP.NET Decode</h6>
                                <p class="small text-muted mb-0">Enterprise ASP.NET decryption system</p>
                            </div>
                        </div>
                        
                        <div class="feature-card card">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-server fa-2x"></i>
                                </div>
                                <h6>IIS Analysis</h6>
                                <p class="small text-muted mb-0">Server environment detection</p>
                            </div>
                        </div>
                        
                        <div class="feature-card card">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-code fa-2x"></i>
                                </div>
                                <h6>C# / VB.NET Support</h6>
                                <p class="small text-muted mb-0">Multi-language ASP.NET support</p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>ASP.NET Decoder Bilgileri
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <i class="fab fa-microsoft text-primary me-2"></i>
                                        EKA ASP.NET şifreleme sistemi destekli
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Page directive ve language detection
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Namespace ve using statements analysis
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Server controls ve Web Forms support
                                    </li>
                                </ul>
                                
                                <div class="alert alert-info small mt-3">
                                    <i class="fab fa-microsoft me-1"></i>
                                    <strong>Not:</strong> Sadece EKA sistemi ile şifrelenmiş ASP.NET dosyaları decode edilebilir.
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
        document.getElementById('decoderForm').addEventListener('submit', function(e) {
            const code = document.getElementById('encrypted_code').value.trim();
            if (!code) {
                e.preventDefault();
                alert('Lütfen şifrelenmiş ASP.NET kodunu girin!');
                return false;
            }
            
            // Loading state
            const btn = e.submitter;
            const originalText = btn.innerHTML;
            if (btn.name === 'decode_action') {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Decode ediliyor...';
            } else {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Analiz ediliyor...';
            }
            btn.disabled = true;
            
            // Form submit sonrası reset için
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 5000);
        });
        
        // Textarea auto-resize ve ASP.NET syntax detection
        const textarea = document.getElementById('encrypted_code');
        
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(300, this.scrollHeight) + 'px';
            
            // ASP.NET encryption detection
            if (this.value.includes('EKASecureASPDecoder') || this.value.includes('encryptedData')) {
                this.style.borderColor = '#20bf6b';
                this.style.backgroundColor = '#f0fff0';
            } else if (this.value.includes('<%@') || this.value.includes('runat="server"')) {
                this.style.borderColor = '#20bf6b';
                this.style.backgroundColor = '#f8f9fa';
            } else {
                this.style.borderColor = '#e9ecef';
                this.style.backgroundColor = '#f8f9fa';
            }
        });
        
        // Copy decoded code
        function copyDecodedCode() {
            const decodedOutput = document.getElementById('decodedOutput');
            const text = decodedOutput.textContent;
            
            navigator.clipboard.writeText(text).then(function() {
                const btn = event.target.closest('.copy-btn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fab fa-microsoft me-1"></i>Kopyalandı!';
                btn.style.background = '#17a2b8';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '#28a745';
                }, 2000);
            }).catch(function(err) {
                alert('Kopyalama başarısız: ' + err);
            });
        }
        
        // Drag and drop support for ASP.NET files
        const textarea = document.getElementById('encrypted_code');
        
        textarea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#20bf6b';
            this.style.backgroundColor = '#f0fff0';
        });
        
        textarea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#e9ecef';
            this.style.backgroundColor = '#f8f9fa';
        });
        
        textarea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#e9ecef';
            this.style.backgroundColor = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.name.endsWith('.aspx') || file.name.endsWith('.aspx.cs') || file.type === 'text/plain') {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        textarea.value = e.target.result;
                        textarea.dispatchEvent(new Event('input'));
                    };
                    reader.readAsText(file);
                } else {
                    alert('Lütfen sadece .aspx veya .aspx.cs ASP.NET dosyası sürükleyin!');
                }
            }
        });
        
        // ASP.NET specific syntax highlighting simulation
        textarea.addEventListener('keyup', function() {
            const value = this.value;
            
            // Check for ASP.NET patterns
            if (value.includes('<%@') && value.includes('%>')) {
                // Page directive detected
                this.style.borderColor = '#20bf6b';
            } else if (value.includes('runat="server"')) {
                // Server controls detected
                this.style.borderColor = '#239b56';
            } else if (value.includes('Response.Write') || value.includes('Page_Load')) {
                // Code-behind patterns
                this.style.borderColor = '#26d0ce';
            }
        });
    </script>
</body>
</html>