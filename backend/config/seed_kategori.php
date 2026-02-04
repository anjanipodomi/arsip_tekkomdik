<?php
/**
 * Seeder Kategori Arsip Default
 * Dijalankan otomatis jika tabel kategori kosong
 */

$default_kategori = [
    'Dokumen Administrasi',
    'Laporan Kegiatan',
    'Laporan Keuangan',
    'Surat Masuk',
    'Surat Keluar'
];

$cek = mysqli_query($conn, "SELECT COUNT(*) total FROM kategori");
$total = mysqli_fetch_assoc($cek)['total'];

if ($total == 0) {
    foreach ($default_kategori as $nama) {
        mysqli_query($conn, "
            INSERT INTO kategori (nama_kategori)
            VALUES ('$nama')
        ");
    }
}
