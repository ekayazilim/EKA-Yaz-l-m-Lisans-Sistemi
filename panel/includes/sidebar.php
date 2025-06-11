<?php
if (!isset($kullaniciYoneticisi)) {
    require_once '../config/veritabani.php';
    require_once '../classes/EkaKullaniciYoneticisi.php';
    $veritabani = new EkaVeritabani();
    $baglanti = $veritabani->baglantiGetir();
    $kullaniciYoneticisi = new EkaKullaniciYoneticisi($baglanti);
}
$adminMi = $kullaniciYoneticisi->adminMi($_SESSION['kullanici_id']);
?>

<div class="sidebar bg-dark">
    <div class="p-3">
        <div class="text-center mb-4">
            <h4 class="text-white">EKA Yazılım</h4>
            <small class="text-white-50">Lisans Sistemi</small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'lisanslar.php' ? 'active' : ''; ?>" href="lisanslar.php">
                <i class="fas fa-key"></i>
                Lisanslarım
            </a>
            
            <?php if ($adminMi): ?>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'lisans-ekle.php' ? 'active' : ''; ?>" href="lisans-ekle.php">
                    <i class="fas fa-plus-circle"></i>
                    Lisans Oluştur
                </a>
                
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'urunler.php' ? 'active' : ''; ?>" href="urunler.php">
                    <i class="fas fa-box"></i>
                    Ürünler
                </a>
                
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'urun-ekle.php' ? 'active' : ''; ?>" href="urun-ekle.php">
                    <i class="fas fa-plus"></i>
                    Ürün Ekle
                </a>
                
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kullanicilar.php' ? 'active' : ''; ?>" href="kullanicilar.php">
                    <i class="fas fa-users"></i>
                    Kullanıcılar
                </a>
                
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'raporlar.php' ? 'active' : ''; ?>" href="raporlar.php">
                    <i class="fas fa-chart-bar"></i>
                    Raporlar
                </a>
                
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'loglar.php' ? 'active' : ''; ?>" href="loglar.php">
                <i class="fas fa-file-alt"></i>
                Lisans Logları
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'php-sifrelemev2.php' ? 'active' : ''; ?>" href="php-sifrelemev2.php">
                <i class="fas fa-lock"></i>
                PHP Şifreleme v2
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'asp-sifreleme.php' ? 'active' : ''; ?>" href="asp-sifreleme.php">
                <i class="fas fa-shield-alt"></i>
                ASP Şifreleme
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'python-sifreleme.php' ? 'active' : ''; ?>" href="python-sifreleme.php">
                <i class="fab fa-python"></i>
                Python Şifreleme
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'php-decoder.php' ? 'active' : ''; ?>" href="php-decoder.php">
                <i class="fas fa-unlock"></i>
                Kod Çözücü
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'python-decoder.php' ? 'active' : ''; ?>" href="python-decoder.php">
                <i class="fab fa-python"></i>
                Python Decoder
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'aspnet-decoder.php' ? 'active' : ''; ?>" href="aspnet-decoder.php">
                <i class="fab fa-microsoft"></i>
                ASP.NET Decoder
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sql.php' ? 'active' : ''; ?>" href="sql.php">
                <i class="fas fa-database"></i>
                SQL Yönetimi
            </a>
            <?php endif; ?>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'lisans-dogrula.php' ? 'active' : ''; ?>" href="lisans-dogrula.php">
                <i class="fas fa-search"></i>
                Lisans Doğrula
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>" href="profil.php">
                <i class="fas fa-user"></i>
                Profil
            </a>
            
            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
            
            <a class="nav-link" href="cikis.php">
                <i class="fas fa-sign-out-alt"></i>
                Çıkış Yap
            </a>
        </nav>
    </div>
</div>
