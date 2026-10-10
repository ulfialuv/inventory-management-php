<?php
// public/login.php
session_start();
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Menggunakan Prepared Statement agar aman dari SQL Injection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Mengubah verifikasi ke teks biasa demi kelancaran testing
        if ($user && hash('sha256', $password) === $user['password_hash']) {
            session_regenerate_id(true); // Menjaga keamanan session fixation
            
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];

            // Jika sukses, lempar ke halaman dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Semua field wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory System</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 35px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 380px;
            transition: transform 0.3s ease;
        }
        .login-container:hover {
            transform: translateY(-5px);
        }
        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand-header .icon {
            font-size: 45px;
            margin-bottom: 5px;
            display: inline-block;
        }
        .brand-header h2 {
            margin: 0;
            color: #1e3c72;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .brand-header p {
            margin: 5px 0 0 0;
            color: #777;
            font-size: 14px;
        }
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }
        .input-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5ee;
            border-radius: 8px;
            font-size: 15px;
            color: #333;
            background-color: #fff;
            transition: all 0.3s ease;
        }
        .input-group input:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.15);
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
            transition: all 0.3s ease;
        }
        button:hover {
            background: linear-gradient(135deg, #1a3565 0%, #214482 100%);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.4);
            transform: scale(1.01);
        }
        button:active {
            transform: scale(0.99);
        }
        .error-container {
            background-color: #ffeef0;
            border-left: 4px solid #f25c6c;
            color: #d92438;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="brand-header">
        <span class="icon">📦</span>
        <h2>Inventory App</h2>
        <p>Sistem Manajemen Stok & Gudang</p>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="error-container">
            <strong>⚠️</strong> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- Atribut autocomplete="off" memaksa kolom langsung kosong saat dibuka pertama kali -->
    <form action="" method="POST" autocomplete="off">
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username Anda" autocomplete="none" required>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password Anda" autocomplete="new-password" required>
        </div>
        <button type="submit">Masuk ke Sistem</button>
    </form>
</div>

</body>
</html>
