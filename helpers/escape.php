<?php
// helpers/escape.php

// Fungsi untuk mengamankan teks sebelum ditampilkan ke halaman HTML
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
