<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id   = $_POST['id_kategori'] ?? '';
$nama = trim($_POST['nama_kategori'] ?? '');

if ($id==='' || $nama==='') {
    $_SESSION['error'] = "Data tidak valid";
    header("Location: edit_kategori.php?id=$id");
    exit;
}

$q = mysqli_query($conn,"SELECT * FROM kategori WHERE id_kategori='$id'");
$d = mysqli_fetch_assoc($q);

if (!$d) {
    $_SESSION['error'] = "Kategori tidak ditemukan";
    header("Location: index.php");
    exit;
}

if ($d['status']==='nonaktif'){
    $_SESSION['error'] = "Kategori nonaktif tidak bisa diedit";
    header("Location: index.php");
    exit;
}

$nama_db = mysqli_real_escape_string($conn,$nama);

/* ===============================
   CEK TIDAK ADA PERUBAHAN
================================ */
if ($d['nama_kategori'] === $nama) {
    $_SESSION['error'] = "Tidak ada perubahan data";
    header("Location: edit_kategori.php?id=$id");
    exit;
}

/* ===============================
   UPDATE
================================ */
mysqli_query($conn,"
    UPDATE kategori
    SET nama_kategori='$nama_db'
    WHERE id_kategori='$id'
");

header("Location: index.php");
exit;
