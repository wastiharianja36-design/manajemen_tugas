<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    exit("Akses ditolak");
}

// 1. Memuat daftar item sub-tugas (AJAX Request)
if (isset($_GET['get_list'])) {
    $tugas_id = (int)$_GET['get_list'];
    $query = mysqli_query($conn, "SELECT * FROM sub_tugas WHERE tugas_id = '$tugas_id' ORDER BY id ASC");
    
    if (mysqli_num_rows($query) > 0) {
        while ($sub = mysqli_fetch_assoc($query)) {
            $checked = ($sub['is_completed'] == 1) ? 'checked' : '';
            $coret = ($sub['is_completed'] == 1) ? 'text-decoration: line-through; color: #888;' : '';
            echo "
            <div class='sub-item'>
                <label style='cursor:pointer; $coret flex:1; display:flex; align-items:center; gap:8px;'>
                    <input type='checkbox' $checked onchange='toggleSub({$sub['id']}, {$tugas_id})'>
                    <span>".htmlspecialchars($sub['judul_sub'])."</span>
                </label>
                <button type='button' onclick='hapusSub({$sub['id']}, {$tugas_id})' style='background:none; border:none; color:#dc2626; cursor:pointer; font-size:13px;'>
                    <i class='fa-solid fa-trash'></i>
                </button>
            </div>";
        }
    } else {
        echo "<p style='color:#888; text-align:center; font-size:13px; margin: 15px 0;'>Belum ada rincian sub-tugas.</p>";
    }
    exit;
}

// 2. Tambah Sub-Tugas (Mendukung Input Banyak Baris Sekaligus via Enter)
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $tugas_id = (int)$_POST['tugas_id'];
    $teks = trim($_POST['judul_sub']);
    
    if (!empty($teks)) {
        // Memisah kalimat berdasarkan baris (Enter) jika pengguna mengetik 2-3 item sekaligus
        $lines = explode("\n", str_replace("\r", "", $teks));
        foreach ($lines as $line) {
            $item = trim($line);
            if (!empty($item)) {
                $stmt = mysqli_prepare($conn, "INSERT INTO sub_tugas (tugas_id, judul_sub, is_completed) VALUES (?, ?, 0)");
                mysqli_stmt_bind_param($stmt, "is", $tugas_id, $item);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    echo "success";
    exit;
}

// 3. Toggle Status Complete/Uncomplete (AJAX)
if (isset($_GET['aksi']) && $_GET['aksi'] === 'toggle') {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE sub_tugas SET is_completed = NOT is_completed WHERE id = '$id'");
    echo "success";
    exit;
}

// 4. Hapus Sub-Tugas (AJAX)
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM sub_tugas WHERE id = '$id'");
    echo "success";
    exit;
}
?>