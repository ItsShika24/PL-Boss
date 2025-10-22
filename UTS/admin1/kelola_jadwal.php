<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Tambah jadwal baru
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_jadwal'])) {
    $id_film = $_POST['id_film'];
    $id_studio = $_POST['id_studio'];
    $tanggal_tayang = $_POST['tanggal_tayang'];
    $jam_tayang = $_POST['jam_tayang'];
    
    $sql = "INSERT INTO jadwal_tayang (id_film, id_studio, tanggal_tayang, jam_tayang) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_film, $id_studio, $tanggal_tayang, $jam_tayang]);
    
    header('Location: kelola_jadwal.php?success=1');
    exit;
}

// Hapus jadwal
if(isset($_GET['hapus_jadwal'])) {
    $id_jadwal = $_GET['hapus_jadwal'];
    $stmt = $pdo->prepare("DELETE FROM jadwal_tayang WHERE id_jadwal = ?");
    $stmt->execute([$id_jadwal]);
    
    header('Location: kelola_jadwal.php?success=1');
    exit;
}

// Ambil data film untuk dropdown
$films = $pdo->query("SELECT * FROM film ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);

// Ambil data studio untuk dropdown
$studios = $pdo->query("SELECT * FROM studio ORDER BY nama_studio")->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua jadwal
$sql = "SELECT jt.*, f.nama as nama_film, s.nama_studio 
        FROM jadwal_tayang jt
        JOIN film f ON jt.id_film = f.id_film
        JOIN studio s ON jt.id_studio = s.id_studio
        ORDER BY jt.tanggal_tayang DESC, jt.jam_tayang DESC";
$jadwals = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jadwal Tayang</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; }
        .navbar h2 { margin: 0; }
        .container { max-width: 1200px; margin: 30px auto; background: white; padding: 30px; border-radius: 10px; }
        .btn { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn-danger { background: #f44336; }
        .btn-success { background: #4CAF50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .form-container { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .success { background: #4CAF50; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>📅 Kelola Jadwal Tayang</h2>
    </div>
    
    <div class="container">
        <a href="index.php" class="btn">← Kembali</a>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="success">Jadwal berhasil diperbarui!</div>
        <?php endif; ?>
        
        <div class="form-container">
            <h3>Tambah Jadwal Baru</h3>
            <form method="POST">
                <input type="hidden" name="tambah_jadwal" value="1">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                    <div class="form-group">
                        <label>Film</label>
                        <select name="id_film" required>
                            <option value="">-- Pilih Film --</option>
                            <?php foreach($films as $film): ?>
                                <option value="<?= $film['id_film'] ?>"><?= htmlspecialchars($film['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Studio</label>
                        <select name="id_studio" required>
                            <option value="">-- Pilih Studio --</option>
                            <?php foreach($studios as $studio): ?>
                                <option value="<?= $studio['id_studio'] ?>"><?= $studio['nama_studio'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Tayang</label>
                        <input type="date" name="tanggal_tayang" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Jam Tayang</label>
                        <input type="time" name="jam_tayang" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">+ Tambah</button>
                    </div>
                </div>
            </form>
        </div>
        
        <h3>Daftar Jadwal Tayang</h3>
        <table>
            <thead>
                <tr>
                    <th>Film</th>
                    <th>Studio</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($jadwals as $jadwal): ?>
                <tr>
                    <td><?= htmlspecialchars($jadwal['nama_film']) ?></td>
                    <td><?= $jadwal['nama_studio'] ?></td>
                    <td><?= date('d/m/Y', strtotime($jadwal['tanggal_tayang'])) ?></td>
                    <td><?= date('H:i', strtotime($jadwal['jam_tayang'])) ?></td>
                    <td>
                        <a href="kelola_jadwal.php?hapus_jadwal=<?= $jadwal['id_jadwal'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus jadwal?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>