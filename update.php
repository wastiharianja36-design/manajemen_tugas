<?php

include 'koneksi.php';

$id = $_POST['id'];
$judul = $_POST['judul'];
$matkul = $_POST['matkul'];
$deadline = $_POST['deadline'];
$status = $_POST['status'];

mysqli_query(
    $conn,
    "UPDATE tugas SET
    judul='$judul',
    matkul='$matkul',
    deadline='$deadline',
    status='$status'
    WHERE id='$id'"
);

header("Location: dashboard.php");

?>