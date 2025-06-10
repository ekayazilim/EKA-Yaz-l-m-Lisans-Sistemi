# EKA Yazılım Lisans Sistemi

Gelişmiş PHP tabanlı lisans yönetim sistemi. Bu sistem, yazılım lisanslarını oluşturma, doğrulama ve yönetme işlemlerini kolaylaştırmak için tasarlanmıştır.

## 🎯 Ana Özellikler

### 🔐 Gelişmiş Lisans Yönetimi
- **Benzersiz Lisans Anahtarı Oluşturma**: EKA-XXXXXXXX-XXXXXXXX formatında
- **Domain Bazlı Kısıtlama**: Lisansları belirli domainlere bağlama
- **Süresiz Lisans Desteği**: Kalıcı lisanslar için özel seçenek
- **IP ve MAC Adresi Bağlama**: Donanım bazlı güvenlik
- **Kullanım Sayısı Takibi**: Maksimum kullanım kontrolü
- **Otomatik Süre Kontrolü**: Tarih bazlı aktivasyon/deaktivasyon
- **Lisans Durumu Yönetimi**: Aktif, Pasif, Süresi Dolmuş durumları

### 👥 Kapsamlı Kullanıcı Yönetimi
- **Güvenli Kayıt ve Giriş Sistemi**: Şifreli oturum yönetimi
- **Rol Bazlı Yetkilendirme**: Admin ve normal kullanıcı rolleri
- **Detaylı Profil Yönetimi**: Kişisel bilgi güncelleme
- **Şifre Güvenliği**: Hash'lenmiş şifre saklama
- **Kullanıcı İstatistikleri**: Toplam, aktif, admin sayıları

### 📦 Ürün Yönetimi Sistemi
- **Ürün Ekleme ve Düzenleme**: Tam CRUD işlemleri
- **Versiyon Takibi**: Ürün sürüm kontrolü
- **Fiyat Yönetimi**: Dinamik fiyatlandırma
- **Ürün Durumu Kontrolü**: Aktif/Pasif durum yönetimi
- **Popülerlik Analizi**: Lisans sayısına göre sıralama

### 📊 Detaylı Raporlama ve İstatistikler
- **Dinamik Dashboard**: Gerçek zamanlı istatistikler
- **Lisans Kullanım Raporları**: Detaylı analiz
- **Kullanıcı İstatistikleri**: Aylık kayıt takibi
- **Ürün Popülerlik Analizi**: En çok kullanılan ürünler
- **Lisans Logları**: Tüm işlem geçmişi

### 🔌 Güçlü API Desteği
- **RESTful API**: Standart HTTP metodları
- **JSON Formatı**: Evrensel veri alışverişi
- **CORS Desteği**: Cross-origin istekler
- **Güvenli Endpoint'ler**: Şifreli iletişim
- **Detaylı Hata Kodları**: Kapsamlı hata yönetimi

### 🛡️ Güvenlik Özellikleri
- **Şifreleme Araçları**: PHP, ASP, Python için
- **SQL Injection Koruması**: PDO prepared statements
- **XSS Koruması**: HTML filtreleme
- **CSRF Koruması**: Token bazlı doğrulama
- **IP Kısıtlama**: Coğrafi güvenlik
- **MAC Adresi Kontrolü**: Donanım kimlik doğrulama

## Sistem Gereksinimleri

- **PHP:** 7.4 veya üzeri
- **MySQL:** 5.7 veya üzeri
- **Web Sunucu:** Apache/Nginx
- **PHP Eklentileri:**
  - PDO
  - PDO_MySQL
  - JSON
  - OpenSSL
  - mbstring

## Kurulum

### 1. Dosyaları Kopyalama
```bash
# XAMPP kullanıyorsanız
cp -r * C:/xampp/htdocs/

# Linux/Unix sistemlerde
cp -r * /var/www/html/
```

### 2. Veritabanı Yapılandırması

`config/veritabani.php` dosyasında veritabanı ayarlarını düzenleyin:

```php
private static $sunucu = 'localhost';
private static $kullanici = 'root';
private static $sifre = '';
private static $veritabani = 'eka_lisans_sistemi';
```

### 3. İlk Çalıştırma

1. Web tarayıcınızda projeyi açın
2. Sistem otomatik olarak veritabanını oluşturacak
3. Varsayılan admin hesabı oluşturulacak:
   - **E-posta:** admin@ekayazilim.com
   - **Şifre:** admin123

### 4. Güvenlik Ayarları

- Admin şifresini hemen değiştirin
- Veritabanı şifresi belirleyin
- SSL sertifikası kullanın (üretim ortamında)

## 🖥️ Admin Paneli - Detaylı Menü Yapısı

### 📊 Dashboard (Ana Sayfa)
- **Dinamik İstatistik Kartları**:
  - Toplam Kullanıcı Sayısı
  - Toplam Ürün Sayısı  
  - Toplam Lisans Sayısı
  - Aktif Lisans Sayısı
- **Son Lisanslar Tablosu**: En son oluşturulan 5 lisans
- **Hızlı İşlemler Paneli**: Sık kullanılan işlemlere hızlı erişim
- **Popüler Ürünler**: En çok lisanslanan ürünler

### 🔑 Lisans Yönetimi
#### Lisanslarım
- Tüm lisansları görüntüleme ve filtreleme
- Lisans durumu güncelleme (Aktif/Pasif)
- Domain güncelleme modalı
- Lisans silme işlemi
- Detaylı lisans bilgileri

#### Lisans Oluştur (Admin)
- **Gelişmiş Form Özellikleri**:
  - Kullanıcı seçimi (dropdown)
  - Ürün seçimi (dropdown)
  - Domain kısıtlaması ayarı
  - Süresiz lisans seçeneği
  - Maksimum kullanım sayısı
  - Başlangıç ve bitiş tarihi
  - IP adresi kısıtlaması

### 📦 Ürün Yönetimi (Admin)
#### Ürünler
- Ürün listesi ve arama
- Ürün düzenleme ve silme
- Ürün durumu kontrolü
- Fiyat güncelleme

#### Ürün Ekle
- Ürün adı ve açıklama
- Versiyon bilgisi
- Fiyat belirleme
- Durum ayarı (Aktif/Pasif)

### 👥 Kullanıcı Yönetimi (Admin)
- Kullanıcı listesi ve arama
- Yeni kullanıcı ekleme
- Kullanıcı bilgilerini düzenleme
- Rol atama (Admin/Kullanıcı)
- Kullanıcı durumu kontrolü

### 📈 Raporlar ve Analiz (Admin)
- Lisans kullanım raporları
- Kullanıcı aktivite analizi
- Ürün popülerlik istatistikleri
- Gelir analizi
- Zaman bazlı raporlar

### 📋 Lisans Logları (Admin)
- Tüm lisans işlemlerinin detaylı geçmişi
- Doğrulama istekleri
- Hata logları
- IP ve MAC adresi takibi
- Zaman damgası bilgileri

### 🛡️ Güvenlik Araçları (Admin)
#### PHP Şifreleme v2
- PHP kodlarını şifreleme
- Lisans kontrolü ekleme
- Kod koruma araçları

#### ASP Şifreleme
- ASP.NET projeler için şifreleme
- Lisans entegrasyonu
- Kod güvenliği

#### Python Şifreleme
- Python scriptleri için koruma
- Lisans doğrulama ekleme
- Kaynak kod güvenliği

### 🗄️ SQL Yönetimi (Admin)
- Veritabanı yönetim araçları
- SQL sorgu çalıştırma
- Tablo yapısı görüntüleme
- Veri yedekleme araçları

### 🔍 Lisans Doğrula
- Manuel lisans doğrulama
- Lisans bilgilerini sorgulama
- Durum kontrolü
- Detaylı lisans raporu

### 👤 Profil Yönetimi
- Kişisel bilgileri güncelleme
- Şifre değiştirme
- İletişim bilgileri
- Hesap ayarları

### 🚪 Güvenli Çıkış
- Oturum sonlandırma
- Güvenlik logları
- Otomatik yönlendirme

## 🔌 API Kullanımı ve Entegrasyon

### API Endpoint'leri

#### 1. Lisans Doğrulama API
**Endpoint:** `POST /api/lisans-dogrula.php`

**Özellikler:**
- CORS desteği (Cross-Origin Resource Sharing)
- JSON formatında veri alışverişi
- Detaylı hata kodları
- IP adresi otomatik algılama
- Domain bazlı doğrulama

#### İstek Örneği

```bash
curl -X POST http://yourdomain.com/api/lisans-dogrula.php \
  -H "Content-Type: application/json" \
  -d '{
    "lisans_anahtari": "EKA-XXXXXXXX-XXXXXXXX",
    "mac_adresi": "00:11:22:33:44:55",
    "islem_tipi": "dogrulama"
  }'
```

#### Başarılı Yanıt
```json
{
  "durum": true,
  "mesaj": "Lisans geçerli",
  "zaman": "2024-01-15 14:30:00",
  "ip_adresi": "192.168.1.100",
  "lisans_bilgileri": {
    "kullanici": "Ahmet Yılmaz",
    "email": "ahmet@example.com",
    "urun": "EKA Yazılım v1.0",
    "baslangic_tarihi": "2024-01-01",
    "bitis_tarihi": "2024-12-31",
    "kullanim_sayisi": 5,
    "max_kullanim": 10,
    "kalan_gun": 350
  }
}
```

#### Hata Yanıtı
```json
{
  "durum": false,
  "mesaj": "Lisans süresi dolmuş",
  "hata_kodu": "LICENSE_EXPIRED",
  "zaman": "2024-01-15 14:30:00",
  "ip_adresi": "192.168.1.100"
}
```

## 📁 Proje Dosya Yapısı

```
eka-lisans-sistemi/
├── 📁 api/
│   └── lisans-dogrula.php          # RESTful API endpoint
├── 📁 assets/
│   ├── 📁 css/
│   │   └── style.css               # Ana stil dosyası
│   └── 📁 js/
│       └── main.js                 # JavaScript fonksiyonları
├── 📁 classes/
│   ├── EkaKullaniciYoneticisi.php  # Kullanıcı işlemleri
│   ├── EkaLisansYoneticisi.php     # Lisans işlemleri
│   └── EkaUrunYoneticisi.php       # Ürün işlemleri
├── 📁 config/
│   └── veritabani.php              # Veritabanı bağlantı ayarları
├── 📁 panel/
│   ├── 📁 includes/
│   │   ├── header.php              # Üst menü
│   │   ├── sidebar.php             # Yan menü
│   │   └── footer.php              # Alt bilgi
│   ├── dashboard.php               # Ana dashboard
│   ├── lisanslar.php               # Lisans listesi
│   ├── lisans-ekle.php             # Yeni lisans oluşturma
│   ├── lisans-dogrula.php          # Manuel lisans doğrulama
│   ├── urunler.php                 # Ürün listesi
│   ├── urun-ekle.php               # Yeni ürün ekleme
│   ├── kullanicilar.php            # Kullanıcı listesi
│   ├── kullanici-ekle.php          # Yeni kullanıcı ekleme
│   ├── raporlar.php                # Raporlar ve analizler
│   ├── loglar.php                  # Lisans logları
│   ├── profil.php                  # Kullanıcı profili
│   ├── sifre-degistir.php          # Şifre değiştirme
│   ├── php-sifrelemev2.php         # PHP şifreleme aracı
│   ├── asp-sifreleme.php           # ASP şifreleme aracı
│   ├── python-sifreleme.php        # Python şifreleme aracı
│   ├── sql.php                     # SQL yönetim aracı
│   └── cikis.php                   # Güvenli çıkış
├── index.php                       # Ana giriş sayfası
├── kayit.php                       # Kullanıcı kayıt sayfası
├── lisans-kontrol.php              # Projelere entegre edilecek dosya
├── ornek-kullanim.php              # Kullanım örneği
├── README.md                       # Bu dosya
└── LISANS-SISTEMI-KULLANIM.md      # Detaylı kullanım kılavuzu
```

## 🗄️ Veritabanı Yapısı

### Ana Tablolar

#### 1. `kullanicilar` - Kullanıcı Bilgileri
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- ad (VARCHAR(100))
- soyad (VARCHAR(100))
- email (VARCHAR(255), UNIQUE)
- telefon (VARCHAR(20))
- sirket (VARCHAR(255))
- sifre (VARCHAR(255)) # Hash'lenmiş
- rol (ENUM: 'admin', 'kullanici')
- durum (ENUM: 'aktif', 'pasif')
- kayit_tarihi (DATETIME)
- guncelleme_tarihi (DATETIME)
```

#### 2. `urunler` - Ürün Bilgileri
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- ad (VARCHAR(255))
- aciklama (TEXT)
- versiyon (VARCHAR(50))
- fiyat (DECIMAL(10,2))
- durum (ENUM: 'aktif', 'pasif')
- olusturma_tarihi (DATETIME)
- guncelleme_tarihi (DATETIME)
```

#### 3. `lisanslar` - Lisans Kayıtları
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- lisans_anahtari (VARCHAR(255), UNIQUE)
- kullanici_id (INT, FOREIGN KEY)
- urun_id (INT, FOREIGN KEY)
- domain (VARCHAR(255)) # Domain kısıtlaması
- ip_adresi (VARCHAR(45))
- mac_adresi (VARCHAR(17))
- baslangic_tarihi (DATE)
- bitis_tarihi (DATE)
- max_kullanim (INT)
- kullanim_sayisi (INT, DEFAULT 0)
- durum (ENUM: 'aktif', 'pasif', 'suresi_dolmus')
- olusturma_tarihi (DATETIME)
- guncelleme_tarihi (DATETIME)
```

#### 4. `lisans_loglar` - İşlem Geçmişi
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- lisans_id (INT, FOREIGN KEY)
- islem_tipi (VARCHAR(50))
- ip_adresi (VARCHAR(45))
- mac_adresi (VARCHAR(17))
- domain (VARCHAR(255))
- durum (BOOLEAN)
- mesaj (TEXT)
- islem_tarihi (DATETIME)
```

#### 5. `ayarlar` - Sistem Ayarları
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- anahtar (VARCHAR(100), UNIQUE)
- deger (TEXT)
- aciklama (TEXT)
- guncelleme_tarihi (DATETIME)
```

### Tablo İlişkileri

- **lisanslar.kullanici_id** → **kullanicilar.id** (N:1)
- **lisanslar.urun_id** → **urunler.id** (N:1)
- **lisans_loglar.lisans_id** → **lisanslar.id** (N:1)

### İndeksler ve Performans

```sql
-- Performans için önemli indeksler
CREATE INDEX idx_lisans_anahtari ON lisanslar(lisans_anahtari);
CREATE INDEX idx_kullanici_email ON kullanicilar(email);
CREATE INDEX idx_lisans_durum ON lisanslar(durum);
CREATE INDEX idx_log_tarih ON lisans_loglar(islem_tarihi);
```

## Güvenlik Özellikleri

- **Şifre Hashleme:** PHP password_hash() fonksiyonu
- **SQL Injection Koruması:** PDO prepared statements
- **XSS Koruması:** htmlspecialchars() filtreleme
- **CSRF Koruması:** Session tabanlı token doğrulama
- **IP Kısıtlama:** Lisans bazında IP adresi bağlama
- **MAC Adresi Kontrolü:** Donanım bazlı lisans bağlama

## Hata Kodları

| Kod | Açıklama |
|-----|----------|
| `INVALID_LICENSE_KEY` | Geçersiz lisans anahtarı |
| `LICENSE_INACTIVE` | Lisans pasif durumda |
| `LICENSE_NOT_STARTED` | Lisans henüz başlamamış |
| `LICENSE_EXPIRED` | Lisans süresi dolmuş |
| `MAX_USAGE_EXCEEDED` | Maksimum kullanım aşıldı |
| `IP_MISMATCH` | IP adresi uyumsuzluğu |
| `MAC_MISMATCH` | MAC adresi uyumsuzluğu |
| `SYSTEM_ERROR` | Sistem hatası |

## Katkıda Bulunma

1. Projeyi fork edin
2. Feature branch oluşturun (`git checkout -b feature/yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik eklendi'`)
4. Branch'inizi push edin (`git push origin feature/yeni-ozellik`)
5. Pull Request oluşturun

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakın.

## Destek

Sorularınız için:
- **E-posta:** destek@ekayazilim.com
- **Website:** https://www.ekayazilim.com
- **GitHub Issues:** Proje sayfasında issue açabilirsiniz

## 📈 Sürüm Geçmişi

### v2.0.0 (2024-12-15) - Güncel Sürüm
- **🆕 Domain Bazlı Lisanslama**: Lisansları belirli domainlere bağlama
- **⏰ Süresiz Lisans Desteği**: Kalıcı lisanslar için özel seçenek
- **🔧 Gelişmiş Admin Paneli**: Yenilenmiş arayüz ve özellikler
- **🛡️ Çoklu Şifreleme Araçları**: PHP, ASP, Python desteği
- **📊 Dinamik Dashboard**: Gerçek zamanlı istatistikler
- **🗄️ SQL Yönetim Aracı**: Veritabanı yönetimi
- **📋 Detaylı Loglama**: Kapsamlı işlem geçmişi
- **🔍 Gelişmiş Arama**: Filtreleme ve sıralama
- **📱 Responsive Tasarım**: Mobil uyumlu arayüz

### v1.5.0 (2024-08-20)
- Lisans logları sistemi
- Raporlama modülü
- Kullanıcı yönetimi iyileştirmeleri
- API güvenlik güncellemeleri

### v1.0.0 (2024-01-15)
- İlk stabil sürüm
- Temel lisans yönetimi özellikleri
- Admin paneli
- RESTful API desteği
- Güvenlik özellikleri

## 🚀 Gelecek Güncellemeler

### v2.1.0 (Planlanan)
- **📧 E-posta Bildirimleri**: Lisans süresi dolmadan uyarı
- **📊 Gelişmiş Raporlar**: Grafik ve analiz araçları
- **🔄 Otomatik Yedekleme**: Veritabanı yedekleme sistemi
- **🌐 Çoklu Dil Desteği**: İngilizce dil seçeneği
- **📱 Mobile API**: Mobil uygulama desteği

## 🤝 Katkıda Bulunma

1. **Projeyi Fork Edin**
   ```bash
   git clone https://github.com/ekayazilim/EKA-Yazilim-Lisans-Sistemi.git
   ```

2. **Feature Branch Oluşturun**
   ```bash
   git checkout -b feature/yeni-ozellik
   ```

3. **Değişikliklerinizi Commit Edin**
   ```bash
   git commit -am 'Yeni özellik: Açıklama'
   ```

4. **Branch'inizi Push Edin**
   ```bash
   git push origin feature/yeni-ozellik
   ```

5. **Pull Request Oluşturun**
   - Detaylı açıklama ekleyin
   - Test sonuçlarını paylaşın
   - Ekran görüntüleri ekleyin (UI değişiklikleri için)

## 📞 Destek ve İletişim

### 🆘 Teknik Destek
- **E-posta**: destek@ekayazilim.com
- **Telefon**: +90 (XXX) XXX XX XX
- **Çalışma Saatleri**: Pazartesi-Cuma 09:00-18:00

### 🌐 Online Kaynaklar
- **Website**: https://www.ekayazilim.com
- **Dokümantasyon**: https://docs.ekayazilim.com
- **GitHub Issues**: Proje sayfasında issue açabilirsiniz
- **Video Eğitimler**: https://youtube.com/ekayazilim

### 💬 Topluluk
- **Discord**: EKA Yazılım Topluluğu
- **Telegram**: @ekayazilim
- **Forum**: https://forum.ekayazilim.com

## 📄 Lisans

Bu proje **MIT Lisansı** altında lisanslanmıştır. 

```
MIT License

Copyright (c) 2024 EKA Yazılım

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

Detaylar için `LICENSE` dosyasına bakın.

---

<div align="center">

**🏢 EKA Yazılım**  
*Profesyonel Yazılım Çözümleri*

**🔗 Bağlantılar**  
[Website](https://www.ekayazilim.com) • [GitHub](https://github.com/ekayazilim) • [LinkedIn](https://linkedin.com/company/ekayazilim)

**📧 İletişim**  
info@ekayazilim.com

© 2024 EKA Yazılım. Tüm hakları saklıdır.

</div>
