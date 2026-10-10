<?php
// helpers/auth.php

// Fungsi untuk memastikan user sudah login sebelum membuka halaman
function memerlukanLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: /inventory-app/public/login.php");
        exit;
    }
}

// Fungsi untuk memastikan hanya Admin yang bisa mengakses halaman tertentu
function hanyaAdmin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        $_SESSION['error_message'] = "Akses Ditolak! Anda harus masuk sebagai Admin untuk mengakses fitur ini.";
        header("Location: /inventory-app/public/products/index.php");
        exit;
    }
}
