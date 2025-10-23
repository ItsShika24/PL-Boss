<?php
session_start();
require_once '../config.php';

// Cek jika sudah login, tampilkan nama user
$userLoggedIn = isset($_SESSION['user_id']);

// Ambil data film yang sedang tayang
$stmt = $pdo->query("SELECT * FROM film WHERE jadwal_tayang = 'Sekarang Tayang' ORDER BY id_film DESC");
$films = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bioskop Online</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h2 { margin: 0; }
        .nav-links a { color: white; text-decoration: none; margin: 0 10px; padding: 8px 15px; border-radius: 5px; }
        .nav-links a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1200px; margin: 30px auto; padding: 20px; }
        .film-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-top: 30px; }
        .film-card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .film-card:hover { transform: translateY(-5px); }
        .film-poster { width: 100%; height: 350px; object-fit: cover; }
        .film-info { padding: 20px; }
        .film-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333; }
        .film-genre { color: #667eea; font-weight: bold; margin-bottom: 5px; }
        .film-duration { color: #666; margin-bottom: 10px; }
        .film-price { color: #2ecc71; font-weight: bold; font-size: 16px; margin-bottom: 15px; }
        .btn { display: block; text-align: center; padding: 12px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn:hover { background: #5568d3; }
        .welcome { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 40px; text-align: center; border-radius: 30px; width: fit-content; margin: 20px auto; }
        .welcome h1 { color: #ffffffff; margin-bottom: 10px; }
        .welcome p { color: #d1cbcbff; font-size: 18px; }
        .no-poster { height: 350px; background: #ddd; display: flex; align-items: center; justify-content: center; color: #666; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🎬 Bioskop Online</h2>
        <div class="nav-links">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span>Halo, <?= htmlspecialchars($_SESSION['user_nama']) ?></span>
                <a href="my_booking.php">Pesanan Saya</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h1>Selamat Datang di Bioskop Online</h1>
            <p>Nikmati pengalaman menonton film terbaik dengan kenyamanan maksimal</p>
        </div>
        
        <h2>🎞️ Film Sedang Tayang</h2>
        
        <?php if(empty($films)): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🎭</div>
                <h3 style="color: #666;">Belum ada film yang sedang tayang</h3>
                <p style="color: #999;">Silakan kembali lagi nanti untuk melihat film terbaru.</p>
            </div>
        <?php else: ?>
            <div class="film-grid">
                <?php foreach($films as $film): ?>
                <div class="film-card">
                    <?php if (!empty($film['poster'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($film['poster']) ?>" alt="<?= htmlspecialchars($film['nama']) ?>" class="film-poster" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="no-poster" style="display: none;">Poster Tidak Tersedia</div>
                    <?php else: ?>
                        <div class="no-poster">No Poster</div>
                    <?php endif; ?>
                    
                    <div class="film-info">
                        <div class="film-title"><?= htmlspecialchars($film['nama']) ?></div>
                        <div class="film-genre">🎭 <?= $film['genre'] ?></div>
                        <div class="film-duration">⏱️ <?= $film['durasi'] ?> menit</div>
                        <div class="film-price">💰 Rp <?= number_format($film['harga'], 0, ',', '.') ?></div>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="booking.php?film_id=<?= $film['id_film'] ?>" class="btn">Pesan Tiket</a>
                        <?php else: ?>
                            <a href="login.php" class="btn">Login untuk Pesan</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>