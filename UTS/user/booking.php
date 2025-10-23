<?php
session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
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

// Proses form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['pilih_jadwal'])) {
        $id_jadwal = $_POST['id_jadwal'] ?? '';
        $_SESSION['booking_jadwal'] = $id_jadwal;
        $_SESSION['booking_film'] = $film_id;
        $_SESSION['selected_kursi'] = []; // Reset kursi
        $_SESSION['jumlah_tiket'] = 1; // Default 1 tiket
        
    } elseif(isset($_POST['ubah_jumlah'])) {
        $change = intval($_POST['change']);
        $current_jumlah = $_SESSION['jumlah_tiket'] ?? 1;
        $new_jumlah = $current_jumlah + $change;
        
        if($new_jumlah >= 1 && $new_jumlah <= 10) {
            $_SESSION['jumlah_tiket'] = $new_jumlah;
            
            // Jika jumlah berkurang, hapus kursi yang kelebihan
            if($new_jumlah < count($_SESSION['selected_kursi'] ?? [])) {
                $_SESSION['selected_kursi'] = array_slice($_SESSION['selected_kursi'], 0, $new_jumlah);
            }
        }
        
    } elseif(isset($_POST['pilih_kursi'])) {
        $kursi_id = $_POST['kursi_id'] ?? '';
        $kursi_kode = $_POST['kursi_kode'] ?? '';
        
        if($kursi_id && $kursi_kode) {
            $selected_kursi = $_SESSION['selected_kursi'] ?? [];
            $jumlah_tiket = $_SESSION['jumlah_tiket'] ?? 1;
            
            // Cek apakah kursi sudah dipilih
            $kursi_index = array_search($kursi_id, array_column($selected_kursi, 'id'));
            
            if($kursi_index !== false) {
                // Hapus kursi jika sudah dipilih
                unset($selected_kursi[$kursi_index]);
                $selected_kursi = array_values($selected_kursi);
            } else {
                // Tambah kursi jika belum mencapai batas
                if(count($selected_kursi) < $jumlah_tiket) {
                    $selected_kursi[] = ['id' => $kursi_id, 'kode' => $kursi_kode];
                } else {
                    $error = "Anda hanya bisa memilih $jumlah_tiket kursi untuk $jumlah_tiket tiket";
                }
            }
            
            $_SESSION['selected_kursi'] = $selected_kursi;
        }
        
    } elseif(isset($_POST['submit_booking'])) {
        $id_jadwal = $_SESSION['booking_jadwal'] ?? '';
        $selected_kursi = $_SESSION['selected_kursi'] ?? [];
        $jumlah_tiket = $_SESSION['jumlah_tiket'] ?? 1;
        
        if(empty($id_jadwal)) {
            $error = "Silakan pilih jadwal terlebih dahulu!";
        } else if(empty($selected_kursi)) {
            $error = "Silakan pilih kursi!";
        } else if(count($selected_kursi) != $jumlah_tiket) {
            $error = "Jumlah kursi yang dipilih harus sama dengan jumlah tiket!";
        } else {
            try {
                $pdo->beginTransaction();
                
                // Cek apakah semua kursi tersedia
                $kursi_ids = array_column($selected_kursi, 'id');
                $placeholders = str_repeat('?,', count($kursi_ids) - 1) . '?';
                $stmt = $pdo->prepare("SELECT id_booking FROM booking WHERE id_jadwal = ? AND id_kursi IN ($placeholders)");
                $params = array_merge([$id_jadwal], $kursi_ids);
                $stmt->execute($params);
                $existing_bookings = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if(count($existing_bookings) > 0) {
                    $error = "Beberapa kursi sudah dipesan! Silakan pilih kursi lain.";
                    $pdo->rollBack();
                } else {
                    // Hitung total harga
                    $total_harga = $film['harga'] * $jumlah_tiket;
                    
                    // Insert booking untuk setiap kursi
                    $stmt = $pdo->prepare("INSERT INTO booking (id_user, id_jadwal, id_kursi, tanggal_pesan, jumlah_tiket, total_harga) 
                                          VALUES (?, ?, ?, CURDATE(), 1, ?)");
                    
                    foreach($kursi_ids as $kursi_id) {
                        $stmt->execute([$_SESSION['user_id'], $id_jadwal, $kursi_id, $film['harga']]);
                    }
                    
                    $pdo->commit();
                    
                    // Clear session booking
                    unset($_SESSION['booking_jadwal']);
                    unset($_SESSION['booking_film']);
                    unset($_SESSION['selected_kursi']);
                    unset($_SESSION['jumlah_tiket']);
                    
                    $_SESSION['booking_success'] = "Booking berhasil! " . count($kursi_ids) . " tiket dengan total: Rp " . number_format($total_harga, 0, ',', '.');
                    header('Location: my_booking.php');
                    exit;
                }
            } catch(Exception $e) {
                $pdo->rollBack();
                $error = "Gagal melakukan booking: " . $e->getMessage();
            }
        }
    }
}

// Ambil data dari session
$selected_jadwal = $_SESSION['booking_jadwal'] ?? '';
$selected_kursi = $_SESSION['selected_kursi'] ?? [];
$jumlah_tiket = $_SESSION['jumlah_tiket'] ?? 1;

// Ambil kursi yang tersedia untuk jadwal yang dipilih
$kursi_tersedia = [];
if(!empty($selected_jadwal)) {
    // Ambil id_studio dari jadwal yang dipilih
    $stmt = $pdo->prepare("SELECT id_studio FROM jadwal_tayang WHERE id_jadwal = ?");
    $stmt->execute([$selected_jadwal]);
    $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($jadwal) {
        // Ambil semua kursi di studio tersebut
        $stmt = $pdo->prepare("SELECT k.* FROM kursi k WHERE k.id_studio = ? ORDER BY 
                              SUBSTRING(k.kode_kursi, 1, 1), 
                              CAST(SUBSTRING(k.kode_kursi, 2) AS UNSIGNED)");
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
            max-width: 1200px;
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
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
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
            background: white;
            cursor: pointer;
        }
        .jadwal-item.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .jadwal-item input[type="radio"] {
            display: none;
        }
        
        /* Kontrol Jumlah Tiket */
        .tiket-control {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            justify-content: center;
        }
        .tiket-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tiket-count {
            font-size: 24px;
            font-weight: bold;
            min-width: 50px;
            text-align: center;
        }
        .tiket-info {
            margin-left: 20px;
            font-weight: bold;
            color: #667eea;
        }
        
        /* Grid Kursi */
        .kursi-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .screen {
            text-align: center;
            background: #333;
            color: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
            font-size: 18px;
        }
        .kursi-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 8px;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .kursi {
            width: 45px;
            height: 45px;
            border: 2px solid #28a745;
            background: #e8f5e8;
            border-radius: 8px;
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
            background: #dc3545;
            color: white;
            border-color: #dc3545;
            cursor: not-allowed;
        }
        .kursi-btn {
            width: 100%;
            height: 100%;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }
        .kursi-btn:disabled {
            cursor: not-allowed;
        }
        
        /* Info Selected */
        .selected-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
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
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
        }
        
        /* Total dan Button */
        .total-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .total-price {
            font-size: 24px;
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
            margin: 5px;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .btn-success {
            background: #28a745;
            width: 100%;
            margin-top: 20px;
        }
        .btn-success:hover {
            background: #218838;
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
            gap: 20px;
            margin-top: 15px;
            justify-content: center;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 10px;
        }
        .step {
            padding: 10px 20px;
            background: #ddd;
            border-radius: 20px;
            font-weight: bold;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Film Header -->
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
                <p><?= htmlspecialchars($film['deskripsi']) ?></p>
            </div>
        </div>

        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?= empty($selected_jadwal) ? 'active' : '' ?>">1. Pilih Jadwal</div>
            <div class="step <?= !empty($selected_jadwal) ? 'active' : '' ?>">2. Pilih Kursi</div>
            <div class="step">3. Konfirmasi</div>
        </div>

        <!-- Step 1: Pilih Jadwal -->
        <?php if(empty($selected_jadwal)): ?>
            <div class="section">
                <div class="section-title">🎬 Pilih Jadwal Tayang</div>
                <form method="POST">
                    <div class="jadwal-grid">
                        <?php foreach($jadwals as $jadwal): ?>
                            <label>
                                <div class="jadwal-item <?= $selected_jadwal == $jadwal['id_jadwal'] ? 'selected' : '' ?>">
                                    <input type="radio" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>" 
                                           onchange="this.form.submit()" 
                                           <?= $selected_jadwal == $jadwal['id_jadwal'] ? 'checked' : '' ?>>
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
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="pilih_jadwal" value="1">
                </form>
            </div>

        <!-- Step 2: Pilih Kursi -->
        <?php else: ?>
            <?php if(!empty($kursi_tersedia)): ?>
                <!-- Kontrol Jumlah Tiket -->
                <div class="section">
                    <div class="section-title">🎫 Pilih Jumlah Tiket</div>
                    <form method="POST">
                        <div class="tiket-control">
                            <button type="submit" name="ubah_jumlah" class="tiket-btn" value="-1">-</button>
                            <div class="tiket-count"><?= $jumlah_tiket ?></div>
                            <button type="submit" name="ubah_jumlah" class="tiket-btn" value="1">+</button>
                            <div class="tiket-info">
                                Maksimal: 10 tiket
                            </div>
                        </div>
                        <input type="hidden" name="change" value="0">
                    </form>
                </div>

                <!-- Pilih Kursi -->
                <div class="section">
                    <div class="section-title">💺 Pilih Kursi (<?= count($selected_kursi) ?>/<?= $jumlah_tiket ?>)</div>
                    
                    <?php if(!empty($selected_kursi)): ?>
                        <div class="selected-info">
                            <strong>Kursi Terpilih:</strong> 
                            <div class="kursi-list">
                                <?php foreach($selected_kursi as $kursi): ?>
                                    <div class="kursi-badge"><?= $kursi['kode'] ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="kursi-container">
                        <div class="screen">L A Y A R</div>
                        <form method="POST">
                            <div class="kursi-grid">
                                <?php foreach($kursi_tersedia as $kursi): 
                                    $is_selected = in_array($kursi['id_kursi'], array_column($selected_kursi, 'id'));
                                ?>
                                    <div class="kursi <?= !$kursi['tersedia'] ? 'terisi' : '' ?> <?= $is_selected ? 'selected' : '' ?>">
                                        <?php if($kursi['tersedia']): ?>
                                            <button type="submit" name="pilih_kursi" class="kursi-btn">
                                                <?= $kursi['kode_kursi'] ?>
                                            </button>
                                            <input type="hidden" name="kursi_id" value="<?= $kursi['id_kursi'] ?>">
                                            <input type="hidden" name="kursi_kode" value="<?= $kursi['kode_kursi'] ?>">
                                        <?php else: ?>
                                            <button class="kursi-btn" disabled>
                                                <?= $kursi['kode_kursi'] ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </form>
                        
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
                </div>

                <!-- Total Pembelian -->
                <div class="total-box">
                    <div>Jumlah Tiket: <?= $jumlah_tiket ?></div>
                    <div>Total: <span class="total-price">Rp <?= number_format($film['harga'] * $jumlah_tiket, 0, ',', '.') ?></span></div>
                </div>

                <!-- Submit Button -->
                <form method="POST">
                    <button type="submit" name="submit_booking" class="btn btn-success" 
                            <?= count($selected_kursi) != $jumlah_tiket ? 'disabled' : '' ?>>
                        Pesan <?= $jumlah_tiket ?> Tiket - Rp <?= number_format($film['harga'] * $jumlah_tiket, 0, ',', '.') ?>
                    </button>
                </form>

            <?php else: ?>
                <div class="section">
                    <p style="text-align: center; color: #666; padding: 40px;">
                        Memuat kursi...
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>