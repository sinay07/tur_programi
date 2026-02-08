<?php
require_once '../config.php';
requireAdmin();

$error = '';
$success = '';

// ID kontrolü
if (!isset($_GET['id'])) {
    header('Location: adminler.php');
    exit;
}

$admin_id = (int)$_GET['id'];

// Admin bilgilerini getir
$stmt = $db->prepare("SELECT * FROM adminler WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION['error'] = 'Admin bulunamadı!';
    header('Location: adminler.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = sanitize($_POST['kullanici_adi'] ?? '');
    $ad_soyad = sanitize($_POST['ad_soyad'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $yeni_sifre = $_POST['yeni_sifre'] ?? '';
    $yeni_sifre_tekrar = $_POST['yeni_sifre_tekrar'] ?? '';
    
    // Validasyon
    if (empty($kullanici_adi) || empty($ad_soyad)) {
        $error = 'Kullanıcı adı ve ad soyad alanları zorunludur!';
    } elseif (strlen($kullanici_adi) < 3) {
        $error = 'Kullanıcı adı en az 3 karakter olmalıdır!';
    } else {
        // Kullanıcı adı kontrolü (başka biri kullanıyor mu?)
        $stmt = $db->prepare("SELECT id FROM adminler WHERE kullanici_adi = ? AND id != ?");
        $stmt->execute([$kullanici_adi, $admin_id]);
        
        if ($stmt->fetch()) {
            $error = 'Bu kullanıcı adı zaten kullanılıyor!';
        } else {
            // E-posta varsa kontrol et
            if (!empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Geçersiz e-posta adresi!';
                } else {
                    $stmt = $db->prepare("SELECT id FROM adminler WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $admin_id]);
                    if ($stmt->fetch()) {
                        $error = 'Bu e-posta adresi zaten kullanılıyor!';
                    }
                }
            }
            
            // Şifre değiştirme kontrolü
            if (!empty($yeni_sifre)) {
                if (strlen($yeni_sifre) < 6) {
                    $error = 'Yeni şifre en az 6 karakter olmalıdır!';
                } elseif ($yeni_sifre !== $yeni_sifre_tekrar) {
                    $error = 'Yeni şifreler eşleşmiyor!';
                }
            }
            
            if (empty($error)) {
                // Admin güncelle
                if (!empty($yeni_sifre)) {
                    // Şifre ile birlikte güncelle
                    $sifre_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE adminler SET kullanici_adi = ?, sifre = ?, ad_soyad = ?, email = ? WHERE id = ?");
                    $result = $stmt->execute([$kullanici_adi, $sifre_hash, $ad_soyad, $email, $admin_id]);
                } else {
                    // Sadece bilgileri güncelle
                    $stmt = $db->prepare("UPDATE adminler SET kullanici_adi = ?, ad_soyad = ?, email = ? WHERE id = ?");
                    $result = $stmt->execute([$kullanici_adi, $ad_soyad, $email, $admin_id]);
                }
                
                if ($result) {
                    // Kendi hesabını güncelliyorsa session'ı da güncelle
                    if ($admin_id == $_SESSION['admin_id']) {
                        $_SESSION['admin_name'] = $ad_soyad;
                    }
                    
                    $_SESSION['success'] = 'Admin başarıyla güncellendi!';
                    header('Location: adminler.php');
                    exit;
                } else {
                    $error = 'Admin güncellenirken bir hata oluştu!';
                }
            }
        }
    }
    
    // Hata varsa, formda girilen değerleri kullan
    if (!empty($error)) {
        $admin['kullanici_adi'] = $kullanici_adi;
        $admin['ad_soyad'] = $ad_soyad;
        $admin['email'] = $email;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Düzenle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>
                <i class="fas fa-user-edit"></i>
                Admin Düzenle
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

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 2rem;">
                <a href="adminler.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Geri Dön
                </a>
            </div>

            <?php if ($admin_id == $_SESSION['admin_id']): ?>
                <div class="alert alert-info" style="margin-bottom: 2rem;">
                    <i class="fas fa-info-circle"></i>
                    Kendi hesabınızı düzenliyorsunuz.
                </div>
            <?php endif; ?>

            <form method="POST" class="form">
                <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">
                    <i class="fas fa-user"></i>
                    Temel Bilgiler
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="kullanici_adi">
                            <i class="fas fa-user"></i>
                            Kullanıcı Adı *
                        </label>
                        <input type="text" 
                               id="kullanici_adi" 
                               name="kullanici_adi" 
                               value="<?php echo htmlspecialchars($admin['kullanici_adi']); ?>"
                               required
                               minlength="3">
                        <small>Giriş için kullanılan kullanıcı adı</small>
                    </div>

                    <div class="form-group">
                        <label for="ad_soyad">
                            <i class="fas fa-id-card"></i>
                            Ad Soyad *
                        </label>
                        <input type="text" 
                               id="ad_soyad" 
                               name="ad_soyad" 
                               value="<?php echo htmlspecialchars($admin['ad_soyad']); ?>"
                               required>
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
                           value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>">
                </div>

                <hr style="margin: 2rem 0; border: none; border-top: 2px solid var(--border-color);">

                <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">
                    <i class="fas fa-key"></i>
                    Şifre Değiştir (Opsiyonel)
                </h3>

                <div class="alert alert-info" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-info-circle"></i>
                    Şifreyi değiştirmek istemiyorsanız bu alanları boş bırakın.
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="yeni_sifre">
                            <i class="fas fa-lock"></i>
                            Yeni Şifre
                        </label>
                        <input type="password" 
                               id="yeni_sifre" 
                               name="yeni_sifre"
                               minlength="6"
                               placeholder="••••••••">
                        <small>En az 6 karakter (boş bırakılırsa değişmez)</small>
                    </div>

                    <div class="form-group">
                        <label for="yeni_sifre_tekrar">
                            <i class="fas fa-lock"></i>
                            Yeni Şifre Tekrar
                        </label>
                        <input type="password" 
                               id="yeni_sifre_tekrar" 
                               name="yeni_sifre_tekrar"
                               minlength="6"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Değişiklikleri Kaydet
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

