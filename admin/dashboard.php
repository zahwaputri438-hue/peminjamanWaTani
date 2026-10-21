<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (
    !isset($_SESSION["id_user"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../login.php");
    exit;
}

function hitungData($koneksi,$tabel)
{
    $daftarTabel = [
        "users",
        "alat",
        "kategori",
        "peminjaman",
        "pengembalian"
    ];

    if (!in_array($tabel,$daftarTabel, true)) {
        return 0;
    }

    $query = mysqli_query($koneksi,
        "SELECT COUNT(*) AS total FROM `$tabel`"
    );

    if (!$query) {
        return 0;
    }

    $data = mysqli_fetch_assoc($query);
    return (int) $data["total"];
}

$totalUser         = hitungData($koneksi, "users");
$totalAlat         = hitungData($koneksi, "alat");
$totalKategori     = hitungData($koneksi, "kategori");
$totalPeminjaman   = hitungData($koneksi, "peminjaman");
$totalPengembalian = hitungData($koneksi, "pengembalian");

$namaAdmin =$_SESSION["nama_user"] ?? "Admin Pertanian";
$tanggal   = date("d M Y");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Dashboard Admin | WaTani</title>
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
            <img src="../assets/images/logo.png" alt="Logo WaTani">
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
            Halaman / <strong>Dashboard</strong>
        </div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
            <div>
                <p class="eyebrow" style="font-size: 10px; font-weight: 700; color: var(--green); letter-spacing: 0.05em; margin-bottom: 4px;">UTAMA</p>                <h1 style="font-size: 25px; font-weight: 700; color: var(--text);">Dashboard</h1>
                <p class="page-description" style="font-size: 11px; color: var(--muted); margin-top: 4px;">
                    Kelola alat pertanian, pengguna, dan transaksi peminjaman melalui dashboard WaTani.
                </p>
            </div>

            <div class="date-card" style="display: flex; align-items: center; gap: 9px; padding: 9px 13px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); color: #596b60; font-size: 11px; white-space: nowrap;">
                <span class="date-icon" style="color: var(--green); font-size: 16px;">▦</span>
                <span><?= htmlspecialchars($tanggal) ?></span>
            </div>
        </header>

        <section class="welcome-banner">
            <div class="welcome-text">
                <span class="welcome-tag">ADMINISTRATOR</span>
                <h2>Selamat datang, <?= htmlspecialchars($namaAdmin) ?>! 👋</h2>
                <p>
                    Kelola alat pertanian, pengguna, dan transaksi
                    peminjaman melalui dashboard WaTani.
                </p>
            </div>

            <div class="welcome-illustration" aria-hidden="true">
                🌾
            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div>
                    <h2>Ringkasan Data</h2>
                    <p>Informasi umum sistem WaTani saat ini.</p>
                </div>
            </div>

            <div class="stats-grid">

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon green">♙</span>
                        <span class="stat-caption">Terdaftar</span>
                    </div>
                    <p class="stat-title">Total Pengguna</p>
                    <h3><?= $totalUser ?></h3>
                    <a href="pengguna.php" class="stat-link">
                        Lihat pengguna <span>→</span>
                    </a>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon orange">⚒</span>
                        <span class="stat-caption">Inventaris</span>
                    </div>
                    <p class="stat-title">Alat Pertanian</p>
                    <h3><?= $totalAlat ?></h3>
                    <a href="alat.php" class="stat-link">
                        Lihat alat <span>→</span>
                    </a>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon blue">▤</span>
                        <span class="stat-caption">Jenis alat</span>
                    </div>
                    <p class="stat-title">Kategori Alat</p>
                    <h3><?= $totalKategori ?></h3>
                    <a href="kategori.php" class="stat-link">
                        Lihat kategori <span>→</span>
                    </a>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon purple">⇄</span>
                        <span class="stat-caption">Transaksi</span>
                    </div>
                    <p class="stat-title">Peminjaman</p>
                    <h3><?= $totalPeminjaman ?></h3>
                    <a href="peminjaman.php" class="stat-link">
                        Lihat peminjaman <span>→</span>
                    </a>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon teal">↩</span>
                        <span class="stat-caption">Transaksi</span>
                    </div>
                    <p class="stat-title">Pengembalian</p>
                    <h3><?= $totalPengembalian ?></h3>
                    <a href="pengembalian.php" class="stat-link">
                        Lihat pengembalian <span>→</span>
                    </a>
                </article>

            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div>
                    <h2>Akses Cepat</h2>
                    <p>Pilih menu untuk mulai mengelola sistem.</p>
                </div>
            </div>

            <div class="quick-grid">
                <a href="alat.php" class="quick-card">
                    <span class="quick-icon">🚜</span>
                    <span class="quick-text">
                        <strong>Kelola Alat</strong>
                        <small>Data inventaris pertanian</small>
                    </span>
                    <span class="quick-arrow">→</span>
                </a>

                <a href="pengguna.php" class="quick-card">
                    <span class="quick-icon">👥</span>
                    <span class="quick-text">
                        <strong>Kelola Pengguna</strong>
                        <small>Data akun pengguna</small>
                    </span>
                    <span class="quick-arrow">→</span>
                </a>

                <a href="peminjaman.php" class="quick-card">
                    <span class="quick-icon">📋</span>
                    <span class="quick-text">
                        <strong>Kelola Peminjaman</strong>
                        <small>Periksa transaksi peminjaman</small>
                    </span>
                    <span class="quick-arrow">→</span>
                </a>

                <a href="pengembalian.php" class="quick-card">
                    <span class="quick-icon">📦</span>
                    <span class="quick-text">
                        <strong>Kelola Pengembalian</strong>
                        <small>Periksa pengembalian alat</small>
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