<?php
include 'koneksi.php';

$pesan = "";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Enkripsi password demi keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah username sudah pernah terdaftar
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan = "Username sudah digunakan, cari username lain!";
    } else {
        // Simpan data pengguna baru ke database
        $query = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location='index.php';</script>";
        } else {
            $pesan = "Gagal mendaftar, coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun Baru</title>
</head>
<body>
    <h2>Registrasi Akun Baru</h2>
    
    <?php if($pesan != ""): ?>
        <p style="color: red;"><?php echo $pesan; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit" name="register">Daftar</button>
    </form>

    <p>Sudah punya akun? <a href="index.php">Login di sini</a></p>
</body>
</html>