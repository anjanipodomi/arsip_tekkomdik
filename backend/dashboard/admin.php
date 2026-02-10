<?php
/**
 * Dashboard Admin / Operator Arsip
 * Fokus: Monitoring & Statistik Arsip
 */

session_start();
include __DIR__ . "/../config/database.php";

// ==========================
// CEK LOGIN & ROLE
// ==========================
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['admin', 'operator'])) {
    die("Akses ditolak");
}

$id_user = $_SESSION['id_user'];

// ==========================
// STATISTIK ARSIP
// ==========================
function countArsip($conn, $where = '') {
    $q = mysqli_query($conn, "SELECT COUNT(*) total FROM arsip $where");
    return mysqli_fetch_assoc($q)['total'];
}

$total_arsip = countArsip($conn);
$inaktif     = countArsip($conn, "WHERE status_arsip='Inaktif'");
$permanen    = countArsip($conn, "WHERE status_arsip='Permanen'");
$siap_musnah = countArsip($conn, "WHERE status_arsip='Siap Musnah'");

// ==========================
// DATA DIAGRAM
// ==========================

// Arsip per Kategori
$arsip_per_kategori = mysqli_query($conn, "
    SELECT kategori.nama_kategori, COUNT(arsip.id_arsip) total
    FROM kategori
    LEFT JOIN arsip ON kategori.id_kategori = arsip.id_kategori
    GROUP BY kategori.id_kategori
");

// Arsip per Box
$arsip_per_box = mysqli_query($conn, "
    SELECT box.kode_box, COUNT(arsip.id_arsip) total
    FROM box
    LEFT JOIN arsip ON box.id_box = arsip.id_box
    GROUP BY box.id_box
");

// ==========================
// NOTIFIKASI
// ==========================
$notif = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(*) total 
        FROM notifikasi 
        WHERE id_user='$id_user' AND status='baru'
    ")
)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin Arsip</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}
.container {
    width: 95%;
    margin: 30px auto;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.menu {
    background: #fff;
    padding: 15px;
    margin: 20px 0;
    border-radius: 8px;
}
.menu a {
    margin-right: 15px;
    text-decoration: none;
    font-weight: bold;
    color: #2c3e50;
}
.stat {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}
.box {
    flex: 1;
    background: #fff;
    padding: 20px;
    text-align: center;
    border-radius: 8px;
}
.chart-box {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.logout {
    color: red;
    text-decoration: none;
    font-weight: bold;
}
</style>
</head>

<body>
<div class="container">

<!-- HEADER -->
<div class="header">
    <h2>📊 Dashboard Admin / Operator Arsip</h2>
    <a href="../auth/logout.php" class="logout">Logout</a>
</div>

<!-- MENU -->
<div class="menu">
    <strong>Menu:</strong><br><br>

    <a href="../arsip/index.php">📂 Manajemen Arsip</a>
    <a href="../arsip/tambah_arsip.php">➕ Upload Arsip</a>

    <!-- MASTER DATA -->
    <a href="../kategori/index.php">🏷️ Kategori Arsip</a>
    <a href="../box/index.php">📦 Box Arsip</a>

    <!-- PROSES ARSIP -->
    <a href="../dashboard/retensi.php">⏳ Retensi Arsip</a>

    <!-- ADMIN -->
    <a href="../kelola_user/index.php">👥 Kelola User</a>
    <a href="../laporan/index.php">📄 Laporan</a>
    <a href="../notifikasi/index.php">🔔 Notifikasi (<?= $notif ?>)</a>
</div>


<!-- STATISTIK -->
<div class="stat">
    <div class="box">
        <h3>Total Arsip</h3>
        <h1><?= $total_arsip ?></h1>
    </div>
    <div class="box">
        <h3>Inaktif</h3>
        <h1><?= $inaktif ?></h1>
    </div>
    <div class="box">
        <h3>Permanen</h3>
        <h1><?= $permanen ?></h1>
    </div>
    <div class="box">
        <h3>Siap Musnah</h3>
        <h1><?= $siap_musnah ?></h1>
    </div>
</div>

<!-- DIAGRAM -->
<div class="chart-box">
    <h3>📦 Arsip per Box</h3>
    <canvas id="chartBox"></canvas>
</div>

<div class="chart-box">
    <h3>🏷️ Arsip per Kategori</h3>
    <canvas id="chartKategori"></canvas>
</div>

</div>

<script>
// ===== Diagram Box =====
new Chart(document.getElementById('chartBox'), {
    type: 'pie',
    data: {
        labels: [
            <?php while($b = mysqli_fetch_assoc($arsip_per_box)) {
                echo "'" . $b['kode_box'] . "',";
            } ?>
        ],
        datasets: [{
            data: [
                <?php
                mysqli_data_seek($arsip_per_box, 0);
                while($b = mysqli_fetch_assoc($arsip_per_box)) {
                    echo $b['total'] . ",";
                } ?>
            ]
        }]
    }
});

// ===== Diagram Kategori =====
new Chart(document.getElementById('chartKategori'), {
    type: 'bar',
    data: {
        labels: [
            <?php while($k = mysqli_fetch_assoc($arsip_per_kategori)) {
                echo "'" . $k['nama_kategori'] . "',";
            } ?>
        ],
        datasets: [{
            data: [
                <?php
                mysqli_data_seek($arsip_per_kategori, 0);
                while($k = mysqli_fetch_assoc($arsip_per_kategori)) {
                    echo $k['total'] . ",";
                } ?>
            ]
        }]
    }
});
</script>

</body>
</html>
