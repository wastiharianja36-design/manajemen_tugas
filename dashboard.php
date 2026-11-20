<?php
session_start();
if (!isset($_SESSION['notif_sudah_tampil'])) {
    $_SESSION['notif_sudah_tampil'] = false;
}
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'dosen') {
    header("Location: dashboard_dosen.php");
    exit;
}
include 'koneksi.php';
$user_id = $_SESSION['user_id'];
$cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";
$filter_matkul = isset($_GET['filter_matkul']) ? mysqli_real_escape_string($conn, $_GET['filter_matkul']) : "";
$filter_jenis = isset($_GET['filter_jenis']) ? $_GET['filter_jenis'] : "";

$query_matkul = mysqli_query($conn, "SELECT DISTINCT matkul FROM tugas WHERE user_id = '$user_id' ORDER BY matkul ASC");
$query_dosen = mysqli_query($conn, "SELECT id, username, nama_lengkap, matkul_ampu FROM users WHERE role = 'dosen' ORDER BY matkul_ampu ASC");

$q_kantong_all = mysqli_query($conn, "SELECT id, dosen_id, jenis_tugas, judul_kantong, deadline FROM kantong_tugas ORDER BY deadline ASC");
$semua_kantong = [];
while ($kt = mysqli_fetch_assoc($q_kantong_all)) {
    $semua_kantong[] = $kt;
}

$where = "WHERE t.user_id = '$user_id'";
if (!empty($cari)) {
    $where .= " AND (t.judul LIKE '%$cari%' OR t.matkul LIKE '%$cari%')";
}
if (!empty($filter_matkul)) {
    $where .= " AND t.matkul = '$filter_matkul'";
}
if ($filter_jenis === 'pribadi') {
    $where .= " AND t.dosen_id IS NULL";
} elseif ($filter_jenis === 'dosen') {
    $where .= " AND t.dosen_id IS NOT NULL";
}

$query_data = "SELECT t.*, d.nama_lengkap AS nama_dosen, d.username AS username_dosen, d.matkul_ampu, k.jenis_tugas AS k_jenis 
               FROM tugas t 
               LEFT JOIN users d ON t.dosen_id = d.id 
               LEFT JOIN kantong_tugas k ON t.kantong_id = k.id
               $where 
               ORDER BY t.is_pinned DESC, t.deadline ASC, t.id DESC";
$data = mysqli_query($conn, $query_data);
$total = mysqli_num_rows($data);
$jumlah_belum = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tugas WHERE user_id = '$user_id' AND status='Belum'"));
$jumlah_selesai = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tugas WHERE user_id = '$user_id' AND status='Selesai'"));

$terlambat = 0;
$hari_ini  = 0;
$besok     = 0;
$mendekati = 0;
$query_notif = mysqli_query($conn, "SELECT deadline FROM tugas WHERE user_id = '$user_id' AND status = 'Belum'");
$tgl_sekarang = date('Y-m-d');
while ($t = mysqli_fetch_assoc($query_notif)) {
    $tgl_deadline = $t['deadline'];
    if ($tgl_deadline < $tgl_sekarang) {
        $terlambat++;
    } elseif ($tgl_deadline == $tgl_sekarang) {
        $hari_ini++;
    } elseif ($tgl_deadline == date('Y-m-d', strtotime('+1 day'))) {
        $besok++;
    } else {
        $diff = (strtotime($tgl_deadline) - strtotime($tgl_sekarang)) / (60 * 60 * 24);
        if ($diff > 1 && $diff <= 3) {
            $mendekati++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nugasia</title>
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
            --alert-red: #dc2626;
            --alert-red-bg: #fee2e2;
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
        .box {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            animation: pageFadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes pageFadeInUp {
            from { opacity: 0; transform: translateY(18px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .notif-banner {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-left: 5px solid #dc2626;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 13.5px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.08);
            animation: slideDown 0.4s ease-out;
        }
        .notif-banner i { font-size: 22px; color: #dc2626; }
        .notif-banner strong { color: #7f1d1d; }
        .sidebar {
            width: 230px;
            height: 100vh;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #1f2937;
            padding: 20px;
            position: fixed;
            transition: all 0.3s ease-in-out;
            box-sizing: border-box;
            z-index: 100;
            border-right: 1px solid rgba(229, 231, 235, 0.8);
            transform: translateX(-100%);
        }
        .sidebar.open { transform: translateX(0); }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
            color: var(--primary-dark);
            font-weight: 700;
        }
        .sidebar a, .sidebar button {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4b5563;
            text-decoration: none;
            padding: 12px 14px;
            margin-top: 8px;
            border-radius: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 14px;
            font-weight: 500;
            width: 100%;
            box-sizing: border-box;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .sidebar a:hover, .sidebar button:hover, .sidebar a.active {
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
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(15, 56, 44, 0.2);
        }
        .btn-toggle:hover { background: var(--primary-hover); transform: translateY(-2px); }
        .btn-toggle:active { transform: scale(0.95); }
        h2.title {
            color: var(--primary-dark);
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .card-container {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .card {
            flex: 1;
            padding: 18px;
            border-radius: 14px;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.04); }
        .card span { display: block; font-size: 26px; font-weight: 700; margin-top: 4px; }
        .card.total { background: #e6f4ea; color: var(--primary-dark); border-color: #b7e4c7; }
        .card.belum-card { background: #ffffff; color: #4b5563; }
        .card.selesai-card { background: #86efac; color: #14532d; border-color: #4ade80; }
        .card.terlambat-card { background: var(--alert-red-bg); color: var(--alert-red); border-color: #fca5a5; }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .action-btns { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn {
            padding: 10px 18px;
            background: var(--primary-dark);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .btn:hover { background: var(--primary-hover); transform: translateY(-2px); }
        .btn:active { transform: scale(0.94); }
        .btn-print { background: #0284c7; }
        .btn-print:hover { background: #0369a1; }
        .search-box { display: flex; gap: 8px; flex-wrap: wrap; }
        .search-box select, .search-box input {
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-box select:focus, .search-box input:focus { border-color: var(--accent-mint); }
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
            min-width: 780px;
        }
        th {
            background: var(--primary-dark);
            color: white;
            padding: 14px;
            font-size: 14px;
            font-weight: 600;
        }
        td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            vertical-align: top;
        }
        .progress-container {
            width: 100%;
            background-color: #e5e7eb;
            border-radius: 6px;
            height: 8px;
            margin-top: 6px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background-color: #10b981;
            border-radius: 6px;
            transition: width 0.3s;
        }
        .btn-subtugas {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--accent-blue);
            color: var(--accent-blue-text);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            margin-top: 4px;
            border: 1px solid #bae6fd;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .btn-subtugas:active { transform: scale(0.92); }
        .tag-pribadi {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 5px;
            margin-top: 4px;
            border: 1px solid #e2e8f0;
        }
        .tag-dosen {
            display: inline-block;
            background: #ecfeff;
            color: #0e7490;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 5px;
            margin-top: 4px;
            border: 1px solid #a5f3fc;
        }
        .review-box {
            margin-top: 7px;
            padding: 7px 9px;
            border-radius: 8px;
            font-size: 11.5px;
            line-height: 1.4;
        }
        .review-wait { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; }
        .review-revisi { background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; }
        .review-done-revisi { background: #eff6ff; border: 1px solid #93c5fd; color: #1e40af; }
        .review-acc { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; }
        .nilai-box {
            margin-top: 7px;
            padding: 7px 9px;
            border-radius: 8px;
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #14532d;
            font-size: 11.5px;
        }
        .badge-mudah { background: #dcfce7; color: #15803d; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
        .badge-sedang { background: #fef9c3; color: #a16207; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
        .badge-sulit { background: #fce7f3; color: #be185d; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
        .mulai-awal { 
            display: block; 
            margin-top: 4px; 
            background: #fff7ed; 
            color: #c2410c; 
            font-size: 11px; 
            padding: 3px 6px; 
            border-radius: 4px; 
            border: 1px solid #ffedd5; 
            font-weight: 600;
        }
        .belum { background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 6px; font-weight: 600; }
        .selesai { background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: 600; }
        .terlambat-badge { background: var(--alert-red); color: white; padding: 4px 10px; border-radius: 6px; font-weight: 700; box-shadow: 0 2px 5px rgba(220,38,38,0.3); }
        .badge-hari-ini { background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 6px; font-weight: 700; border: 1px solid #fca5a5; }
        .badge-besok { background: #ffedd5; color: #c2410c; padding: 4px 10px; border-radius: 6px; font-weight: 700; border: 1px solid #fed7aa; }
        .badge-mendekati { background: #fef9c3; color: #854d0e; padding: 4px 10px; border-radius: 6px; font-weight: 600; border: 1px solid #fef08a; }
        .btn-link {
            background: var(--accent-blue);
            color: var(--accent-blue-text);
            padding: 4px 8px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin: 2px 0;
        }
        .btn-pdf {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 4px 8px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin: 2px 0;
        }
        .edit { background: #e0f2fe; color: #0369a1; padding: 5px 10px; border-radius: 5px; text-decoration: none; margin-right: 3px; font-weight: 500; transition: transform 0.15s; display: inline-block; }
        .hapus { background: #fee2e2; color: #b91c1c; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-weight: 500; transition: transform 0.15s; display: inline-block; }
        .edit:active, .hapus:active { transform: scale(0.9); }
        .pinned-star { color: #f59e0b; font-size: 16px; margin-right: 4px; }
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
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 15px;
            box-sizing: border-box;
            overflow-y: auto;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .close-btn { cursor: pointer; font-size: 22px; color: #888; }
        .sub-item { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .jenis-switch {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 6px;
        }
        .jenis-option input { display: none; }
        .jenis-card {
            display: block;
            text-align: center;
            padding: 9px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        .jenis-option input:checked + .jenis-card {
            border-color: var(--primary-dark);
            background: #ecfdf5;
            color: var(--primary-dark);
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 92% !important;
                max-width: 380px !important;
                height: 66px !important;
                position: fixed !important;
                bottom: 16px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                border-radius: 35px !important;
                background: rgba(18, 40, 33, 0.94) !important;
                backdrop-filter: blur(18px) saturate(180%) !important;
                -webkit-backdrop-filter: blur(18px) saturate(180%) !important;
                box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.2) !important;
                border: 1px solid rgba(255, 255, 255, 0.15) !important;
                display: flex !important;
                justify-content: space-around !important;
                align-items: center !important;
                padding: 0 8px !important;
                z-index: 9999 !important;
                box-sizing: border-box !important;
            }
            .sidebar h2, .sidebar .credit { display: none !important; }
            .sidebar .dock-item {
                position: relative !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 2px !important;
                color: rgba(255, 255, 255, 0.65) !important;
                padding: 6px 14px !important;
                margin: 0 !important;
                border-radius: 20px !important; 
                font-size: 10px !important;
                text-decoration: none !important;
                background: none !important;
                border: none !important;
                box-sizing: border-box !important;
                transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.2s ease, background-color 0.25s ease !important;
            }
            .sidebar .dock-item i { font-size: 18px !important; margin: 0 !important; transition: transform 0.25s ease !important; }
            .sidebar .dock-item span { display: block !important; font-size: 10px !important; font-weight: 500 !important; }
            .sidebar .dock-item.active { color: #ffffff !important; font-weight: 700 !important; }
            .sidebar .dock-item.active::before {
                content: '' !important;
                position: absolute !important;
                top: -2px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                width: 45px !important;
                height: 52px !important;
                background: linear-gradient(to bottom, rgba(45, 212, 191, 0.5) 0%, rgba(45, 212, 191, 0.1) 70%, transparent 100%) !important;
                clip-path: polygon(32% 0%, 68% 0%, 100% 100%, 0% 100%) !important;
                pointer-events: none !important;
                z-index: 1 !important;
            }
            .sidebar .dock-item.active::after {
                content: '' !important;
                position: absolute !important;
                top: -1px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                width: 22px !important;
                height: 3.5px !important;
                background: #2dd4bf !important;
                border-radius: 0 0 4px 4px !important;
                box-shadow: 0 0 10px #2dd4bf, 0 0 16px #2dd4bf !important;
                z-index: 2 !important;
            }
            .sidebar .dock-item.bounce-elastic {
                animation: smoothPillBounce 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
            }
            .main {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 15px 15px 95px 15px !important;
            }
            .btn-toggle { display: none !important; }
            .card-container {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 10px !important;
            }
            .card { padding: 12px !important; font-size: 12px !important; }
            .card span { font-size: 22px !important; }
            .action-bar { flex-direction: column !important; align-items: stretch !important; gap: 10px !important; }
            .action-btns { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 8px !important; width: 100% !important; }
            .action-btns .btn { width: 100% !important; box-sizing: border-box !important; }
            .search-box { display: flex !important; flex-direction: column !important; gap: 8px !important; width: 100% !important; }
            .search-box select, .search-box input, .search-box button, .search-box a { width: 100% !important; box-sizing: border-box !important; }
        }
        @keyframes smoothPillBounce {
            0% { transform: scale(0.92) translateY(2px); background-color: rgba(45, 212, 191, 0.15); }
            45% { transform: scale(1.05) translateY(-8px); background-color: rgba(45, 212, 191, 0.28); box-shadow: 0 6px 16px rgba(45, 212, 191, 0.3); }
            75% { transform: scale(0.98) translateY(-2px); }
            100% { transform: scale(1) translateY(0); background-color: transparent; }
        }
    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <h2>Manajemen Tugas</h2>
    <a href="dashboard.php" class="dock-item active" onclick="picuBounceNav(event, this, 'dashboard.php')">
        <i class="fa-solid fa-house"></i> <span>Dashboard</span>
    </a>
    <button onclick="picuBounceNav(event, this, 'modalTambah')" class="dock-item" title="Tambah Tugas Baru">
        <i class="fa-solid fa-plus"></i> <span>Tambah</span>
    </button>
    <a href="kritik_saran.php" class="dock-item" onclick="picuBounceNav(event, this, 'kritik_saran.php')">
        <i class="fa-solid fa-comment-dots"></i> <span>Krisar</span>
    </a>
    <a href="logout.php" class="dock-item" onclick="picuBounceNav(event, this, 'logout.php')">
        <i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
    </a>
    <div class="credit">
        <p>Developed by</p>
        <h4>Wasti Harianja & Cetrin Yolanda</h4>
    </div>
</div>
<div class="main" id="mainContent">
    <button id="toggleBtn" class="btn-toggle" title="Tutup/Buka Sidebar">
        <i class="fa-solid fa-bars"></i> Menu
    </button>
    <div class="box">
        <h2 class="title">Dashboard Tugas</h2>
        <?php if ($terlambat > 0 || $hari_ini > 0 || $besok > 0 || $mendekati > 0): ?>
            <div class="notif-banner">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <?php if ($terlambat > 0): ?>
                        <strong>Perhatian!</strong> Ada <span><strong><?= $terlambat; ?> tugas</strong></span> yang sudah melewati deadline! <br>
                    <?php endif; ?>
                    <?php if ($hari_ini > 0): ?>
                        <strong>Darurat!</strong> Ada <span><strong><?= $hari_ini; ?> tugas</strong></span> yang tenggat waktunya <strong>HARI INI!</strong> Segera selesaikan! <br>
                    <?php endif; ?>
                    <?php if ($besok > 0): ?>
                        <strong>Pengingat:</strong> Ada <span><strong><?= $besok; ?> tugas</strong></span> yang tenggat waktunya <strong>BESOK!</strong> <br>
                    <?php endif; ?>
                    <?php if ($mendekati > 0): ?>
                        <strong>Info:</strong> Ada <span><strong><?= $mendekati; ?> tugas</strong></span> yang deadline-nya tinggal 2-3 hari lagi.
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="card-container">
            <div class="card total">Total <span><?= $total; ?></span></div>
            <div class="card belum-card">Belum <span><?= $jumlah_belum; ?></span></div>
            <div class="card selesai-card">Selesai <span><?= $jumlah_selesai; ?></span></div>
            <div class="card terlambat-card">Terlambat <span><?= $terlambat; ?></span></div>
        </div>
        <div class="action-bar">
            <div class="action-btns">
                <button onclick="bukaModalTambah()" class="btn"><i class="fa-solid fa-plus"></i> Tambah Tugas</button>
                <button onclick="window.print()" class="btn btn-print"><i class="fa-solid fa-print"></i> Cetak</button>
            </div>
            <form method="GET" class="search-box">
                <select name="filter_jenis" onchange="this.form.submit()">
                    <option value="">-- Semua Jenis Tugas --</option>
                    <option value="pribadi" <?= ($filter_jenis === 'pribadi') ? 'selected' : ''; ?>>Tugas Pribadi</option>
                    <option value="dosen" <?= ($filter_jenis === 'dosen') ? 'selected' : ''; ?>>Pantauan Dosen</option>
                </select>
                <select name="filter_matkul" onchange="this.form.submit()">
                    <option value="">-- Semua Mata Kuliah --</option>
                    <?php while ($m = mysqli_fetch_assoc($query_matkul)): ?>
                        <option value="<?= htmlspecialchars($m['matkul']); ?>" <?= ($filter_matkul == $m['matkul']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($m['matkul']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="cari" placeholder="Cari judul..." value="<?= htmlspecialchars($cari); ?>">
                <button type="submit" class="btn"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
                <?php if (!empty($cari) || !empty($filter_matkul) || !empty($filter_jenis)): ?>
                    <a href="dashboard.php" class="btn" style="background:#6b7280;" title="Reset Filter"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Mata Kuliah</th>
                        <th>Kesulitan</th>
                        <th>Deadline</th>
                        <th>Lampiran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($data) > 0):
                        while ($row = mysqli_fetch_assoc($data)):
                            $tugas_id = $row['id'];
                            $sub_total_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM sub_tugas WHERE tugas_id='$tugas_id'");
                            $sub_total = mysqli_fetch_assoc($sub_total_q)['total'];
                            $sub_done_q = mysqli_query($conn, "SELECT COUNT(*) as done FROM sub_tugas WHERE tugas_id='$tugas_id' AND is_completed=1");
                            $sub_done = mysqli_fetch_assoc($sub_done_q)['done'];
                            $persen = ($sub_total > 0) ? round(($sub_done / $sub_total) * 100) : 0;
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td style="text-align: left; max-width: 280px;">
                            <div>
                                <?php if ($row['is_pinned'] == 1): ?>
                                    <i class="fa-solid fa-star pinned-star" title="Tugas Prioritas Utama"></i>
                                <?php endif; ?>
                                <strong style="color: var(--primary-dark); font-size: 14px;"><?= htmlspecialchars($row['judul']); ?></strong>
                            </div>
                            <?php if (!empty($row['dosen_id'])): 
                                $nama_tampil_dosen = !empty($row['nama_dosen']) ? $row['nama_dosen'] : $row['username_dosen'];
                            ?>
                                <span class="tag-dosen">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                    <?= !empty($row['k_jenis']) ? htmlspecialchars($row['k_jenis']) . ' • ' : ''; ?>
                                    <?= htmlspecialchars($nama_tampil_dosen); ?>
                                </span>
                            <?php else: ?>
                                <span class="tag-pribadi"><i class="fa-solid fa-lock"></i> Tugas Pribadi</span>
                            <?php endif; ?>

                            <?php if (!empty($row['deskripsi'])): ?>
                                <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                    <?= htmlspecialchars($row['deskripsi']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($row['dosen_id'])): ?>
                                <?php
                                $st_rev = $row['status_review'] ?? 'belum_direview';
                                if ($st_rev === 'revisi'):
                                ?>
                                    <div class="review-box review-revisi">
                                        <strong><i class="fa-solid fa-pen"></i> Perlu Revisi Dosen:</strong><br>
                                        <?= nl2br(htmlspecialchars($row['catatan_dosen'])); ?>
                                    </div>
                                <?php elseif ($st_rev === 'sudah_direvisi'): ?>
                                    <div class="review-box review-done-revisi">
                                        <strong><i class="fa-solid fa-clock-rotate-left"></i> Sudah Direvisi (Menunggu Cek Dosen)</strong>
                                        <?php if (!empty($row['catatan_dosen'])): ?>
                                            <div style="margin-top:3px; opacity:0.85;">Catatan sebelumnya: <?= htmlspecialchars($row['catatan_dosen']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($st_rev === 'acc'): ?>
                                    <div class="review-box review-acc">
                                        <strong><i class="fa-solid fa-circle-check"></i> Sudah Direview Dosen — 100% Pas</strong>
                                        <?php if (!empty($row['catatan_dosen'])): ?>
                                            <div style="margin-top:3px;"><?= nl2br(htmlspecialchars($row['catatan_dosen'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="review-box review-wait">
                                        <i class="fa-regular fa-hourglass-half"></i> Menunggu Review Dosen
                                    </div>
                                <?php endif; ?>

                                <?php if ($row['nilai_akhir'] !== null): 
                                    $rincian = json_decode($row['detail_nilai'], true);
                                ?>
                                    <div class="nilai-box">
                                        <strong><i class="fa-solid fa-award"></i> Nilai Akhir: <?= number_format((float)$row['nilai_akhir'], 2); ?> / 100</strong>
                                        <?php if (is_array($rincian) && count($rincian) > 0): ?>
                                            <div style="margin-top:3px; font-size:11px;">
                                                <?php
                                                $parts = [];
                                                foreach ($rincian as $item) {
                                                    $parts[] = htmlspecialchars($item['kriteria']) . ": " . $item['nilai'] . " (" . $item['bobot'] . "%)";
                                                }
                                                echo implode(" • ", $parts);
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div style="margin-top: 6px;">
                                <button onclick="bukaModalSub(<?= $tugas_id; ?>, '<?= htmlspecialchars(addslashes($row['judul'])); ?>')" class="btn-subtugas">
                                    <i class="fa-solid fa-list-check"></i> Sub-Tugas (<?= $sub_done; ?>/<?= $sub_total; ?>)
                                </button>
                                <?php if ($sub_total > 0): ?>
                                    <div class="progress-container" title="<?= $persen; ?>% Selesai">
                                        <div class="progress-bar" style="width: <?= $persen; ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['matkul']); ?></td>
                        <td>
                            <?php
                            $tingkat = $row['tingkat_kesulitan'] ?? 'Sedang';
                            if ($tingkat == 'Mudah') {
                                echo "<span class='badge-mudah'>Mudah</span>";
                            } elseif ($tingkat == 'Sulit') {
                                echo "<span class='badge-sulit'>Sulit</span>";
                                $tgl_deadline = strtotime($row['deadline']);
                                $hari_ini_str = strtotime(date('Y-m-d'));
                                $sisa_hari = floor(($tgl_deadline - $hari_ini_str) / (60 * 60 * 24));
                                if ($sisa_hari >= 0 && $sisa_hari <= 14 && $row['status'] == 'Belum') {
                                    echo "<span class='mulai-awal'>⚠️ Cicil dari sekarang!</span>";
                                }
                            } else {
                                echo "<span class='badge-sedang'>Sedang</span>";
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['deadline']); ?></td>
                        <td>
                            <?php if (!empty($row['file_tugas'])): ?>
                                <a href="uploads/<?= rawurlencode($row['file_tugas']); ?>" target="_blank" class="btn-pdf">
                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                </a><br>
                            <?php endif; ?>
                            <?php if (!empty($row['url_link'])): ?>
                                <a href="<?= htmlspecialchars($row['url_link']); ?>" target="_blank" class="btn-link">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Link
                                </a>
                            <?php endif; ?>
                            <?php if (empty($row['file_tugas']) && empty($row['url_link'])): ?>
                                <span style="color:#aaa;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status = $row['status'];
                            $tgl_dead = $row['deadline'];
                            $tgl_now = date('Y-m-d');
                            $selisih = (strtotime($tgl_dead) - strtotime($tgl_now)) / (60 * 60 * 24);
                            if ($status == "Selesai") {
                                echo "<span class='selesai'>Selesai</span>";
                            } elseif ($tgl_dead < $tgl_now) {
                                echo "<span class='terlambat-badge'>Terlambat</span>";
                            } elseif ($tgl_dead == $tgl_now) {
                                echo "<span class='badge-hari-ini'>🔥 Hari Ini!</span>";
                            } elseif ($selisih == 1) {
                                echo "<span class='badge-besok'>⏰ Besok</span>";
                            } elseif ($selisih > 1 && $selisih <= 3) {
                                echo "<span class='badge-mendekati'>⏳ H-" . ceil($selisih) . "</span>";
                            } else {
                                echo "<span class='belum'>Belum</span>";
                            }
                            ?>
                        </td>
                        <td style="white-space: nowrap;">
                            <a class="edit" href="edit.php?id=<?= $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                            <a class="hapus" href="hapus.php?id=<?= $row['id']; ?>" onclick="return confirm('Yakin hapus tugas ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr>
                        <td colspan="8">Belum ada tugas yang sesuai filter.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="modalTambah">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin:0; color: var(--primary-dark); font-size:16px;">
                <i class="fa-solid fa-square-plus"></i> Tambah Tugas Baru
            </h3>
            <span class="close-btn" onclick="tutupModalTambah()">&times;</span>
        </div>
        <form action="simpan.php" method="POST" enctype="multipart/form-data">
            <label style="font-size:13px; font-weight:600; display:block;">Jenis Tugas</label>
            <div class="jenis-switch">
                <label class="jenis-option">
                    <input type="radio" name="jenis_tugas" value="pribadi" checked onchange="togglePilihDosen()">
                    <span class="jenis-card"><i class="fa-solid fa-lock"></i> Tugas Pribadi</span>
                </label>
                <label class="jenis-option">
                    <input type="radio" name="jenis_tugas" value="dosen" onchange="togglePilihDosen()">
                    <span class="jenis-card"><i class="fa-solid fa-chalkboard-user"></i> Pantauan Dosen</span>
                </label>
            </div>

            <div id="boxPilihDosen" style="display:none; margin-top:10px; background:#f0f9ff; padding:11px; border-radius:8px; border:1px dashed #38bdf8;">
                <label style="font-size:12.5px; font-weight:600; display:block; color:#0369a1;">1. Pilih Mata Kuliah & Dosen</label>
                <select name="dosen_id" id="selectDosen" onchange="isiMatkulDanKantong()" style="width:100%; padding:9px; border-radius:6px; border:1px solid #bae6fd; margin-top:4px; box-sizing:border-box;">
                    <option value="">-- Pilih Dosen Pengampu --</option>
                    <?php while ($d = mysqli_fetch_assoc($query_dosen)): 
                        $nama_d = !empty($d['nama_lengkap']) ? $d['nama_lengkap'] : $d['username'];
                    ?>
                        <option value="<?= $d['id']; ?>" data-matkul="<?= htmlspecialchars($d['matkul_ampu']); ?>">
                            <?= htmlspecialchars($d['matkul_ampu']); ?> — <?= htmlspecialchars($nama_d); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label style="font-size:12.5px; font-weight:600; display:block; color:#0369a1; margin-top:9px;">2. Pilih Kantong Tugas Dosen</label>
                <select name="kantong_id" id="selectKantong" onchange="isiDetailKantong()" style="width:100%; padding:9px; border-radius:6px; border:1px solid #bae6fd; margin-top:4px; box-sizing:border-box;">
                    <option value="">-- Pilih Dosen Terlebih Dahulu --</option>
                </select>
            </div>

            <label style="font-size:13px; font-weight:600; display:block; margin-top:10px;">Judul Tugas</label>
            <input type="text" name="judul" id="inputJudul" placeholder="Contoh: Laporan Mini Research" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box;">
            
            <label style="font-size:13px; font-weight:600; display:block; margin-top:10px;">Mata Kuliah</label>
            <input type="text" name="matkul" id="inputMatkul" placeholder="Contoh: Cloud Computing" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box;">
            
            <label style="font-size:13px; font-weight:600; display:block; margin-top:10px;">Deadline</label>
            <input type="date" name="deadline" id="inputDeadline" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box;">
            
            <label style="font-size:13px; font-weight:600; display:block; margin-top:10px;">Tingkat Kesulitan</label>
            <select name="tingkat_kesulitan" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box;">
                <option value="Mudah">Mudah (Bisa selesai cepat)</option>
                <option value="Sedang" selected>Sedang (Butuh waktu beberapa hari)</option>
                <option value="Sulit">Sulit / Kompleks (Butuh riset & persiapan)</option>
            </select>
            
            <label style="font-size:13px; font-weight:600; display:block; margin-top:10px;">Deskripsi / Catatan Tugas</label>
            <textarea name="deskripsi" placeholder="Tuliskan catatan atau ringkasan pengerjaan..." style="width:100%; height:58px; padding:9px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box; font-family:inherit; resize:vertical;"></textarea>

            <label style="font-size:13px; font-weight:600; display:block; margin-top:10px;">Upload File PDF (Opsional, Maks. 8MB — Langsung Tampil di Dosen)</label>
            <input type="file" name="file_tugas" accept=".pdf" style="width:100%; padding:7px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box; font-size:12.5px;">

            <label style="font-size:13px; font-weight:600; display:block; margin-top:10px;">Link Lampiran / Repo / Drive (Opsional)</label>
            <input type="url" name="url_link" placeholder="https://drive.google.com/..." style="width:100%; padding:9px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box;">
            
            <div style="display:flex; align-items:center; gap:8px; margin-top:12px;">
                <input type="checkbox" name="is_pinned" id="is_pinned_modal" value="1">
                <label for="is_pinned_modal" style="font-size:13px; cursor:pointer; margin:0;">⭐ Prioritas Utama (Pin di atas)</label>
            </div>
            <button type="submit" class="btn" style="width:100%; margin-top:18px; justify-content:center; padding:12px;">Simpan Tugas</button>
        </form>
    </div>
</div>

<div class="modal" id="modalSub">
    <div class="modal-content">
        <div class="modal-header">
            <h4 style="margin:0; color: var(--primary-dark);" id="modalJudulTugas">Checklist Sub-Tugas</h4>
            <span class="close-btn" onclick="tutupModalSub()">&times;</span>
        </div>
        <form id="formSubTugas" onsubmit="tambahSub(event)" style="margin-bottom:15px;">
            <input type="hidden" id="modalTugasId" name="tugas_id">
            <input type="hidden" name="aksi" value="tambah">
            <textarea id="inputJudulSub" name="judul_sub" placeholder="Tulis sub-tugas (bisa 2-3 baris sekaligus, pisahkan dengan Enter)..." required style="width:100%; height:70px; padding:8px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box; font-family:inherit; resize:none; font-size:13px;"></textarea>
            <button type="submit" class="btn" style="width:100%; margin-top:8px; justify-content:center; padding:8px;">+ Tambah Sub-Tugas</button>
        </form>
        <div id="subTugasList"></div>
    </div>
</div>

<?php if (!$_SESSION['notif_sudah_tampil'] && ($terlambat > 0 || $hari_ini > 0 || $besok > 0 || $mendekati > 0)): 
    $_SESSION['notif_sudah_tampil'] = true;
    $pesan_popup = "⚠️ PERINGATAN TUGAS MENDESAK:\\n\\n";
    if ($terlambat > 0) $pesan_popup .= "- $terlambat tugas TERLAMBAT!\\n";
    if ($hari_ini > 0)  $pesan_popup .= "- $hari_ini tugas DEADLINE HARI INI!\\n";
    if ($besok > 0)     $pesan_popup .= "- $besok tugas DEADLINE BESOK!\\n";
    if ($mendekati > 0) $pesan_popup .= "- $mendekati tugas deadline dalam 2-3 hari!\\n";
    $pesan_popup .= "\\nSegera periksa dan cicil tugasmu!";
?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        alert("<?= $pesan_popup; ?>");
    });
</script>
<?php endif; ?>

<script>
    const daftarKantong = <?= json_encode($semua_kantong); ?>;

    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        mainContent.classList.toggle('shifted');
    });

    function togglePilihDosen() {
        const isDosen = document.querySelector('input[name="jenis_tugas"]:checked').value === 'dosen';
        const boxDosen = document.getElementById('boxPilihDosen');
        const selectDosen = document.getElementById('selectDosen');
        const selectKantong = document.getElementById('selectKantong');
        if (isDosen) {
            boxDosen.style.display = 'block';
            selectDosen.required = true;
        } else {
            boxDosen.style.display = 'none';
            selectDosen.required = false;
            selectDosen.value = '';
            selectKantong.innerHTML = '<option value="">-- Pilih Dosen Terlebih Dahulu --</option>';
        }
    }

    function isiMatkulDanKantong() {
        const selectDosen = document.getElementById('selectDosen');
        const dosenId = selectDosen.value;
        const selectedOption = selectDosen.options[selectDosen.selectedIndex];
        const matkul = selectedOption.getAttribute('data-matkul');
        if (matkul) {
            document.getElementById('inputMatkul').value = matkul;
        }

        const selectKantong = document.getElementById('selectKantong');
        selectKantong.innerHTML = '<option value="">-- Tanpa Kantong Khusus (Umum) --</option>';
        daftarKantong.forEach(kt => {
            if (String(kt.dosen_id) === String(dosenId)) {
                const opt = document.createElement('option');
                opt.value = kt.id;
                opt.setAttribute('data-judul', `[${kt.jenis_tugas}] ${kt.judul_kantong}`);
                opt.setAttribute('data-deadline', kt.deadline);
                opt.textContent = `[${kt.jenis_tugas}] ${kt.judul_kantong} (Deadline: ${kt.deadline})`;
                selectKantong.appendChild(opt);
            }
        });
    }

    function isiDetailKantong() {
        const selectKantong = document.getElementById('selectKantong');
        const opt = selectKantong.options[selectKantong.selectedIndex];
        const judul = opt.getAttribute('data-judul');
        const deadline = opt.getAttribute('data-deadline');
        if (judul) document.getElementById('inputJudul').value = judul;
        if (deadline) document.getElementById('inputDeadline').value = deadline;
    }

    function picuBounceNav(event, element, tujuan) {
        if (tujuan === 'dashboard.php' && window.location.pathname.endsWith('dashboard.php')) {
            event.preventDefault();
        } else if (tujuan !== 'modalTambah') {
            event.preventDefault();
        }
        element.classList.add('bounce-elastic');
        setTimeout(() => {
            element.classList.remove('bounce-elastic');
            if (tujuan === 'modalTambah') {
                bukaModalTambah();
            } else if (tujuan && !window.location.pathname.endsWith(tujuan)) {
                window.location.href = tujuan;
            }
        }, 220); 
    }

    function bukaModalTambah() {
        document.getElementById('modalTambah').style.display = 'flex';
    }
    function tutupModalTambah() {
        document.getElementById('modalTambah').style.display = 'none';
    }

    let activeTugasId = null;
    function bukaModalSub(id, judul) {
        activeTugasId = id;
        document.getElementById('modalTugasId').value = id;
        document.getElementById('modalJudulTugas').innerText = 'Checklist: ' + judul;
        document.getElementById('modalSub').style.display = 'flex';
        muatSubTugas(id);
    }
    function tutupModalSub() {
        document.getElementById('modalSub').style.display = 'none';
        window.location.reload();
    }
    function muatSubTugas(tugas_id) {
        fetch('sub_tugas_proses.php?get_list=' + tugas_id)
            .then(res => res.text())
            .then(html => {
                document.getElementById('subTugasList').innerHTML = html;
            });
    }
    function tambahSub(e) {
        e.preventDefault();
        const form = document.getElementById('formSubTugas');
        const formData = new FormData(form);
        fetch('sub_tugas_proses.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(() => {
            document.getElementById('inputJudulSub').value = '';
            muatSubTugas(activeTugasId);
        });
    }
    function toggleSub(sub_id, t_id) {
        fetch('sub_tugas_proses.php?aksi=toggle&id=' + sub_id)
            .then(() => muatSubTugas(t_id));
    }
    function hapusSub(sub_id, t_id) {
        fetch('sub_tugas_proses.php?aksi=hapus&id=' + sub_id)
            .then(() => muatSubTugas(t_id));
    }
    window.onclick = function(event) {
        const modalTambah = document.getElementById('modalTambah');
        const modalSub = document.getElementById('modalSub');
        if (event.target == modalTambah) tutupModalTambah();
        if (event.target == modalSub) tutupModalSub();
    }
</script>
</body>
</html>