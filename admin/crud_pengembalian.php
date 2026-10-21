<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

if (
    !isset($_SESSION["id_user"]) ||
    !isset($_SESSION["role"]) ||
    ($_SESSION["role"] !== "admin" && $_SESSION["role"] !== "petugas")
) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pengembalian.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? "";
if (empty($csrfToken) || !isset($_SESSION["csrf_pengembalian"]) || !hash_equals($_SESSION["csrf_pengembalian"], $csrfToken)) {
    header("Location: pengembalian.php?pesan=gagal");
    exit;
}

$aksi = $_POST["aksi"] ?? "";

if ($aksi === "tambah") {
    $id_peminjaman = (int)($_POST["id_peminjaman"] ?? 0);
    $tgl_dikembalikan = trim($_POST["tanggal_kembali"] ?? date("Y-m-d"));
    $denda = (int)($_POST["denda"] ?? 0);
    $kondisi_alat = trim($_POST["kondisi_alat"] ?? "Baik");
    $keterangan = trim($_POST["keterangan"] ?? "");

    if ($id_peminjaman <= 0 || empty($tgl_dikembalikan)) {
        header("Location: pengembalian.php?pesan=gagal");
        exit;
    }

    $qDetail = mysqli_query($koneksi, "SELECT id_alat FROM detail_peminjaman WHERE id_peminjaman = $id_peminjaman");
    $detail = mysqli_fetch_assoc($qDetail);

    if (!$detail) {
        header("Location: pengembalian.php?pesan=gagal");
        exit;
    }

    $id_alat = (int)$detail['id_alat'];
    $jumlah = 1;

    $stmt = mysqli_prepare($koneksi, "INSERT INTO pengembalian (id_peminjaman, tgl_dikembalikan, denda, kondisi_alat, keterangan) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isiss", $id_peminjaman, $tgl_dikembalikan, $denda, $kondisi_alat, $keterangan);
        $sukses = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($sukses) {
            mysqli_query($koneksi, "UPDATE peminjaman SET status = 'Dikembalikan' WHERE id_peminjaman = $id_peminjaman");

            if ($kondisi_alat === 'Baik' || $kondisi_alat === 'Rusak Ringan') {
                mysqli_query($koneksi, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat = $id_alat");
            }

            header("Location: pengembalian.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: pengembalian.php?pesan=gagal");
    exit;
} else {
    header("Location: pengembalian.php");
    exit;
}