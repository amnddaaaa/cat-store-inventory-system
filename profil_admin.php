<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { 
    header("Location: login_admin.php"); 
    exit; 
}
$koneksi = mysqli_connect("localhost", "root", "", "db_kucingku");

// Mengambil ID admin yang sedang login dari session
$admin_id = $_SESSION['id_admin'] ?? 1; 

// --- PROSES UPDATE PROFIL ---
if (isset($_POST['update_profil'])) {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email        = mysqli_real_escape_string($koneksi, $_POST['email']);
    
    // Update data profil dasar
    mysqli_query($koneksi, "UPDATE admin SET nama_lengkap='$nama_lengkap', username='$username', email='$email' WHERE id_admin='$admin_id'");
    
    // Perbarui session agar langsung terlihat di sidebar/halaman lain
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_nama'] = $nama_lengkap;

    // Update password jika diisi
    if (!empty($_POST['password'])) {
        $password_baru = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE admin SET password='$password_baru' WHERE id_admin='$admin_id'");
    }

    header("Location: profil_admin.php?sukses=1");
    exit;
}

// Ambil data admin yang sedang login saat ini
$query_admin = mysqli_query($koneksi, "SELECT * FROM admin WHERE id_admin = '$admin_id'");
$data_admin  = mysqli_fetch_assoc($query_admin);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Profil - MeowGudang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #FF9F43; --bg: #FFFDF5; --text: #2D3436; }
        body { margin: 0; display: flex; min-height: 100vh; background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #ddd; padding: 30px; flex-shrink: 0; display: flex; flex-direction: column; }
        .logo { font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 30px; display: flex; align-items: center; gap: 10px; font-family: 'Outfit', sans-serif; }
        .menu-group { font-size: 11px; font-weight: 800; color: #aaa; margin: 25px 0 10px; letter-spacing: 1px; text-transform: uppercase; }
        .sidebar a { display: flex; align-items: center; gap: 12px; padding: 12px 0; color: #777; text-decoration: none; font-weight: 600; font-family: 'Outfit', sans-serif; }
        .sidebar a.active { color: var(--primary); background: #FFF8E1; padding: 10px; border-radius: 10px; }
        .logout-btn { margin-top: auto; border-top: 1px solid #eee; padding-top: 20px; color: #d63031; }
        
        .main { flex-grow: 1; padding: 40px; }
        h1, h2, h3, label { font-family: 'Outfit', sans-serif; color: var(--text); }
        
        .card { background: #fff; padding: 30px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.03); max-width: 600px; margin-top: 20px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; margin-top: 5px; margin-bottom: 15px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        input:focus { outline: none; border-color: var(--primary); }
        button { background: var(--primary); color: white; border: none; padding: 14px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; font-family: 'Outfit', sans-serif; font-size: 15px; }
        button:hover { opacity: 0.9; }
        .alert { background: #E8F8F5; color: #00b894; padding: 15px; border-radius: 10px; border: 1px solid #00b894; margin-top: 20px; max-width: 600px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">🐾 MeowGudang</div>
        
        <div style="background: #fff8e1; padding: 12px; border-radius: 10px; border: 2px solid #FF9F43; font-size: 13px; margin-bottom: 10px;">
            <strong>Login:</strong> <span style="color:var(--primary); font-weight:bold;"><?= $_SESSION['admin_username'] ?? 'Admin'; ?></span><br>
            <span style="color: #2ed573;">● Online</span>
        </div>

        <div class="menu-group">MANAJEMEN AKUN</div>
        <a href="profil_admin.php" class="active">👤 Pengaturan Profil</a>
        
        <div class="menu-group">MAIN MENU</div>
        <a href="dashboard_meow.php">🐱 Dashboard</a>
        
        <div class="menu-group">PERSEDIAAN</div>
        <a href="input_stok.php">🔄 Stok Masuk/Keluar</a>
        
        <div class="menu-group">MASTER DATA</div>
        <a href="kelola_barang.php">📦 Daftar Barang</a>
        <a href="kelola_kategori.php">🏷️ Kategori Barang</a>
        
        <a href="logout_admin.php" class="logout-btn">🚪 Keluar</a>
    </div>

    <div class="main">
        <div>
            <h1 style="margin:0;">Pengaturan Profil ⚙️</h1>
            <p style="color:#636e72; margin-top:5px;">Kelola informasi akun dan keamanan Anda di sini.</p>
        </div>

        <div class="card">
            <form method="POST">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data_admin['nama_lengkap'] ?? '') ?>" required>

                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($data_admin['username'] ?? '') ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($data_admin['email'] ?? '') ?>" required>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                
                <h3 style="color: #666; margin-bottom: 5px; font-size: 16px;">Ubah Kata Sandi</h3>
                <p style="font-size: 12px; color: #999; margin-top:0; margin-bottom:15px;">Abaikan jika tidak ingin mengubah kata sandi.</p>
                
                <label>Password Baru</label>
                <input type="password" name="password" placeholder="••••••••">

                <button type="submit" name="update_profil">Simpan Perubahan</button>
            </form>
        </div>

        <?php if(isset($_GET['sukses'])): ?>
            <div class="alert">✅ Profil berhasil diperbarui!</div>
        <?php endif; ?>
    </div>
</body>
</html>