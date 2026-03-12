<?php

$notif_count = 0;

if (isset($_SESSION['id_user'])) {

    require_once __DIR__ . "/../app/config/database.php";

    $id_user = $_SESSION['id_user'];

    $result = mysqli_query($conn, "
        SELECT COUNT(*) as total
        FROM notifikasi
        WHERE id_user = '$id_user'
        AND status = 'baru'
    ");

    if ($row = mysqli_fetch_assoc($result)) {
        $notif_count = $row['total'];
    }
}
?>