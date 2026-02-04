<?php
/**
 * AJAX Tambah Box Arsip
 * Role : Admin
 */

session_start();
include __DIR__ . "/../config/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status'=>'error','pesan'=>'Akses ditolak']);
    exit;
}

$kode   = trim($_POST['kode_box'] ?? '');
$lokasi = trim($_POST['lokasi_fisik'] ?? '');
$ket    = trim($_POST['keterangan'] ?? '');

if ($kode === '' || $lokasi === '') {
    echo json_encode(['status'=>'error','pesan'=>'Data wajib diisi']);
    exit;
}

// cek duplikat
$cek = mysqli_query($conn, "
    SELECT id_box FROM box 
    WHERE kode_box='".mysqli_real_escape_string($conn,$kode)."'
");

if (mysqli_num_rows($cek) > 0) {
    echo json_encode(['status'=>'error','pesan'=>'Kode box sudah ada']);
    exit;
}

mysqli_query($conn, "
    INSERT INTO box (kode_box, lokasi_fisik, keterangan)
    VALUES (
        '".mysqli_real_escape_string($conn,$kode)."',
        '".mysqli_real_escape_string($conn,$lokasi)."',
        '".mysqli_real_escape_string($conn,$ket)."'
    )
");

echo json_encode([
    'status' => 'ok',
    'id'     => mysqli_insert_id($conn),
    'kode'   => $kode,
    'lokasi' => $lokasi
]);
exit;
