<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $genre = $_POST['genre'];
    $harga = $_POST['harga'];
    $durasi = $_POST['durasi'];
    $poster = $_POST['poster'];
    
    $sql = "UPDATE film SET nama=:nama, deskripsi=:deskripsi, genre=:genre, 
            harga=:harga, durasi=:durasi, poster=:poster, photo_poster=:poster WHERE id_film=:id";
    
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
    
    header('Location: index.php?success=edit');
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
    <title>Edit Film - Admin Bioskop</title>
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
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .btn {
            padding: 10px 20px;
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

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #667eea;
            outline: none;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .current-poster {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .card {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
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

    <div class="container">
        <?php if(isset($_GET['success'])): ?>
            <div class="success-message">
                ✅ Film berhasil diperbarui!
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="card-title">✏️ Edit Film</div>
                <a href="index.php" class="btn btn-secondary">← Kembali</a>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Nama Film *</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($film['nama']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi *</label>
                    <textarea name="deskripsi" rows="4" required><?= htmlspecialchars($film['deskripsi']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Genre *</label>
                    <select name="genre" required>
                        <option value="Action" <?= $film['genre']=='Action'?'selected':'' ?>>Action</option>
                        <option value="Romance" <?= $film['genre']=='Romance'?'selected':'' ?>>Romance</option>
                        <option value="Thriller" <?= $film['genre']=='Thriller'?'selected':'' ?>>Thriller</option>
                        <option value="Sci-Fi" <?= $film['genre']=='Sci-Fi'?'selected':'' ?>>Sci-Fi</option>
                        <option value="Horror" <?= $film['genre']=='Horror'?'selected':'' ?>>Horror</option>
                        <option value="Comedy" <?= $film['genre']=='Comedy'?'selected':'' ?>>Comedy</option>
                        <option value="Drama" <?= $film['genre']=='Drama'?'selected':'' ?>>Drama</option>
                        <option value="Adventure" <?= $film['genre']=='Adventure'?'selected':'' ?>>Adventure</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Harga Tiket (Rp) *</label>
                    <input type="number" name="harga" value="<?= $film['harga'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Durasi (menit) *</label>
                    <input type="number" name="durasi" value="<?= $film['durasi'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Nama File Poster</label>
                    <input type="text" name="poster" value="<?= htmlspecialchars($film['poster']) ?>" placeholder="contoh: avengers.jpg">
                    <?php if (!empty($film['poster'])): ?>
                        <div class="current-poster">
                            <strong>Poster saat ini:</strong> <?= htmlspecialchars($film['poster']) ?>
                            <?php if(file_exists('../uploads/' . $film['poster'])): ?>
                                <br><small>✅ File poster ditemukan di folder uploads</small>
                            <?php else: ?>
                                <br><small>⚠️ File poster tidak ditemukan</small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">❌ Batal</a>
                    <button type="submit" class="btn btn-primary">💾 Update Film</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>