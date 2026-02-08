<?php
require_once 'config.php';
requireLogin();

$sehir = getSehir($db, 'gaziantep');
if (!$sehir) {
    redirect('index.php');
}

$aktiviteler = getAktiviteler($db, $sehir['id']);
$restoranlar = getRestoranlar($db, $sehir['id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($sehir['sehir_adi']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="city-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container">
            <h1><i class="fas fa-map-marker-alt"></i> <?php echo sanitize($sehir['sehir_adi']); ?></h1>
            <p><?php echo sanitize($sehir['aciklama']); ?></p>
        </div>
    </div>
    
    <div class="container city-content">
        <!-- Aktiviteler Bölümü -->
        <section class="city-section">
            <div class="section-header">
                <h2><i class="fas fa-hiking"></i> Aktiviteler</h2>
                <p>Gezilecek yerler ve yapılacak aktiviteler</p>
            </div>
            
            <?php if (count($aktiviteler) > 0): ?>
                <div class="cards-grid">
                    <?php foreach ($aktiviteler as $aktivite): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3><?php echo sanitize($aktivite['baslik']); ?></h3>
                            </div>
                            <div class="card-body">
                                <p><?php echo nl2br(sanitize($aktivite['aciklama'])); ?></p>
                                
                                <?php if (!empty($aktivite['adres'])): ?>
                                    <div class="card-info">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo sanitize($aktivite['adres']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($aktivite['sure'])): ?>
                                    <div class="card-info">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo sanitize($aktivite['sure']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($aktivite['fiyat'] > 0): ?>
                                    <div class="card-info">
                                        <i class="fas fa-tag"></i>
                                        <span><?php echo number_format($aktivite['fiyat'], 2); ?> TL</span>
                                    </div>
                                <?php else: ?>
                                    <div class="card-info">
                                        <i class="fas fa-tag"></i>
                                        <span class="badge badge-success">Ücretsiz</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Henüz aktivite eklenmemiş.
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Restoranlar Bölümü -->
        <section class="city-section">
            <div class="section-header">
                <h2><i class="fas fa-utensils"></i> Restoranlar</h2>
                <p>Lezzet duraklarınız</p>
            </div>
            
            <?php if (count($restoranlar) > 0): ?>
                <div class="cards-grid">
                    <?php foreach ($restoranlar as $restoran): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3><?php echo sanitize($restoran['baslik']); ?></h3>
                                <?php if (!empty($restoran['mutfak_turu'])): ?>
                                    <span class="badge"><?php echo sanitize($restoran['mutfak_turu']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <p><?php echo nl2br(sanitize($restoran['aciklama'])); ?></p>
                                
                                <?php if (!empty($restoran['adres'])): ?>
                                    <div class="card-info">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo sanitize($restoran['adres']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($restoran['telefon'])): ?>
                                    <div class="card-info">
                                        <i class="fas fa-phone"></i>
                                        <a href="tel:<?php echo sanitize($restoran['telefon']); ?>">
                                            <?php echo sanitize($restoran['telefon']); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($restoran['ortalama_fiyat'])): ?>
                                    <div class="card-info">
                                        <i class="fas fa-wallet"></i>
                                        <span><?php echo sanitize($restoran['ortalama_fiyat']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php
                                // Restoran menüsünü getir
                                $kategoriler = getMenuKategoriler($db, $restoran['id']);
                                if (count($kategoriler) > 0):
                                ?>
                                    <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--border-color);">
                                        <h4 style="color: var(--dark-color); margin-bottom: 15px;">
                                            <i class="fas fa-book-open"></i> Menü
                                        </h4>
                                        
                                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                            <?php foreach ($kategoriler as $kategori): ?>
                                                <?php
                                                $urunler = getMenuUrunler($db, $kategori['id']);
                                                if (count($urunler) > 0):
                                                ?>
                                                    <button 
                                                        class="kategori-btn"
                                                        onclick="openMenuModal(<?php echo $kategori['id']; ?>, '<?php echo htmlspecialchars($kategori['kategori_adi'], ENT_QUOTES); ?>', <?php echo $restoran['id']; ?>)"
                                                        style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; border: none; padding: 12px 24px; border-radius: 25px; cursor: pointer; font-size: 1rem; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s;">
                                                        <i class="fas fa-layer-group"></i>
                                                        <?php echo sanitize($kategori['kategori_adi']); ?>
                                                        <span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 10px; margin-left: 8px; font-size: 0.85rem;">
                                                            <?php echo count($urunler); ?>
                                                        </span>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Henüz restoran eklenmemiş.
                </div>
            <?php endif; ?>
        </section>
    </div>
    
    <!-- Menü Modal -->
    <div id="menu-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 15px; max-width: 800px; max-height: 90vh; overflow-y: auto; position: relative; animation: modalSlideIn 0.3s ease;">
            <div style="position: sticky; top: 0; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; padding: 20px; border-radius: 15px 15px 0 0; display: flex; justify-content: space-between; align-items: center; z-index: 10;">
                <h3 id="modal-title" style="margin: 0; font-size: 1.5rem;">
                    <i class="fas fa-utensils"></i> <span id="modal-kategori-adi"></span>
                </h3>
                <button onclick="closeMenuModal()" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="modal-urunler" style="padding: 20px;">
                <!-- Ürünler buraya yüklenecek -->
            </div>
        </div>
    </div>
    
    <style>
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .kategori-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
        }
        
        .urun-card {
            background: var(--light-color);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .urun-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .urun-gorsel {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }
        
        .urun-gorsel-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            flex-shrink: 0;
        }
        
        .sepet-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        
        .sepet-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
    
    <script>
        function openMenuModal(kategoriId, kategoriAdi, restoranId) {
            document.getElementById('modal-kategori-adi').textContent = kategoriAdi;
            document.getElementById('menu-modal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // AJAX ile ürünleri yükle
            fetch(`get_menu_urunler.php?kategori_id=${kategoriId}&restoran_id=${restoranId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.forEach(urun => {
                        html += `
                            <div class="urun-card">
                                ${urun.gorsel 
                                    ? `<img src="/avustur/uploads/urunler/${urun.gorsel}" class="urun-gorsel" alt="${urun.urun_adi}">` 
                                    : `<div class="urun-gorsel-placeholder"><i class="fas fa-utensils"></i></div>`
                                }
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 8px 0; color: var(--dark-color);">${urun.urun_adi}</h4>
                                    ${urun.aciklama ? `<p style="color: var(--text-light); margin: 0 0 8px 0; font-size: 0.95rem;">${urun.aciklama}</p>` : ''}
                                    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 8px;">
                                        ${urun.porsiyon_bilgisi ? `<span style="color: var(--text-light); font-size: 0.9rem;"><i class="fas fa-weight"></i> ${urun.porsiyon_bilgisi}</span>` : ''}
                                        ${urun.kalori ? `<span style="color: var(--text-light); font-size: 0.9rem;"><i class="fas fa-fire"></i> ${urun.kalori}</span>` : ''}
                                    </div>
                                    <button type="button" class="sepet-btn" onclick="sepeteEkle(${urun.id}, ${restoranId}, this)">
                                        <i class="fas fa-shopping-cart"></i> Sepete Ekle
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    document.getElementById('modal-urunler').innerHTML = html || '<p style="text-align: center; color: var(--text-light);">Bu kategoride ürün bulunmamaktadır.</p>';
                })
                .catch(error => {
                    document.getElementById('modal-urunler').innerHTML = '<p style="text-align: center; color: red;">Ürünler yüklenirken hata oluştu.</p>';
                });
        }
        
        function closeMenuModal() {
            document.getElementById('menu-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // ESC tuşu ile kapat
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMenuModal();
            }
        });
        
        // Modal dışına tıklandığında kapat
        document.getElementById('menu-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMenuModal();
            }
        });
        
        // Sepete ekle fonksiyonu (AJAX)
        function sepeteEkle(urunId, restoranId, button) {
            // Butonu devre dışı bırak
            button.disabled = true;
            const eskiMetin = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ekleniyor...';
            
            // FormData oluştur
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('urun_id', urunId);
            formData.append('restoran_id', restoranId);
            
            // AJAX ile gönder
            fetch('sepet.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Başarı bildirimi göster
                    gosterBildirim('✅ Ürün sepete eklendi!', 'success');
                    
                    // Sepet sayacını güncelle
                    guncelSepetSayaci();
                    
                    // Butonu eski haline getir
                    setTimeout(() => {
                        button.disabled = false;
                        button.innerHTML = eskiMetin;
                    }, 500);
                } else {
                    gosterBildirim('❌ Bir hata oluştu!', 'error');
                    button.disabled = false;
                    button.innerHTML = eskiMetin;
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                gosterBildirim('❌ Bir hata oluştu!', 'error');
                button.disabled = false;
                button.innerHTML = eskiMetin;
            });
        }
        
        // Bildirim göster
        function gosterBildirim(mesaj, tip) {
            const bildirim = document.createElement('div');
            bildirim.textContent = mesaj;
            bildirim.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${tip === 'success' ? '#48bb78' : '#f56565'};
                color: white;
                padding: 15px 25px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 10000;
                font-weight: 600;
                animation: slideInRight 0.3s ease;
            `;
            
            document.body.appendChild(bildirim);
            
            setTimeout(() => {
                bildirim.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => bildirim.remove(), 300);
            }, 2000);
        }
        
        // Sepet sayacını güncelle
        function guncelSepetSayaci() {
            fetch('sepet.php?action=bilgi')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const sepetSayac = document.querySelector('.sepet-sayac');
                        if (sepetSayac && data.adet > 0) {
                            sepetSayac.textContent = data.adet;
                            sepetSayac.style.display = 'flex';
                        }
                    }
                });
        }
    </script>
    
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
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
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

