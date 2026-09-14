<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header("Location: login_admin.php"); exit; }
$koneksi = mysqli_connect("localhost", "root", "", "db_kucingku");

// --- LOGIKA ---
if (isset($_POST['tambah_kategori'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['category_name']);
    if (!empty($nama)) { mysqli_query($koneksi, "INSERT INTO categories (category_name) VALUES ('$nama')"); header("Location: kelola_kategori.php"); exit; }
}

if (isset($_POST['update_kategori'])) {
    $id = intval($_POST['id_category']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['category_name']);
    mysqli_query($koneksi, "UPDATE categories SET category_name = '$nama' WHERE id_category = $id");
    header("Location: kelola_kategori.php"); exit;
}

if (isset($_GET['hapus'])) {
    mysqli_query($koneksi, "DELETE FROM categories WHERE id_category = '".intval($_GET['hapus'])."'");
    header("Location: kelola_kategori.php"); exit;
}

// Statistik
$stat_cat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as t FROM categories"))['t'];
$stat_prod = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as t FROM products"))['t'];

// Data
$search = $_GET['search'] ?? '';
$q = "SELECT c.*, COUNT(p.id_product) as total_produk 
      FROM categories c 
      LEFT JOIN products p ON c.id_category = p.id_category 
      WHERE c.category_name LIKE '%$search%' 
      GROUP BY c.id_category ORDER BY c.id_category DESC";
$query_kategori = mysqli_query($koneksi, $q);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori - MeowGudang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #FF9F43; --bg: #FFFDF5; --border: #333; }
        body { margin: 0; display: flex; min-height: 100vh; background: var(--bg); font-family: 'Outfit', sans-serif; }
        
        /* Sidebar Styling (Konsisten) */
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #ddd; padding: 30px; flex-shrink: 0; display: flex; flex-direction: column; }
        .logo { font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 40px; display: flex; align-items: center; gap: 10px; font-family: 'Outfit', sans-serif; }
        .menu-group { font-size: 11px; font-weight: 800; color: #aaa; margin: 25px 0 10px; letter-spacing: 1px; text-transform: uppercase; font-family: 'Outfit', sans-serif; }
        .sidebar a { display: flex; align-items: center; gap: 12px; padding: 12px 0; color: #777; text-decoration: none; font-weight: 600; font-family: 'Outfit', sans-serif; }
        .sidebar a.active { color: var(--primary); background: #FFF8E1; padding: 10px; border-radius: 10px; }
        .logout-btn { color: #FF6B6B; font-weight: bold; text-decoration: none; padding: 12px 0; margin-top: auto; display: block; }
        
        /* Konten */
        .main { flex-grow: 1; padding: 40px; font-family: 'Plus Jakarta Sans', sans-serif; }
        .stats-wrap { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { flex: 1; background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; }
        .stat-card h3 { font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 16px; margin: 0 0 10px; color: #555; }
        .content-wrap { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        .card { background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card h3 { font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 18px; margin-top: 0; }
        
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; margin-bottom: 10px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        .btn-meow { background: var(--primary); color: white; padding: 12px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; width: 100%; font-family: 'Outfit', sans-serif; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; color: #333; border-bottom: 1px solid #eee; font-family: 'Outfit', sans-serif; background-color: #fdfdfd; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .btn-act { padding: 5px 10px; border-radius: 6px; border: 1px solid #ddd; cursor: pointer; background: #fff; }

        /* Modal Edit */
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); backdrop-filter: blur(3px); justify-content:center; align-items:center; z-index:9999; }
        .modal-content { background: #fff; padding: 30px; border-radius: 20px; width: 400px; border: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">🐾 MeowGudang</div>
        
        <div class="menu-group">MAIN MENU</div>
        <a href="dashboard_meow.php">🐱 Dashboard</a>
        
        <div class="menu-group">PROFIL</div>
        <a href="kelola_user.php">👤 Profil & Staf</a>
        
        <div class="menu-group">PERSEDIAAN</div>
        <a href="tambah_stock.php">🔄 Stok Masuk/Keluar</a>
        
        <div class="menu-group">MASTER DATA</div>
        <a href="kelola_barang.php">📦 Daftar Barang</a>
        <a href="kelola_kategori.php" class="active">🏷️ Kategori Barang</a>
        
        <div class="menu-group">LAPORAN</div>
        <a href="laporan_stok.php">📊 Laporan Bulanan</a>

        <a href="logout.php" class="logout-btn">🚪 Keluar</a>
    </div>

    <div class="main">
        <div class="stats-wrap">
            <div class="stat-card"><h3>Total Kategori</h3><h2 style="color:var(--primary); margin:0;"><?=$stat_cat?></h2></div>
            <div class="stat-card"><h3>Total Produk</h3><h2 style="color:var(--primary); margin:0;"><?=$stat_prod?></h2></div>
        </div>

        <div class="content-wrap">
            <div class="card">
                <h3>Tambah Kategori</h3>
                <form method="POST">
                    <input type="text" name="category_name" placeholder="Nama Kategori..." required>
                    <button type="submit" name="tambah_kategori" class="btn-meow">Simpan Data</button>
                </form>
            </div>

            <div class="card">
                <h3>Daftar Kategori</h3>
                <form method="GET" style="display:flex; gap:10px;">
                    <input type="text" name="search" placeholder="Cari kategori..." value="<?=$search?>">
                    <button type="submit" class="btn-meow" style="width:100px;">Cari</button>
                </form>
                <table>
                    <thead><tr><th>ID</th><th>Nama Kategori</th><th>Jumlah Produk</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php while ($r = mysqli_fetch_assoc($query_kategori)) : ?>
                        <tr>
                            <td>#<?=$r['id_category']?></td>
                            <td><b><?=$r['category_name']?></b></td>
                            <td><?=$r['total_produk']?> Item</td>
                            <td>
                                <button class="btn-act" onclick="bukaEdit(<?=$r['id_category']?>,'<?=$r['category_name']?>')">✏️</button>
                                <a href="?hapus=<?=$r['id_category']?>" onclick="return confirm('Hapus?')"><button class="btn-act">🗑️</button></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <h3 style="margin-top:0; font-family:'Outfit', sans-serif;">Edit Kategori ✏️</h3>
            <form method="POST">
                <input type="hidden" name="id_category" id="edit_id">
                <input type="text" name="category_name" id="edit_name" placeholder="Nama Kategori..." required>
                <button type="submit" name="update_kategori" class="btn-meow">Update Data</button>
            </form>
        </div>
    </div>

    <script>
        function bukaEdit(id, name) {
            document.getElementById('modalEdit').style.display = 'flex';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
        }

        window.onclick = function(e) {
            if (e.target.className == 'modal') {
                e.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>