<?php
session_start();
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../log/log_helper.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id_box = $_POST['id_box'] ?? '';
$kode   = trim($_POST['kode_box'] ?? '');
$lokasi = trim($_POST['lokasi_fisik'] ?? '');

if ($kode === '' || $lokasi === '') {
    die("Kode box dan lokasi wajib diisi");
}

$kode_db   = mysqli_real_escape_string($conn, $kode);
$lokasi_db = mysqli_real_escape_string($conn, $lokasi);

/* ======================
   EDIT BOX
====================== */
if ($id_box !== '') {

    // ambil data lama
    $q = mysqli_query($conn,"
        SELECT kode_box, lokasi_fisik, status
        FROM box
        WHERE id_box='$id_box'
    ");
    $d = mysqli_fetch_assoc($q);

    if (!$d) die("Data box tidak ditemukan");

    if ($d['status'] === 'nonaktif') {
        die("Status nonaktif, tidak bisa diedit");
    }

    /* ======================
       CEK TIDAK ADA PERUBAHAN
    ====================== */
    if (
        $kode === $d['kode_box'] &&
        $lokasi === $d['lokasi_fisik']
    ) {
        $_SESSION['error'] = "Tidak ada perubahan data";
        $_SESSION['old'] = $_POST;

        header("Location: " . BASE_URL . "views/edit_box.php?id=$id_box");
        exit;
    }

    /* ======================
       UPDATE JIKA ADA PERUBAHAN
    ====================== */
    mysqli_query($conn,"
        UPDATE box
        SET kode_box='$kode_db',
            lokasi_fisik='$lokasi_db'
        WHERE id_box='$id_box'
    ");

    simpan_log(
        $conn,
        $_SESSION['id_user'],
        "Edit box ID $id_box",
        "Box"
    );

/* ======================
   TAMBAH BOX
====================== */
} else {

    mysqli_query($conn,"
        INSERT INTO box (kode_box,lokasi_fisik,status)
        VALUES ('$kode_db','$lokasi_db','aktif')
    ");

    $id = mysqli_insert_id($conn);
    simpan_log($conn,$_SESSION['id_user'],"Tambah box ID $id","Box");
}

header("Location: " . BASE_URL . "views/box_arsip.php");
exit;
