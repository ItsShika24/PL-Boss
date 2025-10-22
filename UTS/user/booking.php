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

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$film_id = $_GET['film_id'] ?? 0;

// Ambil data film dengan pengecekan null
$film = null;
if ($film_id > 0) {
    $sql = "SELECT * FROM film WHERE id_film = $film_id";
    $result = mysqli_query($user_conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $film = mysqli_fetch_assoc($result);
    }
}

// Jika film tidak ditemukan, redirect
if (!$film) {
    header("Location: index.php");
    exit();
}

// Ambil jadwal tayang
$schedules = [];
$sql = "SELECT j.*, s.nama_studio, s.kapasitas 
        FROM jadwal_tayang j 
        JOIN studio s ON j.id_studio = s.id_studio 
        WHERE j.id_film = $film_id 
        ORDER BY j.tanggal_tayang, j.jam_tayang";
$result = mysqli_query($user_conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = $row;
    }
}

// Inisialisasi variabel
$error = '';

// Proses booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jadwal_id = intval($_POST['jadwal_id'] ?? 0);
    $jumlah_tiket = intval($_POST['jumlah_tiket'] ?? 0);
    $selected_seats = $_POST['seats'] ?? [];
    
    // Validasi
    if ($jadwal_id === 0) {
        $error = "Silakan pilih jadwal tayang!";
    } elseif ($jumlah_tiket === 0) {
        $error = "Silakan pilih jumlah tiket!";
    } elseif (empty($selected_seats)) {
        $error = "Silakan pilih kursi!";
    } elseif (count($selected_seats) !== $jumlah_tiket) {
        $error = "Jumlah kursi (" . count($selected_seats) . ") tidak sesuai dengan jumlah tiket ($jumlah_tiket)!";
    } else {
        // Begin transaction
        mysqli_begin_transaction($user_conn);
        
        try {
            $total_harga = $jumlah_tiket * $film['harga'];
            
            // Buat booking utama
            $booking_sql = "INSERT INTO booking (id_user, id_jadwal, tanggal_pesan, jumlah_tiket, total_harga) 
                           VALUES (?, ?, CURDATE(), ?, ?)";
            $stmt = mysqli_prepare($user_conn, $booking_sql);
            mysqli_stmt_bind_param($stmt, "iiid", $_SESSION['user_id'], $jadwal_id, $jumlah_tiket, $total_harga);
            mysqli_stmt_execute($stmt);
            $booking_id = mysqli_insert_id($user_conn);
            
            // Update status kursi
            foreach ($selected_seats as $seat_id) {
                $seat_id = intval($seat_id);
                
                // Update kursi menjadi terisi
                $update_seat_sql = "UPDATE kursi SET status = 'terisi' WHERE id_kursi = ?";
                $stmt = mysqli_prepare($user_conn, $update_seat_sql);
                mysqli_stmt_bind_param($stmt, "i", $seat_id);
                mysqli_stmt_execute($stmt);
            }
            
            mysqli_commit($user_conn);
            
            // Set session success dan redirect
            $_SESSION['booking_success'] = "Pemesanan berhasil! ID Booking: #$booking_id";
            header("Location: my_bookings.php");
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($user_conn);
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket - <?php echo htmlspecialchars($film['nama'] ?? 'Film'); ?></title>
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

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .booking-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .movie-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .movie-poster {
            width: 150px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .movie-info h1 {
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        .movie-details {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .detail-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .booking-content {
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.4em;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .schedule-btn {
            padding: 15px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .schedule-btn:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .schedule-btn.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .seats-container {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
        }

        .screen {
            background: #333;
            color: white;
            text-align: center;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .seats-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        .seat {
            width: 35px;
            height: 35px;
            border: 2px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            font-weight: bold;
            transition: all 0.3s;
        }

        .seat:hover:not(.booked):not(.selected) {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .seat.selected {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
        }

        .seat.booked {
            background: #f44336;
            color: white;
            cursor: not-allowed;
        }

        .seat.available {
            background: #e8f5e8;
            border-color: #4caf50;
        }

        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .summary-title {
            font-size: 1.5em;
            margin-bottom: 20px;
            text-align: center;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .total-price {
            font-size: 1.4em;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid rgba(255, 255, 255, 0.5);
        }

        .book-now-btn {
            width: 100%;
            padding: 15px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .book-now-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .book-now-btn:disabled {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #c62828;
        }

        select {
            width: 200px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
        }

        @media (max-width: 968px) {
            .booking-content {
                grid-template-columns: 1fr;
            }
            
            .movie-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">⬅ Kembali ke Daftar Film</a>
        
        <div class="booking-card">
            <div class="movie-header">
                <div class="movie-poster" style="<?php 
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
                    <h1><?php echo htmlspecialchars($film['nama'] ?? 'Film'); ?></h1>
                    <div class="movie-details">
                        <div class="detail-item">🎭 <?php echo htmlspecialchars($film['genre'] ?? '-'); ?></div>
                        <div class="detail-item">⏱️ <?php echo htmlspecialchars($film['durasi'] ?? '0'); ?> menit</div>
                        <div class="detail-item">💰 Rp <?php echo number_format($film['harga'] ?? 0, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

            <div class="booking-content">
                <div class="booking-form">
                    <?php if (!empty($error)): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="film_id" value="<?php echo $film_id; ?>">
                        
                        <div class="form-section">
                            <h2 class="section-title">📅 Pilih Jadwal Tayang</h2>
                            <div class="schedule-grid" id="scheduleGrid">
                                <?php if (empty($schedules)): ?>
                                    <p style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #666;">
                                        Tidak ada jadwal tersedia untuk film ini
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($schedules as $schedule): ?>
                                        <div class="schedule-btn" data-schedule="<?php echo $schedule['id_jadwal']; ?>" data-studio="<?php echo $schedule['id_studio']; ?>">
                                            <div style="font-weight: bold; font-size: 1.1em;"><?php echo date('H:i', strtotime($schedule['jam_tayang'])); ?></div>
                                            <small><?php echo htmlspecialchars($schedule['nama_studio']); ?></small>
                                            <div style="font-size: 0.8em; margin-top: 5px;"><?php echo date('d M', strtotime($schedule['tanggal_tayang'])); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="jadwal_id" id="jadwal_id" required>
                        </div>

                        <div class="form-section">
                            <h2 class="section-title">🎫 Jumlah Tiket</h2>
                            <select name="jumlah_tiket" id="jumlah_tiket" required style="width: 200px; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 1em;">
                                <option value="">Pilih jumlah tiket</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> tiket</option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-section">
                            <h2 class="section-title">💺 Pilih Kursi</h2>
                            <div class="seats-container">
                                <div class="screen">🎬 L A Y A R 🎬</div>
                                <div class="seats-grid" id="seatsGrid">
                                    <p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                                        Silakan pilih jadwal tayang terlebih dahulu
                                    </p>
                                </div>
                                <div class="seat-legend">
                                    <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 5px;">
                                            <div class="seat available" style="cursor: default;"></div>
                                            <span>Tersedia</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 5px;">
                                            <div class="seat selected" style="cursor: default;"></div>
                                            <span>Dipilih</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 5px;">
                                            <div class="seat booked" style="cursor: default;"></div>
                                            <span>Terisi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden input untuk menyimpan kursi yang dipilih -->
                        <div id="seatsInputs"></div>

                        <button type="submit" class="book-now-btn" id="submitBtn" disabled>
                            🎟️ Konfirmasi Pemesanan
                        </button>
                    </form>
                </div>

                <div class="summary-card">
                    <h3 class="summary-title">📋 Ringkasan Pemesanan</h3>
                    <div class="summary-item">
                        <span>Film:</span>
                        <span id="summaryFilm"><?php echo htmlspecialchars($film['nama'] ?? '-'); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Harga per tiket:</span>
                        <span id="summaryHarga">Rp <?php echo number_format($film['harga'] ?? 0, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Jumlah tiket:</span>
                        <span id="summaryTiket">0</span>
                    </div>
                    <div class="summary-item">
                        <span>Kursi terpilih:</span>
                        <span id="summaryKursi">-</span>
                    </div>
                    <div class="total-price">
                        <span>Total:</span>
                        <span id="summaryTotal">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedSeats = [];
        let currentStudio = null;
        const ticketPrice = <?php echo $film['harga'] ?? 0; ?>;

        // Pilih jadwal
        document.querySelectorAll('.schedule-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.schedule-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                
                const jadwalId = this.getAttribute('data-schedule');
                const studioId = this.getAttribute('data-studio');
                
                document.getElementById('jadwal_id').value = jadwalId;
                currentStudio = studioId;
                
                // Tampilkan loading
                document.getElementById('seatsGrid').innerHTML = '<p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">Memuat kursi...</p>';
                
                loadSeats(studioId);
                checkFormValidity();
            });
        });

        // Load kursi
        function loadSeats(studioId) {
            if (!studioId) return;
            
            fetch('get_seats.php?studio_id=' + studioId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(seats => {
                    renderSeats(seats);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('seatsGrid').innerHTML = '<p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">Error memuat kursi</p>';
                });
        }

        function renderSeats(seats) {
            const seatsGrid = document.getElementById('seatsGrid');
            seatsGrid.innerHTML = '';
            
            if (!seats || seats.length === 0) {
                seatsGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">Tidak ada kursi tersedia</p>';
                return;
            }
            
            // Reset selected seats ketika ganti studio
            selectedSeats = [];
            updateSeatsInputs();
            updateSummary();
            
            seats.forEach(seat => {
                const seatElement = document.createElement('div');
                seatElement.className = 'seat';
                seatElement.textContent = seat.kode_kursi;
                seatElement.dataset.seatId = seat.id_kursi;
                seatElement.dataset.seatCode = seat.kode_kursi;

                if (seat.status === 'terisi') {
                    seatElement.classList.add('booked');
                } else {
                    seatElement.classList.add('available');
                    seatElement.addEventListener('click', () => toggleSeat(seat.id_kursi, seat.kode_kursi, seatElement));
                }
                
                seatsGrid.appendChild(seatElement);
            });
        }

        function toggleSeat(seatId, seatCode, element) {
            const maxTickets = parseInt(document.getElementById('jumlah_tiket').value) || 0;
            
            if (maxTickets === 0) {
                alert('Silakan pilih jumlah tiket terlebih dahulu');
                return;
            }
            
            if (selectedSeats.length >= maxTickets && !selectedSeats.find(s => s.id === seatId)) {
                alert(`Anda hanya dapat memilih ${maxTickets} kursi`);
                return;
            }

            const existingIndex = selectedSeats.findIndex(s => s.id === seatId);
            
            if (existingIndex > -1) {
                selectedSeats.splice(existingIndex, 1);
                element.classList.remove('selected');
            } else {
                selectedSeats.push({ id: seatId, code: seatCode });
                element.classList.add('selected');
            }

            updateSeatsInputs();
            updateSummary();
            checkFormValidity();
        }

        // Update jumlah tiket
        document.getElementById('jumlah_tiket').addEventListener('change', function() {
            const maxTickets = parseInt(this.value) || 0;
            
            if (selectedSeats.length > maxTickets) {
                // Kurangi selected seats jika melebihi batas baru
                selectedSeats = selectedSeats.slice(0, maxTickets);
                
                // Update tampilan kursi
                document.querySelectorAll('.seat.selected').forEach((seat, index) => {
                    if (index >= maxTickets) {
                        seat.classList.remove('selected');
                    }
                });
                
                updateSeatsInputs();
            }
            
            updateSummary();
            checkFormValidity();
        });

        function updateSeatsInputs() {
            const seatsInputs = document.getElementById('seatsInputs');
            seatsInputs.innerHTML = '';
            
            selectedSeats.forEach((seat, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `seats[${index}]`;
                input.value = seat.id;
                seatsInputs.appendChild(input);
            });
        }

        function updateSummary() {
            const ticketCount = selectedSeats.length;
            const totalPrice = ticketCount * ticketPrice;

            document.getElementById('summaryTiket').textContent = ticketCount;
            document.getElementById('summaryKursi').textContent = selectedSeats.map(s => s.code).join(', ') || '-';
            document.getElementById('summaryTotal').textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
        }

        function checkFormValidity() {
            const jadwalSelected = document.getElementById('jadwal_id').value !== '';
            const tiketSelected = document.getElementById('jumlah_tiket').value !== '';
            const seatsSelected = selectedSeats.length > 0;
            const seatsMatchTiket = selectedSeats.length === parseInt(document.getElementById('jumlah_tiket').value || 0);

            const isValid = jadwalSelected && tiketSelected && seatsSelected && seatsMatchTiket;
            
            document.getElementById('submitBtn').disabled = !isValid;
        }

        // Inisialisasi summary
        updateSummary();
        checkFormValidity();
    </script>
</body>
</html>