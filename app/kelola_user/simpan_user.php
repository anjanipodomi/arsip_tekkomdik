<?php
session_start();
require_once __DIR__ . "/../config/config.php";
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$nama     = trim($_POST['nama_lengkap'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

if ($nama === '' || $username === '' || $password === '' || $role === '') {
    die("Data tidak lengkap");
}

// Cek username
$cek = mysqli_query($conn, "SELECT id_user FROM users WHERE username='$username'");
if (mysqli_num_rows($cek) > 0) {
    die("Username sudah digunakan");
}

// HASH PASSWORD (WAJIB)
$password_hash = md5($password);

// Simpan
mysqli_query($conn, "
    INSERT INTO users (nama_lengkap, username, password, role)
    VALUES ('$nama', '$username', '$password_hash', '$role')
");

header("Location: " . BASE_URL . "views/kelola_user.php");
exit;
