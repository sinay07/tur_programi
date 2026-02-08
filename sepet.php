<?php
require_once 'config.php';
requireLogin();

// Sepet başlat
if (!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = [];
}

// POST veya GET'ten parametreleri al
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$urun_id = isset($_POST['urun_id']) ? (int)$_POST['urun_id'] : (isset($_GET['urun_id']) ? (int)$_GET['urun_id'] : 0);
$restoran_id = isset($_POST['restoran_id']) ? (int)$_POST['restoran_id'] : (isset($_GET['restoran_id']) ? (int)$_GET['restoran_id'] : 0);

// Sepete ürün ekle
if (($action === 'ekle' || $action === 'add') && $urun_id > 0) {
    // Ürün bilgilerini al
    $stmt = $db->prepare("
        SELECT u.*, k.kategori_adi, k.restoran_id, r.baslik as restoran_adi
        FROM menu_urunler u
        INNER JOIN menu_kategoriler k ON u.kategori_id = k.id
        INNER JOIN restoranlar r ON k.restoran_id = r.id
        WHERE u.id = ? AND u.aktif = 1
    ");
    $stmt->execute([$urun_id]);
    $urun = $stmt->fetch();
    
    if ($urun) {
        // Sepette bu ürün var mı kontrol et
        $bulundu = false;
        foreach ($_SESSION['sepet'] as &$item) {
            if ($item['urun_id'] == $urun_id) {
                $item['adet']++;
                $bulundu = true;
                break;
            }
        }
        
        // Yoksa yeni ekle
        if (!$bulundu) {
            $_SESSION['sepet'][] = [
                'urun_id' => $urun['id'],
                'urun_adi' => $urun['urun_adi'],
                'kategori_adi' => $urun['kategori_adi'],
                'fiyat' => $urun['fiyat'],
                'restoran_id' => $urun['restoran_id'],
                'restoran_adi' => $urun['restoran_adi'],
                'adet' => 1
            ];
        }
        
        // Her durumda JSON döndür (AJAX veya normal istek)
        echo json_encode(['success' => true, 'message' => 'Ürün sepete eklendi']);
        exit;
    }
}

// Sepet bilgilerini döndür (AJAX için)
if ($action === 'bilgi') {
    $toplam_adet = 0;
    if (isset($_SESSION['sepet']) && is_array($_SESSION['sepet'])) {
        foreach ($_SESSION['sepet'] as $item) {
            $toplam_adet += $item['adet'];
        }
    }
    echo json_encode(['success' => true, 'adet' => $toplam_adet]);
    exit;
}

// Sepetten ürün çıkar
if ($action === 'cikar' && $urun_id > 0) {
    foreach ($_SESSION['sepet'] as $key => $item) {
        if ($item['urun_id'] == $urun_id) {
            unset($_SESSION['sepet'][$key]);
            $_SESSION['sepet'] = array_values($_SESSION['sepet']); // Index'leri yenile
            break;
        }
    }
    echo json_encode(['success' => true, 'message' => 'Ürün sepetten çıkarıldı']);
    exit;
}

// Ürün adedi artır/azalt
if ($action === 'guncelle' && $urun_id > 0) {
    $adet = isset($_GET['adet']) ? (int)$_GET['adet'] : 1;
    if ($adet < 1) $adet = 1;
    
    foreach ($_SESSION['sepet'] as &$item) {
        if ($item['urun_id'] == $urun_id) {
            $item['adet'] = $adet;
            break;
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

// Sepeti temizle
if ($action === 'temizle') {
    $_SESSION['sepet'] = [];
    echo json_encode(['success' => true, 'message' => 'Sepet temizlendi']);
    exit;
}

// Menü ürünlerini getir (AJAX için)
if ($action === 'menu') {
    $kategori_id = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;
    
    if ($kategori_id > 0) {
        $stmt = $db->prepare("
            SELECT u.*, k.kategori_adi, k.restoran_id
            FROM menu_urunler u
            INNER JOIN menu_kategoriler k ON u.kategori_id = k.id
            WHERE u.kategori_id = ? AND u.aktif = 1
            ORDER BY u.sira ASC, u.urun_adi ASC
        ");
        $stmt->execute([$kategori_id]);
        $urunler = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'urunler' => $urunler
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Kategori ID gerekli',
            'urunler' => []
        ]);
    }
    exit;
}

// Sepet bilgilerini getir
if ($action === 'bilgi') {
    $toplam = 0;
    $adet = 0;
    foreach ($_SESSION['sepet'] as $item) {
        $toplam += $item['fiyat'] * $item['adet'];
        $adet += $item['adet'];
    }
    
    echo json_encode([
        'success' => true,
        'adet' => $adet,
        'toplam' => $toplam,
        'urunler' => $_SESSION['sepet']
    ]);
    exit;
}

// Varsayılan: Sepet sayfası
$kullanici = getKullanici($db, $_SESSION['kullanici_id']);
$sepet = $_SESSION['sepet'];
$sepetToplam = 0;

foreach ($sepet as $item) {
    $sepetToplam += $item['fiyat'] * $item['adet'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <?php if (isset($_SESSION['sepet_mesaj'])): ?>
        <div class="alert alert-success" style="margin: 20px auto; max-width: 1200px;">
            <i class="fas fa-check-circle"></i>
            <?php 
            echo $_SESSION['sepet_mesaj']; 
            unset($_SESSION['sepet_mesaj']);
            ?>
        </div>
    <?php endif; ?>
    
    <div class="container" style="padding: 40px 20px; min-height: 80vh;">
        <h1><i class="fas fa-shopping-cart"></i> Sepetim</h1>
        
        <?php if (count($sepet) > 0): ?>
            <div style="display: grid; gap: 30px; margin-top: 30px;">
                <!-- Sepet Ürünleri -->
                <div class="admin-card">
                    <h2><i class="fas fa-list"></i> Sepetinizdeki Ürünler</h2>
                    
                    <?php
                    $restoranlar = [];
                    foreach ($sepet as $item) {
                        $restoranlar[$item['restoran_id']] = $item['restoran_adi'];
                    }
                    ?>
                    
                    <?php foreach ($restoranlar as $rest_id => $rest_adi): ?>
                        <div style="margin-bottom: 30px;">
                            <h3 style="color: var(--primary-color); margin-bottom: 15px;">
                                <i class="fas fa-utensils"></i> <?php echo sanitize($rest_adi); ?>
                            </h3>
                            
                            <table style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Ürün</th>
                                        <th>Kategori</th>
                                        <th>Adet</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sepet as $item): ?>
                                        <?php if ($item['restoran_id'] == $rest_id): ?>
                                            <tr>
                                                <td><strong><?php echo sanitize($item['urun_adi']); ?></strong></td>
                                                <td><?php echo sanitize($item['kategori_adi']); ?></td>
                                                <td>
                                                    <input type="number" 
                                                           value="<?php echo $item['adet']; ?>" 
                                                           min="1" 
                                                           style="width: 70px; padding: 5px;"
                                                           onchange="adetGuncelle(<?php echo $item['urun_id']; ?>, this.value)">
                                                </td>
                                                <td>
                                                    <button onclick="urunCikar(<?php echo $item['urun_id']; ?>)" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                    
                </div>
                
                <!-- Sipariş Formu -->
                <div class="admin-card">
                    <h2><i class="fas fa-clipboard-check"></i> Sipariş Bilgileri</h2>
                    
                    <form method="POST" action="siparis_ver.php" style="max-width: 600px;">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Ad Soyad</label>
                            <input type="text" value="<?php echo sanitize($kullanici['ad_soyad']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Telefon</label>
                            <input type="text" value="<?php echo sanitize($kullanici['telefon']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="koltuk_no"><i class="fas fa-chair"></i> Koltuk Numarası *</label>
                            <input type="text" id="koltuk_no" name="koltuk_no" required placeholder="Örn: 14">
                        </div>
                        
                        <div class="form-group">
                            <label for="siparis_notu"><i class="fas fa-comment"></i> Sipariş Notu (İsteğe bağlı)</label>
                            <textarea id="siparis_notu" name="siparis_notu" rows="3" placeholder="Özel istekleriniz varsa belirtiniz..."></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 15px;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check-circle"></i> Siparişi Tamamla
                            </button>
                            <button type="button" onclick="sepetiTemizle()" class="btn btn-danger btn-lg">
                                <i class="fas fa-trash"></i> Sepeti Temizle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card" style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--text-light); margin-bottom: 20px;"></i>
                <h2>Sepetiniz Boş</h2>
                <p style="color: var(--text-light); margin: 20px 0;">Sipariş vermek için menüden ürün ekleyin.</p>
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Geri Dön
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    function adetGuncelle(urunId, adet) {
        fetch(`sepet.php?action=guncelle&urun_id=${urunId}&adet=${adet}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }
    
    function urunCikar(urunId) {
        if (confirm('Bu ürünü sepetten çıkarmak istediğinize emin misiniz?')) {
            fetch(`sepet.php?action=cikar&urun_id=${urunId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }
    }
    
    function sepetiTemizle() {
        if (confirm('Sepetteki tüm ürünleri silmek istediğinize emin misiniz?')) {
            fetch('sepet.php?action=temizle')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }
    }
    </script>
</body>
</html>


