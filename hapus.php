<?php
session_start();
include 'koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Hapus hanya jika ID tugas DAN user_id cocok dengan akun yang log in
mysqli_query(
    $conn,
    "DELETE FROM tugas WHERE id='$id' AND user_id='$user_id'"
);

header("Location: dashboard.php");
exit;
?>