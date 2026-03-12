<?php
/**
 * Approve Pemusnahan Arsip
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
$id_arsip = $_GET['id'] ?? '';

if ($id_arsip === '') {
    die("ID arsip tidak valid");
}

// ==========================
// UPDATE STATUS ARSIP
// ==========================
mysqli_query($conn, "
    UPDATE arsip
    SET status_arsip='Dimusnahkan'
    WHERE id_arsip='$id_arsip'
");

// ==========================
// SIMPAN DATA PEMUSNAHAN
// ==========================
mysqli_query($conn, "
    INSERT INTO pemusnahan
    (id_arsip, tanggal_pemusnahan, disetujui_oleh, keterangan)
    VALUES
    ('$id_arsip', CURDATE(), '$id_user', 'Disetujui pimpinan')
");

// ==========================
// AUDIT LOG
// ==========================
simpan_log(
    $conn,
    $id_user,
    "Menyetujui pemusnahan arsip ID $id_arsip",
    "Pemusnahan"
);

// ==========================
// NOTIFIKASI KE ADMIN
// ==========================
$admin = mysqli_query($conn, "SELECT id_user FROM users WHERE role='admin'");
while ($a = mysqli_fetch_assoc($admin)) {
    kirim_notifikasi(
        $conn,
        $a['id_user'],
        "Arsip ID $id_arsip telah dimusnahkan (disetujui pimpinan)"
    );
}

// ==========================
// REDIRECT
// ==========================
header("Location: index.php");
exit;
