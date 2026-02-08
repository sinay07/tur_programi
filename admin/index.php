<?php
require_once '../config.php';
requireAdmin();

// İstatistikleri al
$stmt = $db->query("SELECT COUNT(*) as total FROM kullanicilar WHERE aktif = 1");
$toplamKullanici = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM sehirler WHERE aktif = 1");
$toplamSehir = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM aktiviteler WHERE aktif = 1");
$toplamAktivite = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM restoranlar WHERE aktif = 1");
$toplamRestoran = $stmt->fetch()['total'];

// Bugünkü tur
$bugunTur = getBugunTur($db);

// Yaklaşan turlar
$stmt = $db->prepare("
    SELECT t.*, s.sehir_adi 
    FROM takvim t
    INNER JOIN sehirler s ON t.sehir_id = s.id
    WHERE t.tarih >= CURDATE() AND t.aktif = 1
    ORDER BY t.tarih ASC
    LIMIT 10
");
$stmt->execute();
$yaklasanTurlar = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <div>
                <span style="margin-right: 15px;">Hoş geldiniz, <strong><?php echo sanitize($_SESSION['admin_name']); ?></strong></span>
                <a href="logout.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Çıkış
                </a>
            </div>
        </div>
        
        <!-- İstatistikler -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Toplam Kullanıcı</h3>
                    <div class="stat-value"><?php echo $toplamKullanici; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Toplam Şehir</h3>
                    <div class="stat-value"><?php echo $toplamSehir; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-hiking"></i>
                </div>
                <div class="stat-info">
                    <h3>Toplam Aktivite</h3>
                    <div class="stat-value"><?php echo $toplamAktivite; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-info">
                    <h3>Toplam Restoran</h3>
                    <div class="stat-value"><?php echo $toplamRestoran; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Bugünkü Tur -->
        <?php if ($bugunTur): ?>
            <div class="admin-card">
                <h2><i class="fas fa-calendar-day"></i> Bugünkü Tur</h2>
                <div class="alert alert-info">
                    <i class="fas fa-map-marker-alt"></i>
                    Bugün <strong><?php echo sanitize($bugunTur['sehir_adi']); ?></strong> turu yapılıyor.
                    <?php if ($bugunTur['aciklama']): ?>
                        <br><?php echo sanitize($bugunTur['aciklama']); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Yaklaşan Turlar -->
        <div class="admin-card">
            <h2><i class="fas fa-calendar-alt"></i> Yaklaşan Turlar</h2>
            
            <?php if (count($yaklasanTurlar) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Şehir</th>
                                <th>Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yaklasanTurlar as $tur): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?php echo date('d.m.Y', strtotime($tur['tarih'])); ?>
                                            (<?php echo getTurkceGunAdi($tur['tarih']); ?>)
                                        </strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                        <?php echo sanitize($tur['sehir_adi']); ?>
                                    </td>
                                    <td><?php echo sanitize($tur['aciklama']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Yaklaşan tur bulunmamaktadır.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

