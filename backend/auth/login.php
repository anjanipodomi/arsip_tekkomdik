<?php
session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login_form.php");
    exit;
}

$username = $_POST['username'] ?? '';
$password = md5($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    die("Username atau password kosong");
}

$query = mysqli_query($conn, "
    SELECT * FROM users 
    WHERE username='$username' AND password='$password'
");

$data = mysqli_fetch_assoc($query);

if ($data) {
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['role']    = $data['role'];

    simpan_log($conn, $data['id_user'], "Login ke sistem");

    header("Location: ../dashboard.php");
    exit;
}

echo "Login gagal";
