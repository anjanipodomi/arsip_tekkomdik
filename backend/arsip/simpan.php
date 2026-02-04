<?php
session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

// ==========================
// AMBIL INPUT
// ==========================
$asal   = $_POST['asal_surat'];
$nomor  = $_POST['nomor_surat'];
$tgl    = $_POST['tanggal_surat'];
$isi    = $_POST['isi_ringkas'];
$klas   = $_POST['klasifikasi_keamanan'];
$jumlah = $_POST['jumlah_berkas'];
$tingkat= $_POST['tingkat_perkembangan'];
$id_kat = $_POST['id_kategori'];
$id_box = $_POST['id_box'];

// ==========================
// UPLOAD FILE
// ==========================
$uploadDir = "../assets/uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$namaFile = time() . "_" . basename($_FILES['file_arsip']['name']);
$target   = $uploadDir . $namaFile;

if (!move_uploaded_file($_FILES['file_arsip']['tmp_name'], $target)) {
    die("Upload file gagal");
}

// PATH YANG DISIMPAN KE DATABASE
$file_path = "assets/uploads/" . $namaFile;

// ==========================
// INSERT DATABASE
// ==========================
mysqli_query($conn, "
    INSERT INTO arsip (
        asal_surat, tanggal_surat, nomor_surat,
        klasifikasi_keamanan, isi_ringkas,
        jumlah_berkas, tingkat_perkembangan,
        lokasi_simpan, id_kategori, id_box,
        status_arsip, tanggal_input, file_path
    ) VALUES (
        '$asal','$tgl','$nomor',
        '$klas','$isi',
        '$jumlah','$tingkat',
        'Box','$id_kat','$id_box',
        'Inaktif',CURDATE(),'$file_path'
    )
");

$id_arsip = mysqli_insert_id($conn);

// ==========================
// AUDIT LOG
// ==========================
simpan_log($conn, $_SESSION['id_user'], "Upload arsip ID $id_arsip", "Arsip");

header("Location: index.php");
exit;
