<?php
// public/stock/index.php
session_start();
require_once '../../config/database.php';

// Proteksi Keamanan: Jika belum login, tendang ke login
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$errors = [];
$success = '';

// Ambil semua produk dari database untuk pilihan dropdown menu
$products = $pdo->query("SELECT id, name, sku, stock FROM products")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $type = $_POST['type']; // 'IN' atau 'OUT'
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user']['id']; // ID user yang sedang login

    // --- VALIDASI INPUT ---
    if (empty($product_id) || empty($type) || $quantity <= 0) {
        $errors[] = "Semua kolom wajib diisi dan jumlah harus lebih besar dari 0!";
    }

    if (empty($errors)) {
        // --- PROSES BUSINESS LOGIC DENGAN DATABASE TRANSACTION ---
        $pdo->beginTransaction(); // Mulai transaksi aman

        try {
            // 1. Cek stok produk saat ini di gudang
            $stmt_check = $pdo->prepare("SELECT stock, name FROM products WHERE id = :id FOR UPDATE");
            $stmt_check->execute(['id' => $product_id]);
            $product = $stmt_check->fetch();

            if (!$product) {
                throw new Exception("Produk tidak ditemukan!");
            }

            // Aturan Bisnis: Barang keluar tidak boleh melebihi stok yang tersedia
            if ($type === 'OUT' && $product['stock'] < $quantity) {
                throw new Exception("Stok untuk '" . $product['name'] . "' tidak mencukupi! Sisa stok saat ini: " . $product['stock']);
            }

            // 2. Update jumlah stok di tabel products
            if ($type === 'IN') {
                $sql_update = "UPDATE products SET stock = stock + :qty WHERE id = :id";
            } else {
                $sql_update = "UPDATE products SET stock = stock - :qty WHERE id = :id";
            }
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute(['qty' => $quantity, 'id' => $product_id]);

            // 3. Catat riwayat mutasi stok ke tabel stock_movements
            $sql_log = "INSERT INTO stock_movements (product_id, user_id, type, quantity) 
                        VALUES (:product_id, :user_id, :type, :quantity)";
            $stmt_log = $pdo->prepare($sql_log);
            $stmt_log->execute([
                'product_id' => $product_id,
                'user_id' => $user_id,
                'type' => $type,
                'quantity' => $quantity
            ]);

            // Jika semua langkah di atas berhasil tanpa error, simpan permanen ke database!
            $pdo->commit();
            $success = "Transaksi Barang " . ($type === 'IN' ? 'Masuk' : 'Keluar') . " berhasil dicatat!";
            
            // Refresh data produk setelah stoknya berubah
            $products = $pdo->query("SELECT id, name, sku, stock FROM products")->fetchAll();
        } catch (Exception $e) {
            // Jika ada salah satu langkah yang gagal/error, batalkan semua perubahan stok agar data tidak korup!
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Stok - Inventory System</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f0f2f5;
            color: #333;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 550px;
            margin: 0 auto;
            background: white;
            padding: 40px 35px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border-top: 5px solid #2a5298;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            margin-bottom: 25px;
            color: #2a5298;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .btn-back:hover {
            color: #1e3c72;
            text-decoration: underline;
        }
        .header-title {
            margin-bottom: 30px;
            text-align: center;
        }
        .header-title h2 {
            color: #1e3c72;
            font-size: 24px;
            font-weight: 700;
        }
        .header-title p {
            color: #777;
            font-size: 14px;
            margin-top: 5px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5ee;
            border-radius: 8px;
            font-size: 15px;
            color: #333;
            background-color: #fff;
            transition: all 0.3s ease;
        }
        .input-group input:focus, .input-group select:focus {
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
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.2);
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        button:hover {
            background: linear-gradient(135deg, #1a3565 0%, #214482 100%);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.3);
            transform: scale(1.01);
        }
        button:active {
            transform: scale(0.99);
        }
        /* Kotak Notifikasi Estetik */
        .error-container {
            background-color: #ffeef0;
            border-left: 4px solid #f25c6c;
            color: #d92438;
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.5;
        }
        .success-container {
            background-color: #e6f9ed;
            border-left: 4px solid #28a745;
            color: #1e7e34;
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
    
    <div class="header-title">
        <h2>Pencatatan Stok Barang</h2>
        <p>Kelola mutasi penambahan atau pengurangan komoditas gudang</p>
    </div>

    <!-- Tampilkan Notifikasi Sukses / Error -->
    <?php if ($success): ?>
        <div class="success-container">
            🎉 <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-container">
            <?php foreach ($errors as $err): ?>
                ⚠️ <?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="input-group">
            <label for="product_id">Pilih Produk Barang</label>
            <select id="product_id" name="product_id" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?php echo $p['id']; ?>">
                        <?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?> (SKU: <?php echo htmlspecialchars($p['sku'], ENT_QUOTES, 'UTF-8'); ?> | Stok Saat Ini: <?php echo $p['stock']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input-group">
            <label for="type">Jenis Transaksi</label>
            <select id="type" name="type" required>
                <option value="IN">🔹 Barang Masuk (Tambah Stok)</option>
                <option value="OUT">🔸 Barang Keluar (Kurangi Stok)</option>
            </select>
        </div>

        <div class="input-group">
            <label for="quantity">Jumlah Barang (Quantity)</label>
            <input type="number" id="quantity" name="quantity" min="1" placeholder="Contoh: 10" required>
        </div>

        <button type="submit">Proses Transaksi Stok</button>
    </form>
</div>

</body>
</html>
