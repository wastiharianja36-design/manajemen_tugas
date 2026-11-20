<?php

if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "tugas_mahasiswa";
} else {
    $host = "sql102.infinityfree.com";
    $user = "if0_43378399";
    $pass = "Wasti5242451002";
    $db   = "if0_43378399_db_MPTI";
}

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>