<!DOCTYPE html>
<html>
<head>
    <title>Tambah Tugas</title>

    <style>

        body{
            font-family: Arial;
            background: #f2f2f2;
            padding: 20px;
        }

        .box{
            width: 500px;
            background: white;
            padding: 20px;
            margin: auto;
            border-radius: 10px;
        }

        input,
        textarea,
        select{
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }

        button{
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background: #3c3a76;
            color: white;
            border: none;
        }

    </style>

</head>
<body>

<div class="box">

    <h2>Tambah Tugas</h2>

    <form action="simpan.php" method="POST">

        <input
            type="text"
            name="judul"
            placeholder="Judul Tugas"
            required
        >

        <input
            type="text"
            name="matkul"
            placeholder="Mata Kuliah"
            required
        >

        <input
            type="date"
            name="deadline"
            required
        >

        <textarea
            name="deskripsi"
            placeholder="Deskripsi"
        ></textarea>

        <select name="status">

            <option value="Belum">
                Belum
            </option>

            <option value="Selesai">
                Selesai
            </option>

        </select>

        <button type="submit">
            Simpan
        </button>

    </form>

</div>

</body>
</html>