<?php
// ============================================
// FILE: config.php
// Konfigurasi Koneksi Database & Sesi
// ============================================

$host = 'localhost';
$dbname = 'bioskop_online';
$username = 'root';
$password = ''; // Pastikan password ini sesuai dengan konfigurasi MAMP Anda

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Mengatur mode error agar PDO melempar Exception pada kesalahan
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Menghentikan skrip jika koneksi gagal
    die("Koneksi gagal: " . $e->getMessage());
}

// Function untuk check login
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function getUserRole() {
    if (isset($_SESSION['admin_id'])) {
        return 'admin';
    } elseif (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return null;
}


// Memulai Sesi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}