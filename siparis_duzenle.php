<?php
require_once 'config.php';
requireLogin();

$siparis_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$kullanici_id = $_SESSION['kullanici_id'];

// Siparişi kontrol et
$stmt = $db->prepare("
    SELECT s.*, r.baslik as restoran_adi, r.id as restoran_id
    FROM siparisler s
    INNER JOIN restoranlar r ON s.restoran_id = r.id
    WHERE s.id = ? AND s.kullanici_id = ?
");
$stmt->execute([$siparis_id, $kullanici_id]);
$siparis = $stmt->fetch();

if (!$siparis) {
    redirect('siparislerim.php');
}

// 30 dakika kontrolü
$siparis_zamani = strtotime($siparis['siparis_tarihi']);
$su_an = time();
$gecen_dakika = ($su_an - $siparis_zamani) / 60;

if ($gecen_dakika > 30 || $siparis['durum'] != 'beklemede') {
    $_SESSION['hata'] = 'Bu sipariş artık güncellenemez.';
    redirect('siparislerim.php');
}

// Sipariş ürünlerini getir
$stmt = $db->prepare("SELECT * FROM siparis_urunler WHERE siparis_id = ?");
$stmt->execute([$siparis_id]);
$siparis_urunleri = $stmt->fetchAll();

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    $yeni_urunler = $_POST['urunler'] ?? [];
    $koltuk_no = sanitize($_POST['koltuk_no']);
    $siparis_notu = sanitize($_POST['siparis_notu']);
    
    try {
        $db->beginTransaction();
        
        // Eski ürünleri sil
        $stmt = $db->prepare("DELETE FROM siparis_urunler WHERE siparis_id = ?");
        $stmt->execute([$siparis_id]);
        
        // Yeni ürünleri ekle ve toplam hesapla
        $toplam_fiyat = 0;
        foreach ($yeni_urunler as $urun_id => $adet) {
            if ($adet > 0) {
                // Ürün bilgilerini al
                $stmt = $db->prepare("
                    SELECT u.*, k.kategori_adi
                    FROM menu_urunler u
                    INNER JOIN menu_kategoriler k ON u.kategori_id = k.id
                    WHERE u.id = ?
                ");
                $stmt->execute([$urun_id]);
                $urun = $stmt->fetch();
                
                if ($urun) {
                    $urun_toplam = $urun['fiyat'] * $adet;
                    $toplam_fiyat += $urun_toplam;
                    
                    $stmt = $db->prepare("
                        INSERT INTO siparis_urunler (siparis_id, urun_id, urun_adi, kategori_adi, adet, birim_fiyat, toplam_fiyat) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $siparis_id,
                        $urun['id'],
                        $urun['urun_adi'],
                        $urun['kategori_adi'],
                        $adet,
                        $urun['fiyat'],
                        $urun_toplam
                    ]);
                }
            }
        }
        
        // Siparişi güncelle
        $stmt = $db->prepare("UPDATE siparisler SET koltuk_no = ?, siparis_notu = ?, toplam_fiyat = ? WHERE id = ?");
        $stmt->execute([$koltuk_no, $siparis_notu, $toplam_fiyat, $siparis_id]);
        
        // Admin bildirimi oluştur
        $kullanici = getKullanici($db, $kullanici_id);
        $bildirim_mesaj = $kullanici['ad_soyad'] . ' (#' . $siparis_id . ' numaralı) siparişini güncelledi. Koltuk: ' . $koltuk_no . ', Restoran: ' . $siparis['restoran_adi'];
        $stmt = $db->prepare("INSERT INTO admin_bildirimler (baslik, mesaj, tip, link) VALUES (?, ?, 'warning', ?)");
        $stmt->execute([
            'Sipariş Güncellendi',
            $bildirim_mesaj,
            'siparisler.php'
        ]);
        
        $db->commit();
        
        $_SESSION['basari'] = 'Siparişiniz başarıyla güncellendi!';
        redirect('siparislerim.php');
        
    } catch (PDOException $e) {
        $db->rollBack();
        $hata = 'Sipariş güncellenirken hata oluştu.';
    }
}

// Restoran menüsünü getir
$kategoriler = getMenuKategoriler($db, $siparis['restoran_id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Güncelle - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container" style="padding: 40px 20px; min-height: 80vh;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><i class="fas fa-edit"></i> Sipariş Güncelle</h1>
            <div class="alert alert-warning" style="margin: 0;">
                <i class="fas fa-clock"></i>
                Kalan süre: <strong><?php echo (30 - (int)$gecen_dakika); ?> dakika</strong>
            </div>
        </div>
        
        <form method="POST">
            <div class="admin-card">
                <h2><i class="fas fa-utensils"></i> <?php echo sanitize($siparis['restoran_adi']); ?></h2>
                
                <h3 style="margin: 20px 0;">
                    <i class="fas fa-book-open"></i> Menüden Ürün Seç
                </h3>
                
                <?php foreach ($kategoriler as $kategori): ?>
                    <?php
                    $urunler = $db->prepare("
                        SELECT id, urun_adi, aciklama, porsiyon_bilgisi
                        FROM menu_urunler 
                        WHERE kategori_id = ? AND aktif = 1 
                        ORDER BY sira ASC, urun_adi ASC
                    ");
                    $urunler->execute([$kategori['id']]);
                    $urunler = $urunler->fetchAll();
                    
                    if (count($urunler) > 0):
                    ?>
                        <div style="margin-bottom: 30px;">
                            <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                                <i class="fas fa-layer-group"></i>
                                <?php echo sanitize($kategori['kategori_adi']); ?>
                            </h4>
                            
                            <div style="display: grid; gap: 10px;">
                                <?php foreach ($urunler as $urun): ?>
                                    <?php
                                    // Mevcut siparişte bu ürün var mı?
                                    $mevcut_adet = 0;
                                    foreach ($siparis_urunleri as $su) {
                                        if ($su['urun_id'] == $urun['id']) {
                                            $mevcut_adet = $su['adet'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <div style="background: var(--light-color); padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                        <div style="flex: 1;">
                                            <strong><?php echo sanitize($urun['urun_adi']); ?></strong>
                                            <?php if (!empty($urun['aciklama'])): ?>
                                                <div style="font-size: 0.9rem; color: var(--text-light); margin-top: 3px;">
                                                    <?php echo sanitize($urun['aciklama']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($urun['porsiyon_bilgisi'])): ?>
                                                <div style="font-size: 0.85rem; color: var(--text-light); margin-top: 3px;">
                                                    <?php echo sanitize($urun['porsiyon_bilgisi']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <input type="number" 
                                                   name="urunler[<?php echo $urun['id']; ?>]" 
                                                   value="<?php echo $mevcut_adet; ?>"
                                                   min="0"
                                                   max="20"
                                                   style="width: 80px; padding: 8px; border: 2px solid var(--border-color); border-radius: 5px; text-align: center; font-weight: bold;">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <hr style="margin: 30px 0;">
                
                <h3><i class="fas fa-clipboard-check"></i> Sipariş Bilgileri</h3>
                
                <div style="max-width: 600px; margin-top: 20px;">
                    <div class="form-group">
                        <label for="koltuk_no"><i class="fas fa-chair"></i> Koltuk Numarası *</label>
                        <input type="text" id="koltuk_no" name="koltuk_no" value="<?php echo sanitize($siparis['koltuk_no']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="siparis_notu"><i class="fas fa-comment"></i> Sipariş Notu</label>
                        <textarea id="siparis_notu" name="siparis_notu" rows="3"><?php echo sanitize($siparis['siparis_notu']); ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <button type="submit" name="guncelle" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Değişiklikleri Kaydet
                        </button>
                        <a href="siparislerim.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>


