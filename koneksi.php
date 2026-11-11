<?php

$conn = mysqli_connect(
    "sql102.infinityfreeapp.com", // Ganti dengan MySQL Hostname dari InfinityFree
    "if0_43378399",              // Ganti dengan MySQL Username Anda
    "Wasti5242451002",     // Ganti dengan Password akun InfinityFree
    "if0_43378399_tugas"         // Ganti dengan Nama Database yang baru dibuat di InfinityFree
);
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>