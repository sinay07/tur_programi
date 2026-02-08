<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';
$restoran_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($restoran_id <= 0) {
    redirect('restoranlar.php');
}

// Restoran bilgilerini getir
$stmt = $db->prepare("
    SELECT r.*, s.sehir_adi 
    FROM restoranlar r
    INNER JOIN sehirler s ON r.sehir_id = s.id
    WHERE r.id = ?
");
$stmt->execute([$restoran_id]);
$restoran = $stmt->fetch();

if (!$restoran) {
    redirect('restoranlar.php');
}

// Restoran güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $baslik = sanitize($_POST['baslik']);
    $aciklama = sanitize($_POST['aciklama']);
    $adres = sanitize($_POST['adres']);
    $mutfak_turu = sanitize($_POST['mutfak_turu']);
    $ortalama_fiyat = sanitize($_POST['ortalama_fiyat']);
    $telefon = sanitize($_POST['telefon']);
    
    if (empty($baslik)) {
        $hata = 'Restoran adı zorunludur.';
    } else {
        try {
            $stmt = $db->prepare("UPDATE restoranlar SET baslik = ?, aciklama = ?, adres = ?, mutfak_turu = ?, ortalama_fiyat = ?, telefon = ? WHERE id = ?");
            $stmt->execute([$baslik, $aciklama, $adres, $mutfak_turu, $ortalama_fiyat, $telefon, $restoran_id]);
            $basari = 'Restoran başarıyla güncellendi.';
            
            // Restoran bilgilerini yeniden yükle
            $stmt = $db->prepare("SELECT r.*, s.sehir_adi FROM restoranlar r INNER JOIN sehirler s ON r.sehir_id = s.id WHERE r.id = ?");
            $stmt->execute([$restoran_id]);
            $restoran = $stmt->fetch();
        } catch (PDOException $e) {
            $hata = 'Restoran güncellenirken hata oluştu.';
        }
    }
}

// Kategorileri getir
$stmt = $db->prepare("SELECT * FROM menu_kategoriler WHERE restoran_id = ? ORDER BY sira ASC, kategori_adi ASC");
$stmt->execute([$restoran_id]);
$kategoriler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restoran Düzenle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-edit"></i> Restoran Düzenle</h1>
            <a href="restoranlar.php" class="btn btn-sm btn-secondary">
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
        
        <!-- Restoran Bilgileri Güncelleme -->
        <div class="admin-card">
            <h2><i class="fas fa-info-circle"></i> Restoran Bilgileri</h2>
            <form method="POST" style="max-width: 800px;">
                <input type="hidden" name="action" value="update">
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Şehir</label>
                    <input type="text" value="<?php echo sanitize($restoran['sehir_adi']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="baslik">
                        <i class="fas fa-heading"></i> Restoran Adı *
                    </label>
                    <input type="text" id="baslik" name="baslik" value="<?php echo sanitize($restoran['baslik']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">
                        <i class="fas fa-align-left"></i> Açıklama
                    </label>
                    <textarea id="aciklama" name="aciklama" rows="4"><?php echo sanitize($restoran['aciklama']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="adres">
                        <i class="fas fa-map-marker-alt"></i> Adres
                    </label>
                    <input type="text" id="adres" name="adres" value="<?php echo sanitize($restoran['adres']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="mutfak_turu">
                        <i class="fas fa-concierge-bell"></i> Mutfak Türü
                    </label>
                    <input type="text" id="mutfak_turu" name="mutfak_turu" value="<?php echo sanitize($restoran['mutfak_turu']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="ortalama_fiyat">
                        <i class="fas fa-wallet"></i> Ortalama Fiyat
                    </label>
                    <input type="text" id="ortalama_fiyat" name="ortalama_fiyat" value="<?php echo sanitize($restoran['ortalama_fiyat']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefon">
                        <i class="fas fa-phone"></i> Telefon
                    </label>
                    <input type="tel" id="telefon" name="telefon" value="<?php echo sanitize($restoran['telefon']); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Bilgileri Güncelle
                </button>
            </form>
        </div>
        
        <!-- Menü Yönetimi -->
        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2><i class="fas fa-book-open"></i> Menü Yönetimi</h2>
                <a href="kategori_ekle.php?restoran_id=<?php echo $restoran_id; ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Kategori Ekle
                </a>
            </div>
            
            <?php if (count($kategoriler) > 0): ?>
                <?php foreach ($kategoriler as $kategori): ?>
                    <?php
                    // Bu kategorinin ürünlerini getir
                    $stmt = $db->prepare("SELECT * FROM menu_urunler WHERE kategori_id = ? ORDER BY sira ASC, urun_adi ASC");
                    $stmt->execute([$kategori['id']]);
                    $urunler = $stmt->fetchAll();
                    ?>
                    
                    <div style="background: #f7fafc; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid var(--primary-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h3 style="margin: 0;">
                                <i class="fas fa-layer-group"></i>
                                <?php echo sanitize($kategori['kategori_adi']); ?>
                                <?php if ($kategori['aciklama']): ?>
                                    <small style="color: var(--text-light); font-weight: normal; font-size: 0.9rem;">
                                        - <?php echo sanitize($kategori['aciklama']); ?>
                                    </small>
                                <?php endif; ?>
                            </h3>
                            <div class="action-buttons">
                                <a href="kategori_duzenle.php?id=<?php echo $kategori['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="urun_ekle.php?kategori_id=<?php echo $kategori['id']; ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i> Ürün Ekle
                                </a>
                            </div>
                        </div>
                        
                        <?php if (count($urunler) > 0): ?>
                            <table style="width: 100%; background: white; border-radius: 8px;">
                                <thead>
                                    <tr>
                                        <th>Ürün Adı</th>
                                        <th>Fiyat</th>
                                        <th>Porsiyon</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($urunler as $urun): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo sanitize($urun['urun_adi']); ?></strong>
                                                <?php if ($urun['aciklama']): ?>
                                                    <br><small style="color: var(--text-light);"><?php echo sanitize($urun['aciklama']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo number_format($urun['fiyat'], 2); ?> TL</strong></td>
                                            <td><?php echo sanitize($urun['porsiyon_bilgisi']); ?></td>
                                            <td>
                                                <?php if ($urun['aktif']): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="urun_duzenle.php?id=<?php echo $urun['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="urun_sil.php?id=<?php echo $urun['id']; ?>&restoran_id=<?php echo $restoran_id; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--text-light); padding: 20px;">
                                Bu kategoride henüz ürün bulunmamaktadır.
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Henüz kategori eklenmemiş. Menü oluşturmak için kategori ekleyin.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


