<?php
function catatLog($koneksi, $aktivitas) {
    if (isset($_SESSION["id_user"])) {
        $id_user = (int)$_SESSION["id_user"];
        $aktivitas_aman = mysqli_real_escape_string($koneksi, $aktivitas);
        
        $stmt = mysqli_prepare($koneksi, "INSERT INTO log_aktifitas (id_user, aktivitas) VALUES (?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "is", $id_user, $aktivitas_aman);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}
?>