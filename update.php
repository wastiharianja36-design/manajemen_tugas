<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id                = $_POST['id'];
    $user_id           = $_SESSION['user_id'];
    $judul             = trim($_POST['judul']);
    $matkul            = trim($_POST['matkul']);
    $deadline          = $_POST['deadline'];
    $tingkat_kesulitan = $_POST['tingkat_kesulitan'];
    $status            = $_POST['status'];
    $deskripsi         = trim($_POST['deskripsi']);
    $url_link          = trim($_POST['url_link']);
    $is_pinned         = isset($_POST['is_pinned']) ? 1 : 0;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE tugas SET 
        judul = ?, 
        matkul = ?, 
        deadline = ?, 
        tingkat_kesulitan = ?, 
        status = ?, 
        deskripsi = ?, 
        url_link = ?, 
        is_pinned = ? 
        WHERE id = ? AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssiii",
        $judul,
        $matkul,
        $deadline,
        $tingkat_kesulitan,
        $status,
        $deskripsi,
        $url_link,
        $is_pinned,
        $id,
        $user_id
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: dashboard.php");
exit;
?>