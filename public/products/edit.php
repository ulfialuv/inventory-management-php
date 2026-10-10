<?php
// public/products/edit.php
session_start();
require_once '../../config/database.php';

// Proteksi Keamanan: Jika belum login, tendang ke login
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$errors = [];
$success = '';

// Ambil data lama produk yang akan diedit
$stmt_product = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt_product->execute(['id' => $id]);
$product = $stmt_product->fetch();

if (!$product) {
    die("Produk tidak ditemukan di sistem!");
}

// Ambil data semua kategori untuk opsi dropdown menu
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = trim($_POST['category_id']);
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $minimum_stock = intval($_POST['minimum_stock']);

    // --- INPUT VALIDATION ---
    if (empty($category_id) || empty($sku) || empty($name)) {
        $errors[] = "Semua kolom wajib diisi!";
    }

    if ($price < 0 || $stock < 0 || $minimum_stock < 0) {
        $errors[] = "Harga maupun kuantitas stok barang tidak boleh bernilai negatif!";
    }

    // Cek duplikasi SKU (SKU wajib unik, tapi abaikan jika itu kode milik produk ini sendiri)
    $stmt_cek = $pdo->prepare("SELECT id FROM products WHERE sku = :sku AND id != :id");
    $stmt_cek->execute(['sku' => $sku, 'id' => $id]);
    if ($stmt_cek->fetch()) {
        $errors[] = "Kode SKU '$sku' sudah digunakan oleh produk lain!";
    }

    // --- PROSES UPDATE DATA ---
    if (empty($errors)) {
        try {
            $sql = "UPDATE products 
                    SET category_id = :category_id, sku = :sku, name = :name, price = :price, stock = :stock, minimum_stock = :minimum_stock 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'category_id' => $category_id,
                'sku' => $sku,
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'minimum_stock' => $minimum_stock,
                'id' => $id
            ]);

            $success = "Informasi produk berhasil diperbarui!";
            
            // Perbarui ulang data produk di layar setelah disimpan
            $product = [
                'category_id' => $category_id, 'sku' => $sku, 'name' => $name,
                'price' => $price, 'stock' => $stock, 'minimum_stock' => $minimum_stock
            ];
        } catch (PDOException $e) {
            $errors[] = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Inventory System</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; color: #333; min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 550px; margin: 0 auto; background: white; padding: 40px 35px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-top: 5px solid #2a5298; }
        .btn-back { display: inline-flex; align-items: center; margin-bottom: 25px; color: #2a5298; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-back:hover { color: #1e3c72; text-decoration: underline; }
        .header-title { margin-bottom: 30px; text-align: center; }
        .header-title h2 { color: #1e3c72; font-size: 24px; font-weight: 700; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; color: #333; font-size: 14px; font-weight: 600; }
        .input-group input, .input-group select { width: 100%; padding: 12px 16px; border: 2px solid #e1e5ee; border-radius: 8px; font-size: 15px; }
        .input-group input:focus, .input-group select:focus { outline: none; border-color: #2a5298; box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.15); }
        button { width: 100%; padding: 14px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none; color: white; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 10px; }
        button:hover { background: linear-gradient(135deg, #1a3565 0%, #214482 100%); transform: scale(1.01); }
        .error-container { background-color: #ffeef0; border-left: 4px solid #f25c6c; color: #d92438; padding: 14px; border-radius: 6px; margin-bottom: 25px; font-size: 14px; }
        .success-container { background-color: #e6f9ed; border-left: 4px solid #28a745; color: #1e7e34; padding: 14px; border-radius: 6px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back">← Kembali ke Daftar Produk</a>
    
    <div class="header-title">
        <h2>Ubah Informasi Produk</h2>
    </div>

    <?php if ($success): ?>
        <div class="success-container">🎉 <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-container">
            <?php foreach ($errors as $err): ?>
                ⚠️ <?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
        <div class="input-group">
            <label for="category_id">Kategori Produk</label>
            <select id="category_id" name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input-group">
            <label for="sku">Kode SKU (Harus Unik)</label>
            <input type="text" id="sku" name="sku" value="<?php echo htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="input-group">
            <label for="name">Nama Produk Barang</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="input-group">
            <label for="price">Harga Jual Satuan (Rp)</label>
            <input type="number" id="price" name="price" min="0" value="<?php echo intval($product['price']); ?>" required>
        </div>

        <div class="input-group">
            <label for="stock">Jumlah Stok Barang</label>
            <input type="number" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
        </div>

        <div class="input-group">
            <label for="minimum_stock">Batas Stok Minimum Peringatan</label>
            <input type="number" id="minimum_stock" name="minimum_stock" min="0" value="<?php echo $product['minimum_stock']; ?>" required>
        </div>

        <button type="submit">Simpan Perubahan Data</button>
    </form>
</div>

</body>
</html>
