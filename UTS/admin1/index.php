<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
// Tampilkan pesan sukses
if(isset($_GET['success'])) {
    $message = '';
    switch($_GET['success']) {
        case 'tambah':
            $message = "Film berhasil ditambahkan!";
            break;
        case 'edit':
            $message = "Film berhasil diperbarui!";
            break;
        case 'hapus':
            $message = "Film berhasil dihapus!";
            break;
    }
    
    if($message) {
        echo '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">✅ ' . $message . '</div>';
    }
}
$stmt = $pdo->query("SELECT * FROM film ORDER BY id_film DESC");
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
        .container { max-width: 1200px; margin: 30px auto; background: white; padding: 30px; border-radius: 10px; }
        .btn { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn-danger { background: #f44336; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:hover { background: #f5f5f5; }
        img { border-radius: 5px; width: 80px; height: auto; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 30px 0; }
        .menu-card { background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; border-left: 4px solid #667eea; }
        .menu-card h3 { margin: 0 0 10px 0; color: #333; }
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
        <h1>Dashboard Admin</h1>
        
        <!-- Menu Admin -->
        <div class="menu-grid">
            <div class="menu-card">
                <h3>🎬 Film</h3>
                <a href="tambah_film.php" class="btn">Tambah Film</a>
            </div>
            <div class="menu-card">
                <h3>📅 Jadwal</h3>
                <a href="manage_jadwal.php" class="btn">Kelola Jadwal</a>
            </div>
            <div class="menu-card">
                <h3>🎟️ Booking</h3>
                <a href="booking_list.php" class="btn">Data Booking</a>
            </div>
        </div>

        <h2>Daftar Film</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Poster</th>
                    <th>Nama Film</th>
                    <th>Genre</th>
                    <th>Durasi</th>
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
