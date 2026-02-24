<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['role'] !== 'staff') {
    exit("Akses ditolak");
}

/* 
   Karena akses staff hanya lihat arsip,
   langsung arahkan ke daftar arsip utama
*/
header("Location: ../arsip/index.php");
exit;