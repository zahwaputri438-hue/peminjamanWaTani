<?php
session_start();
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/fungsi_log.php";

if (
    !isset($_SESSION["id_user"]) ||
    !isset($_SESSION["role"]) ||
    ($_SESSION["role"] !== "admin" && $_SESSION["role"] !== "petugas")
) {
    header("Location: ../login.php");
    exit;
}

$aksi = $_POST["aksi"] ?? $_GET["aksi"] ?? "";

// 1. TAMBAH KATEGORI
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
            catatLog($koneksi, "Menambahkan kategori alat baru: $nama_kategori");
            header("Location: kategori.php?pesan=berhasil");
            exit;
        }
    }
    header("Location: kategori.php?pesan=gagal");
    exit;
}

// 2. HAPUS KATEGORI
elseif (isset($_GET["hapus"]) || $aksi === "hapus") {
    $id_kategori = (int)($_GET["hapus"] ?? $_POST["id_kategori"] ?? 0);

    if ($id_kategori > 0) {
        // Ambil nama kategori untuk log sebelum dihapus
        $qKat = mysqli_query($koneksi, "SELECT nama_kategori FROM kategori WHERE id_kategori = $id_kategori");
        $dataKat = mysqli_fetch_assoc($qKat);
        $namaKat = $dataKat["nama_kategori"] ?? "ID: $id_kategori";

        $stmt = mysqli_prepare($koneksi, "DELETE FROM kategori WHERE id_kategori = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_kategori);
            $sukses = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($sukses) {
                catatLog($koneksi, "Menghapus kategori alat: $namaKat");
                header("Location: kategori.php?pesan=berhasil");
                exit;
            }
        }
    }
    header("Location: kategori.php?pesan=gagal");
    exit;
}

else {
    header("Location: kategori.php");
    exit;
}