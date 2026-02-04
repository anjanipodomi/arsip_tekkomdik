<?php
/**
 * Approval Pemusnahan Arsip
 * Role: Pimpinan
 */

session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";
include __DIR__ . "/../notifikasi/helper.php";

// ==========================
// CEK LOGIN & ROLE
// ==========================
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pimpinan') {
    die("Akses ditolak");
}

$id_user  = $_SESSION['id_user'];
$id_arsip = $_GET['id']   ?? '';
$aksi     = $_GET['aksi'] ?? '';

if ($id_arsip === '' || !in_array($aksi, ['setuju','tolak'])) {
    die("Permintaan tidak valid");
}

// ==========================
// VALIDASI STATUS ARSIP
// ==========================
$cek = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT status_arsip 
    FROM arsip 
    WHERE id_arsip='$id_arsip'
"));

if (!$cek) {
    die("Arsip tidak ditemukan");
}

if ($cek['status_arsip'] !== 'Siap Musnah') {
    die("Arsip ini tidak dalam status siap dimusnahkan");
}

// ==========================
// PROSES APPROVAL
// ==========================
if ($aksi === 'setuju') {

    // catat pemusnahan
    mysqli_query($conn, "
        INSERT INTO pemusnahan (id_arsip, tanggal_pemusnahan, disetujui_oleh)
        VALUES ('$id_arsip', CURDATE(), '$id_user')
    ");

    // status final
    mysqli_query($conn, "
        UPDATE arsip 
        SET status_arsip='Dimusnahkan'
        WHERE id_arsip='$id_arsip'
    ");

    simpan_log($conn, $id_user, "Menyetujui pemusnahan arsip ID $id_arsip");

    $pesan = "Pemusnahan arsip ID $id_arsip TELAH DISETUJUI pimpinan";

} else {

    // dikembalikan ke permanen
    mysqli_query($conn, "
        UPDATE arsip 
        SET status_arsip='Permanen'
        WHERE id_arsip='$id_arsip'
    ");

    simpan_log($conn, $id_user, "Menolak pemusnahan arsip ID $id_arsip");

    $pesan = "Pemusnahan arsip ID $id_arsip DITOLAK pimpinan";
}

// ==========================
// KIRIM NOTIFIKASI KE ADMIN
// ==========================
$admin = mysqli_query($conn, "
    SELECT id_user 
    FROM users 
    WHERE role='admin'
");

while ($a = mysqli_fetch_assoc($admin)) {
    kirim_notifikasi($conn, $a['id_user'], $pesan);
}

// ==========================
// REDIRECT
// ==========================
header("Location: index.php");
exit;
