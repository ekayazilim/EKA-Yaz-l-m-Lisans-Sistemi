<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/veritabani.php';
require_once '../classes/EkaLisansYoneticisi.php';

$lisansYoneticisi = new EkaLisansYoneticisi();

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'durum' => false,
        'mesaj' => 'Sadece POST istekleri kabul edilir',
        'hata_kodu' => 'METHOD_NOT_ALLOWED'
    ], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

if (empty($input['lisans_anahtari'])) {
    jsonResponse([
        'durum' => false,
        'mesaj' => 'Lisans anahtarı gereklidir',
        'hata_kodu' => 'MISSING_LICENSE_KEY'
    ], 400);
}

$lisansAnahtari = trim($input['lisans_anahtari']);
$ipAdresi = getClientIP();
$macAdresi = isset($input['mac_adresi']) ? trim($input['mac_adresi']) : null;
$domain = isset($input['domain']) ? trim($input['domain']) : null;
$islemTipi = isset($input['islem_tipi']) ? trim($input['islem_tipi']) : 'dogrulama';

switch ($islemTipi) {
    case 'dogrulama':
        $sonuc = $lisansYoneticisi->lisansDogrulaWithDomain($lisansAnahtari, $ipAdresi, $macAdresi, $domain);
        break;
        
    case 'aktivasyon':
        $sonuc = $lisansYoneticisi->lisansAktive($lisansAnahtari, $ipAdresi, $macAdresi);
        break;
        
    default:
        jsonResponse([
            'durum' => false,
            'mesaj' => 'Geçersiz işlem tipi',
            'hata_kodu' => 'INVALID_OPERATION'
        ], 400);
}

if ($sonuc['durum']) {
    $response = [
        'durum' => true,
        'mesaj' => $sonuc['mesaj'],
        'zaman' => date('Y-m-d H:i:s'),
        'ip_adresi' => $ipAdresi
    ];
    
    if (isset($sonuc['lisans'])) {
        $response['lisans_bilgileri'] = [
            'kullanici' => $sonuc['lisans']['ad'] . ' ' . $sonuc['lisans']['soyad'],
            'email' => $sonuc['lisans']['email'],
            'urun' => $sonuc['lisans']['urun_adi'],
            'baslangic_tarihi' => $sonuc['lisans']['baslangic_tarihi'],
            'bitis_tarihi' => $sonuc['lisans']['bitis_tarihi'],
            'kullanim_sayisi' => $sonuc['lisans']['kullanim_sayisi'],
            'max_kullanim' => $sonuc['lisans']['max_kullanim'],
            'kalan_gun' => max(0, (strtotime($sonuc['lisans']['bitis_tarihi']) - time()) / (60 * 60 * 24))
        ];
    }
    
    jsonResponse($response);
} else {
    $errorCodes = [
        'Geçersiz lisans anahtarı' => 'INVALID_LICENSE_KEY',
        'Lisans pasif durumda' => 'LICENSE_INACTIVE',
        'Lisans henüz geçerli değil' => 'LICENSE_NOT_STARTED',
        'Lisans süresi dolmuş' => 'LICENSE_EXPIRED',
        'Maksimum kullanım sayısı aşıldı' => 'MAX_USAGE_EXCEEDED',
        'IP adresi uyumsuzluğu' => 'IP_MISMATCH',
        'MAC adresi uyumsuzluğu' => 'MAC_MISMATCH',
        'Domain uyumsuzluğu' => 'DOMAIN_MISMATCH',
        'Sistem hatası' => 'SYSTEM_ERROR'
    ];
    
    $hataKodu = $errorCodes[$sonuc['mesaj']] ?? 'UNKNOWN_ERROR';
    
    jsonResponse([
        'durum' => false,
        'mesaj' => $sonuc['mesaj'],
        'hata_kodu' => $hataKodu,
        'zaman' => date('Y-m-d H:i:s'),
        'ip_adresi' => $ipAdresi
    ], 400);
}
?>
