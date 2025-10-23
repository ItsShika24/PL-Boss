<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Ambil data booking user saja
$sql = "SELECT b.*, f.nama AS nama_film, s.nama_studio, jt.tanggal_tayang, jt.jam_tayang, k.kode_kursi
        FROM booking b
        JOIN jadwal_tayang jt ON b.id_jadwal = jt.id_jadwal
        JOIN film f ON jt.id_film = f.id_film
        JOIN studio s ON jt.id_studio = s.id_studio
        JOIN kursi k ON b.id_kursi = k.id_kursi
        WHERE b.id_user = ?
        ORDER BY b.id_booking DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            background: #f5f5f5;
        }

        .navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        .success {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .booking-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f9f9f9;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            /* PERBAIKI: ganti 'between' jadi 'space-between' */
            align-items: center;
            margin-bottom: 15px;
        }

        .booking-id {
            font-weight: bold;
            color: #667eea;
            font-size: 18px;
        }

        .booking-date {
            color: #666;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            margin-bottom: 8px;
        }

        .detail-label {
            font-weight: bold;
            color: #333;
        }

        .detail-value {
            color: #666;
        }

        .no-booking {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .btn-pesan {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <h2>🎬 Bioskop Online</h2>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <span>Halo, <?= htmlspecialchars($_SESSION['user_nama']) ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>🎟️ Pesanan Saya</h1>

        <?php if (isset($_SESSION['booking_success'])): ?>
            <div class="success">
                <?= $_SESSION['booking_success'] ?>
            </div>
            <?php unset($_SESSION['booking_success']); ?>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="no-booking">
                <div>📭</div>
                <h3>Belum ada pesanan</h3>
                <p>Silakan pesan tiket film favorit Anda terlebih dahulu.</p>
                <a href="index.php" class="btn-pesan">Pesan Tiket</a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-id">Booking #<?= $booking['id_booking'] ?></div>
                        <div class="booking-date">Tanggal Pesan: <?= date('d/m/Y', strtotime($booking['tanggal_pesan'])) ?></div>
                    </div>

                    <div class="booking-details">
                        <div class="detail-item">
                            <div class="detail-label">Film</div>
                            <div class="detail-value"><?= htmlspecialchars($booking['nama_film']) ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Studio</div>
                            <div class="detail-value"><?= $booking['nama_studio'] ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Tanggal Tayang</div>
                            <div class="detail-value"><?= date('d/m/Y', strtotime($booking['tanggal_tayang'])) ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Jam Tayang</div>
                            <div class="detail-value"><?= date('H:i', strtotime($booking['jam_tayang'])) ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Kursi</div>
                            <div class="detail-value"><?= $booking['kode_kursi'] ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Total Harga</div>
                            <div class="detail-value" style="color: #2ecc71; font-weight: bold;">
                                Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="button-group">
            <a href="index.php" class="btn btn-secondary">Pesan Tiket Lagi</a>
        </div>
    </div>
</body>

</html>