<?php
/**
 * Helper Notifikasi Sistem Arsip Inaktif Digital
 */

function kirim_notifikasi($conn, $id_user, $pesan) {

    if (!$conn || !$id_user || !$pesan) {
        return false;
    }

    $id_user = mysqli_real_escape_string($conn, $id_user);
    $pesan   = mysqli_real_escape_string($conn, $pesan);

    return mysqli_query($conn, "
        INSERT INTO notifikasi (id_user, pesan, status, tanggal)
        VALUES ('$id_user', '$pesan', 'baru', NOW())
    ");
}
