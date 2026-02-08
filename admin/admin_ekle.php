<?php
require_once '../config.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = sanitize($_POST['kullanici_adi'] ?? '');
    $ad_soyad = sanitize($_POST['ad_soyad'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    $sifre_tekrar = $_POST['sifre_tekrar'] ?? '';
    
    // Validasyon
    if (empty($kullanici_adi) || empty($ad_soyad) || empty($sifre)) {
        $error = 'Kullanıcı adı, ad soyad ve şifre alanları zorunludur!';
    } elseif (strlen($kullanici_adi) < 3) {
        $error = 'Kullanıcı adı en az 3 karakter olmalıdır!';
    } elseif (strlen($sifre) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır!';
    } elseif ($sifre !== $sifre_tekrar) {
        $error = 'Şifreler eşleşmiyor!';
    } else {
        // Kullanıcı adı kontrolü
        $stmt = $db->prepare("SELECT id FROM adminler WHERE kullanici_adi = ?");
        $stmt->execute([$kullanici_adi]);
        
        if ($stmt->fetch()) {
            $error = 'Bu kullanıcı adı zaten kullanılıyor!';
        } else {
            // E-posta varsa kontrol et
            if (!empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Geçersiz e-posta adresi!';
                } else {
                    $stmt = $db->prepare("SELECT id FROM adminler WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Bu e-posta adresi zaten kullanılıyor!';
                    }
                }
            }
            
            if (empty($error)) {
                // Admin ekle
                $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO adminler (kullanici_adi, sifre, ad_soyad, email) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$kullanici_adi, $sifre_hash, $ad_soyad, $email])) {
                    $_SESSION['success'] = 'Admin başarıyla eklendi!';
                    header('Location: adminler.php');
                    exit;
                } else {
                    $error = 'Admin eklenirken bir hata oluştu!';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Admin Ekle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>
                <i class="fas fa-user-plus"></i>
                Yeni Admin Ekle
            </h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            </div>
        </div>

        <div class="admin-card">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 2rem;">
                <a href="adminler.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Geri Dön
                </a>
            </div>

            <form method="POST" class="form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="kullanici_adi">
                            <i class="fas fa-user"></i>
                            Kullanıcı Adı *
                        </label>
                        <input type="text" 
                               id="kullanici_adi" 
                               name="kullanici_adi" 
                               value="<?php echo htmlspecialchars($_POST['kullanici_adi'] ?? ''); ?>"
                               required
                               minlength="3"
                               placeholder="admin, mehmet, vb.">
                        <small>En az 3 karakter, giriş için kullanılacak</small>
                    </div>

                    <div class="form-group">
                        <label for="ad_soyad">
                            <i class="fas fa-id-card"></i>
                            Ad Soyad *
                        </label>
                        <input type="text" 
                               id="ad_soyad" 
                               name="ad_soyad" 
                               value="<?php echo htmlspecialchars($_POST['ad_soyad'] ?? ''); ?>"
                               required
                               placeholder="Mehmet Yılmaz">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        E-posta (Opsiyonel)
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="admin@example.com">
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="sifre">
                            <i class="fas fa-lock"></i>
                            Şifre *
                        </label>
                        <input type="password" 
                               id="sifre" 
                               name="sifre" 
                               required
                               minlength="6"
                               placeholder="••••••••">
                        <small>En az 6 karakter</small>
                    </div>

                    <div class="form-group">
                        <label for="sifre_tekrar">
                            <i class="fas fa-lock"></i>
                            Şifre Tekrar *
                        </label>
                        <input type="password" 
                               id="sifre_tekrar" 
                               name="sifre_tekrar" 
                               required
                               minlength="6"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Admin Ekle
                    </button>
                    <a href="adminler.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        İptal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/bildirim_popup.php'; ?>
</body>
</html>

