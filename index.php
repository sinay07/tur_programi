<?php
require_once 'config.php';

// Eğer kullanıcı giriş yapmışsa bugünkü tura yönlendir
if (isLoggedIn()) {
    $bugunTur = getBugunTur($db);
    if ($bugunTur) {
        redirect($bugunTur['sehir_slug'] . '.php');
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Ana Sayfa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="logo">
                <i class="fas fa-route"></i>
                <h1><?php echo SITE_NAME; ?></h1>
            </div>
            <p class="hero-subtitle">Avustur ile Eşsiz Güzellikleri Keşfedin</p>
            
            <?php if (isLoggedIn()): ?>
                <?php 
                $kullanici = getKullanici($db, $_SESSION['kullanici_id']);
                ?>
                <div class="welcome-box">
                    <h2>Hoş Geldiniz, <?php echo sanitize($kullanici['ad_soyad']); ?>!</h2>
                    <p class="info-text">
                        <?php if ($bugunTur): ?>
                            <i class="fas fa-map-marked-alt"></i>
                            Bugünkü turunuz: <strong><?php echo sanitize($bugunTur['sehir_adi']); ?></strong>
                            <br><br>
                            <a href="<?php echo $bugunTur['sehir_slug']; ?>.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i> Tura Git
                            </a>
                        <?php else: ?>
                            <i class="fas fa-info-circle"></i>
                            Bugün için planlanmış bir tur bulunmamaktadır.
                        <?php endif; ?>
                    </p>
                    <a href="logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                    </a>
                </div>
            <?php else: ?>
                <div class="cta-buttons">
                    <a href="login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Giriş Yap
                    </a>
                    <a href="admin/login.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-user-shield"></i> Admin Girişi
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!isLoggedIn()): ?>
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Gezilerimiz</h2>
            <div class="features-grid">
                <?php
                // Aktif şehirleri çek
                $stmt = $db->query("SELECT * FROM sehirler WHERE aktif = 1 ORDER BY sehir_adi ASC");
                $sehirler = $stmt->fetchAll();
                
                // İkon map (veritabanında ikon yoksa varsayılan ikonlar)
                $ikon_map = [
                    'gaziantep' => 'fas fa-utensils',
                    'diyarbakir' => 'fas fa-fort-awesome',
                    'adiyaman' => 'fas fa-mountain',
                    'sanliurfa' => 'fas fa-mosque',
                    'batman' => 'fas fa-landmark',
                    'mardin' => 'fas fa-home'
                ];
                
                foreach ($sehirler as $sehir):
                    $ikon = isset($ikon_map[$sehir['sehir_slug']]) ? $ikon_map[$sehir['sehir_slug']] : 'fas fa-map-marker-alt';
                ?>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="<?php echo $ikon; ?>"></i>
                        </div>
                        <h3><?php echo sanitize($sehir['sehir_adi']); ?></h3>
                        <p><?php echo sanitize($sehir['aciklama']); ?></p>
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($sehirler) === 0): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                        <i class="fas fa-info-circle" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p style="color: #666;">Henüz aktif şehir bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 <?php echo SITE_NAME; ?>. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>

