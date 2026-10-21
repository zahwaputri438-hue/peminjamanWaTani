<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (
    !isset($_SESSION["id_user"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../login.php");
    exit;
}

if (empty($_SESSION["csrf_alat"])) {
    $_SESSION["csrf_alat"] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$sql = "SELECT a.*, COALESCE(k.nama_kategori, 'Tanpa Kategori') AS nama_kategori 
        FROM alat a 
        LEFT JOIN kategori k ON a.id_kategori = k.id_kategori 
        ORDER BY a.id_alat DESC";
$result = mysqli_query($koneksi, $sql);

if (!$result) {
    $sql = "SELECT * FROM alat ORDER BY id_alat DESC";
    $result = mysqli_query($koneksi, $sql);
}

$alatList = [];
$totalAlat = 0;
$totalStok = 0;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $alatList[] = $row;
        $totalAlat++;
        $stok = isset($row['stok']) ? (int)$row['stok'] : 0;
        $totalStok += $stok;
    }
}

$pesan = $_GET["pesan"] ?? "";
$modeTambah = isset($_GET["tambah"]);
$modeEdit = isset($_GET["edit"]);
$idEdit = $modeEdit ? (int)$_GET["edit"] : 0;

$dataEdit = [
    "nama_alat" => "",
    "id_kategori" => "",
    "stok" => "",
    "kondisi" => "Baik",
    "keterangan" => "",
    "gambar" => ""
];

if ($modeEdit) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM alat WHERE id_alat = ?");
    mysqli_stmt_bind_param($stmt, "i", $idEdit);
    mysqli_stmt_execute($stmt);
    $editResult = mysqli_stmt_get_result($stmt);
    $found = mysqli_fetch_assoc($editResult);
    mysqli_stmt_close($stmt);

    if (!$found) {
        header("Location: alat.php?pesan=tidak_ditemukan");
        exit;
    }
    $dataEdit = $found;
}

$kategoriList = [];
$qKat = mysqli_query($koneksi, "SELECT * FROM kategori");
if ($qKat) {
    while ($kat = mysqli_fetch_assoc($qKat)) {
        $kategoriList[] = $kat;
    }
}

$namaAdmin = $_SESSION["nama_user"] ?? "Admin Pertanian";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Alat Pertanian | WaTani</title>
    <link rel="stylesheet" href="../assets/css/alat.css">
    <style>
        .alat-table th, 
        .alat-table td {
            vertical-align: middle;
        }

        .alat-table th:nth-child(1), .alat-table td:nth-child(1), 
        .alat-table th:nth-child(2), .alat-table td:nth-child(2), 
        .alat-table th:nth-child(5), .alat-table td:nth-child(5), 
        .alat-table th:nth-child(6), .alat-table td:nth-child(6), 
        .alat-table th:nth-child(7), .alat-table td:nth-child(7) {
            text-align: center;
        }

        .alat-table td:nth-child(2) img,
        .alat-table td:nth-child(2) div {
            margin: 0 auto;
        }

        .modal-img-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }
        .modal-img-content {
            max-width: 85%;
            max-height: 85%;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.5);
            animation: zoomIn 0.2s ease-in-out;
        }
        .modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        @keyframes zoomIn {
            from {transform: scale(0.8); opacity: 0;}
            to {transform: scale(1); opacity: 1;}
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

            <a href="alat.php" class="menu-link active">
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

            <a href="../logout.php" class="logout-link"
               onclick="return confirm('Yakin ingin keluar dari WaTani?')">
                ↪ Keluar
            </a>
        </div>
    </aside>

    <main class="main-content">

        <div class="breadcrumb">
            Halaman / <strong>Data Alat Pertanian</strong>
        </div>

        <header class="page-header">
            <div>
                <p class="eyebrow">INVENTARIS</p>
                <h1>Data Alat Pertanian</h1>
                <p class="page-description">
                    Kelola daftar inventaris dan ketersediaan alat pertanian WaTani.
                </p>
            </div>

            <div class="date-card">
                <span class="date-icon">▦</span>
                <span><?= date("d M Y") ?></span>
            </div>
        </header>

        <?php if ($pesan === "berhasil"): ?>
            <div class="notice success">✓ Data alat berhasil diproses.</div>
        <?php elseif ($pesan === "gagal"): ?>
            <div class="notice error">Data alat gagal diproses. Periksa kembali input Anda.</div>
        <?php elseif ($pesan === "tidak_ditemukan"): ?>
            <div class="notice error">Data alat pertanian tidak ditemukan.</div>
        <?php endif; ?>

        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon green">⚒</div>
                <div class="stat-content">
                    <span>Total Jenis Alat</span>
                    <strong><?= $totalAlat ?></strong>
                    <small>Jenis alat terdaftar</small>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon orange">📦</div>
                <div class="stat-content">
                    <span>Akumulasi Stok</span>
                    <strong><?= $totalStok ?></strong>
                    <small>Total unit keseluruhan</small>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon blue">▤</div>
                <div class="stat-content">
                    <span>Kategori Alat</span>
                    <strong><?= count($kategoriList) ?></strong>
                    <small>Kelompok inventaris</small>
                </div>
            </article>
        </section>

        <?php if ($modeTambah || $modeEdit): ?>
            <section class="panel form-panel">
                <div class="panel-header">
                    <div>
                        <h2><?= $modeEdit ? "Edit Alat Pertanian" : "Tambah Alat Pertanian" ?></h2>
                        <p>Lengkapi informasi inventaris alat di bawah ini.</p>
                    </div>
                    <a href="alat.php" class="close-link">✕</a>
                </div>

                <form action="crud_alat.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_alat"]) ?>">
                    <input type="hidden" name="aksi" value="<?= $modeEdit ? "edit" : "tambah" ?>">

                    <?php if ($modeEdit): ?>
                        <input type="hidden" name="id_alat" value="<?= $idEdit ?>">
                        <input type="hidden" name="gambar_lama" value="<?= e($dataEdit["gambar"] ?? "") ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="nama_alat">Nama Alat</label>
                            <input
                                id="nama_alat"
                                name="nama_alat"
                                type="text"
                                value="<?= e($dataEdit["nama_alat"]) ?>"
                                placeholder="Contoh: Traktor Roda Empat / Cangkul Baja"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="id_kategori">Kategori Alat</label>
                            <select id="id_kategori" name="id_kategori" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategoriList as $kat): ?>
                                    <option
                                        value="<?= (int)$kat["id_kategori"] ?>"
                                        <?= (isset($dataEdit["id_kategori"]) && $dataEdit["id_kategori"] == $kat["id_kategori"]) ? "selected" : "" ?>
                                    >
                                        <?= e($kat["nama_kategori"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="stok">Jumlah Stok (Unit)</label>
                            <input
                                id="stok"
                                name="stok"
                                type="number"
                                min="0"
                                value="<?= e($dataEdit["stok"] ?? "") ?>"
                                placeholder="Jumlah unit tersedia"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="kondisi">Kondisi Alat</label>
                            <select id="kondisi" name="kondisi" required>
                                <option value="Baik" <?= ($dataEdit["kondisi"] ?? "") === "Baik" ? "selected" : "" ?>>Baik</option>
                                <option value="Perbaikan" <?= ($dataEdit["kondisi"] ?? "") === "Perbaikan" ? "selected" : "" ?>>Dalam Perbaikan</option>
                                <option value="Rusak" <?= ($dataEdit["kondisi"] ?? "") === "Rusak" ? "selected" : "" ?>>Rusak</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="gambar">Foto / Gambar Alat (Opsional)</label>
                            <input id="gambar" name="gambar" type="file" accept="image/*">
                            <small style="color: var(--muted); font-size: 10px; margin-top: 4px; display: block;">Format: JPG, JPEG, PNG, WEBP, JFIF</small>
                        </div>

                        <div class="form-group full-width">
                            <label for="keterangan">Keterangan / Spesifikasi Singkat</label>
                            <textarea
                                id="keterangan"
                                name="keterangan"
                                placeholder="Catatan tambahan mengenai kondisi atau fungsi alat..."
                            ><?= e($dataEdit["keterangan"] ?? "") ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="alat.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Alat</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-header table-heading">
                <div>
                    <h2>Daftar Inventaris Alat</h2>
                    <p>Seluruh alat pertanian yang terdaftar dalam sistem.</p>
                </div>

                <a href="alat.php?tambah=1" class="btn btn-primary">
                    <span>＋</span> Tambah Alat
                </a>
            </div>

            <div class="table-responsive">
                <table class="alat-table">
                    <thead>
                        <tr>
                            <th class="number-column">No.</th>
                            <th>Foto</th>
                            <th>Nama Alat</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Kondisi</th>
                            <th class="action-column">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalAlat > 0): ?>
                            <?php foreach ($alatList as $index => $alat): ?>
                                <tr>
                                    <td class="number-cell"><?= $index + 1 ?></td>
                                    <td>
                                        <?php if (!empty($alat["gambar"])): ?>
                                            <img src="../assets/images/alat/<?= e($alat["gambar"]) ?>" alt="Foto Alat" style="width: 55px; height: 55px; object-fit: cover; border-radius: 10px; border: 1px solid var(--border); cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="bukaModal('../assets/images/alat/<?= e($alat["gambar"]) ?>')">
                                        <?php else: ?>
                                            <div class="alat-icon-cell" style="width: 55px; height: 55px; font-size: 22px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #f3f4f6;">🚜</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="alat-cell">
                                            <span><?= e($alat["nama_alat"]) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-kategori">
                                            <?= e($alat["nama_kategori"] ?? "Tanpa Kategori") ?>
                                        </span>
                                    </td>
                                    <td><strong><?= e($alat["stok"]) ?></strong> Unit</td>
                                    <td>
                                        <?php 
                                            $kondisi = $alat["kondisi"] ?? "Baik";
                                            $badgeClass = ($kondisi === "Baik") ? "role-admin" : "role-petugas";
                                        ?>
                                        <span class="role-badge <?= $badgeClass ?>">
                                            <?= e($kondisi) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="row-actions" style="justify-content: center;">
                                            <a href="alat.php?edit=<?= (int)$alat["id_alat"] ?>" class="action-btn edit-btn">Edit</a>
                                            
                                            <form action="crud_alat.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus alat ini?');" style="display: inline-block;">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_alat"]) ?>">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id_alat" value="<?= (int)$alat["id_alat"] ?>">
                                                <input type="hidden" name="gambar_lama" value="<?= e($alat["gambar"] ?? "") ?>">
                                                <button type="submit" class="action-btn delete-btn">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <span>⚒</span>
                                    <strong>Belum ada data alat pertanian</strong>
                                    <small>Tambahkan alat baru untuk mulai mengisi inventaris.</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                Menampilkan <strong><?= $totalAlat ?></strong> jenis alat pertanian
            </div>
        </section>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>
    </main>
</div>

<div id="imgModal" class="modal-img-overlay" onclick="tutupModal()">
    <span class="modal-close">&times;</span>
    <img class="modal-img-content" id="img01">
</div>

<script>
function bukaModal(src) {
    var modal = document.getElementById("imgModal");
    var modalImg = document.getElementById("img01");
    modal.style.display = "flex";
    modalImg.src = src;
}

function tutupModal() {
    document.getElementById("imgModal").style.display = "none";
}
</script>

</body>
</html>