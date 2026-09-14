<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header("Location: login_admin.php"); exit; }
$koneksi = mysqli_connect("localhost", "root", "", "db_kucingku");

// Filter Bulan & Tahun
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Query Data Laporan (Log Transaksi pada bulan/tahun tersebut)
$q = "SELECT s.*, p.product_name, c.category_name 
      FROM stock_logs s 
      JOIN products p ON s.id_product = p.id_product 
      LEFT JOIN categories c ON p.id_category = c.id_category
      WHERE MONTH(s.created_at) = '$bulan' AND YEAR(s.created_at) = '$tahun'
      ORDER BY s.created_at DESC";
$query_laporan = mysqli_query($koneksi, $q);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan - MeowGudang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #FF9F43; --bg: #FFFDF5; }
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
        .card { background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card h3 { font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 20px; margin-top: 0; color: #333; }

        .filter-wrap { display: flex; gap: 15px; margin-bottom: 25px; background: #FFFDF5; padding: 15px; border-radius: 15px; border: 1px dashed #ffd394; }
        select, .btn-meow { padding: 12px 20px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; outline: none; }
        select { background: #fff; }
        .btn-meow { background: var(--primary); color: white; border: none; cursor: pointer; font-family: 'Outfit', sans-serif; }
        .btn-meow:hover { opacity: 0.9; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 15px; color: #333; border-bottom: 2px solid #eee; font-family: 'Outfit', sans-serif; background-color: #fdfdfd; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .badge-in { background: #e1fced; color: #2ed573; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; }
        .badge-out { background: #ffebee; color: #ff6b6b; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; }
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
        <a href="kelola_kategori.php">🏷️ Kategori Barang</a>
        
        <div class="menu-group">LAPORAN</div>
        <a href="laporan_stok.php" class="active">📊 Laporan Bulanan</a>

        <a href="logout.php" class="logout-btn">🚪 Keluar</a>
    </div>

    <div class="main">
        <div class="card">
            <h3>📊 Laporan Mutasi Stok Bulanan</h3>
            
            <form method="GET" class="filter-wrap">
                <select name="bulan">
                    <?php
                    $list_bulan = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    foreach ($list_bulan as $b_val => $b_nama) {
                        $selected = ($b_val == $bulan) ? 'selected' : '';
                        echo "<option value='$b_val' $selected>$b_nama</option>";
                    }
                    ?>
                </select>
                <select name="tahun">
                    <?php
                    $tahun_ini = date('Y');
                    for ($t = $tahun_ini; $t >= $tahun_ini - 5; $t--) {
                        $selected = ($t == $tahun) ? 'selected' : '';
                        echo "<option value='$t' $selected>$t</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn-meow">Tampilkan Laporan</button>
                <button type="button" class="btn-meow" style="background:#777; margin-left:auto;" onclick="window.print()">Print / PDF</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query_laporan) > 0): ?>
                        <?php while ($r = mysqli_fetch_assoc($query_laporan)) : ?>
                        <tr>
                            <td><?=date('d M Y H:i', strtotime($r['created_at']))?></td>
                            <td><b><?=$r['product_name']?></b></td>
                            <td><?=$r['category_name'] ?? '-'?></td>
                            <td>
                                <span class="<?=$r['type'] == 'in' ? 'badge-in' : 'badge-out'?>">
                                    <?=$r['type'] == 'in' ? 'MASUK' : 'KELUAR'?>
                                </span>
                            </td>
                            <td><?=$r['quantity']?> pcs</td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:40px; color:#888;">Tidak ada mutasi stok pada periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>