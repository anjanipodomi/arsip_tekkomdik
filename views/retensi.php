<?php
session_start();

require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['admin','operator'])) {
    die("Akses ditolak");
}

/* ==========================
   HITUNG STATUS ARSIP
========================== */
function hitung($conn, $status){
    $q = mysqli_query($conn,"
        SELECT COUNT(*) total 
        FROM arsip 
        WHERE status_arsip='$status'
    ");
    return mysqli_fetch_assoc($q)['total'];
}

$inaktif    = hitung($conn,'Inaktif');
$permanen   = hitung($conn,'Permanen');
$siapMusnah = hitung($conn,'Siap Musnah');

include '../layout/init.php';
include '../layout/head.php';
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-hourglass-split me-2"></i> Dahboard Retensi Arsip
</h3>

<div class="row g-4 mb-4">

    <div class="col-md-4">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h6 class="text-muted">Inaktif</h6>
                <h3 class="fw-bold text-primary"><?= $inaktif ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h6 class="text-muted">Permanen</h6>
                <h3 class="fw-bold text-success"><?= $permanen ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h6 class="text-muted">Siap Musnah</h6>
                <h3 class="fw-bold text-danger"><?= $siapMusnah ?></h3>
            </div>
        </div>
    </div>

</div>

<div class="text-start">
    <a href="<?= BASE_URL ?>views/Manajemen_retensi.php"
    class="btn btn-primary">
    ⚙ Kelola Retensi Arsip
    </a>
</div>

</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>