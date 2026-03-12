<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/head.php';
require_once __DIR__ . '/header.php';

/* ==========================
   SIDEBAR BERDASARKAN ROLE
========================== */

$role = $_SESSION['role'] ?? null;

if ($role === 'admin') {
    require_once __DIR__ . '/sidebar.php';
}

elseif ($role === 'pimpinan') {
    require_once __DIR__ . '/sidebar_pimpinan.php';
}

elseif ($role === 'staff') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function(){
            document.body.classList.add('staff');
        });
    </script>";
}
?>