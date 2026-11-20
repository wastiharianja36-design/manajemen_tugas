<?php
session_start();
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'dosen') {
    header("Location: index.php");
    exit;
}
include 'koneksi.php';

$dosen_id = (int)$_SESSION['user_id'];
$nama_dosen = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];
$matkul_ampu = $_SESSION['matkul_ampu'] ?? '-';
$pesan_sukses = "";
$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    if ($_POST['aksi'] === 'tambah_kantong') {
        $jenis_tugas = trim($_POST['jenis_tugas']);
        $judul_kantong = trim($_POST['judul_kantong']);
        $deskripsi = trim($_POST['deskripsi']);
        $deadline = $_POST['deadline'];

        $kriteria_arr = $_POST['kriteria'] ?? [];
        $bobot_arr = $_POST['bobot'] ?? [];
        $rubrik = [];
        $total_bobot = 0;

        for ($i = 0; $i < count($kriteria_arr); $i++) {
            $k = trim($kriteria_arr[$i]);
            $b = (float)($bobot_arr[$i] ?? 0);
            if ($k !== '' && $b > 0) {
                $rubrik[] = ['kriteria' => $k, 'bobot' => $b];
                $total_bobot += $b;
            }
        }

        if (count($rubrik) > 0 && $total_bobot != 100) {
            $pesan_error = "Gagal membuat kantong: jika bobot diisi, totalnya harus tepat 100% (saat ini " . $total_bobot . "%).";
        } else {
            $rubrik_json = count($rubrik) > 0 ? json_encode($rubrik, JSON_UNESCAPED_UNICODE) : null;
            $stmt = mysqli_prepare($conn, "INSERT INTO kantong_tugas (dosen_id, jenis_tugas, judul_kantong, deskripsi, deadline, rubrik_nilai) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isssss", $dosen_id, $jenis_tugas, $judul_kantong, $deskripsi, $deadline, $rubrik_json);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $pesan_sukses = "Kantong tugas baru berhasil dibuat!";
        }
    }

    if ($_POST['aksi'] === 'simpan_rubrik') {
        $kantong_id = (int)$_POST['kantong_id'];
        $kriteria_arr = $_POST['kriteria'] ?? [];
        $bobot_arr = $_POST['bobot'] ?? [];
        $rubrik = [];
        $total_bobot = 0;

        for ($i = 0; $i < count($kriteria_arr); $i++) {
            $k = trim($kriteria_arr[$i]);
            $b = (float)($bobot_arr[$i] ?? 0);
            if ($k !== '' && $b > 0) {
                $rubrik[] = ['kriteria' => $k, 'bobot' => $b];
                $total_bobot += $b;
            }
        }

        if ($total_bobot != 100) {
            $pesan_error = "Total bobot penilaian harus tepat 100% (saat ini " . $total_bobot . "%).";
        } else {
            $rubrik_json = json_encode($rubrik, JSON_UNESCAPED_UNICODE);
            $stmt = mysqli_prepare($conn, "UPDATE kantong_tugas SET rubrik_nilai = ? WHERE id = ? AND dosen_id = ?");
            mysqli_stmt_bind_param($stmt, "sii", $rubrik_json, $kantong_id, $dosen_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $pesan_sukses = "Bobot penilaian kantong tugas berhasil disimpan!";
        }
    }

    if ($_POST['aksi'] === 'hapus_kantong') {
        $kantong_id = (int)$_POST['kantong_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM kantong_tugas WHERE id = ? AND dosen_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $kantong_id, $dosen_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $pesan_sukses = "Kantong tugas berhasil dihapus.";
    }

    if ($_POST['aksi'] === 'simpan_review') {
        $tugas_id = (int)$_POST['tugas_id'];
        $catatan_dosen = trim($_POST['catatan_dosen']);
        $acc_100 = isset($_POST['acc_100']);

        if ($acc_100) {
            $status_review = 'acc';
        } elseif (!empty($catatan_dosen)) {
            $status_review = 'revisi';
        } else {
            $status_review = 'belum_direview';
        }

        $stmt = mysqli_prepare($conn, "UPDATE tugas SET status_review = ?, catatan_dosen = ? WHERE id = ? AND dosen_id = ?");
        mysqli_stmt_bind_param($stmt, "ssii", $status_review, $catatan_dosen, $tugas_id, $dosen_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $pesan_sukses = "Hasil review mahasiswa berhasil disimpan!";
    }

    if ($_POST['aksi'] === 'simpan_nilai') {
        $tugas_id = (int)$_POST['tugas_id'];
        $kriteria_arr = $_POST['kriteria'] ?? [];
        $bobot_arr = $_POST['bobot'] ?? [];
        $nilai_arr = $_POST['nilai'] ?? [];

        $rincian = [];
        $total_nilai = 0;
        $total_bobot = 0;

        for ($i = 0; $i < count($kriteria_arr); $i++) {
            $k = trim($kriteria_arr[$i]);
            $b = (float)($bobot_arr[$i] ?? 0);
            $n = (float)($nilai_arr[$i] ?? 0);
            if ($k !== '' && $b > 0) {
                $rincian[] = [
                    'kriteria' => $k,
                    'bobot' => $b,
                    'nilai' => $n
                ];
                $total_bobot += $b;
                $total_nilai += ($n * ($b / 100));
            }
        }

        if ($total_bobot != 100) {
            $pesan_error = "Total bobot penilaian harus 100% sebelum menyimpan nilai.";
        } else {
            $detail_json = json_encode($rincian, JSON_UNESCAPED_UNICODE);
            $nilai_akhir = round($total_nilai, 2);

            $stmt = mysqli_prepare($conn, "UPDATE tugas SET detail_nilai = ?, nilai_akhir = ? WHERE id = ? AND dosen_id = ?");
            mysqli_stmt_bind_param($stmt, "sdii", $detail_json, $nilai_akhir, $tugas_id, $dosen_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $pesan_sukses = "Nilai akhir otomatis (" . number_format($nilai_akhir, 2) . ") berhasil disimpan!";
        }
    }
}

$q_kantong = mysqli_query($conn, "SELECT k.*, (SELECT COUNT(*) FROM tugas t WHERE t.kantong_id = k.id) AS jml_kumpul FROM kantong_tugas k WHERE k.dosen_id = '$dosen_id' ORDER BY k.deadline ASC, k.id DESC");
$daftar_kantong = [];
while ($k = mysqli_fetch_assoc($q_kantong)) {
    $daftar_kantong[] = $k;
}

$cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";
$filter_kantong = isset($_GET['filter_kantong']) ? (int)$_GET['filter_kantong'] : 0;
$filter_review = isset($_GET['filter_review']) ? $_GET['filter_review'] : "";

$where = "WHERE t.dosen_id = '$dosen_id'";
if ($filter_kantong > 0) {
    $where .= " AND t.kantong_id = '$filter_kantong'";
}
if (!empty($cari)) {
    $where .= " AND (t.judul LIKE '%$cari%' OR u.nama_lengkap LIKE '%$cari%' OR u.username LIKE '%$cari%')";
}
if ($filter_review === 'perlu_cek') {
    $where .= " AND t.status_review IN ('belum_direview', 'sudah_direvisi')";
} elseif ($filter_review === 'revisi') {
    $where .= " AND t.status_review = 'revisi'";
} elseif ($filter_review === 'acc') {
    $where .= " AND t.status_review = 'acc'";
} elseif ($filter_review === 'dinilai') {
    $where .= " AND t.nilai_akhir IS NOT NULL";
}

$query_tugas = "SELECT t.*, u.username AS mhs_username, u.nama_lengkap AS mhs_nama,
                k.jenis_tugas AS k_jenis, k.judul_kantong AS k_judul, k.rubrik_nilai AS k_rubrik
                FROM tugas t 
                JOIN users u ON t.user_id = u.id 
                LEFT JOIN kantong_tugas k ON t.kantong_id = k.id
                $where 
                ORDER BY t.deadline ASC, t.id DESC";
$data = mysqli_query($conn, $query_tugas);

$stat_total = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tugas WHERE dosen_id = '$dosen_id'"));
$stat_cek = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tugas WHERE dosen_id = '$dosen_id' AND status_review IN ('belum_direview', 'sudah_direvisi')"));
$stat_acc = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tugas WHERE dosen_id = '$dosen_id' AND status_review = 'acc'"));
$stat_nilai = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tugas WHERE dosen_id = '$dosen_id' AND nilai_akhir IS NOT NULL"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - Nugasia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-body: #f1f5f9;
            --primary-dark: #0f382c;
            --primary-hover: #08281f;
            --accent-mint: #2dd4bf;
            --accent-blue: #e0f2fe;
            --accent-blue-text: #0369a1;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-body);
            margin: 0;
            display: flex;
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }
        .sidebar {
            width: 230px;
            height: 100vh;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            color: #1f2937;
            padding: 20px;
            position: fixed;
            transition: all 0.3s ease-in-out;
            box-sizing: border-box;
            z-index: 100;
            border-right: 1px solid #e5e7eb;
            transform: translateX(-100%);
        }
        .sidebar.open { transform: translateX(0); }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 18px;
            color: var(--primary-dark);
            font-weight: 700;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4b5563;
            text-decoration: none;
            padding: 12px 14px;
            margin-top: 8px;
            border-radius: 12px;
            transition: all 0.2s;
            font-size: 14px;
            font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #e6fffa;
            color: var(--primary-dark);
            transform: translateX(4px);
        }
        .main {
            margin-left: 0;
            width: 100%;
            padding: 25px;
            transition: all 0.3s ease-in-out;
            box-sizing: border-box;
        }
        .main.shifted {
            margin-left: 230px;
            width: calc(100% - 230px);
        }
        .btn-toggle {
            background: var(--primary-dark);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 18px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(15, 56, 44, 0.2);
        }
        .box {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            margin-bottom: 22px;
        }
        .header-dosen {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .header-dosen h2 {
            margin: 0;
            color: var(--primary-dark);
            font-size: 21px;
        }
        .badge-matkul-dosen {
            background: #ecfeff;
            color: #0e7490;
            border: 1px solid #a5f3fc;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }
        .alert-sukses {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 500;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 500;
        }
        .card-container {
            display: flex;
            gap: 15px;
            margin-bottom: 5px;
            flex-wrap: wrap;
        }
        .card {
            flex: 1;
            min-width: 140px;
            padding: 16px;
            border-radius: 14px;
            text-align: center;
            font-size: 13.5px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: white;
        }
        .card span { display: block; font-size: 25px; font-weight: 700; margin-top: 4px; }
        .card.c1 { background: #e6f4ea; color: var(--primary-dark); border-color: #b7e4c7; }
        .card.c2 { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
        .card.c3 { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
        .card.c4 { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .section-head h3 {
            margin: 0;
            color: var(--primary-dark);
            font-size: 17px;
        }
        .kantong-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 14px;
        }
        .kantong-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .kantong-jenis {
            display: inline-block;
            background: var(--primary-dark);
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 6px;
            margin-bottom: 6px;
        }
        .kantong-meta {
            font-size: 12px;
            color: #475569;
            margin-top: 6px;
            line-height: 1.5;
        }
        .kantong-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        .search-box {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .search-box select, .search-box input {
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
        }
        .btn {
            padding: 8px 14px;
            background: var(--primary-dark);
            color: white;
            text-decoration: none;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn:hover { background: var(--primary-hover); }
        .btn-blue { background: #0284c7; }
        .btn-blue:hover { background: #0369a1; }
        .btn-emerald { background: #059669; }
        .btn-emerald:hover { background: #047857; }
        .btn-soft { background: #e2e8f0; color: #334155; }
        .btn-soft:hover { background: #cbd5e1; }
        .btn-danger-soft { background: #fee2e2; color: #b91c1c; }
        .btn-danger-soft:hover { background: #fecaca; }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 840px;
        }
        th {
            background: var(--primary-dark);
            color: white;
            padding: 13px;
            font-size: 13.5px;
            font-weight: 600;
        }
        td {
            padding: 13px;
            text-align: center;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            vertical-align: top;
        }
        .badge-wait { background: #f1f5f9; color: #475569; padding: 4px 9px; border-radius: 6px; font-weight: 600; font-size: 11.5px; }
        .badge-rev { background: #ffedd5; color: #c2410c; padding: 4px 9px; border-radius: 6px; font-weight: 600; font-size: 11.5px; }
        .badge-done-rev { background: #dbeafe; color: #1d4ed8; padding: 4px 9px; border-radius: 6px; font-weight: 600; font-size: 11.5px; }
        .badge-acc { background: #dcfce7; color: #15803d; padding: 4px 9px; border-radius: 6px; font-weight: 700; font-size: 11.5px; }
        .badge-nilai {
            display: inline-block;
            margin-top: 6px;
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #14532d;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
        }
        .tag-pdf {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        .tag-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 15px;
            box-sizing: border-box;
        }
        .modal-content {
            background: white;
            padding: 24px;
            border-radius: 16px;
            width: 100%;
            max-width: 540px;
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        }
        .modal-wide {
            max-width: 1060px;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .close-btn { cursor: pointer; font-size: 24px; color: #64748b; line-height: 1; }
        .split-review {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 20px;
        }
        .preview-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
        }
        .action-panel {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .panel-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            background: #ffffff;
        }
        .acc-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 12px;
            cursor: pointer;
        }
        .acc-box input { width: 18px; height: 18px; cursor: pointer; }
        .form-inp {
            width: 100%;
            padding: 9px 11px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            box-sizing: border-box;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .kriteria-row-2 {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 8px;
            margin-bottom: 8px;
            align-items: center;
        }
        .kriteria-row-3 {
            display: grid;
            grid-template-columns: 2fr 0.9fr 1fr auto;
            gap: 8px;
            margin-bottom: 8px;
            align-items: center;
        }
        .kriteria-row-2 input, .kriteria-row-3 input {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: inherit;
            font-size: 12.5px;
            width: 100%;
            box-sizing: border-box;
        }
        .btn-del-row {
            background: #fee2e2;
            color: #b91c1c;
            border: none;
            border-radius: 8px;
            padding: 8px 10px;
            cursor: pointer;
        }
        .hasil-otomatis-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 10px;
            padding: 12px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .credit {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
            text-align: center;
            color: #6b7280;
        }
        .credit p { font-size: 11px; margin: 0; }
        .credit h4 { margin-top: 3px; font-size: 12px; color: var(--primary-dark); }
        @media (max-width: 860px) {
            .split-review { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 92% !important;
                max-width: 360px !important;
                height: 64px !important;
                position: fixed !important;
                bottom: 16px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                border-radius: 35px !important;
                background: rgba(18, 40, 33, 0.94) !important;
                display: flex !important;
                justify-content: space-around !important;
                align-items: center !important;
                padding: 0 12px !important;
            }
            .sidebar h2, .sidebar .credit { display: none !important; }
            .sidebar a { color: white !important; margin: 0 !important; padding: 8px 14px !important; font-size: 12px !important; }
            .btn-toggle { display: none !important; }
            .main { padding: 15px 15px 95px 15px !important; }
        }
    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <h2>Panel Dosen</h2>
    <a href="dashboard_dosen.php" class="active"><i class="fa-solid fa-chalkboard-user"></i> <span>Monitoring Tugas</span></a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    <div class="credit">
        <p>Developed by</p>
        <h4>Wasti Harianja & Cetrin Yolanda</h4>
    </div>
</div>

<div class="main" id="mainContent">
    <button id="toggleBtn" class="btn-toggle"><i class="fa-solid fa-bars"></i> Menu</button>

    <div class="box">
        <div class="header-dosen">
            <div>
                <h2>Dashboard Peninjauan Dosen</h2>
                <div style="font-size:13px; color:#64748b; margin-top:3px;">Selamat datang, <strong><?= htmlspecialchars($nama_dosen); ?></strong></div>
            </div>
            <div class="badge-matkul-dosen">
                <i class="fa-solid fa-book-open"></i> Mata Kuliah: <?= htmlspecialchars($matkul_ampu); ?>
            </div>
        </div>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert-sukses"><i class="fa-solid fa-circle-check"></i> <?= $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= $pesan_error; ?></div>
        <?php endif; ?>

        <div class="card-container">
            <div class="card c1">Total Tugas Masuk <span><?= $stat_total; ?></span></div>
            <div class="card c2">Perlu Dicek <span><?= $stat_cek; ?></span></div>
            <div class="card c3">Sudah 100% Pas <span><?= $stat_acc; ?></span></div>
            <div class="card c4">Sudah Dinilai <span><?= $stat_nilai; ?></span></div>
        </div>
    </div>

    <div class="box">
        <div class="section-head">
            <h3><i class="fa-solid fa-folder-open"></i> Kantong Tugas Mata Kuliah</h3>
            <button type="button" class="btn" onclick="bukaModalBuatKantong()">
                <i class="fa-solid fa-folder-plus"></i> Buat Kantong Tugas
            </button>
        </div>

        <?php if (count($daftar_kantong) > 0): ?>
            <div class="kantong-grid">
                <?php foreach ($daftar_kantong as $kt): 
                    $rubrik_arr = !empty($kt['rubrik_nilai']) ? json_decode($kt['rubrik_nilai'], true) : [];
                    $ada_rubrik = (is_array($rubrik_arr) && count($rubrik_arr) > 0);
                ?>
                <div class="kantong-card">
                    <div>
                        <span class="kantong-jenis"><?= htmlspecialchars($kt['jenis_tugas']); ?></span>
                        <div style="font-weight:700; color:var(--primary-dark); font-size:14.5px;"><?= htmlspecialchars($kt['judul_kantong']); ?></div>
                        <?php if (!empty($kt['deskripsi'])): ?>
                            <div style="font-size:12px; color:#64748b; margin-top:3px;"><?= htmlspecialchars($kt['deskripsi']); ?></div>
                        <?php endif; ?>
                        <div class="kantong-meta">
                            <div><i class="fa-regular fa-calendar"></i> Deadline: <strong><?= htmlspecialchars($kt['deadline']); ?></strong></div>
                            <div><i class="fa-solid fa-users"></i> Terkumpul: <strong><?= $kt['jml_kumpul']; ?> tugas</strong></div>
                            <div>
                                <i class="fa-solid fa-scale-balanced"></i> Bobot Nilai: 
                                <?php if ($ada_rubrik): ?>
                                    <span style="color:#059669; font-weight:600;">Sudah diatur (<?= count($rubrik_arr); ?> kriteria)</span>
                                <?php else: ?>
                                    <span style="color:#d97706; font-weight:600;">Belum diatur</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="kantong-actions">
                        <a href="dashboard_dosen.php?filter_kantong=<?= $kt['id']; ?>" class="btn btn-blue">
                            <i class="fa-solid fa-filter"></i> Lihat (<?= $kt['jml_kumpul']; ?>)
                        </a>
                        <button type="button" class="btn btn-emerald" onclick='bukaModalAturBobot(<?= $kt["id"]; ?>, <?= json_encode($kt["judul_kantong"]); ?>, <?= json_encode($kt["rubrik_nilai"] ?? "[]"); ?>)'>
                            <i class="fa-solid fa-sliders"></i> Atur Bobot
                        </button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus kantong tugas ini?')">
                            <input type="hidden" name="aksi" value="hapus_kantong">
                            <input type="hidden" name="kantong_id" value="<?= $kt['id']; ?>">
                            <button type="submit" class="btn btn-danger-soft" title="Hapus Kantong"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="font-size:13px; color:#64748b; margin:0;">Belum ada kantong tugas. Klik <strong>Buat Kantong Tugas</strong> untuk menambahkan wadah pengumpulan tugas (misal: Tugas Rutin, CBR, Mini Research, Project).</p>
        <?php endif; ?>
    </div>

    <div class="box">
        <div class="section-head">
            <h3><i class="fa-solid fa-list-check"></i> Daftar Pengumpulan Tugas Mahasiswa</h3>
        </div>

        <form method="GET" class="search-box">
            <select name="filter_kantong" onchange="this.form.submit()">
                <option value="0">-- Semua Kantong Tugas --</option>
                <?php foreach ($daftar_kantong as $kt): ?>
                    <option value="<?= $kt['id']; ?>" <?= ($filter_kantong === (int)$kt['id']) ? 'selected' : ''; ?>>
                        [<?= htmlspecialchars($kt['jenis_tugas']); ?>] <?= htmlspecialchars($kt['judul_kantong']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="filter_review" onchange="this.form.submit()">
                <option value="">-- Semua Status Review --</option>
                <option value="perlu_cek" <?= ($filter_review === 'perlu_cek') ? 'selected' : ''; ?>>Perlu Dicek / Baru Direvisi</option>
                <option value="revisi" <?= ($filter_review === 'revisi') ? 'selected' : ''; ?>>Sedang Direvisi Mahasiswa</option>
                <option value="acc" <?= ($filter_review === 'acc') ? 'selected' : ''; ?>>Sudah 100% Pas (ACC)</option>
                <option value="dinilai" <?= ($filter_review === 'dinilai') ? 'selected' : ''; ?>>Sudah Diberi Nilai</option>
            </select>
            <input type="text" name="cari" placeholder="Cari nama mahasiswa..." value="<?= htmlspecialchars($cari); ?>">
            <button type="submit" class="btn"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            <?php if (!empty($cari) || !empty($filter_review) || $filter_kantong > 0): ?>
                <a href="dashboard_dosen.php" class="btn btn-soft"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            <?php endif; ?>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Kantong & Judul Tugas</th>
                        <th>Deadline</th>
                        <th>Lampiran</th>
                        <th>Status Review & Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $tgl_hari_ini = date('Y-m-d');
                    if (mysqli_num_rows($data) > 0):
                        while ($row = mysqli_fetch_assoc($data)):
                            $tid = $row['id'];
                            $nama_mhs = !empty($row['mhs_nama']) ? $row['mhs_nama'] : $row['mhs_username'];
                            $sub_tot = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM sub_tugas WHERE tugas_id='$tid'"))['c'];
                            $sub_ok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM sub_tugas WHERE tugas_id='$tid' AND is_completed=1"))['c'];

                            $st_rev = $row['status_review'] ?? 'belum_direview';
                            $lewat_deadline = ($row['deadline'] < $tgl_hari_ini);
                            $siap_dinilai = ($st_rev === 'acc' || $lewat_deadline);

                            $rubrik_default = !empty($row['k_rubrik']) ? $row['k_rubrik'] : "[]";
                            $nilai_existing = !empty($row['detail_nilai']) ? $row['detail_nilai'] : "[]";
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td style="text-align:left;">
                            <strong style="color:var(--primary-dark);"><?= htmlspecialchars($nama_mhs); ?></strong>
                            <div style="font-size:11.5px; color:#64748b;">@<?= htmlspecialchars($row['mhs_username']); ?></div>
                        </td>
                        <td style="text-align:left; max-width:270px;">
                            <?php if (!empty($row['k_jenis'])): ?>
                                <span style="display:inline-block; background:#e0f2fe; color:#0369a1; font-size:10.5px; font-weight:700; padding:2px 7px; border-radius:5px; margin-bottom:3px;">
                                    <?= htmlspecialchars($row['k_jenis']); ?>
                                </span>
                            <?php endif; ?>
                            <div style="font-weight:700; color:var(--primary-dark); font-size:13.5px;"><?= htmlspecialchars($row['judul']); ?></div>
                            <?php if (!empty($row['deskripsi'])): ?>
                                <div style="font-size:12px; color:#555; margin-top:3px;"><?= htmlspecialchars($row['deskripsi']); ?></div>
                            <?php endif; ?>
                            <?php if ($sub_tot > 0): ?>
                                <div style="font-size:11px; color:#0369a1; margin-top:4px;">
                                    <i class="fa-solid fa-list-check"></i> Checklist Mahasiswa: <?= $sub_ok; ?>/<?= $sub_tot; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($row['catatan_dosen'])): ?>
                                <div style="margin-top:6px; background:#f8fafc; border-left:3px solid #0284c7; padding:5px 8px; font-size:11.5px; color:#334155;">
                                    <strong>Komentar Anda:</strong> <?= htmlspecialchars($row['catatan_dosen']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($row['deadline']); ?></div>
                            <div style="font-size:11px; margin-top:3px; color:<?= $lewat_deadline ? '#dc2626' : '#059669'; ?>; font-weight:600;">
                                <?= $lewat_deadline ? 'Deadline Lewat' : 'Masih Berjalan'; ?>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:5px; align-items:center;">
                                <?php if (!empty($row['file_tugas'])): ?>
                                    <span class="tag-pdf"><i class="fa-solid fa-file-pdf"></i> File PDF</span>
                                <?php endif; ?>
                                <?php if (!empty($row['url_link'])): ?>
                                    <a href="<?= htmlspecialchars($row['url_link']); ?>" target="_blank" class="tag-link">
                                        <i class="fa-solid fa-link"></i> Link
                                    </a>
                                <?php endif; ?>
                                <?php if (empty($row['file_tugas']) && empty($row['url_link'])): ?>
                                    <span style="color:#94a3b8; font-size:12px;">Belum ada</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($st_rev === 'acc'): ?>
                                <span class="badge-acc"><i class="fa-solid fa-check"></i> 100% Pas (ACC)</span>
                            <?php elseif ($st_rev === 'revisi'): ?>
                                <span class="badge-rev"><i class="fa-solid fa-pen"></i> Perlu Revisi</span>
                            <?php elseif ($st_rev === 'sudah_direvisi'): ?>
                                <span class="badge-done-rev"><i class="fa-solid fa-rotate"></i> Sudah Direvisi</span>
                            <?php else: ?>
                                <span class="badge-wait">Belum Direview</span>
                            <?php endif; ?>

                            <?php if ($row['nilai_akhir'] !== null): ?>
                                <div>
                                    <span class="badge-nilai">Nilai: <?= number_format((float)$row['nilai_akhir'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <button type="button" class="btn btn-blue"
                                onclick='bukaModalReviewNilai(<?= json_encode([
                                    "id" => $tid,
                                    "judul" => $row["judul"],
                                    "mhs" => $nama_mhs,
                                    "deskripsi" => $row["deskripsi"] ?? "",
                                    "file_tugas" => $row["file_tugas"] ?? "",
                                    "url_link" => $row["url_link"] ?? "",
                                    "status_review" => $st_rev,
                                    "catatan_dosen" => $row["catatan_dosen"] ?? "",
                                    "lewat_deadline" => $lewat_deadline,
                                    "siap_dinilai" => $siap_dinilai,
                                    "rubrik_default" => $rubrik_default,
                                    "nilai_existing" => $nilai_existing
                                ]); ?>)'>
                                <i class="fa-solid fa-eye"></i> Review & Nilai
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" style="padding:25px; color:#64748b;">Belum ada pengumpulan tugas pada filter ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="modalBuatKantong">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin:0; color:var(--primary-dark); font-size:16px;"><i class="fa-solid fa-folder-plus"></i> Buat Kantong Tugas Baru</h3>
            <span class="close-btn" onclick="tutupModal('modalBuatKantong')">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="aksi" value="tambah_kantong">

            <label style="font-size:13px; font-weight:600;">Jenis Tugas (Ketik Bebas atau Pilih)</label>
            <input type="text" name="jenis_tugas" list="listJenisTugas" placeholder="Contoh: Tugas Rutin / Mini Research / Project..." required class="form-inp">
            <datalist id="listJenisTugas">
                <option value="Tugas Rutin">
                <option value="Critical Book Report (CBR)">
                <option value="Critical Journal Review (CJR)">
                <option value="Mini Research">
                <option value="Rekayasa Ide">
                <option value="Project">
                <option value="Laporan Praktikum">
            </datalist>

            <label style="font-size:13px; font-weight:600;">Judul Kantong Tugas</label>
            <input type="text" name="judul_kantong" placeholder="Contoh: Laporan Analisis Sistem Informasi" required class="form-inp">

            <label style="font-size:13px; font-weight:600;">Deadline Pengumpulan</label>
            <input type="date" name="deadline" required class="form-inp">

            <label style="font-size:13px; font-weight:600;">Deskripsi / Ketentuan Singkat (Opsional)</label>
            <textarea name="deskripsi" placeholder="Petunjuk format file atau ketentuan tugas..." class="form-inp" style="height:65px;"></textarea>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px; margin-top:6px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong style="font-size:12.5px; color:var(--primary-dark);">Bobot Penilaian (Opsional, bisa diatur nanti)</strong>
                    <span id="infoBobotBuat" style="font-size:12px; font-weight:700; color:#059669;">Total: 100%</span>
                </div>
                <div id="containerBobotBuat">
                    <div class="kriteria-row-2">
                        <input type="text" name="kriteria[]" placeholder="Kriteria (misal: Isi)" value="Isi / Substansi">
                        <input type="number" name="bobot[]" class="bobot-buat" placeholder="%" min="0" max="100" value="60" oninput="hitungBobotModal('bobot-buat', 'infoBobotBuat')">
                        <button type="button" class="btn-del-row" onclick="this.parentElement.remove(); hitungBobotModal('bobot-buat', 'infoBobotBuat');"><i class="fa-solid fa-trash"></i></button>
                    </div>
                    <div class="kriteria-row-2">
                        <input type="text" name="kriteria[]" placeholder="Kriteria (misal: Kerapian)" value="Kerapian & Format">
                        <input type="number" name="bobot[]" class="bobot-buat" placeholder="%" min="0" max="100" value="40" oninput="hitungBobotModal('bobot-buat', 'infoBobotBuat')">
                        <button type="button" class="btn-del-row" onclick="this.parentElement.remove(); hitungBobotModal('bobot-buat', 'infoBobotBuat');"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
                <button type="button" class="btn btn-soft" style="margin-top:4px; font-size:11.5px;" onclick="tambahBarisBobot('containerBobotBuat', 'bobot-buat', 'infoBobotBuat')">
                    <i class="fa-solid fa-plus"></i> Tambah Kriteria
                </button>
            </div>

            <button type="submit" class="btn" style="width:100%; margin-top:15px; justify-content:center; padding:11px;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Kantong Tugas
            </button>
        </form>
    </div>
</div>

<div class="modal" id="modalAturBobot">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin:0; color:var(--primary-dark); font-size:16px;"><i class="fa-solid fa-sliders"></i> Atur Bobot Penilaian Kantong</h3>
            <span class="close-btn" onclick="tutupModal('modalAturBobot')">&times;</span>
        </div>
        <div style="font-size:13px; color:#475569; margin-bottom:12px;">
            Kantong Tugas: <strong id="judulKantongBobot" style="color:var(--primary-dark);"></strong>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="aksi" value="simpan_rubrik">
            <input type="hidden" name="kantong_id" id="idKantongBobot">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <span style="font-size:12px; color:#64748b;">Total semua bobot harus tepat 100%</span>
                <span id="infoBobotEdit" style="font-size:12.5px; font-weight:700; color:#059669;">Total: 0%</span>
            </div>

            <div id="containerBobotEdit"></div>

            <button type="button" class="btn btn-soft" style="margin-top:4px; font-size:11.5px;" onclick="tambahBarisBobot('containerBobotEdit', 'bobot-edit', 'infoBobotEdit')">
                <i class="fa-solid fa-plus"></i> Tambah Kriteria
            </button>

            <button type="submit" class="btn btn-emerald" style="width:100%; margin-top:15px; justify-content:center; padding:11px;">
                <i class="fa-solid fa-check"></i> Simpan Bobot Penilaian
            </button>
        </form>
    </div>
</div>

<div class="modal" id="modalReviewNilai">
    <div class="modal-content modal-wide">
        <div class="modal-header">
            <div>
                <h3 style="margin:0; color:var(--primary-dark); font-size:16.5px;"><i class="fa-solid fa-file-circle-check"></i> Review Langsung & Penilaian Otomatis</h3>
                <div style="font-size:12.5px; color:#475569; margin-top:2px;">
                    Mahasiswa: <strong id="rnMhs" style="color:var(--primary-dark);"></strong> — Tugas: <strong id="rnJudul" style="color:var(--primary-dark);"></strong>
                </div>
            </div>
            <span class="close-btn" onclick="tutupModal('modalReviewNilai')">&times;</span>
        </div>

        <div class="split-review">
            <div class="preview-panel">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                    <strong style="font-size:13px; color:var(--primary-dark);"><i class="fa-solid fa-eye"></i> Pratinjau Tugas (Tanpa Download)</strong>
                    <a id="rnLinkExternal" href="#" target="_blank" class="btn btn-blue" style="display:none; padding:5px 10px; font-size:11.5px;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Link Lampiran
                    </a>
                </div>

                <div id="rnDeskripsiBox" style="display:none; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; padding:9px 11px; font-size:12px; color:#334155; margin-bottom:10px;"></div>

                <div id="rnPdfContainer"></div>
            </div>

            <div class="action-panel">
                <div class="panel-card">
                    <strong style="font-size:13.5px; color:var(--primary-dark); display:block; margin-bottom:8px;">1. Komentar Review & Status Revisi</strong>

                    <div id="rnPeringatanDeadline" style="display:none; background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; padding:8px 10px; border-radius:8px; font-size:11.5px; margin-bottom:10px;">
                        <i class="fa-solid fa-lock"></i> Deadline sudah lewat, mahasiswa tidak lagi merevisi tugas ini.
                    </div>

                    <form method="POST" action="">
                        <input type="hidden" name="aksi" value="simpan_review">
                        <input type="hidden" name="tugas_id" id="rnRevTugasId">

                        <label class="acc-box" for="rnChkAcc">
                            <input type="checkbox" name="acc_100" id="rnChkAcc" value="1">
                            <div>
                                <strong style="color:#065f46; font-size:12.5px;">Sudah 100% Pas (ACC)</strong>
                                <div style="font-size:11px; color:#047857;">Centang jika tugas sudah sesuai semua.</div>
                            </div>
                        </label>

                        <textarea name="catatan_dosen" id="rnCatatan" placeholder="Tulis komentar bagian yang harus direvisi mahasiswa..." class="form-inp" style="height:80px; margin-bottom:8px;"></textarea>

                        <button type="submit" class="btn btn-blue" style="width:100%; justify-content:center; padding:9px;">
                            <i class="fa-solid fa-paper-plane"></i> Simpan Komentar Review
                        </button>
                    </form>
                </div>

                <div class="panel-card">
                    <strong style="font-size:13.5px; color:var(--primary-dark); display:block; margin-bottom:8px;">2. Penilaian Berbobot Otomatis</strong>

                    <div id="rnInfoBelumSiap" style="display:none; background:#fffbeb; border:1px solid #fcd34d; color:#92400e; padding:8px 10px; border-radius:8px; font-size:11.5px; margin-bottom:10px;">
                        Tugas belum dicentang <strong>100% Pas</strong> & belum lewat deadline.
                        <label style="display:block; margin-top:4px; cursor:pointer; font-weight:600;">
                            <input type="checkbox" id="rnChkManualNilai" onchange="toggleFormNilai(this.checked)"> Tetap buka form penilaian sekarang
                        </label>
                    </div>

                    <form method="POST" action="" id="rnFormNilai">
                        <input type="hidden" name="aksi" value="simpan_nilai">
                        <input type="hidden" name="tugas_id" id="rnNilTugasId">

                        <div style="display:grid; grid-template-columns:2fr 0.9fr 1fr auto; gap:6px; font-size:11px; font-weight:700; color:#475569; margin-bottom:5px;">
                            <div>Kriteria</div>
                            <div>Bobot %</div>
                            <div>Nilai</div>
                            <div></div>
                        </div>

                        <div id="rnContainerKriteria"></div>

                        <button type="button" class="btn btn-soft" style="font-size:11px; padding:5px 10px;" onclick="tambahBarisNilai('', '', '')">
                            <i class="fa-solid fa-plus"></i> Kriteria
                        </button>

                        <div class="hasil-otomatis-box">
                            <div style="font-size:11.5px; color:#166534;">
                                Bobot: <strong id="rnTotalBobot">100%</strong>
                            </div>
                            <div style="text-align:right;">
                                <span style="font-size:11px; color:#166534;">Nilai Akhir: </span>
                                <strong style="font-size:20px; color:#14532d;" id="rnNilaiAkhir">0.00</strong>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-emerald" style="width:100%; margin-top:10px; justify-content:center; padding:10px;">
                            <i class="fa-solid fa-check-double"></i> Simpan Nilai Akhir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        mainContent.classList.toggle('shifted');
    });

    function tutupModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function bukaModalBuatKantong() {
        document.getElementById('modalBuatKantong').style.display = 'flex';
        hitungBobotModal('bobot-buat', 'infoBobotBuat');
    }

    function tambahBarisBobot(containerId, classInput, infoId, nama = '', bobot = '') {
        const container = document.getElementById(containerId);
        const div = document.createElement('div');
        div.className = 'kriteria-row-2';
        div.innerHTML = `
            <input type="text" name="kriteria[]" placeholder="Nama kriteria..." value="${nama}" required>
            <input type="number" name="bobot[]" class="${classInput}" placeholder="%" min="1" max="100" value="${bobot}" oninput="hitungBobotModal('${classInput}', '${infoId}')" required>
            <button type="button" class="btn-del-row" onclick="this.parentElement.remove(); hitungBobotModal('${classInput}', '${infoId}');"><i class="fa-solid fa-trash"></i></button>
        `;
        container.appendChild(div);
        hitungBobotModal(classInput, infoId);
    }

    function hitungBobotModal(classInput, infoId) {
        const inputs = document.querySelectorAll('.' + classInput);
        let total = 0;
        inputs.forEach(inp => total += parseFloat(inp.value) || 0);
        const el = document.getElementById(infoId);
        el.innerText = 'Total: ' + total + '%';
        el.style.color = (total === 100 || total === 0) ? '#059669' : '#dc2626';
    }

    function bukaModalAturBobot(id, judul, rubrikJson) {
        document.getElementById('idKantongBobot').value = id;
        document.getElementById('judulKantongBobot').innerText = judul;
        const container = document.getElementById('containerBobotEdit');
        container.innerHTML = '';

        let data = [];
        try { data = JSON.parse(rubrikJson); } catch (e) { data = []; }

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(item => tambahBarisBobot('containerBobotEdit', 'bobot-edit', 'infoBobotEdit', item.kriteria, item.bobot));
        } else {
            tambahBarisBobot('containerBobotEdit', 'bobot-edit', 'infoBobotEdit', 'Isi / Substansi', 60);
            tambahBarisBobot('containerBobotEdit', 'bobot-edit', 'infoBobotEdit', 'Kerapian & Format', 40);
        }
        document.getElementById('modalAturBobot').style.display = 'flex';
    }

    function bukaModalReviewNilai(info) {
        document.getElementById('rnMhs').innerText = info.mhs;
        document.getElementById('rnJudul').innerText = info.judul;
        document.getElementById('rnRevTugasId').value = info.id;
        document.getElementById('rnNilTugasId').value = info.id;
        document.getElementById('rnCatatan').value = info.catatan_dosen;
        document.getElementById('rnChkAcc').checked = (info.status_review === 'acc');

        const deskBox = document.getElementById('rnDeskripsiBox');
        if (info.deskripsi) {
            deskBox.style.display = 'block';
            deskBox.innerHTML = '<strong>Catatan Mahasiswa:</strong> ' + info.deskripsi;
        } else {
            deskBox.style.display = 'none';
        }

        const linkExt = document.getElementById('rnLinkExternal');
        if (info.url_link) {
            linkExt.style.display = 'inline-flex';
            linkExt.href = info.url_link;
        } else {
            linkExt.style.display = 'none';
        }

        const pdfBox = document.getElementById('rnPdfContainer');
        if (info.file_tugas) {
            pdfBox.innerHTML = `<iframe src="uploads/${encodeURIComponent(info.file_tugas)}#toolbar=0" style="width:100%; height:440px; border:1px solid #cbd5e1; border-radius:10px; background:white;"></iframe>`;
        } else if (info.url_link) {
            pdfBox.innerHTML = `<div style="padding:40px 20px; text-align:center; background:white; border:1px dashed #cbd5e1; border-radius:10px; color:#475569; font-size:13px;">
                <i class="fa-solid fa-link" style="font-size:28px; color:#0284c7; margin-bottom:8px;"></i><br>
                Mahasiswa mengumpulkan tugas melalui <strong>Link Lampiran</strong>.<br>Klik tombol <strong>Buka Link Lampiran</strong> di atas untuk melihat langsung.
            </div>`;
        } else {
            pdfBox.innerHTML = `<div style="padding:40px 20px; text-align:center; background:white; border:1px dashed #cbd5e1; border-radius:10px; color:#94a3b8; font-size:13px;">
                Mahasiswa belum mengunggah file PDF maupun link lampiran.
            </div>`;
        }

        const alertDead = document.getElementById('rnPeringatanDeadline');
        alertDead.style.display = (info.lewat_deadline && info.status_review !== 'acc') ? 'block' : 'none';

        const infoBelum = document.getElementById('rnInfoBelumSiap');
        document.getElementById('rnChkManualNilai').checked = false;
        if (info.siap_dinilai) {
            infoBelum.style.display = 'none';
            toggleFormNilai(true);
        } else {
            infoBelum.style.display = 'block';
            toggleFormNilai(false);
        }

        const containerNilai = document.getElementById('rnContainerKriteria');
        containerNilai.innerHTML = '';

        let existing = [];
        let rubrik = [];
        try { existing = JSON.parse(info.nilai_existing); } catch (e) { existing = []; }
        try { rubrik = JSON.parse(info.rubrik_default); } catch (e) { rubrik = []; }

        if (Array.isArray(existing) && existing.length > 0) {
            existing.forEach(item => tambahBarisNilai(item.kriteria, item.bobot, item.nilai));
        } else if (Array.isArray(rubrik) && rubrik.length > 0) {
            rubrik.forEach(item => tambahBarisNilai(item.kriteria, item.bobot, ''));
        } else {
            tambahBarisNilai('Isi / Substansi', 60, '');
            tambahBarisNilai('Kerapian & Format', 40, '');
        }

        hitungNilaiOtomatis();
        document.getElementById('modalReviewNilai').style.display = 'flex';
    }

    function toggleFormNilai(aktif) {
        const form = document.getElementById('rnFormNilai');
        form.style.opacity = aktif ? '1' : '0.45';
        form.style.pointerEvents = aktif ? 'auto' : 'none';
    }

    function tambahBarisNilai(nama, bobot, nilai) {
        const container = document.getElementById('rnContainerKriteria');
        const row = document.createElement('div');
        row.className = 'kriteria-row-3';
        row.innerHTML = `
            <input type="text" name="kriteria[]" placeholder="Kriteria..." value="${nama}" required>
            <input type="number" name="bobot[]" class="inp-bobot-rn" placeholder="%" min="1" max="100" value="${bobot}" oninput="hitungNilaiOtomatis()" required>
            <input type="number" name="nilai[]" class="inp-nilai-rn" placeholder="0-100" min="0" max="100" step="0.01" value="${nilai}" oninput="hitungNilaiOtomatis()" required>
            <button type="button" class="btn-del-row" onclick="this.parentElement.remove(); hitungNilaiOtomatis();"><i class="fa-solid fa-trash"></i></button>
        `;
        container.appendChild(row);
        hitungNilaiOtomatis();
    }

    function hitungNilaiOtomatis() {
        const bobotInputs = document.querySelectorAll('.inp-bobot-rn');
        const nilaiInputs = document.querySelectorAll('.inp-nilai-rn');
        let totalBobot = 0;
        let nilaiAkhir = 0;

        for (let i = 0; i < bobotInputs.length; i++) {
            const b = parseFloat(bobotInputs[i].value) || 0;
            const n = parseFloat(nilaiInputs[i].value) || 0;
            totalBobot += b;
            nilaiAkhir += (n * (b / 100));
        }

        const elBobot = document.getElementById('rnTotalBobot');
        elBobot.innerText = totalBobot + '%';
        elBobot.style.color = (totalBobot === 100) ? '#15803d' : '#dc2626';
        document.getElementById('rnNilaiAkhir').innerText = nilaiAkhir.toFixed(2);
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>
</body>
</html>