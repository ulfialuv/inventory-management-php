<?php
// public/products/index.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category_id'] ?? '');
$sort = trim($_GET['sort'] ?? 'name_asc'); // Default urutkan berdasarkan Nama A-Z

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.name LIKE :search OR p.sku LIKE :search)";
    $params['search'] = "%$search%";
}
if ($category_filter !== '') {
    $sql .= " AND p.category_id = :category_id";
    $params['category_id'] = $category_filter;
}

// Logika Sorting (Pengurutan Data)
switch ($sort) {
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'stock_asc':
        $sql .= " ORDER BY p.stock ASC";
        break;
    case 'stock_desc':
        $sql .= " ORDER BY p.stock DESC";
        break;
    default:
        $sql .= " ORDER BY p.name ASC";
        break;
}

$stmt_total = $pdo->prepare($sql);
$stmt_total->execute($params);
$total_data = $stmt_total->rowCount();
$total_pages = ceil($total_data / $perPage);

$sql .= " LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Produk - Inventory App</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f0f4f8; color: #333; }
        
        .navbar { background-color: #1e3a8a; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar-brand { font-size: 18px; font-weight: bold; }
        .navbar-user { display: flex; align-items: center; gap: 15px; font-size: 14px; }
        .badge-admin { background-color: #dc2626; color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .btn-logout { background: transparent; border: 1px solid rgba(255,255,255,0.4); color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; }
        .btn-logout:hover { background: rgba(255,255,255,0.1); }

        .main-container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #1e3a8a; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-back:hover { text-decoration: underline; }

        .alert-success { background: #f0fdf4; color: #166534; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #bbf7d0; font-weight: 500; }
        .alert-error { background: #fef2f2; color: #991b1b; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #fecaca; font-weight: 500; }

        .dashboard-card { background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; border-left: 5px solid #1e3a8a; margin-bottom: 25px; }
        
        .header-area { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        h2 { margin: 0 0 5px 0; color: #1e3a8a; font-size: 22px; }
        .subtitle { color: #64748b; font-size: 14px; margin: 0; }
        .btn-add { display: inline-block; padding: 10px 18px; background: #16a34a; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; }
        .btn-add:hover { background: #15803d; }

        .form-filter { display: flex; gap: 12px; margin-bottom: 25px; flex-wrap: wrap; background: #eef2f7; padding: 15px; border-radius: 8px; border: 1px solid #cbd5e1; }
        .form-filter input, .form-filter select { padding: 10px 14px; border: 1px solid #94a3b8; border-radius: 6px; font-size: 14px; outline: none; flex: 1; min-width: 180px; background: white; }
        .form-filter button { padding: 10px 20px; background: #1e3a8a; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; }
        .form-filter button:hover { background: #172554; }
        .btn-reset { padding: 10px 16px; background: #475569; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; }
        .btn-reset:hover { background: #334155; }

        .table-responsive { overflow-x: auto; margin-bottom: 20px; border-radius: 8px; border: 1px solid #cbd5e1; }
        table { width: 100%; border-collapse: collapse; background: white; white-space: nowrap; }
        th, td { padding: 12px 16px; text-align: left; }
        th { background-color: #1e3a8a; color: white; font-weight: 600; font-size: 13px; text-transform: uppercase; }
        td { font-size: 14px; border-bottom: 1px solid #f1f5f9; color: #333; }
        tbody tr:hover { background-color: #f8fafc; }
        
        .badge-stock { padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 12px; display: inline-block; }
        .stock-safe { background: #dcfce7; color: #166534; }
        .stock-low { background: #fee2e2; color: #991b1b; }
        
        .action-btns { display: flex; gap: 8px; }
        .btn-action { padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; text-decoration: none; }
        .btn-edit { background: #e0f2fe; color: #0369a1; }
        .btn-delete { background: #fee2e2; color: #991b1b; }

        .pagination-wrapper { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-top: 20px; }
        .pagination-info { font-size: 14px; color: #64748b; }
        .pagination { display: flex; gap: 6px; }
        .pagination a { padding: 8px 14px; border: 1px solid #94a3b8; color: #1e3a8a; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; }
        .pagination a:hover { background: #e2e8f0; }
        .pagination a.active { background: #1e3a8a; color: white; border-color: #1e3a8a; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="navbar-brand">📦 Inventory App</div>
        <div class="navbar-user">
            <span>Selamat Datang, <strong><?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'admin', ENT_QUOTES, 'UTF-8'); ?></strong></span>
            <span class="badge-admin">ADMIN</span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="main-container">
        <a href="../dashboard.php" class="btn-back">← Kembali ke Dashboard</a>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="dashboard-card">
            <div class="header-area">
                <div>
                    <h2>Daftar Produk Barang</h2>
                    <p class="subtitle">Kelola inventaris gudang, pencarian, filter, dan pengurutan data produk</p>
                </div>
                <a href="create.php" class="btn-add">+ Tambah Produk Baru</a>
            </div>

            <!-- Form Search, Filter, & Sorting -->
            <form method="GET" class="form-filter">
                <input type="text" name="search" placeholder="Cari Nama / SKU..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                
                <select name="category_id">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort">
                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Nama (A - Z)</option>
                    <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Nama (Z - A)</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                    <option value="stock_asc" <?php echo $sort === 'stock_asc' ? 'selected' : ''; ?>>Stok Sedikit</option>
                    <option value="stock_desc" <?php echo $sort === 'stock_desc' ? 'selected' : ''; ?>>Stok Terbanyak</option>
                </select>
                
                <button type="submit">Terapkan</button>
                <a href="index.php" class="btn-reset">Reset</a>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="6" style="text-align:center; color:#94a3b8; padding: 30px;">Belum ada data produk.</td></tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><strong style="color: #1e3a8a;"><?php echo htmlspecialchars($p['sku'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($p['category_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>Rp <?php echo number_format($p['price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php 
                                            $is_low = $p['stock'] <= ($p['minimum_stock'] ?? 5);
                                            $badge_class = $is_low ? 'stock-low' : 'stock-safe';
                                        ?>
                                        <span class="badge-stock <?php echo $badge_class; ?>"><?php echo $p['stock']; ?> Unit</span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="edit.php?id=<?php echo $p['id']; ?>" class="btn-action btn-edit">Edit</a>
                                            <a href="delete.php?id=<?php echo $p['id']; ?>" class="btn-action btn-delete">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <div class="pagination-info">Halaman <strong><?php echo $page; ?></strong> dari <strong><?php echo max(1, $total_pages); ?></strong></div>
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="index.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo $category_filter; ?>&sort=<?php echo $sort; ?>" class="<?php echo $page === $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>