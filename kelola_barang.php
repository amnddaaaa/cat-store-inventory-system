<?php
session_start();
$koneksi = mysqli_connect("localhost", "root", "", "db_kucingku");

// --- LOGIKA PROSES DATA ---
if (isset($_POST['simpan_transaksi'])) {
    $id_p = intval($_POST['id_product']);
    $type = mysqli_real_escape_string($koneksi, $_POST['type']);
    $qty  = intval($_POST['quantity']);
    if ($type == 'in') mysqli_query($koneksi, "UPDATE inventory SET stock_quantity = stock_quantity + $qty WHERE id_product = $id_p");
    else mysqli_query($koneksi, "UPDATE inventory SET stock_quantity = stock_quantity - $qty WHERE id_product = $id_p");
    mysqli_query($koneksi, "INSERT INTO stock_logs (id_product, type, quantity, created_at) VALUES ($id_p, '$type', $qty, NOW())");
    header("Location: kelola_barang.php"); exit;
}

if (isset($_POST['simpan_barang'])) {
    $nama  = mysqli_real_escape_string($koneksi, $_POST['product_name']);
    $cat   = intval($_POST['id_category']);
    $stok  = intval($_POST['initial_stock']);
    $harga = floatval($_POST['price']);
    mysqli_query($koneksi, "INSERT INTO products (product_name, id_category, price) VALUES ('$nama', $cat, $harga)");
    $id_b  = mysqli_insert_id($koneksi);
    mysqli_query($koneksi, "INSERT INTO inventory (id_product, stock_quantity) VALUES ($id_b, $stok)");
    header("Location: kelola_barang.php"); exit;
}

if (isset($_POST['update_barang'])) {
    $id    = intval($_POST['id_product']);
    $nama  = mysqli_real_escape_string($koneksi, $_POST['product_name']);
    $cat   = intval($_POST['id_category']);
    $harga = floatval($_POST['price']);
    mysqli_query($koneksi, "UPDATE products SET product_name = '$nama', id_category = $cat, price = $harga WHERE id_product = $id");
    header("Location: kelola_barang.php"); exit;
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM inventory WHERE id_product = $id");
    mysqli_query($koneksi, "DELETE FROM products WHERE id_product = $id");
    header("Location: kelola_barang.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Barang - MeowGudang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #FF9F43; --bg: #FFFDF5; }
        body { margin: 0; display: flex; min-height: 100vh; background: var(--bg); font-family: 'Outfit', sans-serif; }
        
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #ddd; padding: 30px; flex-shrink: 0; display: flex; flex-direction: column; }
        .logo { font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 40px; display: flex; align-items: center; gap: 10px; font-family: 'Outfit', sans-serif; }
        .menu-group { font-size: 11px; font-weight: 800; color: #aaa; margin: 25px 0 10px; letter-spacing: 1px; text-transform: uppercase; font-family: 'Outfit', sans-serif; }
        .sidebar a { display: flex; align-items: center; gap: 12px; padding: 12px 0; color: #777; text-decoration: none; font-weight: 600; font-family: 'Outfit', sans-serif; }
        .sidebar a.active { color: var(--primary); background: #FFF8E1; padding: 10px; border-radius: 10px; }
        .logout-btn { color: #FF6B6B; font-weight: bold; text-decoration: none; padding: 12px 0; margin-top: auto; display: block; }

        .main { flex-grow: 1; padding: 40px; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; }
        .card { background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .scroll-wrapper { height: 400px; overflow-y: auto; margin-top: 15px; border: 1px solid #eee; border-radius: 10px; padding: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #fdfdfd; padding: 12px; border-bottom: 1px solid #eee; position: sticky; top: 0; font-family: 'Outfit', sans-serif; }
        td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
        .btn-meow { background: var(--primary); color: white; padding: 12px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; width: 100%; margin-top: 15px; font-family: 'Outfit', sans-serif; }
        .btn-act { padding: 5px 10px; border-radius: 6px; border: 1px solid #ddd; cursor: pointer; background: #fff; }
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999; }
        .modal-content { background: #fff; padding: 30px; border-radius: 20px; width: 400px; }
        input, select { width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        .cat-badge { background: #fff4e0; padding: 2px 8px; border-radius: 5px; font-size: 11px; color: #d35400; font-weight: 600; }
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
        <a href="kelola_barang.php" class="active">📦 Daftar Barang</a>
        <a href="kelola_kategori.php">🏷️ Kategori Barang</a>
        
        <div class="menu-group">LAPORAN</div>
        <a href="laporan_stok.php">📊 Laporan Bulanan</a>

        <a href="logout.php" class="logout-btn">🚪 Keluar</a>
    </div>

    <div class="main">
        <div class="page-grid">
            <div class="card">
                <h3>📦 Daftar Barang</h3>
                <form method="GET" style="display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:10px;">
                    <input type="text" name="search" placeholder="Cari..." value="<?=$_GET['search']??''?>">
                    <select name="cat_id"><option value="">Semua Kategori</option><?php $c=mysqli_query($koneksi,"SELECT * FROM categories"); while($r=mysqli_fetch_assoc($c)) echo "<option value='{$r['id_category']}' ".($_GET['cat_id']??''==$r['id_category']?'selected':'').">{$r['category_name']}</option>"; ?></select>
                    <select name="sort">
                        <option value="DESC" <?=($_GET['sort']??'')=='DESC'?'selected':''?>>Stok Tertinggi</option>
                        <option value="ASC" <?=($_GET['sort']??'')=='ASC'?'selected':''?>>Stok Terendah</option>
                    </select>
                    <button type="submit" style="padding:0 15px; border-radius:10px; cursor:pointer; background:var(--primary); color:#fff; border:none;">🔍</button>
                </form>
                <div class="scroll-wrapper">
                    <table>
                        <thead><tr><th>Nama</th><th>Kat</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php 
                            $s = $_GET['search']??''; $cat = $_GET['cat_id']??''; $sort = $_GET['sort']??'DESC';
                            $q = "SELECT p.*, c.category_name, i.stock_quantity FROM products p LEFT JOIN categories c ON p.id_category=c.id_category LEFT JOIN inventory i ON p.id_product=i.id_product WHERE p.product_name LIKE '%$s%'"; 
                            if($cat) $q .= " AND p.id_category=$cat"; 
                            $q .= " ORDER BY i.stock_quantity $sort";
                            $data = mysqli_query($koneksi, $q);
                            while($r = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?=$r['product_name']?></td>
                                <td><span class="cat-badge"><?=$r['category_name']??'-'?></span></td>
                                <td>Rp <?=number_format($r['price']??0)?></td>
                                <td><b><?=$r['stock_quantity']??0?></b></td>
                                <td>
                                    <button class="btn-act" onclick="editModal(<?=$r['id_product']?>, '<?=htmlspecialchars($r['product_name'], ENT_QUOTES)?>', <?=$r['id_category']?>, <?=$r['price']??0?>)">✏️</button>
                                    <a href="?hapus=<?=$r['id_product']?>" onclick="return confirm('Hapus?')"><button class="btn-act">🗑️</button></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <button class="btn-meow" onclick="bukaModal('modalBarang')">+ Tambah Barang Baru</button>
            </div>

            <div class="card">
                <h3>🕒 Transaksi Terakhir</h3>
                <div class="scroll-wrapper">
                    <table>
                        <thead><tr><th>Produk</th><th>Jns</th><th>Jml</th></tr></thead>
                        <tbody>
                            <?php $l=mysqli_query($koneksi,"SELECT s.type, s.quantity, p.product_name FROM stock_logs s JOIN products p ON s.id_product=p.id_product ORDER BY s.created_at DESC");
                            while($r=mysqli_fetch_assoc($l)): ?>
                            <tr><td><?=$r['product_name']?></td><td><b style="color:<?=$r['type']=='in'?'#2ed573':'#ff6b6b'?>"><?=strtoupper($r['type'])?></b></td><td><?=$r['quantity']?></td></tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <button class="btn-meow" style="background:#555;" onclick="bukaModal('modalTransaksi')">+ Input Transaksi</button>
            </div>
        </div>
    </div>

    <div id="modalTransaksi" class="modal"><div class="modal-content">
        <h3>Transaksi 🐾</h3>
        <form method="POST"><select name="id_product"><?php $p=mysqli_query($koneksi,"SELECT * FROM products"); while($d=mysqli_fetch_assoc($p)) echo "<option value='{$d['id_product']}'>{$d['product_name']}</option>"; ?></select>
        <select name="type"><option value="in">Masuk</option><option value="out">Keluar</option></select>
        <input type="number" name="quantity" placeholder="Jumlah" required><button class="btn-meow" name="simpan_transaksi">Simpan</button></form>
    </div></div>
    <div id="modalBarang" class="modal"><div class="modal-content">
        <h3>Tambah Barang 📦</h3>
        <form method="POST"><input type="text" name="product_name" placeholder="Nama Produk" required><select name="id_category"><?php $c=mysqli_query($koneksi,"SELECT * FROM categories"); while($d=mysqli_fetch_assoc($c)) echo "<option value='{$d['id_category']}'>{$d['category_name']}</option>"; ?></select><input type="number" name="price" placeholder="Harga" required><input type="number" name="initial_stock" placeholder="Stok Awal" required><button class="btn-meow" name="simpan_barang">Simpan</button></form>
    </div></div>
    <div id="modalEdit" class="modal"><div class="modal-content">
        <h3>Edit Barang ✏️</h3>
        <form method="POST"><input type="hidden" name="id_product" id="eid"><input type="text" name="product_name" id="ename" required><select name="id_category" id="ecat"><?php $c=mysqli_query($koneksi,"SELECT * FROM categories"); while($d=mysqli_fetch_assoc($c)) echo "<option value='{$d['id_category']}'>{$d['category_name']}</option>"; ?></select><input type="number" name="price" id="eprice" required><button class="btn-meow" name="update_barang">Update</button></form>
    </div></div>

    <script>
        function bukaModal(id) { document.getElementById(id).style.display = 'flex'; }
        function editModal(id, name, cat, price) { document.getElementById('modalEdit').style.display='flex'; document.getElementById('eid').value=id; document.getElementById('ename').value=name; document.getElementById('ecat').value=cat; document.getElementById('eprice').value=price; }
        window.onclick = function(e) { if(e.target.className=='modal') e.target.style.display='none'; }
    </script>
</body>
</html>