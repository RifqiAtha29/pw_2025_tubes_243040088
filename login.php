<?php
session_start();
require_once 'User.php';

// Konfigurasi koneksi database (sesuaikan dengan Laragon)
try {
    $db = new PDO('mysql:host=localhost;dbname=tubes2025_event_management', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $userModel = new User($db);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = $userModel->authenticate($_POST['username'], $_POST['password']);
        
        if ($user) {
            // Ambil data LENGKAP dari database
            $completeUser = $userModel->getById($user['id']);
            
            if ($completeUser) {
                // Hapus password sebelum simpan di session
                unset($completeUser['password']);
                
                // Simpan semua data ke session
                $_SESSION['user'] = $completeUser;
                
                // Debug: Cek isi session
                error_log("Session setelah login: " . print_r($_SESSION, true));
                
                // Redirect
                if ($completeUser['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: user_dashboard.php');
                }
                exit;
            }
        } else {
            $_SESSION['error'] = "Username atau password salah";
        }
    }
} catch (PDOException $e) {
    // Tangkap error koneksi/query
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Tampilkan form login
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .login-container h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']) ?>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username atau Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
            <div class="links">
                <a href="register.php">Daftar Akun Baru</a>
                <a href="forgot_password.php">Lupa Password?</a>
            </div>
        </form>
    </div>
</body>
</html>
