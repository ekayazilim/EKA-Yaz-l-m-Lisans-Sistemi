# EKA Lisans Sistemi - Kullanım Kılavuzu

## 📋 Genel Bakış

EKA Lisans Sistemi, PHP projelerinizi domain bazlı lisanslarla korumak için geliştirilmiş kapsamlı bir çözümdür.

## 🚀 Özellikler

### ✅ Tamamlanan Güncellemeler

1. **Domain Kısıtlaması**
   - Lisanslar artık belirli domainlere bağlanabilir
   - Domain kontrolü API seviyesinde yapılır
   - Yetkisiz domain kullanımında otomatik hata

2. **Süresiz Lisans Desteği**
   - Lisans ekleme formunda "Süresiz Lisans" seçeneği
   - Dinamik form alanları (JavaScript ile kontrol)
   - Süresiz lisanslar için otomatik 2099-12-31 bitiş tarihi

3. **Gelişmiş Lisans Yönetimi**
   - Lisanslar sayfasında domain kolonu eklendi
   - Domain güncelleme modalı
   - Detaylı işlem butonları (Durum, Domain, Sil)

4. **Güvenli Lisans Kontrol Sistemi**
   - Şifreli lisans kontrol kodu (`lisans-kontrol.php`)
   - Otomatik sistem bilgisi toplama
   - Detaylı hata loglama
   - Güvenli API iletişimi

## 📁 Dosya Yapısı

```
├── panel/
│   ├── lisans-ekle.php          # Güncellenmiş lisans ekleme formu
│   └── lisanslar.php            # Domain kolonu ve güncelleme özelliği
├── classes/
│   └── EkaLisansYoneticisi.php  # Domain kontrol metodları
├── api/
│   └── lisans-dogrula.php       # Domain kontrolü eklendi
├── lisans-kontrol.php           # 🆕 Projelere eklenecek koruma kodu
├── ornek-kullanim.php           # 🆕 Kullanım örneği
└── eksik_kolonlar_ekle.sql      # Veritabanı güncelleme scripti
```

## 🛠️ Kurulum Adımları

### 2. Lisans Oluşturma

1. Admin paneline giriş yapın
2. "Lisans Ekle" sayfasına gidin
3. Gerekli bilgileri doldurun:
   - **Domain Kısıtlaması**: `ornek.com` (www olmadan)
   - **Lisans Tipi**: Süreli veya Süresiz seçin
   - Diğer alanları doldurun
4. Lisansı kaydedin

### 3. Projeye Entegrasyon

#### Adım 1: Dosyaları Kopyalayın
```php
// lisans-kontrol.php dosyasını projenize kopyalayın
copy('lisans-kontrol.php', '/path/to/your/project/');
```

#### Adım 2: Projenizin Başına Ekleyin
```php
<?php
// index.php veya ana dosyanızın başına
require_once 'lisans-kontrol.php';

// Lisans anahtarınızı buraya yazın
$lisansAnahtari = 'EKA-XXXXXXXXXXXXXXXX-XXXXXXXXXXXXXXXX';
$apiUrl = 'http://yourdomain.com/api/lisans-dogrula.php';

// Lisans kontrolü
if (!ekaLisansKontrol($lisansAnahtari, $apiUrl)) {
    exit; // Geçersiz lisansta uygulamayı durdur
}

// Buradan sonra normal kodlarınız...
?>
```

## 🔧 API Kullanımı

### Lisans Doğrulama İsteği

```php
$postData = [
    'lisans_anahtari' => 'EKA-XXXXXXXXXXXXXXXX-XXXXXXXXXXXXXXXX',
    'domain' => 'ornek.com',
    'ip_adresi' => '192.168.1.1',
    'mac_adresi' => '00:11:22:33:44:55',
    'islem_tipi' => 'dogrulama'
];

$response = file_get_contents('http://localhost/api/lisans-dogrula.php', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'content' => http_build_query($postData)
    ]
]));

$result = json_decode($response, true);
```

### Başarılı Yanıt
```json
{
    "durum": true,
    "mesaj": "Lisans geçerli",
    "zaman": "2024-01-15 14:30:25",
    "ip_adresi": "192.168.1.1",
    "lisans_bilgileri": {
        "kullanici": "Ahmet Yılmaz",
        "email": "ahmet@ornek.com",
        "urun": "Web Uygulaması",
        "baslangic_tarihi": "2024-01-01",
        "bitis_tarihi": "2024-12-31",
        "kullanim_sayisi": 5,
        "max_kullanim": 100,
        "kalan_gun": 350
    }
}
```

### Hata Yanıtı
```json
{
    "durum": false,
    "mesaj": "Domain uyumsuzluğu",
    "hata_kodu": "DOMAIN_MISMATCH",
    "zaman": "2024-01-15 14:30:25",
    "ip_adresi": "192.168.1.1"
}
```

## 🔒 Güvenlik Özellikleri

### 1. Domain Kontrolü
- Lisans sadece belirtilen domainde çalışır
- www prefiksi otomatik olarak kaldırılır
- Alt domain desteği (isteğe bağlı)

### 2. Sistem Bilgisi Toplama
- IP adresi takibi
- MAC adresi kontrolü
- Server bilgileri
- PHP ve OS versiyonu
- Request detayları

### 3. Detaylı Loglama
```
[2024-01-15 14:30:25] LISANS HATASI
Hata: Domain uyumsuzluğu
Domain: sahte-site.com
Lisans Anahtarı: EKA-XXXXXXXXXXXXXXXX-XXXXXXXXXXXXXXXX
IP Adresi: 192.168.1.100
MAC Adresi: 00:11:22:33:44:55
User Agent: Mozilla/5.0...
Server: sahte-site.com
Script: /index.php
Document Root: /var/www/html
PHP Version: 8.1.0
OS: Linux
Request URI: /admin/panel
HTTP Referer: https://google.com
```

## 🎯 Hata Kodları

| Kod | Açıklama |
|-----|----------|
| `INVALID_LICENSE_KEY` | Geçersiz lisans anahtarı |
| `LICENSE_INACTIVE` | Lisans pasif durumda |
| `LICENSE_EXPIRED` | Lisans süresi dolmuş |
| `DOMAIN_MISMATCH` | Domain uyumsuzluğu |
| `IP_MISMATCH` | IP adresi uyumsuzluğu |
| `MAC_MISMATCH` | MAC adresi uyumsuzluğu |
| `MAX_USAGE_EXCEEDED` | Maksimum kullanım aşıldı |

## 📊 Yönetim Paneli

### Lisans Listesi
- Domain bilgisi görüntüleme
- Domain güncelleme modalı
- Durum güncelleme
- Detaylı işlem butonları

### Lisans Ekleme
- Domain kısıtlaması alanı (zorunlu)
- Süresiz lisans seçeneği
- Dinamik form kontrolleri
- Gelişmiş validasyon

## 🔧 Özelleştirme

### API URL Değiştirme
```php
// Farklı sunucu kullanımı
$apiUrl = 'https://lisans-sunucunuz.com/api/lisans-dogrula.php';
ekaLisansKontrol($lisansAnahtari, $apiUrl);
```

### Hata Sayfası Özelleştirme
`lisans-kontrol.php` dosyasındaki `lisansHatasi()` metodunu düzenleyin.

### Log Dosyası Konumu
```php
$this->logDosyasi = '/custom/path/lisans_log.txt';
```

## 🚨 Önemli Notlar

1. **Güvenlik**: Lisans anahtarlarını asla client-side kodlarda saklamayın
2. **Performance**: API çağrıları cache'lenebilir (önerilmez)
3. **Backup**: Log dosyalarını düzenli olarak temizleyin
4. **SSL**: Üretim ortamında mutlaka HTTPS kullanın

## 📞 Destek

Sorularınız için:
- 📧 Email: destek@eka-yazilim.com
- 📱 Telefon: +90 XXX XXX XX XX
- 🌐 Web: https://eka-yazilim.com

---

**© 2024 EKA Yazılım - Tüm hakları saklıdır.**