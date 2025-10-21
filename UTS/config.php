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

// Memulai Sesi
session_start();
?>