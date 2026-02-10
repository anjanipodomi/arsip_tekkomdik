<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    die("Akses ditolak");
}

$q = mysqli_query($conn, "
    SELECT 
        users.nama_lengkap,
        log_aktivitas.aktivitas,
        log_aktivitas.modul,
        log_aktivitas.tanggal
    FROM log_aktivitas
    LEFT JOIN users ON log_aktivitas.id_user = users.id_user
    ORDER BY log_aktivitas.tanggal DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Log Aktivitas</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:95%;margin:30px auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#eee}
.actions{margin-bottom:15px}
.btn{
    padding:8px 12px;
    background:#2c3e50;
    color:#fff;
    text-decoration:none;
    border-radius:4px
}
.badge{
    padding:4px 8px;
    background:#3498db;
    color:#fff;
    border-radius:4px;
    font-size:12px
}
</style>
</head>
<body>

<div class="container">

<h2>🧾 Laporan Log Aktivitas Sistem</h2>

<div class="actions">
    <a href="cetak_pdf.php?jenis=log" target="_blank" class="btn">
        ⬇ Download PDF
    </a>
</div>

<table>
<tr>
<th>No</th>
<th>Nama User</th>
<th>Aktivitas</th>
<th>Modul</th>
<th>Tanggal & Waktu</th>
</tr>

<?php $no=1; while($r=mysqli_fetch_assoc($q)){ ?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($r['nama_lengkap'] ?? '-') ?></td>
<td><?= htmlspecialchars($r['aktivitas']) ?></td>
<td>
    <span class="badge">
        <?= htmlspecialchars($r['modul'] ?: '-') ?>
    </span>
</td>
<td><?= date('d-m-Y H:i', strtotime($r['tanggal'])) ?></td>
</tr>
<?php } ?>

</table>

<br>
<a href="index.php">⬅ Kembali</a>

</div>
</body>
</html>
