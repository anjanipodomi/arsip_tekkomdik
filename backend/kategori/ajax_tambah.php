<?php
/**
 * AJAX Tambah Kategori Arsip
 * Role : Admin
 * Dipanggil dari modal (tanpa reload halaman)
 */

session_start();
include __DIR__ . "/../config/database.php";

header('Content-Type: application/json');

// ==========================
// CEK LOGIN & ROLE
// ==========================
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'status' => 'error',
        'pesan'  => 'Akses ditolak'
    ]);
    exit;
}

// ==========================
// VALIDASI INPUT
// ==========================
$nama = trim($_POST['nama_kategori'] ?? '');

if ($nama === '') {
    echo json_encode([
        'status' => 'error',
        'pesan'  => 'Nama kategori wajib diisi'
    ]);
    exit;
}

// ==========================
// CEK DUPLIKASI
// ==========================
$cek = mysqli_query($conn, "
    SELECT id_kategori 
    FROM kategori 
    WHERE nama_kategori = '".mysqli_real_escape_string($conn, $nama)."'
");

if (mysqli_num_rows($cek) > 0) {
    echo json_encode([
        'status' => 'error',
        'pesan'  => 'Kategori sudah ada'
    ]);
    exit;
}

// ==========================
// INSERT KATEGORI
// ==========================
mysqli_query($conn, "
    INSERT INTO kategori (nama_kategori)
    VALUES ('".mysqli_real_escape_string($conn, $nama)."')
");

$id = mysqli_insert_id($conn);

// ==========================
// RESPONSE SUKSES
// ==========================
echo json_encode([
    'status' => 'ok',
    'id'     => $id,
    'nama'   => $nama
]);
exit;
