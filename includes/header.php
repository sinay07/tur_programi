<header class="main-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">
                    <i class="fas fa-route"></i>
                    <span><?php echo SITE_NAME; ?></span>
                </a>
            </div>
            
            <nav class="main-nav">
                <?php if (isLoggedIn()): ?>
                    <?php 
                    $kullanici = getKullanici($db, $_SESSION['kullanici_id']); 
                    $sepetAdet = isset($_SESSION['sepet']) ? count($_SESSION['sepet']) : 0;
                    ?>
                    <div class="user-menu">
                        <a href="siparislerim.php" class="btn btn-sm btn-secondary" title="Siparişlerim">
                            <i class="fas fa-receipt"></i>
                        </a>
                        <a href="sepet.php" class="btn btn-sm btn-primary" style="position: relative;" title="Sepetim">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($sepetAdet > 0): ?>
                                <span style="position: absolute; top: -8px; right: -8px; background: var(--danger-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                                    <?php echo $sepetAdet; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <span class="user-name">
                            <i class="fas fa-user"></i>
                            <?php echo sanitize($kullanici['ad_soyad']); ?>
                        </span>
                        <a href="logout.php" class="btn btn-sm btn-secondary">
                            <i class="fas fa-sign-out-alt"></i> Çıkış
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Giriş Yap
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

