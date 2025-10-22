<?php
session_start();
require_once '../config.php';

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $no_telp = $_POST['no_telp'];
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);
    
    // Validasi
    if($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $pdo->prepare("SELECT id_user FROM user WHERE email = ?");
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            // Insert user baru
            $stmt = $pdo->prepare("INSERT INTO user (nama, email, no_telp, pass) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $no_telp, $password]);
            
            $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register User</title>
    <style>
        body { font-family: Arial; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .register-box { background: white; padding: 40px; border-radius: 10px; width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        h1 { color: #667eea; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn:hover { background: #5568d3; }
        .error { background: #f44336; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .link { text-align: center; margin-top: 15px; }
        .link a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="register-box">
        <h1>📝 Daftar Akun Baru</h1>
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Nama lengkap" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="user@email.com" required>
            </div>
            
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="no_telp" placeholder="081234567890" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm_password" placeholder="Ulangi password" required>
            </div>
            
            <button type="submit" class="btn">Daftar</button>
        </form>
        
        <div class="link">
            <a href="login.php">Sudah punya akun? Login</a>
        </div>
    </div>
</body>
</html>
