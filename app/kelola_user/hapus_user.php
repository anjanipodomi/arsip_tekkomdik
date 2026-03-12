<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id = $_GET['id'] ?? '';

if ($id == '') {
    die("ID user tidak ditemukan");
}

/* ==========================
   CEK USER ADA ATAU TIDAK
========================== */
$cek = mysqli_query($conn,"SELECT id_user FROM users WHERE id_user='$id'");
if(mysqli_num_rows($cek) == 0){
    die("User tidak ditemukan");
}

/* ==========================
   HAPUS LOG AKTIVITAS USER
========================== */
mysqli_query($conn,"DELETE FROM log_aktivitas WHERE id_user='$id'");

/* ==========================
   HAPUS NOTIFIKASI USER
========================== */
mysqli_query($conn,"DELETE FROM notifikasi WHERE id_user='$id'");


/* ==========================
   HAPUS USER
========================== */
$hapus = mysqli_query($conn,"DELETE FROM users WHERE id_user='$id'");

if(!$hapus){
    die("Gagal menghapus: " . mysqli_error($conn));
}

/* ==========================
   REDIRECT
========================== */
header("Location: ../../views/kelola_user.php");
exit;
