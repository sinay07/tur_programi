<?php
require_once 'config.php';
requireLogin();

$kullanici_id = $_SESSION['kullanici_id'];
$kullanici = getKullanici($db, $kullanici_id);

// Kullanıcının siparişlerini getir
$stmt = $db->prepare("
    SELECT s.*, r.baslik as restoran_adi
    FROM siparisler s
    INNER JOIN restoranlar r ON s.restoran_id = r.id
    WHERE s.kullanici_id = ?
    ORDER BY s.siparis_tarihi DESC
");
$stmt->execute([$kullanici_id]);
$siparisler = $stmt->fetchAll();

$basari = $_SESSION['basari'] ?? '';
$hata = $_SESSION['hata'] ?? '';
unset($_SESSION['basari'], $_SESSION['hata']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişlerim - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container" style="padding: 40px 20px; min-height: 80vh;">
        <h1><i class="fas fa-receipt"></i> Siparişlerim</h1>
        
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
        
        <?php if (count($siparisler) > 0): ?>
            <?php foreach ($siparisler as $siparis): ?>
                <?php
                // Sipariş ürünlerini getir
                $stmt = $db->prepare("SELECT * FROM siparis_urunler WHERE siparis_id = ?");
                $stmt->execute([$siparis['id']]);
                $urunler = $stmt->fetchAll();
                
                // Durum renkleri
                $durum_renk = [
                    'beklemede' => 'info',
                    'hazirlaniyor' => 'warning',
                    'yolda' => 'primary',
                    'teslim_edildi' => 'success',
                    'iptal' => 'danger'
                ];
                $renk = $durum_renk[$siparis['durum']] ?? 'info';
                
                // Sipariş ne kadar önce verildi?
                $siparis_zamani = strtotime($siparis['siparis_tarihi']);
                $su_an = time();
                $gecen_dakika = ($su_an - $siparis_zamani) / 60;
                $guncellenebilir = ($gecen_dakika <= 30 && $siparis['durum'] == 'beklemede');
                ?>
                
                <div class="admin-card" style="margin-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <h2 style="margin-bottom: 10px;">
                                <i class="fas fa-utensils"></i>
                                <?php echo sanitize($siparis['restoran_adi']); ?>
                            </h2>
                            <p style="color: var(--text-light);">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d.m.Y H:i', strtotime($siparis['siparis_tarihi'])); ?>
                                <span style="margin: 0 10px;">•</span>
                                <i class="fas fa-chair"></i>
                                Koltuk: <strong><?php echo sanitize($siparis['koltuk_no']); ?></strong>
                            </p>
                            <?php if ($guncellenebilir): ?>
                                <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 5px;">
                                    <i class="fas fa-clock"></i>
                                    <small>Siparişinizi <?php echo (30 - (int)$gecen_dakika); ?> dakika içinde güncelleyebilirsiniz.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end;">
                            <span class="badge badge-<?php echo $renk; ?>" style="font-size: 1rem; padding: 10px 20px;">
                                <?php
                                $durum_metinler = [
                                    'beklemede' => 'Beklemede',
                                    'hazirlaniyor' => 'Hazırlanıyor',
                                    'yolda' => 'Yolda',
                                    'teslim_edildi' => 'Teslim Edildi',
                                    'iptal' => 'İptal Edildi'
                                ];
                                echo $durum_metinler[$siparis['durum']];
                                ?>
                            </span>
                            <?php if ($guncellenebilir): ?>
                                <a href="siparis_duzenle.php?id=<?php echo $siparis['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Siparişi Güncelle
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Kategori</th>
                                <th>Adet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($urunler as $urun): ?>
                                <tr>
                                    <td><strong><?php echo sanitize($urun['urun_adi']); ?></strong></td>
                                    <td><?php echo sanitize($urun['kategori_adi']); ?></td>
                                    <td><?php echo $urun['adet']; ?> adet</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (!empty($siparis['siparis_notu'])): ?>
                        <div style="margin-top: 15px; padding: 15px; background: var(--light-color); border-radius: 8px;">
                            <strong><i class="fas fa-comment"></i> Sipariş Notu:</strong>
                            <p style="margin-top: 5px;"><?php echo nl2br(sanitize($siparis['siparis_notu'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="admin-card" style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-receipt" style="font-size: 4rem; color: var(--text-light); margin-bottom: 20px;"></i>
                <h2>Henüz Sipariş Vermediniz</h2>
                <p style="color: var(--text-light); margin: 20px 0;">İlk siparişinizi vermek için menüden ürün seçin.</p>
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Geri Dön
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>


