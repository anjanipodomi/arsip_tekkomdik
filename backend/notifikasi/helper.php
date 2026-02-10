<?php
/**
 * Helper Notifikasi Sistem Arsip Inaktif Digital
 */

/**
 * Kirim notifikasi ke SATU user
 */
function kirim_notifikasi($conn, $id_user, $pesan)
{
    if (!$conn || !$id_user || !$pesan) return false;

    $id_user = mysqli_real_escape_string($conn, $id_user);
    $pesan   = mysqli_real_escape_string($conn, $pesan);

    return mysqli_query($conn, "
        INSERT INTO notifikasi (id_user, pesan, status, tanggal)
        VALUES ('$id_user', '$pesan', 'baru', NOW())
    ");
}

/**
 * 🔔 Kirim notifikasi ke SEMUA user berdasarkan ROLE
 */
function kirim_notifikasi_role($conn, $role, $pesan)
{
    $role = mysqli_real_escape_string($conn, $role);

    $q = mysqli_query($conn, "
        SELECT id_user
        FROM users
        WHERE role = '$role'
    ");

    while ($u = mysqli_fetch_assoc($q)) {
        kirim_notifikasi($conn, $u['id_user'], $pesan);
    }
}

/**
 * 🔔 Khusus pimpinan
 */
function kirim_notifikasi_pimpinan($conn, $pesan)
{
    kirim_notifikasi_role($conn, 'pimpinan', $pesan);
}

/**
 * 🔔 Hitung notifikasi belum dibaca
 */
function jumlah_notifikasi_baru($conn, $id_user)
{
    $id_user = mysqli_real_escape_string($conn, $id_user);

    $q = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM notifikasi
        WHERE id_user = '$id_user'
          AND status = 'baru'
    ");

    $r = mysqli_fetch_assoc($q);
    return (int)$r['total'];
}
