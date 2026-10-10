<?php
// helpers/validation.php

// Fungsi untuk memvalidasi apakah angka inputan bernilai positif (tidak minus)
function apakahAngkaPositif($nilai) {
    return is_numeric($nilai) && $nilai >= 0;
}

// Fungsi untuk memvalidasi panjang teks inputan (minimal 3 karakter)
function validasiPanjangTeks($teks, $minimal = 3) {
    return strlen(trim($teks)) >= $minimal;
}
