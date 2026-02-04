<?php
session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login_form.php");
    exit;
}

$id_arsip = $_GET['id'] ?? '';
if ($id_arsip === '') {
    die("ID arsip tidak valid");
}

// ambil data arsip
$q = mysqli_query($conn, "
    SELECT file_path, nomor_surat
    FROM arsip
    WHERE id_arsip = '$id_arsip'
");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Data arsip tidak ditemukan");
}

// base folder backend
$baseDir  = realpath(__DIR__ . "/.."); // backend/
$filePath = $baseDir . "/" . $data['file_path'];

if (!file_exists($filePath)) {
    die("File arsip tidak ditemukan di server");
}

// audit log
simpan_log(
    $conn,
    $_SESSION['id_user'],
    "Download arsip: {$data['nomor_surat']}",
    "Arsip"
);

// download
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . basename($filePath) . "\"");
header("Content-Length: " . filesize($filePath));
header("Cache-Control: no-cache");
header("Pragma: public");

readfile($filePath);
exit;
