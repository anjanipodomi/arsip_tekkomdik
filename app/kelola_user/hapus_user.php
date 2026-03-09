<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id = $_GET['id'];

mysqli_query($conn,"DELETE FROM users WHERE id_user='$id'");

header("Location: index.php");
exit;
