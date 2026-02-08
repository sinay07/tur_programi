<?php
require_once 'config.php';

// Oturumu temizle
session_unset();
session_destroy();

// Anasayfaya yönlendir
redirect('index.php');
?>

