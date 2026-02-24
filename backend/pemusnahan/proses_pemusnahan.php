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
$cek = mysqli_query($conn, "
    SELECT nomor_surat, status_arsip
    FROM arsip
    WHERE id_arsip = '$id_arsip'
");

$data = mysqli_fetch_assoc($cek);

if (!$data) {
    die("Data arsip tidak ditemukan");
}

if ($data['status_arsip'] !== 'Siap Musnah') {
    die("Arsip tidak dalam status siap musnah");
}

$nomor_surat = $data['nomor_surat'];

/* ==========================
   HAPUS NOTIFIKASI PIMPINAN TERKAIT ARSIP INI
========================== */
$nomor_safe = mysqli_real_escape_string($conn, $nomor_surat);

mysqli_query($conn, "
    DELETE FROM notifikasi
    WHERE id_user = '$id_user'
      AND pesan LIKE '%$nomor_safe%'
");


/* ==========================
   PROSES SETUJU
========================== */
if ($aksi === 'setuju') {

    mysqli_query($conn,"
        UPDATE arsip
        SET status_arsip='Dimusnahkan'
        WHERE id_arsip='$id_arsip'
    ");

    mysqli_query($conn,"
        INSERT INTO pemusnahan
        (id_arsip, tanggal_pemusnahan, disetujui_oleh, keterangan)
        VALUES
        ('$id_arsip', CURDATE(), '$id_user', 'Disetujui pimpinan')
    ");

    simpan_log(
        $conn,
        $id_user,
        "Menyetujui pemusnahan arsip ($nomor_surat)",
        "Pemusnahan"
    );

    // ✅ ADMIN TANPA LINK
    kirim_notifikasi_role(
        $conn,
        'admin',
        "Pemusnahan arsip ($nomor_surat) DISETUJUI pimpinan"
    );

    $_SESSION['success'] = "Pemusnahan dokumen arsip DISETUJUI";

/* ==========================
   PROSES TOLAK
========================== */
} else {

    mysqli_query($conn,"
        UPDATE arsip
        SET status_arsip='Permanen'
        WHERE id_arsip='$id_arsip'
    ");

    mysqli_query($conn,"
        INSERT INTO pemusnahan
        (id_arsip, tanggal_pemusnahan, disetujui_oleh, keterangan)
        VALUES
        ('$id_arsip', CURDATE(), '$id_user', 'Ditolak pimpinan')
    ");

    simpan_log(
        $conn,
        $id_user,
        "Menolak pemusnahan arsip ($nomor_surat)",
        "Pemusnahan"
    );

    // ✅ ADMIN TANPA LINK
    kirim_notifikasi_role(
        $conn,
        'admin',
        "Pemusnahan arsip ($nomor_surat) DITOLAK pimpinan"
    );

    $_SESSION['success'] = "Pemusnahan dokumen arsip DITOLAK";
}

/* ==========================
   REDIRECT
========================== */
header("Location: ../dashboard/arsip_menunggu.php");
exit;