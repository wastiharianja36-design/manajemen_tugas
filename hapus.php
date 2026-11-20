<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $user_id = (int)$_SESSION['user_id'];

    $cek = mysqli_prepare($conn, "SELECT id, file_tugas FROM tugas WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($cek, "ii", $id, $user_id);
    mysqli_stmt_execute($cek);
    $res = mysqli_stmt_get_result($cek);

    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['file_tugas']) && file_exists('uploads/' . $row['file_tugas'])) {
            unlink('uploads/' . $row['file_tugas']);
        }

        $del_sub = mysqli_prepare($conn, "DELETE FROM sub_tugas WHERE tugas_id = ?");
        mysqli_stmt_bind_param($del_sub, "i", $id);
        mysqli_stmt_execute($del_sub);
        mysqli_stmt_close($del_sub);

        $del_tugas = mysqli_prepare($conn, "DELETE FROM tugas WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($del_tugas, "ii", $id, $user_id);
        mysqli_stmt_execute($del_tugas);
        mysqli_stmt_close($del_tugas);
    }
    mysqli_stmt_close($cek);
}
header("Location: dashboard.php");
exit;
?>