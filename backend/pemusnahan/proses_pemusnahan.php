<?php
/**
 * Proses Persetujuan / Penolakan Pemusnahan Arsip
 * Role : Pimpinan
 */

session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";
include __DIR__ . "/../notifikasi/helper.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pimpinan') {
    die("Akses ditolak");
}

$id_user  = $_SESSION['id_user'];
$id_arsip = $_GET['id']   ?? '';
$aksi     = $_GET['aksi'] ?? '';

if ($id_arsip === '' || !in_array($aksi, ['setuju','tolak'])) {
    die("Parameter tidak valid");
}

/* ==========================
   CEK STATUS ARSIP
========================== */
$cek = mysqli_query($conn,"
    SELECT nomor_surat, status_arsip 
    FROM arsip 
    WHERE id_arsip='$id_arsip'
");

$data = mysqli_fetch_assoc($cek);

if (!$data || $data['status_arsip'] !== 'Siap Musnah') {
    die("Arsip tidak dalam status siap musnah");
}

$nomor_surat = $data['nomor_surat'];

/* ==========================
   PROSES SETUJU
========================== */
if ($aksi === 'setuju') {

    // update arsip
    mysqli_query($conn,"
        UPDATE arsip 
        SET status_arsip='Dimusnahkan'
        WHERE id_arsip='$id_arsip'
    ");

    // catat pemusnahan
    mysqli_query($conn,"
        INSERT INTO pemusnahan
        (id_arsip, tanggal_pemusnahan, disetujui_oleh, keterangan)
        VALUES
        ('$id_arsip', CURDATE(), '$id_user', 'Disetujui pimpinan')
    ");

    // audit log
    simpan_log(
        $conn,
        $id_user,
        "Menyetujui pemusnahan arsip ($nomor_surat)",
        "Pemusnahan"
    );

    // 🔔 NOTIFIKASI KE ADMIN
    $admin = mysqli_query($conn,"SELECT id_user FROM users WHERE role='admin'");
    while ($a = mysqli_fetch_assoc($admin)) {
        kirim_notifikasi(
            $conn,
            $a['id_user'],
            "Pemusnahan arsip ($nomor_surat) TELAH DISETUJUI pimpinan"
        );
    }

/* ==========================
   PROSES TOLAK
========================== */
} else {

    // kembalikan arsip jadi permanen
    mysqli_query($conn,"
        UPDATE arsip 
        SET status_arsip='Permanen'
        WHERE id_arsip='$id_arsip'
    ");

    // audit log
    simpan_log(
        $conn,
        $id_user,
        "Menolak pemusnahan arsip ($nomor_surat)",
        "Pemusnahan"
    );

    // 🔔 NOTIFIKASI KE ADMIN (INI YANG TADI KURANG)
    $admin = mysqli_query($conn,"SELECT id_user FROM users WHERE role='admin'");
    while ($a = mysqli_fetch_assoc($admin)) {
        kirim_notifikasi(
            $conn,
            $a['id_user'],
            "Arsip ID $id_arsip telah dimusnahkan (disetujui pimpinan)"
        );
    }
}

/* ==========================
   REDIRECT
========================== */
header("Location: ../dashboard/pimpinan.php");
exit;
