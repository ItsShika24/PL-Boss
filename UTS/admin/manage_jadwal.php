<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Tambah jadwal baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_jadwal'])) {
    $id_film = $_POST['id_film'];
    $id_studio = $_POST['id_studio'];
    $tanggal_tayang = $_POST['tanggal_tayang'];
    $jam_tayang = $_POST['jam_tayang'];

    $stmt = $pdo->prepare("INSERT INTO jadwal_tayang (id_film, id_studio, tanggal_tayang, jam_tayang) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_film, $id_studio, $tanggal_tayang, $jam_tayang]);

    header('Location: manage_jadwal.php?success=1');
    exit;
}

// Hapus jadwal
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM jadwal_tayang WHERE id_jadwal = ?");
    $stmt->execute([$_GET['hapus']]);

    header('Location: manage_jadwal.php?success=1');
    exit;
}

// Ambil data jadwal
$sql = "SELECT jt.*, f.nama AS nama_film, s.nama_studio 
        FROM jadwal_tayang jt
        JOIN film f ON jt.id_film = f.id_film
        JOIN studio s ON jt.id_studio = s.id_studio
        ORDER BY jt.tanggal_tayang DESC, jt.jam_tayang DESC";
$jadwals = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Ambil data film untuk dropdown
$films = $pdo->query("SELECT * FROM film ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);

// Ambil data studio untuk dropdown
$studios = $pdo->query("SELECT * FROM studio ORDER BY nama_studio")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Jadwal Tayang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f5f5;
            color: #333;
        }

        .navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            border-color: #667eea;
            outline: none;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            table {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="navbar">
        <h2>🎬 Admin Bioskop</h2>
        <div class="nav-links">
            <span>Halo, <?= $_SESSION['admin_nama'] ?></span>
            <a href="index.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="button-group">
        <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                ✅ Jadwal berhasil diperbarui!
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-title">➕ Tambah Jadwal Tayang Baru</div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Film</label>
                        <select name="id_film" required>
                            <option value="">Pilih Film</option>
                            <?php foreach ($films as $film): ?>
                                <option value="<?= $film['id_film'] ?>">
                                    <?= htmlspecialchars($film['nama']) ?> (<?= $film['genre'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Studio</label>
                        <select name="id_studio" required>
                            <option value="">Pilih Studio</option>
                            <?php foreach ($studios as $studio): ?>
                                <option value="<?= $studio['id_studio'] ?>">
                                    <?= $studio['nama_studio'] ?> (<?= $studio['kapasitas'] ?> kursi)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Tayang</label>
                        <input type="date" name="tanggal_tayang" min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Jam Tayang</label>
                        <input type="time" name="jam_tayang" required>
                    </div>
                </div>

                <button type="submit" name="tambah_jadwal" class="btn btn-primary">
                    💾 Simpan Jadwal
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-title">📅 Daftar Jadwal Tayang</div>

            <?php if (empty($jadwals)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>Belum ada jadwal tayang</h3>
                    <p>Silakan tambah jadwal tayang baru menggunakan form di atas.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Film</th>
                            <th>Studio</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jadwals as $jadwal): ?>
                            <tr>
                                <td><?= $jadwal['id_jadwal'] ?></td>
                                <td><?= htmlspecialchars($jadwal['nama_film']) ?></td>
                                <td><?= $jadwal['nama_studio'] ?></td>
                                <td><?= date('d/m/Y', strtotime($jadwal['tanggal_tayang'])) ?></td>
                                <td><?= date('H:i', strtotime($jadwal['jam_tayang'])) ?></td>
                                <td class="action-buttons">
                                    <a href="manage_jadwal.php?hapus=<?= $jadwal['id_jadwal'] ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('Yakin hapus jadwal ini?')">
                                        🗑️ Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>