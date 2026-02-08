<?php
require_once '../config.php';

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isAdmin()) {
    redirect('index.php');
}

$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = sanitize($_POST['kullanici_adi'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    
    if (empty($kullanici_adi) || empty($sifre)) {
        $hata = 'Lütfen tüm alanları doldurunuz.';
    } else {
        $stmt = $db->prepare("SELECT * FROM adminler WHERE kullanici_adi = ?");
        $stmt->execute([$kullanici_adi]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($sifre, $admin['sifre'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['ad_soyad'];
            redirect('index.php');
        } else {
            $hata = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h1>Admin Paneli</h1>
                <p>Yönetici Girişi</p>
            </div>
            
            <?php if ($hata): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $hata; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="kullanici_adi">
                        <i class="fas fa-user"></i> Kullanıcı Adı
                    </label>
                    <input 
                        type="text" 
                        id="kullanici_adi" 
                        name="kullanici_adi" 
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="sifre">
                        <i class="fas fa-lock"></i> Şifre
                    </label>
                    <input 
                        type="password" 
                        id="sifre" 
                        name="sifre" 
                        required
                    >
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                </button>
            </form>
            
            <div class="login-footer">
                <a href="../index.php" class="btn-link">
                    <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
                </a>
            </div>
        </div>
    </div>
</body>
</html>

