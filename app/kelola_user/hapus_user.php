<?php
session_start();
<<<<<<< HEAD
require_once __DIR__ . "/../config/database.php";
=======
include __DIR__ . "/../config/database.php";
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

<<<<<<< HEAD
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
=======
$id = $_GET['id'];

mysqli_query($conn,"DELETE FROM users WHERE id_user='$id'");

header("Location: index.php");
exit;
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de
