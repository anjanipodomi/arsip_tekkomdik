<?php
session_start();

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id = $_POST['id_kategori'];
$aktif = $_POST['masa_aktif'];
$inaktif = $_POST['masa_inaktif'];

$cek = mysqli_query($conn,"SELECT * FROM retensi WHERE id_kategori='$id'");

if(mysqli_num_rows($cek)){

    $data = mysqli_fetch_assoc($cek);

    if($data['masa_aktif'] == $aktif && $data['masa_inaktif'] == $inaktif){
        $_SESSION['error'] = "Tidak ada perubahan data.";
        header("Location: " . BASE_URL . "views/atur_retensi.php?id=".$id);
        exit;
    }

    mysqli_query($conn,"
        UPDATE retensi SET
        masa_aktif='$aktif',
        masa_inaktif='$inaktif'
        WHERE id_kategori='$id'
    ");
}else{

    mysqli_query($conn,"
        INSERT INTO retensi(id_kategori,masa_aktif,masa_inaktif)
        VALUES('$id','$aktif','$inaktif')
    ");
}

header("Location: " . BASE_URL . "views/manajemen_retensi.php");
exit;