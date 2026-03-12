<?php
session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
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
    SELECT id_arsip, nomor_surat, file_path, status_arsip
    FROM arsip
    WHERE id_arsip='$id_arsip'
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Data arsip tidak ditemukan");
}

// ==========================
// BLOK ARSIP DIMUSNAHKAN
// ==========================
if ($data['status_arsip'] === 'Dimusnahkan') {
    simpan_log($conn, $id_user, "Gagal download arsip dimusnahkan", "Download");
    die("❌ Arsip ini telah dimusnahkan");
}

// ==========================
// 🔥 NORMALISASI PATH (INI KUNCINYA)
// ==========================
$fileRel = trim($data['file_path']);
$fileRel = str_replace(['\\', '//'], '/', $fileRel);
$fileRel = ltrim($fileRel, '/');

// Root project (naik dari app/arsip)
$rootPath = realpath(__DIR__ . '/../');
$filePath = $rootPath . '/' . $fileRel;

if (!is_readable($filePath)) {
    die("❌ File fisik tidak dapat dibaca: " . $filePath);
}

// ==========================
// VALIDASI FILE (PAKAI READABLE)
// ==========================
if (!is_readable($filePath)) {
    die("❌ File fisik tidak dapat dibaca: " . $filePath);
}

// ==========================
// LOG DOWNLOAD
// ==========================
simpan_log(
    $conn,
    $id_user,
    "Download arsip nomor surat: {$data['nomor_surat']}",
    "Download"
);

// ==========================
// DOWNLOAD
// ==========================
$filename = basename($filePath);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($filePath));
header("Cache-Control: must-revalidate");
header("Pragma: public");
header("Expires: 0");

readfile($filePath);
exit;
