<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id                = (int)$_POST['id'];
    $user_id           = (int)$_SESSION['user_id'];
    $judul             = trim($_POST['judul']);
    $matkul            = trim($_POST['matkul']);
    $deadline          = $_POST['deadline'];
    $tingkat_kesulitan = $_POST['tingkat_kesulitan'];
    $status            = $_POST['status'];
    $deskripsi         = trim($_POST['deskripsi']);
    $url_link          = trim($_POST['url_link']);
    $is_pinned         = isset($_POST['is_pinned']) ? 1 : 0;

    $cek_lama = mysqli_prepare($conn, "SELECT file_tugas FROM tugas WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($cek_lama, "ii", $id, $user_id);
    mysqli_stmt_execute($cek_lama);
    $res_lama = mysqli_stmt_get_result($cek_lama);
    $data_lama = mysqli_fetch_assoc($res_lama);
    mysqli_stmt_close($cek_lama);

    $file_tugas = $data_lama['file_tugas'] ?? null;

    if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file_tugas']['name'], PATHINFO_EXTENSION));
        $size = $_FILES['file_tugas']['size'];
        if ($ext === 'pdf' && $size <= 8 * 1024 * 1024) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            $nama_baru = 'tugas_' . $user_id . '_' . time() . '.pdf';
            if (move_uploaded_file($_FILES['file_tugas']['tmp_name'], 'uploads/' . $nama_baru)) {
                if (!empty($file_tugas) && file_exists('uploads/' . $file_tugas)) {
                    unlink('uploads/' . $file_tugas);
                }
                $file_tugas = $nama_baru;
            }
        }
    }

    $jenis_tugas = isset($_POST['jenis_tugas']) ? $_POST['jenis_tugas'] : 'pribadi';
    $dosen_id = null;
    $kantong_id = null;
    if ($jenis_tugas === 'dosen' && !empty($_POST['dosen_id'])) {
        $dosen_id = (int)$_POST['dosen_id'];
        if (!empty($_POST['kantong_id'])) {
            $kantong_id = (int)$_POST['kantong_id'];
        }
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE tugas SET 
        dosen_id = ?,
        kantong_id = ?,
        judul = ?, 
        matkul = ?, 
        deadline = ?, 
        tingkat_kesulitan = ?, 
        status = ?, 
        deskripsi = ?, 
        url_link = ?, 
        file_tugas = ?,
        is_pinned = ?,
        status_review = CASE WHEN status_review = 'revisi' THEN 'sudah_direvisi' ELSE status_review END
        WHERE id = ? AND user_id = ?"
    );
    mysqli_stmt_bind_param(
        $stmt,
        "iissssssssiii",
        $dosen_id,
        $kantong_id,
        $judul,
        $matkul,
        $deadline,
        $tingkat_kesulitan,
        $status,
        $deskripsi,
        $url_link,
        $file_tugas,
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