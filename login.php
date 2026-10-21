<?php
session_start();
require_once "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($password)) {
        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        $user  = mysqli_fetch_assoc($query);

        if ($user) {
            $is_password_valid = (md5($password) === $user["password"]) || 
                                 password_verify($password, $user["password"]) || 
                                 ($password === $user["password"]);

            if ($is_password_valid) {
                $_SESSION["login"]     = true;
                $_SESSION["id_user"]   = $user["id_user"];
                $_SESSION["username"]  = $user["username"];
                $_SESSION["nama_user"] = $user["nama_user"] ?? $user["username"];
                
                $role = strtolower(trim($user["role"]));
                $_SESSION["role"] = $role;

                if ($role === "admin") {
                    header("Location: admin/dashboard.php");
                    exit;
                } elseif ($role === "petugas") {
                    header("Location: petugas/dashboard.php");
                    exit;
                } else {
                   if (isset($_GET['id_alat']) && !empty($_GET['id_alat'])) {
                        header("Location: peminjam/peminjam.php?id_alat=" . $_GET['id_alat']);
                        exit;
                    }
                    
                    $redirect_url = $_POST["redirect"] ?? $_GET["redirect"] ?? "";
                    if (!empty($redirect_url)) {
                        header("Location: " . $redirect_url);
                        exit;
                    } else {
                        header("Location: peminjam/katalog.php");
                        exit;
                    }
                }
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Username '$username' tidak ditemukan di database!";
        }
    } else {
        $error = "Semua kolom wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#176638">
    <title>Login | WaTani</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<main class="login-card">

    <section class="left-panel">
        <a href="index.php" class="logo">
            <img src="assets/images/logo.png"
                 alt="Logo WaTani"
                 style="width:42px;height:42px;object-fit:contain;border-radius:6px;"
                 onerror="this.style.display='none'">
            <span>
                <strong>WaTani</strong>
                <small>Sistem Peminjaman Alat Pertanian</small>
            </span>
        </a>

        <div class="form-area">
            <p class="welcome">Selamat Datang</p>
            <h1>Masuk ke Akun</h1>
            <p class="description">
                Silakan masukkan username dan password untuk melanjutkan.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="color: #9f2525; background: #ffe6e6; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center; border: 1px solid #f5c6cb;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" method="POST" autocomplete="on">
                <input type="hidden" name="redirect"
                       value="<?= htmlspecialchars(
                           $_POST["redirect"] ?? $_GET["redirect"] ?? (isset($_GET['id_alat']) ? 'peminjam/peminjaman.php?id_alat=' . $_GET['id_alat'] : ''),
                           ENT_QUOTES,
                           "UTF-8"
                       ) ?>">

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-box">
                        <span class="field-icon">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text"
                               id="username"
                               name="username"
                               placeholder="Masukkan username"
                               value="<?= htmlspecialchars(
                                   $_POST["username"] ?? "",
                                   ENT_QUOTES,
                                   "UTF-8"
                               ) ?>"
                               autocomplete="username"
                               required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-box">
                        <span class="field-icon">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Masukkan password"
                               autocomplete="current-password"
                               required>
                        <button type="button"
                                class="password-toggle"
                                id="togglePassword"
                                aria-label="Tampilkan password"
                                aria-pressed="false">
                            <i class="fa-regular fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-button"
                        style="margin-top:15px;">
                    <span>Masuk</span>
                </button>
            </form>

            <p class="contact" style="margin-top:15px;font-size:13px;">
                Belum punya akun?
                <strong>
                    <a href="register.php"
                       style="color:#176638;text-decoration:none;">
                        Daftar di sini
                    </a>
                </strong>
            </p>

            <p class="back-home" style="margin-top:10px;font-size:13px;">
                <a href="index.php"
                   style="color:#6b7c76;text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Beranda &amp; Katalog
                </a>
            </p>
        </div>

        <div class="left-footer">
            &copy; <?= date("Y") ?> WaTani. Seluruh hak cipta dilindungi.
        </div>
    </section>

    <section class="right-panel">
        <div class="right-logo">
            <div>
                <strong>WaTani</strong>
                <small>Untuk Petani, Bersama Petani</small>
            </div>
        </div>

        <div class="farm-art">
            <img src="assets/images/petani.png"
                 alt="Ilustrasi petani dan hasil pertanian"
                 style="max-width:330px;max-height:230px;width:100%;height:auto;object-fit:contain;"
                 onerror="this.style.display='none'">
        </div>

        <div class="promo">
            <h2>Alat Pertanian untuk<br>Masa Depan yang Lebih Baik</h2>
            <p>
                Temukan dan pinjam alat pertanian dengan mudah.
                Bersama WaTani, dukung produktivitas petani Indonesia.
            </p>
        </div>

        <div class="features">
            <div class="feature">
                <span class="feature-icon">🌱</span>
                <span>Mudah<br>Digunakan</span>
            </div>
            <div class="feature">
                <span class="feature-icon">🚜</span>
                <span>Pilihan Alat<br>Lengkap</span>
            </div>
            <div class="feature">
                <span class="feature-icon">🤝</span>
                <span>Mendukung<br>Petani Indonesia</span>
            </div>
        </div>
    </section>
</main>

<script>
const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
const eyeIcon = document.getElementById("eyeIcon");

togglePassword.addEventListener("click", function () {
    const tersembunyi = passwordInput.type === "password";

    passwordInput.type = tersembunyi ? "text" : "password";
    eyeIcon.className = tersembunyi
        ? "fa-regular fa-eye-slash"
        : "fa-regular fa-eye";

    togglePassword.setAttribute(
        "aria-label",
        tersembunyi ? "Sembunyikan password" : "Tampilkan password"
    );
    togglePassword.setAttribute(
        "aria-pressed",
        tersembunyi ? "true" : "false"
    );
});
</script>

</body>
</html>