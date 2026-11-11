<?php
$host = "sql102.infinityfree.com"; // Ganti sesuai MySQL Hostname dari InfinityFree
$user = "if0_43378399";           // MySQL Username Anda
$pass = "Wasti5242451002";  // Password akun InfinityFree (vpanel)
$db   = "if0_43378399_tugas";     // Nama Database di InfinityFree

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>