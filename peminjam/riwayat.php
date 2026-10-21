<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (!isset($_SESSION["id_user"]) || ($_SESSION["role"] ?? "") !== "peminjam") {
    header("Location: ../login.php");
    exit;
}

$idUser     = (int) $_SESSION["id_user"];
$namaPetani = $_SESSION["nama_user"] ?? $_SESSION["username"] ?? "Petani WaTani";
$tanggal    = date("d M Y");

$sql = "
    SELECT 
        p.id_peminjaman,
        p.tgl_pinjam,
        p.tgl_kembali_rencana,
        p.status,
        a.nama_alat,
        a.gambar
    FROM peminjaman p
    LEFT JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    LEFT JOIN alat a ON dp.id_alat = a.id_alat
    WHERE p.id_user = $idUser
    ORDER BY p.id_peminjaman DESC
";

$queryRiwayat = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Status & Riwayat Peminjaman | WaTani</title>
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
        .badge-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .status-pending { background: #fffbe6; color: #d48806; border: 1px solid #ffe58f; }
        .status-disetujui, .status-dipinjam { background: #e6f7ff; color: #0958d9; border: 1px solid #91caff; }
        .status-dikembalikan, .status-selesai { background: #f6ffed; color: #389e0d; border: 1px solid #b7eb8f; }
        .status-ditolak { background: #fff2f0; color: #cf1322; border: 1px solid #ffccc7; }

        .table-responsive {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }
        th {
            background: #f8faf8;
            color: var(--text);
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            color: #334155;
            vertical-align: middle;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .alat-thumb {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        .alat-thumb img {
            width: 38px;
            height: 38px;
            object-fit: contain;
            background: #f8faf8;
            border-radius: 6px;
            border: 1px solid var(--border);
            padding: 2px;
        }
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

            <a href="katalog.php" class="menu-link">
                <span class="menu-icon">🚜</span>
                Katalog Alat
            </a>

            <div class="sidebar-label">PEMINJAMAN SAYA</div>

            <a href="riwayat.php" class="menu-link active">
                <span class="menu-icon">📋</span>
                Status & Riwayat
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="admin-mini">
                <div class="admin-avatar">P</div>
                <div class="admin-info">
                    <strong><?= htmlspecialchars($namaPetani) ?></strong>
                    <small>Petani / Peminjam</small>
                </div>
            </div>

            <a href="../logout.php" class="logout-link" onclick="return confirm('Yakin ingin keluar dari WaTani?')">
                ↪ Keluar
            </a>
        </div>
    </aside>

    <main class="main-content">

        <div class="breadcrumb">
            Peminjaman Saya / <strong>Status & Riwayat</strong>
        </div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
            <div>
                <p class="eyebrow" style="font-size: 10px; font-weight: 700; color: var(--green); margin-bottom: 4px;">RIWAYAT WATANI</p>
                <h1 style="font-size: 25px; font-weight: 700; color: var(--text);">Status & Riwayat Peminjaman</h1>
                <p class="page-description" style="font-size: 11px; color: var(--muted); margin-top: 4px;">
                    Pantau status pengajuan peminjaman alat pertanian Anda di sini.
                </p>
            </div>

            <div class="date-card" style="padding: 9px 13px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); font-size: 11px;">
                📅 <?= htmlspecialchars($tanggal) ?>
            </div>
        </header>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Alat Pertanian</th>
                        <th>Tgl Pinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Status Pengajuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($queryRiwayat && mysqli_num_rows($queryRiwayat) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($queryRiwayat)): ?>
                            <?php 
                                $statusRaw = strtolower($row['status'] ?? 'pending');
                                $badgeClass = 'status-pending';
                                $statusLabel = 'Menunggu Konfirmasi';

                                if ($statusRaw === 'disetujui' || $statusRaw === 'dipinjam') {
                                    $badgeClass = 'status-disetujui';
                                    $statusLabel = 'Disetujui / Dipinjam';
                                } elseif ($statusRaw === 'dikembalikan' || $statusRaw === 'selesai') {
                                    $badgeClass = 'status-dikembalikan';
                                    $statusLabel = 'Sudah Dikembalikan';
                                } elseif ($statusRaw === 'ditolak') {
                                    $badgeClass = 'status-ditolak';
                                    $statusLabel = 'Pengajuan Ditolak';
                                }
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="alat-thumb">
                                        <?php if (!empty($row['gambar']) && file_exists("../assets/images/alat/" . $row['gambar'])): ?>
                                            <img src="../assets/images/alat/<?= htmlspecialchars($row['gambar']) ?>" alt="Alat">
                                        <?php else: ?>
                                            <span>🚜</span>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($row['nama_alat'] ?? 'Alat Pertanian'); ?></span>
                                    </div>
                                </td>
                                <td><?= date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                                <td><?= date('d M Y', strtotime($row['tgl_kembali_rencana'])); ?></td>
                                <td>
                                    <span class="badge-status <?= $badgeClass; ?>">
                                        <?= $statusLabel; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--muted); padding: 40px;">
                                Belum ada riwayat peminjaman alat.
                            </td>
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