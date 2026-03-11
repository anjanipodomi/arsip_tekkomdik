<?php
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";


/* ==========================
   STATISTIK
========================== */
function countArsip($conn, $where = '') {
    $q = mysqli_query($conn, "SELECT COUNT(*) total FROM arsip $where");
    return mysqli_fetch_assoc($q)['total'];
}

$total_arsip = countArsip($conn);
$inaktif     = countArsip($conn, "WHERE status_arsip='Inaktif'");
$permanen    = countArsip($conn, "WHERE status_arsip='Permanen'");
$siap_musnah = countArsip($conn, "WHERE status_arsip='Siap Musnah'");

/* ==========================
   DATA GRAFIK
========================== */

// Arsip per Box
$arsip_per_box = mysqli_query($conn, "
    SELECT box.kode_box, COUNT(arsip.id_arsip) total
    FROM box
    LEFT JOIN arsip ON box.id_box = arsip.id_box
    GROUP BY box.id_box
");

// Arsip per Kategori
$arsip_per_kategori = mysqli_query($conn, "
    SELECT kategori.nama_kategori, COUNT(arsip.id_arsip) total
    FROM kategori
    LEFT JOIN arsip ON kategori.id_kategori = arsip.id_kategori
    GROUP BY kategori.id_kategori
");
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
<i class="bi bi-speedometer2 me-2"></i> Dashboard Admin
</h3>

<!-- ==========================
     STATISTIK CARD
========================== -->
<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h6 class="text-muted">Total Arsip</h6>
                <h3 class="fw-bold"><?= $total_arsip ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h6 class="text-muted">Inaktif</h6>
                <h3 class="fw-bold text-warning"><?= $inaktif ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h6 class="text-muted">Permanen</h6>
                <h3 class="fw-bold text-success"><?= $permanen ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h6 class="text-muted">Siap Musnah</h6>
                <h3 class="fw-bold text-danger"><?= $siap_musnah ?></h3>
                <small class="text-muted">Menunggu persetujuan</small>
            </div>
        </div>
    </div>

</div>

<!-- ==========================
     GRAFIK
========================== -->
<div class="row g-4">

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <span class="fw-semibold">
                    <i class="bi bi-box-seam me-2"></i> Arsip per Box
                </span>
            </div>
            <div class="card-body">
                <div style="height:300px;">
                    <canvas id="chartBox"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <span class="fw-semibold">
                    <i class="bi bi-tags me-2"></i> Arsip per Kategori
                </span>
            </div>
            <div class="card-body">
                <div style="height:300px;">
                    <canvas id="chartKategori"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

</div>
</main>

<!-- ==========================
     CHART JS
========================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

// PIE - ARSIP PER BOX
new Chart(document.getElementById('chartBox'), {
    type: 'pie',
    data: {
        labels: [
            <?php 
            mysqli_data_seek($arsip_per_box,0);
            while($b=mysqli_fetch_assoc($arsip_per_box)){
                echo "'".$b['kode_box']."',";
            }
            ?>
        ],
        datasets: [{
            data: [
                <?php
                mysqli_data_seek($arsip_per_box,0);
                while($b=mysqli_fetch_assoc($arsip_per_box)){
                    echo $b['total'].",";
                }
                ?>
            ],
            backgroundColor:[
                '#2563eb',
                '#16a34a',
                '#f59e0b',
                '#dc2626',
                '#7c3aed',
                '#0891b2'
            ]
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{ position:'bottom' }
        }
    }
});

// BAR - ARSIP PER KATEGORI
new Chart(document.getElementById('chartKategori'), {
    type: 'bar',
    data: {
        labels: [
            <?php 
            mysqli_data_seek($arsip_per_kategori,0);
            while($k=mysqli_fetch_assoc($arsip_per_kategori)){
                echo "'".$k['nama_kategori']."',";
            }
            ?>
        ],
        datasets: [{
            data: [
                <?php
                mysqli_data_seek($arsip_per_kategori,0);
                while($k=mysqli_fetch_assoc($arsip_per_kategori)){
                    echo $k['total'].",";
                }
                ?>
            ],
            backgroundColor:'#2563eb'
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true } }
    }
});

</script>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>