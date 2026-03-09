<?php
require_once __DIR__ . "/app/config/config.php";

// Jika sudah login
if (isset($_SESSION['id_user'])) {

    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: " . BASE_URL . "views/dashboard_admin.php");
            break;

        case 'staff':
            header("Location: " . BASE_URL . "views/dashboard_staff.php");
            break;

        case 'pimpinan':
            header("Location: " . BASE_URL . "views/dashboard_pimpinan.php");
            break;

        default:
            session_unset();
            session_destroy();
            header("Location: " . BASE_URL . "views/login.php");
            break;
    }

} else {
    header("Location: " . BASE_URL . "views/login.php");
}

exit;