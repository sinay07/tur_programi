<!-- Bildirim Popup (Admin Sayfalarında) -->
<?php
// Son 5 okunmamış bildirimi al
$stmt = $db->query("SELECT * FROM admin_bildirimler WHERE okundu = 0 ORDER BY olusturma_tarihi DESC LIMIT 5");
$yeni_bildirimler = $stmt->fetchAll();

if (count($yeni_bildirimler) > 0):
?>
<div id="bildirimPopup" style="position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 400px;">
    <?php foreach ($yeni_bildirimler as $index => $bildirim): ?>
        <?php
        $tip_renk = [
            'info' => '#4299e1',
            'success' => '#48bb78',
            'warning' => '#ed8936',
            'error' => '#f56565'
        ];
        $renk = $tip_renk[$bildirim['tip']] ?? '#4299e1';
        ?>
        <div class="bildirim-item" 
             style="background: white; padding: 18px 20px; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.2); margin-bottom: 15px; border-left: 4px solid <?php echo $renk; ?>; animation: slideInRight 0.5s ease <?php echo $index * 0.1; ?>s both; cursor: pointer;"
             onclick="window.location.href='bildirimler.php?okundu=1&id=<?php echo $bildirim['id']; ?>'">
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 15px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                        <i class="fas fa-bell" style="color: <?php echo $renk; ?>;"></i>
                        <strong style="color: var(--dark-color);"><?php echo sanitize($bildirim['baslik']); ?></strong>
                    </div>
                    <p style="margin: 5px 0; font-size: 0.9rem; color: var(--text-color);">
                        <?php echo sanitize(substr($bildirim['mesaj'], 0, 80)) . (strlen($bildirim['mesaj']) > 80 ? '...' : ''); ?>
                    </p>
                    <small style="color: var(--text-light);">
                        <i class="fas fa-clock"></i>
                        <?php
                        $zaman_farki = time() - strtotime($bildirim['olusturma_tarihi']);
                        if ($zaman_farki < 60) {
                            echo 'Az önce';
                        } elseif ($zaman_farki < 3600) {
                            echo floor($zaman_farki / 60) . ' dakika önce';
                        } else {
                            echo floor($zaman_farki / 3600) . ' saat önce';
                        }
                        ?>
                    </small>
                </div>
                <button onclick="event.stopPropagation(); bildirimKapat(this);" 
                        style="background: transparent; border: none; color: var(--text-light); cursor: pointer; font-size: 1.2rem; padding: 5px; transition: all 0.3s;"
                        onmouseover="this.style.color='var(--danger-color)'"
                        onmouseout="this.style.color='var(--text-light)'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
@keyframes slideInRight {
    from {
        transform: translateX(500px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(500px);
        opacity: 0;
    }
}
</style>

<script>
function bildirimKapat(button) {
    const bildirimItem = button.closest('.bildirim-item');
    bildirimItem.style.animation = 'slideOutRight 0.3s ease';
    setTimeout(() => {
        bildirimItem.remove();
        
        // Hiç bildirim kalmadıysa popup'ı gizle
        const popup = document.getElementById('bildirimPopup');
        if (popup && popup.children.length === 0) {
            popup.remove();
        }
    }, 300);
}

// 10 saniyede bir yeni bildirim kontrolü (sayfa yenilenmeden)
setInterval(() => {
    fetch('bildirim_kontrol.php')
        .then(response => response.json())
        .then(data => {
            if (data.yeni_bildirim) {
                location.reload(); // Yeni bildirim varsa sayfayı yenile
            }
        })
        .catch(error => console.log('Bildirim kontrolü yapılamadı'));
}, 10000);
</script>
<?php endif; ?>


