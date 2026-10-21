<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (
    !isset($_SESSION["id_user"]) ||
    !isset($_SESSION["role"]) ||
    ($_SESSION["role"] !== "admin" && $_SESSION["role"] !== "petugas")
) {
    header("Location: ../login.php");
    exit;
}

if (empty($_SESSION["csrf_pengembalian"])) {
    $_SESSION["csrf_pengembalian"] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$peminjamanAktifList = [];
$qAktif = mysqli_query($koneksi, "
    SELECT p.id_peminjaman, u.nama_user, a.nama_alat, p.tgl_pinjam 
    FROM peminjaman p 
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN users u ON p.id_user = u.id_user 
    JOIN alat a ON dp.id_alat = a.id_alat 
    WHERE p.status = 'Dipinjam' 
    ORDER BY p.id_peminjaman DESC
");
if ($qAktif) {
    while ($row = mysqli_fetch_assoc($qAktif)) {
        $peminjamanAktifList[] = $row;
    }
}

$sqlPengembalian = "
    SELECT pg.*, p.tgl_pinjam, u.nama_user, a.nama_alat 
    FROM pengembalian pg
    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
    LEFT JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN users u ON p.id_user = u.id_user 
    LEFT JOIN alat a ON dp.id_alat = a.id_alat 
    ORDER BY pg.id_pengembalian DESC
";
$result = mysqli_query($koneksi, $sqlPengembalian);

$pengembalianList = [];
$totalPengembalian = 0;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pengembalianList[] = $row;
        $totalPengembalian++;
    }
}

$pesan = $_GET["pesan"] ?? "";
$modeTambah = isset($_GET["tambah"]);
$namaAdmin = $_SESSION["nama_user"] ?? "Administrator";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengembalian | WaTani</title>
    <link rel="stylesheet" href="../assets/css/alat.css">
    <style>
        .alat-table th, .alat-table td {
            vertical-align: middle;
        }
        .alat-table th:nth-child(1), .alat-table td:nth-child(1),
        .alat-table th:nth-child(4), .alat-table td:nth-child(4),
        .alat-table th:nth-child(5), .alat-table td:nth-child(5) {
            text-align: center;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }
        .status-aman { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .status-rusak { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .status-hilang { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="Logo WaTani">
            <span>
                <strong>WaTani</strong>
                <small>Sistem Pertanian</small>
            </span>
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

            <a href="kategori.php" class="menu-link">
                <span class="menu-icon">▤</span>
                Kategori Alat
            </a>

            <div class="sidebar-label">TRANSAKSI</div>

            <a href="peminjaman.php" class="menu-link">
                <span class="menu-icon">⇄</span>
                Data Peminjaman
            </a>

            <a href="pengembalian.php" class="menu-link active">
                <span class="menu-icon">↩</span>
                Data Pengembalian
            </a>

            <a href="log_aktifitas.php" class="menu-link">
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

            <a href="../logout.php" class="logout-link" onclick="return confirm('Yakin ingin keluar dari WaTani?')">
                ↪ Keluar
            </a>
        </div>
    </aside>

    <main class="main-content">

        <div class="breadcrumb">
            Halaman / <strong>Data Pengembalian</strong>
        </div>

        <header class="page-header">
            <div>
                <p class="eyebrow">TRANSAKSI</p>
                <h1>Pengembalian Alat Pertanian</h1>
                <p class="page-description">
                    Proses pengembalian alat dan pemeriksaan kondisi fisik dari peminjam.
                </p>
            </div>

            <div class="date-card">
                <span class="date-icon">▦</span>
                <span><?= date("d M Y") ?></span>
            </div>
        </header>

        <?php if ($pesan === "berhasil"): ?>
            <div class="notice success">✓ Pengembalian alat berhasil dicatat dan stok telah diperbarui.</div>
        <?php elseif ($pesan === "gagal"): ?>
            <div class="notice error">Proses pengembalian gagal dilakukan. Periksa kembali input Anda.</div>
        <?php endif; ?>

        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon green">↩</div>
                <div class="stat-content">
                    <span>Total Pengembalian</span>
                    <strong><?= $totalPengembalian ?></strong>
                    <small>Alat telah dikembalikan</small>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon blue">⇄</div>
                <div class="stat-content">
                    <span>Menunggu Dikembalikan</span>
                    <strong><?= count($peminjamanAktifList) ?></strong>
                    <small>Alat sedang dipinjam aktif</small>
                </div>
            </article>
        </section>

        <?php if ($modeTambah): ?>
            <section class="panel form-panel">
                <div class="panel-header">
                    <div>
                        <h2>Proses Pengembalian Alat</h2>
                        <p>Pilih transaksi peminjaman aktif yang akan dikembalikan.</p>
                    </div>
                    <a href="pengembalian.php" class="close-link">✕</a>
                </div>

                <form action="crud_pengembalian.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_pengembalian"]) ?>">
                    <input type="hidden" name="aksi" value="tambah">

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="id_peminjaman">Pilih Transaksi Peminjaman Aktif</label>
                            <select id="id_peminjaman" name="id_peminjaman" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border);">
                                <option value="">-- Pilih Peminjam & Alat --</option>
                                <?php foreach ($peminjamanAktifList as $act): ?>
                                    <option value="<?= $act['id_peminjaman'] ?>">
                                        ID #<?= $act['id_peminjaman'] ?> | <?= e($act['nama_user']) ?> meminjam <?= e($act['nama_alat']) ?> - Tgl Pinjam: <?= $act['tgl_pinjam'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_kembali">Tanggal Pengembalian Aktual</label>
                            <input id="tanggal_kembali" name="tanggal_kembali" type="date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="kondisi_alat">Kondisi Alat Saat Kembali</label>
                            <select id="kondisi_alat" name="kondisi_alat" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border);">
                                <option value="Baik">Baik / Normal</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                                <option value="Hilang">Hilang</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="keterangan">Keterangan / Catatan Pemeriksaan</label>
                            <textarea id="keterangan" name="keterangan" rows="3" placeholder="Tuliskan catatan kondisi alat jika ada kerusakan..." style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); box-sizing:border-box;"></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="pengembalian.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Proses Pengembalian</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-header table-heading">
                <div>
                    <h2>Riwayat Pengembalian Alat</h2>
                    <p>Daftar seluruh alat pertanian yang telah dikembalikan oleh peminjam.</p>
                </div>

                <a href="pengembalian.php?tambah=1" class="btn btn-primary">
                    <span>＋</span> Proses Pengembalian Baru
                </a>
            </div>

            <div class="table-responsive">
                <table class="alat-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No.</th>
                            <th>Peminjam</th>
                            <th>Alat Pertanian</th>
                            <th style="width: 130px;">Tgl Kembali</th>
                            <th style="width: 130px;">Kondisi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalPengembalian > 0): ?>
                            <?php foreach ($pengembalianList as $index => $pg): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= e($pg["nama_user"] ?? "-") ?></strong></td>
                                    <td><?= e($pg["nama_alat"] ?? "-") ?></td>
                                    <td><?= e($pg["tanggal_kembali"] ?? $pg["tgl_kembali"] ?? "-") ?></td>
                                    <td>
                                        <?php 
                                            $kondisi = $pg["kondisi_alat"] ?? "Baik";
                                            $badgeKondisi = "status-aman";
                                            if (strpos($kondisi, "Rusak") !== false) $badgeKondisi = "status-rusak";
                                            elseif ($kondisi === "Hilang") $badgeKondisi = "status-hilang";
                                        ?>
                                        <span class="status-badge <?= $badgeKondisi ?>"><?= e($kondisi) ?></span>
                                    </td>
                                    <td><?= e($pg["keterangan"] ?? "-") ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <span>↩</span>
                                    <strong>Belum ada riwayat pengembalian</strong>
                                    <small>Catat pengembalian alat baru menggunakan tombol di atas.</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                Menampilkan <strong><?= $totalPengembalian ?></strong> data riwayat pengembalian
            </div>
        </section>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>
    </main>
</div>
</body>
</html>