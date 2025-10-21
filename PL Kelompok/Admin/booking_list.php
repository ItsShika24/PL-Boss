<?php

require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$sql = "SELECT b.*, u.nama AS nama_user, u.email, f.nama AS nama_film, 
        s.nama_studio, jt.tanggal_tayang, jt.jam_tayang, k.kode_kursi
        FROM booking b
        JOIN user u ON b.id_user = u.id_user
        JOIN jadwal_tayang jt ON b.id_jadwal = jt.id_jadwal
        JOIN film f ON jt.id_film = f.id_film
        JOIN studio s ON jt.id_studio = s.id_studio
        JOIN kursi k ON b.id_kursi = k.id_kursi
        ORDER BY b.id_booking DESC";

$stmt = $pdo->query($sql);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Booking</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; }
        .container { max-width: 1400px; margin: 30px auto; background: white; padding: 30px; border-radius: 10px; }
        .btn { padding: 10px 20px; background: #667eea; color: white; text-decoration: none; display: inline-block; border-radius: 5px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:hover { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🎟️ Data Booking Tiket</h2>
    </div>
    
    <div class="container">
        <a href="index.php" class="btn">← Kembali</a>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Film</th>
                    <th>Studio</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Kursi</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $b): ?>
                <tr>
                    <td><?= $b['id_booking'] ?></td>
                    <td><?= htmlspecialchars($b['nama_user']) ?></td>
                    <td><?= $b['email'] ?></td>
                    <td><?= htmlspecialchars($b['nama_film']) ?></td>
                    <td><?= $b['nama_studio'] ?></td>
                    <td><?= date('d/m/Y', strtotime($b['tanggal_tayang'])) ?></td>
                    <td><?= date('H:i', strtotime($b['jam_tayang'])) ?></td>
                    <td><?= $b['kode_kursi'] ?></td>
                    <td>Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
