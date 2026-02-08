<?php
require_once '../config.php';
requireAdmin();

$sehir_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Şehir bilgisini çek
$stmt = $db->prepare("SELECT * FROM sehirler WHERE id = ?");
$stmt->execute([$sehir_id]);
$sehir = $stmt->fetch();

if (!$sehir) {
    $_SESSION['mesaj'] = "Şehir bulunamadı!";
    $_SESSION['mesaj_tip'] = 'danger';
    redirect('sehirler.php');
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sehir_adi = trim($_POST['sehir_adi']);
    $aciklama = trim($_POST['aciklama']);
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    try {
        $stmt = $db->prepare("UPDATE sehirler SET sehir_adi = ?, aciklama = ?, aktif = ? WHERE id = ?");
        $stmt->execute([$sehir_adi, $aciklama, $aktif, $sehir_id]);
        
        $_SESSION['mesaj'] = "Şehir bilgileri başarıyla güncellendi!";
        $_SESSION['mesaj_tip'] = 'success';
        redirect('sehirler.php');
        
    } catch (PDOException $e) {
        $_SESSION['mesaj'] = "Hata: " . $e->getMessage();
        $_SESSION['mesaj_tip'] = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şehir Düzenle - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-edit"></i> Şehir Düzenle</h1>
            <div>
                <a href="sehirler.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri Dön
                </a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?php echo $_SESSION['mesaj_tip']; ?>">
                <?php 
                echo $_SESSION['mesaj'];
                unset($_SESSION['mesaj']);
                unset($_SESSION['mesaj_tip']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Şehir Bilgileri</h2>
                <p style="margin: 0.5rem 0 0; color: #666;">
                    Dosya Adı: <code><?php echo sanitize($sehir['sehir_slug']); ?>.php</code>
                </p>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="sehir_adi">Şehir Adı <span class="required">*</span></label>
                        <input type="text" name="sehir_adi" id="sehir_adi" class="form-control" 
                               value="<?php echo sanitize($sehir['sehir_adi']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="aciklama">Açıklama <span class="required">*</span></label>
                        <textarea name="aciklama" id="aciklama" class="form-control" rows="3" required><?php echo sanitize($sehir['aciklama']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="aktif" <?php echo $sehir['aktif'] ? 'checked' : ''; ?>>
                            Aktif
                        </label>
                        <small class="form-text">Pasif şehirler ana sayfada görünmez.</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Not:</strong> Slug değiştirilemez. Slug değiştirmek için şehri silip yeniden eklemeniz gerekir.
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Kaydet
                        </button>
                        <a href="sehirler.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-cog"></i> İlişkili İçerikler</h2>
            </div>
            <div class="card-body">
                <?php
                // Aktivite sayısı
                $stmt = $db->prepare("SELECT COUNT(*) FROM aktiviteler WHERE sehir_id = ?");
                $stmt->execute([$sehir_id]);
                $aktivite_sayisi = $stmt->fetchColumn();
                
                // Restoran sayısı
                $stmt = $db->prepare("SELECT COUNT(*) FROM restoranlar WHERE sehir_id = ?");
                $stmt->execute([$sehir_id]);
                $restoran_sayisi = $stmt->fetchColumn();
                
                // Tur programı sayısı
                $stmt = $db->prepare("SELECT COUNT(*) FROM takvim WHERE sehir_id = ?");
                $stmt->execute([$sehir_id]);
                $takvim_sayisi = $stmt->fetchColumn();
                ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #3b82f6;">
                            <i class="fas fa-hiking"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $aktivite_sayisi; ?></h3>
                            <p>Aktivite</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #10b981;">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $restoran_sayisi; ?></h3>
                            <p>Restoran</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #f59e0b;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $takvim_sayisi; ?></h3>
                            <p>Tur Programı</p>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="aktiviteler.php?sehir_id=<?php echo $sehir_id; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-hiking"></i> Aktiviteleri Yönet
                    </a>
                    <a href="restoranlar.php?sehir_id=<?php echo $sehir_id; ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-utensils"></i> Restoranları Yönet
                    </a>
                    <a href="takvim.php?sehir_id=<?php echo $sehir_id; ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-calendar-alt"></i> Tur Programlarını Yönet
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

