<?php
/**
 * Proses OCR Arsip
 * Fungsi:
 * - Mengubah dokumen hasil scan menjadi teks
 * - Menyimpan hasil OCR ke database
 * - Menandai status OCR pada arsip
 * - Mendukung pencarian berbasis isi dokumen
 */

session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['id_user'])) {
    die("Akses ditolak");
}

$id_user  = $_SESSION['id_user'];
$id_arsip = $_GET['id'] ?? '';

if ($id_arsip === '') {
    die("ID arsip tidak valid");
}

// ==========================
// AMBIL DATA ARSIP
// ==========================
$q = mysqli_query($conn, "
    SELECT file_path, status_ocr 
    FROM arsip 
    WHERE id_arsip='$id_arsip'
");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Arsip tidak ditemukan");
}

// ==========================
// CEK STATUS OCR (ANTI DUPLIKASI)
// ==========================
if ($data['status_ocr'] === 'Sukses') {
    header("Location: ../arsip/detail.php?id=$id_arsip");
    exit;
}

$file = "../" . $data['file_path'];

// ==========================
// VALIDASI FILE
// ==========================
if (!file_exists($file)) {
    mysqli_query($conn, "
        UPDATE arsip 
        SET status_ocr='Gagal' 
        WHERE id_arsip='$id_arsip'
    ");
    die("File arsip tidak ditemukan");
}

// ==========================
// SIAPKAN FOLDER OCR
// ==========================
$folderOCR = "../assets/ocr";
if (!is_dir($folderOCR)) {
    mkdir($folderOCR, 0777, true);
}

$output = "$folderOCR/ocr_$id_arsip";

// ==========================
// PROSES OCR (TESSERACT)
// ==========================
exec("tesseract \"$file\" \"$output\" -l ind 2>&1", $log, $status);

// ==========================
// AMBIL HASIL OCR
// ==========================
$textOCR = file_exists($output . ".txt")
    ? file_get_contents($output . ".txt")
    : '';

$textOCR = mysqli_real_escape_string($conn, $textOCR);

// ==========================
// SIMPAN HASIL OCR
// ==========================
if ($status === 0 && $textOCR !== '') {

    // Simpan ke tabel hasil_ocr
    mysqli_query($conn, "
        INSERT INTO hasil_ocr (id_arsip, teks_ocr, status_ocr, tanggal_ocr)
        VALUES ('$id_arsip', '$textOCR', 'Sukses', NOW())
    ");

    // Update status arsip
    mysqli_query($conn, "
        UPDATE arsip 
        SET status_ocr='Sukses' 
        WHERE id_arsip='$id_arsip'
    ");

    simpan_log($conn, $id_user, "OCR berhasil untuk arsip ID $id_arsip");

} else {

    mysqli_query($conn, "
        UPDATE arsip 
        SET status_ocr='Gagal' 
        WHERE id_arsip='$id_arsip'
    ");

    simpan_log($conn, $id_user, "OCR gagal untuk arsip ID $id_arsip");
}

// ==========================
// REDIRECT
// ==========================
header("Location: ../arsip/detail.php?id=$id_arsip");
exit;
