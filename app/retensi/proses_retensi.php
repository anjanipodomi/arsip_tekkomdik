<?php
/**
 * PROSES RETENSI ARSIP (FINAL & STABIL)
 * Role : ADMIN
 *
 * Alur:
 * 1. Validasi login & role
 * 2. Cek kategori yang belum punya retensi → BATAL
 * 3. Hitung umur arsip (berdasarkan tanggal_surat)
 * 4. Update status arsip:
 *    - Inaktif
 *    - Permanen
 *    - Siap Musnah
 * 5. Kirim NOTIFIKASI KE PIMPINAN (1x, jika ada arsip siap musnah)
 * 6. Simpan audit log
 */

session_start();

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../log/log_helper.php";
require_once __DIR__ . "/../notifikasi/helper.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    exit("Akses ditolak");
}

$id_user = $_SESSION['id_user'];

/* ==========================
   AMBIL DATA ARSIP + RETENSI
========================== */
$q = mysqli_query($conn, "
    SELECT 
        arsip.id_arsip,
        arsip.nomor_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.id_kategori,
        kategori.nama_kategori,
        retensi.masa_aktif,
        retensi.masa_inaktif
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN retensi  ON kategori.id_kategori = retensi.id_kategori
");

/* ==========================
   VALIDASI RETENSI
========================== */
$kategori_tanpa_retensi = [];

while ($row = mysqli_fetch_assoc($q)) {
    if ($row['masa_aktif'] === null || $row['masa_inaktif'] === null) {
        $kategori_tanpa_retensi[$row['id_kategori']] = $row['nama_kategori'];
    }
}

/* ==========================
   JIKA ADA KATEGORI TANPA RETENSI
========================== */
if (!empty($kategori_tanpa_retensi)) {

    $_SESSION['warning_retensi'] = array_values($kategori_tanpa_retensi);

    header("Location: index.php");
    exit;
}

/* ==========================
   RESET POINTER RESULT
========================== */
mysqli_data_seek($q, 0);

/* ==========================
   PROSES RETENSI
========================== */
$jumlah_update = 0;
$jumlah_musnah = 0;

while ($row = mysqli_fetch_assoc($q)) {

    if (empty($row['tanggal_surat'])) {
        continue; // skip data tidak valid
    }

    $tgl_surat = new DateTime($row['tanggal_surat']);
    $hari_ini  = new DateTime();
    $umur      = $hari_ini->diff($tgl_surat)->y;

    /* ==========================
       LOGIKA STATUS
    ========================== */
    if ($umur <= $row['masa_aktif']) {
        $status_baru = 'Inaktif';
    } elseif ($umur <= ($row['masa_aktif'] + $row['masa_inaktif'])) {
        $status_baru = 'Permanen';
    } else {
        $status_baru = 'Siap Musnah';
    }

    /* ==========================
       UPDATE JIKA BERUBAH
    ========================== */
    if ($status_baru !== $row['status_arsip']) {

        mysqli_query($conn, "
            UPDATE arsip
            SET status_arsip = '$status_baru'
            WHERE id_arsip = '{$row['id_arsip']}'
        ");

        $jumlah_update++;

        if ($status_baru === 'Siap Musnah') {
            $jumlah_musnah++;
        }
    }
}

/* ==========================
   NOTIFIKASI KE PIMPINAN
   (HANYA SEKALI, JIKA ADA)
========================== */
if ($jumlah_musnah > 0) {
    kirim_notifikasi_pimpinan_link(
        $conn,
        "🔔 Terdapat $jumlah_musnah arsip yang siap dimusnahkan dan menunggu persetujuan",
        BASE_URL . "views/arsip_menunggu.php"
    );
}




/* ==========================
   AUDIT LOG
========================== */
simpan_log(
    $conn,
    $id_user,
    "Menjalankan proses retensi | Update: $jumlah_update | Siap Musnah: $jumlah_musnah",
    "Retensi"
);

/* ==========================
   REDIRECT
========================== */
header("Location: " . BASE_URL . "views/manajemen_retensi.php?success=1&update=$jumlah_update&musnah=$jumlah_musnah");
exit;