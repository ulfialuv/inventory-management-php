<?php
// views/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = $_SESSION['user']['username'] ?? 'Guest';
$role = $_SESSION['user']['role'] ?? 'staff';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; color: #333; }
        .global-navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .global-navbar .brand { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; text-decoration: none; color: white; }
        .global-navbar .user-info { display: flex; align-items: center; gap: 15px; font-size: 15px; }
        .global-navbar a.logout-link { color: #ffc107; text-decoration: none; font-weight: 600; padding: 6px 12px; border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 6px; transition: all 0.3s ease; }
        .global-navbar a.logout-link:hover { background: #ffc107; color: #1e3c72; }
        .global-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-adm { background-color: #dc3545; color: white; }
        .badge-stf { background-color: #28a745; color: white; }
    </style>
</head>
<body>

<div class="global-navbar">
    <a href="/inventory-app/public/dashboard.php" class="brand">📦 Inventory App</a>
    <div class="user-info">
        <span>Selamat Datang, <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></span>
        <span class="global-badge <?php echo $role === 'admin' ? 'badge-adm' : 'badge-stf'; ?>">
            <?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>
        </span>
        <a href="/inventory-app/public/logout.php" class="logout-link">Logout</a>
    </div>
</div>
