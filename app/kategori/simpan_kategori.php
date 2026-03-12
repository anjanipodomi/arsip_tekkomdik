<?php
session_start();
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id   = $_POST['id_kategori'] ?? '';
$klasifikasi = trim($_POST['klasifikasi_kategori'] ?? '');
$nama = trim($_POST['nama_kategori'] ?? '');
<<<<<<< HEAD
/* ===============================
   VALIDASI SPASI KOSONG
================================ */
if ($nama === '' || $klasifikasi === '') {
    $_SESSION['error'] = "Nama dan klasifikasi tidak boleh kosong";
    header("Location: " . BASE_URL . "views/edit_kategori.php?id=$id");
    exit;
}
=======
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de

if ($id==='' || $nama==='' || $klasifikasi==='') {
    $_SESSION['error'] = "Data tidak valid";
    header("Location: " . BASE_URL . "views/edit_kategori.php?id=$id");
    exit;
}

$q = mysqli_query($conn,"SELECT * FROM kategori WHERE id_kategori='$id'");
$d = mysqli_fetch_assoc($q);

if (!$d) {
    $_SESSION['error'] = "Kategori tidak ditemukan";
    header("Location: " . BASE_URL . "views/kategori.php");
    exit;
}

if ($d['status']==='nonaktif'){
    $_SESSION['error'] = "Kategori nonaktif tidak bisa diedit";
    header("Location: " . BASE_URL . "views/kategori.php");
    exit;
}

$nama_db = mysqli_real_escape_string($conn,$nama);
$klasifikasi_db = mysqli_real_escape_string($conn,$klasifikasi);

/* ===============================
   CEK TIDAK ADA PERUBAHAN
================================ */
<<<<<<< HEAD
if (trim($d['nama_kategori']) === $nama && trim($d['klasifikasi_kategori']) === $klasifikasi) {
=======
if ($d['nama_kategori'] === $nama && $d['klasifikasi_kategori'] === $klasifikasi) {
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de
    $_SESSION['error'] = "Tidak ada perubahan data";
    header("Location: " . BASE_URL . "views/edit_kategori.php?id=$id");
    exit;
}

/* ===============================
   UPDATE
================================ */
mysqli_query($conn,"
    UPDATE kategori
    SET 
        klasifikasi_kategori='$klasifikasi_db',
        nama_kategori='$nama_db'
    WHERE id_kategori='$id'
");

/* ===============================
   REDIRECT SETELAH UPDATE
================================ */
header("Location: " . BASE_URL . "views/kategori.php");
exit;