<?php
require_once '../config.php';
requireAdmin();

$format = $_GET['format'] ?? 'csv';
$tarih = $_GET['tarih'] ?? date('Y-m-d');
$sekme = $_GET['sekme'] ?? 'aktif';
$ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

// Siparişleri getir
if (!empty($ids)) {
    // Seçilen siparişleri getir
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $db->prepare("
        SELECT s.*, k.ad_soyad, k.telefon, r.baslik as restoran_adi
        FROM siparisler s
        INNER JOIN kullanicilar k ON s.kullanici_id = k.id
        INNER JOIN restoranlar r ON s.restoran_id = r.id
        WHERE s.id IN ($placeholders)
        ORDER BY s.koltuk_no ASC
    ");
    $stmt->execute($ids);
} else {
    // Tüm siparişleri getir
    if ($sekme == 'aktif') {
        $stmt = $db->prepare("
            SELECT s.*, k.ad_soyad, k.telefon, r.baslik as restoran_adi
            FROM siparisler s
            INNER JOIN kullanicilar k ON s.kullanici_id = k.id
            INNER JOIN restoranlar r ON s.restoran_id = r.id
            WHERE DATE(s.siparis_tarihi) = ?
            AND s.durum IN ('beklemede', 'hazirlaniyor')
            ORDER BY s.koltuk_no ASC
        ");
    } else {
        $stmt = $db->prepare("
            SELECT s.*, k.ad_soyad, k.telefon, r.baslik as restoran_adi
            FROM siparisler s
            INNER JOIN kullanicilar k ON s.kullanici_id = k.id
            INNER JOIN restoranlar r ON s.restoran_id = r.id
            WHERE DATE(s.siparis_tarihi) = ?
            AND s.durum IN ('teslim_edildi', 'iptal')
            ORDER BY s.koltuk_no ASC
        ");
    }
    $stmt->execute([$tarih]);
}
$siparisler = $stmt->fetchAll();

if ($format == 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    $filename = !empty($ids) ? 'secili_siparisler_' . date('YmdHis') : 'siparisler_' . $sekme . '_' . $tarih;
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM ekle (Türkçe karakterler için)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Başlıklar - Her kategori için ayrı sütun
    fputcsv($output, ['Siparis No', 'Yolcu Adi Soyadi', 'Telefon', 'Koltuk No', 'Restoran', 'Ana Yemek', 'Icecek', 'Tatli', 'Diger', 'Toplam Fiyat', 'Durum', 'Siparis Zamani'], ';');
    
    foreach ($siparisler as $siparis) {
        // Sipariş ürünlerini getir
        $stmt = $db->prepare("SELECT * FROM siparis_urunler WHERE siparis_id = ?");
        $stmt->execute([$siparis['id']]);
        $urunler = $stmt->fetchAll();
        
        // Ürünleri kategorilere ayır
        $ana_yemek = [];
        $icecek = [];
        $tatli = [];
        $diger = [];
        
        foreach ($urunler as $urun) {
            $kategori_lower = strtolower($urun['kategori_adi']);
            $urun_text = $urun['urun_adi'] . ' x' . $urun['adet'];
            
            if (strpos($kategori_lower, 'kebap') !== false || 
                strpos($kategori_lower, 'ana') !== false || 
                strpos($kategori_lower, 'yemek') !== false ||
                strpos($kategori_lower, 'ara') !== false) {
                $ana_yemek[] = $urun_text;
            } elseif (strpos($kategori_lower, 'icecek') !== false || 
                      strpos($kategori_lower, 'içecek') !== false) {
                $icecek[] = $urun_text;
            } elseif (strpos($kategori_lower, 'tatli') !== false || 
                      strpos($kategori_lower, 'tatlı') !== false) {
                $tatli[] = $urun_text;
            } else {
                $diger[] = $urun_text;
            }
        }
        
        $durum_metinler = [
            'beklemede' => 'Beklemede',
            'hazirlaniyor' => 'Hazirlaniyor',
            'yolda' => 'Yolda',
            'teslim_edildi' => 'Teslim Edildi',
            'iptal' => 'Iptal Edildi'
        ];
        
        fputcsv($output, [
            $siparis['id'],
            $siparis['ad_soyad'],
            $siparis['telefon'],
            $siparis['koltuk_no'],
            $siparis['restoran_adi'],
            implode(' & ', $ana_yemek) ?: '-',
            implode(' & ', $icecek) ?: '-',
            implode(' & ', $tatli) ?: '-',
            implode(' & ', $diger) ?: '-',
            number_format($siparis['toplam_fiyat'], 2) . ' TL',
            $durum_metinler[$siparis['durum']],
            date('d.m.Y H:i', strtotime($siparis['siparis_tarihi']))
        ], ';');
    }
    
    fclose($output);
    exit;
}

if ($format == 'pdf') {
    // PDF Export - Kompakt Tablo Formatı
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <title>Siparişler - <?php echo $tarih; ?></title>
        <style>
            @page {
                margin: 15mm;
            }
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                font-size: 10px;
                margin: 0;
                color: #2d3748;
            }
            h1 {
                color: #667eea;
                border-bottom: 3px solid #667eea;
                padding-bottom: 8px;
                margin-bottom: 15px;
                font-size: 18px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
            }
            th {
                background: #667eea;
                color: white;
                padding: 8px 6px;
                text-align: left;
                font-weight: 600;
                font-size: 9px;
                border: 1px solid #5568d3;
            }
            td {
                border: 1px solid #e2e8f0;
                padding: 6px 5px;
                font-size: 9px;
                vertical-align: top;
            }
            tr:nth-child(even) {
                background: #f7fafc;
            }
            .urun-item {
                margin-bottom: 4px;
                padding: 3px 0;
                border-bottom: 1px dashed #e2e8f0;
            }
            .urun-item:last-child {
                border-bottom: none;
            }
            .urun-baslik {
                font-weight: bold;
                color: #2d3748;
                display: block;
            }
            .urun-detay {
                color: #718096;
                font-size: 8px;
            }
            .total-cell {
                font-weight: bold;
                color: #667eea;
                font-size: 10px;
            }
            .grand-total-row {
                background: #48bb78 !important;
                color: white !important;
                font-weight: bold;
                font-size: 11px;
            }
            .koltuk {
                background: #667eea;
                color: white;
                padding: 3px 8px;
                border-radius: 10px;
                font-weight: bold;
                display: inline-block;
            }
            .footer {
                margin-top: 20px;
                padding: 15px;
                background: #f7fafc;
                border-radius: 5px;
                font-size: 9px;
                display: flex;
                justify-content: space-between;
            }
            @media print {
                button, a.no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <h1>🍽️ Sipariş Raporu - <?php echo date('d.m.Y', strtotime($tarih)); ?></h1>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 15%;">Yolcu Adı</th>
                    <th style="width: 6%;">Koltuk</th>
                    <th style="width: 35%;">Siparişler (Birim Fiyat)</th>
                    <th style="width: 12%;">Telefon</th>
                    <th style="width: 8%;">Saat</th>
                    <th style="width: 10%;">Toplam</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $genel_toplam = 0;
                $sira = 1;
                foreach ($siparisler as $siparis): 
                    // Sipariş ürünlerini getir
                    $stmt = $db->prepare("SELECT * FROM siparis_urunler WHERE siparis_id = ? ORDER BY kategori_adi, urun_adi");
                    $stmt->execute([$siparis['id']]);
                    $urunler = $stmt->fetchAll();
                    
                    $genel_toplam += $siparis['toplam_fiyat'];
                ?>
                    <tr>
                        <td style="text-align: center;"><strong><?php echo $sira++; ?></strong></td>
                        <td><strong><?php echo sanitize($siparis['ad_soyad']); ?></strong></td>
                        <td style="text-align: center;">
                            <span class="koltuk"><?php echo sanitize($siparis['koltuk_no']); ?></span>
                        </td>
                        <td>
                            <?php foreach ($urunler as $urun): ?>
                                <div class="urun-item">
                                    <span class="urun-baslik">
                                        • <?php echo sanitize($urun['urun_adi']); ?>
                                    </span>
                                    <span class="urun-detay">
                                        <?php echo $urun['adet']; ?>x × <?php echo number_format($urun['birim_fiyat'], 2); ?> TL 
                                        = <strong><?php echo number_format($urun['toplam_fiyat'], 2); ?> TL</strong>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td><?php echo sanitize($siparis['telefon']); ?></td>
                        <td style="text-align: center;"><?php echo date('H:i', strtotime($siparis['siparis_tarihi'])); ?></td>
                        <td class="total-cell" style="text-align: right;">
                            <strong><?php echo number_format($siparis['toplam_fiyat'], 2); ?> TL</strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <tr class="grand-total-row">
                    <td colspan="6" style="text-align: right; padding: 10px;">
                        💰 GENEL TOPLAM (<?php echo count($siparisler); ?> Sipariş):
                    </td>
                    <td style="text-align: right; padding: 10px; font-size: 12px;">
                        <strong><?php echo number_format($genel_toplam, 2); ?> TL</strong>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="footer">
            <div>
                <strong>📊 Rapor Bilgileri</strong><br>
                Tarih: <?php echo date('d.m.Y', strtotime($tarih)); ?><br>
                Oluşturma: <?php echo date('d.m.Y H:i:s'); ?>
            </div>
            <div style="text-align: right;">
                <strong>🏢 <?php echo SITE_NAME; ?></strong><br>
                Admin Raporu
            </div>
        </div>
        
        <button onclick="window.print()" style="background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin-top: 15px;">
            🖨️ Yazdır / PDF Olarak Kaydet
        </button>
        <a href="siparisler.php?sekme=<?php echo $sekme; ?>&tarih=<?php echo $tarih; ?>" class="no-print" style="background: #718096; color: white; padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; margin-left: 10px;">
            ← Geri Dön
        </a>
    </body>
    </html>
    <?php
    exit;
}
?>


