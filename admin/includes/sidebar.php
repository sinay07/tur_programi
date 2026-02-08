<aside class="admin-sidebar">
    <div class="logo">
        <i class="fas fa-route"></i>
        <h2><?php echo SITE_NAME; ?></h2>
        <p style="font-size: 0.9rem; opacity: 0.8; margin-top: 0.5rem;">Admin Panel</p>
    </div>
    
    <ul class="admin-nav">
        <li>
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="adminler.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['adminler.php', 'admin_ekle.php', 'admin_duzenle.php']) ? 'active' : ''; ?>">
                <i class="fas fa-user-shield"></i>
                <span>Adminler</span>
            </a>
        </li>
        <li>
            <a href="bildirimler.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bildirimler.php' ? 'active' : ''; ?>" style="position: relative;">
                <i class="fas fa-bell"></i>
                <span>Bildirimler</span>
                <?php
                $stmt = $db->query("SELECT COUNT(*) as total FROM admin_bildirimler WHERE okundu = 0");
                $okunmamis = $stmt->fetch()['total'];
                if ($okunmamis > 0):
                ?>
                    <span style="position: absolute; top: 5px; right: 10px; background: var(--danger-color); color: white; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                        <?php echo $okunmamis; ?>
                    </span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="kullanicilar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kullanicilar.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Kullanıcılar</span>
            </a>
        </li>
        <li>
            <a href="takvim.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'takvim.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Takvim</span>
            </a>
        </li>
        <li>
            <a href="aktiviteler.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'aktiviteler.php' ? 'active' : ''; ?>">
                <i class="fas fa-hiking"></i>
                <span>Aktiviteler</span>
            </a>
        </li>
        <li>
            <a href="restoranlar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'restoranlar.php' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i>
                <span>Restoranlar</span>
            </a>
        </li>
        <li>
            <a href="sehirler.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sehirler.php' ? 'active' : ''; ?>">
                <i class="fas fa-city"></i>
                <span>Şehirler</span>
            </a>
        </li>
        <li>
            <a href="siparisler.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'siparisler.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Siparişler</span>
            </a>
        </li>
        <li>
            <a href="../index.php" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                <span>Siteyi Görüntüle</span>
            </a>
        </li>
        <li style="margin-top: 2rem;">
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Çıkış Yap</span>
            </a>
        </li>
    </ul>
</aside>

