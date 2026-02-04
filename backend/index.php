<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: auth/login_form.php");
    exit;
}

header("Location: dashboard.php");
exit;
