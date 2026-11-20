<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id           = (int)$_SESSION['user_id'];
    $judul             = trim($_POST['judul']);
    $matkul            = trim($_POST['matkul']);
    $deadline          = $_POST['deadline'];
    $tingkat_kesulitan = $_POST['tingkat_kesulitan'];
    $deskripsi         = trim($_POST['deskripsi']);
    $url_link          = trim($_POST['url_link']);
    $is_pinned         = isset($_POST['is_pinned']) ? 1 : 0;
    $status            = "Belum";

    $dosen_id = null;
    $kantong_id = null;
    if (isset($_POST['jenis_tugas']) && $_POST['jenis_tugas'] === 'dosen' && !empty($_POST['dosen_id'])) {
        $dosen_id = (int)$_POST['dosen_id'];
        if (!empty($_POST['kantong_id'])) {
            $kantong_id = (int)$_POST['kantong_id'];
        }
    }

    $file_tugas = null;
    if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file_tugas']['name'], PATHINFO_EXTENSION));
        $size = $_FILES['file_tugas']['size'];
        if ($ext === 'pdf' && $size <= 8 * 1024 * 1024) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            $nama_baru = 'tugas_' . $user_id . '_' . time() . '.pdf';
            if (move_uploaded_file($_FILES['file_tugas']['tmp_name'], 'uploads/' . $nama_baru)) {
                $file_tugas = $nama_baru;
            }
        }
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO tugas 
        (user_id, dosen_id, kantong_id, judul, matkul, deadline, deskripsi, tingkat_kesulitan, url_link, file_tugas, is_pinned, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt,
        "iiisssssssis",
        $user_id,
        $dosen_id,
        $kantong_id,
        $judul,
        $matkul,
        $deadline,
        $deskripsi,
        $tingkat_kesulitan,
        $url_link,
        $file_tugas,
        $is_pinned,
        $status
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
header("Location: dashboard.php");
exit;
?>