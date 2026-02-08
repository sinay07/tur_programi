<?php
require_once '../config.php';
requireAdmin();

// Şehirleri çek
$stmt = $db->query("SELECT * FROM sehirler ORDER BY sehir_adi ASC");
$sehirler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şehir Yönetimi - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-city"></i> Şehir Yönetimi</h1>
            <div>
                <span style="margin-right: 15px;">Hoş geldiniz, <strong><?php echo sanitize($_SESSION['admin_name']); ?></strong></span>
                <a href="logout.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Çıkış
                </a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?php echo $_SESSION['mesaj_tip']; ?>">
                <?php 
                echo $_SESSION['mesaj'];
                unset($_SESSION['mesaj']);
                unset($_SESSION['mesaj_tip']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2>Kayıtlı Şehirler</h2>
                    <p style="margin: 0.5rem 0 0; color: #666;">Sistemde kayıtlı olan şehirleri yönetin</p>
                </div>
                <a href="sehir_ekle.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Şehir Ekle
                </a>
            </div>
            <div class="card-body">
                <?php if (count($sehirler) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Şehir Adı</th>
                                    <th>Slug</th>
                                    <th>Açıklama</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sehirler as $sehir): ?>
                                    <tr>
                                        <td><?php echo $sehir['id']; ?></td>
                                        <td><strong><?php echo sanitize($sehir['sehir_adi']); ?></strong></td>
                                        <td><code><?php echo sanitize($sehir['sehir_slug']); ?>.php</code></td>
                                        <td><?php echo mb_substr(sanitize($sehir['aciklama']), 0, 50) . '...'; ?></td>
                                        <td>
                                            <?php if ($sehir['aktif']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="sehir_duzenle.php?id=<?php echo $sehir['id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </a>
                                            <a href="sehir_sil.php?id=<?php echo $sehir['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Bu şehri silmek istediğinizden emin misiniz? İlgili tüm veriler (aktiviteler, restoranlar) silinecektir!');">
                                                <i class="fas fa-trash"></i> Sil
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Henüz şehir eklenmemiş. <a href="sehir_ekle.php">Yeni şehir ekle</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Bilgilendirme</h2>
            </div>
            <div class="card-body">
                <ul style="line-height: 1.8;">
                    <li>Yeni şehir eklediğinizde, otomatik olarak <code>sehir_slug.php</code> dosyası oluşturulacaktır.</li>
                    <li>Her şehir için ayrı aktiviteler ve restoranlar ekleyebilirsiniz.</li>
                    <li>Şehri sildiğinizde, ilgili tüm veriler (aktiviteler, restoranlar, tur programları) silinecektir.</li>
                    <li>Ana sayfadaki şehir kartları veritabanından dinamik olarak çekilir.</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>

