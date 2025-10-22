<?php
// Start session hanya jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Buat koneksi database manual untuk user
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "bioskop_online";

$user_conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$user_conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data booking user
$bookings = [];
$sql = "SELECT b.*, f.nama as nama_film, f.harga, j.tanggal_tayang, j.jam_tayang, s.nama_studio
        FROM booking b
        JOIN jadwal_tayang j ON b.id_jadwal = j.id_jadwal
        JOIN film f ON j.id_film = f.id_film
        JOIN studio s ON j.id_studio = s.id_studio
        WHERE b.id_user = {$_SESSION['user_id']}
        ORDER BY b.tanggal_pesan DESC";
$result = mysqli_query($user_conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - Bioskop Online</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .bookings-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.2em;
        }

        .booking-item {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
            transition: all 0.3s;
        }

        .booking-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .booking-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }

        .movie-title {
            font-size: 1.4em;
            color: #333;
            font-weight: bold;
        }

        .booking-id {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
        }

        .booking-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .total-price {
            font-size: 1.3em;
            font-weight: bold;
            color: #667eea;
        }

        .booking-status {
            background: #4caf50;
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .no-bookings {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-bookings h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .booking-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">⬅ Kembali ke Daftar Film</a>
        
        <div class="bookings-card">
            <h1 class="page-title">🎫 Tiket Saya</h1>
            
            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    <h3>📭 Belum ada pemesanan</h3>
                    <p>Silakan pesan tiket film favorit Anda terlebih dahulu</p>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-item">
                        <div class="booking-header">
                            <div class="movie-title"><?php echo htmlspecialchars($booking['nama_film']); ?></div>
                            <div class="booking-id">#<?php echo $booking['id_booking']; ?></div>
                        </div>
                        
                        <div class="booking-details">
                            <div class="detail-item">
                                <span class="detail-label">📅 Tanggal Tayang</span>
                                <span class="detail-value"><?php echo date('d M Y', strtotime($booking['tanggal_tayang'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">⏰ Jam Tayang</span>
                                <span class="detail-value"><?php echo date('H:i', strtotime($booking['jam_tayang'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">🎭 Studio</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['nama_studio']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">💺 Kursi</span>
                                <span class="detail-value"><?php echo $booking['kode_kursi'] ?? '-'; ?></span>
                            </div>
                        </div>
                        
                        <div class="booking-footer">
                            <div class="total-price">
                                Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
                            </div>
                            <div class="booking-status">
                                ✅ Terkonfirmasi
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>