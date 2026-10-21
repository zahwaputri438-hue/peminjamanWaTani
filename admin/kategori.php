<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (!isset($_SESSION["id_user"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../login.php");
    exit;
}

$namaAdmin = $_SESSION["nama_user"] ?? $_SESSION["username"] ?? "Administrator";
$tanggal   = date("d M Y");

$queryKategori = mysqli_query($koneksi, "
    SELECT kategori.*, COUNT(alat.id_alat) AS jumlah_alat 
    FROM kategori 
    LEFT JOIN alat ON kategori.id_kategori = alat.id_kategori 
    GROUP BY kategori.id_kategori 
    ORDER BY kategori.id_kategori DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Kategori Alat | WaTani Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/kategori.css">
</head>
<body>

<div class="dashboard-container">

    <aside class="sidebar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="Logo WaTani" onerror="this.style.display='none'">
            <span><strong>WaTani</strong><small>Sistem Pertanian</small></span>
        </a>

        <div class="sidebar-label">MENU UTAMA</div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-link">
                <span class="menu-icon">▦</span>
                Dashboard
            </a>

            <a href="pengguna.php" class="menu-link">
                <span class="menu-icon">♙</span>
                Data Pengguna
            </a>

            <a href="alat.php" class="menu-link">
                <span class="menu-icon">⚒</span>
                Data Alat Pertanian
            </a>

            <a href="kategori.php" class="menu-link active">
                <span class="menu-icon">▤</span>
                Kategori Alat
            </a>

            <div class="sidebar-label">TRANSAKSI</div>

            <a href="peminjaman.php" class="menu-link">
                <span class="menu-icon">⇄</span>
                Data Peminjaman
            </a>

            <a href="pengembalian.php" class="menu-link">
                <span class="menu-icon">↩</span>
                Data Pengembalian
            </a>

            <a href="log.php" class="menu-link">
                <span class="menu-icon">◷</span>
                Log Aktivitas
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="admin-mini">
                <div class="admin-avatar">A</div>
                <div class="admin-info">
                    <strong><?= htmlspecialchars($namaAdmin) ?></strong>
                    <small>Administrator</small>
                </div>
            </div>
            <a href="../logout.php" class="logout-link" onclick="return confirm('Yakin ingin keluar?')">↪ Keluar</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="breadcrumb">Halaman / <strong>Kategori Alat Pertanian</strong></div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
            <div>
                <p class="eyebrow" style="font-size: 10px; font-weight: 700; color: var(--green); margin-bottom: 4px;">INVENTARIS</p>
                <h1 style="font-size: 25px; font-weight: 700; color: var(--text);">Kategori Alat Pertanian</h1>
                <p class="page-description" style="font-size: 11px; color: var(--muted); margin-top: 4px;">
                    Kelompokkan inventaris alat pertanian berdasarkan kategori spesifik.
                </p>
            </div>
            <div class="date-card" style="padding: 9px 13px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); font-size: 11px; color: var(--muted);">
                📅 <?= htmlspecialchars($tanggal) ?>
            </div>
        </header>

        <?php if (isset($_GET['pesan'])): ?>
            <?php if ($_GET['pesan'] === 'berhasil'): ?>
                <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; border: 1px solid #c3e6cb;">
                    Aksi kategori berhasil dilakukan!
                </div>
            <?php elseif ($_GET['pesan'] === 'gagal'): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; border: 1px solid #f5c6cb;">
                    ⚠️ Terjadi kesalahan atau nama kategori kosong!
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0; font-size: 16px; color: var(--text);">Daftar Kategori</h3>
                <button type="button" class="btn-green" onclick="bukaModalTambah()">+ Tambah Kategori</button>
            </div>

            <table class="table-data">
                <thead>
                    <tr>
                        <th style="width: 60px;">No.</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Alat Terdaftar</th>
                        <th style="text-align: center; width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($queryKategori && mysqli_num_rows($queryKategori) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($queryKategori)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_kategori']); ?></strong></td>
                                <td><span class="badge-count"><?= $row['jumlah_alat']; ?> Unit</span></td>
                                <td style="text-align: center;">
                                    <a href="proses_kategori.php?hapus=<?= $row['id_kategori']; ?>" class="btn-action-sm btn-del" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--muted); padding: 30px;">Belum ada kategori alat yang ditambahkan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="modalTambah" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
            <div class="modal-box" style="background: #fff; padding: 24px; border-radius: 12px; width: 400px; max-width: 90%;">
                <h3 style="margin-top: 0; margin-bottom: 15px; color: var(--text);">Tambah Kategori Baru</h3>
                <form action="proses_kategori.php" method="POST">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600;">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Pengolah Tanah" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                    </div>
                    <div style="display: flex; gap: 8px; margin-top: 20px;">
                        <button type="submit" class="btn-green" style="flex: 1; justify-content: center; padding: 10px; background: var(--green); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Simpan</button>
                        <button type="button" onclick="tutupModalTambah()" style="background: #e2e8f0; color: #475569; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer;">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>
    </main>
</div>

<script>
function bukaModalTambah() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function tutupModalTambah() {
    document.getElementById('modalTambah').style.display = 'none';
}
</script>

</body>
</html>