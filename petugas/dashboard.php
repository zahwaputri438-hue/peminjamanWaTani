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

function hitungDataPetugas($koneksi, $status)
{
    $status = mysqli_real_escape_string($koneksi, $status);
    $query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = '$status'");
    if (!$query) {
        return 0;
    }
    $data = mysqli_fetch_assoc($query);
    return (int) $data["total"];
}

$totalPending   = hitungDataPetugas($koneksi, "pending");
$totalDipinjam  = hitungDataPetugas($koneksi, "dipinjam");
$totalSelesai   = hitungDataPetugas($koneksi, "selesai");

$namaPetugas = $_SESSION["nama_user"] ?? $_SESSION["username"] ?? "Petugas Lapangan";
$tanggal     = date("d M Y");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Dashboard Petugas | WaTani</title>
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
            <a href="dashboard.php" class="menu-link active">
                <span class="menu-icon">▦</span>
                Dashboard
            </a>

            <div class="sidebar-label">TRANSAKSI & VERIFIKASI</div>

            <a href="peminjaman.php" class="menu-link">
                <span class="menu-icon">⇄</span>
                Persetujuan Peminjaman
            </a>

            <a href="pengembalian.php" class="menu-link">
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
            Halaman / <strong>Dashboard Petugas</strong>
        </div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
            <div>
                <p class="eyebrow" style="font-size: 10px; font-weight: 700; color: var(--green); letter-spacing: 0.05em; margin-bottom: 4px;">PETUGAS LAPANGAN</p>
                <h1 style="font-size: 25px; font-weight: 700; color: var(--text);">Dashboard</h1>
                <p class="page-description" style="font-size: 11px; color: var(--muted); margin-top: 4px;">
                    Verifikasi pengajuan peminjaman alat dan kelola transaksi pengembalian alat pertanian.
                </p>
            </div>

            <div class="date-card" style="display: flex; align-items: center; gap: 9px; padding: 9px 13px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); color: #596b60; font-size: 11px; white-space: nowrap;">
                <span class="date-icon" style="color: var(--green); font-size: 16px;">▦</span>
                <span><?= htmlspecialchars($tanggal) ?></span>
            </div>
        </header>

        <section class="welcome-banner">
            <div class="welcome-text">
                <span class="welcome-tag">PETUGAS LAPANGAN</span>
                <h2>Selamat datang, <?= htmlspecialchars($namaPetugas) ?>! 👋</h2>
                <p>
                    Verifikasi pengajuan peminjaman alat dan kelola transaksi
                    pengembalian alat pertanian melalui dashboard WaTani.
                </p>
            </div>

            <div class="welcome-illustration" aria-hidden="true">
                📋
            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div>
                    <h2>Ringkasan Data</h2>
                    <p>Informasi status peminjaman alat pertanian saat ini.</p>
                </div>
            </div>

            <div class="stats-grid">

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon orange">⏳</span>
                        <span class="stat-caption">Verifikasi</span>
                    </div>
                    <p class="stat-title">Menunggu Persetujuan</p>
                    <h3><?= $totalPending ?></h3>
                    <a href="peminjaman.php" class="stat-link">
                        Lihat pengajuan <span>→</span>
                    </a>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon blue">🚜</span>
                        <span class="stat-caption">Aktif</span>
                    </div>
                    <p class="stat-title">Sedang Dipinjam</p>
                    <h3><?= $totalDipinjam ?></h3>
                    <a href="peminjaman.php" class="stat-link">
                        Lihat detail <span>→</span>
                    </a>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon green">✅</span>
                        <span class="stat-caption">Selesai</span>
                    </div>
                    <p class="stat-title">Peminjaman Selesai</p>
                    <h3><?= $totalSelesai ?></h3>
                    <a href="pengembalian.php" class="stat-link">
                        Lihat riwayat <span>→</span>
                    </a>
                </article>

            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div>
                    <h2>Akses Cepat</h2>
                    <p>Pilih menu untuk mulai memproses transaksi.</p>
                </div>
            </div>

            <div class="quick-grid">
                <a href="peminjaman.php" class="quick-card">
                    <span class="quick-icon">📋</span>
                    <span class="quick-text">
                        <strong>Persetujuan Peminjaman</strong>
                        <small>Verifikasi pengajuan dari peminjam</small>
                    </span>
                    <span class="quick-arrow">→</span>
                </a>

                <a href="pengembalian.php" class="quick-card">
                    <span class="quick-icon">📦</span>
                    <span class="quick-text">
                        <strong>Pengembalian Alat</strong>
                        <small>Proses pengembalian alat pertanian</small>
                    </span>
                    <span class="quick-arrow">→</span>
                </a>
            </div>
        </section>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>

    </main>
</div>

</body>
</html>