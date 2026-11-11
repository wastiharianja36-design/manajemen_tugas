<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung lempar ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$sukses_login = false; // Flag untuk trigger animasi sukses login

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            // Set Session
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['notif_sudah_tampil'] = false; // Reset notif dashboard untuk session ini

            $sukses_login = true; // Trigger animasi sukses di JS
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manajemen Tugas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-dark: #164e63; /* Cyan 900 */
            --primary-hover: #083344;
            --accent-mint: #22d3ee; /* Cyan 400 */
            --accent-blue-text: #0284c7;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #09203f 0%, #164e63 50%, #0f172a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
            overflow: hidden;
            position: relative;
        }

        /* Animasi Pergeseran Warna Background */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- BACKGROUND ANIMATED GLOWING ORBS --- */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            z-index: 1;
            opacity: 0.5;
            animation: floatOrb 10s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 350px;
            height: 350px;
            background: rgba(34, 211, 238, 0.35);
            top: -50px;
            left: -50px;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: rgba(14, 165, 233, 0.25);
            bottom: -80px;
            right: -80px;
            animation-delay: -5s;
        }

        @keyframes floatOrb {
            0% { transform: translateY(0px) translateX(0px) scale(1); }
            100% { transform: translateY(40px) translateX(30px) scale(1.1); }
        }

        /* --- GLASSMORPHISM DESIGN LOGIN BOX --- */
        .login-box {
            background: rgba(255, 255, 255, 0.85); /* Semi transparan */
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            padding: 35px 30px;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.6);
            width: 100%;
            max-width: 380px;
            box-sizing: border-box;
            border: 1px solid rgba(255, 255, 255, 0.4);
            position: relative;
            z-index: 10;
            animation: formFloatIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes formFloatIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ANIMASI ERROR SHAKE (Bergetar saat salah password) */
        .login-box.error-shake {
            animation: shakeLeftRight 0.5s cubic-bezier(.36,.07,.19,.97) both;
            border: 1px solid #ef4444;
            box-shadow: 0 0 25px rgba(239, 68, 68, 0.4);
        }

        @keyframes shakeLeftRight {
            10%, 90% { transform: translateX(-3px); }
            20%, 80% { transform: translateX(5px); }
            30%, 50%, 70% { transform: translateX(-8px); }
            40%, 60% { transform: translateX(8px); }
        }

        .header-title { text-align: center; margin-bottom: 25px; }
        
        .header-title h2 {
            color: var(--primary-dark);
            margin: 0; font-size: 22px; font-weight: 700;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        
        .header-title p { color: #475569; font-size: 13px; margin-top: 5px; }

        label {
            font-size: 13px; font-weight: 600; display: block;
            margin-top: 15px; color: var(--primary-dark);
        }

        /* GLOWING INPUT FOCUS */
        .input-group { position: relative; margin-top: 5px; }
        
        .input-group i {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%); color: var(--primary-dark); font-size: 14px;
            transition: color 0.3s;
        }

        .input-group input {
            width: 100%; padding: 12px 12px 12px 40px;
            border: 2px solid rgba(203, 213, 225, 0.8); border-radius: 12px;
            font-family: 'Poppins', sans-serif; font-size: 13px;
            box-sizing: border-box; transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--accent-mint);
            background: #ffffff;
            box-shadow: 0 0 15px rgba(34, 211, 238, 0.35);
        }

        .input-group input:focus + i, .input-group input:focus ~ i {
            color: var(--accent-mint);
        }

        /* MORPHING BUTTON ANIMATION */
        .btn-wrapper {
            position: relative;
            width: 100%;
            height: 48px;
            margin-top: 25px;
            display: flex;
            justify-content: center;
        }

        .btn-login {
            width: 100%;
            height: 100%;
            background: var(--primary-dark);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            box-shadow: 0 6px 20px rgba(22, 78, 99, 0.35);
            position: absolute;
        }

        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 78, 99, 0.45);
        }

        /* Tombol berubah jadi loading bulat */
        .btn-login.loading {
            width: 48px;
            border-radius: 50%;
            background: var(--primary-dark);
            color: transparent;
            pointer-events: none;
            box-shadow: 0 0 20px rgba(34, 211, 238, 0.5);
        }

        .btn-login.loading::after {
            content: "";
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: var(--accent-mint);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* Tombol berubah jadi centang hijau saat sukses */
        .btn-login.success-morph {
            width: 48px;
            border-radius: 50%;
            background: #10b981;
            color: white;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.7);
            pointer-events: none;
        }

        .btn-login.success-morph::after { display: none; }

        .btn-login.success-morph i {
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
            background: rgba(254, 226, 226, 0.9); color: #b91c1c;
            padding: 12px; border-radius: 10px; margin-bottom: 15px;
            text-align: center; font-size: 13px; font-weight: 500;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            border: 1px solid #fca5a5;
        }

        .register-link { text-align: center; margin-top: 20px; font-size: 13px; color: #475569; }
        .register-link a { color: var(--accent-blue-text); text-decoration: none; font-weight: 600; }
        .register-link a:hover { text-decoration: underline; color: var(--primary-dark); }

        .credit { margin-top: 25px; text-align: center; color: #64748b; font-size: 11px; border-top: 1px solid rgba(203, 213, 225, 0.6); padding-top: 15px; }
        .credit h4 { margin: 2px 0 0 0; font-size: 12px; color: var(--primary-dark); }
    </style>
</head>
<body>

<!-- Latar Belakang Bola Cahaya Animasi -->
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="login-box <?= !empty($error) ? 'error-shake' : '' ?>" id="loginBox">
    <div class="header-title">
        <h2><i class="fa-solid fa-right-to-bracket"></i> Masuk Sistem</h2>
        <p>Silakan masuk untuk mengakses tugasmu</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-msg">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Username</label>
        <div class="input-group">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username" placeholder="Masukkan username..." required <?= empty($error) ? 'autofocus' : '' ?>>
        </div>

        <label>Password</label>
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" placeholder="Masukkan password..." required>
        </div>

        <div class="btn-wrapper">
            <button type="submit" name="login" id="btnLogin" class="btn-login">
                <i class="fa-solid fa-arrow-right-to-bracket" id="btnIcon"></i> <span id="btnText">Masuk</span>
            </button>
        </div>
    </form>

    <form></form>

    <div class="register-link">
        Belum punya akun? <a href="daftar.php">Daftar di Sini</a>
    </div>

    <div class="credit">
        <span>Developed by</span>
        <h4>Wasti Harianja & Cetrin Yolanda</h4>
    </div>
</div>

<script>
    // CEK APAKAH LOGIN BERHASIL DARI PHP
    <?php if ($sukses_login): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btnLogin');
            const icon = document.getElementById('btnIcon');
            const text = document.getElementById('btnText');
            
            // 1. Ubah tombol jadi loading bulat
            btn.classList.add('loading');
            text.style.display = 'none';
            icon.style.display = 'none';

            // 2. Berubah jadi centang hijau sukses
            setTimeout(() => {
                btn.classList.remove('loading');
                btn.classList.add('success-morph');
                icon.className = 'fa-solid fa-check';
            }, 1200);

            // 3. Masuk ke halaman dashboard.php
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2200);
        });
    <?php endif; ?>

    // Hilangkan efek shake setelah bergetar
    const loginBox = document.getElementById('loginBox');
    if(loginBox.classList.contains('error-shake')) {
        setTimeout(() => {
            loginBox.classList.remove('error-shake');
        }, 600);
    }
</script>

</body>
</html>