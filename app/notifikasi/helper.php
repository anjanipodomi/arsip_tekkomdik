<?php
/**
 * Helper Notifikasi Sistem Arsip Inaktif Digital
 */

/* ==========================
   AUTO HAPUS NOTIF > 3 BULAN
========================== */
function bersihkan_notifikasi_lama($conn, $id_user)
{
    $id_user = mysqli_real_escape_string($conn, $id_user);

    mysqli_query($conn, "
        DELETE FROM notifikasi
        WHERE id_user = '$id_user'
          AND tanggal < DATE_SUB(NOW(), INTERVAL 3 MONTH)
    ");
}


/* ==========================
   NOTIFIKASI TANPA LINK
========================== */
function kirim_notifikasi($conn, $id_user, $pesan)
{
    if (!$conn || !$id_user || !$pesan) return false;

    $id_user = mysqli_real_escape_string($conn, $id_user);
    $pesan   = mysqli_real_escape_string($conn, $pesan);

    return mysqli_query($conn, "
        INSERT INTO notifikasi (id_user, pesan, tanggal)
        VALUES ('$id_user', '$pesan', NOW())
    ");
}

function kirim_notifikasi_role($conn, $role, $pesan)
{
    $role = mysqli_real_escape_string($conn, $role);

    $q = mysqli_query($conn, "
        SELECT id_user FROM users WHERE role = '$role'
    ");

    while ($u = mysqli_fetch_assoc($q)) {
        kirim_notifikasi($conn, $u['id_user'], $pesan);
    }
}


/* ==========================
   NOTIFIKASI DENGAN LINK
========================== */
function kirim_notifikasi_dengan_link($conn, $id_user, $pesan, $link)
{
    if (!$conn || !$id_user || !$pesan) return false;

    $id_user = mysqli_real_escape_string($conn, $id_user);
    $pesan   = mysqli_real_escape_string($conn, $pesan);
    $link    = mysqli_real_escape_string($conn, $link);

    return mysqli_query($conn, "
        INSERT INTO notifikasi (id_user, pesan, link, tanggal)
        VALUES ('$id_user', '$pesan', '$link', NOW())
    ");
}

function kirim_notifikasi_role_link($conn, $role, $pesan, $link)
{
    $role = mysqli_real_escape_string($conn, $role);

    $q = mysqli_query($conn, "
        SELECT id_user FROM users WHERE role = '$role'
    ");

    while ($u = mysqli_fetch_assoc($q)) {
        kirim_notifikasi_dengan_link($conn, $u['id_user'], $pesan, $link);
    }
}


/* ==========================
   KHUSUS PIMPINAN
========================== */
function kirim_notifikasi_pimpinan_link($conn, $pesan, $link)
{
    $pesan = mysqli_real_escape_string($conn, $pesan);
    $link  = mysqli_real_escape_string($conn, $link);

    $q = mysqli_query($conn, "
        SELECT id_user FROM users WHERE role = 'pimpinan'
    ");

    while ($u = mysqli_fetch_assoc($q)) {
        mysqli_query($conn, "
            INSERT INTO notifikasi (id_user, pesan, link, tanggal)
            VALUES ('{$u['id_user']}', '$pesan', '$link', NOW())
        ");
    }
}


/* ==========================
   HITUNG JUMLAH NOTIFIKASI
========================== */
function jumlah_notifikasi_baru($conn, $id_user)
{
    $id_user = mysqli_real_escape_string($conn, $id_user);

    // 🔥 AUTO CLEAN NOTIF LAMA
    bersihkan_notifikasi_lama($conn, $id_user);

    $q = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM notifikasi
        WHERE id_user = '$id_user'
    ");

    $r = mysqli_fetch_assoc($q);
    return (int)$r['total'];
}