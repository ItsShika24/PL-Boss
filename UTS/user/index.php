<?php
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data film
try {
    $stmt = $pdo->query("SELECT * FROM film WHERE jadwal_tayang = 'Sedang Tayang'");
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Film - Bioskop Online</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        .header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .film-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .film-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .film-poster { width: 100%; height: 300px; object-fit: cover; }
        .film-info { padding: 15px; }
        .film-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .film-genre { color: #666; margin-bottom: 10px; }
        .film-price { color: #e74c3c; font-weight: bold; margin-bottom: 10px; }
        .btn { display: inline-block; padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .nav-links a { color: white; text-decoration: none; margin-left: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bioskop Online</h1>
        <div class="user-info">
            <span>Halo, <?php echo $_SESSION['user_nama']; ?></span>
            <div class="nav-links">
                <a href="my_bookings.php">Tiket Saya</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h2>Film Sedang Tayang</h2>
        
        <?php if (isset($error)): ?>
            <div style="color: red; margin-bottom: 20px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="film-grid">
            <?php foreach ($films as $film): ?>
            <div class="film-card">
                <img src="../images/<?php echo $film['poster']; ?>" alt="<?php echo $film['nama']; ?>" class="film-poster" onerror="this.src='../images/default.jpg'">
                <div class="film-info">
                    <div class="film-title"><?php echo $film['nama']; ?></div>
                    <div class="film-genre"><?php echo $film['genre']; ?> • <?php echo $film['durasi']; ?> menit</div>
                    <div class="film-price">Rp <?php echo number_format($film['harga'], 0, ',', '.'); ?></div>
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                        <?php echo substr($film['deskripsi'], 0, 100); ?>...
                    </p>
                    <a href="booking.php?film_id=<?php echo $film['id_film']; ?>" class="btn">Pesan Tiket</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>