<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id           = $_SESSION['user_id'];
    $judul             = trim($_POST['judul']);
    $matkul            = trim($_POST['matkul']);
    $deadline          = $_POST['deadline'];
    $tingkat_kesulitan = $_POST['tingkat_kesulitan'];
    $deskripsi         = trim($_POST['deskripsi']);
    $url_link          = trim($_POST['url_link']);
    $is_pinned         = isset($_POST['is_pinned']) ? 1 : 0;
    $status            = "Belum";

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO tugas 
        (user_id, judul, matkul, deadline, deskripsi, tingkat_kesulitan, url_link, is_pinned, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "issssssis",
        $user_id,
        $judul,
        $matkul,
        $deadline,
        $deskripsi,
        $tingkat_kesulitan,
        $url_link,
        $is_pinned,
        $status
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: dashboard.php");
exit;
?>