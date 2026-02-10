<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id   = $_POST['id_kategori'] ?? '';
$nama = trim($_POST['nama_kategori'] ?? '');

if ($id==='' || $nama==='') {
    die("Data tidak valid");
}

$q = mysqli_query($conn,"SELECT status FROM kategori WHERE id_kategori='$id'");
$d = mysqli_fetch_assoc($q);

if ($d['status']==='nonaktif'){
    die("Kategori nonaktif tidak bisa diedit");
}

$nama_db = mysqli_real_escape_string($conn,$nama);

mysqli_query($conn,"
    UPDATE kategori
    SET nama_kategori='$nama_db'
    WHERE id_kategori='$id'
");

header("Location: index.php");
exit;
