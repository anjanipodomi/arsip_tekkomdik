<?php
session_start();
include __DIR__ . "/../config/database.php";

/* =========================
   CEK LOGIN
========================= */
if (!isset($_SESSION['id_user'])) {
    die("Akses ditolak");
}

/* =========================
   VALIDASI ID
========================= */
$id = $_GET['id'] ?? '';

if ($id === '' || !is_numeric($id)) {
    die("ID tidak valid");
}

/* =========================
   AMBIL DATA FILE
========================= */
$stmt = mysqli_prepare($conn, "
    SELECT file_path 
    FROM arsip 
    WHERE id_arsip = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

if (!$data || empty($data['file_path'])) {
    die("File tidak ditemukan di database");
}

$file = __DIR__ . "/../" . $data['file_path'];

/* =========================
   CEK FILE ADA
========================= */
if (!file_exists($file)) {
    die("File tidak ditemukan di server");
}

/* =========================
   TENTUKAN MIME TYPE
========================= */
$mime = mime_content_type($file);

/* =========================
   VALIDASI HANYA PDF & IMAGE
========================= */
$allowed = [
    'application/pdf',
    'image/jpeg',
    'image/png'
];

if (!in_array($mime, $allowed)) {
    die("Format file tidak didukung untuk preview");
}

/* =========================
   TAMPILKAN FILE (INLINE)
========================= */
header("Content-Type: $mime");
header("Content-Disposition: inline; filename=\"" . basename($file) . "\"");
header("Content-Length: " . filesize($file));

readfile($file);
exit;