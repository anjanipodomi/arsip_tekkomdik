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
$q = mysqli_query($conn, "
    SELECT pesan, link, status, tanggal
    FROM notifikasi
    WHERE id_user = '$id_user'
      AND status IN ('baru','dibaca')
    ORDER BY tanggal DESC
");


$data = mysqli_fetch_assoc($cek);

if (!$data || $data['status_arsip'] !== 'Siap Musnah') {
    die("Arsip tidak dalam status siap musnah");
}

$nomor_surat = $data['nomor_surat'];

/* ==========================
   TUTUP NOTIFIKASI LAMA
   (BERLAKU UNTUK SETUJU & TOLAK)
========================== */
mysqli_query($conn, "
    UPDATE notifikasi
    SET status = 'selesai'
    WHERE pesan LIKE '%$nomor_surat%'
      AND status = 'baru'
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

    kirim_notifikasi_role_link(
        $conn,
        'admin',
        "Pemusnahan arsip ($nomor_surat) DISETUJUI pimpinan",
        "/arsip_tekkomdik/backend/pemusnahan/history.php"
    );

    $_SESSION['success'] = "Pemusnahan dokumen arsip DISSETUJUI";

/* ==========================
   PROSES TOLAK
========================== */
} else {

    mysqli_query($conn,"
        UPDATE arsip
        SET status_arsip='Permanen'
        WHERE id_arsip='$id_arsip'
    ");

    simpan_log(
        $conn,
        $id_user,
        "Menolak pemusnahan arsip ($nomor_surat)",
        "Pemusnahan"
    );

    kirim_notifikasi_role_link(
        $conn,
        'admin',
        "Pemusnahan arsip ($nomor_surat) DITOLAK pimpinan",
        "/arsip_tekkomdik/backend/pemusnahan/history.php"
    );

    $_SESSION['success'] = "Pemusnahan dokumen arsip DITOLAK";
}

/* ==========================
   REDIRECT
========================== */
header("Location: ../dashboard/arsip_menunggu.php");
exit;
