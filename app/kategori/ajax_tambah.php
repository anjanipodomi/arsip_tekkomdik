<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status'=>'error','pesan'=>'Akses ditolak']);
    exit;
}

$klasifikasi = trim($_POST['klasifikasi_kategori'] ?? '');
$nama = trim($_POST['nama_kategori'] ?? '');
if ($klasifikasi==='' || $nama==='') {
    echo json_encode([
        'status'=>'error',
        'pesan'=>'Klasifikasi dan nama kategori wajib diisi'
    ]);
    exit;
}

$klasifikasi_db = mysqli_real_escape_string($conn,$klasifikasi);
$nama_db = mysqli_real_escape_string($conn,$nama);

$sql = "
INSERT INTO kategori 
(klasifikasi_kategori, nama_kategori, keterangan, status)
VALUES 
('$klasifikasi_db','$nama_db',NULL,'aktif')
";

if(!mysqli_query($conn,$sql)){
    echo json_encode([
        'status'=>'error',
        'pesan'=>mysqli_error($conn)
    ]);
    exit;
}

echo json_encode([
    'status'=>'ok',
    'id'=>mysqli_insert_id($conn),
    'nama'=>$nama
]);
