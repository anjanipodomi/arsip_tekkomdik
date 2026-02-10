<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status'=>'error','pesan'=>'Akses ditolak']);
    exit;
}

$nama = trim($_POST['nama_kategori'] ?? '');
if ($nama==='') {
    echo json_encode(['status'=>'error','pesan'=>'Nama kategori wajib diisi']);
    exit;
}

$nama_db = mysqli_real_escape_string($conn,$nama);

mysqli_query($conn,"
    INSERT INTO kategori (nama_kategori,status)
    VALUES ('$nama_db','aktif')
");

echo json_encode([
    'status'=>'ok',
    'id'=>mysqli_insert_id($conn),
    'nama'=>$nama
]);
