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
    header("Location: alat.php");
    exit;
}

$aksi = $_POST["aksi"] ?? "";

// 1. TAMBAH ALAT
if ($aksi === "tambah") {
    $nama_alat  = trim($_POST["nama_alat"] ?? "");
    $id_kategori= (int)($_POST["id_kategori"] ?? 0);
    $stok       = (int)($_POST["stok"] ?? 0);
    $kondisi    = trim($_POST["kondisi"] ?? "Baik");
    $keterangan = trim($_POST["keterangan"] ?? "");
    $gambar     = "";

    if ($nama_alat === "" || $id_kategori <= 0 || $stok < 0) {
        header("Location: alat.php?pesan=gagal");
        exit;
    }

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['gambar']['tmp_name'];
        $fileName      = $_FILES['gambar']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid('alat_', true) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../assets/images/alat/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $gambar = $newFileName;
            }
        }
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO alat (nama_alat, id_kategori, stok, kondisi, keterangan, gambar) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "siisss", $nama_alat, $id_kategori, $stok, $kondisi, $keterangan, $gambar);
        $sukses = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($sukses) {
            catatLog($koneksi, "Menambahkan alat pertanian baru: " . $nama_alat);
            header("Location: alat.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: alat.php?pesan=gagal");
    exit;
}

elseif ($aksi === "edit") {
    $id_alat    = (int)($_POST["id_alat"] ?? 0);
    $nama_alat  = trim($_POST["nama_alat"] ?? "");
    $id_kategori= (int)($_POST["id_kategori"] ?? 0);
    $stok       = (int)($_POST["stok"] ?? 0);
    $kondisi    = trim($_POST["kondisi"] ?? "Baik");
    $keterangan = trim($_POST["keterangan"] ?? "");
    $gambar_lama= $_POST["gambar_lama"] ?? "";
    $gambar     = $gambar_lama;

    if ($id_alat <= 0 || $nama_alat === "" || $id_kategori <= 0 || $stok < 0) {
        header("Location: alat.php?pesan=gagal");
        exit;
    }

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['gambar']['tmp_name'];
        $fileName      = $_FILES['gambar']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid('alat_', true) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../assets/images/alat/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $gambar = $newFileName;
                if (!empty($gambar_lama) && file_exists($uploadFileDir . $gambar_lama)) {
                    @unlink($uploadFileDir . $gambar_lama);
                }
            }
        }
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE alat SET nama_alat = ?, id_kategori = ?, stok = ?, kondisi = ?, keterangan = ?, gambar = ? WHERE id_alat = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "siisssi", $nama_alat, $id_kategori, $stok, $kondisi, $keterangan, $gambar, $id_alat);
        $sukses = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($sukses) {
            catatLog($koneksi, "Mengubah data alat pertanian: " . $nama_alat);
            header("Location: alat.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: alat.php?pesan=gagal");
    exit;
}

// 3. HAPUS ALAT
elseif ($aksi === "hapus") {
    $id_alat     = (int)($_POST["id_alat"] ?? 0);
    $gambar_lama = $_POST["gambar_lama"] ?? "";

    if ($id_alat > 0) {
        $qAlat = mysqli_query($koneksi, "SELECT nama_alat FROM alat WHERE id_alat = $id_alat");
        $dAlat = mysqli_fetch_assoc($qAlat);
        $namaAlat = $dAlat["nama_alat"] ?? "ID: $id_alat";

        $stmt = mysqli_prepare($koneksi, "DELETE FROM alat WHERE id_alat = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_alat);
            $sukses = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($sukses) {
                if (!empty($gambar_lama)) {
                    $pathGambar = __DIR__ . '/../assets/images/alat/' . $gambar_lama;
                    if (file_exists($pathGambar)) {
                        @unlink($pathGambar);
                    }
                }

                catatLog($koneksi, "Menghapus alat pertanian: " . $namaAlat);
                header("Location: alat.php?pesan=berhasil");
                exit;
            }
        }
    }

    header("Location: alat.php?pesan=gagal");
    exit;
}

else {
    header("Location: alat.php");
    exit;
}