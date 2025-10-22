<?php
require_once '../config.php';

$id = $_GET['id'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $genre = $_POST['genre'];
    $harga = $_POST['harga'];
    $durasi = $_POST['durasi'];
    $poster = $_POST['poster'];
    
    $sql = "UPDATE film SET nama=:nama, deskripsi=:deskripsi, genre=:genre, 
            harga=:harga, durasi=:durasi, poster=:poster WHERE id_film=:id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nama' => $nama,
        ':deskripsi' => $deskripsi,
        ':genre' => $genre,
        ':harga' => $harga,
        ':durasi' => $durasi,
        ':poster' => $poster,
        ':id' => $id
    ]);
    
    header('Location: index.php');
    exit;
}

// Ambil data film
$stmt = $pdo->prepare("SELECT * FROM film WHERE id_film = ?");
$stmt->execute([$id]);
$film = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Film</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #667eea; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .btn-back { background: #ccc; color: #333; text-decoration: none; display: inline-block; padding: 12px 30px; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Film</h1>
        <form method="POST">
            <div class="form-group">
                <label>Nama Film</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($film['nama']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="4" required><?= htmlspecialchars($film['deskripsi']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Genre</label>
                <select name="genre" required>
                    <option value="Action" <?= $film['genre']=='Action'?'selected':'' ?>>Action</option>
                    <option value="Romance" <?= $film['genre']=='Romance'?'selected':'' ?>>Romance</option>
                    <option value="Thriller" <?= $film['genre']=='Thriller'?'selected':'' ?>>Thriller</option>
                    <option value="Sci-Fi" <?= $film['genre']=='Sci-Fi'?'selected':'' ?>>Sci-Fi</option>
                    <option value="Horror" <?= $film['genre']=='Horror'?'selected':'' ?>>Horror</option>
                    <option value="Comedy" <?= $film['genre']=='Comedy'?'selected':'' ?>>Comedy</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Harga Tiket (Rp)</label>
                <input type="number" name="harga" value="<?= $film['harga'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>Durasi (menit)</label>
                <input type="number" name="durasi" value="<?= $film['durasi'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>Poster (nama file)</label>
                <input type="text" name="poster" value="<?= htmlspecialchars($film['poster']) ?>">
            </div>
            
            <a href="index.php" class="btn-back">Kembali</a>
            <button type="submit" class="btn">Update Film</button>
        </form>
    </div>
</body>
</html>
