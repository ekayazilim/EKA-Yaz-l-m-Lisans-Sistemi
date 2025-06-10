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

$sonuc = '';
$sonucTur = '';

// POST işlemleri
if ($_POST) {
    $metin = $_POST['metin'] ?? '';
    $islem = $_POST['islem'] ?? '';
    
    if (!empty($metin) && !empty($islem)) {
        switch ($islem) {
            case 'md5':
                $sonuc = md5($metin);
                $sonucTur = 'MD5 Hash';
                break;
            case 'sha1':
                $sonuc = sha1($metin);
                $sonucTur = 'SHA1 Hash';
                break;
            case 'sha256':
                $sonuc = hash('sha256', $metin);
                $sonucTur = 'SHA256 Hash';
                break;
            case 'sha512':
                $sonuc = hash('sha512', $metin);
                $sonucTur = 'SHA512 Hash';
                break;
            case 'base64_encode':
                $sonuc = base64_encode($metin);
                $sonucTur = 'Base64 Encode';
                break;
            case 'base64_decode':
                $sonuc = base64_decode($metin);
                $sonucTur = 'Base64 Decode';
                break;
            case 'url_encode':
                $sonuc = urlencode($metin);
                $sonucTur = 'URL Encode';
                break;
            case 'url_decode':
                $sonuc = urldecode($metin);
                $sonucTur = 'URL Decode';
                break;
            case 'html_encode':
                $sonuc = htmlspecialchars($metin);
                $sonucTur = 'HTML Encode';
                break;
            case 'html_decode':
                $sonuc = htmlspecialchars_decode($metin);
                $sonucTur = 'HTML Decode';
                break;
            case 'password_hash':
                $sonuc = password_hash($metin, PASSWORD_DEFAULT);
                $sonucTur = 'PHP Password Hash';
                break;
            case 'crc32':
                $sonuc = hash('crc32', $metin);
                $sonucTur = 'CRC32 Hash';
                break;
            case 'mysql_hash':
                $sonuc = '*' . strtoupper(sha1(sha1($metin, true)));
                $sonucTur = 'MySQL Password Hash';
                break;
            case 'bcrypt':
                $sonuc = password_hash($metin, PASSWORD_BCRYPT);
                $sonucTur = 'BCrypt Hash';
                break;
            case 'reverse':
                $sonuc = strrev($metin);
                $sonucTur = 'Ters Çevir';
                break;
            case 'upper':
                $sonuc = strtoupper($metin);
                $sonucTur = 'Büyük Harf';
                break;
            case 'lower':
                $sonuc = strtolower($metin);
                $sonucTur = 'Küçük Harf';
                break;
            case 'length':
                $sonuc = strlen($metin);
                $sonucTur = 'Karakter Sayısı';
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifreleme Araçları - EKA Yazılım</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .tools-header {
            background: linear-gradient(135deg, #8e44ad 0%, #3498db 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .tool-card {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .tool-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .hash-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        .hash-btn {
            padding: 12px 8px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .hash-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .hash-btn.encoding {
            background: linear-gradient(135deg, #20bf6b 0%, #26d0ce 100%);
        }
        .hash-btn.utility {
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
        }
        .result-container {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            min-height: 120px;
        }
        .result-text {
            font-family: 'Courier New', monospace;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            word-break: break-all;
            white-space: pre-wrap;
            min-height: 60px;
            max-height: 200px;
            overflow-y: auto;
        }
        .input-container {
            position: relative;
        }
        .char-counter {
            position: absolute;
            bottom: 10px;
            right: 15px;
            background: rgba(0,0,0,0.1);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            color: #666;
        }
        .copy-result-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .copy-result-btn:hover {
            background: #218838;
        }
        .tool-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #8e44ad, #3498db);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5em;
        }
        .quick-examples {
            background: rgba(255,255,255,0.9);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .example-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin: 2px;
            cursor: pointer;
        }
        .example-btn:hover {
            background: #5a6268;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-item {
            text-align: center;
            background: rgba(255,255,255,0.9);
            padding: 15px;
            border-radius: 8px;
        }
        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #8e44ad;
        }
        .stat-label {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="fade-in">
                <div class="tools-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2><i class="fas fa-tools me-2"></i>Şifreleme & Hash Araçları</h2>
                            <p class="mb-2">MD5, SHA, Base64, URL encoding ve daha fazlası - Tek panelde tüm araçlar</p>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-number">16+</div>
                                    <div class="stat-label">Hash Algoritması</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">8+</div>
                                    <div class="stat-label">Encoding Türü</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">6+</div>
                                    <div class="stat-label">Yardımcı Araç</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">∞</div>
                                    <div class="stat-label">Kullanım Limiti</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="tool-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="tool-card card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>Metin Girişi
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="post" id="toolsForm">
                                    <div class="input-container mb-3">
                                        <label for="metin" class="form-label">Şifrelenecek/İşlenecek Metin:</label>
                                        <textarea 
                                            name="metin" 
                                            id="metin" 
                                            class="form-control" 
                                            rows="4" 
                                            placeholder="Metninizi buraya yazın..."
                                            oninput="updateCharCounter()"
                                        ><?php echo htmlspecialchars($_POST['metin'] ?? ''); ?></textarea>
                                        <div class="char-counter" id="charCounter">0 karakter</div>
                                    </div>
                                    
                                    <input type="hidden" name="islem" id="islem" value="">
                                    
                                    <!-- Hash Algorithms -->
                                    <h6><i class="fas fa-hashtag me-2"></i>Hash Algoritmaları</h6>
                                    <div class="hash-grid">
                                        <button type="button" class="hash-btn" onclick="submitForm('md5')">
                                            <i class="fas fa-lock me-1"></i>MD5
                                        </button>
                                        <button type="button" class="hash-btn" onclick="submitForm('sha1')">
                                            <i class="fas fa-key me-1"></i>SHA1
                                        </button>
                                        <button type="button" class="hash-btn" onclick="submitForm('sha256')">
                                            <i class="fas fa-shield-alt me-1"></i>SHA256
                                        </button>
                                        <button type="button" class="hash-btn" onclick="submitForm('sha512')">
                                            <i class="fas fa-shield-virus me-1"></i>SHA512
                                        </button>
                                        <button type="button" class="hash-btn" onclick="submitForm('crc32')">
                                            <i class="fas fa-fingerprint me-1"></i>CRC32
                                        </button>
                                        <button type="button" class="hash-btn" onclick="submitForm('mysql_hash')">
                                            <i class="fas fa-database me-1"></i>MySQL
                                        </button>
                                        <button type="button" class="hash-btn" onclick="submitForm('password_hash')">
                                            <i class="fas fa-user-lock me-1"></i>PHP Pass
                                        </button>
                                        <button type="button" class="hash-btn" onclick="submitForm('bcrypt')">
                                            <i class="fas fa-user-shield me-1"></i>BCrypt
                                        </button>
                                    </div>
                                    
                                    <!-- Encoding/Decoding -->
                                    <h6><i class="fas fa-code me-2"></i>Encoding & Decoding</h6>
                                    <div class="hash-grid">
                                        <button type="button" class="hash-btn encoding" onclick="submitForm('base64_encode')">
                                            <i class="fas fa-arrow-up me-1"></i>Base64 Enc
                                        </button>
                                        <button type="button" class="hash-btn encoding" onclick="submitForm('base64_decode')">
                                            <i class="fas fa-arrow-down me-1"></i>Base64 Dec
                                        </button>
                                        <button type="button" class="hash-btn encoding" onclick="submitForm('url_encode')">
                                            <i class="fas fa-link me-1"></i>URL Encode
                                        </button>
                                        <button type="button" class="hash-btn encoding" onclick="submitForm('url_decode')">
                                            <i class="fas fa-unlink me-1"></i>URL Decode
                                        </button>
                                        <button type="button" class="hash-btn encoding" onclick="submitForm('html_encode')">
                                            <i class="fas fa-code me-1"></i>HTML Enc
                                        </button>
                                        <button type="button" class="hash-btn encoding" onclick="submitForm('html_decode')">
                                            <i class="fas fa-code me-1"></i>HTML Dec
                                        </button>
                                    </div>
                                    
                                    <!-- Utility Tools -->
                                    <h6><i class="fas fa-wrench me-2"></i>Yardımcı Araçlar</h6>
                                    <div class="hash-grid">
                                        <button type="button" class="hash-btn utility" onclick="submitForm('reverse')">
                                            <i class="fas fa-undo me-1"></i>Ters Çevir
                                        </button>
                                        <button type="button" class="hash-btn utility" onclick="submitForm('upper')">
                                            <i class="fas fa-arrow-up me-1"></i>BÜYÜK
                                        </button>
                                        <button type="button" class="hash-btn utility" onclick="submitForm('lower')">
                                            <i class="fas fa-arrow-down me-1"></i>küçük
                                        </button>
                                        <button type="button" class="hash-btn utility" onclick="submitForm('length')">
                                            <i class="fas fa-ruler me-1"></i>Uzunluk
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Quick Examples -->
                                <div class="quick-examples">
                                    <h6><i class="fas fa-bolt me-2"></i>Hızlı Örnekler:</h6>
                                    <button class="example-btn" onclick="setExample('admin')">admin</button>
                                    <button class="example-btn" onclick="setExample('password123')">password123</button>
                                    <button class="example-btn" onclick="setExample('EKA-2025-XXXX-YYYY')">EKA-2025-XXXX-YYYY</button>
                                    <button class="example-btn" onclick="setExample('Hello World!')">Hello World!</button>
                                    <button class="example-btn" onclick="setExample('https://eka-yazilim.com')">URL örneği</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <?php if ($sonuc !== ''): ?>
                            <div class="tool-card card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-clipboard-check me-2"></i><?php echo $sonucTur; ?> Sonucu
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="result-text" id="resultText"><?php echo htmlspecialchars($sonuc); ?></div>
                                    <button class="copy-result-btn" onclick="copyResult()">
                                        <i class="fas fa-copy me-1"></i>Sonucu Kopyala
                                    </button>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Uzunluk: <?php echo strlen($sonuc); ?> karakter
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="tool-card card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Araç Bilgileri
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="helpAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hashHelp">
                                                <i class="fas fa-hashtag me-2"></i>Hash Algoritmaları
                                            </button>
                                        </h2>
                                        <div id="hashHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                            <div class="accordion-body small">
                                                <strong>MD5:</strong> Hızlı ama güvenlik açısından zayıf<br>
                                                <strong>SHA1:</strong> MD5'den güvenli ama eski<br>
                                                <strong>SHA256:</strong> Modern ve güvenli<br>
                                                <strong>SHA512:</strong> En güvenli hash<br>
                                                <strong>BCrypt:</strong> Şifre hashleme için ideal
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#encodeHelp">
                                                <i class="fas fa-code me-2"></i>Encoding Türleri
                                            </button>
                                        </h2>
                                        <div id="encodeHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                            <div class="accordion-body small">
                                                <strong>Base64:</strong> Binary veriyi text'e çevirir<br>
                                                <strong>URL Encode:</strong> URL güvenli karakterler<br>
                                                <strong>HTML Encode:</strong> HTML karakterlerini escape eder
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#utilityHelp">
                                                <i class="fas fa-wrench me-2"></i>Yardımcı Araçlar
                                            </button>
                                        </h2>
                                        <div id="utilityHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                            <div class="accordion-body small">
                                                <strong>Ters Çevir:</strong> String'i tersten yazar<br>
                                                <strong>Büyük/Küçük:</strong> Harf case değiştirir<br>
                                                <strong>Uzunluk:</strong> Karakter sayısını verir
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tool-card card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Kullanım İstatistikleri
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="stat-number text-primary">16+</div>
                                        <div class="stat-label">Algoritma</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-number text-success">∞</div>
                                        <div class="stat-label">Limit Yok</div>
                                    </div>
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
        function submitForm(islemTuru) {
            const metin = document.getElementById('metin').value.trim();
            if (!metin) {
                alert('Lütfen işlem yapılacak metni girin!');
                return;
            }
            
            document.getElementById('islem').value = islemTuru;
            document.getElementById('toolsForm').submit();
        }
        
        function updateCharCounter() {
            const metin = document.getElementById('metin').value;
            const counter = document.getElementById('charCounter');
            counter.textContent = metin.length + ' karakter';
        }
        
        function setExample(text) {
            document.getElementById('metin').value = text;
            updateCharCounter();
        }
        
        function copyResult() {
            const resultText = document.getElementById('resultText').textContent;
            navigator.clipboard.writeText(resultText).then(function() {
                const btn = event.target.closest('.copy-result-btn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Kopyalandı!';
                btn.style.background = '#17a2b8';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '#28a745';
                }, 2000);
            }).catch(function(err) {
                alert('Kopyalama başarısız: ' + err);
            });
        }
        
        // Sayfa yüklendiğinde karakter sayacını güncelle
        document.addEventListener('DOMContentLoaded', function() {
            updateCharCounter();
        });
        
        // Enter tuşu ile form gönderme engelleme
        document.getElementById('metin').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                // Ctrl+Enter ile MD5 hash yap
                submitForm('md5');
            }
        });
    </script>
</body>
</html>