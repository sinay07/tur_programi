<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';
$urun_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($urun_id <= 0) {
    redirect('restoranlar.php');
}

// Ürün bilgilerini getir
$stmt = $db->prepare("
    SELECT u.*, k.kategori_adi, k.id as kategori_id, r.baslik as restoran_adi, r.id as restoran_id
    FROM menu_urunler u
    INNER JOIN menu_kategoriler k ON u.kategori_id = k.id
    INNER JOIN restoranlar r ON k.restoran_id = r.id
    WHERE u.id = ?
");
$stmt->execute([$urun_id]);
$urun = $stmt->fetch();

if (!$urun) {
    redirect('restoranlar.php');
}

// Ürün güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urun_adi = sanitize($_POST['urun_adi']);
    $aciklama = sanitize($_POST['aciklama'] ?? '');
    $fiyat = (float)$_POST['fiyat'];
    $porsiyon_bilgisi = sanitize($_POST['porsiyon_bilgisi'] ?? '');
    $kalori = sanitize($_POST['kalori'] ?? '');
    $sira = (int)$_POST['sira'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    $gorsel_dosya = $urun['gorsel']; // Mevcut görseli koru
    $gorsel_sil = isset($_POST['gorsel_sil']) ? true : false;
    
    if (empty($urun_adi)) {
        $hata = 'Ürün adı zorunludur.';
    } else {
        // Yeni görsel yüklendi mi?
        if (isset($_FILES['gorsel']) && $_FILES['gorsel']['error'] === UPLOAD_ERR_OK) {
            $upload_sonuc = uploadVeOlceklendir($_FILES['gorsel']);
            if (!$upload_sonuc['success']) {
                $hata = 'Görsel yükleme hatası: ' . $upload_sonuc['error'];
            } else {
                // Eski görseli sil
                if (!empty($urun['gorsel'])) {
                    gorselSil($urun['gorsel']);
                }
                $gorsel_dosya = $upload_sonuc['filename'];
            }
        } elseif ($gorsel_sil && !empty($urun['gorsel'])) {
            // Görsel silinmek istendi
            gorselSil($urun['gorsel']);
            $gorsel_dosya = null;
        }
        
        if (empty($hata)) {
            try {
                $stmt = $db->prepare("UPDATE menu_urunler SET urun_adi = ?, aciklama = ?, fiyat = ?, gorsel = ?, porsiyon_bilgisi = ?, kalori = ?, sira = ?, aktif = ? WHERE id = ?");
                $stmt->execute([$urun_adi, $aciklama, $fiyat, $gorsel_dosya, $porsiyon_bilgisi, $kalori, $sira, $aktif, $urun_id]);
                $basari = 'Ürün başarıyla güncellendi.';
                
                // Ürün bilgilerini yeniden yükle
                $stmt = $db->prepare("SELECT u.*, k.kategori_adi, k.id as kategori_id, r.baslik as restoran_adi, r.id as restoran_id FROM menu_urunler u INNER JOIN menu_kategoriler k ON u.kategori_id = k.id INNER JOIN restoranlar r ON k.restoran_id = r.id WHERE u.id = ?");
                $stmt->execute([$urun_id]);
                $urun = $stmt->fetch();
            } catch (PDOException $e) {
                $hata = 'Ürün güncellenirken hata oluştu.';
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
    <title>Ürün Düzenle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-edit"></i> Ürün Düzenle</h1>
            <a href="restoran_duzenle.php?id=<?php echo $urun['restoran_id']; ?>" class="btn btn-sm btn-secondary">
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
            <h2>
                <i class="fas fa-utensils"></i> 
                <?php echo sanitize($urun['restoran_adi']); ?> 
                <i class="fas fa-angle-right" style="color: var(--text-light);"></i>
                <?php echo sanitize($urun['kategori_adi']); ?>
            </h2>
            
            <form method="POST" enctype="multipart/form-data" style="max-width: 800px;">
                <div class="form-group">
                    <label for="urun_adi">
                        <i class="fas fa-shopping-bag"></i> Ürün Adı *
                    </label>
                    <input type="text" id="urun_adi" name="urun_adi" value="<?php echo sanitize($urun['urun_adi']); ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-image"></i> Ürün Görseli
                    </label>
                    
                    <?php if (!empty($urun['gorsel'])): ?>
                        <div style="margin-bottom: 15px; background: #f7fafc; padding: 15px; border-radius: 8px;">
                            <img src="/avustur/uploads/urunler/<?php echo sanitize($urun['gorsel']); ?>" 
                                 style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                            <div style="margin-top: 10px;">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: #e53e3e;">
                                    <input type="checkbox" name="gorsel_sil" id="gorsel_sil">
                                    <span><i class="fas fa-trash"></i> Mevcut görseli sil</span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" id="gorsel" name="gorsel" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small>📸 Maksimum 5MB | Otomatik olarak 800x600px'e ölçeklendirilir | JPG, PNG, GIF, WEBP</small>
                    <div id="gorsel-onizleme" style="margin-top: 10px;"></div>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">
                        <i class="fas fa-align-left"></i> Açıklama
                    </label>
                    <textarea id="aciklama" name="aciklama" rows="3"><?php echo sanitize($urun['aciklama']); ?></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="fiyat">
                            <i class="fas fa-tag"></i> Fiyat (TL) *
                        </label>
                        <input type="number" id="fiyat" name="fiyat" step="0.01" min="0" value="<?php echo $urun['fiyat']; ?>" required>
                        <small>⚠️ Fiyat sadece admin panelde görünür!</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="porsiyon_bilgisi">
                            <i class="fas fa-weight"></i> Porsiyon Bilgisi
                        </label>
                        <input type="text" id="porsiyon_bilgisi" name="porsiyon_bilgisi" value="<?php echo sanitize($urun['porsiyon_bilgisi']); ?>">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="kalori">
                            <i class="fas fa-fire"></i> Kalori Bilgisi
                        </label>
                        <input type="text" id="kalori" name="kalori" value="<?php echo sanitize($urun['kalori']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="sira">
                            <i class="fas fa-sort-numeric-up"></i> Sıra
                        </label>
                        <input type="number" id="sira" name="sira" value="<?php echo $urun['sira']; ?>" min="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="aktif" <?php echo $urun['aktif'] ? 'checked' : ''; ?>>
                        <span><i class="fas fa-check-circle"></i> Ürün Aktif (Kullanıcılar Görebilsin)</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                </button>
                <a href="restoran_duzenle.php?id=<?php echo $urun['restoran_id']; ?>" class="btn btn-secondary">
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
                        <div style="border: 2px solid var(--primary-color); border-radius: 8px; padding: 10px; display: inline-block; margin-top: 10px;">
                            <img src="${event.target.result}" style="max-width: 300px; max-height: 200px; border-radius: 5px;">
                            <p style="margin: 5px 0 0 0; text-align: center; color: var(--text-light); font-size: 12px;">
                                ✅ Yeni Görsel Önizleme (Yüklendikten sonra otomatik ölçeklendirilecek)
                            </p>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                onizlemeDiv.innerHTML = '';
            }
        });
        
        // Görsel silme checkbox'ı işaretlendiğinde dosya input'unu devre dışı bırak
        const gorselSilCheckbox = document.getElementById('gorsel_sil');
        if (gorselSilCheckbox) {
            gorselSilCheckbox.addEventListener('change', function() {
                const gorselInput = document.getElementById('gorsel');
                const onizlemeDiv = document.getElementById('gorsel-onizleme');
                if (this.checked) {
                    gorselInput.disabled = true;
                    gorselInput.value = '';
                    onizlemeDiv.innerHTML = '<p style="color: #e53e3e;"><i class="fas fa-exclamation-triangle"></i> Mevcut görsel silinecek!</p>';
                } else {
                    gorselInput.disabled = false;
                    onizlemeDiv.innerHTML = '';
                }
            });
        }
    </script>
</body>
</html>


