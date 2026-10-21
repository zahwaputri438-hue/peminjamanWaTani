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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: peminjaman.php");
    exit;
}

$aksi = $_POST["aksi"] ?? "";

if ($aksi === "tambah") {
    $id_peminjam = (int)($_POST["id_peminjam"] ?? 0);
    $id_alat = (int)($_POST["id_alat"] ?? 0);
    $tgl_pinjam = trim($_POST["tgl_pinjam"] ?? date("Y-m-d"));
    $tgl_kembali_rencana = trim($_POST["tgl_kembali_rencana"] ?? date("Y-m-d"));

    if ($id_peminjam <= 0 || $id_alat <= 0) {
        header("Location: peminjaman.php?pesan=gagal");
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO peminjaman (id_user, tgl_pinjam, tgl_kembali_rencana, status) VALUES (?, ?, ?, 'Dipinjam')");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $id_peminjam, $tgl_pinjam, $tgl_kembali_rencana);
        $sukses = mysqli_stmt_execute($stmt);
        $id_peminjaman_baru = mysqli_insert_id($koneksi);
        mysqli_stmt_close($stmt);

        if ($sukses) {
            mysqli_query($koneksi, "INSERT INTO detail_peminjaman (id_peminjaman, id_alat, jumlah) VALUES ($id_peminjaman_baru, $id_alat, 1)");
            
            mysqli_query($koneksi, "UPDATE alat SET stok = stok - 1 WHERE id_alat = $id_alat");

            // CATAT LOG AKTIVITAS
            catatLog($koneksi, "Membuat data peminjaman alat baru (ID: $id_peminjaman_baru)");

            header("Location: peminjaman.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: peminjaman.php?pesan=gagal");
    exit;
} else {
    header("Location: peminjaman.php");
    exit;
}