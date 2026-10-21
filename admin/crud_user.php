<?php
session_start();
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/fungsi_log.php";

if (
    !isset($_SESSION["id_user"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pengguna.php");
    exit;
}

$aksi = $_POST["aksi"] ?? "";

if ($aksi === "tambah") {
    $nama_user = trim($_POST["nama_user"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = password_hash($_POST["password"] ?? "", PASSWORD_DEFAULT);
    $role = trim($_POST["role"] ?? "petugas");

    if ($nama_user === "" || $username === "") {
        header("Location: pengguna.php?pesan=gagal");
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama_user, username, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $nama_user, $username, $password, $role);
        $sukses = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($sukses) {
            catatLog($koneksi, "Menambahkan pengguna baru: $nama_user ($role)");

            header("Location: pengguna.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: pengguna.php?pesan=gagal");
    exit;
} else {
    header("Location: pengguna.php");
    exit;
}