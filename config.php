<?php
// Veritabanı Yapılandırması (FastPanel varsayılanlarına göre)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'avusturtest_usr');
define('DB_PASS', getenv('DB_PASS') ?: '123123Aa');
define('DB_NAME', getenv('DB_NAME') ?: 'avusturtest');

// Site Yapılandırması
define('SITE_NAME', getenv('SITE_NAME') ?: 'Avustur');
define('SITE_URL', getenv('SITE_URL') ?: (
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') .
    ($_SERVER['HTTP_HOST'] ?? 'localhost')
));
define('ADMIN_URL', rtrim(SITE_URL, '/') . '/admin');

// Oturum ayarları
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // HTTPS kullanıyorsanız 1 yapın

// Hata raporlama (production'da kapatın)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Istanbul');

// Veritabanı bağlantısı
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Yardımcı fonksiyonlar
function sanitize($data) {
    if ($data === null) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['kullanici_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('admin/login.php');
    }
}

// Bugünkü tur programını getir
function getBugunTur($db) {
    $bugun = date('Y-m-d');
    $stmt = $db->prepare("
        SELECT s.sehir_slug, s.sehir_adi, t.aciklama
        FROM takvim t
        INNER JOIN sehirler s ON t.sehir_id = s.id
        WHERE t.tarih = ? AND t.aktif = 1 AND s.aktif = 1
        LIMIT 1
    ");
    $stmt->execute([$bugun]);
    return $stmt->fetch();
}

// Kullanıcı bilgilerini getir
function getKullanici($db, $kullanici_id) {
    $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id = ? AND aktif = 1");
    $stmt->execute([$kullanici_id]);
    return $stmt->fetch();
}

// Şehir bilgilerini getir
function getSehir($db, $sehir_slug) {
    $stmt = $db->prepare("SELECT * FROM sehirler WHERE sehir_slug = ? AND aktif = 1");
    $stmt->execute([$sehir_slug]);
    return $stmt->fetch();
}

// Şehir aktivitelerini getir
function getAktiviteler($db, $sehir_id) {
    $stmt = $db->prepare("
        SELECT * FROM aktiviteler 
        WHERE sehir_id = ? AND aktif = 1 
        ORDER BY sira ASC, baslik ASC
    ");
    $stmt->execute([$sehir_id]);
    return $stmt->fetchAll();
}

// Şehir restoranlarını getir
function getRestoranlar($db, $sehir_id) {
    $stmt = $db->prepare("
        SELECT * FROM restoranlar 
        WHERE sehir_id = ? AND aktif = 1 
        ORDER BY sira ASC, baslik ASC
    ");
    $stmt->execute([$sehir_id]);
    return $stmt->fetchAll();
}

// Türkçe gün adını getir (PHP 8.2+ için strftime() yerine)
function getTurkceGunAdi($tarih) {
    $gunler = [
        'Monday' => 'Pazartesi',
        'Tuesday' => 'Salı',
        'Wednesday' => 'Çarşamba',
        'Thursday' => 'Perşembe',
        'Friday' => 'Cuma',
        'Saturday' => 'Cumartesi',
        'Sunday' => 'Pazar'
    ];
    
    $gun = date('l', strtotime($tarih));
    return $gunler[$gun] ?? $gun;
}

// Restoran menü kategorilerini getir
function getMenuKategoriler($db, $restoran_id) {
    $stmt = $db->prepare("
        SELECT * FROM menu_kategoriler 
        WHERE restoran_id = ? AND aktif = 1 
        ORDER BY sira ASC, kategori_adi ASC
    ");
    $stmt->execute([$restoran_id]);
    return $stmt->fetchAll();
}

// Kategori ürünlerini getir (kullanıcı için - fiyatsız)
function getMenuUrunler($db, $kategori_id) {
    $stmt = $db->prepare("
        SELECT id, urun_adi, aciklama, gorsel, porsiyon_bilgisi, kalori, sira 
        FROM menu_urunler 
        WHERE kategori_id = ? AND aktif = 1 
        ORDER BY sira ASC, urun_adi ASC
    ");
    $stmt->execute([$kategori_id]);
    return $stmt->fetchAll();
}

/**
 * Görsel yükleme ve otomatik ölçeklendirme fonksiyonu
 * Maksimum boyut: 800x600px
 * Format: JPEG (kalite: 85)
 */
function uploadVeOlceklendir($file, $hedef_klasor = 'uploads/urunler') {
    // Mutlak yol oluştur (config.php'nin bulunduğu dizinden)
    $base_path = dirname(__FILE__); // config.php'nin bulunduğu dizin
    $tam_yol = $base_path . '/' . $hedef_klasor;
    
    // Klasör yoksa oluştur
    if (!is_dir($tam_yol)) {
        mkdir($tam_yol, 0755, true);
    }
    
    // Dosya kontrolleri
    $izinli_tipler = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_boyut = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $izinli_tipler)) {
        return ['success' => false, 'error' => 'Sadece JPG, PNG, GIF ve WEBP formatları kabul edilir.'];
    }
    
    if ($file['size'] > $max_boyut) {
        return ['success' => false, 'error' => 'Dosya boyutu maksimum 5MB olmalıdır.'];
    }
    
    // Benzersiz dosya adı oluştur
    $uzanti = pathinfo($file['name'], PATHINFO_EXTENSION);
    $yeni_ad = uniqid('urun_', true) . '.jpg'; // Her zaman JPEG olarak kaydet
    $hedef_yol = $tam_yol . '/' . $yeni_ad;
    
    // Görsel bilgilerini al
    $gorsel_bilgi = getimagesize($file['tmp_name']);
    if ($gorsel_bilgi === false) {
        return ['success' => false, 'error' => 'Geçersiz görsel dosyası.'];
    }
    
    // Orijinal görseli yükle
    $kaynak_gorsel = null;
    switch ($gorsel_bilgi['mime']) {
        case 'image/jpeg':
        case 'image/jpg':
            $kaynak_gorsel = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $kaynak_gorsel = imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/gif':
            $kaynak_gorsel = imagecreatefromgif($file['tmp_name']);
            break;
        case 'image/webp':
            $kaynak_gorsel = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            return ['success' => false, 'error' => 'Desteklenmeyen görsel formatı.'];
    }
    
    if (!$kaynak_gorsel) {
        return ['success' => false, 'error' => 'Görsel yüklenemedi.'];
    }
    
    // Orijinal boyutlar
    $orijinal_genislik = imagesx($kaynak_gorsel);
    $orijinal_yukseklik = imagesy($kaynak_gorsel);
    
    // Maksimum boyutlar
    $max_genislik = 800;
    $max_yukseklik = 600;
    
    // Ölçeklendirme oranını hesapla
    $oran_genislik = $max_genislik / $orijinal_genislik;
    $oran_yukseklik = $max_yukseklik / $orijinal_yukseklik;
    $oran = min($oran_genislik, $oran_yukseklik, 1); // 1'den büyük yapma (büyütme)
    
    // Yeni boyutlar
    $yeni_genislik = round($orijinal_genislik * $oran);
    $yeni_yukseklik = round($orijinal_yukseklik * $oran);
    
    // Yeni görsel oluştur
    $yeni_gorsel = imagecreatetruecolor($yeni_genislik, $yeni_yukseklik);
    
    // Şeffaflığı koru (PNG için)
    imagealphablending($yeni_gorsel, false);
    imagesavealpha($yeni_gorsel, true);
    
    // Görseli yeniden boyutlandır
    imagecopyresampled(
        $yeni_gorsel, $kaynak_gorsel,
        0, 0, 0, 0,
        $yeni_genislik, $yeni_yukseklik,
        $orijinal_genislik, $orijinal_yukseklik
    );
    
    // JPEG olarak kaydet (kalite: 85)
    $kayit_basarili = imagejpeg($yeni_gorsel, $hedef_yol, 85);
    
    // Belleği temizle
    imagedestroy($kaynak_gorsel);
    imagedestroy($yeni_gorsel);
    
    if ($kayit_basarili) {
        return [
            'success' => true,
            'filename' => $yeni_ad,
            'path' => $hedef_yol,
            'url' => '/' . str_replace('\\', '/', $hedef_yol)
        ];
    } else {
        return ['success' => false, 'error' => 'Görsel kaydedilemedi.'];
    }
}

/**
 * Eski görseli sil
 */
function gorselSil($dosya_adi, $klasor = 'uploads/urunler') {
    if (empty($dosya_adi)) return false;
    
    // Mutlak yol oluştur
    $base_path = dirname(__FILE__);
    $dosya_yolu = $base_path . '/' . $klasor . '/' . $dosya_adi;
    
    if (file_exists($dosya_yolu)) {
        return unlink($dosya_yolu);
    }
    return false;
}
?>

