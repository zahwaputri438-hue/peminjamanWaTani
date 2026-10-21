<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (
    !isset($_SESSION["id_user"]) ||
    ($_SESSION["role"] ?? "") !== "petugas"
) {
    header("Location: ../login.php");
    exit;
}

$namaPetugas = $_SESSION["nama_user"] ?? "Petugas WaTani";
$tanggal     = date("d M Y");

$tglAwal  = $_GET['tgl_awal'] ?? '';
$tglAkhir = $_GET['tgl_akhir'] ?? '';

$where = "";
if (!empty($tglAwal) && !empty($tglAkhir)) {
    $where = "WHERE p.tgl_pinjam BETWEEN '$tglAwal' AND '$tglAkhir'";
}

$queryLaporan = mysqli_query($koneksi, "
    SELECT p.*, u.nama_user, GROUP_CONCAT(a.nama_alat SEPARATOR ', ') AS daftar_alat, pg.tgl_kembali_realisasi, pg.denda
    FROM peminjaman p
    LEFT JOIN users u ON p.id_user = u.id_user
    LEFT JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    LEFT JOIN alat a ON dp.id_alat = a.id_alat
    LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
    $where
    GROUP BY p.id_peminjaman
    ORDER BY p.id_peminjaman DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Laporan Transaksi | WaTani Petugas</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/laporan.css">
</head>
<body>

<div class="dashboard-container">

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
            <a href="dashboard.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <span class="menu-icon">▦</span>
                Dashboard
            </a>

            <a href="peminjaman.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'active' : '' ?>">
                <span class="menu-icon">⇄</span>
                Data Peminjaman
            </a>

            <a href="pengembalian.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'pengembalian.php' ? 'active' : '' ?>">
                <span class="menu-icon">↩</span>
                Data Pengembalian
            </a>

            <div class="sidebar-label">TRANSAKSI & LAPORAN</div>

            <a href="riwayat.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : '' ?>">
                <span class="menu-icon">◷</span>
                Riwayat Transaksi
            </a>

            <a href="laporan.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
                <span class="menu-icon">▤</span>
                Laporan
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="admin-mini">
                <div class="admin-avatar">P</div>
                <div class="admin-info">
                    <strong><?= htmlspecialchars($namaPetugas) ?></strong>
                    <small>Petugas Sistem</small>
                </div>
            </div>

            <a href="../logout.php" class="logout-link" onclick="return confirm('Yakin ingin keluar dari WaTani?')">
                ↪ Keluar
            </a>
        </div>
    </aside>

    <main class="main-content">

        <div class="breadcrumb">
            Halaman / <strong>Laporan Transaksi</strong>
        </div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
            <div>
                <p class="eyebrow" style="font-size: 10px; font-weight: 700; color: var(--green); letter-spacing: 0.05em; margin-bottom: 4px;">LAPORAN OPERASIONAL</p>
                <h1 style="font-size: 25px; font-weight: 700; color: var(--text);">Laporan Transaksi</h1>
                <p class="page-description" style="font-size: 11px; color: var(--muted); margin-top: 4px;">
                    Cetak dan filter laporan transaksi peminjaman alat pertanian.
                </p>
            </div>

            <div class="date-card" style="display: flex; align-items: center; gap: 9px; padding: 9px 13px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); color: #596b60; font-size: 11px; white-space: nowrap;">
                <span class="date-icon" style="color: var(--green); font-size: 16px;">▦</span>
                <span><?= htmlspecialchars($tanggal) ?></span>
            </div>
        </header>

        <div class="filter-card">
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <label>Tanggal Awal</label>
                    <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tglAwal) ?>">
                </div>

                <div class="form-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tglAkhir) ?>">
                </div>

                <button type="submit" class="btn-green">Filter Laporan</button>
                <button type="button" class="btn-print" onclick="window.print()">🖨 Cetak Laporan</button>
            </form>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Alat Pertanian</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali Rencana</th>
                            <th>Tgl Kembali Realisasi</th>
                            <th>Denda</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($queryLaporan && mysqli_num_rows($queryLaporan) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($queryLaporan)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_user'] ?? '-') ?></strong></td>
                                    <td><?= htmlspecialchars($row['daftar_alat'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['tgl_pinjam'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['tgl_kembali_rencana'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['tgl_kembali_realisasi'] ?? '-') ?></td>
                                    <td>Rp <?= number_format($row['denda'] ?? 0, 0, ',', '.') ?></td>
                                    <td><?= ucfirst($row['status'] ?? '-') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--muted); padding: 24px;">
                                    Tidak ada data laporan transaksi untuk periode ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>

    </main>
</div>

</body>
</html>