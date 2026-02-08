<?php
// Şifre test ve düzeltme sayfası

require_once __DIR__ . '/config.php';

$sifre = 'admin123';
$hash = password_hash($sifre, PASSWORD_DEFAULT);

echo "<h2>Şifre Hash Test</h2>";
echo "<p><strong>Şifre:</strong> admin123</p>";
echo "<p><strong>Yeni Hash:</strong> $hash</p>";

// Veritabanındaki hash
$db_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "<p><strong>Veritabanındaki Hash:</strong> $db_hash</p>";

// Test edelim
$verify_old = password_verify($sifre, $db_hash);
$verify_new = password_verify($sifre, $hash);

echo "<hr>";
echo "<h3>Test Sonuçları:</h3>";
echo "<p><strong>Eski hash geçerli mi?</strong> " . ($verify_old ? 'EVET ✅' : 'HAYIR ❌') . "</p>";
echo "<p><strong>Yeni hash geçerli mi?</strong> " . ($verify_new ? 'EVET ✅' : 'HAYIR ❌') . "</p>";

echo "<hr>";
echo "<h3>Veritabanını Düzelt:</h3>";
echo "<p>phpMyAdmin'de şu SQL sorgusunu çalıştır:</p>";
echo "<textarea style='width: 100%; height: 100px; font-family: monospace;'>";
echo "USE " . DB_NAME . ";\n";
echo "UPDATE adminler SET sifre = '$hash' WHERE kullanici_adi = 'admin';\n";
echo "SELECT * FROM adminler;";
echo "</textarea>";

echo "<hr>";
echo "<h3>Veritabanı Bağlantı Testi:</h3>";

if (isset($db) && $db instanceof PDO) {
    echo "<p>✅ Veritabanı bağlantısı başarılı! (" . DB_NAME . ")</p>";

    // Admin kullanıcısını kontrol et
    $stmt = $db->prepare("SELECT * FROM adminler WHERE kullanici_adi = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "<p>✅ Admin kullanıcısı bulundu!</p>";
        echo "<p><strong>ID:</strong> " . $admin['id'] . "</p>";
        echo "<p><strong>Kullanıcı Adı:</strong> " . $admin['kullanici_adi'] . "</p>";
        echo "<p><strong>Ad Soyad:</strong> " . $admin['ad_soyad'] . "</p>";
        echo "<p><strong>Şifre Hash:</strong> " . substr($admin['sifre'], 0, 50) . "...</p>";

        // Şifre kontrolü
        $verify_db = password_verify('admin123', $admin['sifre']);
        echo "<p><strong>Şifre doğru mu?</strong> " . ($verify_db ? 'EVET ✅' : 'HAYIR ❌') . "</p>";

        if (!$verify_db) {
            echo "<p style='color: red;'><strong>⚠️ SORUN BULUNDU!</strong> Veritabanındaki şifre hash'i çalışmıyor.</p>";
            echo "<p><strong>ÇÖZÜM:</strong> Aşağıdaki SQL sorgusunu phpMyAdmin'de çalıştır:</p>";
            echo "<textarea style='width: 100%; height: 80px; font-family: monospace;'>";
            echo "UPDATE adminler SET sifre = '$hash' WHERE kullanici_adi = 'admin';";
            echo "</textarea>";
            echo "<br><br>";
            echo "<a href='test_password.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Sayfayı Yenile</a>";
        } else {
            echo "<p style='color: green; font-size: 18px;'><strong>🎉 HER ŞEY TAMAM!</strong></p>";
            echo "<p>Şimdi <a href='admin/login.php'>admin panele giriş yapabilirsin</a>!</p>";
            echo "<p><strong>Kullanıcı Adı:</strong> admin</p>";
            echo "<p><strong>Şifre:</strong> admin123</p>";
        }

    } else {
        echo "<p>❌ Admin kullanıcısı bulunamadı!</p>";
    }

} else {
    echo "<p style='color: red;'>❌ Veritabanı bağlantı hatası: Yapılandırma kontrol edilmeli.</p>";
}
?>



