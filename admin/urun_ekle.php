<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';
$kategori_id = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;

if ($kategori_id <= 0) {
    redirect('restoranlar.php');
}

// Kategori ve restoran bilgilerini getir
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

// Ürün ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urun_adi = sanitize($_POST['urun_adi']);
    $aciklama = sanitize($_POST['aciklama'] ?? '');
    $fiyat = (float)$_POST['fiyat'];
    $porsiyon_bilgisi = sanitize($_POST['porsiyon_bilgisi'] ?? '');
    $kalori = sanitize($_POST['kalori'] ?? '');
    $sira = (int)$_POST['sira'];
    $gorsel_dosya = null;
    
    if (empty($urun_adi)) {
        $hata = 'Ürün adı zorunludur.';
    } else {
        // Görsel yükleme
        if (isset($_FILES['gorsel']) && $_FILES['gorsel']['error'] === UPLOAD_ERR_OK) {
            $upload_sonuc = uploadVeOlceklendir($_FILES['gorsel']);
            if (!$upload_sonuc['success']) {
                $hata = 'Görsel yükleme hatası: ' . $upload_sonuc['error'];
            } else {
                $gorsel_dosya = $upload_sonuc['filename'];
            }
        }
        
        if (empty($hata)) {
            try {
                $stmt = $db->prepare("INSERT INTO menu_urunler (kategori_id, urun_adi, aciklama, fiyat, gorsel, porsiyon_bilgisi, kalori, sira) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$kategori_id, $urun_adi, $aciklama, $fiyat, $gorsel_dosya, $porsiyon_bilgisi, $kalori, $sira]);
                redirect('restoran_duzenle.php?id=' . $kategori['restoran_id']);
            } catch (PDOException $e) {
                $hata = 'Ürün eklenirken hata oluştu.';
                // Hata varsa yüklenen görseli sil
                if ($gorsel_dosya) {
                    gorselSil($gorsel_dosya);
                }
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
    <title>Yeni Ürün Ekle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-plus-circle"></i> Yeni Ürün Ekle</h1>
            <a href="restoran_duzenle.php?id=<?php echo $kategori['restoran_id']; ?>" class="btn btn-sm btn-secondary">
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
                <?php echo sanitize($kategori['restoran_adi']); ?> 
                <i class="fas fa-angle-right" style="color: var(--text-light);"></i>
                <?php echo sanitize($kategori['kategori_adi']); ?>
            </h2>
            
            <form method="POST" enctype="multipart/form-data" style="max-width: 800px;">
                <div class="form-group">
                    <label for="urun_adi">
                        <i class="fas fa-shopping-bag"></i> Ürün Adı *
                    </label>
                    <input type="text" id="urun_adi" name="urun_adi" placeholder="Örn: Adana Kebap" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="gorsel">
                        <i class="fas fa-image"></i> Ürün Görseli
                    </label>
                    <input type="file" id="gorsel" name="gorsel" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small>📸 Maksimum 5MB | Otomatik olarak 800x600px'e ölçeklendirilir | JPG, PNG, GIF, WEBP</small>
                    <div id="gorsel-onizleme" style="margin-top: 10px;"></div>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">
                        <i class="fas fa-align-left"></i> Açıklama
                    </label>
                    <textarea id="aciklama" name="aciklama" rows="3" placeholder="Ürün hakkında detaylı bilgi"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="fiyat">
                            <i class="fas fa-tag"></i> Fiyat (TL) *
                        </label>
                        <input type="number" id="fiyat" name="fiyat" step="0.01" min="0" value="0" required>
                        <small>⚠️ Fiyat sadece admin panelde görünür, kullanıcılar göremez!</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="porsiyon_bilgisi">
                            <i class="fas fa-weight"></i> Porsiyon Bilgisi
                        </label>
                        <input type="text" id="porsiyon_bilgisi" name="porsiyon_bilgisi" placeholder="Örn: 1 Porsiyon, 250gr">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="kalori">
                            <i class="fas fa-fire"></i> Kalori Bilgisi
                        </label>
                        <input type="text" id="kalori" name="kalori" placeholder="Örn: 450 kcal">
                    </div>
                    
                    <div class="form-group">
                        <label for="sira">
                            <i class="fas fa-sort-numeric-up"></i> Sıra
                        </label>
                        <input type="number" id="sira" name="sira" value="0" min="0">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Ürün Ekle
                </button>
                <a href="restoran_duzenle.php?id=<?php echo $kategori['restoran_id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </form>
        </div>
    </div>
    
    <script>
        // Görsel önizleme
        document.getElementById('gorsel').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const onizlemeDiv = document.getElementById('gorsel-onizleme');
            
            if (file) {
                // Dosya boyutu kontrolü
                if (file.size > 5 * 1024 * 1024) {
                    alert('Dosya boyutu maksimum 5MB olmalıdır!');
                    this.value = '';
                    onizlemeDiv.innerHTML = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    onizlemeDiv.innerHTML = `
                        <div style="border: 2px solid var(--primary-color); border-radius: 8px; padding: 10px; display: inline-block;">
                            <img src="${event.target.result}" style="max-width: 300px; max-height: 200px; border-radius: 5px;">
                            <p style="margin: 5px 0 0 0; text-align: center; color: var(--text-light); font-size: 12px;">
                                ✅ Önizleme (Yüklendikten sonra otomatik ölçeklendirilecek)
                            </p>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                onizlemeDiv.innerHTML = '';
            }
        });
    </script>
</body>
</html>


