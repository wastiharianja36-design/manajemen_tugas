<?php
session_start();
include 'koneksi.php';

// Proteksi akses
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Ambil pesan kritik & saran beserta nama pengirimnya
$query = "SELECT k.*, u.username FROM kritik_saran k 
          JOIN users u ON k.user_id = u.id 
          ORDER BY k.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Masukan Pengguna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #c1ddff;
            padding: 30px;
            margin: 0;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: auto;
        }

        h2 {
            color: #29205e;
            margin-top: 0;
        }

        .btn-kembali {
            display: inline-block;
            padding: 8px 14px;
            background: #4b3f8f;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #99e1a9;
            color: #0d1a25;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .badge-kategori {
            background: #e0e7ff;
            color: #3730a3;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="box">
    <a href="dashboard.php" class="btn-kembali"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
    <h2><i class="fa-solid fa-comments"></i> Daftar Kritik & Saran Pengguna Trial</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Pengirim</th>
                <th>Kategori</th>
                <th>Pesan Masukan</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)): 
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><strong><?= htmlspecialchars($row['username']); ?></strong></td>
                <td><span class="badge-kategori"><?= htmlspecialchars($row['kategori']); ?></span></td>
                <td><?= htmlspecialchars($row['pesan']); ?></td>
                <td style="color: #666; font-size: 12px;"><?= $row['created_at']; ?></td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="5" style="text-align:center;">Belum ada kritik atau saran yang masuk.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>