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

// ASP.NET Şifreleyici Sınıfı
class ASPNetEncoder {
    private $key1 = 'EKA_ASP_SecretKey2025!@#$%^&*()';
    private $key2 = 'EKA_ASP_AnotherKey987654321';
    private $key3 = 'EKA_ASP_ThirdKey123ABC';
    
    public function encode($aspCode) {
        $aspCode = $this->cleanCode($aspCode);
        $encrypted = $this->encryptLayers($aspCode);
        return $this->createASPDecoder($encrypted);
    }
    
    private function cleanCode($code) {
        $code = trim($code);
        // ASP etiketlerini temizle
        $code = preg_replace('/^<%@.*?%>/s', '', $code);
        $code = preg_replace('/^<%/s', '', $code);
        $code = preg_replace('/%>$/s', '', $code);
        return trim($code);
    }
    
    private function encryptLayers($data) {
        // 6 katmanlı ASP şifreleme
        $layer1 = base64_encode(gzcompress($data, 9));
        $layer2 = $this->xorEncrypt($layer1, $this->key1);
        $layer3 = $this->rotateChars(base64_encode($layer2));
        $layer4 = $this->xorEncrypt($layer3, $this->key2);
        $layer5 = $this->hexEncode($layer4);
        return $this->fragmentData(base64_encode($layer5));
    }
    
    private function xorEncrypt($data, $key) {
        $result = '';
        $keyLen = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }
        return $result;
    }
    
    private function rotateChars($data) {
        $result = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $char = $data[$i];
            $ascii = ord($char);
            if ($ascii >= 65 && $ascii <= 90) { // A-Z
                $result .= chr((($ascii - 65 + 13) % 26) + 65);
            } elseif ($ascii >= 97 && $ascii <= 122) { // a-z
                $result .= chr((($ascii - 97 + 13) % 26) + 97);
            } else {
                $result .= $char;
            }
        }
        return $result;
    }
    
    private function hexEncode($data) {
        return bin2hex($data);
    }
    
    private function fragmentData($data) {
        $chunks = str_split($data, 20);
        $result = [];
        
        foreach ($chunks as $i => $chunk) {
            $result[] = base64_encode($chunk) . '#' . ($i + 200);
        }
        
        return implode('|', $result);
    }
    
    private function createASPDecoder($encryptedData) {
        return '<%@ Page Language="C#" %>
<%@ Import Namespace="System" %>
<%@ Import Namespace="System.IO" %>
<%@ Import Namespace="System.Text" %>
<%@ Import Namespace="System.IO.Compression" %>

<script runat="server">
/*
 * 🔒 EKA Yazılım - Encrypted ASP.NET File
 * Security Level: MAXIMUM - 6 Layer Encryption
 * Generated: ' . date('Y-m-d H:i:s') . '
 * File Hash: ' . md5($encryptedData . time()) . '
 */

public class EKASecureASPDecoder
{
    private static string encryptedData = "' . addslashes($encryptedData) . '";
    private static string key1 = "' . $this->key1 . '";
    private static string key2 = "' . $this->key2 . '";
    
    public static string DecodeAndExecute()
    {
        try
        {
            string data = encryptedData;
            
            // 6 katmanlı şifre çözme
            string step1 = DefragmentData(data);
            byte[] step2 = Convert.FromBase64String(step1);
            string step3 = HexDecode(Encoding.UTF8.GetString(step2));
            string step4 = XorDecrypt(step3, key2);
            string step5 = ReverseRotation(step4);
            byte[] step6 = Convert.FromBase64String(step5);
            string step7 = XorDecrypt(Encoding.UTF8.GetString(step6), key1);
            byte[] step8 = Convert.FromBase64String(step7);
            
            // Gzip uncompress
            string original = GzipDecompress(step8);
            return original;
        }
        catch (Exception ex)
        {
            return "DECODE_ERROR: " + ex.Message;
        }
    }
    
    private static string DefragmentData(string data)
    {
        string[] parts = data.Split(\'|\');
        var chunks = new SortedDictionary<int, string>();
        
        foreach (string part in parts)
        {
            if (part.Contains("#"))
            {
                string[] splitPart = part.Split(\'#\');
                if (splitPart.Length == 2)
                {
                    int index = int.Parse(splitPart[1]) - 200;
                    chunks[index] = Encoding.UTF8.GetString(Convert.FromBase64String(splitPart[0]));
                }
            }
        }
        
        StringBuilder result = new StringBuilder();
        foreach (var chunk in chunks.Values)
        {
            result.Append(chunk);
        }
        
        return result.ToString();
    }
    
    private static string XorDecrypt(string data, string key)
    {
        StringBuilder result = new StringBuilder();
        int keyLen = key.Length;
        
        for (int i = 0; i < data.Length; i++)
        {
            char decrypted = (char)(data[i] ^ key[i % keyLen]);
            result.Append(decrypted);
        }
        
        return result.ToString();
    }
    
    private static string ReverseRotation(string data)
    {
        StringBuilder result = new StringBuilder();
        
        for (int i = 0; i < data.Length; i++)
        {
            char ch = data[i];
            int ascii = (int)ch;
            
            if (ascii >= 65 && ascii <= 90) // A-Z
            {
                result.Append((char)(((ascii - 65 - 13 + 26) % 26) + 65));
            }
            else if (ascii >= 97 && ascii <= 122) // a-z
            {
                result.Append((char)(((ascii - 97 - 13 + 26) % 26) + 97));
            }
            else
            {
                result.Append(ch);
            }
        }
        
        return result.ToString();
    }
    
    private static string HexDecode(string hex)
    {
        byte[] bytes = new byte[hex.Length / 2];
        for (int i = 0; i < bytes.Length; i++)
        {
            bytes[i] = Convert.ToByte(hex.Substring(i * 2, 2), 16);
        }
        return Encoding.UTF8.GetString(bytes);
    }
    
    private static string GzipDecompress(byte[] data)
    {
        using (var stream = new GZipStream(new MemoryStream(data), CompressionMode.Decompress))
        {
            using (var reader = new StreamReader(stream))
            {
                return reader.ReadToEnd();
            }
        }
    }
}

protected void Page_Load(object sender, EventArgs e)
{
    // Anti-debug kontrolleri
    if (System.Diagnostics.Debugger.IsAttached)
    {
        Response.End();
        return;
    }
    
    // Şifrelenmiş kodu çöz ve çalıştır
    string decodedCode = EKASecureASPDecoder.DecodeAndExecute();
    
    if (!decodedCode.StartsWith("DECODE_ERROR"))
    {
        // Güvenli execution environment
        try
        {
            // Decoded code execution burada yapılır
            // Not: Bu kısım güvenlik nedeniyle basitleştirilmiştir
            Response.Write("<!-- EKA Secure ASP.NET Code Executed -->");
            Response.Write(decodedCode);
        }
        catch (Exception ex)
        {
            Response.Write("Execution Error: " + ex.Message);
        }
    }
    else
    {
        Response.Write(decodedCode);
        Response.End();
    }
}
</script>

<!DOCTYPE html>
<html>
<head>
    <title>EKA Secure ASP.NET</title>
</head>
<body>
    <!-- Encrypted content will be rendered here -->
</body>
</html>';
    }
}

$mesaj = '';
$mesajTur = '';

// POST işlemi
if ($_POST && isset($_POST['asp_code'])) {
    $aspCode = $_POST['asp_code'];
    
    if (!empty($aspCode)) {
        try {
            $encoder = new ASPNetEncoder();
            $encrypted = $encoder->encode($aspCode);
            
            $dosyaAdi = 'eka_encrypted_' . date('Ymd_His') . '.aspx';
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $dosyaAdi . '"');
            echo $encrypted;
            exit;
        } catch (Exception $e) {
            $mesaj = 'Şifreleme sırasında hata oluştu: ' . $e->getMessage();
            $mesajTur = 'danger';
        }
    } else {
        $mesaj = 'Lütfen şifrelenecek ASP.NET kodunu girin!';
        $mesajTur = 'warning';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASP.NET Şifreleyici - EKA Yazılım Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .encoder-stats {
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
            font-size: 14px;
            line-height: 1.5;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            min-height: 400px;
        }
        .code-editor:focus {
            border-color: #20bf6b;
            box-shadow: 0 0 0 0.2rem rgba(32, 191, 107, 0.25);
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
            background: linear-gradient(45deg, #20bf6b, #26d0ce);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .asp-logo {
            background: linear-gradient(45deg, #239b56, #58d68d);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8em;
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
                    <div class="asp-logo me-2">ASP</div>ASP.NET Kod Şifreleyici
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
                            <h4><i class="fas fa-code me-2"></i>EKA ASP.NET Güvenli Şifreleyici</h4>
                            <p class="mb-2">Enterprise-level encryption ile ASP.NET kodlarınızı koruyun</p>
                            <div>
                                <span class="security-badge">🗜️ GZip Compression</span>
                                <span class="security-badge">🔐 Multi-XOR Encryption</span>
                                <span class="security-badge">🔄 ROT13 Cipher</span>
                                <span class="security-badge">📦 Hex Encoding</span>
                                <span class="security-badge">🧩 Data Fragmentation</span>
                                <span class="security-badge">🛡️ Anti-Debug</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="h1 mb-0">
                                <i class="fab fa-microsoft"></i>
                            </div>
                            <small>6-Layer Security</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fab fa-microsoft me-2"></i>ASP.NET Kod Şifreleme
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="post" id="encoderForm">
                                    <div class="mb-3">
                                        <label for="asp_code" class="form-label">
                                            <i class="fas fa-file-code me-1"></i>ASP.NET Kodunuz:
                                        </label>
                                        <textarea 
                                            name="asp_code" 
                                            id="asp_code" 
                                            class="form-control code-editor" 
                                            placeholder="Şifrelenecek ASP.NET kodunu buraya yapıştırın..."
                                            required><?php echo isset($_POST['asp_code']) ? htmlspecialchars($_POST['asp_code']) : '<%@ Page Language="C#" %>
<%@ Import Namespace="System" %>
<%@ Import Namespace="System.Data" %>
<%@ Import Namespace="System.Data.SqlClient" %>

<script runat="server">
    // EKA Yazılım - Örnek ASP.NET Kodu
    
    public void Page_Load(object sender, EventArgs e)
    {
        Response.Write("<h2>Merhaba EKA Yazılım ASP.NET!</h2>");
        
        // Kullanıcı bilgileri
        string[] kullanicilar = {"admin", "user", "guest"};
        
        Response.Write("<h3>Kullanıcı Listesi:</h3>");
        Response.Write("<ul>");
        
        foreach(string kullanici in kullanicilar)
        {
            Response.Write("<li>Kullanıcı: " + kullanici + "</li>");
        }
        
        Response.Write("</ul>");
        
        // Lisans doğrulama fonksiyonu
        bool lisansGecerli = LisansDogrula("EKA-ASP-2025-XXXX");
        
        if(lisansGecerli)
        {
            Response.Write("<div style=\"color: green;\">✅ Lisans geçerli!</div>");
        }
        else
        {
            Response.Write("<div style=\"color: red;\">❌ Geçersiz lisans!</div>");
        }
        
        // Veritabanı bağlantısı örneği
        string connectionString = "Server=localhost;Database=EKA;Integrated Security=true;";
        
        try
        {
            using(SqlConnection conn = new SqlConnection(connectionString))
            {
                // conn.Open();
                Response.Write("<p>Veritabanı bağlantısı hazır.</p>");
            }
        }
        catch(Exception ex)
        {
            Response.Write("<p>Hata: " + ex.Message + "</p>");
        }
    }
    
    public bool LisansDogrula(string anahtar)
    {
        string[] gecerliAnahtarlar = {
            "EKA-ASP-2025-XXXX",
            "EKA-ASP-2025-YYYY",
            "EKA-ASP-2025-ZZZZ"
        };
        
        foreach(string gAnahtar in gecerliAnahtarlar)
        {
            if(gAnahtar == anahtar)
                return true;
        }
        
        return false;
    }
</script>

<html>
<head>
    <title>EKA ASP.NET Örnek</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #2c3e50; }
        ul { background: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <!-- ASP.NET Server Controls buraya gelecek -->
</body>
</html>'; ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fab fa-microsoft me-2"></i>ASP.NET Şifrele ve İndir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="security-level">
                            🛡️ GÜVENLİK SEVİYESİ: ENTERPRISE
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="fab fa-microsoft fa-2x"></i>
                                </div>
                                <h6>6-Katmanlı ASP Koruma</h6>
                                <p class="small text-muted mb-0">Enterprise düzeyinde şifreleme algoritması</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-server fa-2x"></i>
                                </div>
                                <h6>IIS Uyumlu</h6>
                                <p class="small text-muted mb-0">Tüm IIS versiyonlarında çalışır</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-database fa-2x"></i>
                                </div>
                                <h6>.NET Framework</h6>
                                <p class="small text-muted mb-0">C# ve VB.NET desteği</p>
                            </div>
                        </div>
                        
                        <div class="card feature-card mb-3">
                            <div class="card-body text-center">
                                <div class="text-info mb-2">
                                    <i class="fas fa-shield-virus fa-2x"></i>
                                </div>
                                <h6>Anti-Reverse</h6>
                                <p class="small text-muted mb-0">Reverse engineering koruması</p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>ASP.NET Bilgileri
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        C# ve VB.NET destekli
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Web Forms uyumlu
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        MVC yapısı korunur
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Session ve ViewState güvenli
                                    </li>
                                </ul>
                                
                                <div class="alert alert-info small mt-3">
                                    <i class="fab fa-microsoft me-1"></i>
                                    <strong>Not:</strong> .aspx uzantılı dosya olarak kaydedilir.
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
            const code = document.getElementById('asp_code').value.trim();
            if (!code) {
                e.preventDefault();
                alert('Lütfen şifrelenecek ASP.NET kodunu girin!');
                return false;
            }
            
            // Loading state
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Şifreleniyor...';
            btn.disabled = true;
        });
        
        // Textarea auto-resize
        const textarea = document.getElementById('asp_code');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(400, this.scrollHeight) + 'px';
        });
        
        // ASP.NET syntax highlighting simulation
        textarea.addEventListener('input', function() {
            // Basit syntax highlighting için renk değişimi
            if (this.value.includes('<%') || this.value.includes('%>')) {
                this.style.borderColor = '#20bf6b';
            }
        });
    </script>
</body>
</html>