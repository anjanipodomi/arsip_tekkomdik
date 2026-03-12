<?php
/**
 * Logout Sistem Arsip Inaktif
 * - Menghapus session
 * - Mencatat log logout
 */

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../log/log_helper.php";

/* =========================
   SIMPAN LOG LOGOUT
========================= */
if (isset($_SESSION['id_user'])) {
    simpan_log($conn, $_SESSION['id_user'], "Logout dari sistem");
}

/* =========================
   HAPUS SEMUA SESSION
========================= */
$_SESSION = [];
session_unset();
session_destroy();

/* =========================
   REDIRECT KE LOGIN
========================= */
header("Location: ../../index.php");
exit;
