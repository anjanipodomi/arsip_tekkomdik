<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status'=>'error','pesan'=>'Akses ditolak']); exit;
}

$kode = trim($_POST['kode_box'] ?? '');
$lokasi = trim($_POST['lokasi_fisik'] ?? '');

if ($kode==='' || $lokasi==='') {
    echo json_encode(['status'=>'error','pesan'=>'Kode dan lokasi wajib']); exit;
}

$kode_db = mysqli_real_escape_string($conn,$kode);
$lokasi_db = mysqli_real_escape_string($conn,$lokasi);

mysqli_query($conn,"
    INSERT INTO box (kode_box,lokasi_fisik,status)
    VALUES ('$kode_db','$lokasi_db','aktif')
");

echo json_encode([
    'status'=>'ok',
    'id'=>mysqli_insert_id($conn),
    'kode'=>$kode,
    'lokasi'=>$lokasi
]);
