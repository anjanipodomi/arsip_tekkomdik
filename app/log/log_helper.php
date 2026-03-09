<?php
/**
 * Helper Audit Log Sistem Arsip Inaktif Digital
 * Digunakan untuk mencatat seluruh aktivitas penting sistem
 */

function simpan_log($conn, $id_user, $aksi, $modul = '-') {

    // validasi dasar
    if (!$conn || !$id_user || !$aksi) {
        return false;
    }

    // escape untuk keamanan
    $id_user = mysqli_real_escape_string($conn, $id_user);
    $aksi    = mysqli_real_escape_string($conn, $aksi);
    $modul   = mysqli_real_escape_string($conn, $modul);

    // simpan log
    return mysqli_query($conn, "
        INSERT INTO log_aktivitas (id_user, aktivitas, modul, tanggal)
        VALUES ('$id_user', '$aksi', '$modul', NOW())
    ");
}
