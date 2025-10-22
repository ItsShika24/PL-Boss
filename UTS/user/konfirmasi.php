<?php
session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Cek apakah ada data booking di session
if(!isset($_SESSION['booking_data'])) {
    header('Location: index.php');
    exit;
}

$booking_data = $_SESSION['booking_data'];

// Ambil data film
$stmt = $pdo->prepare("SELECT * FROM film WHERE id_film = ?");
$stmt->execute([$booking_data['id_film']]);
$film = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil data jadwal
$stmt = $pdo->prepare("
    SELECT jt.*, s.nama_studio 
    FROM jadwal_tayang jt 
    JOIN studio s ON jt.id_studio = s.id_studio 
    WHERE jt.id_jadwal = ?
");
$stmt->execute([$booking_data['id_jadwal']]);
$jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM user WHERE id_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Proses konfirmasi pemesanan
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['konfirmasi'])) {
    try {
        $pdo->beginTransaction();
        
        // Simpan booking utama
        $stmt = $pdo->prepare("
            INSERT INTO booking (id_user, id_jadwal, tanggal_pesan, jumlah_tiket, total_harga) 
            VALUES (?, ?, CURDATE(), ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $booking_data['id_jadwal'],
            $booking_data['jumlah_tiket'],
            $booking_data['total_harga']
        ]);
        
        $id_booking = $pdo->lastInsertId();
        
        // Simpan detail kursi
        foreach($booking_data['kursi'] as $kode_kursi) {
            // Dapatkan id_kursi berdasarkan kode_kursi
            $stmt = $pdo->prepare("
                SELECT id_kursi FROM kursi 
                WHERE kode_kursi = ? AND id_studio = ?
            ");
            $stmt->execute([$kode_kursi, $jadwal['id_studio']]);
            $kursi = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($kursi) {
                // Update booking dengan id_kursi (untuk kursi pertama)
                if($kode_kursi === $booking_data['kursi'][0]) {
                    $stmt = $pdo->prepare("
                        UPDATE booking SET id_kursi = ? WHERE id_booking = ?
                    ");
                    $stmt->execute([$kursi['id_kursi'], $id_booking]);
                }
                
                // Update status kursi menjadi terisi
                $stmt = $pdo->prepare("
                    UPDATE kursi SET status = 'terisi' WHERE id_kursi = ?
                ");
                $stmt->execute([$kursi['id_kursi']]);
                
                // Simpan booking tambahan untuk kursi lainnya
                if($kode_kursi !== $booking_data['kursi'][0]) {
                    $stmt = $pdo->prepare("
                        INSERT INTO booking (id_user, id_jadwal, id_kursi, tanggal_pesan, jumlah_tiket, total_harga) 
                        VALUES (?, ?, ?, CURDATE(), 1, ?)
                    ");
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $booking_data['id_jadwal'],
                        $kursi['id_kursi'],
                        $film['harga']
                    ]);
                }
            }
        }
        
        $pdo->commit();
        
        // Simpan ID booking untuk halaman sukses
        $_SESSION['last_booking_id'] = $id_booking;
        
        // Hapus session booking dan redirect ke halaman sukses
        unset($_SESSION['booking_data']);
        header('Location: booking_success.php');
        exit;
        
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Gagal memproses pemesanan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f5f5;
            color: #333;
        }

        .navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
        }

        .confirmation-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .confirmation-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .confirmation-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }

        .confirmation-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .confirmation-subtitle {
            color: #666;
            font-size: 16px;
        }

        .section {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
        }

        .kursi-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .kursi-badge {
            background: #667eea;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .price-summary {
            background: #e8f5e8;
            border: 2px solid #2ecc71;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #d4edda;
        }

        .total-price {
            font-size: 20px;
            font-weight: bold;
            color: #2ecc71;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #d4edda;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-confirm {
            background: #2ecc71;
            color: white;
        }

        .btn-confirm:hover {
            background: #27ae60;
        }

        .btn-cancel {
            background: #e74c3c;
            color: white;
        }

        .btn-cancel:hover {
            background: #c0392b;
        }

        .btn-back {
            background: #95a5a6;
            color: white;
        }

        .btn-back:hover {
            background: #7f8c8d;
        }

        .error-message {
            background: #ffeaa7;
            color: #d63031;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .confirmation-card {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🎬 Bioskop Online</h2>
        <div class="nav-links">
            <span>Halo, <?= htmlspecialchars($_SESSION['user_nama']) ?></span>
            <a href="index.php">Beranda</a>
            <a href="my_booking.php">Pesanan Saya</a>
        </div>
    </div>

    <div class="container">
        <?php if(isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <div class="confirmation-card">
            <div class="confirmation-header">
                <div class="confirmation-icon">📋</div>
                <div class="confirmation-title">Konfirmasi Pemesanan</div>
                <div class="confirmation-subtitle">Silakan periksa detail pemesanan Anda sebelum melanjutkan</div>
            </div>

            <div class="section">
                <div class="section-title">🎬 Informasi Film</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Film</div>
                        <div class="info-value"><?= htmlspecialchars($film['nama']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Genre</div>
                        <div class="info-value"><?= $film['genre'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Durasi</div>
                        <div class="info-value"><?= $film['durasi'] ?> menit</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">📅 Jadwal Tayang</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Tanggal</div>
                        <div class="info-value"><?= date('d F Y', strtotime($jadwal['tanggal_tayang'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jam</div>
                        <div class="info-value"><?= date('H:i', strtotime($jadwal['jam_tayang'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Studio</div>
                        <div class="info-value"><?= $jadwal['nama_studio'] ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">👤 Data Pemesan</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nama</div>
                        <div class="info-value"><?= htmlspecialchars($user['nama']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Telepon</div>
                        <div class="info-value"><?= htmlspecialchars($user['no_telp']) ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">💺 Kursi Terpilih</div>
                <div class="info-item">
                    <div class="info-label">Jumlah Tiket</div>
                    <div class="info-value"><?= $booking_data['jumlah_tiket'] ?> tiket</div>
                </div>
                <div class="kursi-list">
                    <?php foreach($booking_data['kursi'] as $kursi): ?>
                        <div class="kursi-badge"><?= $kursi ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="price-summary">
                <div class="section-title">💰 Ringkasan Pembayaran</div>
                <div class="price-item">
                    <span>Harga per tiket:</span>
                    <span>Rp <?= number_format($film['harga'], 0, ',', '.') ?></span>
                </div>
                <div class="price-item">
                    <span>Jumlah tiket:</span>
                    <span><?= $booking_data['jumlah_tiket'] ?></span>
                </div>
                <div class="price-item total-price">
                    <span>Total Pembayaran:</span>
                    <span>Rp <?= number_format($booking_data['total_harga'], 0, ',', '.') ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="action-buttons">
                    <a href="booking.php?film_id=<?= $booking_data['id_film'] ?>" class="btn btn-back">
                        Kembali
                    </a>
                    <button type="submit" name="konfirmasi" class="btn btn-confirm">
                        ✅ Konfirmasi & Bayar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
