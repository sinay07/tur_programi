<?php
require_once '../config.php';
requireAdmin();

// Türkiye'nin 81 ili
$turkiye_sehirler = [
    'Adana' => 'Gastronomi ve tarih buluşuyor',
    'Adıyaman' => 'Nemrut Dağı ve muhteşem gün doğumları',
    'Afyonkarahisar' => 'Termal turizmin merkezi',
    'Ağrı' => 'Doğa harikası İshak Paşa Sarayı',
    'Aksaray' => 'Ihlara Vadisi\'nin eşsiz güzelliği',
    'Amasya' => 'Kral kaya mezarları ve doğa',
    'Ankara' => 'Başkent ve tarihi zenginlikler',
    'Antalya' => 'Türkiye\'nin turizm cenneti',
    'Ardahan' => 'Doğal güzellikler ve göller',
    'Artvin' => 'Yeşilin her tonuyla buluşma',
    'Aydın' => 'Efes antik kenti ve deniz',
    'Balıkesir' => 'Cunda\'nın huzurlu atmosferi',
    'Bartın' => 'Amasra\'nın tarihi dokusu',
    'Batman' => 'Hasankeyf\'in tarihi hazineleri',
    'Bayburt' => 'Doğu Karadeniz\'in saklı kalmış güzelliği',
    'Bilecik' => 'Osmanlı\'nın kuruluş şehri',
    'Bingöl' => 'Doğal güzellikler ve yayla turizmi',
    'Bitlis' => 'Tarihi Bitlis Kalesi',
    'Bolu' => 'Yedigöller\'in muhteşem doğası',
    'Burdur' => 'Salda Gölü\'nün turkuaz suları',
    'Bursa' => 'Yeşil Bursa ve termal kaynaklar',
    'Çanakkale' => 'Şehitlik ve Truva\'nın tarihi',
    'Çankırı' => 'Tuz mağarası ve tarihi dokular',
    'Çorum' => 'Hitit başkenti',
    'Denizli' => 'Pamukkale\'nin beyaz cennet',
    'Diyarbakır' => 'Tarihi surları ve Dicle nehriyle büyüleyici',
    'Düzce' => 'Melen Şelalesi ve yeşil doğa',
    'Edirne' => 'Selimiye Camii ve tarihi dokular',
    'Elazığ' => 'Harput kalesi ve Hazar Gölü',
    'Erzincan' => 'Kemaliye ve tarihi köprüler',
    'Erzurum' => 'Tarihi ve kış turizmi',
    'Eskişehir' => 'Modern şehir, üniversite kenti',
    'Gaziantep' => 'Gastronomi başkenti, baklava ve kebabın anavatanı',
    'Giresun' => 'Yeşilin her tonuyla Karadeniz',
    'Gümüşhane' => 'Karaca Mağarası ve tarihi yollar',
    'Hakkari' => 'Cilo-Sat dağları',
    'Hatay' => 'Medeniyetler beşiği',
    'Iğdır' => 'Ağrı Dağı manzarası',
    'Isparta' => 'Gül kenti ve göller bölgesi',
    'İstanbul' => 'İki kıtanın buluşma noktası',
    'İzmir' => 'Ege\'nin incisi',
    'Kahramanmaraş' => 'Dondurma ve tarihi dokular',
    'Karabük' => 'Safranbolu\'nun tarihi evleri',
    'Karaman' => 'Karamanoğlu Mehmet Bey\'in şehri',
    'Kars' => 'Ani harabeleri ve tarihi zenginlikler',
    'Kastamonu' => 'İnebolu ve tarihi dokular',
    'Kayseri' => 'Erciyes ve tarihi zenginlikler',
    'Kırıkkale' => 'Keskin ve çevre kültürü',
    'Kırklareli' => 'Tarihi köprüler ve doğa',
    'Kırşehir' => 'Cacabey Camii ve tarihi dokular',
    'Kilis' => 'Gastronomi ve kültür',
    'Kocaeli' => 'Sanayi ve deniz',
    'Konya' => 'Mevlana ve Sema',
    'Kütahya' => 'Çini sanatı ve tarih',
    'Malatya' => 'Kayısı bahçeleri',
    'Manisa' => 'Sardes antik kenti',
    'Mardin' => 'Taş evleri ve benzersiz mimarisi',
    'Mersin' => 'Akdeniz\'in incisi',
    'Muğla' => 'Bodrum ve Marmaris',
    'Muş' => 'Tarihi Malazgirt',
    'Nevşehir' => 'Kapadokya\'nın büyüsü',
    'Niğde' => 'Aladağlar ve tarihi kaleler',
    'Ordu' => 'Fındık ve Boztepe',
    'Osmaniye' => 'Karatepe-Aslantaş',
    'Rize' => 'Çay bahçeleri ve yaylalar',
    'Sakarya' => 'Sapanca Gölü ve doğa',
    'Samsun' => 'Karadeniz\'in güzellikleri',
    'Siirt' => 'Tarihi Veysel Karani türbesi',
    'Sinop' => 'Deniz ve tarihi kaleler',
    'Sivas' => 'Divriği ve Şifaiye Medresesi',
    'Şanlıurfa' => 'Peygamberler şehri, Balıklıgöl ve Göbeklitepe',
    'Şırnak' => 'Cudi Dağı',
    'Tekirdağ' => 'Rakı ve deniz',
    'Tokat' => 'Tarihi evler ve Ballıca Mağarası',
    'Trabzon' => 'Sümela Manastırı',
    'Tunceli' => 'Munzur Vadisi',
    'Uşak' => 'Tarihi Karun Hazinesi',
    'Van' => 'Van Gölü ve Van kedisi',
    'Yalova' => 'Termal kaynaklar',
    'Yozgat' => 'Boğazlıyan ve Çamlık Milli Parkı',
    'Zonguldak' => 'Kömür madenleri ve deniz'
];

// Mevcut şehirleri çek
$stmt = $db->query("SELECT sehir_adi, sehir_slug FROM sehirler");
$mevcut_sehirler = [];
while ($row = $stmt->fetch()) {
    $mevcut_sehirler[] = $row['sehir_adi'];
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sehir_adi = trim($_POST['sehir_adi']);
    $aciklama = trim($_POST['aciklama']);
    $ikon = trim($_POST['ikon']);
    $gradient = trim($_POST['gradient']);
    
    // Slug oluştur (Türkçe karakterleri değiştir)
    $sehir_slug = strtolower($sehir_adi);
    $sehir_slug = str_replace(['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü'], 
                              ['c', 'g', 'i', 'o', 's', 'u', 'c', 'g', 'i', 'o', 's', 'u'], 
                              $sehir_slug);
    $sehir_slug = preg_replace('/[^a-z0-9]+/', '', $sehir_slug);
    
    // Veritabanına ekle
    try {
        $stmt = $db->prepare("INSERT INTO sehirler (sehir_adi, sehir_slug, aciklama, aktif) VALUES (?, ?, ?, 1)");
        $stmt->execute([$sehir_adi, $sehir_slug, $aciklama]);
        
        // PHP dosyasını oluştur
        $php_dosya_adi = "../{$sehir_slug}.php";
        $php_icerik = "<?php
require_once 'config.php';
requireLogin();

\$sehir = getSehir(\$db, '{$sehir_slug}');
if (!\$sehir) {
    redirect('index.php');
}

\$aktiviteler = getAktiviteler(\$db, \$sehir['id']);
\$restoranlar = getRestoranlar(\$db, \$sehir['id']);
?>
<!DOCTYPE html>
<html lang=\"tr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title><?php echo sanitize(\$sehir['sehir_adi']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel=\"stylesheet\" href=\"assets/css/style.css\">
    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class=\"city-header\" style=\"background: {$gradient};\">
        <div class=\"container\">
            <h1><i class=\"{$ikon}\"></i> <?php echo sanitize(\$sehir['sehir_adi']); ?></h1>
            <p><?php echo sanitize(\$sehir['aciklama']); ?></p>
        </div>
    </div>
    
    <div class=\"container city-content\">
        <!-- Aktiviteler Bölümü -->
        <section class=\"city-section\">
            <div class=\"section-header\">
                <h2><i class=\"fas fa-hiking\"></i> Aktiviteler</h2>
                <p>Gezilecek yerler ve yapılacak aktiviteler</p>
            </div>
            
            <?php if (count(\$aktiviteler) > 0): ?>
                <div class=\"cards-grid\">
                    <?php foreach (\$aktiviteler as \$aktivite): ?>
                        <div class=\"card\">
                            <div class=\"card-header\">
                                <h3><?php echo sanitize(\$aktivite['baslik']); ?></h3>
                            </div>
                            <div class=\"card-body\">
                                <p><?php echo nl2br(sanitize(\$aktivite['aciklama'])); ?></p>
                                
                                <?php if (!empty(\$aktivite['adres'])): ?>
                                    <div class=\"card-info\">
                                        <i class=\"fas fa-map-marker-alt\"></i>
                                        <span><?php echo sanitize(\$aktivite['adres']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty(\$aktivite['sure'])): ?>
                                    <div class=\"card-info\">
                                        <i class=\"fas fa-clock\"></i>
                                        <span><?php echo sanitize(\$aktivite['sure']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (\$aktivite['fiyat'] > 0): ?>
                                    <div class=\"card-info\">
                                        <i class=\"fas fa-tag\"></i>
                                        <span><?php echo number_format(\$aktivite['fiyat'], 2); ?> TL</span>
                                    </div>
                                <?php else: ?>
                                    <div class=\"card-info\">
                                        <i class=\"fas fa-tag\"></i>
                                        <span class=\"badge badge-success\">Ücretsiz</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class=\"alert alert-info\">
                    <i class=\"fas fa-info-circle\"></i>
                    Henüz aktivite eklenmemiş.
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Restoranlar Bölümü -->
        <section class=\"city-section\">
            <div class=\"section-header\">
                <h2><i class=\"fas fa-utensils\"></i> Restoranlar</h2>
                <p>Lezzet duraklarınız</p>
            </div>
            
            <?php if (count(\$restoranlar) > 0): ?>
                <div class=\"cards-grid\">
                    <?php foreach (\$restoranlar as \$restoran): ?>
                        <div class=\"card\">
                            <div class=\"card-header\">
                                <h3><?php echo sanitize(\$restoran['baslik']); ?></h3>
                                <?php if (!empty(\$restoran['mutfak_turu'])): ?>
                                    <span class=\"badge\"><?php echo sanitize(\$restoran['mutfak_turu']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class=\"card-body\">
                                <p><?php echo nl2br(sanitize(\$restoran['aciklama'])); ?></p>
                                
                                <?php if (!empty(\$restoran['adres'])): ?>
                                    <div class=\"card-info\">
                                        <i class=\"fas fa-map-marker-alt\"></i>
                                        <span><?php echo sanitize(\$restoran['adres']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty(\$restoran['telefon'])): ?>
                                    <div class=\"card-info\">
                                        <i class=\"fas fa-phone\"></i>
                                        <a href=\"tel:<?php echo sanitize(\$restoran['telefon']); ?>\">
                                            <?php echo sanitize(\$restoran['telefon']); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty(\$restoran['ortalama_fiyat'])): ?>
                                    <div class=\"card-info\">
                                        <i class=\"fas fa-wallet\"></i>
                                        <span><?php echo sanitize(\$restoran['ortalama_fiyat']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Restoran Menüsü -->
                                <?php
                                \$kategoriler = getMenuKategoriler(\$db, \$restoran['id']);
                                if (count(\$kategoriler) > 0):
                                ?>
                                    <div style=\"margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;\">
                                        <div style=\"display: flex; flex-wrap: wrap; gap: 0.5rem;\">
                                            <?php foreach (\$kategoriler as \$kategori): ?>
                                                <button onclick=\"menuAc(<?php echo \$restoran['id']; ?>, <?php echo \$kategori['id']; ?>, '<?php echo sanitize(\$restoran['baslik']); ?>', '<?php echo sanitize(\$kategori['kategori_adi']); ?>')\" 
                                                        class=\"btn btn-sm btn-primary\">
                                                    <i class=\"fas fa-utensils\"></i> <?php echo sanitize(\$kategori['kategori_adi']); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class=\"alert alert-info\">
                    <i class=\"fas fa-info-circle\"></i>
                    Henüz restoran eklenmemiş.
                </div>
            <?php endif; ?>
        </section>
    </div>
    
    <!-- Menu Modal -->
    <div id=\"menuModal\" class=\"modal\">
        <div class=\"modal-content\" style=\"max-width: 800px; max-height: 80vh; overflow-y: auto;\">
            <span class=\"close\" onclick=\"menuKapat()\">&times;</span>
            <h2 id=\"menuBaslik\">Menü</h2>
            <div id=\"menuIcerik\" style=\"margin-top: 1.5rem;\">
                <!-- Ürünler burada listelenecek -->
            </div>
        </div>
    </div>
    
    <script>
    function menuAc(restoranId, kategoriId, restoranAdi, kategoriAdi) {
        document.getElementById('menuBaslik').innerHTML = '<i class=\"fas fa-utensils\"></i> ' + restoranAdi + ' - ' + kategoriAdi;
        document.getElementById('menuIcerik').innerHTML = '<div style=\"text-align: center; padding: 2rem;\"><i class=\"fas fa-spinner fa-spin fa-2x\"></i><br><br>Yükleniyor...</div>';
        document.getElementById('menuModal').style.display = 'block';
        
        fetch('sepet.php?action=menu&kategori_id=' + kategoriId)
            .then(response => response.json())
            .then(data => {
                let html = '<div style=\"display: grid; gap: 1rem;\">';
                
                if (data.urunler && data.urunler.length > 0) {
                    data.urunler.forEach(urun => {
                        let gorselUrl = urun.gorsel ? '/avustur/uploads/urunler/' + urun.gorsel : '/avustur/assets/img/no-image.png';
                        
                        html += `
                            <div class=\"card\" style=\"padding: 1rem; display: flex; gap: 1rem; flex-direction: row; align-items: center;\">
                                <img src=\"\${gorselUrl}\" 
                                     alt=\"\${urun.urun_adi}\" 
                                     style=\"width: 100px; height: 75px; object-fit: cover; border-radius: 8px;\"
                                     onerror=\"this.src='/avustur/assets/img/no-image.png'\">
                                <div style=\"flex: 1;\">
                                    <h3 style=\"margin: 0 0 0.5rem 0; font-size: 1.1rem;\">\${urun.urun_adi}</h3>
                                    <p style=\"margin: 0; color: #666; font-size: 0.9rem;\">\${urun.aciklama || 'Açıklama yok'}</p>
                                </div>
                                <div style=\"display: flex; align-items: center; gap: 0.5rem;\">
                                    <button onclick=\"sepeteEkle(\${urun.id}, \${restoranId})\" 
                                            class=\"btn btn-sm btn-primary\" 
                                            style=\"white-space: nowrap;\">
                                        <i class=\"fas fa-shopping-cart\"></i> Sepete Ekle
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html += '<div class=\"alert alert-info\"><i class=\"fas fa-info-circle\"></i> Bu kategoride ürün bulunmamaktadır.</div>';
                }
                
                html += '</div>';
                document.getElementById('menuIcerik').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('menuIcerik').innerHTML = '<div class=\"alert alert-danger\"><i class=\"fas fa-exclamation-triangle\"></i> Menü yüklenirken bir hata oluştu.</div>';
                console.error('Hata:', error);
            });
    }
    
    function menuKapat() {
        document.getElementById('menuModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        let modal = document.getElementById('menuModal');
        if (event.target == modal) {
            menuKapat();
        }
    }
    
    function sepeteEkle(urunId, restoranId) {
        fetch('sepet.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=ekle&urun_id=' + urunId + '&restoran_id=' + restoranId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                gosterBildirim('Ürün sepete eklendi!', 'success');
                sepetGuncelle();
            } else {
                gosterBildirim(data.message || 'Bir hata oluştu!', 'error');
            }
        })
        .catch(error => {
            console.error('Hata:', error);
            gosterBildirim('Bir hata oluştu!', 'error');
        });
    }
    
    function gosterBildirim(mesaj, tip) {
        let bildirim = document.createElement('div');
        bildirim.className = 'notification ' + (tip === 'success' ? 'notification-success' : 'notification-error');
        bildirim.innerHTML = '<i class=\"fas fa-' + (tip === 'success' ? 'check-circle' : 'exclamation-circle') + '\"></i> ' + mesaj;
        bildirim.style.cssText = 'position: fixed; top: 20px; right: 20px; padding: 1rem 1.5rem; background: ' + 
                                (tip === 'success' ? '#10b981' : '#ef4444') + 
                                '; color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10000; animation: slideIn 0.3s ease-out;';
        document.body.appendChild(bildirim);
        
        setTimeout(() => {
            bildirim.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => bildirim.remove(), 300);
        }, 3000);
    }
    
    function sepetGuncelle() {
        fetch('sepet.php?action=bilgi')
            .then(response => response.json())
            .then(data => {
                let sepetSayisi = document.querySelector('.cart-badge');
                if (sepetSayisi) {
                    sepetSayisi.textContent = data.adet || 0;
                    if (data.adet > 0) {
                        sepetSayisi.style.display = 'inline-block';
                    }
                }
            });
    }
    </script>
    
    <style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 30px;
        border: none;
        border-radius: 15px;
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease-out;
    }
    
    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 35px;
        font-weight: bold;
        line-height: 20px;
        cursor: pointer;
        transition: color 0.2s;
    }
    
    .close:hover,
    .close:focus {
        color: #e74c3c;
    }
    
    .card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
    </style>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
";
        
        // Dosyayı oluştur
        if (file_put_contents($php_dosya_adi, $php_icerik)) {
            $_SESSION['mesaj'] = "Şehir başarıyla eklendi ve {$sehir_slug}.php dosyası oluşturuldu!";
            $_SESSION['mesaj_tip'] = 'success';
        } else {
            $_SESSION['mesaj'] = "Şehir veritabanına eklendi ancak PHP dosyası oluşturulamadı. Lütfen manuel olarak oluşturun.";
            $_SESSION['mesaj_tip'] = 'warning';
        }
        
        redirect('sehirler.php');
        
    } catch (PDOException $e) {
        $_SESSION['mesaj'] = "Hata: " . $e->getMessage();
        $_SESSION['mesaj_tip'] = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Şehir Ekle - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/bildirim_popup.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-plus-circle"></i> Yeni Şehir Ekle</h1>
            <div>
                <a href="sehirler.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri Dön
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
            <div class="card-header">
                <h2>Şehir Bilgileri</h2>
                <p style="margin: 0.5rem 0 0; color: #666;">Eklemek istediğiniz şehri seçin ve detayları girin</p>
            </div>
            <div class="card-body">
                <form method="POST" id="sehirForm">
                    <div class="form-group">
                        <label for="sehir_adi">Şehir Seçin <span class="required">*</span></label>
                        <select name="sehir_adi" id="sehir_adi" class="form-control" required onchange="aciklamaYukle()">
                            <option value="">-- Şehir Seçin --</option>
                            <?php foreach ($turkiye_sehirler as $sehir => $aciklama): ?>
                                <?php if (!in_array($sehir, $mevcut_sehirler)): ?>
                                    <option value="<?php echo $sehir; ?>" data-aciklama="<?php echo $aciklama; ?>">
                                        <?php echo $sehir; ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">Sadece henüz eklenmemiş şehirler listelenmektedir.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="aciklama">Açıklama <span class="required">*</span></label>
                        <textarea name="aciklama" id="aciklama" class="form-control" rows="3" required placeholder="Şehir hakkında kısa açıklama..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="ikon">Font Awesome İkon Sınıfı <span class="required">*</span></label>
                        <input type="text" name="ikon" id="ikon" class="form-control" 
                               value="fas fa-map-marker-alt" required 
                               placeholder="Örn: fas fa-landmark">
                        <small class="form-text">
                            Font Awesome ikonları: 
                            <a href="https://fontawesome.com/icons" target="_blank">https://fontawesome.com/icons</a>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="gradient">CSS Gradient (Arka Plan Rengi) <span class="required">*</span></label>
                        <select id="gradient" name="gradient" class="form-control" required>
                            <option value="linear-gradient(135deg, #667eea 0%, #764ba2 100%)">Mor-Mavi</option>
                            <option value="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)">Pembe-Kırmızı</option>
                            <option value="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)">Açık Mavi</option>
                            <option value="linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)">Yeşil-Turkuaz</option>
                            <option value="linear-gradient(135deg, #fa709a 0%, #fee140 100%)">Pembe-Sarı</option>
                            <option value="linear-gradient(135deg, #30cfd0 0%, #330867 100%)">Turkuaz-Mor</option>
                            <option value="linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)">Pastel Mavi-Pembe</option>
                            <option value="linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)">Turuncu-Pembe</option>
                            <option value="linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%)">Leylak-Mavi</option>
                            <option value="linear-gradient(135deg, #fddb92 0%, #d1fdff 100%)">Sarı-Açık Mavi</option>
                        </select>
                        <div id="gradientPreview" style="margin-top: 0.5rem; height: 50px; border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Şehir Ekle ve Dosya Oluştur
                        </button>
                        <a href="sehirler.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Önemli Bilgiler</h2>
            </div>
            <div class="card-body">
                <ul style="line-height: 1.8;">
                    <li>Şehir eklendiğinde otomatik olarak <code>sehir_slug.php</code> dosyası oluşturulacaktır.</li>
                    <li>Slug otomatik oluşturulur (Türkçe karakterler İngilizce'ye çevrilir).</li>
                    <li>Şehir sayfası, restoranlar ve aktiviteler için hazır template ile gelir.</li>
                    <li>Ekledikten sonra aktivite ve restoran ekleyebilirsiniz.</li>
                    <li>Ana sayfada şehir otomatik olarak görünür hale gelecektir.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
    function aciklamaYukle() {
        const select = document.getElementById('sehir_adi');
        const aciklamaInput = document.getElementById('aciklama');
        const seciliOption = select.options[select.selectedIndex];
        
        if (seciliOption.value) {
            aciklamaInput.value = seciliOption.getAttribute('data-aciklama');
        }
    }
    
    document.getElementById('gradient').addEventListener('change', function() {
        document.getElementById('gradientPreview').style.background = this.value;
    });
    </script>
</body>
</html>

