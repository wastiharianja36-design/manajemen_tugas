<?php

include 'koneksi.php';

$judul = $_POST['judul'];
$matkul = $_POST['matkul'];
$deadline = $_POST['deadline'];
$deskripsi = $_POST['deskripsi'];
$status = $_POST['status'];

mysqli_query(
    $conn,

    "INSERT INTO tugas
    (judul, matkul, deadline, deskripsi, status)

    VALUES

    ('$judul',
    '$matkul',
    '$deadline',
    '$deskripsi',
    '$status')"
);

header("Location: dashboard.php");

?>