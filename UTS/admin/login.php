<?php
// ============================================
// FILE: admin/login.php
// Login untuk Admin
// ============================================

// Memuat konfigurasi database dan sesi
require_once '../config.php';

// Cek apakah ada data yang dikirim melalui metode POST
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil data dari form
    $email = $_POST['email'];
    // Melakukan hashing MD5 pada password yang diinput
    $pass = md5($_POST['password']);
    
    // Prepared Statement untuk mencari user berdasarkan email dan password
    // (Error terjadi di baris 14 saat mencoba menjalankan query ini sebelum tabel dibuat)
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? AND pass = ?");
    $stmt->execute([$email, $pass]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifikasi user dan cek apakah email mengandung string 'admin'
    if($user && strpos($user['email'], 'admin') !== false) {
        // Login Berhasil - Buat variabel session
        $_SESSION['admin_id'] = $user['id_user'];
        $_SESSION['admin_nama'] = $user['nama'];
        // Redirect ke halaman admin utama
        header('Location: index.php');
        exit;
    } else {
        // Login Gagal
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <style>
        body { font-family: Arial; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: white; padding: 40px; border-radius: 10px; width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
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
    <div class="login-box">
        <h1>🔐 Login Admin</h1>
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="admin@bioskop.com" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="link">
            <a href="../user/index.php">← Kembali ke Halaman User</a>
        </div>
    </div>
</body>
</html>