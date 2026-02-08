<?php
require_once '../config.php';
requireAdmin();

$urun_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$restoran_id = isset($_GET['restoran_id']) ? (int)$_GET['restoran_id'] : 0;

if ($urun_id > 0) {
    try {
        $stmt = $db->prepare("DELETE FROM menu_urunler WHERE id = ?");
        $stmt->execute([$urun_id]);
    } catch (PDOException $e) {
        // Hata olsa bile yönlendir
    }
}

if ($restoran_id > 0) {
    redirect('restoran_duzenle.php?id=' . $restoran_id);
} else {
    redirect('restoranlar.php');
}
?>



