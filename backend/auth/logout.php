<?php

session_start();

include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

// Jika user sedang login, catat ke audit log
if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    simpan_log($conn, $id_user, "Logout dari sistem");
}

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: login_form.php");
exit;
