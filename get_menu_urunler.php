<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

$kategori_id = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;
$restoran_id = isset($_GET['restoran_id']) ? (int)$_GET['restoran_id'] : 0;

if ($kategori_id <= 0) {
    echo json_encode([]);
    exit;
}

// Ürünleri getir (görsel dahil)
$stmt = $db->prepare("
    SELECT id, urun_adi, aciklama, gorsel, porsiyon_bilgisi, kalori, sira 
    FROM menu_urunler 
    WHERE kategori_id = ? AND aktif = 1 
    ORDER BY sira ASC, urun_adi ASC
");
$stmt->execute([$kategori_id]);
$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Güvenlik için HTML karakterlerini temizle
foreach ($urunler as &$urun) {
    $urun['urun_adi'] = htmlspecialchars($urun['urun_adi'], ENT_QUOTES, 'UTF-8');
    $urun['aciklama'] = htmlspecialchars($urun['aciklama'] ?? '', ENT_QUOTES, 'UTF-8');
    $urun['porsiyon_bilgisi'] = htmlspecialchars($urun['porsiyon_bilgisi'] ?? '', ENT_QUOTES, 'UTF-8');
    $urun['kalori'] = htmlspecialchars($urun['kalori'] ?? '', ENT_QUOTES, 'UTF-8');
    $urun['gorsel'] = htmlspecialchars($urun['gorsel'] ?? '', ENT_QUOTES, 'UTF-8');
}

echo json_encode($urunler);
?>


