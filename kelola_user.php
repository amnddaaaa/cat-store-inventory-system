<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { 
    header("Location: login_admin.php"); 
    exit; 
}
$koneksi = mysqli_connect("localhost", "root", "", "db_kucingku");

// Mengambil ID user/admin dari session
$id_admin = $_SESSION['id_user'] ?? $_SESSION['id_admin'] ?? 1;

// Logika Ubah Password Profil Sendiri
if (isset($_POST['update_pass'])) {
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE users SET password='$new_pass' WHERE id_user=$id_admin");
    $pesan = "✅ Password berhasil diperbarui!";
}

// Logika Hapus Staf
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    if ($id_hapus != $id_admin) {
        mysqli_query($koneksi, "DELETE FROM users WHERE id_user = '$id_hapus'");
    }
    header("Location: kelola_user.php");
    exit;
}

// Ambil Data Staf / Pengguna untuk ditampilkan
$query_users = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id_user DESC");
$total_users = mysqli_num_rows($query_users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil & Staf - MeowGudang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #FF9F43; --bg: #FFFDF5; --text: #2D3436; --border: #eee; }
        * { box-sizing: border-box; }
        body { margin: 0; display: flex; min-height: 100vh; background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text); }
        
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #ddd; padding: 30px; flex-shrink: 0; }
        .logo { font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 40px; display: flex; align-items: center; gap: 10px; font-family: 'Outfit', sans-serif; }
        .menu-group { font-size: 11px; font-weight: 800; color: #aaa; margin: 25px 0 10px; letter-spacing: 1px; text-transform: uppercase; font-family: 'Outfit', sans-serif; }
        .sidebar a { display: flex; align-items: center; gap: 12px; padding: 12px 0; color: #777; text-decoration: none; font-weight: 600; font-family: 'Outfit', sans-serif; }
        .sidebar a.active { color: var(--primary); background: #FFF8E1; padding: 10px; border-radius: 10px; }
        
        /* Konten dibuat Flexbox dan terpusat secara presisi */
        .main { flex-grow: 1; padding: 40px; display: flex; flex-direction: column; gap: 30px; box-sizing: border-box; align-items: center; }
        .header-wraper { width: 100%; max-width: 650px; }
        h1, h2, h3 { font-family: 'Outfit', sans-serif; }
        
        .card { background: #fff; padding: 30px; border-radius: 20px; border: 1px solid var(--border); box-shadow: 0 4px 15px rgba(0,0,0,0.03); width: 100%; max-width: 650px; box-sizing: border-box; }
        
        .cat-deco { font-size: 40px; text-align: center; margin-bottom: 10px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; margin-top: 5px; margin-bottom: 15px; font-family: 'Plus Jakarta Sans', sans-serif; }
        input:focus { outline: none; border-color: var(--primary); }
        button { background: var(--primary); color: white; border: none; padding: 14px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; font-family: 'Outfit', sans-serif; font-size: 15px; }
        button:hover { opacity: 0.9; }
        
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
        .modal-content { background: #fff; padding: 30px; border-radius: 20px; border: 1px solid #ddd; width: 400px; text-align: center; }

        .tabel-staf { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .tabel-staf th { background: #fdfaf0; padding: 10px; text-align: left; border-bottom: 2px solid #ddd; font-family: 'Outfit', sans-serif; }
        .tabel-staf td { padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        .btn-hapus { background: #d63031; color: white; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block; font-family: 'Outfit', sans-serif; }
        .btn-hapus:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">🐾 MeowGudang</div>
        <div class="menu-group">MAIN MENU</div>
        <a href="dashboard_meow.php">🐱 Dashboard</a>
        <div class="menu-group">PROFIL</div>
        <a href="kelola_user.php" class="active">👤 Profil & Staf</a>
        <div class="menu-group">PERSEDIAAN</div>
        <a href="tambah_stock.php">🔄 Stok Masuk/Keluar</a>
        <div class="menu-group">MASTER DATA</div>
        <a href="kelola_barang.php">📦 Daftar Barang</a>
        <a href="kelola_kategori.php">🏷️ Kategori Barang</a>
        <div class="menu-group">LAPORAN</div>
        <a href="laporan_stok.php">📊 Laporan Bulanan</a>
    </div>

    <div class="main">
        <div class="header-wraper">
            <h1 style="margin:0;">Manajemen Profil & Staf 👤</h1>
            <p style="color:#636e72; margin-top:5px;">Kelola kata sandi akun Anda dan daftar staf gudang.</p>
        </div>

        <div class="card">
            <div class="cat-deco">🐱</div>
            <h2 style="margin-top:0; text-align:center; font-family:'Outfit', sans-serif;">Profil Saya</h2>
            <?php if(isset($pesan)): ?>
                <div style="background:#E8F8F5; color:#00b894; padding:10px; border-radius:10px; text-align:center; margin-bottom:15px; font-weight:600;">
                    <?=$pesan?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <label>Username</label>
                <input type="text" value="<?= $_SESSION['admin_username'] ?? 'Admin' ?>" disabled style="background:#f9f9f9; color:#777;">
                
                <label>Password Baru</label>
                <input type="password" name="password" placeholder="Masukkan password baru" required>
                
                <button type="submit" name="update_pass">Simpan Perubahan</button>
            </form>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <h3 style="margin:0; font-size:18px;">👥 Daftar Staf (<?=$total_users?> Akun)</h3>
            </div>
            <p style="color:#636e72; font-size:13px; margin-top:0;">Berikut adalah daftar profil/staf yang terdaftar dalam sistem.</p>
            
            <table class="tabel-staf">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($usr = mysqli_fetch_assoc($query_users)): 
                    ?>
                    <tr>
                        <td><?=$no++?></td>
                        <td><?=htmlspecialchars($usr['username'])?></td>
                        <td>
                            <?php if($usr['id_user'] != $id_admin): ?>
                                <a href="?hapus=<?=$usr['id_user']?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus staf ini?')">Hapus</a>
                            <?php else: ?>
                                <span style="color:#aaa; font-size:12px; font-style:italic;">Sedang Aktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; if($total_users == 0): ?>
                        <tr><td colspan="3" style="text-align:center; color:#999;">Belum ada staf terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="border-style: dashed; border-color: var(--primary);">
            <div class="cat-deco">😽</div>
            <h2 style="margin-top:0; text-align:center; font-family:'Outfit', sans-serif;">Tambah Staf</h2>
            <p style="text-align:center; color:#636e72; margin-bottom:20px;">Tambah anggota tim baru untuk membantu operasional gudang.</p>
            <button onclick="bukaModal()" style="background:var(--text);">+ Tambah Staf Baru</button>
        </div>
    </div>

    <div id="modalUser" class="modal">
        <div class="modal-content">
            <h2 style="font-family:'Outfit', sans-serif; color:var(--primary); margin-bottom:20px;">Halo Staf Baru! 😺</h2>
            <form method="POST" action="proses_tambah_user.php">
                <input type="text" name="username" placeholder="Username staf" required>
                <input type="password" name="password" placeholder="Password sementara" required>
                <button type="submit" style="margin-top:10px;">Meow! Simpan Data</button>
                <button type="button" onclick="tutupModal()" style="background:transparent; border:none; color:#999; margin-top:15px;">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function bukaModal() { document.getElementById('modalUser').style.display = 'flex'; }
        function tutupModal() { document.getElementById('modalUser').style.display = 'none'; }
    </script>
</body>
</html>