<?php
session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$film_id = $_GET['film_id'] ?? 0;

// Ambil data film
$stmt = $pdo->prepare("SELECT * FROM film WHERE id_film = ?");
$stmt->execute([$film_id]);
$film = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$film) {
    header('Location: index.php');
    exit;
}

// Ambil jadwal tayang
$stmt = $pdo->prepare("SELECT jt.*, s.nama_studio 
                       FROM jadwal_tayang jt 
                       JOIN studio s ON jt.id_studio = s.id_studio 
                       WHERE jt.id_film = ? 
                       ORDER BY jt.tanggal_tayang, jt.jam_tayang");
$stmt->execute([$film_id]);
$jadwals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses booking - HANYA JIKA TOMBOL SUBMIT DIKLIK
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_booking'])) {
    $id_jadwal = $_POST['id_jadwal'] ?? '';
    $id_kursi = $_POST['id_kursi'] ?? ''; // PERBAIKAN: gunakan null coalescing
    
    if(empty($id_jadwal)) {
        $error = "Silakan pilih jadwal terlebih dahulu!";
    } else if(empty($id_kursi)) {
        $error = "Silakan pilih kursi!";
    } else {
        try {
            // Cek apakah kursi sudah dipesan di jadwal yang sama
            $stmt = $pdo->prepare("SELECT id_booking FROM booking WHERE id_jadwal = ? AND id_kursi = ?");
            $stmt->execute([$id_jadwal, $id_kursi]);
            $existing_booking = $stmt->fetch();
            
            if($existing_booking) {
                $error = "Kursi sudah dipesan! Silakan pilih kursi lain.";
            } else {
                // Insert booking
                $total_harga = $film['harga'];
                $stmt = $pdo->prepare("INSERT INTO booking (id_user, id_jadwal, id_kursi, tanggal_pesan, jumlah_tiket, total_harga) 
                                      VALUES (?, ?, ?, CURDATE(), 1, ?)");
                $stmt->execute([$_SESSION['user_id'], $id_jadwal, $id_kursi, $total_harga]);
                
                $_SESSION['booking_success'] = "Booking berhasil! Total: Rp " . number_format($total_harga, 0, ',', '.');
                header('Location: my_booking.php');
                exit;
            }
        } catch(Exception $e) {
            $error = "Gagal melakukan booking: " . $e->getMessage();
        }
    }
}

// Ambil kursi yang tersedia untuk jadwal yang dipilih
$kursi_tersedia = [];
$selected_jadwal = $_POST['id_jadwal'] ?? '';
$selected_kursi = $_POST['id_kursi'] ?? ''; // PERBAIKAN: gunakan null coalescing

if(!empty($selected_jadwal)) {
    // Ambil id_studio dari jadwal yang dipilih
    $stmt = $pdo->prepare("SELECT id_studio FROM jadwal_tayang WHERE id_jadwal = ?");
    $stmt->execute([$selected_jadwal]);
    $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($jadwal) {
        // Ambil semua kursi di studio tersebut
        $stmt = $pdo->prepare("SELECT k.* FROM kursi k WHERE k.id_studio = ? ORDER BY k.kode_kursi");
        $stmt->execute([$jadwal['id_studio']]);
        $semua_kursi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ambil kursi yang sudah dipesan di jadwal ini
        $stmt = $pdo->prepare("SELECT id_kursi FROM booking WHERE id_jadwal = ?");
        $stmt->execute([$selected_jadwal]);
        $kursi_terisi = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Tandai kursi yang tersedia
        foreach($semua_kursi as $kursi) {
            $kursi['tersedia'] = !in_array($kursi['id_kursi'], $kursi_terisi);
            $kursi_tersedia[] = $kursi;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Booking - <?= htmlspecialchars($film['nama']) ?></title>
    <style>
        body { 
            font-family: Arial; 
            margin: 20px; 
            background: #f0f2f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .film-header {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            align-items: flex-start;
        }
        .film-poster {
            width: 150px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .no-poster {
            width: 150px;
            height: 200px;
            background: #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 14px;
        }
        .film-info {
            flex: 1;
        }
        .film-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .film-price {
            font-size: 18px;
            color: #667eea;
            font-weight: bold;
        }
        .section {
            margin: 25px 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        .jadwal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .jadwal-item {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            background: white;
        }
        .jadwal-item:hover {
            border-color: #667eea;
        }
        .jadwal-item.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .kursi-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 5px;
            margin: 15px 0;
            max-width: 400px;
        }
        .kursi {
            width: 40px;
            height: 40px;
            border: 2px solid #4caf50;
            background: #e8f5e8;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        .kursi.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .kursi.terisi {
            background: #f44336;
            color: white;
            cursor: not-allowed;
        }
        .screen {
            text-align: center;
            background: #333;
            color: white;
            padding: 10px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .total-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .total-price {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .btn-select {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .error {
            background: #ffebee;
            color: #d32f2f;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #d32f2f;
        }
        .legend {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            font-size: 14px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .selected-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #4caf50;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Film Header dengan Poster -->
        <div class="film-header">
            <?php if (!empty($film['poster'])): ?>
                <img src="../uploads/<?= htmlspecialchars($film['poster']) ?>" 
                     alt="<?= htmlspecialchars($film['nama']) ?>" 
                     class="film-poster">
            <?php else: ?>
                <div class="no-poster">No Poster</div>
            <?php endif; ?>
            
            <div class="film-info">
                <div class="film-title"><?= htmlspecialchars($film['nama']) ?></div>
                <div class="film-price">Rp <?= number_format($film['harga'], 0, ',', '.') ?> / tiket</div>
            </div>
        </div>

        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" id="bookingForm">
            <input type="hidden" name="id_jadwal" id="id_jadwal" value="<?= $selected_jadwal ?>">
            <input type="hidden" name="id_kursi" id="id_kursi" value="<?= $selected_kursi ?>">

            <!-- Pilih Jadwal -->
            <div class="section">
                <div class="section-title">Pilih Jadwal Tayang</div>
                <div class="jadwal-grid">
                    <?php foreach($jadwals as $jadwal): ?>
                        <div class="jadwal-item <?= $selected_jadwal == $jadwal['id_jadwal'] ? 'selected' : '' ?>" 
                             onclick="selectJadwal(<?= $jadwal['id_jadwal'] ?>)">
                            <div style="font-weight: bold; font-size: 16px;">
                                <?= date('H:i', strtotime($jadwal['jam_tayang'])) ?>
                            </div>
                            <div style="font-size: 14px;">
                                <?= date('d/m/Y', strtotime($jadwal['tanggal_tayang'])) ?>
                            </div>
                            <div style="font-size: 12px; margin-top: 5px;">
                                Studio <?= $jadwal['nama_studio'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pilih Kursi (Muncul setelah pilih jadwal) -->
            <?php if(!empty($kursi_tersedia)): ?>
                <div class="section">
                    <div class="section-title">Pilih Kursi</div>
                    
                    <?php if(!empty($selected_kursi)): ?>
                        <?php 
                        // Cari info kursi yang dipilih
                        $selected_kursi_info = null;
                        foreach($kursi_tersedia as $kursi) {
                            if($kursi['id_kursi'] == $selected_kursi) {
                                $selected_kursi_info = $kursi;
                                break;
                            }
                        }
                        ?>
                        <div class="selected-info">
                            <strong>Kursi Terpilih:</strong> <?= $selected_kursi_info['kode_kursi'] ?? '' ?>
                        </div>
                    <?php endif; ?>

                    <div class="screen">LAYAR</div>
                    <div class="kursi-grid">
                        <?php foreach($kursi_tersedia as $kursi): ?>
                            <div class="kursi <?= !$kursi['tersedia'] ? 'terisi' : '' ?> <?= $selected_kursi == $kursi['id_kursi'] ? 'selected' : '' ?>" 
                                 <?= $kursi['tersedia'] ? 'onclick="selectKursi(' . $kursi['id_kursi'] . ')"' : '' ?>>
                                <?= $kursi['kode_kursi'] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="legend">
                        <div class="legend-item">
                            <div class="kursi" style="cursor: default;"></div>
                            <span>Tersedia</span>
                        </div>
                        <div class="legend-item">
                            <div class="kursi selected" style="cursor: default;"></div>
                            <span>Dipilih</span>
                        </div>
                        <div class="legend-item">
                            <div class="kursi terisi" style="cursor: default;"></div>
                            <span>Terisi</span>
                        </div>
                    </div>
                </div>

                <!-- Total Pembelian -->
                <div class="total-box">
                    <div class="total-price">
                        Total: Rp <?= number_format($film['harga'], 0, ',', '.') ?>
                    </div>
                </div>

                <button type="submit" name="submit_booking" class="btn" <?= empty($selected_jadwal) || empty($selected_kursi) ? 'disabled' : '' ?>>
                    Pesan Tiket
                </button>
            <?php else: ?>
                <div class="section">
                    <p style="text-align: center; color: #666; padding: 40px;">
                        <?= empty($selected_jadwal) ? 'Silakan pilih jadwal tayang terlebih dahulu' : 'Memuat kursi...' ?>
                    </p>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        function selectJadwal(jadwalId) {
            document.getElementById('id_jadwal').value = jadwalId;
            document.getElementById('id_kursi').value = ''; // Reset kursi ketika ganti jadwal
            document.getElementById('bookingForm').submit();
        }

        function selectKursi(kursiId) {
            document.getElementById('id_kursi').value = kursiId;
            // TIDAK AUTO-SUBMIT, hanya update form
            document.getElementById('bookingForm').submit();
        }
    </script>
</body>
</html>
