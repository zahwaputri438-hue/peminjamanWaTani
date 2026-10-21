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

if (empty($_SESSION["csrf_user"])) {
    $_SESSION["csrf_user"] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$sql = "SELECT id_user, username, nama_user, role FROM users ORDER BY id_user DESC";
$result = mysqli_query($koneksi, $sql);

if (!$result) {
    die("Data pengguna gagal dimuat. Periksa tabel users.");
}

$users = [];
$jumlahAdmin = 0;
$jumlahPetugas = 0;
$jumlahPeminjam = 0;

while ($user = mysqli_fetch_assoc($result)) {
    $users[] = $user;

    if ($user["role"] === "admin") {
        $jumlahAdmin++;
    } elseif ($user["role"] === "petugas") {
        $jumlahPetugas++;
    } elseif ($user["role"] === "peminjam") {
        $jumlahPeminjam++;
    }
}

$totalPengguna = count($users);
$pesan = $_GET["pesan"] ?? "";

$modeTambah = isset($_GET["tambah"]);
$modeEdit = isset($_GET["edit"]);
$idEdit = $modeEdit ? (int)$_GET["edit"] : 0;

$dataEdit = [
    "username" => "",
    "role" => "peminjam"
];

if ($modeEdit) {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT username, role FROM users WHERE id_user = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $idEdit);
    mysqli_stmt_execute($stmt);
    $editResult = mysqli_stmt_get_result($stmt);
    $found = mysqli_fetch_assoc($editResult);
    mysqli_stmt_close($stmt);

    if (!$found) {
        header("Location: pengguna.php?pesan=tidak_ditemukan");
        exit;
    }

    $dataEdit = $found;
}

$namaAdmin = $_SESSION["nama_user"] ?? "Admin Pertanian";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna | WaTani</title>

    <link rel="stylesheet" href="../assets/css/pengguna.css">
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="Logo WaTani" style="width: 40px; height: 40px; object-fit: contain;">
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

            <a href="pengguna.php" class="menu-link active">
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
                    <strong><?= htmlspecialchars($_SESSION["nama_user"] ?? "Administrator") ?></strong>
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
            <span>Halaman</span>
            <span class="breadcrumb-separator">/</span>
            <strong>Data Pengguna</strong>
        </div>

        <header class="page-header">
            <div>
                <p class="eyebrow">MANAJEMEN DATA</p>
                <h1>Data Pengguna</h1>
                <p class="page-description">
                    Kelola akun dan hak akses pengguna WaTani.
                </p>
            </div>

            <div class="date-card">
                <span class="date-icon">▦</span>
                <span><?= date("d M Y") ?></span>
            </div>
        </header>

        <?php if ($pesan === "berhasil"): ?>
            <div class="notice success">✓ Data pengguna berhasil diproses.</div>
        <?php elseif ($pesan === "gagal"): ?>
            <div class="notice error">Data gagal diproses. Periksa kembali input dan proses CRUD.</div>
        <?php elseif ($pesan === "diri"): ?>
            <div class="notice error">Akun yang sedang digunakan tidak dapat dihapus.</div>
        <?php elseif ($pesan === "tidak_ditemukan"): ?>
            <div class="notice error">Data pengguna tidak ditemukan.</div>
        <?php endif; ?>

        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon green">♙</div>
                <div class="stat-content">
                    <span>Total Pengguna</span>
                    <strong><?= $totalPengguna ?></strong>
                    <small>Semua akun terdaftar</small>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon orange">♟</div>
                <div class="stat-content">
                    <span>Administrator</span>
                    <strong><?= $jumlahAdmin ?></strong>
                    <small>Akun dengan akses admin</small>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon blue">♧</div>
                <div class="stat-content">
                    <span>Petugas & Peminjam</span>
                    <strong><?= $jumlahPetugas + $jumlahPeminjam ?></strong>
                    <small>Pengguna layanan WaTani</small>
                </div>
            </article>
        </section>

        <?php if ($modeTambah || $modeEdit): ?>
            <section class="panel form-panel">
                <div class="panel-header">
                    <div>
                        <h2><?= $modeEdit ? "Edit Pengguna" : "Tambah Pengguna" ?></h2>
                        <p>Lengkapi informasi akun di bawah ini.</p>
                    </div>
                    <a href="pengguna.php" class="close-link">✕</a>
                </div>

                <form action="crud_user.php" method="POST">
                    <input type="hidden" name="csrf_token"
                           value="<?= e($_SESSION["csrf_user"]) ?>">

                    <input type="hidden" name="aksi"
                           value="<?= $modeEdit ? "edit" : "tambah" ?>">

                    <?php if ($modeEdit): ?>
                        <input type="hidden" name="id_user" value="<?= $idEdit ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                maxlength="50"
                                value="<?= e($dataEdit["username"]) ?>"
                                placeholder="Masukkan username"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="role">Hak Akses</label>
                            <select id="role" name="role" required>
                                <?php foreach (["admin", "petugas", "peminjam"] as $role): ?>
                                    <option
                                        value="<?= e($role) ?>"
                                        <?= $dataEdit["role"] === $role ? "selected" : "" ?>
                                    >
                                        <?= ucfirst($role) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="password">
                                Password
                                <?= $modeEdit ? "(kosongkan jika tidak diubah)" : "" ?>
                            </label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                minlength="6"
                                autocomplete="new-password"
                                placeholder="Masukkan password"
                                <?= $modeEdit ? "" : "required" ?>
                            >
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="pengguna.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <section class="panel table-panel">
            <div class="panel-header table-heading">
                <div>
                    <h2>Daftar Pengguna</h2>
                    <p>Daftar akun yang memiliki akses ke sistem.</p>
                </div>

                <a href="pengguna.php?tambah=1" class="btn btn-primary">
                    <span>＋</span> Tambah Pengguna
                </a>
            </div>

            <div class="table-responsive">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th class="number-column">No.</th>
                            <th>Username</th>
                            <th>Hak Akses</th>
                            <th class="action-column">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalPengguna > 0): ?>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td class="number-cell"><?= $index + 1 ?></td>

                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                <?= e(strtoupper(substr($user["username"], 0, 1))) ?>
                                            </div>
                                            <span><?= e($user["username"]) ?></span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="role-badge role-<?= e($user["role"]) ?>">
                                            <?= e(ucfirst($user["role"])) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="row-actions">
                                            <a
                                                href="pengguna.php?edit=<?= (int)$user["id_user"] ?>"
                                                class="action-btn edit-btn"
                                            >Edit</a>

                                            <?php if ((int)$user["id_user"] !== (int)$_SESSION["id_user"]): ?>
                                                <form
                                                    action="crud_user.php"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');"
                                                >
                                                    <input type="hidden" name="csrf_token"
                                                           value="<?= e($_SESSION["csrf_user"]) ?>">
                                                    <input type="hidden" name="aksi" value="hapus">
                                                    <input type="hidden" name="id_user"
                                                           value="<?= (int)$user["id_user"] ?>">

                                                    <button type="submit" class="action-btn delete-btn">
                                                        Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <span>♙</span>
                                    <strong>Belum ada pengguna</strong>
                                    <small>Tambahkan pengguna untuk mulai mengelola akun.</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                Menampilkan <strong><?= $totalPengguna ?></strong> pengguna
            </div>
        </section>

        <footer class="page-footer">
            © <?= date("Y") ?> WaTani · Sistem Peminjaman Alat Pertanian
        </footer>
    </main>
</div>

</body>
</html>