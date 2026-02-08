<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';

// Tur ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $sehir_id = (int)$_POST['sehir_id'];
    $tarih = sanitize($_POST['tarih']);
    $aciklama = sanitize($_POST['aciklama'] ?? '');
    
    if (empty($sehir_id) || empty($tarih)) {
        $hata = 'Şehir ve Tarih alanları zorunludur.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO takvim (sehir_id, tarih, aciklama) VALUES (?, ?, ?)");
            $stmt->execute([$sehir_id, $tarih, $aciklama]);
            $basari = 'Tur programı başarıyla eklendi.';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $hata = 'Bu tarihte zaten bu şehir için bir tur programı var.';
            } else {
                $hata = 'Tur programı eklenirken hata oluştu.';
            }
        }
    }
}

// Tur silme
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM takvim WHERE id = ?");
        $stmt->execute([$id]);
        $basari = 'Tur programı başarıyla silindi.';
    } catch (PDOException $e) {
        $hata = 'Tur programı silinirken hata oluştu.';
    }
}

// Şehirleri getir
$sehirler = $db->query("SELECT * FROM sehirler WHERE aktif = 1 ORDER BY sehir_adi")->fetchAll();

// Tur programlarını getir
$stmt = $db->query("
    SELECT t.*, s.sehir_adi 
    FROM takvim t
    INNER JOIN sehirler s ON t.sehir_id = s.id
    ORDER BY t.tarih DESC
");
$turlar = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takvim Yönetimi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-calendar-alt"></i> Takvim Yönetimi</h1>
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
        
        <!-- Yeni Tur Ekleme Formu -->
        <div class="admin-card">
            <h2><i class="fas fa-plus-circle"></i> Yeni Tur Programı Ekle</h2>
            <form method="POST" style="max-width: 600px;">
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
                    <label for="tarih">
                        <i class="fas fa-calendar"></i> Tarih *
                    </label>
                    <input type="date" id="tarih" name="tarih" required>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">
                        <i class="fas fa-comment"></i> Açıklama
                    </label>
                    <textarea id="aciklama" name="aciklama" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Tur Programı Ekle
                </button>
            </form>
        </div>
        
        <!-- Tur Programları Listesi -->
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Tur Programları</h2>
            
            <?php if (count($turlar) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tarih</th>
                                <th>Şehir</th>
                                <th>Açıklama</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turlar as $tur): ?>
                                <tr>
                                    <td><?php echo $tur['id']; ?></td>
                                    <td>
                                        <strong><?php echo date('d.m.Y', strtotime($tur['tarih'])); ?></strong>
                                        <br>
                                        <small><?php echo getTurkceGunAdi($tur['tarih']); ?></small>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                        <?php echo sanitize($tur['sehir_adi']); ?>
                                    </td>
                                    <td><?php echo sanitize($tur['aciklama']); ?></td>
                                    <td>
                                        <?php 
                                        $bugun = date('Y-m-d');
                                        $turTarih = $tur['tarih'];
                                        if ($turTarih == $bugun) {
                                            echo '<span class="badge badge-info">Bugün</span>';
                                        } elseif ($turTarih > $bugun) {
                                            echo '<span class="badge badge-success">Yaklaşan</span>';
                                        } else {
                                            echo '<span class="badge">Geçmiş</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?delete=<?php echo $tur['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Bu tur programını silmek istediğinize emin misiniz?')">
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
                    Henüz tur programı bulunmamaktadır.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

