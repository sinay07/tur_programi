<?php
require_once '../config.php';
requireAdmin();

// Admin silme işlemi
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    
    // Kendi hesabını silmesini engelle
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['error'] = 'Kendi hesabınızı silemezsiniz!';
    } else {
        // En az bir admin kalmalı kontrolü
        $stmt = $db->query("SELECT COUNT(*) as total FROM adminler");
        $total = $stmt->fetch()['total'];
        
        if ($total <= 1) {
            $_SESSION['error'] = 'Son admin hesabı silinemez!';
        } else {
            $stmt = $db->prepare("DELETE FROM adminler WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Admin başarıyla silindi.';
        }
    }
    
    header('Location: adminler.php');
    exit;
}

// Admin listesi
$adminler = $db->query("SELECT * FROM adminler ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Yönetimi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>
                <i class="fas fa-user-shield"></i>
                Admin Yönetimi
            </h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            </div>
        </div>

        <div class="admin-card">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="margin: 0;">
                    <i class="fas fa-list"></i>
                    Admin Listesi
                </h2>
                <a href="admin_ekle.php" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Yeni Admin Ekle
                </a>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Kayıt Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($adminler)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                                    <p style="color: var(--text-light);">Henüz admin bulunmuyor.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($adminler as $admin): ?>
                                <tr>
                                    <td><?php echo $admin['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($admin['kullanici_adi']); ?></strong>
                                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                            <span class="badge badge-primary" style="margin-left: 0.5rem;">Siz</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($admin['ad_soyad']); ?></td>
                                    <td>
                                        <?php if (!empty($admin['email'])): ?>
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($admin['email']); ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d.m.Y H:i', strtotime($admin['kayit_tarihi'])); ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin_duzenle.php?id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-primary" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                <a href="adminler.php?sil=<?php echo $admin['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   title="Sil"
                                                   onclick="return confirm('Bu admini silmek istediğinizden emin misiniz?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'includes/bildirim_popup.php'; ?>
</body>
</html>

