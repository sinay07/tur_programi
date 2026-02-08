<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';

// Kullanıcı ID kontrolü
if (!isset($_GET['id'])) {
    redirect('kullanicilar.php');
}

$kullanici_id = (int)$_GET['id'];

// Kullanıcı bilgilerini getir
$stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$kullanici = $stmt->fetch();

if (!$kullanici) {
    redirect('kullanicilar.php');
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_soyad = sanitize($_POST['ad_soyad'] ?? '');
    $telefon = preg_replace('/[^0-9]/', '', $_POST['telefon'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($ad_soyad) || empty($telefon)) {
        $hata = 'Ad Soyad ve Telefon alanları zorunludur.';
    } else {
        // Telefon numarası kontrolü (kendisi hariç)
        $stmt = $db->prepare("SELECT id FROM kullanicilar WHERE telefon = ? AND id != ?");
        $stmt->execute([$telefon, $kullanici_id]);
        if ($stmt->fetch()) {
            $hata = 'Bu telefon numarası başka bir kullanıcıya ait.';
        } else {
            try {
                // Bilgileri güncelle
                $stmt = $db->prepare("UPDATE kullanicilar SET ad_soyad = ?, telefon = ?, email = ? WHERE id = ?");
                $stmt->execute([$ad_soyad, $telefon, $email, $kullanici_id]);
                $basari = 'Kullanıcı bilgileri başarıyla güncellendi.';
                
                // Güncel kullanıcı bilgilerini tekrar getir
                $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
                $stmt->execute([$kullanici_id]);
                $kullanici = $stmt->fetch();
            } catch (PDOException $e) {
                $hata = 'Kullanıcı güncellenirken hata oluştu: ' . $e->getMessage();
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
    <title>Kullanıcı Düzenle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-user-edit"></i> Kullanıcı Düzenle</h1>
            <a href="kullanicilar.php" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Geri
            </a>
        </div>
        
        <?php if ($basari): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $basari; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($hata): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $hata; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-card">
            <h2><i class="fas fa-user"></i> Kullanıcı Bilgileri</h2>
            
            <form method="POST" style="max-width: 600px;">
                <div class="form-group">
                    <label for="ad_soyad">
                        <i class="fas fa-user"></i> Ad Soyad *
                    </label>
                    <input type="text" id="ad_soyad" name="ad_soyad" 
                           value="<?php echo sanitize($kullanici['ad_soyad']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telefon">
                        <i class="fas fa-phone"></i> Telefon *
                    </label>
                    <input type="tel" id="telefon" name="telefon" 
                           value="<?php echo sanitize($kullanici['telefon']); ?>" 
                           placeholder="05XXXXXXXXX" maxlength="11" required>
                    <small>Telefon numarası kullanıcının giriş yapması için kullanılır.</small>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> E-posta
                    </label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo sanitize($kullanici['email']); ?>">
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Değişiklikleri Kaydet
                    </button>
                    <a href="kullanicilar.php" class="btn btn-secondary" style="margin-left: 10px;">
                        <i class="fas fa-times"></i> İptal
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Kullanıcı Detayları -->
        <div class="admin-card">
            <h2><i class="fas fa-info-circle"></i> Kullanıcı Detayları</h2>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px;">
                    <strong style="color: #718096; display: block; margin-bottom: 5px;">
                        <i class="fas fa-calendar"></i> Kayıt Tarihi
                    </strong>
                    <span style="color: #2d3748;">
                        <?php echo date('d.m.Y H:i', strtotime($kullanici['kayit_tarihi'])); ?>
                    </span>
                </div>
                
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px;">
                    <strong style="color: #718096; display: block; margin-bottom: 5px;">
                        <i class="fas fa-toggle-on"></i> Durum
                    </strong>
                    <?php if ($kullanici['aktif']): ?>
                        <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Pasif</span>
                    <?php endif; ?>
                </div>
                
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px;">
                    <strong style="color: #718096; display: block; margin-bottom: 5px;">
                        <i class="fas fa-hashtag"></i> Kullanıcı ID
                    </strong>
                    <span style="color: #2d3748;">#<?php echo $kullanici['id']; ?></span>
                </div>
                
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px;">
                    <strong style="color: #718096; display: block; margin-bottom: 5px;">
                        <i class="fas fa-clock"></i> Son Güncelleme
                    </strong>
                    <span style="color: #2d3748;">Az önce</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

