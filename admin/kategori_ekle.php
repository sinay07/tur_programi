<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';
$restoran_id = isset($_GET['restoran_id']) ? (int)$_GET['restoran_id'] : 0;

if ($restoran_id <= 0) {
    redirect('restoranlar.php');
}

// Restoran bilgilerini getir
$stmt = $db->prepare("SELECT * FROM restoranlar WHERE id = ?");
$stmt->execute([$restoran_id]);
$restoran = $stmt->fetch();

if (!$restoran) {
    redirect('restoranlar.php');
}

// Kategori ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori_adi = sanitize($_POST['kategori_adi']);
    $aciklama = sanitize($_POST['aciklama'] ?? '');
    $sira = (int)$_POST['sira'];
    
    if (empty($kategori_adi)) {
        $hata = 'Kategori adı zorunludur.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO menu_kategoriler (restoran_id, kategori_adi, aciklama, sira) VALUES (?, ?, ?, ?)");
            $stmt->execute([$restoran_id, $kategori_adi, $aciklama, $sira]);
            redirect('restoran_duzenle.php?id=' . $restoran_id);
        } catch (PDOException $e) {
            $hata = 'Kategori eklenirken hata oluştu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Kategori Ekle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-plus-circle"></i> Yeni Kategori Ekle</h1>
            <a href="restoran_duzenle.php?id=<?php echo $restoran_id; ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Geri
            </a>
        </div>
        
        <?php if ($hata): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $hata; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-card">
            <h2>
                <i class="fas fa-utensils"></i> 
                <?php echo sanitize($restoran['baslik']); ?> - Yeni Kategori
            </h2>
            
            <form method="POST" style="max-width: 600px;">
                <div class="form-group">
                    <label for="kategori_adi">
                        <i class="fas fa-layer-group"></i> Kategori Adı *
                    </label>
                    <input type="text" id="kategori_adi" name="kategori_adi" placeholder="Örn: Ana Yemekler, Tatlılar, İçecekler" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">
                        <i class="fas fa-align-left"></i> Açıklama
                    </label>
                    <textarea id="aciklama" name="aciklama" rows="3" placeholder="Kategori hakkında kısa açıklama"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="sira">
                        <i class="fas fa-sort-numeric-up"></i> Sıra
                    </label>
                    <input type="number" id="sira" name="sira" value="0" min="0">
                    <small>Kategorilerin menüde gösterilme sırası (0 = en başta)</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kategori Ekle
                </button>
                <a href="restoran_duzenle.php?id=<?php echo $restoran_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </form>
        </div>
        
        <!-- Örnek Kategoriler -->
        <div class="admin-card">
            <h2><i class="fas fa-lightbulb"></i> Örnek Kategoriler</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px; border-left: 3px solid var(--primary-color);">
                    <strong>🍽️ Ana Yemekler</strong>
                    <p style="font-size: 0.9rem; color: var(--text-light); margin-top: 5px;">Kebaplar, etli yemekler</p>
                </div>
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px; border-left: 3px solid var(--success-color);">
                    <strong>🥗 Mezeler</strong>
                    <p style="font-size: 0.9rem; color: var(--text-light); margin-top: 5px;">Soğuk ve sıcak mezeler</p>
                </div>
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px; border-left: 3px solid var(--warning-color);">
                    <strong>🍰 Tatlılar</strong>
                    <p style="font-size: 0.9rem; color: var(--text-light); margin-top: 5px;">Baklava, künefe, sütlü tatlılar</p>
                </div>
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px; border-left: 3px solid var(--info-color);">
                    <strong>🥤 İçecekler</strong>
                    <p style="font-size: 0.9rem; color: var(--text-light); margin-top: 5px;">Soğuk ve sıcak içecekler</p>
                </div>
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px; border-left: 3px solid var(--danger-color);">
                    <strong>🥙 Ara Sıcaklar</strong>
                    <p style="font-size: 0.9rem; color: var(--text-light); margin-top: 5px;">Lahmacun, pide</p>
                </div>
                <div style="background: #f7fafc; padding: 15px; border-radius: 8px; border-left: 3px solid #9333ea;">
                    <strong>🥣 Çorbalar</strong>
                    <p style="font-size: 0.9rem; color: var(--text-light); margin-top: 5px;">Çeşitli çorbalar</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>



