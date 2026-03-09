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
    UPDATE kategori
    SET status='$aksi'
    WHERE id_kategori='$id'
");

header("Location: index.php");
exit;
