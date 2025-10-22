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

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data film dari database
$films = [];
$sql = "SELECT * FROM film ORDER BY id_film DESC";
$result = mysqli_query($user_conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $films[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bioskop Online - Pemesanan Tiket</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-welcome {
            color: #667eea;
            font-weight: 600;
            font-size: 1.1em;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
        }

        .nav-btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9em;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .nav-btn.logout {
            background: #f44336;
        }

        .nav-btn.logout:hover {
            background: #d32f2f;
            box-shadow: 0 5px 15px rgba(244, 67, 54, 0.4);
        }

        h1 {
            color: #667eea;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 0.9em;
        }

        .movies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .movie-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .movie-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
        }

        .movie-image {
            width: 100%;
            height: 320px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4em;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
        }

        .movie-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.8) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .movie-card:hover .movie-image::before {
            opacity: 1;
        }

        .movie-info {
            padding: 25px;
        }

        .movie-title {
            font-size: 1.4em;
            color: #333;
            margin-bottom: 12px;
            font-weight: bold;
            line-height: 1.3;
            min-height: 3.2em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .rating {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .movie-details {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .movie-details span {
            display: block;
            margin-bottom: 6px;
        }

        .movie-details strong {
            color: #333;
        }

        .price {
            font-size: 1.3em;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            background: #f8f9ff;
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #e3e9ff;
        }

        .book-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .book-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .book-btn:hover::before {
            left: 100%;
        }

        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        footer {
            text-align: center;
            color: white;
            margin-top: 50px;
            padding: 20px;
            opacity: 0.8;
        }

        .no-movies {
            grid-column: 1 / -1;
            text-align: center;
            color: white;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .no-movies h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .movies-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            h1 {
                font-size: 2em;
            }

            .header-top {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .nav-buttons {
                justify-content: center;
                flex-wrap: wrap;
            }

            .movie-image {
                height: 280px;
            }

            .movie-title {
                font-size: 1.2em;
            }
        }

        @media (max-width: 480px) {
            .movies-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 10px;
            }
            
            header {
                padding: 15px;
            }
            
            h1 {
                font-size: 1.8em;
            }
        }

        /* Animation for movie cards staggered */
        .movie-card:nth-child(1) { animation-delay: 0.1s; }
        .movie-card:nth-child(2) { animation-delay: 0.2s; }
        .movie-card:nth-child(3) { animation-delay: 0.3s; }
        .movie-card:nth-child(4) { animation-delay: 0.4s; }
        .movie-card:nth-child(5) { animation-delay: 0.5s; }
        .movie-card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-top">
                <div class="user-info">
                    <div class="user-welcome">🎉 Halo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Selamat menonton 🎉</div>
                </div>
                <div class="nav-buttons">
                    <a href="my_bookings.php" class="nav-btn">🎫 Tiket Saya</a>
                    <a href="logout.php" class="nav-btn logout">🚪 Logout</a>
                </div>
            </div>
            <h1>🎬 BIOSKOP ONLINE</h1>
            <p class="subtitle">Pesan tiket favorit Anda sekarang juga! Nikmati pengalaman menonton terbaik</p>
        </header>

        <div class="movies-grid" id="moviesGrid">
            <?php if (empty($films)): ?>
                <div class="no-movies">
                    <h3>🎭 Tidak ada film yang sedang tayang</h3>
                    <p>Silakan kembali lagi nanti untuk melihat film terbaru</p>
                    <div style="margin-top: 20px; font-size: 3em;">😴</div>
                </div>
            <?php else: ?>
                <?php foreach ($films as $index => $film): ?>
                    <div class="movie-card" style="animation-delay: <?php echo ($index * 0.1) + 0.1; ?>s;">
                        <div class="movie-image" style="<?php 
                            if (!empty($film['poster'])) {
                                // Coba beberapa kemungkinan path
                                $possible_paths = [
                                    '../admin/uploads/' . $film['poster'],
                                    '../../admin/uploads/' . $film['poster'],
                                    'admin/uploads/' . $film['poster'],
                                    '../uploads/' . $film['poster']
                                ];
                                
                                $found_path = '';
                                foreach ($possible_paths as $path) {
                                    if (file_exists($path)) {
                                        $found_path = $path;
                                        break;
                                    }
                                }
                                
                                if ($found_path) {
                                    echo "background-image: url('$found_path')";
                                } else {
                                    // Jika tidak ditemukan, gunakan gradient
                                    echo "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)";
                                }
                            } else {
                                echo "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)";
                            }
                        ?>">
                            <?php if (empty($film['poster'])): ?>
                                🎬
                            <?php endif; ?>
                        </div>
                        <div class="movie-info">
                            <div class="movie-title"><?php echo htmlspecialchars($film['nama']); ?></div>
                            <div class="rating"><?php echo htmlspecialchars($film['genre']); ?></div>
                            <div class="movie-details">
                                <span><strong>🎭 Genre:</strong> <?php echo htmlspecialchars($film['genre']); ?></span>
                                <span><strong>⏱️ Durasi:</strong> <?php echo htmlspecialchars($film['durasi']); ?> menit</span>
                                <span><strong>📖 Deskripsi:</strong> <?php echo htmlspecialchars($film['deskripsi']); ?></span>
                            </div>
                            <div class="price">💰 Rp <?php echo number_format($film['harga'], 0, ',', '.'); ?></div>
                            <button type="button" class="book-btn" onclick="window.location.href='booking.php?film_id=<?php echo $film['id_film']; ?>'">
                                🎟️ Pesan Tiket Sekarang
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Bioskop Online. Selamat menonton! 🎭</p>
        <p style="margin-top: 10px; font-size: 0.9em; opacity: 0.6;">Pengalaman menonton terbaik hanya di Bioskop Online</p>
    </footer>

    <script>
        // Debugging untuk poster
        document.addEventListener('DOMContentLoaded', function() {
            const movieImages = document.querySelectorAll('.movie-image');
            movieImages.forEach((img, index) => {
                const bgImage = img.style.backgroundImage;
                if (bgImage && bgImage !== 'none') {
                    console.log(`Poster ${index + 1}: ${bgImage}`);
                } else {
                    console.log(`Poster ${index + 1}: No background image - using gradient`);
                }
            });
        });
    </script>
</body>
</html>