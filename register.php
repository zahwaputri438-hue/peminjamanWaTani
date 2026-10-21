<?php
session_start();
require_once "koneksi.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama_user = trim($_POST['nama_user'] ?? '');
    $role = 'peminjam';

    if ($username === '' || $password === '' || $nama_user === '') {
        $error = "Semua kolom wajib diisi.";
    } else {
        $stmtCek = mysqli_prepare($koneksi, "SELECT id_user FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmtCek, "s", $username);
        mysqli_stmt_execute($stmtCek);
        mysqli_stmt_store_result($stmtCek);

        if (mysqli_stmt_num_rows($stmtCek) > 0) {
            $error = "Username sudah digunakan, silakan pilih yang lain.";
        } else {
            $stmtInsert = mysqli_prepare($koneksi, "INSERT INTO users (username, password, nama_user, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmtInsert, "ssss", $username, $password, $nama_user, $role);
            
            if (mysqli_stmt_execute($stmtInsert)) {
                $success = "Registrasi berhasil! Silakan <a href='login.php' style='color: var(--green); font-weight: 600;'>masuk di sini</a>.";
            } else {
                $error = "Terjadi kesalahan, silakan coba lagi.";
            }
            mysqli_stmt_close($stmtInsert);
        }
        mysqli_stmt_close($stmtCek);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#176638">
    <title>Daftar Akun | WaTani</title>
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .login-error {
            margin: 0 0 12px;
            padding: 9px 12px;
            border: 1px solid #f1caca;
            border-radius: 8px;
            background: #fff0f0;
            color: #9f2525;
            font-size: 11px;
            line-height: 1.6;
        }
        .login-success {
            margin: 0 0 12px;
            padding: 9px 12px;
            border: 1px solid #c6f6d5;
            border-radius: 8px;
            background: #f0fff4;
            color: #22543d;
            font-size: 11px;
            line-height: 1.6;
        }
    </style>
</head>

<body>

    <main class="login-card">

        <section class="left-panel">

            <a href="index.php" class="logo">
                <img src="assets/images/logo.png" alt="Logo WaTani" style="width: 42px; height: 42px; object-fit: contain; border-radius: 6px;">
                <span>
                    <strong>WaTani</strong>
                    <small>Sistem Peminjaman Alat Pertanian</small>
                </span>
            </a>

            <div class="form-area">

                <p class="welcome">Pendaftaran Mandiri</p>

                <h1>Buat Akun Baru</h1>

                <p class="description">
                    Daftar sekarang untuk mulai meminjam alat pertanian dengan mudah.
                </p>

                <?php if ($error !== ""): ?>
                    <div class="login-error" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success !== ""): ?>
                    <div class="login-success" role="alert">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?= $success ?></span>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" autocomplete="on">

                    <div class="field">
                        <label for="nama_user">Nama Lengkap</label>
                        <div class="input-box">
                            <span class="field-icon" aria-hidden="true">
                                <i class="fa-regular fa-id-badge"></i>
                            </span>
                            <input
                                type="text"
                                id="nama_user"
                                name="nama_user"
                                placeholder="Masukkan nama lengkap"
                                value="<?= htmlspecialchars($_POST["nama_user"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="username">Username</label>
                        <div class="input-box">
                            <span class="field-icon" aria-hidden="true">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Buat username unik"
                                value="<?= htmlspecialchars($_POST["username"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-box">
                            <span class="field-icon" aria-hidden="true">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Buat password"
                                required
                            >
                        </div>
                    </div>

                    <div class="forgot-row" style="margin-bottom: 15px;"></div>

                    <button type="submit" name="register" class="login-button">
                        <span>Daftar Sekarang</span>
                        <span class="arrow" aria-hidden="true">→</span>
                    </button>

                </form>

                <p class="contact">
                    Sudah punya akun?
                    <strong><a href="login.php" style="color: var(--green); text-decoration: none;">Masuk di sini</a></strong>
                </p>

            </div>

            <div class="left-footer">
                © <?= date("Y") ?> WaTani. Seluruh hak cipta dilindungi.
            </div>

            <div class="leaf-decoration" aria-hidden="true">🌿</div>

        </section>

        <section class="right-panel">

            <div class="right-logo">
                <div>
                    <strong>WaTani</strong>
                    <small>Untuk Petani, Bersama Petani</small>
                </div>
            </div>

            <div class="farm-art">
                <img
                    src="assets/images/petani.png"
                    alt="Ilustrasi petani dan hasil pertanian"
                >
            </div>

            <div class="promo">
                <h2>
                    Solusi Peminjaman<br>
                    Alat Pertanian Modern
                </h2>

                <p>
                    Nikmati kemudahan akses alat-alat pertanian berkualitas tinggi secara mandiri.
                </p>
            </div>

            <div class="features">
                <div class="feature">
                    <span class="feature-icon" aria-hidden="true">🌾</span>
                    <span>Praktis &amp; Cepat</span>
                </div>
                <div class="feature">
                    <span class="feature-icon" aria-hidden="true">🚜</span>
                    <span>Alat Berkualitas</span>
                </div>
                <div class="feature">
                    <span class="feature-icon" aria-hidden="true">👨‍🌾</span>
                    <span>Mandiri</span>
                </div>
            </div>

        </section>

    </main>

</body>
</html>