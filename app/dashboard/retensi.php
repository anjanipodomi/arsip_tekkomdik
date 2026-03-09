<?php
/**
 * Dashboard Retensi Arsip
 * Role: Admin & Operator Arsip
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

if (!in_array($_SESSION['role'], ['admin','operator'])) {
    die("Akses ditolak");
}

// ==========================
// HITUNG STATUS ARSIP
// ==========================
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Retensi Arsip</title>

<style>
body { font-family: Arial; background:#f4f6f8; }
.container { width:90%; margin:30px auto; }
.stat { display:flex; gap:20px; margin-bottom:20px; }
.card {
    flex:1;
    background:#fff;
    padding:20px;
    text-align:center;
    border-radius:6px;
}
.inaktif   { border-top:5px solid #3498db; }
.permanen  { border-top:5px solid #2ecc71; }
.siap      { border-top:5px solid #e67e22; }

.btn {
    display:inline-block;
    padding:10px 16px;
    background:#8e44ad;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
}
.back {
    display:inline-block;
    margin-top:15px;
}
</style>
</head>

<body>
<div class="container">

<h2>⏳ Dashboard Retensi Arsip</h2>
<?php if (isset($_GET['success'])): ?>
    <div style="
        background:#d4edda;
        color:#155724;
        padding:12px;
        margin:15px 0;
        border-radius:6px;
        font-weight:bold;
    ">
        ✅ Proses retensi berhasil dijalankan.
        <br>
        🔄 <?= (int)($_GET['update'] ?? 0) ?> arsip diperbarui.
        <br>
        🗑 <?= (int)($_GET['musnah'] ?? 0) ?> arsip siap musnah telah dikirim ke pimpinan.
    </div>
<?php endif; ?>

<div class="stat">
    <div class="card inaktif">
        <h3>Inaktif</h3>
        <h1><?= $inaktif ?></h1>
    </div>

    <div class="card permanen">
        <h3>Permanen</h3>
        <h1><?= $permanen ?></h1>
    </div>

    <div class="card siap">
        <h3>Siap Musnah</h3>
        <h1><?= $siapMusnah ?></h1>
    </div>
</div>

<a href="../retensi/index.php" class="btn">
   ⚙ Kelola Retensi Arsip
</a>

<br>

<a href="admin.php" class="back">⬅ Kembali ke Dashboard</a>

</div>
</body>
</html>
