<?php
session_start();
$koneksi = mysqli_connect("localhost", "root", "", "db_kucingku");

$error = "";

if (isset($_POST['submit_login'])) {
    $username_email = mysqli_real_escape_string($koneksi, $_POST['username_email']);
    $password = $_POST['password'];

    // 1. Validasi: Apakah input kosong?
    if (empty($username_email) || empty($password)) {
        $error = "Mohon isi semua kolom, Meow! 🐾";
    } else {
        // 2. Query mencari user
        $query = "SELECT * FROM users WHERE username='$username_email' OR email='$username_email'";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);

            // 3. Verifikasi Password
            if (password_verify($password, $row['password']) || $password === $row['password']) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $row['username'];
                $_SESSION['id_user'] = $row['id_user'];
                $_SESSION['role'] = $row['role'];
                
                header("Location: dashboard_meow.php");
                exit;
            } else {
                $error = "Password salah, Meow! 😾";
            }
        } else {
            $error = "Akun tidak ditemukan! 🙀";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - MeowGudang</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #FFDE88; font-family: 'Outfit', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container { background-color: #ffffff; width: 380px; padding: 40px; border-radius: 40px; border: 4px solid #333; box-shadow: 12px 12px 0px #333; text-align: center; }
        h2 { font-family: 'Fredoka One', cursive; font-size: 32px; color: #333; margin-bottom: 20px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { font-family: 'Fredoka One', cursive; font-size: 13px; color: #333; margin-bottom: 8px; display: block; }
        .form-group input { width: 100%; background-color: #FFFDF5; border: 3px solid #333; padding: 16px; border-radius: 15px; font-weight: 700; outline: none; }
        .btn-login { width: 100%; background-color: #FF9F43; color: #333; border: 3px solid #333; padding: 18px; border-radius: 15px; font-family: 'Fredoka One', cursive; font-size: 18px; cursor: pointer; box-shadow: 5px 5px 0px #333; transition: 0.2s; }
        .btn-login:hover { transform: translate(-2px, -2px); box-shadow: 7px 7px 0px #333; }
        .alert { background: #FF6B6B; color: white; padding: 10px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🐾 MeowGudang Admin</h2>
        <?php if (!empty($error)) echo "<div class='alert'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group"><label>USERNAME / EMAIL</label><input type="text" name="username_email"></div>
            <div class="form-group"><label>PASSWORD</label><input type="password" name="password"></div>
            <button type="submit" name="submit_login" class="btn-login">Masuk Sekarang 🐈</button>
        </form>
    </div>
</body>
</html>