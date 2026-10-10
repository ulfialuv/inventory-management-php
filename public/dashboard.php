<?php
// public/dashboard.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['user']['role'];
$username = $_SESSION['user']['username'];

// Hitung Statistik Dashboard
$total_produk = $pdo->query("SELECT COUNT(id) FROM products")->fetchColumn() ?? 0;
$total_nilai = $pdo->query("SELECT SUM(stock * price) FROM products")->fetchColumn() ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Inventory App</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f0f4f8; color: #333; }
        
        .navbar { background-color: #1e3a8a; color: white; padding: 16px 35px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .navbar-brand { font-size: 19px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .navbar-user { display: flex; align-items: center; gap: 15px; font-size: 14px; }
        .badge-admin { background-color: #dc2626; color: white; padding: 3px 9px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .btn-logout { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: background 0.2s; }
        .btn-logout:hover { background: rgba(255, 255, 255, 0.25); }

        .container { max-width: 1150px; margin: 35px auto; padding: 0 20px; }
        
        .welcome-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 30px; border-left: 6px solid #1e3a8a; }
        .welcome-card h2 { color: #1e3a8a; font-size: 24px; font-weight: 700; margin-bottom: 5px; }
        .welcome-card p { color: #64748b; font-size: 14px; }

        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 35px; }
        .card-stat { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); border-top: 4px solid #1e3a8a; }
        .card-stat h3 { color: #64748b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; font-weight: 600; }
        .card-stat .value { font-size: 28px; font-weight: 700; color: #1e293b; }

        .menu-title { font-size: 18px; font-weight: 700; color: #1e3a8a; margin-bottom: 15px; }
        .grid-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
        .card-menu { background: white; padding: 22px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); text-decoration: none; color: #333; display: flex; align-items: center; gap: 15px; border: 1px solid #e2e8f0; transition: all 0.2s; }
        .card-menu:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -4px rgba(30, 58, 138, 0.1); border-color: #1e3a8a; }
        .card-menu .menu-icon { font-size: 28px; background: #eef2f7; padding: 12px; border-radius: 10px; }
        .card-menu .menu-text h4 { font-size: 16px; color: #1e3a8a; margin-bottom: 3px; font-weight: 600; }
        .card-menu .menu-text p { font-size: 13px; color: #64748b; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="navbar-brand">📦 Inventory App</div>
        <div class="navbar-user">
            <span>Selamat Datang, <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></span>
            <span class="badge-admin"><?php echo strtoupper(htmlspecialchars($user_role, ENT_QUOTES, 'UTF-8')); ?></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Dashboard Panel Utama</h2>
            <p>Selamat datang di sistem manajemen kontrol logistik persediaan barang gudang terintegrasi.</p>
        </div>

        <div class="grid-stats">
            <div class="card-stat">
                <h3>Total Macam Produk</h3>
                <div class="value"><?php echo $total_produk; ?> Barang</div>
            </div>
            <div class="card-stat" style="border-top-color: #16a34a;">
                <h3>Nilai Total Inventory</h3>
                <div class="value" style="color: #16a34a;">Rp <?php echo number_format($total_nilai, 0, ',', '.'); ?></div>
            </div>
        </div>

        <h3 class="menu-title">Menu Manajemen Kontrol</h3>
        <div class="grid-menu">
            <a href="products/index.php" class="card-menu">
                <div class="menu-icon">📦</div>
                <div class="menu-text">
                    <h4>Master Produk</h4>
                    <p>Kelola data, edit info, pencarian produk</p>
                </div>
            </a>
            <a href="stock/index.php" class="card-menu">
                <div class="menu-icon">🔄</div>
                <div class="menu-text">
                    <h4>Transaksi Stok</h4>
                    <p>Catat mutasi barang masuk & keluar</p>
                </div>
            </a>
        </div>
    </div>

</body>
</html>