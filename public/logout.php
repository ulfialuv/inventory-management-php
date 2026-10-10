<?php
// public/logout.php
session_start();
session_unset();
session_destroy();

// Tendang kembali ke halaman login setelah logout
header("Location: login.php");
exit;
