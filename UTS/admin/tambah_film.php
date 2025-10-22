<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // proses upload file
    $poster_name = '';
    if(isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $poster_name = time() . '_' . basename($_FILES['poster']['name']);
        $target_path = '../uploads/' . $poster_name;
        move_uploaded_file($_FILES['poster']['tmp_name'], $target_path);
    }

    $sql = "INSERT INTO film (nama, deskripsi, genre, harga, durasi, poster) 
            VALUES (:nama, :deskripsi, :genre, :harga, :durasi, :poster)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nama' => $_POST['nama'],
        ':deskripsi' => $_POST['deskripsi'],
        ':genre' => $_POST['genre'],
        ':harga' => $_POST['harga'],
        ':durasi' => $_POST['durasi'],
        ':poster' => $poster_name
    ]);
    
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Film</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; }
        .navbar h2 { margin: 0; }
        .container { max-width: 600px; margin: 30px auto; background: white; padding: 30px; border-radius: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn-back { background: #ccc; color: #333; text-decoration: none; display: inline-block; padding: 12px 30px; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🎬 Tambah Film Baru</h2>
    </div>
    
    <div class="container">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Film *</label>
                <input type="text" name="nama" required>
            </div>
            
            <div class="form-group">
                <label>Deskripsi *</label>
                <textarea name="deskripsi" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Genre *</label>
                <select name="genre" required>
                    <option value="">-- Pilih --</option>
                    <option value="Action">Action</option>
                    <option value="Romance">Romance</option>
                    <option value="Thriller">Thriller</option>
                    <option value="Sci-Fi">Sci-Fi</option>
                    <option value="Horror">Horror</option>
                    <option value="Comedy">Comedy</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Harga Tiket (Rp) *</label>
                <input type="number" name="harga" required>
            </div>
            
            <div class="form-group">
                <label>Durasi (menit) *</label>
                <input type="number" name="durasi" required>
            </div>
            
            <div class="form-group">
                <label>Poster Film *</label>
                <input type="file" name="poster" accept="image/*" required>
            </div>
            
            <a href="index.php" class="btn-back">Kembali</a>
            <button type="submit" class="btn">Simpan</button>
        </form>
    </div>
</body>
</html>
