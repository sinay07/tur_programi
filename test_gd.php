<?php
// GD Extension Test
echo "<h1>GD Extension Test</h1>";

if (extension_loaded('gd')) {
    echo "<p style='color: green; font-size: 20px;'>✅ GD Extension AKTIF!</p>";
    
    $gd_info = gd_info();
    echo "<h2>GD Bilgileri:</h2>";
    echo "<pre>";
    print_r($gd_info);
    echo "</pre>";
    
    // Test görsel oluştur
    $test_image = imagecreatetruecolor(200, 100);
    if ($test_image) {
        echo "<p style='color: green;'>✅ imagecreatetruecolor() çalışıyor!</p>";
        
        $bg_color = imagecolorallocate($test_image, 102, 126, 234);
        imagefill($test_image, 0, 0, $bg_color);
        
        $white = imagecolorallocate($test_image, 255, 255, 255);
        imagestring($test_image, 5, 50, 40, 'TEST OK!', $white);
        
        // Görseli kaydet
        $test_path = 'uploads/urunler/test_' . time() . '.jpg';
        if (imagejpeg($test_image, $test_path, 85)) {
            echo "<p style='color: green;'>✅ Görsel başarıyla kaydedildi: {$test_path}</p>";
            echo "<img src='{$test_path}' alt='Test Görsel'>";
        } else {
            echo "<p style='color: red;'>❌ Görsel kaydedilemedi!</p>";
        }
        
        imagedestroy($test_image);
    } else {
        echo "<p style='color: red;'>❌ imagecreatetruecolor() çalışmıyor!</p>";
    }
    
} else {
    echo "<p style='color: red; font-size: 20px;'>❌ GD Extension AKTİF DEĞİL!</p>";
    echo "<h2>Yapılacaklar:</h2>";
    echo "<ol>";
    echo "<li>XAMPP Control Panel'i aç</li>";
    echo "<li>Apache -> Config -> PHP (php.ini) tıkla</li>";
    echo "<li><code>;extension=gd</code> satırını bul</li>";
    echo "<li>Başındaki <code>;</code> işaretini kaldır: <code>extension=gd</code></li>";
    echo "<li>Dosyayı kaydet</li>";
    echo "<li>Apache'yi yeniden başlat (Stop -> Start)</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<h2>Uploads Klasörü Test:</h2>";

$upload_dir = 'uploads/urunler';
if (is_dir($upload_dir)) {
    echo "<p style='color: green;'>✅ {$upload_dir} klasörü mevcut</p>";
    
    if (is_writable($upload_dir)) {
        echo "<p style='color: green;'>✅ {$upload_dir} klasörüne yazma izni VAR</p>";
    } else {
        echo "<p style='color: red;'>❌ {$upload_dir} klasörüne yazma izni YOK</p>";
    }
} else {
    echo "<p style='color: red;'>❌ {$upload_dir} klasörü BULUNAMADI</p>";
}

echo "<hr>";
echo "<a href='admin/restoranlar.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>← Admin Panele Dön</a>";
?>


