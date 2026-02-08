<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';
$kategori_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($kategori_id <= 0) {
    redirect('restoranlar.php');
}

// Kategori bilgilerini getir
$stmt = $db->prepare("
    SELECT k.*, r.baslik as restoran_adi, r.id as restoran_id
    FROM menu_kategoriler k
    INNER JOIN restoranlar r ON k.restoran_id = r.id
    WHERE k.id = ?
");
$stmt->execute([$kategori_id]);
$kategori = $stmt->fetch();

if (!$kategori) {
    redirect('restoranlar.php');
}

$restoran_id = $kategori['restoran_id'];

// Kategori güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $kategori_adi = sanitize($_POST['kategori_adi']);
    $aciklama = sanitize($_POST['aciklama'] ?? '');
    $sira = (int)$_POST['sira'];
    
    if (empty($kategori_adi)) {
        $hata = 'Kategori adı zorunludur.';
    } else {
        try {
            $stmt = $db->prepare("UPDATE menu_kategoriler SET kategori_adi = ?, aciklama = ?, sira = ? WHERE id = ?");
            $stmt->execute([$kategori_adi, $aciklama, $sira, $kategori_id]);
            $basari = 'Kategori başarıyla güncellendi.';
            
            // Kategori bilgilerini yeniden yükle
            $stmt = $db->prepare("SELECT k.*, r.baslik as restoran_adi, r.id as restoran_id FROM menu_kategoriler k INNER JOIN restoranlar r ON k.restoran_id = r.id WHERE k.id = ?");
            $stmt->execute([$kategori_id]);
            $kategori = $stmt->fetch();
        } catch (PDOException $e) {
            $hata = 'Kategori güncellenirken hata oluştu.';
        }
    }
}

// Kategori silme
if (isset($_GET['delete']) && $_GET['delete'] == 'confirm') {
    try {
        $stmt = $db->prepare("DELETE FROM menu_kategoriler WHERE id = ?");
        $stmt->execute([$kategori_id]);
        redirect('restoran_duzenle.php?id=' . $restoran_id);
    } catch (PDOException $e) {
        $hata = 'Kategori silinirken hata oluştu. Önce kategorideki ürünleri silmelisiniz.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Düzenle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-edit"></i> Kategori Düzenle</h1>
            <div>
                <a href="restoran_duzenle.php?id=<?php echo $restoran_id; ?>" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
                <a href="?id=<?php echo $kategori_id; ?>&delete=confirm" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Bu kategoriyi ve içindeki TÜM ÜRÜNLERİ silmek istediğinize emin misiniz?')">
                    <i class="fas fa-trash"></i> Kategoriyi Sil
                </a>
            </div>
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
            <h2>
                <i class="fas fa-utensils"></i> 
                <?php echo sanitize($kategori['restoran_adi']); ?> - Kategori Düzenle
            </h2>
            
            <form method="POST" style="max-width: 600px;">
                <input type="hidden" name="action" value="update">
                
                <div class="form-group">
                    <label for="kategori_adi">
                        <i class="fas fa-layer-group"></i> Kategori Adı *
                    </label>
                    <input type="text" id="kategori_adi" name="kategori_adi" value="<?php echo sanitize($kategori['kategori_adi']); ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">
                        <i class="fas fa-align-left"></i> Açıklama
                    </label>
                    <textarea id="aciklama" name="aciklama" rows="3"><?php echo sanitize($kategori['aciklama']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="sira">
                        <i class="fas fa-sort-numeric-up"></i> Sıra
                    </label>
                    <input type="number" id="sira" name="sira" value="<?php echo $kategori['sira']; ?>" min="0">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                </button>
                <a href="restoran_duzenle.php?id=<?php echo $restoran_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </form>
        </div>
    </div>
</body>
</html>



