<?php
/**
 * Proses Retensi Arsip Otomatis
 * Berdasarkan umur arsip dan kebijakan retensi
 */

session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

// ==========================
// CEK LOGIN & ROLE
// ==========================
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id_user = $_SESSION['id_user'];

// ==========================
// AMBIL DATA ARSIP + RETENSI
// HANYA YANG BELUM SIAP MUSNAH
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        arsip.id_arsip,
        arsip.tanggal_surat,
        arsip.status_arsip,
        retensi.masa_aktif,
        retensi.masa_inaktif
    FROM arsip
    JOIN retensi ON arsip.id_kategori = retensi.id_kategori
    WHERE arsip.status_arsip != 'Siap Musnah'
");

$jumlah_update = 0;

while ($row = mysqli_fetch_assoc($query)) {

    $id_arsip     = $row['id_arsip'];
    $tgl_surat    = $row['tanggal_surat'];
    $masa_aktif   = (int)$row['masa_aktif'];
    $masa_inaktif = (int)$row['masa_inaktif'];

    // ==========================
    // HITUNG UMUR ARSIP (TAHUN)
    // ==========================
    $tanggal_surat = new DateTime($tgl_surat);
    $hari_ini      = new DateTime();
    $umur          = $hari_ini->diff($tanggal_surat)->y;

    // ==========================
    // TENTUKAN STATUS BARU
    // ==========================
    if ($umur <= $masa_aktif) {
        $status_baru = 'Inaktif';
    } elseif ($umur <= ($masa_aktif + $masa_inaktif)) {
        $status_baru = 'Permanen';
    } else {
        $status_baru = 'Siap Musnah';
    }

    // ==========================
    // UPDATE JIKA BERUBAH
    // ==========================
    if ($status_baru !== $row['status_arsip']) {
        mysqli_query($conn, "
            UPDATE arsip 
            SET status_arsip='$status_baru'
            WHERE id_arsip='$id_arsip'
        ");
        $jumlah_update++;
    }
}

// ==========================
// AUDIT LOG
// ==========================
simpan_log($conn, $id_user, "Menjalankan proses retensi arsip otomatis");

// ==========================
// OUTPUT
// ==========================
echo "Proses retensi selesai. Arsip diperbarui: $jumlah_update";
