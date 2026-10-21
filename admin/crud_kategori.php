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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: kategori.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? "";
if (empty($csrfToken) || !isset($_SESSION["csrf_kategori"]) || !hash_equals($_SESSION["csrf_kategori"], $csrfToken)) {
    header("Location: kategori.php?pesan=gagal");
    exit;
}

$aksi = $_POST["aksi"] ?? "";

if ($aksi === "tambah") {
    $nama_kategori = trim($_POST["nama_kategori"] ?? "");

    if ($nama_kategori === "") {
        header("Location: kategori.php?pesan=gagal");
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO kategori (nama_kategori) VALUES (?)");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $nama_kategori);
        $sukses = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($sukses) {
            header("Location: kategori.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: kategori.php?pesan=gagal");
    exit;
}

elseif ($aksi === "edit") {
    $id_kategori   = (int)($_POST["id_kategori"] ?? 0);
    $nama_kategori = trim($_POST["nama_kategori"] ?? "");

    if ($id_kategori <= 0 || $nama_kategori === "") {
        header("Location: kategori.php?pesan=gagal");
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $nama_kategori, $id_kategori);
        $sukses = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($sukses) {
            header("Location: kategori.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: kategori.php?pesan=gagal");
    exit;
}

elseif ($aksi === "hapus") {
    $id_kategori = (int)($_POST["id_kategori"] ?? 0);

    if ($id_kategori <= 0) {
        header("Location: kategori.php?pesan=gagal");
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM kategori WHERE id_kategori = ?");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_kategori);
        $sukses = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($sukses) {
            header("Location: kategori.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: kategori.php?pesan=gagal");
    exit;
}

else {
    header("Location: kategori.php");
    exit;
}