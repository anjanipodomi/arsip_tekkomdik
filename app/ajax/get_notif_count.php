<?php
session_start();
include "../../backend/config/database.php";

if (!isset($_SESSION['id_user']) 
    || !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    echo 0;
    exit;
}


$id_user = $_SESSION['id_user'];

$q = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM notifikasi
    WHERE id_user='$id_user'
    AND status='baru'
");

$row = mysqli_fetch_assoc($q);
echo $row['total'] ?? 0;
