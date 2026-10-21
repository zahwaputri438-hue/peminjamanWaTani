<?php
session_start();
require_once __DIR__ . "/koneksi.php";

$keyword = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM alat WHERE 1=1";
$params = [];
$types = "";

if ($keyword !== '') {
    $sql .= " AND (nama_alat LIKE ?)";
    $likeKey = "%" . $keyword . "%";
    $params[] = $likeKey;
    $types .= "s";
}

$sql .= " ORDER BY id_alat DESC";

$stmt = mysqli_prepare($koneksi, $sql);
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $queryAlat = mysqli_stmt_get_result($stmt);
} else {
    $queryAlat = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY id_alat DESC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Alat Pertanian | WaTani</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

    <header class="navbar">
        <a href="index.php" class="brand">
            <img src="assets/images/logo.png" alt="Logo WaTani">
            <div>
                <strong>WaTani</strong>
                <small>Sistem Peminjaman Alat Pertanian</small>
            </div>
        </a>

        <div class="nav-actions">
            <a href="index.php" class="nav-link-custom">Beranda</a>
            <a href="#katalog" class="nav-link-custom">Katalog</a>
            <?php if (isset($_SESSION['id_user']) && in_array($_SESSION['role'] ?? '', ['peminjam', 'petani'], true)): ?>
                <a href="peminjam/katalog.php" class="btn-solid"><i class="fa-regular fa-user"></i> Login</a>
            <?php else: ?>
                <a href="login.php" class="btn-solid"><i class="fa-regular fa-user"></i> Login</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero">
        <span class="hero-badge"><i class="fa-solid fa-seedling"></i> Katalog Resmi WaTani</span>
        <h1>Katalog Alat Pertanian Modern</h1>
        <p>Temukan dan pinjam berbagai peralatan pertanian berkualitas tinggi untuk mendukung produktivitas lahan Anda secara mudah, cepat, dan terpercaya.</p>
        
        <div class="hero-search-wrapper">
            <form action="index.php" method="GET" class="search-form">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Cari mesin traktor, sprayer, pompa air..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">Cari Alat</button>
            </form>
        </div>
    </section>

    <main class="container" id="katalog">
        <div class="section-header">
            <h2 class="section-title">Daftar Alat Tersedia</h2>
            
            <div class="category-filters">
                <a href="index.php" class="filter-chip active">Semua Alat</a>
                <a href="index.php?search=Traktor" class="filter-chip">Traktor</a>
                <a href="index.php?search=Irigasi" class="filter-chip">Irigasi</a>
                <a href="index.php?search=Sprayer" class="filter-chip">Sprayer</a>
            </div>
        </div>

        <div class="grid-alat">
            <?php if ($queryAlat && mysqli_num_rows($queryAlat) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($queryAlat)): ?>
                    <?php 
                        $namaGambar = $row['gambar'] ?? '';
                        $gambarFile = (!empty($namaGambar) && file_exists(__DIR__ . "/assets/images/alat/" . $namaGambar)) 
                                      ? "assets/images/alat/" . $namaGambar 
                                      : "assets/images/logo.png";
                        
                        $stok = $row['stok'] ?? 0;
                        $statusStok = ($stok > 0) ? "Stok: $stok unit" : "Stok Habis";
                        $badgeClass = ($stok > 0) ? "stok-badge" : "stok-badge habis";

                        $idAlat = (int) ($row['id_alat'] ?? 0);
                        if (isset($_SESSION['id_user']) && in_array($_SESSION['role'] ?? '', ['peminjam', 'petani'], true)) {
                            $linkPinjam = "peminjam/peminjam.php?id_alat=" . $idAlat;
                        } else {
                            $tujuan = "peminjam/peminjam.php?id_alat=" . $idAlat;
                            $linkPinjam = "login.php?redirect=" . urlencode($tujuan);
                        }
                    ?>
                    <article class="card-alat">
                        <div class="card-img-wrapper">
                            <img src="<?= $gambarFile ?>" alt="<?= htmlspecialchars($row['nama_alat'] ?? 'Alat') ?>">
                        </div>

                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($row['nama_alat'] ?? '-') ?></h3>
                            <p class="card-desc"><?= htmlspecialchars($row['deskripsi'] ?? 'Alat pertanian siap guna untuk efisiensi kerja di lahan.') ?></p>
                        </div>

                        <div class="card-footer">
                            <span class="<?= $badgeClass ?>"><?= $statusStok ?></span>
                            <?php if ((int)$stok > 0): ?>
                                <a href="<?= htmlspecialchars($linkPinjam, ENT_QUOTES, 'UTF-8') ?>" class="btn-pinjam">Pinjam Alat</a>
                            <?php else: ?>
                                <span class="btn-pinjam" aria-disabled="true" style="opacity:.55;cursor:not-allowed;">Stok Habis</span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 60px; background: #ffffff; border-radius: 14px; border: 1px solid var(--border-color);">
                    <i class="fa-solid fa-box-open" style="font-size: 36px; margin-bottom: 12px; color: #a0aec0;"></i>
                    <p style="margin: 0; font-size: 14px;">Maaf, alat pertanian yang Anda cari tidak ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> WaTani &middot; Sistem Peminjaman Alat Pertanian</p>
    </footer>

</body>
</html>