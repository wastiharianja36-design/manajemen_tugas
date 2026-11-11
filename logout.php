<?php
session_start();

// Hapus semua data session untuk keamanan
$_SESSION = [];
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluar Sistem...</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Fallback jika JavaScript mati di browser -->
    <meta http-equiv="refresh" content="2.5;url=index.php">

    <style>
        :root {
            --bg-body: #f0f7f9;
            --primary-dark: #164e63; /* Cyan 900 */
            --accent-mint: #22d3ee; /* Cyan 400 - Biru neon terang */
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--primary-dark);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            color: white;
        }

        /* Container Utama Animasi */
        .logout-container {
            text-align: center;
            animation: fadeInOut 2.5s ease-in-out forwards;
        }

        /* Lingkaran Ikon Berdenyut */
        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(34, 211, 238, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px auto;
            position: relative;
            box-shadow: 0 0 20px rgba(34, 211, 238, 0.2);
        }

        .icon-circle i {
            font-size: 35px;
            color: var(--accent-mint);
            z-index: 2;
            animation: lockSnap 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s forwards;
            opacity: 0;
            transform: scale(0.5);
        }

        /* Efek Gelombang Pulse (Radar) */
        .icon-circle::after, .icon-circle::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(34, 211, 238, 0.4);
            z-index: 1;
            animation: pulseWave 1.5s cubic-bezier(0.25, 0.8, 0.25, 1) infinite;
        }

        .icon-circle::after {
            animation-delay: 0.5s;
        }

        /* Teks Status */
        .status-text {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .sub-text {
            font-size: 13px;
            color: #a5f3fc; /* Light Cyan */
            font-weight: 300;
            margin-bottom: 25px;
        }

        /* Progress Bar Modern */
        .progress-bar-container {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            margin: 0 auto;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            width: 0%;
            height: 100%;
            background: var(--accent-mint);
            box-shadow: 0 0 10px var(--accent-mint);
            animation: fillBar 2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* Keyframes Animasi */
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(20px); }
            15% { opacity: 1; transform: translateY(0); }
            85% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }

        @keyframes lockSnap {
            0% { opacity: 0; transform: scale(0.5) rotate(-20deg); }
            100% { opacity: 1; transform: scale(1) rotate(0deg); }
        }

        @keyframes pulseWave {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(2); opacity: 0; }
        }

        @keyframes fillBar {
            0% { width: 0%; }
            40% { width: 60%; }
            80% { width: 90%; }
            100% { width: 100%; }
        }

    </style>
</head>
<body>

    <div class="logout-container">
        <!-- Ikon yang dikelilingi efek pulse -->
        <div class="icon-circle">
            <i class="fa-solid fa-lock" id="iconStatus"></i>
        </div>

        <!-- Teks yang akan berubah dengan Javascript -->
        <div class="status-text" id="statusText">Mengamankan Sesi...</div>
        <div class="sub-text" id="subText">Menutup koneksi dari server</div>

        <!-- Loading bar -->
        <div class="progress-bar-container">
            <div class="progress-fill"></div>
        </div>
    </div>

    <script>
        // Ubah teks & ikon setelah 1.2 detik untuk memberi kesan "Selesai"
        setTimeout(() => {
            const icon = document.getElementById('iconStatus');
            icon.classList.remove('fa-lock');
            icon.classList.add('fa-check'); // Berubah jadi centang

            document.getElementById('statusText').innerText = "Sesi Berakhir";
            document.getElementById('subText').innerText = "Sampai Jumpa!";
        }, 1200);

        // Redirect otomatis ke halaman login (index.php) setelah animasi selesai
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 2500); // 2.5 detik
    </script>

</body>
</html>