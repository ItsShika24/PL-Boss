<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil data film beserta jadwal tayang
$sql = "SELECT f.*, 
               GROUP_CONCAT(CONCAT(jt.tanggal_tayang, ' ', jt.jam_tayang) SEPARATOR ' | ') as jadwal
        FROM film f
        LEFT JOIN jadwal_tayang jt ON f.id_film = jt.id_film
        GROUP BY f.id_film
        ORDER BY f.id_film DESC";
        
$stmt = $pdo->query($sql);
$films = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h2 { margin: 0; }
        .navbar a { color: white; text-decoration: none; padding: 10px 15px; margin: 0 5px; background: rgba(255,255,255,0.2); border-radius: 5px; }
        .container { max-width: 1400px; margin: 30px auto; background: white; padding: 30px; border-radius: 10px; }
        .btn { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn-danger { background: #f44336; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:hover { background: #f5f5f5; }
        img { border-radius: 5px; width: 80px; height: auto; }
        .jadwal-list { max-width: 200px; }
        .jadwal-item { 
            background: #f8f9fa; 
            padding: 5px 8px; 
            margin: 2px 0; 
            border-radius: 4px; 
            font-size: 12px;
            border-left: 3px solid #667eea;
        }
        .status-badge { 
            padding: 4px 8px; 
            border-radius: 12px; 
            font-size: 12px; 
            font-weight: bold; 
        }
        .status-now { background: #4CAF50; color: white; }
        .status-coming { background: #FF9800; color: white; }
        .status-ended { background: #f44336; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🎬 Admin Bioskop</h2>
        <div>
            <span>Halo, <?= $_SESSION['admin_nama'] ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h1>Kelola Film</h1>
        <a href="tambah_film.php" class="btn">+ Tambah Film</a>
        <a href="booking_list.php" class="btn">🎟️ Data Booking</a>
        <a href="kelola_jadwal.php" class="btn">📅 Kelola Jadwal</a>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Poster</th>
                    <th>Nama Film</th>
                    <th>Genre</th>
                    <th>Durasi</th>
                    <th>Status</th>
                    <th>Jadwal Tayang</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($films as $film): ?>
                <tr>
                    <td><?= $film['id_film'] ?></td>
                    <td>
                        <?php if (!empty($film['poster'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($film['poster']) ?>" alt="Poster">
                        <?php else: ?>
                            <span>–</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($film['nama']) ?></td>
                    <td><?= $film['genre'] ?></td>
                    <td><?= $film['durasi'] ?> menit</td>
                    <td>
                        <?php 
                        $status_class = 'status-now';
                        if($film['jadwal_tayang'] == 'Akan Tayang') {
                            $status_class = 'status-coming';
                        } elseif($film['jadwal_tayang'] == 'Selesai Tayang') {
                            $status_class = 'status-ended';
                        }
                        ?>
                        <span class="status-badge <?= $status_class ?>">
                            <?= $film['jadwal_tayang'] ?>
                        </span>
                    </td>
                    <td class="jadwal-list">
                        <?php if (!empty($film['jadwal'])): ?>
                            <?php 
                            $jadwals = explode(' | ', $film['jadwal']);
                            foreach($jadwals as $jadwal):
                                list($tanggal, $waktu) = explode(' ', $jadwal, 2);
                                $tanggal_formatted = date('d/m/Y', strtotime($tanggal));
                                $waktu_formatted = date('H:i', strtotime($waktu));
                            ?>
                                <div class="jadwal-item">
                                    <?= $tanggal_formatted ?> - <?= $waktu_formatted ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">Belum ada jadwal</span>
                        <?php endif; ?>
                    </td>
                    <td>Rp <?= number_format($film['harga'], 0, ',', '.') ?></td>
                    <td>
                        <a href="edit_film.php?id=<?= $film['id_film'] ?>" class="btn">Edit</a>
                        <a href="hapus_film.php?id=<?= $film['id_film'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>