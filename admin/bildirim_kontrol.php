<?php
require_once '../config.php';
requireAdmin();

// Son bildirim kontrolü (session'da sakla)
if (!isset($_SESSION['son_bildirim_kontrol'])) {
    $_SESSION['son_bildirim_kontrol'] = time();
}

// Son kontrolden sonra yeni bildirim var mı?
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM admin_bildirimler 
    WHERE okundu = 0 
    AND olusturma_tarihi > FROM_UNIXTIME(?)
");
$stmt->execute([$_SESSION['son_bildirim_kontrol']]);
$result = $stmt->fetch();

// Son kontrol zamanını güncelle
$_SESSION['son_bildirim_kontrol'] = time();

header('Content-Type: application/json');
echo json_encode([
    'yeni_bildirim' => $result['total'] > 0,
    'adet' => $result['total']
]);
?>


