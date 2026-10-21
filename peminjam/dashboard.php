<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (
    !isset($_SESSION["id_user"]) ||
    ($_SESSION["role"] ?? "") !== "peminjam"
) {
    header("Location: ../login.php");
    exit;
}

$idUser     = (int) $_SESSION["id_user"];
$namaPetani = $_SESSION["nama_user"] ?? $_SESSION["username"] ?? "Petani WaTani";
$tanggal    = date("d M Y");

$qPending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user = $idUser AND status = 'pending'");
$totalPending = ($qPending && $rowPending = mysqli_fetch_assoc($qPending)) ? (int)$rowPending['total'] : 0;

$qDipinjam = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user = $idUser AND status = 'dipinjam'");
$totalDipinjam = ($qDipinjam && $rowDipinjam = mysqli_fetch_assoc($qDipinjam)) ? (int)$rowDipinjam['total'] : 0;

$qTersedia = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM alat");
$totalTersedia = ($qTersedia && $rowTersedia = mysqli_fetch_assoc($qTersedia)) ? (int)$rowTersedia['total'] : 0;

$queryAlat = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY id_alat DESC LIMIT 4");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Dashboard Petani | WaTani</title>
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
        .alat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 15px;
        }
        .alat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .alat-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
        }
        .alat-desc {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 12px;
        }
        .btn-pinjam {
            display: inline-block;
            text-align: center;
            background: #176638;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-pinjam:hover {
            background: #124d2a;
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

            <a href="katalog.php" class="menu-link">
                <span class="menu-icon">🚜</span>
                Katalog Alat
            </a>

            <div class="sidebar-label">PEMINJAMAN SAYA</div>

            <a href="riwayat.php" class="menu-link">
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
            Halaman / <strong>Dashboard Petani</strong>
        </div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
            <div>
                <p class="eyebrow" style="font-size: 10px; font-weight: 700; color: var(--green); letter-spacing: 0.05em; margin-bottom: 4px;">PETANI WATANI</p>
                <h1 style="font-size: 25px; font-weight: 700; color: var(--text);">Dashboard</h1>
                <p class="page-description" style="font-size: 11px; color: var(--muted); margin-top: 4px;">
                    Cari alat pertanian yang Anda butuhkan dan ajukan peminjaman dengan mudah.
                </p>
            </div>

            <div class="date-card" style="display: flex; align-items: center; gap: 9px; padding: 9px 13px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); color: #596b60; font-size: 11px; white-space: nowrap;">
                <span class="date-icon" style="color: var(--green); font-size: 16px;">▦</span>
                <span><?= htmlspecialchars($tanggal) ?></span>
            </div>
        </header>

        <section class="welcome-banner">
            <div class="welcome-text">
                <span class="welcome-tag">PETANI WATANI</span>
                <h2>Selamat datang, <?= htmlspecialchars($namaPetani) ?>! 🌾</h2>
                <p>
                    Ayo majukan pertanian Anda! Pilih dan pinjam alat pertanian berkualitas dari WaTani sekarang.
                </p>
            </div>

            <div class="welcome-illustration" aria-hidden="true">
                🚜
            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div>
                    <h2>Ringkasan Peminjaman</h2>
                    <p>Status transaksi peminjaman alat Anda saat ini.</p>
                </div>
            </div>

            <div class="stats-grid">

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon green">🚜</span>
                        <span class="stat-caption">Siap Pinjam</span>
                    </div>
                    <p class="stat-title">Alat Tersedia</p>
                    <h3><?= $totalTersedia ?></h3>
                    <a href="katalog.php" class="stat-link">
                        Lihat Katalog <span>→</span>
                    </a>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon orange">⏳</span>
                        <span class="stat-caption">Status</span>
                    </div>
                    <p class="stat-title">Menunggu Verifikasi</p>
                    <h3><?= $totalPending ?></h3>
                    <a href="riwayat.php" class="stat-link">
                        Cek Status <span>→</span>
                    </a>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon blue">📦</span>
                        <span class="stat-caption">Masa Pinjam</span>
                    </div>
                    <p class="stat-title">Sedang Dipinjam</p>
                    <h3><?= $totalDipinjam ?></h3>
                    <a href="riwayat.php" class="stat-link">
                        Lihat Detail <span>→</span>
                    </a>
                </article>

            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div>
                    <h2>Katalog Alat Pertanian</h2>
                    <p>Pilihan alat pertanian yang siap untuk Anda ajukan.</p>
                </div>
            </div>

            <div class="alat-grid">
                <?php if ($queryAlat && mysqli_num_rows($queryAlat) > 0): ?>
                    <?php while ($alat = mysqli_fetch_assoc($queryAlat)): ?>
                        <div class="alat-card">
                            <div>
                                <div class="alat-title"><?= htmlspecialchars($alat['nama_alat']); ?></div>
                                <div class="alat-desc"><?= htmlspecialchars(substr($alat['deskripsi'] ?? 'Alat pertanian siap pakai untuk kebutuhan perkebunan dan sawah.', 0, 70)); ?>...</div>
                            </div>
                            <a href="pinjam.php?id=<?= $alat['id_alat']; ?>" class="btn-pinjam">Ajukan Pinjam</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--muted); font-size: 13px;">Belum ada alat pertanian yang tersedia.</p>
                <?php endif; ?>
            </div>
        </section>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>

    </main>
</div>

</body>
</html>