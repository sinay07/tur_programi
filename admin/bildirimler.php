<?php
require_once '../config.php';
requireAdmin();

// Bildirimi okundu olarak işaretle
if (isset($_GET['okundu']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("UPDATE admin_bildirimler SET okundu = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    // Link varsa yönlendir
    $stmt = $db->prepare("SELECT link FROM admin_bildirimler WHERE id = ?");
    $stmt->execute([$id]);
    $bildirim = $stmt->fetch();
    if ($bildirim && !empty($bildirim['link'])) {
        redirect($bildirim['link']);
    } else {
        redirect('bildirimler.php');
    }
}

// Tüm bildirimleri okundu yap
if (isset($_GET['tumu_okundu'])) {
    $db->query("UPDATE admin_bildirimler SET okundu = 1");
    redirect('bildirimler.php');
}

// Bildirimi sil
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM admin_bildirimler WHERE id = ?");
    $stmt->execute([$id]);
    redirect('bildirimler.php');
}

// Tüm bildirimleri sil
if (isset($_GET['tumu_sil'])) {
    $db->query("DELETE FROM admin_bildirimler");
    redirect('bildirimler.php');
}

// Bildirimleri getir
$stmt = $db->query("SELECT * FROM admin_bildirimler ORDER BY okundu ASC, olusturma_tarihi DESC");
$bildirimler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildirimler - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-bell"></i> Bildirimler</h1>
            <div>
                <?php if (count($bildirimler) > 0): ?>
                    <a href="?tumu_okundu=1" class="btn btn-sm btn-success">
                        <i class="fas fa-check-double"></i> Tümünü Okundu Yap
                    </a>
                    <a href="?tumu_sil=1" class="btn btn-sm btn-danger" onclick="return confirm('Tüm bildirimleri silmek istediğinize emin misiniz?')">
                        <i class="fas fa-trash"></i> Tümünü Sil
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (count($bildirimler) > 0): ?>
            <div class="admin-card">
                <?php foreach ($bildirimler as $bildirim): ?>
                    <?php
                    $tip_renk = [
                        'info' => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'error' => 'danger'
                    ];
                    $renk = $tip_renk[$bildirim['tip']] ?? 'info';
                    $tip_icon = [
                        'info' => 'fa-info-circle',
                        'success' => 'fa-check-circle',
                        'warning' => 'fa-exclamation-triangle',
                        'error' => 'fa-times-circle'
                    ];
                    $icon = $tip_icon[$bildirim['tip']] ?? 'fa-bell';
                    ?>
                    
                    <div style="background: <?php echo $bildirim['okundu'] ? '#f7fafc' : 'white'; ?>; padding: 20px; border-radius: 10px; margin-bottom: 15px; border-left: 4px solid var(--<?php echo $renk; ?>-color); box-shadow: <?php echo $bildirim['okundu'] ? 'none' : '0 2px 8px rgba(102, 126, 234, 0.15)'; ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                    <i class="fas <?php echo $icon; ?>" style="color: var(--<?php echo $renk; ?>-color); font-size: 1.2rem;"></i>
                                    <h3 style="margin: 0; font-size: 1.2rem;">
                                        <?php echo sanitize($bildirim['baslik']); ?>
                                    </h3>
                                    <?php if (!$bildirim['okundu']): ?>
                                        <span class="badge badge-danger" style="font-size: 0.75rem;">YENİ</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p style="margin: 10px 0; color: var(--text-color);">
                                    <?php echo nl2br(sanitize($bildirim['mesaj'])); ?>
                                </p>
                                
                                <small style="color: var(--text-light);">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('d.m.Y H:i', strtotime($bildirim['olusturma_tarihi'])); ?>
                                </small>
                            </div>
                            
                            <div class="action-buttons" style="flex-direction: column;">
                                <?php if (!$bildirim['okundu']): ?>
                                    <a href="?okundu=1&id=<?php echo $bildirim['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?sil=<?php echo $bildirim['id']; ?>" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="admin-card" style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-bell-slash" style="font-size: 4rem; color: var(--text-light); margin-bottom: 20px;"></i>
                <h2>Bildirim Yok</h2>
                <p style="color: var(--text-light);">Henüz hiç bildiriminiz bulunmuyor.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>


