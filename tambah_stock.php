<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header("Location: login_admin.php"); exit; }
$koneksi = mysqli_connect("localhost", "root", "", "db_kucingku");

$pesan = "";
if (isset($_POST['submit'])) {
    $id_produk    = (int)$_POST['id_product'];
    $jumlah      = (int)$_POST['quantity'];
    $tipe        = $_POST['type'];
    $catatan     = mysqli_real_escape_string($koneksi, $_POST['notes']);

    mysqli_begin_transaction($koneksi);
    try {
        $op = ($tipe == 'in') ? "+" : "-";
        mysqli_query($koneksi, "UPDATE inventory SET stock_quantity = stock_quantity $op $jumlah WHERE id_product = $id_produk");
        mysqli_query($koneksi, "INSERT INTO stock_logs (id_product, type, quantity, notes, created_at) VALUES ($id_produk, '$tipe', $jumlah, '$catatan', NOW())");
        mysqli_commit($koneksi);
        $pesan = "success";
    } catch (Exception $e) { mysqli_rollback($koneksi); $pesan = "error"; }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Stok - MeowGudang</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #FF9F43; --bg: #FFFDF5; --border: #333; }
        body { margin: 0; display: flex; min-height: 100vh; background: var(--bg); font-family: 'Outfit', sans-serif; }
        
        /* Sidebar Styling (Konsisten) */
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #ddd; padding: 30px; flex-shrink: 0; display: flex; flex-direction: column; }
        .logo { font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .menu-group { font-size: 12px; font-weight: 800; color: #aaa; margin: 25px 0 10px; letter-spacing: 0.5px; }
        .sidebar a { display: flex; align-items: center; gap: 12px; padding: 12px 0; color: #777; text-decoration: none; font-weight: 600; }
        .sidebar a.active { color: var(--primary); background: #FFF8E1; padding: 10px; border-radius: 10px; }
        .logout-btn { color: #FF6B6B; font-weight: bold; text-decoration: none; padding: 12px 0; margin-top: auto; display: block; }
        
        /* Layout Utama */
        .main { flex-grow: 1; display: flex; flex-direction: column; justify-content: flex-start; padding: 40px; overflow-y: auto; }
        .card { background: #fff; padding: 30px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; }
        
        /* Form & Input */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .input-group { margin-bottom: 20px; }
        label { font-weight: 600; margin-bottom: 8px; display: block; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; font-family: 'Outfit'; }
        
        .btn-submit { background: var(--primary); color: white; padding: 15px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; width: 100%; font-size: 16px; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; text-align: center; }
        
        /* Indikator Status */
        .status-badge { display: inline-block; padding: 6px 12px; border-radius: 8px; font-weight: 800; font-size: 13px; }
        .status-tersedia { background: #dff9fb; color: #27ae60; }
        .status-warning { background: #ffeaa7; color: #d35400; }
        .status-kosong { background: #ff7675; color: white; }

        /* Tabel Data Barang */
        .table-responsive { width: 100%; overflow-x: auto; }
        .stock-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .stock-table th, .stock-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        .stock-table th { background-color: #FFF9E6; font-weight: 600; color: #333; }
        .stock-table tr:hover { background-color: #fafafa; }

        /* Tombol Pop-up */
        .btn-open-modal { background: var(--primary); color: white; padding: 12px 24px; border: none; border-radius: 15px; font-weight: 600; cursor: pointer; font-size: 16px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(255,159,67,0.2); }
        
        /* Pop-up Modal Styling */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(3px); justify-content: center; align-items: center; }
        .modal-content { background-color: #fff; padding: 30px; border-radius: 20px; border: 1px solid #eee; width: 90%; max-width: 500px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        .close-modal { position: absolute; top: 20px; right: 25px; font-size: 28px; font-weight: 800; color: #aaa; cursor: pointer; }
        .close-modal:hover { color: #333; }
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
        <a href="tambah_stock.php" class="active">🔄 Stok Masuk/Keluar</a>
        
        <div class="menu-group">MASTER DATA</div>
        <a href="kelola_barang.php">📦 Daftar Barang</a>
        <a href="kelola_kategori.php">🏷️ Kategori Barang</a>
        
        <div class="menu-group">LAPORAN</div>
        <a href="laporan_stok.php">📊 Laporan Bulanan</a>

        <a href="logout.php" class="logout-btn">🚪 Keluar</a>
    </div>

    <div class="main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; width: 100%;">
            <h1 style="margin: 0; font-size: 24px; font-weight: 800;">Halaman Kelola Stok</h1>
            <button class="btn-open-modal" id="openModalBtn">➕ Update Inventaris</button>
        </div>

        <div class="modal" id="updateModal" <?php if($pesan == 'success' || $pesan == 'error') echo "style='display: flex;'"; ?>>
            <div class="modal-content">
                <span class="close-modal" id="closeModalBtn">&times;</span>
                <h2 style="margin-top:0; margin-bottom: 20px;">🔄 Update Inventaris</h2>
                
                <?php if($pesan == 'success') echo "<div class='alert' style='background:#dff9fb; color:#27ae60;'>✅ Transaksi Berhasil!</div>"; ?>
                <?php if($pesan == 'error') echo "<div class='alert' style='background:#ff7675; color:white;'>❌ Terjadi kesalahan!</div>"; ?>

                <form method="POST">
                    <div class="input-group">
                        <label>Barang</label>
                        <select name="id_product" id="productSelect" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php 
                            $res = mysqli_query($koneksi, "SELECT id_product, product_name FROM products");
                            while($p = mysqli_fetch_assoc($res)) echo "<option value='{$p['id_product']}'>{$p['product_name']}</option>";
                            ?>
                        </select>
                    </div>

                    <div class="input-group" id="stockStatusGroup" style="display: none;">
                        <label>Status Persediaan Saat Ini</label>
                        <div id="stockStatusDisplay"></div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>Jumlah</label>
                            <input type="number" name="quantity" required min="1" placeholder="0">
                        </div>
                        <div class="input-group">
                            <label>Tipe</label>
                            <select name="type">
                                <option value="in">📥 Masuk</option>
                                <option value="out">📤 Keluar</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Catatan</label>
                        <textarea name="notes" placeholder="Keterangan transaksi..." rows="2"></textarea>
                    </div>
                    
                    <button type="submit" name="submit" class="btn-submit">Simpan Transaksi</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-top:0; font-size: 18px;">📦 Daftar Status Persediaan Barang</h2>
            <div class="table-responsive">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Barang</th>
                            <th style="width: 15%;">Stok</th>
                            <th style="width: 30%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query_stok = mysqli_query($koneksi, "
                            SELECT p.product_name, COALESCE(i.stock_quantity, 0) AS stock_quantity 
                            FROM products p 
                            LEFT JOIN inventory i ON p.id_product = i.id_product
                        ");
                        
                        while ($row = mysqli_fetch_assoc($query_stok)) {
                            $qty = (int)$row['stock_quantity'];
                            
                            if ($qty > 5) {
                                $status_label = "TERSEDIA";
                                $status_class = "status-tersedia";
                            } elseif ($qty > 0 && $qty <= 5) {
                                $status_label = "WARNING";
                                $status_class = "status-warning";
                            } else {
                                $status_label = "TIDAK TERSEDIA";
                                $status_class = "status-kosong";
                            }
                            
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td><strong>" . $qty . "</strong></td>";
                            echo "<td><span class='status-badge " . $status_class . "'>" . $status_label . "</span></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php
    $stock_map = [];
    $res_stok = mysqli_query($koneksi, "SELECT id_product, stock_quantity FROM inventory");
    while($s = mysqli_fetch_assoc($res_stok)) {
        $stock_map[$s['id_product']] = (int)$s['stock_quantity'];
    }
    ?>

    <script>
        const stockData = <?= json_encode($stock_map); ?>;
        const productSelect = document.getElementById('productSelect');
        const stockStatusGroup = document.getElementById('stockStatusGroup');
        const stockStatusDisplay = document.getElementById('stockStatusDisplay');

        // Modal Elements
        const modal = document.getElementById('updateModal');
        const openBtn = document.getElementById('openModalBtn');
        const closeBtn = document.getElementById('closeModalBtn');

        openBtn.addEventListener('click', () => modal.style.display = 'flex');
        closeBtn.addEventListener('click', () => modal.style.display = 'none');
        window.addEventListener('click', (e) => { if (e.target == modal) modal.style.display = 'none'; });

        productSelect.addEventListener('change', function() {
            const selectedId = this.value;

            if (selectedId && stockData[selectedId] !== undefined) {
                const qty = stockData[selectedId];
                let statusText = '';
                let badgeClass = '';

                if (qty > 5) {
                    statusText = 'TERSEDIA (' + qty + ' pcs)';
                    badgeClass = 'status-tersedia';
                } else if (qty > 0 && qty <= 5) {
                    statusText = 'WARNING (' + qty + ' pcs - Stok Menipis!)';
                    badgeClass = 'status-warning';
                } else {
                    statusText = 'TIDAK TERSEDIA (Stok Habis)';
                    badgeClass = 'status-kosong';
                }

                stockStatusDisplay.innerHTML = `<span class="status-badge ${badgeClass}">${statusText}</span>`;
                stockStatusGroup.style.display = 'block';
            } else {
                stockStatusGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>