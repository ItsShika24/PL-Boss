<?php
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data booking user
try {
    $stmt = $pdo->prepare("
        SELECT 
            b.id_booking,
            f.nama AS nama_film,
            f.poster,
            s.nama_studio,
            jt.tanggal_tayang,
            jt.jam_tayang,
            k.kode_kursi,
            b.tanggal_pesan,
            b.total_harga
        FROM booking b
        JOIN jadwal_tayang jt ON b.id_jadwal = jt.id_jadwal
        JOIN film f ON jt.id_film = f.id_film
        JOIN studio s ON jt.id_studio = s.id_studio
        JOIN kursi k ON b.id_kursi = k.id_kursi
        WHERE b.id_user = ?
        ORDER BY b.tanggal_pesan DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - Bioskop Online</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        .header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1000px; margin: 20px auto; padding: 0 20px; }
        .booking-card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .booking-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .booking-id { font-size: 14px; color: #666; }
        .film-info { display: flex; gap: 20px; }
        .film-poster { width: 100px; height: 150px; object-fit: cover; }
        .film-details { flex: 1; }
        .btn { display: inline-block; padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .nav-links a { color: white; text-decoration: none; margin-left: 15px; }
        .no-data { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bioskop Online</h1>
        <div class="nav-links">
            <a href="index.php">Daftar Film</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Tiket Saya</h2>
        
        <?php if (isset($error)): ?>
            <div style="color: red; margin-bottom: 20px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="no-data">
                <h3>Belum ada tiket yang dipesan</h3>
                <p><a href="index.php" class="btn">Pesan Tiket Sekarang</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
            <div class="booking-card">
                <div class="booking-header">
                    <h3><?php echo $booking['nama_film']; ?></h3>
                    <span class="booking-id">ID: <?php echo $booking['id_booking']; ?></span>
                </div>
                
                <div class="film-info">
                    <img src="../images/<?php echo $booking['poster']; ?>" alt="<?php echo $booking['nama_film']; ?>" class="film-poster" onerror="this.src='../images/default.jpg'">
                    <div class="film-details">
                        <p><strong>Studio:</strong> <?php echo $booking['nama_studio']; ?></p>
                        <p><strong>Tanggal Tayang:</strong> <?php echo date('d M Y', strtotime($booking['tanggal_tayang'])); ?></p>
                        <p><strong>Jam:</strong> <?php echo date('H:i', strtotime($booking['jam_tayang'])); ?></p>
                        <p><strong>Kursi:</strong> <?php echo $booking['kode_kursi']; ?></p>
                        <p><strong>Tanggal Pesan:</strong> <?php echo date('d M Y', strtotime($booking['tanggal_pesan'])); ?></p>
                        <p><strong>Total Harga:</strong> Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>