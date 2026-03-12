<?php
/*
|--------------------------------------------------------------------------
| KONFIGURASI APLIKASI
|--------------------------------------------------------------------------
| File ini berisi pengaturan global seperti BASE_URL
| dan pengaturan umum sistem.
|--------------------------------------------------------------------------
*/

// ================= BASE URL =================
// Pastikan ada garis miring (/) di akhir
define('BASE_URL', 'http://localhost/arsip_tekkomdik/');



// ================= TIMEZONE =================
date_default_timezone_set('Asia/Jakarta');


// ================= SESSION ==================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}