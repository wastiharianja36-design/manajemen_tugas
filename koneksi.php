<?php

$host = "sql102.infinityfree.com";
$user = "if0_43378399";
$pass = "Wasti5242451002";
$db   = "if0_43378399_db_MPTI"; // Sesuaikan dengan nama database di phpMyAdmin Anda

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>