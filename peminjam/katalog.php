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
$pesanError = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['aksi_pinjam'])) {
    $idAlatPinjam        = (int) $_POST['id_alat'];
    $tglPinjam           = mysqli_real_escape_string($koneksi, $_POST['tgl_pinjam']);
    $tglRencana          = mysqli_real_escape_string($koneksi, $_POST['tgl_kembali_rencana']);
    $jaminan             = mysqli_real_escape_string($koneksi, trim($_POST['jaminan']));

    if (empty($tglPinjam) || empty($tglRencana) || empty($jaminan)) {
        $pesanError = "Semua field (tanggal dan jaminan) wajib diisi!";
    } else {
        $insertPeminjaman = mysqli_query($koneksi, "
            INSERT INTO peminjaman (id_user, tgl_pinjam, tgl_kembali_rencana, jaminan, status) 
            VALUES ($idUser, '$tglPinjam', '$tglRencana', '$jaminan', 'Pending')
        ");

        if ($insertPeminjaman) {
            $idPeminjamanBaru = mysqli_insert_id($koneksi);
            mysqli_query($koneksi, "
                INSERT INTO detail_peminjaman (id_peminjaman, id_alat) 
                VALUES ($idPeminjamanBaru, $idAlatPinjam)
            ");

            echo "<script>alert('Pengajuan peminjaman berhasil dikirim! Menunggu verifikasi petugas.'); window.location='riwayat.php';</script>";
            exit;
        } else {
            $pesanError = "Gagal memproses peminjaman: " . mysqli_error($koneksi);
        }
    }
}

$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
if (!empty($keyword)) {
    $keywordEsc = mysqli_real_escape_string($koneksi, $keyword);
    $whereClause = "WHERE nama_alat LIKE '%$keywordEsc%' OR deskripsi LIKE '%$keywordEsc%'";
}

$queryAlat = mysqli_query($koneksi, "SELECT * FROM alat $whereClause ORDER BY id_alat DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Katalog & Peminjaman Alat | WaTani</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .page-footer { margin-top: 40px; text-align: center; font-size: 13px; color: var(--muted); border-top: 1px solid var(--border); padding-top: 20px; }
        .filter-container { display: flex; gap: 10px; margin-bottom: 24px; align-items: center; justify-content: space-between; background: var(--white); padding: 15px 20px; border-radius: 12px; border: 1px solid var(--border); }
        .search-box-inline { display: flex; gap: 8px; width: 100%; }
        .search-input { flex: 1; padding: 9px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; outline: none; }
        .search-input:focus { border-color: var(--green); }
        .btn-search { background: #176638; color: white; border: none; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-reset { background: #e2e8f0; color: #475569; text-decoration: none; padding: 9px 15px; border-radius: 8px; font-size: 13px; display: flex; align-items: center; }
        .alat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .alat-card { background: var(--white); border: 1px solid var(--border); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .alat-image-box { width: 100%; height: 150px; background: #fdfdfd; border: 1px solid #edf2ed; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; overflow: hidden; }
        .alat-image-box img { width: 100%; height: 100%; object-fit: contain; }
        .alat-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .alat-desc { font-size: 11px; color: var(--muted); line-height: 1.5; margin-bottom: 16px; }
        .alat-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid var(--border); }
        .stok-text { font-size: 12px; font-weight: 700; color: #176638; }
        .btn-pinjam { background: #176638; color: white; padding: 7px 14px; border-radius: 20px; text-decoration: none; font-size: 12px; font-weight: 600; cursor: pointer; border: none; }
        .btn-pinjam:hover { background: #124d2a; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-box { background: white; padding: 25px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: var(--text); }
        .form-group input, .form-group select { width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; box-sizing: border-box; }
    </style>
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
            <a href="dashboard.php" class="menu-link"><span class="menu-icon">▦</span> Dashboard</a>
            <a href="katalog.php" class="menu-link active"><span class="menu-icon">🚜</span> Katalog Alat</a>
            <div class="sidebar-label">PEMINJAMAN SAYA</div>
            <a href="riwayat.php" class="menu-link"><span class="menu-icon">📋</span> Status & Riwayat</a>
        </nav>
        <div class="sidebar-bottom">
            <div class="admin-mini">
                <div class="admin-avatar">P</div>
                <div class="admin-info">
                    <strong><?= htmlspecialchars($namaPetani) ?></strong>
                    <small>Petani / Peminjam</small>
                </div>
            </div>
            <a href="../logout.php" class="logout-link" onclick="return confirm('Yakin ingin keluar?')">↪ Keluar</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="breadcrumb">Halaman / <strong>Katalog Alat Pertanian</strong></div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
            <div>
                <p class="eyebrow" style="font-size: 10px; font-weight: 700; color: var(--green); margin-bottom: 4px;">KATALOG WATANI</p>
                <h1 style="font-size: 25px; font-weight: 700; color: var(--text);">Daftar Alat Tersedia</h1>
            </div>
            <div class="date-card" style="padding: 9px 13px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); font-size: 11px;">
                📅 <?= htmlspecialchars($tanggal) ?>
            </div>
        </header>

        <?php if (!empty($pesanError)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
                ⚠️ <?= $pesanError ?>
            </div>
        <?php endif; ?>

        <div class="filter-container">
            <form action="" method="GET" class="search-box-inline">
                <input type="text" name="search" class="search-input" placeholder="Cari nama alat pertanian..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit" class="btn-search">Cari</button>
                <?php if (!empty($keyword)): ?>
                    <a href="katalog.php" class="btn-reset">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="alat-grid">
            <?php if ($queryAlat && mysqli_num_rows($queryAlat) > 0): ?>
                <?php while ($alat = mysqli_fetch_assoc($queryAlat)): ?>
                    <div class="alat-card">
                        <div>
                            <div class="alat-image-box">
                                <?php if (!empty($alat['gambar']) && file_exists("../assets/images/alat/" . $alat['gambar'])): ?>
                                    <img src="../assets/images/alat/<?= htmlspecialchars($alat['gambar']) ?>" alt="Alat">
                                <?php else: ?>
                                    <span style="font-size: 36px;">🚜</span>
                                <?php endif; ?>
                            </div>
                            <div class="alat-title"><?= htmlspecialchars($alat['nama_alat']); ?></div>
                            <div class="alat-desc"><?= htmlspecialchars(substr($alat['deskripsi'] ?? 'Alat siap guna.', 0, 80)); ?>...</div>
                        </div>
                        <div class="alat-footer">
                            <span class="stok-text">Stok: <?= htmlspecialchars($alat['stok'] ?? '0'); ?></span>
                            <!-- Tombol memicu modal popup -->
                            <button type="button" class="btn-pinjam" onclick="bukaModal(<?= $alat['id_alat']; ?>, '<?= htmlspecialchars($alat['nama_alat'], ENT_QUOTES); ?>')">Pinjam Alat</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; background: var(--white); border: 1px solid var(--border); padding: 40px; text-align: center; color: var(--muted); border-radius: 12px;">
                    Tidak ada alat ditemukan.
                </div>
            <?php endif; ?>
        </div>

        <div id="modalPinjam" class="modal-overlay">
            <div class="modal-box">
                <h3 style="margin-top: 0; color: var(--text); margin-bottom: 4px;">Form Peminjaman Alat</h3>
                <p id="namaAlatModal" style="color: #176638; font-weight: 700; font-size: 14px; margin-bottom: 20px;"></p>

                <form action="" method="POST">
                    <input type="hidden" name="aksi_pinjam" value="1">
                    <input type="hidden" name="id_alat" id="modalIdAlat">

                    <div class="form-group">
                        <label>Tanggal Mulai Pinjam</label>
                        <input type="date" name="tgl_pinjam" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Rencana Tanggal Pengembalian</label>
                        <input type="date" name="tgl_kembali_rencana" required min="<?= date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Jaminan yang Diserahkan</label>
                        <select name="jaminan" required>
                            <option value="">-- Pilih Jenis Jaminan --</option>
                            <option value="KTP Asli">KTP Asli</option>
                            <option value="SIM Asli">SIM Asli</option>
                            <option value="STNK Kendaraan">STNK Kendaraan</option>
                            <option value="Kartu Identitas Lainnya">Kartu Identitas Lainnya / Surat Desa</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn-search" style="flex: 1;">Kirim Pengajuan</button>
                        <button type="button" onclick="tutupModal()" style="background: #e2e8f0; color: #475569; border: none; padding: 9px 16px; border-radius: 8px; font-weight: 600; cursor: pointer;">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <footer class="page-footer">© <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian</footer>
    </main>
</div>

<script>
function bukaModal(idAlat, namaAlat) {
    document.getElementById('modalIdAlat').value = idAlat;
    document.getElementById('namaAlatModal').innerText = 'Alat: ' + namaAlat;
    document.getElementById('modalPinjam').style.display = 'flex';
}
function tutupModal() {
    document.getElementById('modalPinjam').style.display = 'none';
}
</script>

</body>
</html>