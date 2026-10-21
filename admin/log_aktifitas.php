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

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$logList = [];
$totalLog = 0;

$sqlLog = "
    SELECT log_aktifitas.id_log, log_aktifitas.id_user, log_aktifitas.aktivitas, log_aktifitas.waktu, users.nama_user AS nama_aktor 
    FROM log_aktifitas 
    LEFT JOIN users ON log_aktifitas.id_user = users.id_user 
    ORDER BY log_aktifitas.waktu DESC, log_aktifitas.id_log DESC
";

$result = mysqli_query($koneksi, $sqlLog);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $logList[] = $row;
        $totalLog++;
    }
}

$namaAdmin = $_SESSION["nama_user"] ?? "Administrator";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas | WaTani</title>
    <link rel="stylesheet" href="../assets/css/alat.css">
    <style>
        .alat-table th, .alat-table td {
            vertical-align: middle;
        }
        .alat-table th:nth-child(1), .alat-table td:nth-child(1) {
            text-align: center;
        }
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

            <a href="pengembalian.php" class="menu-link">
                <span class="menu-icon">↩</span>
                Data Pengembalian
            </a>

            <a href="log_aktifitas.php" class="menu-link active">
                <span class="menu-icon">◷</span>
                Log Aktivitas
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="admin-mini">
                <div class="admin-avatar">A</div>
                <div class="admin-info">
                    <strong><?= e($namaAdmin) ?></strong>
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
            Halaman / <strong>Log Aktivitas</strong>
        </div>

        <header class="page-header">
            <div>
                <p class="eyebrow">TRANSAKSI</p>
                <h1>Log Aktivitas</h1>
                <p class="page-description">
                    Riwayat aktivitas dan aksi pengguna pada sistem WaTani.
                </p>
            </div>

            <div class="date-card">
                <span class="date-icon">▦</span>
                <span><?= date("d M Y") ?></span>
            </div>
        </header>

        <section class="panel">
            <div class="panel-header table-heading">
                <div>
                    <h2>Daftar Riwayat Aktivitas</h2>
                    <p>Catatan riwayat aktivitas pengguna yang terekam di sistem.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="alat-table">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center; padding: 12px 16px;">NO.</th>
                            <th style="width: 180px; padding: 12px 16px;">WAKTU</th>
                            <th style="width: 220px; padding: 12px 16px;">USER / AKTOR</th>
                            <th style="padding: 12px 16px;">AKTIVITAS / KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalLog > 0): ?>
                            <?php foreach ($logList as $index => $log): ?>
                                <tr>
                                    <td style="text-align: center;"><?= $index + 1 ?></td>
                                    <td><?= e($log["waktu"] ?? "-") ?></td>
                                    <td><strong><?= e($log["nama_aktor"] ?? "System / ID: " . $log["id_user"]) ?></strong></td>
                                    <td><?= e($log["aktivitas"] ?? "-") ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <span style="font-size: 32px; display: block; margin-bottom: 8px;">◷</span>
                                    <strong>Belum ada log aktivitas tercatat</strong>
                                    <small>Aktivitas pengguna akan muncul di sini secara otomatis.</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                Menampilkan <strong><?= $totalLog ?></strong> data riwayat aktivitas
            </div>
        </section>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>
    </main>
</div>
</body>
</html>