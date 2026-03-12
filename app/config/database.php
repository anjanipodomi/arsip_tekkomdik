<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "arsip_tekkomdik";
<<<<<<< HEAD
$port = 3306;
=======
$port = 3307;
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}