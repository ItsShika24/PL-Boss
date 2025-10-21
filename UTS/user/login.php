<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = md5($_POST['pass']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? AND pass = ?");
        $stmt->execute([$email, $pass]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Email atau password salah!";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User - Bioskop Online</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 400px; margin: 100px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        .error { color: red; margin-bottom: 10px; }
        .register-link { text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login User</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="pass" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</body>
</html>