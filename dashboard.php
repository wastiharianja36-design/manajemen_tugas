<?php

session_start();
if (!isset($_SESSION['notif_sudah_tampil'])) {
    $_SESSION['notif_sudah_tampil'] = false;
}

if(!isset($_SESSION['login'])){

    header("Location: index.php");

}

include 'koneksi.php';

$cari = "";

if(isset($_GET['cari'])){

    $cari = $_GET['cari'];

    $data = mysqli_query(
        $conn,
        "SELECT * FROM tugas 
        WHERE judul LIKE '%$cari%'
        OR matkul LIKE '%$cari%'"
    );

}else{

    $data = mysqli_query(
        $conn,
        "SELECT * FROM tugas"
    );

}

$total = mysqli_num_rows($data);

$belum = mysqli_query(
    $conn,
    "SELECT * FROM tugas WHERE status='Belum'"
);

$jumlah_belum = mysqli_num_rows($belum);

$selesai = mysqli_query(
    $conn,
    "SELECT * FROM tugas WHERE status='Selesai'"
);

$jumlah_selesai = mysqli_num_rows($selesai);

$terlambat = 0;
$besok = 0;

$query_terlambat = mysqli_query(
    $conn,
    "SELECT * FROM tugas"
);

while($t = mysqli_fetch_assoc($query_terlambat)){

   if(
    $t['deadline'] < date('Y-m-d')
    &&
    $t['status'] == "Belum"
){
    $terlambat++;
}

if(
    $t['deadline'] == date('Y-m-d', strtotime('+1 day'))
    &&
    $t['status'] == "Belum"
){
    $besok++;
}

}

?>

<!DOCTYPE html>
<html>
<head>

    <title>Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        body{
            font-family: 'Poppins', sans-serif;
            background: #c1ddff;
            margin: 0;
            display: flex;
        }

        .sidebar{
            width: 220px;
            height: 100vh;
            background: #f9c5d5;
            color: black;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2{
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a{
            display: block;
            color: navy;
            text-decoration: none;
            padding: 12px;
            margin-top: 10px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .sidebar a:hover{
            background: rgba(255,255,255,0.2);
        }

        .main{
            margin-left: 240px;
            width: calc(100% - 240px);
            padding: 20px;
        }

        .box{
            background: #d9a5f8;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .card-container{
            display: flex;
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .card{
            flex: 1;
            padding: 20px;
            border-radius: 12px;
            color: white;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }

        .total{
            background: #8ae4f6;
        }

        .belum-card{
            background: #eda98a;
        }

        .selesai-card{
            background: #4dd7a0;
        }

        .terlambat-card{
            background: #d23a3a;
        }

        .btn{
            padding: 12px 18px;
            background: #4b3f8f;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 10px;
        }

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            overflow: hidden;
            border-radius: 10px;
            background: white;
        }

        th{
            background: #99e1a9;
            color: #0d1a25;
        }

        table, th, td{
            border: 1px solid #e5e7eb;
        }

        th, td{
            padding: 14px;
            text-align: center;
        }

        .belum{
            background: #b48af7;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .selesai{
            background: #9ac7ee;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .terlambat{
            background: salmon;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .edit{
            background: #7aceef;
            color: navy;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
        }

        .hapus{
            background: #ffafaf;
            color: navy;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
        }
@media(max-width: 768px){

    .sidebar{
        width: 100%;
        height: auto;
        position: relative;
    }

    .content{
        margin-left: 0;
        padding: 15px;
    }

    .cards{
        flex-direction: column;
    }

    .card{
        width: 100%;
        margin-bottom: 15px;
    }

    table{
        font-size: 14px;
    }

    .btn{
        display: inline-block;
        margin-top: 10px;
    }

}
.credit{
    position: absolute;
    bottom: 20px;
    left: 0;
    width: 100%;

    text-align: center;
    color: navy;
    opacity: 0.7;
}

.credit p{
    font-size: 14px;
    margin: 0;
}

.credit h4{
    margin-top: 5px;
    font-size: 14px;
}
    </style>
    

</head>

<body>

<div class="sidebar">

    <h2>Manajemen Tugas</h2>

    <a href="dashboard.php">Dashboard</a>

    <a href="tambah.php">Tambah Tugas</a>
    <a href="logout.php" class="menu">Logout</a>
    <div class="credit">
    <p>Developed by</p>
    <h4>Wasti, Cetrin, Keysa, dan Shinta</h4>
    </div>
</div>

<div class="main">

    <div class="box">

        <h2>Dashboard Tugas</h2>

        <div class="card-container">

            <div class="card total">
                Total <br>
                <?= $total; ?>
            </div>

            <div class="card belum-card">
                Belum <br>
                <?= $jumlah_belum; ?>
            </div>

            <div class="card selesai-card">
                Selesai <br>
                <?= $jumlah_selesai; ?>
            </div>

            <div class="card terlambat-card">
                Terlambat <br>
                <?= $terlambat; ?>
            </div>

        </div>

        <a href="tambah.php" class="btn">
            + Tambah Tugas
        </a>
          <form method="GET">

    <input 
        type="text"
        name="cari"
        placeholder="Cari tugas..."
        value="<?= $cari; ?>"
    >

    <button type="submit" class="btn">
        Cari
    </button>
</form>

        <table>

            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Mata Kuliah</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php
            $no = 1;

            while($row = mysqli_fetch_assoc($data)){
            ?>

            <tr>

                <td><?= $no++; ?></td>

                <td><?= $row['judul']; ?></td>

                <td><?= $row['matkul']; ?></td>

                <td><?= $row['deadline']; ?></td>

                <td>

                <?php

                $status = $row['status'];

                if(
                    $row['deadline'] < date('Y-m-d')
                    &&
                    $status == "Belum"
                ){
                    $status = "Terlambat";
                }

                if($status == "Belum"){
                    echo "<span class='belum'>Belum</span>";
                }

                elseif($status == "Selesai"){
                    echo "<span class='selesai'>Selesai</span>";
                }

                else{
                    echo "<span class='terlambat'>Terlambat</span>";
                }

                ?>

                </td>

                <td>

                    <a class="edit" href="edit.php?id=<?= $row['id']; ?>">
                        Edit
                    </a>
                    <a 
                    class="hapus"
                    href="hapus.php?id=<?= $row['id']; ?>"
                    onclick="return konfirmasiHapus()"> 
                    Hapus
                </a>
                

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

<script src="script.js"></script>
<?php if ($_SESSION['notif_sudah_tampil'] == false) { ?>
<script>
    cekDeadline(<?= $terlambat; ?>);
    cekBesok(<?= $besok; ?>);
</script>
<?php
    $_SESSION['notif_sudah_tampil'] = true;
}
?>
</body>
</html>