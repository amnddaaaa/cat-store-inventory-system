<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header("Location: login_admin.php"); exit; }
$koneksi = mysqli_connect("localhost", "root", "", "db_kucingku");

$filter = $_GET['filter'] ?? 'all';
$date_clause = ($filter == 'today') ? "AND DATE(created_at) = CURDATE()" : "";

// Data Statistik
$stats = [
    'total_stok' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(stock_quantity) as t FROM inventory"))['t'] ?? 0,
    'kritis'     => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as t FROM inventory WHERE stock_quantity <= 3"))['t'] ?? 0,
    'in'         => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(quantity) as t FROM stock_logs WHERE type='in' $date_clause"))['t'] ?? 0,
    'out'        => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(quantity) as t FROM stock_logs WHERE type='out' $date_clause"))['t'] ?? 0
];

// Data Grafik & Log
$query_grafik = mysqli_query($koneksi, "SELECT p.product_name, i.stock_quantity FROM inventory i JOIN products p ON i.id_product = p.id_product");
$labels = []; $data = [];
while ($row = mysqli_fetch_assoc($query_grafik)) { $labels[] = $row['product_name']; $data[] = $row['stock_quantity']; }

$query_log = mysqli_query($koneksi, "SELECT p.product_name, s.quantity, s.type, s.created_at FROM stock_logs s JOIN products p ON s.id_product = p.id_product ORDER BY s.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - MeowGudang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #FF9F43; --bg: #FFFDF5; --text: #2D3436; }
        * { box-sizing: border-box; }
        body { margin: 0; display: flex; min-height: 100vh; background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #ddd; padding: 30px; flex-shrink: 0; }
        .logo { font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 40px; display: flex; align-items: center; gap: 10px; font-family: 'Outfit', sans-serif; }
        .menu-group { font-size: 11px; font-weight: 800; color: #aaa; margin: 25px 0 10px; letter-spacing: 1px; text-transform: uppercase; font-family: 'Outfit', sans-serif; }
        .sidebar a { display: flex; align-items: center; gap: 12px; padding: 12px 0; color: #777; text-decoration: none; font-weight: 600; font-family: 'Outfit', sans-serif; }
        .sidebar a.active { color: var(--primary); background: #FFF8E1; padding: 10px; border-radius: 10px; }
        .logout-btn { color: #FF6B6B; font-weight: bold; text-decoration: none; padding: 12px 0; margin-top: auto; display: block; }
        
        .main { flex-grow: 1; padding: 40px; }
        h1, h2, h3 { font-family: 'Outfit', sans-serif; color: var(--text); }
        
        .stats-wrap { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .card { background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        
        .btn-filter { padding: 8px 15px; border: 1px solid #ddd; border-radius: 10px; text-decoration: none; color: #777; font-weight: 600; font-size: 13px; }
        .btn-filter.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">🐾 MeowGudang</div>
        <div class="menu-group">MAIN MENU</div>
        <a href="dashboard_meow.php" class="active">🐱 Dashboard</a>
        
        <div class="menu-group">PROFIL</div>
        <a href="kelola_user.php">👤 Profil & Staf</a>
        
        <div class="menu-group">PERSEDIAAN</div>
        <a href="tambah_stock.php">🔄 Stok Masuk/Keluar</a>
        
        <div class="menu-group">MASTER DATA</div>
        <a href="kelola_barang.php">📦 Daftar Barang</a>
        <a href="kelola_kategori.php">🏷️ Kategori Barang</a>
        
        <div class="menu-group">LAPORAN</div>
        <a href="laporan_stok.php">📊 Laporan Bulanan</a>
        
        <a href="logout.php" class="logout-btn">🚪 Keluar</a>
    </div>

    <div class="main">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:30px;">
            <div>
                <h1 style="margin:0;">Halo, Meow Admin! 👋</h1>
                <p style="color:#636e72; margin-top:5px;">Berikut adalah rangkuman performa gudang.</p>
            </div>
            <div>
                <a href="?filter=all" class="btn-filter <?= $filter == 'all' ? 'active' : '' ?>">Semua</a>
                <a href="?filter=today" class="btn-filter <?= $filter == 'today' ? 'active' : '' ?>">Hari Ini</a>
            </div>
        </div>

        <div class="stats-wrap">
            <div class="stat-card"><h3>Total Stok</h3><h2 style="color:var(--primary); margin:0;"><?=$stats['total_stok']?></h2><small style="color:#aaa;">Stok saat ini</small></div>
            <div class="stat-card"><h3>Stok Masuk</h3><h2 style="color:#00b894; margin:0;"><?=$stats['in']?></h2><small style="color:#aaa;"><?= $filter == 'today' ? 'Hari ini' : 'Total semua' ?></small></div>
            <div class="stat-card"><h3>Stok Keluar</h3><h2 style="color:#d63031; margin:0;"><?=$stats['out']?></h2><small style="color:#aaa;"><?= $filter == 'today' ? 'Hari ini' : 'Total semua' ?></small></div>
            <div class="stat-card"><h3>Produk Kritis</h3><h2 style="color:#fdcb6e; margin:0;"><?=$stats['kritis']?></h2><small style="color:#aaa;">Perlu Restock</small></div>
        </div>

        <div class="content-grid">
            <div class="card">
                <h3>Visualisasi Stok Barang</h3>
                <canvas id="stokChart" height="120"></canvas>
            </div>
            <div class="card">
                <h3>🕒 Log Terbaru</h3>
                <?php while($log = mysqli_fetch_assoc($query_log)): ?>
                    <div style="padding:12px 0; border-bottom:1px solid #f9f9f9; display:flex; justify-content:space-between;">
                        <div>
                            <div style="font-weight:600; font-size:14px;"><?= $log['product_name'] ?></div>
                            <small style="color:#b2bec3;"><?= date('H:i', strtotime($log['created_at'])) ?></small>
                        </div>
                        <div style="font-weight:800; color:<?= $log['type'] == 'in' ? '#00b894' : '#d63031' ?>">
                            <?= $log['type'] == 'in' ? '+' : '-' ?><?= $log['quantity'] ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('stokChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Jumlah Stok',
                    data: <?= json_encode($data) ?>,
                    backgroundColor: '#FF9F43',
                    borderRadius: 10
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>