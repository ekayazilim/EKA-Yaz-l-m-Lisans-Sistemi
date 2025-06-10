<?php

require_once 'lisans-kontrol.php';

$lisansAnahtari = 'EKA-F7C436771596690D-x';
$apiUrl = 'http://localhost/api/lisans-dogrula.php';

if (!ekaLisansKontrol($lisansAnahtari, $apiUrl)) {
    exit;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisanslı Uygulama</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-size: 18px; margin-bottom: 20px; }
        .info { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; }
        h1 { color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 Lisanslı Uygulama</h1>
        <div class="success">✅ Lisans doğrulaması başarılı!</div>
        
        <div class="info">
            <h3>📋 Uygulama Bilgileri</h3>
            <p><strong>Domain:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'localhost'; ?></p>
            <p><strong>IP Adresi:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; ?></p>
            <p><strong>Tarih:</strong> <?php echo date('d.m.Y H:i:s'); ?></p>
            <p><strong>PHP Sürümü:</strong> <?php echo PHP_VERSION; ?></p>
        </div>
        
        <div class="info">
            <h3>🔐 Lisans Durumu</h3>
            <p>Bu uygulama geçerli bir lisans ile korunmaktadır.</p>
            <p>Tüm işlemler güvenli bir şekilde loglanmaktadır.</p>
        </div>
        
        <div class="info">
            <h3>🚀 Uygulama İçeriği</h3>
            <p>Buraya lisanslı uygulamanızın içeriği gelecek...</p>
            <p>Lisans kontrolü başarılı olduğu için uygulama normal şekilde çalışmaktadır.</p>
        </div>
    </div>
</body>
</html>