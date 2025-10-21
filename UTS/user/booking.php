<?php
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validasi film_id
$film_id = isset($_GET['film_id']) ? intval($_GET['film_id']) : 0;

if ($film_id == 0) {
    echo '<div style="text-align:center; padding:50px;">';
    echo '<h3>Error: ID Film tidak valid!</h3>';
    echo '<p>Silakan pilih film dari <a href="index.php">halaman utama</a>.</p>';
    echo '</div>';
    exit();
}

// Ambil data film
try {
    $stmt = $pdo->prepare("SELECT * FROM film WHERE id_film = ?");
    $stmt->execute([$film_id]);
    $film = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$film) {
        // Debugging tambahan
        echo "<h3>Film tidak ditemukan!</h3>";
        echo "<p>Film ID yang dicari: " . $film_id . "</p>";
        echo "<p><a href='index.php'>Kembali ke Beranda</a></p>";
        
        // Cek apakah ada film di database
        $stmt = $pdo->query("SELECT id_film, nama FROM film LIMIT 5");
        $all_films = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($all_films) {
            echo "<h4>Film yang tersedia di database:</h4><ul>";
            foreach ($all_films as $f) {
                echo "<li>ID: {$f['id_film']} - {$f['nama']} <a href='?film_id={$f['id_film']}'>Pilih</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Tidak ada film di database. Silakan tambahkan film terlebih dahulu.</p>";
        }
        exit();
    }
    
    // Ambil jadwal tayang
    $stmt = $pdo->prepare("
        SELECT jt.*, s.nama_studio 
        FROM jadwal_tayang jt 
        JOIN studio s ON jt.id_studio = s.id_studio 
        WHERE jt.id_film = ? AND jt.tanggal_tayang >= CURDATE()
        ORDER BY jt.tanggal_tayang, jt.jam_tayang
    ");
    $stmt->execute([$film_id]);
    $jadwals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Proses booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_jadwal = $_POST['id_jadwal'];
    $id_kursi = $_POST['id_kursi'];
    $jumlah_tiket = 1; // Default 1 tiket
    
    try {
        // Cek apakah kursi tersedia
        $stmt = $pdo->prepare("SELECT status FROM kursi WHERE id_kursi = ?");
        $stmt->execute([$id_kursi]);
        $kursi = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($kursi['status'] == 'terisi') {
            $error = "Kursi sudah dipesan! Silakan pilih kursi lain.";
        } else {
            // Update status kursi
            $stmt = $pdo->prepare("UPDATE kursi SET status = 'terisi' WHERE id_kursi = ?");
            $stmt->execute([$id_kursi]);
            
            // Insert booking
            $stmt = $pdo->prepare("
                INSERT INTO booking (id_user, id_jadwal, id_kursi, tanggal_pesan, jumlah_tiket, total_harga) 
                VALUES (?, ?, ?, CURDATE(), ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'], 
                $id_jadwal, 
                $id_kursi, 
                $jumlah_tiket, 
                $film['harga']
            ]);
            
            $success = "Booking berhasil!";
            header("refresh:2; url=my_bookings.php");
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket - <?php echo htmlspecialchars($film['nama']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .film-info { display: flex; gap: 20px; margin-bottom: 30px; }
        .film-poster { width: 200px; height: 300px; object-fit: cover; border-radius: 8px; }
        .booking-form { margin-top: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; max-width: 400px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .error { color: red; padding: 10px; background: #ffe6e6; border-radius: 4px; margin: 10px 0; }
        .success { color: green; padding: 10px; background: #e6ffe6; border-radius: 4px; margin: 10px 0; }
        .kursi-grid { display: grid; grid-template-columns: repeat(10, 1fr); gap: 5px; margin: 20px 0; }
        .kursi { padding: 10px; text-align: center; border: 1px solid #ddd; cursor: pointer; border-radius: 4px; font-size: 12px; }
        .kursi.tersedia { background: #28a745; color: white; }
        .kursi.terisi { background: #dc3545; color: white; cursor: not-allowed; }
        .kursi.selected { background: #007bff; color: white; }
        .screen { background: #333; color: white; text-align: center; padding: 10px; margin: 20px 0; border-radius: 4px; }
        .legend { display: flex; gap: 20px; margin: 20px 0; }
        .legend-item { display: flex; align-items: center; gap: 5px; }
        .legend-box { width: 20px; height: 20px; border-radius: 4px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Kembali ke Beranda</a>
        
        <h1>Pesan Tiket - <?php echo htmlspecialchars($film['nama']); ?></h1>
        
        <div class="film-info">
            <img src="../images/<?php echo htmlspecialchars($film['poster']); ?>" alt="<?php echo htmlspecialchars($film['nama']); ?>" class="film-poster" onerror="this.src='../images/default.jpg'">
            <div>
                <h2><?php echo htmlspecialchars($film['nama']); ?></h2>
                <p><strong>Genre:</strong> <?php echo htmlspecialchars($film['genre']); ?></p>
                <p><strong>Durasi:</strong> <?php echo $film['durasi']; ?> menit</p>
                <p><strong>Harga:</strong> Rp <?php echo number_format($film['harga'], 0, ',', '.'); ?></p>
                <p><?php echo nl2br(htmlspecialchars($film['deskripsi'])); ?></p>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($jadwals)): ?>
            <div class="error">Belum ada jadwal tayang untuk film ini.</div>
        <?php else: ?>
        
        <form method="POST" class="booking-form">
            <div class="form-group">
                <label>Pilih Jadwal:</label>
                <select name="id_jadwal" id="jadwal" required onchange="loadKursi()">
                    <option value="">-- Pilih Jadwal --</option>
                    <?php foreach ($jadwals as $jadwal): ?>
                    <option value="<?php echo $jadwal['id_jadwal']; ?>">
                        <?php echo date('d M Y', strtotime($jadwal['tanggal_tayang'])); ?> - 
                        <?php echo date('H:i', strtotime($jadwal['jam_tayang'])); ?> - 
                        <?php echo htmlspecialchars($jadwal['nama_studio']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="kursi-container" style="display: none;">
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-box" style="background: #28a745;"></div>
                        <span>Tersedia</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background: #dc3545;"></div>
                        <span>Terisi</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background: #007bff;"></div>
                        <span>Dipilih</span>
                    </div>
                </div>
                
                <div class="screen">LAYAR</div>
                <div class="kursi-grid" id="kursi-grid">
                    <!-- Kursi akan di-load via JavaScript -->
                </div>
                <input type="hidden" name="id_kursi" id="selected_kursi" required>
            </div>

            <button type="submit" class="btn" id="submit-btn" disabled>Pesan Tiket</button>
        </form>
        
        <?php endif; ?>
    </div>

    <script>
        function loadKursi() {
            const jadwalSelect = document.getElementById('jadwal');
            const kursiContainer = document.getElementById('kursi-container');
            const kursiGrid = document.getElementById('kursi-grid');
            const submitBtn = document.getElementById('submit-btn');
            
            if (jadwalSelect.value) {
                // Simulasi loading kursi (dalam implementasi real, gunakan AJAX ke get_kursi.php)
                kursiGrid.innerHTML = '';
                
                // Contoh data kursi (dalam implementasi real, ambil dari database)
                const rows = ['A', 'B', 'C', 'D', 'E'];
                const seatsPerRow = 10;
                let kursiId = 1;
                
                rows.forEach(row => {
                    for (let i = 1; i <= seatsPerRow; i++) {
                        const kursi = {
                            id: kursiId,
                            kode: row + i,
                            status: Math.random() > 0.7 ? 'terisi' : 'tersedia' // Random untuk demo
                        };
                        
                        const kursiElement = document.createElement('div');
                        kursiElement.className = `kursi ${kursi.status}`;
                        kursiElement.textContent = kursi.kode;
                        kursiElement.dataset.id = kursi.id;
                        
                        if (kursi.status === 'tersedia') {
                            kursiElement.addEventListener('click', function() {
                                // Hapus selected dari semua kursi
                                document.querySelectorAll('.kursi').forEach(k => {
                                    k.classList.remove('selected');
                                });
                                
                                // Tambah selected ke kursi yang dipilih
                                this.classList.add('selected');
                                document.getElementById('selected_kursi').value = kursi.id;
                                submitBtn.disabled = false;
                            });
                        }
                        
                        kursiGrid.appendChild(kursiElement);
                        kursiId++;
                    }
                });
                
                kursiContainer.style.display = 'block';
            } else {
                kursiContainer.style.display = 'none';
                submitBtn.disabled = true;
            }
        }
    </script>
</body>
</html>