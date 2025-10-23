<?php
// proses_booking.php
session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if($_POST && isset($_POST['id_jadwal'])) {
    $id_user = $_SESSION['user_id'];
    $id_jadwal = $_POST['id_jadwal'];
    $id_kursi = $_POST['id_kursi'];
    $total_harga = $_POST['total_harga'];
    
    try {
        // 1. INSERT ke tabel booking
        $sql = "INSERT INTO booking (id_user, id_jadwal, id_kursi, total_harga, tanggal_pesan) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_user, $id_jadwal, $id_kursi, $total_harga]);
        
        // 2. Ambil ID booking yang baru dibuat
        $last_booking_id = $pdo->lastInsertId();
        
        // 3. Set session untuk success message
        $_SESSION['booking_success'] = "Booking berhasil! Total: Rp " . number_format($total_harga, 0, ',', '.');
        $_SESSION['last_booking_id'] = $last_booking_id;
        
        // 4. Redirect ke success page atau langsung ke my_booking
        header('Location: booking_success.php'); // atau my_booking.php
        exit;
        
    } catch(PDOException $e) {
        $_SESSION['error'] = "Gagal melakukan booking: " . $e->getMessage();
        header('Location: booking.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>