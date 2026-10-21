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

$namaPetugas = $_SESSION["nama_user"] ?? $_SESSION["username"] ?? "Petugas Lapangan";
$tanggal     = date("d M Y");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["aksi"]) && $_POST["aksi"] === "selesai" && isset($_POST["id_peminjaman"])) {
    $idPeminjaman = (int) $_POST["id_peminjaman"];

    $updateQuery = mysqli_query($koneksi, "UPDATE peminjaman SET status = 'selesai' WHERE id_peminjaman = $idPeminjaman");

    if ($updateQuery) {
        header("Location: pengembalian.php?pesan=sukses");
        exit;
    }
}

$queryPengembalian = mysqli_query($koneksi, "
    SELECT 
        peminjaman.*,
        users.nama_user,
        GROUP_CONCAT(DISTINCT alat.nama_alat SEPARATOR ', ') AS nama_alat
    FROM peminjaman
    LEFT JOIN users ON peminjaman.id_user = users.id_user
    LEFT JOIN detail_peminjaman ON peminjaman.id_peminjaman = detail_peminjaman.id_peminjaman
    LEFT JOIN alat ON detail_peminjaman.id_alat = alat.id_alat
    WHERE peminjaman.status IN ('dipinjam', 'selesai')
    GROUP BY peminjaman.id_peminjaman
    ORDER BY peminjaman.id_peminjaman DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Pengembalian Alat | WaTani Petugas</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .page-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }
        .table-container {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
        }
        .data-table th {
            background-color: #f8faf8;
            color: var(--text);
            font-weight: 700;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-dipinjam { background: #d1ecf1; color: #0c5460; }
        .badge-selesai { background: #d4edda; color: #155724; }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            font-weight: 600;
            background: #176638;
            color: white;
            transition: 0.2s;
        }
        .btn-action:hover { background: #124d2a; }
    </style>
</head>
<body>

<div class="dashboard-container">

    <aside class="sidebar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="Logo WaTani" onerror="this.style.display='none'">
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

            <div class="sidebar-label">TRANSAKSI & VERIFIKASI</div>

            <a href="peminjaman.php" class="menu-link">
                <span class="menu-icon">⇄</span>
                Persetujuan Peminjaman
            </a>

            <a href="pengembalian.php" class="menu-link active">
                <span class="menu-icon">↩</span>
                Pengembalian Alat
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="admin-mini">
                <div class="admin-avatar">P</div>
                <div class="admin-info">
                    <strong><?= htmlspecialchars($namaPetugas) ?></strong>
                    <small>Petugas Lapangan</small>
                </div>
            </div>

            <a href="../logout.php" class="logout-link" onclick="return confirm('Yakin ingin keluar dari WaTani?')">
                ↪ Keluar
            </a>
        </div>
    </aside>

    <main class="main-content">

        <div class="breadcrumb">
            Halaman / <strong>Pengembalian Alat</strong>
        </div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
            <div>
                <p class="eyebrow" style="font-size: 10px; font-weight: 700; color: var(--green); letter-spacing: 0.05em; margin-bottom: 4px;">PETUGAS LAPANGAN</p>
                <h1 style="font-size: 25px; font-weight: 700; color: var(--text);">Pengelolaan Pengembalian</h1>
                <p class="page-description" style="font-size: 11px; color: var(--muted); margin-top: 4px;">
                    Catat dan konfirmasi pengembalian alat pertanian dari peminjam.
                </p>
            </div>

            <div class="date-card" style="display: flex; align-items: center; gap: 9px; padding: 9px 13px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); color: #596b60; font-size: 11px; white-space: nowrap;">
                <span class="date-icon" style="color: var(--green); font-size: 16px;">▦</span>
                <span><?= htmlspecialchars($tanggal) ?></span>
            </div>
        </header>

        <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses'): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; border: 1px solid #c3e6cb;">
                Alat berhasil dikonfirmasi kembali dan transaksi selesai!
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Peminjam</th>
                        <th>Alat Pertanian</th>
                        <th>Tgl Pinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($queryPengembalian && mysqli_num_rows($queryPengembalian) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($queryPengembalian)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_user'] ?? 'Petani'); ?></strong></td>
                                <td><?= htmlspecialchars($row['nama_alat'] ?? 'Alat Pertanian'); ?></td>
                                <td><?= !empty($row['tgl_pinjam']) ? date('d-m-Y', strtotime($row['tgl_pinjam'])) : '-'; ?></td>
                                <td><?= !empty($row['tgl_kembali_rencana']) ? date('d-m-Y', strtotime($row['tgl_kembali_rencana'])) : '-'; ?></td>
                                <td>
                                    <?php 
                                        $st = strtolower($row['status'] ?? '');
                                        if ($st === 'dipinjam') {
                                            echo '<span class="badge badge-dipinjam">Sedang Dipinjam</span>';
                                        } else {
                                            echo '<span class="badge badge-selesai">Selesai</span>';
                                        }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (strtolower($row['status']) === 'dipinjam'): ?>
                                        <form action="" method="POST" style="display: inline-block;">
                                            <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                            <button type="submit" name="aksi" value="selesai" class="btn-action" onclick="return confirm('Konfirmasi bahwa alat sudah dikembalikan dengan baik?')">Terima Pengembalian</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--muted); font-size: 12px;">Sudah Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--muted); padding: 20px;">Belum ada transaksi pengembalian aktif.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>

    </main>
</div>

</body>
</html>