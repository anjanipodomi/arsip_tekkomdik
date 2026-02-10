<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id = $_GET['id'] ?? '';
if ($id === '') die("ID tidak valid");

// ambil file path
$q = mysqli_query($conn,"SELECT file_path FROM arsip WHERE id_arsip='$id'");
$data = mysqli_fetch_assoc($q);

if ($data) {
    if (file_exists("../".$data['file_path'])) {
        unlink("../".$data['file_path']);
    }
}

// hapus OCR
mysqli_query($conn,"DELETE FROM hasil_ocr WHERE id_arsip='$id'");

// hapus arsip
mysqli_query($conn,"DELETE FROM arsip WHERE id_arsip='$id'");

header("Location: index.php");
exit;
