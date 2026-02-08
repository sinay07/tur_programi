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

// Silme işlemi onaylandı mı?
if (isset($_GET['onayla']) && $_GET['onayla'] === 'evet') {
    try {
        // Veritabanından sil (CASCADE ile ilişkili veriler otomatik silinir)
        $stmt = $db->prepare("DELETE FROM sehirler WHERE id = ?");
        $stmt->execute([$sehir_id]);
        
        // PHP dosyasını sil
        $php_dosya_adi = "../{$sehir['sehir_slug']}.php";
        if (file_exists($php_dosya_adi)) {
            if (unlink($php_dosya_adi)) {
                $_SESSION['mesaj'] = "Şehir ve ilgili PHP dosyası başarıyla silindi!";
            } else {
                $_SESSION['mesaj'] = "Şehir silindi ancak PHP dosyası silinemedi. Manuel olarak siliniz: {$sehir['sehir_slug']}.php";
            }
        } else {
            $_SESSION['mesaj'] = "Şehir başarıyla silindi!";
        }
        
        $_SESSION['mesaj_tip'] = 'success';
        redirect('sehirler.php');
        
    } catch (PDOException $e) {
        $_SESSION['mesaj'] = "Hata: " . $e->getMessage();
        $_SESSION['mesaj_tip'] = 'danger';
        redirect('sehirler.php');
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şehir Sil - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-trash"></i> Şehir Sil</h1>
            <div>
                <a href="sehirler.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri Dön
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header" style="background: #ef4444; color: white;">
                <h2 style="color: white;"><i class="fas fa-exclamation-triangle"></i> Dikkat!</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-danger" style="font-size: 1.1rem;">
                    <p><strong><?php echo sanitize($sehir['sehir_adi']); ?></strong> şehrini silmek üzeresiniz!</p>
                    <p style="margin-top: 1rem;">Bu işlem geri alınamaz ve aşağıdaki veriler silinecektir:</p>
                </div>
                
                <?php
                // İstatistikler
                $stmt = $db->prepare("SELECT COUNT(*) FROM aktiviteler WHERE sehir_id = ?");
                $stmt->execute([$sehir_id]);
                $aktivite_sayisi = $stmt->fetchColumn();
                
                $stmt = $db->prepare("SELECT COUNT(*) FROM restoranlar WHERE sehir_id = ?");
                $stmt->execute([$sehir_id]);
                $restoran_sayisi = $stmt->fetchColumn();
                
                $stmt = $db->prepare("SELECT COUNT(*) FROM takvim WHERE sehir_id = ?");
                $stmt->execute([$sehir_id]);
                $takvim_sayisi = $stmt->fetchColumn();
                ?>
                
                <div style="background: #fef3c7; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 1.5rem 0;">
                    <h3 style="margin-top: 0; color: #92400e;">
                        <i class="fas fa-database"></i> Silinecek Veriler
                    </h3>
                    <ul style="line-height: 2; font-size: 1.05rem;">
                        <li><i class="fas fa-check-circle" style="color: #f59e0b;"></i> <strong><?php echo $aktivite_sayisi; ?></strong> adet aktivite</li>
                        <li><i class="fas fa-check-circle" style="color: #f59e0b;"></i> <strong><?php echo $restoran_sayisi; ?></strong> adet restoran</li>
                        <li><i class="fas fa-check-circle" style="color: #f59e0b;"></i> <strong><?php echo $takvim_sayisi; ?></strong> adet tur programı</li>
                        <li><i class="fas fa-check-circle" style="color: #f59e0b;"></i> PHP dosyası: <code><?php echo sanitize($sehir['sehir_slug']); ?>.php</code></li>
                    </ul>
                </div>
                
                <div style="background: #fee2e2; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #dc2626; margin: 1.5rem 0;">
                    <h3 style="margin-top: 0; color: #991b1b;">
                        <i class="fas fa-exclamation-circle"></i> Bu İşlem Geri Alınamaz!
                    </h3>
                    <p style="margin: 0; font-size: 1.05rem;">
                        Silme işlemini onaylamak istediğinizden emin misiniz?
                    </p>
                </div>
                
                <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                    <a href="sehir_sil.php?id=<?php echo $sehir_id; ?>&onayla=evet" 
                       class="btn btn-danger btn-lg">
                        <i class="fas fa-trash"></i> Evet, Şehri Sil
                    </a>
                    <a href="sehirler.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Hayır, İptal Et
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

