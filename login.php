<?php
require_once 'config.php';

// Zaten giriş yapmışsa anasayfaya yönlendir
if (isLoggedIn()) {
    redirect('index.php');
}

$hata = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefon = sanitize($_POST['telefon'] ?? '');
    
    if (empty($telefon)) {
        $hata = 'Lütfen telefon numaranızı giriniz.';
    } else {
        // Telefon numarasını temizle (sadece rakamlar)
        $telefon = preg_replace('/[^0-9]/', '', $telefon);
        
        // Kullanıcıyı kontrol et
        $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE telefon = ? AND aktif = 1");
        $stmt->execute([$telefon]);
        $kullanici = $stmt->fetch();
        
        if ($kullanici) {
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['kullanici_adi'] = $kullanici['ad_soyad'];
            $_SESSION['kullanici_telefon'] = $kullanici['telefon'];
            
            // Bugünkü tura yönlendir
            $bugunTur = getBugunTur($db);
            if ($bugunTur) {
                redirect($bugunTur['sehir_slug'] . '.php');
            } else {
                redirect('index.php');
            }
        } else {
            $hata = 'Bu telefon numarası ile kayıtlı kullanıcı bulunamadı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-route"></i>
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Kullanıcı Girişi</p>
            </div>
            
            <?php if ($hata): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $hata; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($basari): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $basari; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="telefon">
                        <i class="fas fa-phone"></i> Telefon Numarası
                    </label>
                    <input 
                        type="tel" 
                        id="telefon" 
                        name="telefon" 
                        placeholder="05XX XXX XX XX"
                        maxlength="11"
                        required
                        autofocus
                    >
                    <small>Kayıtlı telefon numaranızı giriniz</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                </button>
            </form>
            
            <div class="login-footer">
                <a href="index.php" class="btn-link">
                    <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
                </a>
            </div>
        </div>
    </div>
</body>
</html>

