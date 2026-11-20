<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'dosen') {
        header("Location: dashboard_dosen.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = "";
$proses_sukses = false;
$KODE_RAHASIA_DOSEN = "UNIMED2026";

if (isset($_POST['register'])) {
    $role = ($_POST['role'] === 'dosen') ? 'dosen' : 'mahasiswa';
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $matkul_ampu = ($role === 'dosen') ? trim($_POST['matkul_ampu']) : null;
    $kode_dosen = ($role === 'dosen') ? trim($_POST['kode_dosen']) : '';
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];

    if ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif ($role === 'dosen' && empty($matkul_ampu)) {
        $error = "Mata kuliah yang diampu wajib diisi!";
    } elseif ($role === 'dosen' && $kode_dosen !== $KODE_RAHASIA_DOSEN) {
        $error = "Kode akses dosen salah!";
    } else {
        $stmt_cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt_cek, "s", $username);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Username sudah digunakan, cari username lain!";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, nama_lengkap, password, role, matkul_ampu) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $username, $nama_lengkap, $password_hash, $role, $matkul_ampu);

            if (mysqli_stmt_execute($stmt)) {
                $proses_sukses = true;
            } else {
                $error = "Gagal mendaftar. Coba lagi nanti.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($stmt_cek);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Nugasia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-body: #f0f7f9;
            --primary-dark: #164e63;
            --primary-hover: #083344;
            --accent-mint: #22d3ee;
            --accent-blue-text: #0369a1;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #164e63 0%, #083344 100%);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
            padding: 25px 15px;
            box-sizing: border-box;
        }
        .register-box {
            background: #ffffff;
            padding: 32px 28px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            border: 1px solid rgba(34, 211, 238, 0.3);
            position: relative;
            z-index: 10;
            animation: formFloatIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes formFloatIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .register-box.error-shake {
            animation: shakeLeftRight 0.5s cubic-bezier(.36,.07,.19,.97) both;
            border: 1px solid #ef4444;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
        }
        @keyframes shakeLeftRight {
            10%, 90% { transform: translateX(-3px); }
            20%, 80% { transform: translateX(5px); }
            30%, 50%, 70% { transform: translateX(-8px); }
            40%, 60% { transform: translateX(8px); }
        }
        .header-title { text-align: center; margin-bottom: 20px; }
        .header-title h2 {
            color: var(--primary-dark);
            margin: 0; font-size: 22px; font-weight: 700;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .header-title p { color: #64748b; font-size: 13px; margin-top: 5px; }
        label {
            font-size: 13px; font-weight: 600; display: block;
            margin-top: 13px; color: var(--primary-dark);
        }
        .role-switch {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 6px;
        }
        .role-option {
            position: relative;
        }
        .role-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        .role-card {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .role-option input:checked + .role-card {
            border-color: var(--primary-dark);
            background: #ecfeff;
            color: var(--primary-dark);
            box-shadow: 0 0 10px rgba(34, 211, 238, 0.25);
        }
        .input-group { position: relative; margin-top: 5px; }
        .input-group i {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%); color: var(--primary-dark); font-size: 14px;
            transition: color 0.3s;
        }
        .input-group input {
            width: 100%; padding: 11px 12px 11px 40px;
            border: 2px solid #e2e8f0; border-radius: 10px;
            font-family: 'Poppins', sans-serif; font-size: 13px;
            box-sizing: border-box; transition: all 0.3s ease;
            background: #f8fafc;
        }
        .input-group input:focus {
            outline: none;
            border-color: var(--accent-mint);
            background: #ffffff;
            box-shadow: 0 0 12px rgba(34, 211, 238, 0.4);
        }
        .input-group input:focus ~ i { color: var(--accent-mint); }
        #dosenFields {
            display: none;
            background: #f0f9ff;
            border: 1px dashed #38bdf8;
            border-radius: 12px;
            padding: 4px 14px 14px 14px;
            margin-top: 14px;
        }
        .btn-wrapper {
            position: relative;
            width: 100%;
            height: 46px;
            margin-top: 22px;
            display: flex;
            justify-content: center;
        }
        .btn-register {
            width: 100%;
            height: 100%;
            background: var(--primary-dark);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            box-shadow: 0 4px 15px rgba(22, 78, 99, 0.3);
            position: absolute;
        }
        .btn-register:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(22, 78, 99, 0.4);
        }
        .btn-register.loading {
            width: 46px;
            border-radius: 50%;
            background: var(--primary-dark);
            color: transparent;
            pointer-events: none;
            box-shadow: 0 0 15px rgba(34, 211, 238, 0.5);
        }
        .btn-register.loading::after {
            content: "";
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: var(--accent-mint);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .btn-register.success-morph {
            width: 46px;
            border-radius: 50%;
            background: #10b981;
            color: white;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.6);
            pointer-events: none;
        }
        .btn-register.success-morph::after { display: none; }
        .btn-register.success-morph i {
            display: block !important;
            font-size: 20px;
            animation: popCheck 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes popCheck {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .error-msg {
            padding: 12px; border-radius: 8px; margin-bottom: 15px;
            text-align: center; font-size: 13px; font-weight: 500;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;
        }
        .login-link { text-align: center; margin-top: 18px; font-size: 13px; color: #64748b; }
        .login-link a { color: var(--accent-blue-text); text-decoration: none; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; color: var(--primary-dark); }
        .credit { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #f1f5f9; padding-top: 12px; }
        .credit h4 { margin: 2px 0 0 0; font-size: 12px; color: var(--primary-dark); }
        .bg-blob-1 { position: fixed; top: -100px; right: -50px; width: 300px; height: 300px; background: rgba(34, 211, 238, 0.1); border-radius: 50%; filter: blur(40px); z-index: 1; }
        .bg-blob-2 { position: fixed; bottom: -100px; left: -50px; width: 250px; height: 250px; background: rgba(34, 211, 238, 0.15); border-radius: 50%; filter: blur(50px); z-index: 1; }
    </style>
</head>
<body>
<div class="bg-blob-1"></div>
<div class="bg-blob-2"></div>
<div class="register-box <?= !empty($error) ? 'error-shake' : '' ?>" id="regBox">
    <div class="header-title">
        <h2><i class="fa-solid fa-user-plus"></i> Buat Akun Baru</h2>
        <p>Bergabung untuk mengelola tugas perkuliahan</p>
    </div>
    <?php if (!empty($error)): ?>
        <div class="error-msg">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= $error; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="" id="regForm">
        <label>Daftar Sebagai</label>
        <div class="role-switch">
            <label class="role-option" style="margin-top:0;">
                <input type="radio" name="role" value="mahasiswa" checked onchange="toggleDosenFields()">
                <span class="role-card"><i class="fa-solid fa-user-graduate"></i> Mahasiswa</span>
            </label>
            <label class="role-option" style="margin-top:0;">
                <input type="radio" name="role" value="dosen" onchange="toggleDosenFields()">
                <span class="role-card"><i class="fa-solid fa-chalkboard-user"></i> Dosen</span>
            </label>
        </div>

        <label>Nama Lengkap</label>
        <div class="input-group">
            <i class="fa-solid fa-id-card"></i>
            <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap..." required>
        </div>

        <label>Username</label>
        <div class="input-group">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username" placeholder="Buat username untuk login..." required>
        </div>

        <div id="dosenFields">
            <label>Mata Kuliah yang Diampu</label>
            <div class="input-group">
                <i class="fa-solid fa-book"></i>
                <input type="text" name="matkul_ampu" id="matkulAmpu" placeholder="Contoh: MPTI atau SPK">
            </div>

            <label>Kode Akses Dosen</label>
            <div class="input-group">
                <i class="fa-solid fa-key"></i>
                <input type="password" name="kode_dosen" id="kodeDosen" placeholder="Masukkan kode dosen...">
            </div>
        </div>

        <label>Password</label>
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" placeholder="Buat password..." required>
        </div>

        <label>Konfirmasi Password</label>
        <div class="input-group">
            <i class="fa-solid fa-shield-halved"></i>
            <input type="password" name="konfirmasi_password" placeholder="Ulangi password..." required>
        </div>

        <div class="btn-wrapper">
            <button type="submit" name="register" id="btnRegister" class="btn-register">
                <i class="fa-solid fa-user-check" id="btnIcon"></i> <span id="btnText">Daftar Akun</span>
            </button>
        </div>
    </form>
    <div class="login-link">
        Sudah punya akun? <a href="index.php">Masuk di Sini</a>
    </div>
    <div class="credit">
        <span>Developed by</span>
        <h4>Wasti Harianja dan Cetrin Yolanda</h4>
    </div>
</div>
<script>
    function toggleDosenFields() {
        const isDosen = document.querySelector('input[name="role"]:checked').value === 'dosen';
        const dosenBox = document.getElementById('dosenFields');
        const matkulInput = document.getElementById('matkulAmpu');
        const kodeInput = document.getElementById('kodeDosen');

        if (isDosen) {
            dosenBox.style.display = 'block';
            matkulInput.required = true;
            kodeInput.required = true;
        } else {
            dosenBox.style.display = 'none';
            matkulInput.required = false;
            kodeInput.required = false;
        }
    }

    <?php if ($proses_sukses): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btnRegister');
            const icon = document.getElementById('btnIcon');
            const text = document.getElementById('btnText');
            
            btn.classList.add('loading');
            text.style.display = 'none';
            icon.style.display = 'none';

            setTimeout(() => {
                btn.classList.remove('loading');
                btn.classList.add('success-morph');
                icon.className = 'fa-solid fa-check';
            }, 1500);

            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2800);
        });
    <?php endif; ?>

    const regBox = document.getElementById('regBox');
    if (regBox.classList.contains('error-shake')) {
        setTimeout(() => {
            regBox.classList.remove('error-shake');
        }, 600);
    }
</script>
</body>
</html>