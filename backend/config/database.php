<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "arsip_tekkomdik";
$port = 3307; 

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
