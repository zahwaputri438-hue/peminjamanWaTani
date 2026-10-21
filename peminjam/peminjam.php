<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'peminjam') {
    $id_alat = $_GET['id_alat'] ?? '';
    header("Location: ../login.php?redirect=peminjam/peminjam.php?id_alat=" . $id_alat);
    exit;
}

$id_user    = $_SESSION['id_user'];
$namaPetani = $_SESSION['nama_user'] ?? $_SESSION['username'] ?? 'Petani WaTani';
$tanggal    = date("d M Y");

$id_alat = $_GET['id_alat'] ?? '';
$error   = '';

$alat = null;
if (!empty($id_alat)) {
    $id_alat_clean = mysqli_real_escape_string($koneksi, $id_alat);
    $q_alat = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat = '$id_alat_clean'");
    if ($q_alat && mysqli_num_rows($q_alat) > 0) {
        $alat = mysqli_fetch_assoc($q_alat);
    }
}

if (!$alat) {
    echo "<script>alert('Silakan pilih alat dari katalog terlebih dahulu!'); window.location='katalog.php';</script>";
    exit;
}

$namaGambar = $alat['gambar'] ?? '';
$gambarFile = (!empty($namaGambar) && file_exists(__DIR__ . "/../assets/images/alat/" . $namaGambar)) 
              ? "../assets/images/alat/" . $namaGambar 
              : "../assets/images/logo.png";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl_pinjam          = mysqli_real_escape_string($koneksi, $_POST['tgl_pinjam']);
    $tgl_kembali_rencana = mysqli_real_escape_string($koneksi, $_POST['tgl_kembali_rencana']);
    $jaminan             = mysqli_real_escape_string($koneksi, trim($_POST['jaminan']));

    if (empty($tgl_pinjam) || empty($tgl_kembali_rencana) || empty($jaminan)) {
        $error = "Semua field (termasuk jaminan) wajib diisi!";
    } elseif (($alat['stok'] ?? 0) <= 0) {
        $error = "Maaf, stok alat ini sedang habis dan tidak dapat dipinjam.";
    } else {
        $query_peminjaman = "INSERT INTO peminjaman (id_user, tgl_pinjam, tgl_kembali_rencana, jaminan, status) 
                             VALUES ('$id_user', '$tgl_pinjam', '$tgl_kembali_rencana', '$jaminan', 'Pending')";

        if (mysqli_query($koneksi, $query_peminjaman)) {
            $id_peminjaman_baru = mysqli_insert_id($koneksi);
            $id_alat_target     = mysqli_real_escape_string($koneksi, $alat['id_alat']);

            $query_detail = "INSERT INTO detail_peminjaman (id_peminjaman, id_alat) 
                             VALUES ('$id_peminjaman_baru', '$id_alat_target')";
            
            if (mysqli_query($koneksi, $query_detail)) {
                echo "<script>alert('Pengajuan peminjaman berhasil dikirim! Menunggu konfirmasi petugas.'); window.location='riwayat.php';</script>";
                exit;
            } else {
                $error = "Gagal menyimpan detail alat: " . mysqli_error($koneksi);
            }
        } else {
            $error = "Gagal mengajukan peminjaman: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman Alat | WaTani</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .form-card-container {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #edf2f7;
            margin-top: 15px;
        }

        .selected-tool-preview {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #f8faf9;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .tool-img-box {
            width: 110px;
            height: 110px;
            background: #ffffff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border: 1px solid #edf2f7;
            flex-shrink: 0;
        }

        .tool-img-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .tool-info-text h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 6px 0;
        }

        .tool-info-text p {
            font-size: 13px;
            color: #718096;
            margin: 0 0 10px 0;
            line-height: 1.4;
        }

        .badge-stok {
            display: inline-block;
            background: #e6f4ea;
            color: #137333;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 13px;
            color: #2d3748;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            color: #a0aec0;
            font-size: 15px;
        }

        .form-control-custom {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1px solid #cbd5e0;
            border-radius: 10px;
            font-size: 14px;
            background: #ffffff;
            color: #2d3748;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-control-custom:focus {
            border-color: #176638;
            box-shadow: 0 0 0 3px rgba(23, 102, 56, 0.12);
        }

        select.form-control-custom {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%3Ca0aec0' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        .btn-green {
            background: #176638;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(23, 102, 56, 0.2);
        }

        .btn-green:hover {
            background: #114e2a;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #edf2f7;
            color: #4a5568;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: #2d3748;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
            .selected-tool-preview {
                flex-direction: column;
                text-align: center;
            }
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

            <a href="katalog.php" class="menu-link active">
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
            Halaman / Katalog / <strong>Form Peminjaman Alat</strong>
        </div>

        <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <p class="eyebrow" style="font-size: 11px; font-weight: 700; color: #176638; letter-spacing: 0.05em; margin-bottom: 4px;">KATALOG WATANI</p>
                <h1 style="font-size: 26px; font-weight: 800; color: #1a202c; margin: 0;">Form Peminjaman Alat</h1>
                <p class="page-description" style="font-size: 13px; color: #718096; margin-top: 4px;">
                    Lengkapi jadwal dan jaminan untuk pengajuan peminjaman alat pertanian.
                </p>
            </div>

            <div class="date-card" style="display: flex; align-items: center; gap: 8px; padding: 8px 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff; color: #4a5568; font-size: 13px;">
                <i class="fa-regular fa-calendar-days" style="color: #176638;"></i>
                <span><?= htmlspecialchars($tanggal) ?></span>
            </div>
        </header>

        <div class="form-card-container">
            <?php if ($error !== ''): ?>
                <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
                    ⚠️ <?= $error; ?>
                </div>
            <?php endif; ?>

            <div class="selected-tool-preview">
                <div class="tool-img-box">
                    <img src="<?= $gambarFile; ?>" alt="<?= htmlspecialchars($alat['nama_alat']); ?>">
                </div>
                <div class="tool-info-text">
                    <h3><?= htmlspecialchars($alat['nama_alat']); ?></h3>
                    <p><?= htmlspecialchars($alat['deskripsi'] ?? 'Alat pertanian modern siap pakai untuk meningkatkan efisiensi lahan Anda.'); ?></p>
                    <span class="badge-stok">
                        <i class="fa-solid fa-boxes-stacked"></i> Stok Tersedia: <?= htmlspecialchars($alat['stok'] ?? '0'); ?> Unit
                    </span>
                </div>
            </div>

            <form action="" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tanggal Mulai Pinjam</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-calendar"></i>
                            <input type="date" name="tgl_pinjam" value="<?= date('Y-m-d'); ?>" class="form-control-custom" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Rencana Tanggal Pengembalian</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-calendar-check"></i>
                            <input type="date" name="tgl_kembali_rencana" class="form-control-custom" required min="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Jaminan yang Diserahkan</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-id-card"></i>
                            <select name="jaminan" class="form-control-custom" required>
                                <option value="">-- Pilih Jenis Jaminan --</option>
                                <option value="KTP Asli">KTP Asli</option>
                                <option value="SIM Asli">SIM Asli</option>
                                <option value="STNK Kendaraan">STNK Kendaraan</option>
                                <option value="Kartu Identitas Lainnya">Kartu Identitas Lainnya / Surat Desa</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn-green" <?= (($alat['stok'] ?? 0) <= 0) ? 'disabled' : ''; ?>>
                        <i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan Peminjaman
                    </button>
                    <a href="katalog.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>

        <footer class="page-footer" style="margin-top: 40px; text-align: center; font-size: 13px; color: #a0aec0; border-top: 1px solid #edf2f7; padding-top: 20px;">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>

    </main>
</div>

</body>
</html>