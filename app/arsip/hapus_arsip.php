<?php
session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../config/config.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID tidak valid");
}

// ambil file path
$q = mysqli_query($conn,"SELECT file_path FROM arsip WHERE id_arsip='$id'");
$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("Data arsip tidak ditemukan");
}

// hapus OCR
mysqli_query($conn,"DELETE FROM hasil_ocr WHERE id_arsip='$id'");

// hapus data pemusnahan jika ada
mysqli_query($conn,"DELETE FROM pemusnahan WHERE id_arsip='$id'");

// hapus log jika ada
mysqli_query($conn,"DELETE FROM log_aktivitas WHERE id_arsip='$id'");

// hapus arsip
mysqli_query($conn,"DELETE FROM arsip WHERE id_arsip='$id'");

header("Location: " . BASE_URL . "views/arsip.php");
exit;
