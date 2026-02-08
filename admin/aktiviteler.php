<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';

// Aktivite ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $sehir_id = (int)$_POST['sehir_id'];
    $baslik = sanitize($_POST['baslik']);
    $aciklama = sanitize($_POST['aciklama']);
    $adres = sanitize($_POST['adres']);
    $fiyat = (float)$_POST['fiyat'];
    $sure = sanitize($_POST['sure']);
    
    if (empty($sehir_id) || empty($baslik)) {
        $hata = 'Şehir ve Başlık alanları zorunludur.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO aktiviteler (sehir_id, baslik, aciklama, adres, fiyat, sure) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sehir_id, $baslik, $aciklama, $adres, $fiyat, $sure]);
            $basari = 'Aktivite başarıyla eklendi.';
        } catch (PDOException $e) {
            $hata = 'Aktivite eklenirken hata oluştu.';
        }
    }
}

// Aktivite silme
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM aktiviteler WHERE id = ?");
        $stmt->execute([$id]);
        $basari = 'Aktivite başarıyla silindi.';
    } catch (PDOException $e) {
        $hata = 'Aktivite silinirken hata oluştu.';
    }
}

// Şehirleri getir
$sehirler = $db->query("SELECT * FROM sehirler WHERE aktif = 1 ORDER BY sehir_adi")->fetchAll();

// Aktiviteleri getir
$stmt = $db->query("
    SELECT a.*, s.sehir_adi 
    FROM aktiviteler a
    INNER JOIN sehirler s ON a.sehir_id = s.id
    ORDER BY s.sehir_adi, a.sira, a.baslik
");
$aktiviteler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivite Yönetimi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-hiking"></i> Aktivite Yönetimi</h1>
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
        
        <!-- Yeni Aktivite Ekleme Formu -->
        <div class="admin-card">
            <h2><i class="fas fa-plus-circle"></i> Yeni Aktivite Ekle</h2>
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
                        <i class="fas fa-heading"></i> Başlık *
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
                    <label for="fiyat">
                        <i class="fas fa-tag"></i> Fiyat (TL)
                    </label>
                    <input type="number" id="fiyat" name="fiyat" step="0.01" min="0" value="0">
                    <small>0 girerseniz "Ücretsiz" olarak gösterilecektir.</small>
                </div>
                
                <div class="form-group">
                    <label for="sure">
                        <i class="fas fa-clock"></i> Süre
                    </label>
                    <input type="text" id="sure" name="sure" placeholder="Örn: 2-3 saat">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Aktivite Ekle
                </button>
            </form>
        </div>
        
        <!-- Aktivite Listesi -->
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Aktivite Listesi</h2>
            
            <?php if (count($aktiviteler) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Şehir</th>
                                <th>Başlık</th>
                                <th>Fiyat</th>
                                <th>Süre</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aktiviteler as $aktivite): ?>
                                <tr>
                                    <td><?php echo $aktivite['id']; ?></td>
                                    <td>
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                        <?php echo sanitize($aktivite['sehir_adi']); ?>
                                    </td>
                                    <td><strong><?php echo sanitize($aktivite['baslik']); ?></strong></td>
                                    <td>
                                        <?php if ($aktivite['fiyat'] > 0): ?>
                                            <?php echo number_format($aktivite['fiyat'], 2); ?> TL
                                        <?php else: ?>
                                            <span class="badge badge-success">Ücretsiz</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo sanitize($aktivite['sure']); ?></td>
                                    <td>
                                        <?php if ($aktivite['aktif']): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?delete=<?php echo $aktivite['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Bu aktiviteyi silmek istediğinize emin misiniz?')">
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
                    Henüz aktivite bulunmamaktadır.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

