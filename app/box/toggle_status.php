<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id   = $_GET['id'] ?? '';
$aksi = $_GET['aksi'] ?? '';

if ($id === '' || !in_array($aksi, ['aktif','nonaktif'])) {
    die("Aksi tidak valid");
}

mysqli_query($conn, "
    UPDATE box 
    SET status='$aksi'
    WHERE id_box='$id'
");

header("Location: ../../frontend/pages/box_arsip.php");
exit;
