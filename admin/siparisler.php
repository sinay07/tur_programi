<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';

// Sipariş durumu güncelleme
if (isset($_GET['durum_guncelle']) && isset($_GET['id'])) {
    $siparis_id = (int)$_GET['id'];
    $yeni_durum = $_GET['durum_guncelle'];
    
    $izin_verilen_durumlar = ['beklemede', 'hazirlaniyor', 'teslim_edildi', 'iptal'];
    if (in_array($yeni_durum, $izin_verilen_durumlar)) {
        $stmt = $db->prepare("UPDATE siparisler SET durum = ? WHERE id = ?");
        $stmt->execute([$yeni_durum, $siparis_id]);
        $basari = 'Sipariş durumu güncellendi.';
    }
}

// Tek sipariş silme
if (isset($_GET['sil']) && isset($_GET['id'])) {
    $siparis_id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("DELETE FROM siparisler WHERE id = ?");
        $stmt->execute([$siparis_id]);
        $basari = 'Sipariş silindi.';
    } catch (PDOException $e) {
        $hata = 'Sipariş silinirken hata oluştu.';
    }
}

// Toplu sipariş silme
if (isset($_POST['toplu_sil']) && isset($_POST['secili_siparisler'])) {
    $secili_ids = $_POST['secili_siparisler'];
    if (is_array($secili_ids) && count($secili_ids) > 0) {
        try {
            $placeholders = str_repeat('?,', count($secili_ids) - 1) . '?';
            $stmt = $db->prepare("DELETE FROM siparisler WHERE id IN ($placeholders)");
            $stmt->execute($secili_ids);
            $basari = count($secili_ids) . ' sipariş silindi.';
        } catch (PDOException $e) {
            $hata = 'Siparişler silinirken hata oluştu.';
        }
    }
}

// Tüm eski siparişleri silme
if (isset($_GET['tumu_sil']) && isset($_GET['tarih'])) {
    $tarih = $_GET['tarih'];
    try {
        $stmt = $db->prepare("DELETE FROM siparisler WHERE DATE(siparis_tarihi) = ? AND durum IN ('teslim_edildi', 'iptal')");
        $stmt->execute([$tarih]);
        $basari = 'Tüm eski siparişler silindi.';
    } catch (PDOException $e) {
        $hata = 'Siparişler silinirken hata oluştu.';
    }
}

// Siparişleri getir
$sekme = $_GET['sekme'] ?? 'aktif';
$tarih_filtre = $_GET['tarih'] ?? date('Y-m-d');

if ($sekme == 'aktif') {
    // Aktif siparişler (beklemede ve hazırlanıyor)
    $sql = "
        SELECT s.*, k.ad_soyad, k.telefon, r.baslik as restoran_adi
        FROM siparisler s
        INNER JOIN kullanicilar k ON s.kullanici_id = k.id
        INNER JOIN restoranlar r ON s.restoran_id = r.id
        WHERE DATE(s.siparis_tarihi) = ?
        AND s.durum IN ('beklemede', 'hazirlaniyor')
        ORDER BY s.siparis_tarihi DESC
    ";
} else {
    // Eski siparişler (teslim edildi ve iptal)
    $sql = "
        SELECT s.*, k.ad_soyad, k.telefon, r.baslik as restoran_adi
        FROM siparisler s
        INNER JOIN kullanicilar k ON s.kullanici_id = k.id
        INNER JOIN restoranlar r ON s.restoran_id = r.id
        WHERE DATE(s.siparis_tarihi) = ?
        AND s.durum IN ('teslim_edildi', 'iptal')
        ORDER BY s.siparis_tarihi DESC
    ";
}

$stmt = $db->prepare($sql);
$stmt->execute([$tarih_filtre]);
$siparisler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Yönetimi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-clipboard-list"></i> Sipariş Yönetimi</h1>
            <div>
                <button onclick="exportSecili('pdf')" class="btn btn-sm btn-danger" id="exportPdfBtn" style="display: none;">
                    <i class="fas fa-file-pdf"></i> Seçilenleri PDF İndir
                </button>
            </div>
        </div>
        
        <!-- Sekmeler -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color);">
            <a href="?sekme=aktif&tarih=<?php echo $tarih_filtre; ?>" 
               style="padding: 15px 30px; text-decoration: none; color: <?php echo $sekme == 'aktif' ? 'var(--primary-color)' : 'var(--text-light)'; ?>; border-bottom: 3px solid <?php echo $sekme == 'aktif' ? 'var(--primary-color)' : 'transparent'; ?>; font-weight: <?php echo $sekme == 'aktif' ? 'bold' : 'normal'; ?>; transition: all 0.3s;">
                <i class="fas fa-clock"></i> Aktif Siparişler
                <?php
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM siparisler WHERE DATE(siparis_tarihi) = ? AND durum IN ('beklemede', 'hazirlaniyor')");
                $stmt->execute([$tarih_filtre]);
                $aktif_count = $stmt->fetch()['total'];
                if ($aktif_count > 0):
                ?>
                    <span style="background: var(--primary-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.85rem; margin-left: 5px;"><?php echo $aktif_count; ?></span>
                <?php endif; ?>
            </a>
            
            <a href="?sekme=eski&tarih=<?php echo $tarih_filtre; ?>" 
               style="padding: 15px 30px; text-decoration: none; color: <?php echo $sekme == 'eski' ? 'var(--primary-color)' : 'var(--text-light)'; ?>; border-bottom: 3px solid <?php echo $sekme == 'eski' ? 'var(--primary-color)' : 'transparent'; ?>; font-weight: <?php echo $sekme == 'eski' ? 'bold' : 'normal'; ?>; transition: all 0.3s;">
                <i class="fas fa-history"></i> Eski Siparişler
                <?php
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM siparisler WHERE DATE(siparis_tarihi) = ? AND durum IN ('teslim_edildi', 'iptal')");
                $stmt->execute([$tarih_filtre]);
                $eski_count = $stmt->fetch()['total'];
                if ($eski_count > 0):
                ?>
                    <span style="background: var(--text-light); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.85rem; margin-left: 5px;"><?php echo $eski_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <?php if ($basari): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $basari; ?>
            </div>
        <?php endif; ?>
        
        <!-- Tarih Filtresi -->
        <div class="admin-card">
            <h2><i class="fas fa-calendar"></i> Tarih Seçimi</h2>
            <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                <input type="hidden" name="sekme" value="<?php echo $sekme; ?>">
                <div class="form-group" style="margin: 0; flex: 1; max-width: 300px;">
                    <label for="tarih"><i class="fas fa-calendar-alt"></i> Tarih</label>
                    <input type="date" id="tarih" name="tarih" value="<?php echo $tarih_filtre; ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Göster
                </button>
            </form>
        </div>
        
        <!-- Siparişler -->
        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">
                    <i class="fas fa-list"></i> Siparişler (<span id="toplamSiparis"><?php echo count($siparisler); ?></span> adet)
                    <span id="seciliSayac" style="color: var(--primary-color); display: none;"> - <span id="seciliSayi">0</span> seçili</span>
                </h2>
                
                <div style="display: flex; gap: 10px;">
                    <?php if ($sekme == 'eski' && count($siparisler) > 0): ?>
                        <button onclick="if(confirm('Tüm eski siparişleri silmek istediğinize emin misiniz?')) { window.location.href='?tumu_sil=1&tarih=<?php echo $tarih_filtre; ?>&sekme=eski'; }" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash-alt"></i> Tümünü Temizle
                        </button>
                    <?php endif; ?>
                    
                    <button onclick="tumunuSec()" class="btn btn-sm btn-secondary" id="tumunuSecBtn">
                        <i class="fas fa-check-square"></i> Tümünü Seç
                    </button>
                    
                    <button onclick="topluSil()" class="btn btn-sm btn-danger" id="topluSilBtn" style="display: none;">
                        <i class="fas fa-trash"></i> Seçilileri Sil
                    </button>
                </div>
            </div>
            
            <?php if (count($siparisler) > 0): ?>
                <form id="siparisForm" method="POST">
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
                    ?>
                    
                    <div style="background: #f7fafc; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid var(--primary-color); position: relative;">
                        <!-- Checkbox Seçimi -->
                        <div style="position: absolute; top: 15px; right: 15px;">
                            <input type="checkbox" 
                                   name="secili_siparisler[]" 
                                   value="<?php echo $siparis['id']; ?>" 
                                   class="siparis-checkbox"
                                   onchange="checkboxDegisti()"
                                   style="width: 20px; height: 20px; cursor: pointer;">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; padding-right: 40px;">
                            <div>
                                <strong>🧍‍♂️ Yolcu:</strong><br>
                                <?php echo sanitize($siparis['ad_soyad']); ?>
                            </div>
                            <div>
                                <strong>📞 Telefon:</strong><br>
                                <?php echo sanitize($siparis['telefon']); ?>
                            </div>
                            <div>
                                <strong>🍽️ Restoran:</strong><br>
                                <?php echo sanitize($siparis['restoran_adi']); ?>
                            </div>
                            <div>
                                <strong>💺 Koltuk No:</strong><br>
                                <span class="badge badge-info" style="font-size: 1rem;"><?php echo sanitize($siparis['koltuk_no']); ?></span>
                            </div>
                            <div>
                                <strong>🕐 Sipariş Zamanı:</strong><br>
                                <?php echo date('H:i', strtotime($siparis['siparis_tarihi'])); ?>
                            </div>
                            <div>
                                <strong>💰 Toplam:</strong><br>
                                <span style="color: var(--primary-color); font-size: 1.1rem; font-weight: bold;">
                                    <?php echo number_format($siparis['toplam_fiyat'], 2); ?> TL
                                </span>
                            </div>
                        </div>
                        
                        <!-- Ürünler Tablosu -->
                        <table style="width: 100%; background: white; border-radius: 8px; margin-bottom: 15px;">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Kategori</th>
                                    <th>Adet</th>
                                    <th>Fiyat</th>
                                    <th>Toplam</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($urunler as $urun): ?>
                                    <tr>
                                        <td><strong><?php echo sanitize($urun['urun_adi']); ?></strong></td>
                                        <td><?php echo sanitize($urun['kategori_adi']); ?></td>
                                        <td><?php echo $urun['adet']; ?>x</td>
                                        <td><?php echo number_format($urun['birim_fiyat'], 2); ?> TL</td>
                                        <td><strong><?php echo number_format($urun['toplam_fiyat'], 2); ?> TL</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- Durum ve İşlemler -->
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <div>
                                <strong>Durum:</strong>
                                <span class="badge badge-<?php echo $renk; ?>" style="font-size: 1rem; padding: 8px 15px;">
                                    <?php
                                    $durum_metinler = [
                                        'beklemede' => '⏳ Beklemede',
                                        'hazirlaniyor' => '👨‍🍳 Hazırlanıyor',
                                        'yolda' => '🚗 Yolda',
                                        'teslim_edildi' => '✅ Teslim Edildi',
                                        'iptal' => '❌ İptal Edildi'
                                    ];
                                    echo $durum_metinler[$siparis['durum']];
                                    ?>
                                </span>
                            </div>
                            
                            <div class="action-buttons">
                                <?php if ($sekme == 'aktif'): ?>
                                    <a href="?durum_guncelle=teslim_edildi&id=<?php echo $siparis['id']; ?>&tarih=<?php echo $tarih_filtre; ?>&sekme=<?php echo $sekme; ?>" 
                                       class="btn btn-sm btn-success">
                                        👨‍🍳 Hazırlandı
                                    </a>
                                    
                                    <a href="?durum_guncelle=iptal&id=<?php echo $siparis['id']; ?>&tarih=<?php echo $tarih_filtre; ?>&sekme=<?php echo $sekme; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bu siparişi iptal etmek istediğinize emin misiniz?')">
                                        ❌ İptal Et
                                    </a>
                                <?php else: ?>
                                    <a href="?sil=1&id=<?php echo $siparis['id']; ?>&tarih=<?php echo $tarih_filtre; ?>&sekme=<?php echo $sekme; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bu siparişi kalıcı olarak silmek istediğinize emin misiniz?')">
                                        <i class="fas fa-trash"></i> Sil
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($siparis['siparis_notu'])): ?>
                            <div style="margin-top: 15px; padding: 10px; background: white; border-radius: 8px; border-left: 3px solid var(--warning-color);">
                                <strong><i class="fas fa-comment"></i> Not:</strong>
                                <?php echo nl2br(sanitize($siparis['siparis_notu'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </form>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Bu tarih için sipariş bulunmamaktadır.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function checkboxDegisti() {
        const checkboxes = document.querySelectorAll('.siparis-checkbox:checked');
        const seciliSayi = checkboxes.length;
        const toplamSayi = document.querySelectorAll('.siparis-checkbox').length;
        
        // Seçili sayacını güncelle
        document.getElementById('seciliSayi').textContent = seciliSayi;
        document.getElementById('seciliSayac').style.display = seciliSayi > 0 ? 'inline' : 'none';
        
        // Butonları göster/gizle
        document.getElementById('topluSilBtn').style.display = seciliSayi > 0 ? 'inline-block' : 'none';
        document.getElementById('exportPdfBtn').style.display = seciliSayi > 0 ? 'inline-block' : 'none';
        
        // Tümünü seç butonunu güncelle
        const tumunuSecBtn = document.getElementById('tumunuSecBtn');
        if (seciliSayi == toplamSayi && toplamSayi > 0) {
            tumunuSecBtn.innerHTML = '<i class="fas fa-square"></i> Seçimi Temizle';
        } else {
            tumunuSecBtn.innerHTML = '<i class="fas fa-check-square"></i> Tümünü Seç';
        }
    }
    
    function tumunuSec() {
        const checkboxes = document.querySelectorAll('.siparis-checkbox');
        const checkedCount = document.querySelectorAll('.siparis-checkbox:checked').length;
        const allChecked = checkedCount === checkboxes.length;
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        checkboxDegisti();
    }
    
    function topluSil() {
        const checkboxes = document.querySelectorAll('.siparis-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Lütfen silmek istediğiniz siparişleri seçin.');
            return;
        }
        
        if (confirm(checkboxes.length + ' siparişi silmek istediğinize emin misiniz?')) {
            const form = document.getElementById('siparisForm');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'toplu_sil';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    }
    
    function exportSecili(format) {
        const checkboxes = document.querySelectorAll('.siparis-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Lütfen export etmek istediğiniz siparişleri seçin.');
            return;
        }
        
        const ids = Array.from(checkboxes).map(cb => cb.value).join(',');
        window.location.href = `siparis_export.php?format=${format}&ids=${ids}&tarih=<?php echo $tarih_filtre; ?>&sekme=<?php echo $sekme; ?>`;
    }
    </script>
</body>
</html>


