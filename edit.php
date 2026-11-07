<?php
include 'koneksi.php';

$id = $_GET['id'];

$data = mysqli_query(
    $conn,
    "SELECT * FROM tugas WHERE id='$id'"
);

$row = mysqli_fetch_assoc($data);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Tugas</title>

    <style>

        body{
            font-family: Arial;
            background: #f2f2f2;
            padding: 20px;
        }

        .box{
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            margin: auto;
        }

        input, select{
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        button{
            padding: 10px 15px;
            background: green;
            color: white;
            border: none;
            border-radius: 5px;
        }

    </style>

</head>
<body>

<div class="box">

    <h2>Edit Tugas</h2>

    <form action="update.php" method="POST">

        <input type="hidden" name="id" value="<?= $row['id']; ?>">

        <label>Judul Tugas</label>
        <input
            type="text"
            name="judul"
            value="<?= $row['judul']; ?>"
            required
        >

        <label>Mata Kuliah</label>
        <input
            type="text"
            name="matkul"
            value="<?= $row['matkul']; ?>"
            required
        >

        <label>Deadline</label>
        <input
            type="date"
            name="deadline"
            value="<?= $row['deadline']; ?>"
            required
        >

        <label>Status</label>

        <select name="status">

            <option value="Belum"
            <?php
            if($row['status']=="Belum"){
                echo "selected";
            }
            ?>
            >
            Belum
            </option>

            <option value="Selesai"
            <?php
            if($row['status']=="Selesai"){
                echo "selected";
            }
            ?>
            >
            Selesai
            </option>

        </select>

        <button type="submit">
            Update
        </button>

    </form>

</div>

</body>
</html>