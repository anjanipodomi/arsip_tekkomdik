<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../log/log_helper.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header("Location: ../../views/login.php?error=Username dan password wajib diisi");
    exit;
}

$username_db = mysqli_real_escape_string($conn, $username);

$q_user = mysqli_query($conn, "
    SELECT id_user, username, password, role
    FROM users
    WHERE username='$username_db'
    LIMIT 1
");

if (mysqli_num_rows($q_user) === 0) {
    header("Location: ../../views/login.php?error=Username tidak ditemukan");
    exit;
}

$user = mysqli_fetch_assoc($q_user);

if ($user['password'] !== md5($password)) {
    header("Location: ../../views/login.php?error=Password salah");
    exit;
}

$_SESSION['id_user']  = $user['id_user'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

simpan_log($conn, $user['id_user'], "Login ke sistem");

header("Location: ../../index.php");
exit;