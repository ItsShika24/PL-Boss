<?php
session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$film_id = $_GET['film_id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM film WHERE id_film = ?");
$stmt->execute([$film_id]);
$film = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$film) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT jt.*, s.nama_studio FROM jadwal_tayang jt JOIN studio s ON jt.id_studio = s.id_studio WHERE jt.id_film = ? ORDER BY jt.tanggal_tayang, jt.jam_tayang");
$stmt->execute([$film_id]);
$jadwals = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['pilih_jadwal'])) {
        $_SESSION['booking_jadwal'] = $_POST['id_jadwal'] ?? '';
        $_SESSION['booking_film'] = $film_id;
        $_SESSION['selected_kursi'] = [];
    } elseif(isset($_POST['pilih_kursi'])) {
        $selected_kursi = $_SESSION['selected_kursi'] ?? [];
        $kursi_id = $_POST['kursi_id'] ?? '';
        $kursi_kode = $_POST['kursi_kode'] ?? '';
        
        if($kursi_id && $kursi_kode) {
            $kursi_index = array_search($kursi_id, array_column($selected_kursi, 'id'));
            if($kursi_index !== false) {
                unset($selected_kursi[$kursi_index]);
            } else {
                $selected_kursi[] = ['id' => $kursi_id, 'kode' => $kursi_kode];
            }
            $_SESSION['selected_kursi'] = array_values($selected_kursi);
        }
    } elseif(isset($_POST['submit_booking'])) {
        $id_jadwal = $_SESSION['booking_jadwal'] ?? '';
        $selected_kursi = $_SESSION['selected_kursi'] ?? [];
        
        if(empty($id_jadwal)) {
            $error = "Silakan pilih jadwal terlebih dahulu!";
        } else if(empty($selected_kursi)) {
            $error = "Silakan pilih kursi!";
        } else {
            try {
                $pdo->beginTransaction();
                $kursi_ids = array_column($selected_kursi, 'id');
                $placeholders = str_repeat('?,', count($kursi_ids) - 1) . '?';
                $stmt = $pdo->prepare("SELECT id_booking FROM booking WHERE id_jadwal = ? AND id_kursi IN ($placeholders)");
                $stmt->execute(array_merge([$id_jadwal], $kursi_ids));
                
                if(count($stmt->fetchAll(PDO::FETCH_COLUMN)) > 0) {
                    $error = "Beberapa kursi sudah dipesan!";
                    $pdo->rollBack();
                } else {
                    $total_harga = $film['harga'] * count($kursi_ids);
                    $stmt = $pdo->prepare("INSERT INTO booking (id_user, id_jadwal, id_kursi, tanggal_pesan, jumlah_tiket, total_harga) VALUES (?, ?, ?, CURDATE(), 1, ?)");
                    foreach($kursi_ids as $kursi_id) {
                        $stmt->execute([$_SESSION['user_id'], $id_jadwal, $kursi_id, $film['harga']]);
                    }
                    $pdo->commit();
                    unset($_SESSION['booking_jadwal'], $_SESSION['booking_film'], $_SESSION['selected_kursi']);
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

$selected_jadwal = $_SESSION['booking_jadwal'] ?? '';
$selected_kursi = $_SESSION['selected_kursi'] ?? [];
$kursi_tersedia = [];

if(!empty($selected_jadwal)) {
    $stmt = $pdo->prepare("SELECT id_studio FROM jadwal_tayang WHERE id_jadwal = ?");
    $stmt->execute([$selected_jadwal]);
    $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($jadwal) {
        $stmt = $pdo->prepare("SELECT k.* FROM kursi k WHERE k.id_studio = ? ORDER BY SUBSTRING(k.kode_kursi, 1, 1), CAST(SUBSTRING(k.kode_kursi, 2) AS UNSIGNED)");
        $stmt->execute([$jadwal['id_studio']]);
        $semua_kursi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT id_kursi FROM booking WHERE id_jadwal = ?");
        $stmt->execute([$selected_jadwal]);
        $kursi_terisi = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
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
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: white; margin-bottom: 10px; font-size: 2.2rem; }
        .progress-steps { display: flex; justify-content: center; margin-bottom: 30px; }
        .step { display: flex; flex-direction: column; align-items: center; margin: 0 20px; }
        .step-number { width: 40px; height: 40px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 8px; }
        .step.active .step-number { background: #4CAF50; color: white; }
        .step-label { color: white; font-weight: 500; }
        .step.active .step-label { color: #4CAF50; font-weight: bold; }
        .booking-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        
        /* UKURAN POSTER STANDAR 2:3 */
        .film-header { display: flex; gap: 25px; margin-bottom: 20px; align-items: flex-start; }
        .poster-container { width: 200px; flex-shrink: 0; }
        .film-poster { width: 100%; height: 300px; object-fit: cover; border-radius: 10px; }
        .no-poster { width: 100%; height: 300px; background: #ddd; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #666; }
        
        .film-info { flex: 1; }
        .film-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; color: #333; }
        .film-price { font-size: 18px; color: #667eea; font-weight: bold; margin-bottom: 10px; }
        
        .section { margin: 25px 0; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
        .jadwal-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
        .jadwal-item { padding: 15px; border: 2px solid #ddd; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .jadwal-item.selected { background: #667eea; color: white; border-color: #667eea; }
        .jadwal-item input[type="radio"] { display: none; }
        
        .selected-seats-info { background: #f5f5f5; border-radius: 8px; padding: 15px; margin-bottom: 25px; }
        .selected-seats-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .seat-tag { background: #4CAF50; color: white; padding: 5px 12px; border-radius: 20px; font-weight: bold; }
        
        .cinema-screen { text-align: center; background: #333; color: white; padding: 15px; margin: 25px 0; border-radius: 5px; font-weight: bold; }
        .seats-container { display: flex; flex-direction: column; align-items: center; gap: 10px; margin-bottom: 30px; }
        .seat-row { display: flex; gap: 8px; }
        .seat { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; transition: all 0.2s; font-size: 0.8rem; }
        .seat.available { background: #e0e0e0; border: 1px solid #ccc; }
        .seat.available:hover { background: #d0d0d0; transform: scale(1.05); }
        .seat.selected { background: #4CAF50; color: white; border: 1px solid #388E3C; }
        .seat.occupied { background: #f44336; color: white; cursor: not-allowed; }
        
        .seat-info { display: flex; justify-content: center; gap: 20px; margin-bottom: 20px; }
        .seat-legend { display: flex; align-items: center; gap: 5px; }
        .legend-color { width: 20px; height: 20px; border-radius: 4px; }
        .available-color { background: #e0e0e0; border: 1px solid #ccc; }
        .selected-color { background: #4CAF50; }
        .occupied-color { background: #f44336; }
        
        .total-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .total-price { font-size: 24px; font-weight: bold; color: #667eea; }
        .actions { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn { padding: 12px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: all 0.3s; }
        .btn-back { background: #e0e0e0; color: #333; }
        .btn-next { background: #4CAF50; color: white; }
        .btn-next:disabled { background: #ccc; cursor: not-allowed; }
        .error { background: #ffebee; color: #d32f2f; padding: 15px; border-radius: 5px; margin: 15px 0; }
        
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .film-header { flex-direction: column; text-align: center; }
            .poster-container { width: 150px; margin: 0 auto; }
            .film-poster, .no-poster { height: 225px; }
            .seat { width: 35px; height: 35px; }
            .progress-steps { flex-wrap: wrap; gap: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pilih Kursi Bioskop</h1>
        </div>
        
        <div class="progress-steps">
            <div class="step <?= empty($selected_jadwal) ? 'active' : '' ?>">
                <div class="step-number">1</div>
                <div class="step-label">Pilih Jadwal</div>
            </div>
            <div class="step <?= !empty($selected_jadwal) ? 'active' : '' ?>">
                <div class="step-number">2</div>
                <div class="step-label">Pilih Kursi</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Konfirmasi</div>
            </div>
        </div>
        
        <div class="booking-card">
            <div class="film-header">
                <div class="poster-container">
                    <?php if (!empty($film['poster'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($film['poster']) ?>" alt="<?= htmlspecialchars($film['nama']) ?>" class="film-poster">
                    <?php else: ?>
                        <div class="no-poster">No Poster</div>
                    <?php endif; ?>
                </div>
                <div class="film-info">
                    <div class="film-title"><?= htmlspecialchars($film['nama']) ?></div>
                    <div class="film-price">Rp <?= number_format($film['harga'], 0, ',', '.') ?> / tiket</div>
                    <p><?= htmlspecialchars($film['deskripsi']) ?></p>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <?php if(empty($selected_jadwal)): ?>
                <div class="section">
                    <div class="section-title">🎬 Pilih Jadwal Tayang</div>
                    <form method="POST">
                        <div class="jadwal-grid">
                            <?php foreach($jadwals as $jadwal): ?>
                                <label>
                                    <div class="jadwal-item <?= $selected_jadwal == $jadwal['id_jadwal'] ? 'selected' : '' ?>">
                                        <input type="radio" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>" onchange="this.form.submit()" <?= $selected_jadwal == $jadwal['id_jadwal'] ? 'checked' : '' ?>>
                                        <div style="font-weight: bold;"><?= date('H:i', strtotime($jadwal['jam_tayang'])) ?></div>
                                        <div><?= date('d/m/Y', strtotime($jadwal['tanggal_tayang'])) ?></div>
                                        <div style="font-size: 12px;">Studio <?= $jadwal['nama_studio'] ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="pilih_jadwal" value="1">
                    </form>
                </div>
            <?php else: ?>
                <?php if(!empty($kursi_tersedia)): ?>
                    <div class="selected-seats-info">
                        <h3>Kursi Terpilih: <span><?= count($selected_kursi) ?></span> tiket</h3>
                        <div class="selected-seats-list">
                            <?php foreach($selected_kursi as $kursi): ?>
                                <div class="seat-tag"><?= $kursi['kode'] ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="cinema-screen">LAYAR</div>
                    
                    <div class="seats-container">
                        <?php
                        $rows = [];
                        foreach($kursi_tersedia as $kursi) {
                            $row = substr($kursi['kode_kursi'], 0, 1);
                            $rows[$row][] = $kursi;
                        }
                        foreach($rows as $row => $seats): ?>
                            <div class="seat-row">
                                <?php foreach($seats as $kursi): 
                                    $is_selected = in_array($kursi['id_kursi'], array_column($selected_kursi, 'id'));
                                ?>
                                    <div class="seat <?= !$kursi['tersedia'] ? 'occupied' : '' ?> <?= $is_selected ? 'selected' : 'available' ?>">
                                        <?php if($kursi['tersedia']): ?>
                                            <form method="POST" style="display: contents;">
                                                <button type="submit" name="pilih_kursi" style="all: unset; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                                    <?= $kursi['kode_kursi'] ?>
                                                </button>
                                                <input type="hidden" name="kursi_id" value="<?= $kursi['id_kursi'] ?>">
                                                <input type="hidden" name="kursi_kode" value="<?= $kursi['kode_kursi'] ?>">
                                            </form>
                                        <?php else: ?>
                                            <span><?= $kursi['kode_kursi'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="seat-info">
                        <div class="seat-legend"><div class="legend-color available-color"></div><span>Tersedia</span></div>
                        <div class="seat-legend"><div class="legend-color selected-color"></div><span>Terpilih</span></div>
                        <div class="seat-legend"><div class="legend-color occupied-color"></div><span>Terisi</span></div>
                    </div>

                    <div class="total-box">
                        <div>Jumlah Tiket: <?= count($selected_kursi) ?></div>
                        <div>Total: <span class="total-price">Rp <?= number_format($film['harga'] * count($selected_kursi), 0, ',', '.') ?></span></div>
                    </div>

                    <div class="actions">
                        <form method="POST"><button type="submit" name="pilih_jadwal" class="btn btn-back">Kembali</button></form>
                        <form method="POST"><button type="submit" name="submit_booking" class="btn btn-next" <?= count($selected_kursi) == 0 ? 'disabled' : '' ?>>Pesan <?= count($selected_kursi) ?> Tiket</button></form>
                    </div>
                <?php else: ?>
                    <div class="section"><p style="text-align: center;">Memuat kursi...</p></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>