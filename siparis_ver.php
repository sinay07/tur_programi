<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('sepet.php');
}

// Sepet boş mu kontrol et
if (!isset($_SESSION['sepet']) || count($_SESSION['sepet']) == 0) {
    redirect('sepet.php');
}

$koltuk_no = sanitize($_POST['koltuk_no'] ?? '');
$siparis_notu = sanitize($_POST['siparis_notu'] ?? '');
$kullanici_id = $_SESSION['kullanici_id'];

if (empty($koltuk_no)) {
    $_SESSION['hata'] = 'Koltuk numarası zorunludur.';
    redirect('sepet.php');
}

try {
    $db->beginTransaction();
    
    // Sepetteki restoranları bul (her restoran için ayrı sipariş)
    $restoranlar = [];
    foreach ($_SESSION['sepet'] as $item) {
        if (!isset($restoranlar[$item['restoran_id']])) {
            $restoranlar[$item['restoran_id']] = [
                'restoran_id' => $item['restoran_id'],
                'restoran_adi' => $item['restoran_adi'],
                'urunler' => []
            ];
        }
        $restoranlar[$item['restoran_id']]['urunler'][] = $item;
    }
    
    // Her restoran için sipariş oluştur
    foreach ($restoranlar as $restoran) {
        // Toplam fiyatı hesapla
        $toplam_fiyat = 0;
        foreach ($restoran['urunler'] as $urun) {
            $toplam_fiyat += $urun['fiyat'] * $urun['adet'];
        }
        
        // Siparişi kaydet
        $stmt = $db->prepare("
            INSERT INTO siparisler (kullanici_id, restoran_id, koltuk_no, toplam_fiyat, siparis_notu, durum) 
            VALUES (?, ?, ?, ?, ?, 'beklemede')
        ");
        $stmt->execute([$kullanici_id, $restoran['restoran_id'], $koltuk_no, $toplam_fiyat, $siparis_notu]);
        
        $siparis_id = $db->lastInsertId();
        
        // Sipariş ürünlerini kaydet
        foreach ($restoran['urunler'] as $urun) {
            $urun_toplam = $urun['fiyat'] * $urun['adet'];
            $stmt = $db->prepare("
                INSERT INTO siparis_urunler (siparis_id, urun_id, urun_adi, kategori_adi, adet, birim_fiyat, toplam_fiyat) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $siparis_id,
                $urun['urun_id'],
                $urun['urun_adi'],
                $urun['kategori_adi'],
                $urun['adet'],
                $urun['fiyat'],
                $urun_toplam
            ]);
        }
    }
    
    $db->commit();
    
    // Sepeti temizle
    $_SESSION['sepet'] = [];
    $_SESSION['basari'] = 'Siparişiniz başarıyla alındı! Teşekkür ederiz.';
    redirect('siparislerim.php');
    
} catch (PDOException $e) {
    $db->rollBack();
    $_SESSION['hata'] = 'Sipariş verilirken bir hata oluştu. Lütfen tekrar deneyin.';
    redirect('sepet.php');
}
?>



