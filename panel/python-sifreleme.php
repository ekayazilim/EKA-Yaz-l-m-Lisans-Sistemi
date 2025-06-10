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

// Python Şifreleyici Sınıfı
class PythonEncoder {
    private $key1 = 'EKA_PYTHON_SecretKey2025!@#$%^&*()';
    private $key2 = 'EKA_PYTHON_AnotherKey987654321';
    private $key3 = 'EKA_PYTHON_ThirdKey123ABC';
    
    public function encode($pythonCode) {
        $pythonCode = $this->cleanCode($pythonCode);
        $encrypted = $this->encryptLayers($pythonCode);
        return $this->createPythonDecoder($encrypted);
    }
    
    private function cleanCode($code) {
        $code = trim($code);
        // Python shebang'i temizle
        $code = preg_replace('/^#!.*\n/', '', $code);
        // Encoding deklarasyonlarını temizle
        $code = preg_replace('/^#.*coding[:=].*\n/', '', $code);
        return trim($code);
    }
    
    private function encryptLayers($data) {
        // 7 katmanlı Python şifreleme
        $layer1 = base64_encode(gzcompress($data, 9));
        $layer2 = $this->xorEncrypt($layer1, $this->key1);
        $layer3 = $this->caesarCipher(base64_encode($layer2), 13);
        $layer4 = $this->reverseString($layer3);
        $layer5 = $this->xorEncrypt($layer4, $this->key2);
        $layer6 = $this->hexEncode($layer5);
        return $this->fragmentData(base64_encode($layer6));
    }
    
    private function xorEncrypt($data, $key) {
        $result = '';
        $keyLen = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }
        return $result;
    }
    
    private function caesarCipher($data, $shift) {
        $result = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $char = $data[$i];
            $ascii = ord($char);
            if ($ascii >= 65 && $ascii <= 90) { // A-Z
                $result .= chr((($ascii - 65 + $shift) % 26) + 65);
            } elseif ($ascii >= 97 && $ascii <= 122) { // a-z
                $result .= chr((($ascii - 97 + $shift) % 26) + 97);
            } else {
                $result .= $char;
            }
        }
        return $result;
    }
    
    private function reverseString($data) {
        return strrev($data);
    }
    
    private function hexEncode($data) {
        return bin2hex($data);
    }
    
    private function fragmentData($data) {
        $chunks = str_split($data, 25);
        $result = [];
        
        foreach ($chunks as $i => $chunk) {
            $result[] = base64_encode($chunk) . '@' . ($i + 300);
        }
        
        return implode('|', $result);
    }
    
    private function createPythonDecoder($encryptedData) {
        return '#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
🔒 EKA Yazılım - Encrypted Python File
Security Level: MAXIMUM - 7 Layer Encryption
Generated: ' . date('Y-m-d H:i:s') . '
File Hash: ' . md5($encryptedData . time()) . '
Anti-reverse engineering protection enabled
"""

import sys
import base64
import zlib
import binascii

class EKASecurePythonDecoder:
    def __init__(self):
        self.encrypted_data = "' . addslashes($encryptedData) . '"
        self.key1 = "' . $this->key1 . '"
        self.key2 = "' . $this->key2 . '"
        
        # Anti-debug kontrolleri
        self._anti_debug_checks()
    
    def _anti_debug_checks(self):
        """Anti-debugging ve güvenlik kontrolleri"""
        import os
        
        # PDB debugger kontrolü
        if "pdb" in sys.modules:
            sys.exit("Debug mode detected!")
        
        # PYTHONDEBUG environment variable kontrolü
        if os.environ.get("PYTHONDEBUG"):
            sys.exit("Debug environment detected!")
        
        # Trace function kontrolü
        if sys.gettrace() is not None:
            sys.exit("Trace function detected!")
    
    def decode_and_execute(self):
        """7 katmanlı şifre çözme ve execution"""
        try:
            data = self.encrypted_data
            
            # 7 katmanlı şifre çözme
            step1 = self._defragment_data(data)
            step2 = base64.b64decode(step1.encode()).decode()
            step3 = self._hex_decode(step2)
            step4 = self._xor_decrypt(step3, self.key2)
            step5 = self._reverse_string(step4)
            step6 = self._caesar_decipher(step5, 13)
            step7 = base64.b64decode(step6.encode()).decode()
            step8 = self._xor_decrypt(step7, self.key1)
            step9 = base64.b64decode(step8.encode())
            
            # Gzip decompress
            original = zlib.decompress(step9).decode("utf-8")
            return original
            
        except Exception as e:
            return f"DECODE_ERROR: {str(e)}"
    
    def _defragment_data(self, data):
        """Veri parçalarını birleştir"""
        parts = data.split("|")
        chunks = {}
        
        for part in parts:
            if "@" in part:
                chunk, index = part.split("@")
                chunks[int(index) - 300] = base64.b64decode(chunk.encode()).decode()
        
        # Sıralı birleştirme
        result = ""
        for i in sorted(chunks.keys()):
            result += chunks[i]
        
        return result
    
    def _xor_decrypt(self, data, key):
        """XOR şifre çözme"""
        result = ""
        key_len = len(key)
        
        for i, char in enumerate(data):
            decrypted = ord(char) ^ ord(key[i % key_len])
            result += chr(decrypted)
        
        return result
    
    def _caesar_decipher(self, data, shift):
        """Caesar cipher şifre çözme"""
        result = ""
        
        for char in data:
            ascii_val = ord(char)
            if 65 <= ascii_val <= 90:  # A-Z
                result += chr(((ascii_val - 65 - shift + 26) % 26) + 65)
            elif 97 <= ascii_val <= 122:  # a-z
                result += chr(((ascii_val - 97 - shift + 26) % 26) + 97)
            else:
                result += char
        
        return result
    
    def _reverse_string(self, data):
        """String\'i ters çevir"""
        return data[::-1]
    
    def _hex_decode(self, hex_data):
        """Hex decode"""
        return binascii.unhexlify(hex_data.encode()).decode()

def main():
    """Ana execution fonksiyonu"""
    # Decoder\'ı başlat
    decoder = EKASecurePythonDecoder()
    
    # Şifrelenmiş kodu çöz
    decoded_code = decoder.decode_and_execute()
    
    if not decoded_code.startswith("DECODE_ERROR"):
        # Güvenli execution
        try:
            # Global namespace\'i hazırla
            global_namespace = {
                "__name__": "__main__",
                "__file__": __file__,
                "__builtins__": __builtins__
            }
            
            # Kodu çalıştır
            exec(decoded_code, global_namespace)
            
        except Exception as e:
            print(f"Execution Error: {str(e)}")
    else:
        print(decoded_code)
        sys.exit(1)

# Guard clause - sadece doğrudan çalıştırıldığında execute et
if __name__ == "__main__":
    main()
';
    }
}

$mesaj = '';
$mesajTur = '';

// POST işlemi
if ($_POST && isset($_POST['python_code'])) {
    $pythonCode = $_POST['python_code'];
    
    if (!empty($pythonCode)) {
        try {
            $encoder = new PythonEncoder();
            $encrypted = $encoder->encode($pythonCode);
            
            $dosyaAdi = 'eka_encrypted_' . date('Ymd_His') . '.py';
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $dosyaAdi . '"');
            echo $encrypted;
            exit;
        } catch (Exception $e) {
            $mesaj = 'Şifreleme sırasında hata oluştu: ' . $e->getMessage();
            $mesajTur = 'danger';
        }
    } else {
        $mesaj = 'Lütfen şifrelenecek Python kodunu girin!';
        $mesajTur = 'warning';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Python Şifreleyici - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .encoder-stats {
            background: linear-gradient(135deg, #3c6382 0%, #40739e 100%);
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
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 14px;
            line-height: 1.6;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            min-height: 400px;
            background-color: #f8f9fa;
        }
        .code-editor:focus {
            border-color: #3c6382;
            box-shadow: 0 0 0 0.2rem rgba(60, 99, 130, 0.25);
            background-color: #fff;
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
            background: linear-gradient(45deg, #3c6382, #40739e);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .python-logo {
            background: linear-gradient(45deg, #3776ab, #ffd43b);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8em;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        .python-stats {
            background: linear-gradient(135deg, #3776ab 0%, #ffd43b 100%);
            border-radius: 8px;
            padding: 15px;
            color: white;
            margin-bottom: 15px;
        }
        .version-badge {
            background: rgba(255,255,255,0.9);
            color: #3776ab;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            font-weight: bold;
            margin: 2px;
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
                    <div class="python-logo me-2">🐍</div>Python Kod Şifreleyici
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
                            <h4><i class="fab fa-python me-2"></i>EKA Python Güvenli Şifreleyici</h4>
                            <p class="mb-2">Advanced cryptography ile Python kodlarınızı koruyun</p>
                            <div>
                                <span class="security-badge">🗜️ Zlib Compression</span>
                                <span class="security-badge">🔐 Dual-XOR Encryption</span>
                                <span class="security-badge">🔄 Caesar Cipher</span>
                                <span class="security-badge">🪞 String Reversal</span>
                                <span class="security-badge">📦 Hex Encoding</span>
                                <span class="security-badge">🧩 Data Fragmentation</span>
                                <span class="security-badge">🛡️ Anti-Debug</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="h1 mb-0">
                                <i class="fab fa-python"></i>
                            </div>
                            <small>7-Layer Security</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fab fa-python me-2"></i>Python Kod Şifreleme
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="post" id="encoderForm">
                                    <div class="mb-3">
                                        <label for="python_code" class="form-label">
                                            <i class="fas fa-file-code me-1"></i>Python Kodunuz:
                                        </label>
                                        <textarea 
                                            name="python_code" 
                                            id="python_code" 
                                            class="form-control code-editor" 
                                            placeholder="Şifrelenecek Python kodunu buraya yapıştırın..."
                                            required><?php echo isset($_POST['python_code']) ? htmlspecialchars($_POST['python_code']) : '#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
EKA Yazılım - Örnek Python Kodu
Lisans Yönetim Sistemi
"""

import os
import sys
import hashlib
import datetime
from typing import List, Dict

class EKALisansYoneticisi:
    def __init__(self):
        self.gecerli_lisanslar = [
            "EKA-PY-2025-XXXX-YYYY",
            "EKA-PY-2025-ABCD-EFGH",
            "EKA-PY-2025-1234-5678"
        ]
        self.kullanicilar = {
            "admin": {"sifre": "admin123", "rol": "yonetici"},
            "user": {"sifre": "user123", "rol": "kullanici"},
            "guest": {"sifre": "guest123", "rol": "misafir"}
        }
    
    def lisans_dogrula(self, anahtar: str) -> bool:
        """Lisans anahtarını doğrular"""
        if not anahtar:
            return False
        
        # Lisans formatını kontrol et
        if len(anahtar.split("-")) != 5:
            return False
        
        # Geçerli lisanslar listesinde kontrol et
        return anahtar in self.gecerli_lisanslar
    
    def kullanici_giris(self, username: str, password: str) -> Dict:
        """Kullanıcı giriş kontrolü"""
        if username in self.kullanicilar:
            user_data = self.kullanicilar[username]
            if user_data["sifre"] == password:
                return {
                    "durum": True,
                    "mesaj": "Giriş başarılı",
                    "rol": user_data["rol"]
                }
        
        return {
            "durum": False,
            "mesaj": "Geçersiz kullanıcı adı veya şifre",
            "rol": None
        }
    
    def lisans_hash_olustur(self, anahtar: str) -> str:
        """Lisans anahtarı için hash oluşturur"""
        salt = "EKA_PYTHON_SALT_2025"
        combined = f"{anahtar}{salt}"
        return hashlib.sha256(combined.encode()).hexdigest()
    
    def sistem_bilgileri(self) -> Dict:
        """Sistem bilgilerini döndürür"""
        return {
            "python_version": sys.version,
            "platform": sys.platform,
            "current_time": datetime.datetime.now().isoformat(),
            "working_directory": os.getcwd(),
            "total_licenses": len(self.gecerli_lisanslar),
            "total_users": len(self.kullanicilar)
        }

def main():
    """Ana program fonksiyonu"""
    print("=" * 50)
    print("🐍 EKA Python Lisans Sistemi")
    print("=" * 50)
    
    # Lisans yöneticisini başlat
    lisans_yoneticisi = EKALisansYoneticisi()
    
    # Sistem bilgilerini göster
    sistem_bilgileri = lisans_yoneticisi.sistem_bilgileri()
    print(f"Python Version: {sistem_bilgileri[\'python_version\']}")
    print(f"Platform: {sistem_bilgileri[\'platform\']}")
    print(f"Toplam Lisans: {sistem_bilgileri[\'total_licenses\']}")
    print(f"Toplam Kullanıcı: {sistem_bilgileri[\'total_users\']}")
    print()
    
    # Örnek kullanıcı girişi
    print("👤 Kullanıcı Giriş Testi:")
    giris_sonucu = lisans_yoneticisi.kullanici_giris("admin", "admin123")
    print(f"Durum: {\'✅ Başarılı\' if giris_sonucu[\'durum\'] else \'❌ Başarısız\'}")
    print(f"Mesaj: {giris_sonucu[\'mesaj\']}")
    if giris_sonucu["rol"]:
        print(f"Rol: {giris_sonucu[\'rol\']}")
    print()
    
    # Örnek lisans doğrulama
    print("🔑 Lisans Doğrulama Testi:")
    test_lisanslar = [
        "EKA-PY-2025-XXXX-YYYY",
        "EKA-PY-2025-INVALID-KEY"
    ]
    
    for lisans in test_lisanslar:
        gecerli = lisans_yoneticisi.lisans_dogrula(lisans)
        print(f"Lisans: {lisans}")
        print(f"Durum: {\'✅ Geçerli\' if gecerli else \'❌ Geçersiz\'}")
        if gecerli:
            hash_value = lisans_yoneticisi.lisans_hash_olustur(lisans)
            print(f"Hash: {hash_value[:16]}...")
        print()
    
    print("🎉 EKA Python Lisans Sistemi Testi Tamamlandı!")

if __name__ == "__main__":
    main()
'; ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fab fa-python me-2"></i>Python Şifrele ve İndir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="security-level">
                            🛡️ GÜVENLİK SEVİYESİ: ADVANCED
                        </div>
                        
                        <div class="python-stats">
                            <h6><i class="fab fa-python me-2"></i>Python Uyumluluk</h6>
                            <div>
                                <span class="version-badge">Python 3.6+</span>
                                <span class="version-badge">Python 3.7+</span>
                                <span class="version-badge">Python 3.8+</span>
                                <span class="version-badge">Python 3.9+</span>
                                <span class="version-badge">Python 3.10+</span>
                                <span class="version-badge">Python 3.11+</span>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="fab fa-python fa-2x"></i>
                                </div>
                                <h6>7-Katmanlı Python Koruma</h6>
                                <p class="small text-muted mb-0">Advanced cryptography ile şifreleme</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="fas fa-cogs fa-2x"></i>
                                </div>
                                <h6>Cross-Platform</h6>
                                <p class="small text-muted mb-0">Windows, Linux, macOS uyumlu</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-memory fa-2x"></i>
                                </div>
                                <h6>Memory Safe</h6>
                                <p class="small text-muted mb-0">Bellek güvenliği koruması</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-info mb-2">
                                    <i class="fas fa-spider fa-2x"></i>
                                </div>
                                <h6>Anti-Decompile</h6>
                                <p class="small text-muted mb-0">Decompiler araçlarına karşı koruma</p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Python Özellikleri
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Shebang ve encoding otomatik
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Import statements korunur
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        __name__ == "__main__" guard
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Exception handling güvenli
                                    </li>
                                </ul>
                                
                                <div class="alert alert-success small mt-3">
                                    <i class="fab fa-python me-1"></i>
                                    <strong>Uyumlu:</strong> Tüm Python 3.6+ versiyonları desteklenir.
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
            const code = document.getElementById('python_code').value.trim();
            if (!code) {
                e.preventDefault();
                alert('Lütfen şifrelenecek Python kodunu girin!');
                return false;
            }
            
            // Loading state
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Şifreleniyor...';
            btn.disabled = true;
        });
        
        // Textarea auto-resize ve syntax highlighting simulation
        const textarea = document.getElementById('python_code');
        
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(400, this.scrollHeight) + 'px';
            
            // Python syntax detection
            if (this.value.includes('def ') || this.value.includes('class ') || this.value.includes('import ')) {
                this.style.borderColor = '#3c6382';
                this.style.backgroundColor = '#f8f9fa';
            }
        });
        
        // Tab key support
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                
                // Insert 4 spaces (Python standard)
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
        
        // Line numbers simulation
        textarea.addEventListener('scroll', function() {
            // Basit line numbering effect
            this.style.backgroundImage = 'linear-gradient(90deg, #f8f9fa 50px, transparent 50px)';
        });
    </script>
</body>
</html>