<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';

// Restoran ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $sehir_id = (int)$_POST['sehir_id'];
    $baslik = sanitize($_POST['baslik']);
    $aciklama = sanitize($_POST['aciklama']);
    $adres = sanitize($_POST['adres']);
    $mutfak_turu = sanitize($_POST['mutfak_turu']);
    $ortalama_fiyat = sanitize($_POST['ortalama_fiyat']);
    $telefon = sanitize($_POST['telefon']);
    
    if (empty($sehir_id) || empty($baslik)) {
        $hata = 'Şehir ve Başlık alanları zorunludur.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO restoranlar (sehir_id, baslik, aciklama, adres, mutfak_turu, ortalama_fiyat, telefon) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sehir_id, $baslik, $aciklama, $adres, $mutfak_turu, $ortalama_fiyat, $telefon]);
            $basari = 'Restoran başarıyla eklendi.';
        } catch (PDOException $e) {
            $hata = 'Restoran eklenirken hata oluştu.';
        }
    }
}

// Restoran silme
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM restoranlar WHERE id = ?");
        $stmt->execute([$id]);
        $basari = 'Restoran başarıyla silindi.';
    } catch (PDOException $e) {
        $hata = 'Restoran silinirken hata oluştu.';
    }
}

// Şehirleri getir
$sehirler = $db->query("SELECT * FROM sehirler WHERE aktif = 1 ORDER BY sehir_adi")->fetchAll();

// Restoranları getir
$stmt = $db->query("
    SELECT r.*, s.sehir_adi 
    FROM restoranlar r
    INNER JOIN sehirler s ON r.sehir_id = s.id
    ORDER BY s.sehir_adi, r.sira, r.baslik
");
$restoranlar = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restoran Yönetimi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-utensils"></i> Restoran Yönetimi</h1>
            <a href="index.php" class="btn btn-sm btn-secondary">
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
        
        <!-- Yeni Restoran Ekleme Formu -->
        <div class="admin-card">
            <h2><i class="fas fa-plus-circle"></i> Yeni Restoran Ekle</h2>
            <form method="POST" style="max-width: 800px;">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="sehir_id">
                        <i class="fas fa-map-marker-alt"></i> Şehir *
                    </label>
                    <select id="sehir_id" name="sehir_id" required>
                        <option value="">Şehir Seçin</option>
                        <?php foreach ($sehirler as $sehir): ?>
                            <option value="<?php echo $sehir['id']; ?>">
                                <?php echo sanitize($sehir['sehir_adi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="baslik">
                        <i class="fas fa-heading"></i> Restoran Adı *
                    </label>
                    <input type="text" id="baslik" name="baslik" required>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">
                        <i class="fas fa-align-left"></i> Açıklama
                    </label>
                    <textarea id="aciklama" name="aciklama" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="adres">
                        <i class="fas fa-map-marker-alt"></i> Adres
                    </label>
                    <input type="text" id="adres" name="adres">
                </div>
                
                <div class="form-group">
                    <label for="mutfak_turu">
                        <i class="fas fa-concierge-bell"></i> Mutfak Türü
                    </label>
                    <input type="text" id="mutfak_turu" name="mutfak_turu" placeholder="Örn: Türk Mutfağı, İtalyan Mutfağı">
                </div>
                
                <div class="form-group">
                    <label for="ortalama_fiyat">
                        <i class="fas fa-wallet"></i> Ortalama Fiyat
                    </label>
                    <input type="text" id="ortalama_fiyat" name="ortalama_fiyat" placeholder="Örn: 200-400 TL">
                </div>
                
                <div class="form-group">
                    <label for="telefon">
                        <i class="fas fa-phone"></i> Telefon
                    </label>
                    <input type="tel" id="telefon" name="telefon" placeholder="0XXX XXX XX XX">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Restoran Ekle
                </button>
            </form>
        </div>
        
        <!-- Restoran Listesi -->
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Restoran Listesi</h2>
            
            <?php if (count($restoranlar) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Şehir</th>
                                <th>Restoran Adı</th>
                                <th>Mutfak Türü</th>
                                <th>Ortalama Fiyat</th>
                                <th>Telefon</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($restoranlar as $restoran): ?>
                                <tr>
                                    <td><?php echo $restoran['id']; ?></td>
                                    <td>
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                        <?php echo sanitize($restoran['sehir_adi']); ?>
                                    </td>
                                    <td><strong><?php echo sanitize($restoran['baslik']); ?></strong></td>
                                    <td><?php echo sanitize($restoran['mutfak_turu']); ?></td>
                                    <td><?php echo sanitize($restoran['ortalama_fiyat']); ?></td>
                                    <td><?php echo sanitize($restoran['telefon']); ?></td>
                                    <td>
                                        <?php if ($restoran['aktif']): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="restoran_duzenle.php?id=<?php echo $restoran['id']; ?>" 
                                               class="btn btn-sm btn-primary"
                                               title="Restoran ve Menüyü Düzenle">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </a>
                                            <a href="?delete=<?php echo $restoran['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Bu restoranı silmek istediğinize emin misiniz?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Henüz restoran bulunmamaktadır.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

