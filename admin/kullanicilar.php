<?php
require_once '../config.php';
requireAdmin();

$basari = '';
$hata = '';

// Kullanıcı ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $ad_soyad = sanitize($_POST['ad_soyad'] ?? '');
    $telefon = preg_replace('/[^0-9]/', '', $_POST['telefon'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($ad_soyad) || empty($telefon)) {
        $hata = 'Ad Soyad ve Telefon alanları zorunludur.';
    } else {
        // Telefon numarası kontrolü
        $stmt = $db->prepare("SELECT id FROM kullanicilar WHERE telefon = ?");
        $stmt->execute([$telefon]);
        if ($stmt->fetch()) {
            $hata = 'Bu telefon numarası zaten kayıtlı.';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO kullanicilar (ad_soyad, telefon, email) VALUES (?, ?, ?)");
                $stmt->execute([$ad_soyad, $telefon, $email]);
                $basari = 'Kullanıcı başarıyla eklendi.';
            } catch (PDOException $e) {
                $hata = 'Kullanıcı eklenirken hata oluştu: ' . $e->getMessage();
            }
        }
    }
}

// Kullanıcı silme
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("UPDATE kullanicilar SET aktif = 0 WHERE id = ?");
        $stmt->execute([$id]);
        $basari = 'Kullanıcı başarıyla silindi.';
    } catch (PDOException $e) {
        $hata = 'Kullanıcı silinirken hata oluştu.';
    }
}

// Kullanıcı aktifleştirme
if (isset($_GET['activate'])) {
    $id = (int)$_GET['activate'];
    try {
        $stmt = $db->prepare("UPDATE kullanicilar SET aktif = 1 WHERE id = ?");
        $stmt->execute([$id]);
        $basari = 'Kullanıcı başarıyla aktifleştirildi.';
    } catch (PDOException $e) {
        $hata = 'Kullanıcı aktifleştirilirken hata oluştu.';
    }
}

// Kullanıcıları getir
$stmt = $db->query("SELECT * FROM kullanicilar ORDER BY id DESC");
$kullanicilar = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-users"></i> Kullanıcı Yönetimi</h1>
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
        
        <!-- Yeni Kullanıcı Ekleme Formu -->
        <div class="admin-card">
            <h2><i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle</h2>
            <form method="POST" style="max-width: 600px;">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="ad_soyad">
                        <i class="fas fa-user"></i> Ad Soyad *
                    </label>
                    <input type="text" id="ad_soyad" name="ad_soyad" required>
                </div>
                
                <div class="form-group">
                    <label for="telefon">
                        <i class="fas fa-phone"></i> Telefon *
                    </label>
                    <input type="tel" id="telefon" name="telefon" placeholder="05XXXXXXXXX" maxlength="11" required>
                    <small>Telefon numarası kullanıcının giriş yapması için kullanılacaktır.</small>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> E-posta
                    </label>
                    <input type="email" id="email" name="email">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kullanıcı Ekle
                </button>
            </form>
        </div>
        
        <!-- Kullanıcı Listesi -->
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Kullanıcı Listesi</h2>
            
            <?php if (count($kullanicilar) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>Telefon</th>
                                <th>E-posta</th>
                                <th>Kayıt Tarihi</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kullanicilar as $kullanici): ?>
                                <tr>
                                    <td><?php echo $kullanici['id']; ?></td>
                                    <td><strong><?php echo sanitize($kullanici['ad_soyad']); ?></strong></td>
                                    <td><?php echo sanitize($kullanici['telefon']); ?></td>
                                    <td><?php echo sanitize($kullanici['email']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($kullanici['kayit_tarihi'])); ?></td>
                                    <td>
                                        <?php if ($kullanici['aktif']): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="kullanici_duzenle.php?id=<?php echo $kullanici['id']; ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($kullanici['aktif']): ?>
                                                <a href="?delete=<?php echo $kullanici['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Bu kullanıcıyı pasif yapmak istediğinize emin misiniz?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?activate=<?php echo $kullanici['id']; ?>" 
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
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
                    Henüz kullanıcı bulunmamaktadır.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

