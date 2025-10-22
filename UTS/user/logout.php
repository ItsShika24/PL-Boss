<?php
// Start session hanya jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua session
session_destroy();

// Redirect ke login
header("Location: login.php");
exit();
?>