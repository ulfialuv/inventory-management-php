<?php
// public/products/delete.php
session_start();
require_once '../../config/database.php';

// 1. Proteksi Keamanan: Jika belum login, tendang ke login
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

// 2. ATURAN BISNIS DOSEN: Cek apakah yang login adalah staff
if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'staff') {
    $_SESSION['error'] = "Akses Ditolak! Akun Staff tidak diizinkan untuk menghapus data produk.";
    header("Location: index.php");
    exit;
}

// 3. Ambil ID dari parameter URL
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "ID Produk tidak valid!";
    header("Location: index.php");
    exit;
}

try {
    // 4. Eksekusi Query Hapus Menggunakan Prepared Statement
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $_SESSION['success'] = "Data produk berhasil dihapus dari sistem!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal menghapus produk: " . $e->getMessage();
}

header("Location: index.php");
exit;
?>