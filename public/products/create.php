<?php
// public/products/create.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$errors = [];
$success = '';
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = trim($_POST['category_id']);
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $minimum_stock = intval($_POST['minimum_stock']);

    if (empty($category_id) || empty($sku) || empty($name)) {
        $errors[] = "Semua kolom wajib diisi!";
    }
    if ($price < 0 || $stock < 0 || $minimum_stock < 0) {
        $errors[] = "Harga dan stok tidak boleh bernilai negatif!";
    }

    $stmt_cek = $pdo->prepare("SELECT id FROM products WHERE sku = :sku");
    $stmt_cek->execute(['sku' => $sku]);
    if ($stmt_cek->fetch()) {
        $errors[] = "Kode SKU '$sku' sudah digunakan oleh produk lain!";
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products (category_id, sku, name, price, stock, minimum_stock) 
                    VALUES (:category_id, :sku, :name, :price, :stock, :minimum_stock)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'category_id' => $category_id, 'sku' => $sku, 'name' => $name,
                'price' => $price, 'stock' => $stock, 'minimum_stock' => $minimum_stock
            ]);
            $success = "Produk baru berhasil ditambahkan ke gudang!";
        } catch (PDOException $e) {
            $errors[] = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - Inventory App</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f0f4f8; color: #333; padding: 40px 20px; }
        
        /* Container Card persis gaya Transaksi Stok */
        .container { 
            max-width: 650px; 
            margin: 0 auto; 
            background: white; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.06); 
            border-top: 6px solid #1e3a8a; 
        }
        
        .btn-back { display: inline-block; margin-bottom: 25px; color: #1e3a8a; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-back:hover { text-decoration: underline; }
        
        .header-title { text-align: center; margin-bottom: 30px; }
        .header-title h2 { color: #1e3a8a; font-size: 24px; font-weight: 700; margin-bottom: 5px; }
        .header-title p { color: #64748b; font-size: 14px; }

        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; color: #1e293b; font-weight: 600; font-size: 14px; }
        .input-group input, .input-group select { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
        .input-group input:focus, .input-group select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.12); }

        /* Tombol utama warna biru tua senada */
        button[type="submit"] { 
            width: 100%; 
            padding: 14px; 
            background: #1e3a8a; 
            border: none; 
            color: white; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 15px; 
            font-weight: 600; 
            margin-top: 15px; 
            transition: background 0.2s; 
        }
        button[type="submit"]:hover { background: #172554; }

        .error-box { background: #fef2f2; color: #991b1b; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #fecaca; }
        .success-box { background: #f0fdf4; color: #166534; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; border: 1px solid #bbf7d0; font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back">← Kembali ke Daftar Produk</a>
    
    <div class="header-title">
        <h2>Tambah Produk Baru</h2>
        <p>Masukkan informasi produk barang inventaris gudang</p>
    </div>

    <?php if ($success): ?>
        <div class="success-box"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $err): ?>
                • <?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="input-group">
            <label>Kategori Produk</label>
            <select name="category_id" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input-group">
            <label>Kode SKU (Harus Unik)</label>
            <input type="text" name="sku" placeholder="Contoh: LAP-ASUS-01" required>
        </div>

        <div class="input-group">
            <label>Nama Produk Barang</label>
            <input type="text" name="name" placeholder="Contoh: Laptop Asus Vivobook" required>
        </div>

        <div class="input-group">
            <label>Harga Jual Satuan (Rp)</label>
            <input type="number" name="price" min="0" placeholder="0" required>
        </div>

        <div class="input-group">
            <label>Stok Awal Barang</label>
            <input type="number" name="stock" min="0" placeholder="0" required>
        </div>

        <div class="input-group">
            <label>Batas Stok Minimum Peringatan</label>
            <input type="number" name="minimum_stock" min="0" value="5" required>
        </div>

        <button type="submit">Simpan Produk Baru</button>
    </form>
</div>

</body>
</html>