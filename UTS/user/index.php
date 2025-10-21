<?php
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil semua film
try {
    $stmt = $pdo->query("SELECT * FROM film ORDER BY nama ASC");
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bioskop Online - Daftar Film</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        
        .navbar {
            background: rgba(255,255,255,0.95);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { color: #667eea; font-size: 24px; }
        .navbar .user-info { display: flex; gap: 15px; align-items: center; }
        .navbar a { color: #667eea; text-decoration: none; padding: 8px 16px; border-radius: 4px; transition: all 0.3s; }
        .navbar a:hover { background: #667eea; color: white; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .welcome { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .welcome h2 { color: #333; margin-bottom: 10px; }
        .welcome p { color: #666; }
        
        .films-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .film-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .film-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .film-poster {
            width: 100%;
            height: 350px;
            object-fit: cover;
        }
        
        .film-info {
            padding: 15px;
        }
        .film-info h3 {
            color: #333;
            margin-bottom: 8px;
            font-size: 18px;
        }
        .film-info .genre {
            color: #667eea;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .film-info .durasi {
            color: #666;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .film-info .harga {
            color: #28a745;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .btn-pesan {
            display: block;
            width: 100%;
            padding: 10px;
            background: #667eea;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-pesan:hover {
            background: #5568d3;
        }
        
        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .empty-state h3 { color: #666; margin-bottom: 10px; }
        .empty-state p { color: #999; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>🎬 Bioskop Online</h1>
            <div class="user-info">
                <span>Halo, <?php echo htmlspecialchars($_SESSION['user_nama'] ?? 'User'); ?>!</span>
                <a href="my_bookings.php">Tiket Saya</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <h2>Selamat Datang di Bioskop Online!</h2>
            <p>Pilih film favorit Anda dan pesan tiket sekarang</p>
        </div>

        <?php if (empty($films)): ?>
            <div class="empty-state">
                <h3>Belum Ada Film Tersedia</h3>
                <p>Mohon maaf, saat ini belum ada film yang bisa ditonton.</p>
            </div>
        <?php else: ?>
            <div class="films-grid">
                <?php foreach ($films as $film): ?>
                <div class="film-card">
                    <img src="../images/<?php echo htmlspecialchars($film['poster']); ?>" 
                         alt="<?php echo htmlspecialchars($film['nama']); ?>" 
                         class="film-poster"
                         onerror="this.src='../images/default.jpg'">
                    <div class="film-info">
                        <h3><?php echo htmlspecialchars($film['nama']); ?></h3>
                        <div class="genre">📌 <?php echo htmlspecialchars($film['genre']); ?></div>
                        <div class="durasi">⏱️ <?php echo $film['durasi']; ?> menit</div>
                        <div class="harga">Rp <?php echo number_format($film['harga'], 0, ',', '.'); ?></div>
                        <a href="booking.php?film_id=<?php echo $film['id_film']; ?>" class="btn-pesan">
                            Pesan Tiket
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>