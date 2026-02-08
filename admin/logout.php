<?php
require_once '../config.php';

// Oturumu temizle
session_unset();
session_destroy();

// Admin login sayfasına yönlendir
redirect('login.php');
?>

