<?php
require_once '../config.php';
requireAdmin();

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_gorsel'])) {
    echo "<h2>Upload Debug</h2>";
    echo "<h3>\$_FILES Bilgileri:</h3>";
    echo "<pre>";
    print_r($_FILES['test_gorsel']);
    echo "</pre>";
    
    if ($_FILES['test_gorsel']['error'] === UPLOAD_ERR_OK) {
        echo "<p style='color: green;'>✅ Dosya başarıyla yüklendi (tmp)</p>";
        
        echo "<h3>uploadVeOlceklendir() Test:</h3>";
        $sonuc = uploadVeOlceklendir($_FILES['test_gorsel']);
        
        echo "<pre>";
        print_r($sonuc);
        echo "</pre>";
        
        if ($sonuc['success']) {
            echo "<p style='color: green; font-size: 20px;'>✅ BAŞARILI! Görsel kaydedildi!</p>";
            echo "<p>Dosya: {$sonuc['filename']}</p>";
            echo "<p>Yol: {$sonuc['path']}</p>";
            echo "<img src='/avustur/{$sonuc['path']}' style='max-width: 400px; border: 2px solid green;'>";
        } else {
            echo "<p style='color: red; font-size: 20px;'>❌ HATA: {$sonuc['error']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Upload hatası: " . $_FILES['test_gorsel']['error'] . "</p>";
        
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'Dosya php.ini\'deki upload_max_filesize sınırını aşıyor',
            UPLOAD_ERR_FORM_SIZE => 'Dosya HTML formdaki MAX_FILE_SIZE sınırını aşıyor',
            UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi',
            UPLOAD_ERR_NO_FILE => 'Hiç dosya yüklenmedi',
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör eksik',
            UPLOAD_ERR_CANT_WRITE => 'Diske yazma hatası',
            UPLOAD_ERR_EXTENSION => 'PHP extension tarafından durduruldu',
        ];
        
        if (isset($upload_errors[$_FILES['test_gorsel']['error']])) {
            echo "<p>Detay: " . $upload_errors[$_FILES['test_gorsel']['error']] . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Upload Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-box {
            background: #f0f0f0;
            padding: 30px;
            border-radius: 10px;
            margin: 20px 0;
        }
        input[type="file"] {
            margin: 10px 0;
        }
        button {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #5568d3;
        }
        pre {
            background: #333;
            color: #0f0;
            padding: 15px;
            border-radius: 5px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <h1>🧪 Görsel Upload Test</h1>
    
    <div class="form-box">
        <h2>Test Görseli Yükle</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="test_gorsel" accept="image/*" required>
            <br><br>
            <button type="submit">📸 Test Et</button>
        </form>
    </div>
    
    <hr>
    
    <h3>Mevcut Uploads Klasörü:</h3>
    <?php
    $upload_dir = '../uploads/urunler';
    if (is_dir($upload_dir)) {
        echo "<p style='color: green;'>✅ Klasör mevcut: " . realpath($upload_dir) . "</p>";
        
        if (is_writable($upload_dir)) {
            echo "<p style='color: green;'>✅ Yazma izni VAR</p>";
        } else {
            echo "<p style='color: red;'>❌ Yazma izni YOK</p>";
        }
        
        $files = scandir($upload_dir);
        $files = array_diff($files, ['.', '..', 'index.php']);
        
        if (count($files) > 0) {
            echo "<h4>Klasördeki Dosyalar:</h4><ul>";
            foreach ($files as $file) {
                echo "<li>{$file}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Klasör boş</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Klasör bulunamadı!</p>";
    }
    ?>
    
    <hr>
    <a href="restoranlar.php" style="color: #667eea;">← Restoranlar</a>
</body>
</html>

