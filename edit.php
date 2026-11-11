<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data tugas khusus milik user yang sedang login
$stmt = mysqli_prepare($conn, "SELECT * FROM tugas WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas - Manajemen Tugas</title>
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

        /* 1. TRANSISI HALAMAN */
        .box {
            background: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            max-width: 550px;
            margin: auto;
            animation: pageFadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes pageFadeInUp {
            from { opacity: 0; transform: translateY(18px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* 2. SIDEBAR DESKTOP (DISAMAKAN DENGAN DASHBOARD) */
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

        .sidebar .dock-item {
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

        .sidebar .dock-item:hover, .sidebar .dock-item.active {
            background: #e6fffa;
            color: var(--primary-dark);
            transform: translateX(4px);
        }

        /* 3. MAIN AREA */
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
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* FORM ELEMENTS */
        label {
            font-size: 13px;
            font-weight: 600;
            display: block;
            margin-top: 14px;
            color: #374151;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            margin-top: 5px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            box-sizing: border-box;
            transition: border-color 0.2s;
            outline: none;
        }

        input:focus, select:focus, textarea:focus { border-color: var(--accent-mint); }
        textarea { height: 75px; resize: vertical; }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }

        .checkbox-group input { width: auto; margin: 0; cursor: pointer; }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--primary-dark);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 22px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(15, 56, 44, 0.2);
        }

        .btn-submit:hover { background: var(--primary-hover); transform: translateY(-2px); }
        .btn-submit:active { transform: scale(0.96); }

        .btn-batal {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #6b7280;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .btn-batal:hover { color: #374151; }

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

        /* --- 4. BOTTOM NAVIGATION DOCK (ANDROID MOBILE REFINED) --- */
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
                width: auto !important;
            }

            .sidebar .dock-item i { 
                font-size: 18px !important; 
                margin: 0 !important;
                transition: transform 0.25s ease !important;
            }

            .sidebar .dock-item span { 
                display: block !important; 
                font-size: 10px !important; 
                font-weight: 500 !important; 
            }
            
            .sidebar .dock-item.active {
                color: #ffffff !important;
                font-weight: 700 !important;
            }

            /* Sinar Kerucut Sorot dari Atas ke Bawah Ikon (Spotlight Cone) */
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

            /* Sumber Lampu LED Neon di Atas Kerucut */
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

            /* Animasi Elastis Pill saat Navigasi Ditekan */
            .sidebar .dock-item.bounce-elastic {
                animation: smoothPillBounce 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
            }

            .main {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 15px 15px 95px 15px !important;
            }

            .btn-toggle { display: none !important; }
        }

        /* KEYFRAME ANIMASI TERLIFT & MEMBAL ELASTIS */
        @keyframes smoothPillBounce {
            0% {
                transform: scale(0.92) translateY(2px);
                background-color: rgba(45, 212, 191, 0.15);
            }
            45% {
                transform: scale(1.05) translateY(-8px);
                background-color: rgba(45, 212, 191, 0.28);
                box-shadow: 0 6px 16px rgba(45, 212, 191, 0.3);
            }
            75% {
                transform: scale(0.98) translateY(-2px);
            }
            100% {
                transform: scale(1) translateY(0);
                background-color: transparent;
            }
        }
    </style>
</head>
<body>

<!-- Glassy Navigation Dock -->
<div class="sidebar" id="sidebar">
    <h2>Manajemen Tugas</h2>
    
    <!-- Item 1: Dashboard -->
    <a href="dashboard.php" class="dock-item" onclick="picuBounceNav(event, this, 'dashboard.php')">
        <i class="fa-solid fa-house"></i> <span>Dashboard</span>
    </a>
    
    <!-- Item 2: Kritik & Saran -->
    <a href="kritik_saran.php" class="dock-item" onclick="picuBounceNav(event, this, 'kritik_saran.php')">
        <i class="fa-solid fa-comment-dots"></i> <span>Krisar</span>
    </a>

    <!-- Item 3: Logout -->
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
        <h2 class="title"><i class="fa-solid fa-pen-to-square"></i> Edit Tugas</h2>

        <form action="update.php" method="POST">
            <input type="hidden" name="id" value="<?= $row['id']; ?>">

            <label>Judul Tugas</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($row['judul']); ?>" required>

            <label>Mata Kuliah</label>
            <input type="text" name="matkul" value="<?= htmlspecialchars($row['matkul']); ?>" required>

            <label>Deadline</label>
            <input type="date" name="deadline" value="<?= htmlspecialchars($row['deadline']); ?>" required>

            <label>Tingkat Kesulitan</label>
            <select name="tingkat_kesulitan" required>
                <option value="Mudah" <?= ($row['tingkat_kesulitan'] == 'Mudah') ? 'selected' : ''; ?>>Mudah (Bisa selesai cepat)</option>
                <option value="Sedang" <?= ($row['tingkat_kesulitan'] == 'Sedang') ? 'selected' : ''; ?>>Sedang (Butuh waktu beberapa hari)</option>
                <option value="Sulit" <?= ($row['tingkat_kesulitan'] == 'Sulit') ? 'selected' : ''; ?>>Sulit / Kompleks (Butuh riset & persiapan)</option>
            </select>

            <label>Status Tugas</label>
            <select name="status" required>
                <option value="Belum" <?= ($row['status'] == 'Belum') ? 'selected' : ''; ?>>Belum Selesai</option>
                <option value="Selesai" <?= ($row['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
            </select>

            <label>Deskripsi / Catatan Tugas</label>
            <textarea name="deskripsi"><?= htmlspecialchars($row['deskripsi']); ?></textarea>

            <label>Link Lampiran / Repo / Drive</label>
            <input type="url" name="url_link" value="<?= htmlspecialchars($row['url_link'] ?? ''); ?>" placeholder="https://drive.google.com/...">

            <div class="checkbox-group">
                <input type="checkbox" name="is_pinned" id="is_pinned" value="1" <?= ($row['is_pinned'] == 1) ? 'checked' : ''; ?>>
                <label for="is_pinned" style="margin:0; cursor:pointer; font-weight:normal;">⭐ Tandai sebagai Prioritas Utama (Pin di atas)</label>
            </div>

            <button type="submit" name="update" class="btn-submit">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
            <a href="dashboard.php" class="btn-batal">Batal & Kembali ke Dashboard</a>
        </form>
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

    // Pemicu Reaksi Elastis Navigasi Dock
    function picuBounceNav(event, element, tujuan) {
        event.preventDefault();
        element.classList.add('bounce-elastic');
        
        setTimeout(() => {
            element.classList.remove('bounce-elastic');
            if (tujuan) {
                window.location.href = tujuan;
            }
        }, 220);
    }
</script>

</body>
</html>