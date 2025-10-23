<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
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

    $sql = "INSERT INTO film (nama, deskripsi, genre, harga, durasi, poster, photo_poster, jadwal_tayang) 
            VALUES (:nama, :deskripsi, :genre, :harga, :durasi, :poster, :photo_poster, :jadwal_tayang)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nama' => $_POST['nama'],
        ':deskripsi' => $_POST['deskripsi'],
        ':genre' => $_POST['genre'],
        ':harga' => $_POST['harga'],
        ':durasi' => $_POST['durasi'],
        ':poster' => $poster_name,
        ':photo_poster' => $poster_name,
        ':jadwal_tayang' => 'Sekarang Tayang'
    ]);
    
    header('Location: index.php?success=tambah');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Film - Admin Bioskop</title>
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

        .file-info {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
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
                ✅ Film berhasil ditambahkan!
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="card-title">➕ Tambah Film Baru</div>
                <a href="index.php" class="btn btn-secondary">Kembali</a>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Film *</label>
                    <input type="text" name="nama" placeholder="Contoh: Avengers: Endgame" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi *</label>
                    <textarea name="deskripsi" rows="4" placeholder="Deskripsi singkat tentang film..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Genre *</label>
                    <select name="genre" required>
                        <option value="">-- Pilih Genre --</option>
                        <option value="Action">Action</option>
                        <option value="Romance">Romance</option>
                        <option value="Thriller">Thriller</option>
                        <option value="Sci-Fi">Sci-Fi</option>
                        <option value="Horror">Horror</option>
                        <option value="Comedy">Comedy</option>
                        <option value="Drama">Drama</option>
                        <option value="Adventure">Adventure</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Harga Tiket (Rp) *</label>
                    <input type="number" name="harga" placeholder="50000" required>
                </div>
                
                <div class="form-group">
                    <label>Durasi (menit) *</label>
                    <input type="number" name="durasi" placeholder="120" required>
                </div>
                
                <div class="form-group">
                    <label>Poster Film *</label>
                    <input type="file" name="poster" accept="image/*" required>
                    <div class="file-info">
                        Format: JPG, PNG, GIF | Maksimal: 2MB
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Simpan Film</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>