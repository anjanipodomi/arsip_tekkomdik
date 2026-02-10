<?php
session_start();
include __DIR__."/../config/database.php";

if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','operator','pimpinan'])) {
    die("Akses ditolak");
}

$q = mysqli_query($conn,"
    SELECT 
        arsip.nomor_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        TIMESTAMPDIFF(YEAR, arsip.tanggal_surat, CURDATE()) AS umur
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    ORDER BY umur DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Laporan Retensi Arsip</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:95%;margin:30px auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#eee}
.actions{margin-bottom:15px}
.btn{padding:8px 12px;background:#8e44ad;color:#fff;text-decoration:none;border-radius:4px}
</style>
</head>
<body>

<div class="container">

<h2>⏳ Laporan Retensi Arsip</h2>

<a href="cetak_pdf.php?jenis=retensi" target="_blank">
    ⬇ Download PDF
</a>


<table>
<tr>
<th>No</th>
<th>Nomor Surat</th>
<th>Kategori</th>
<th>Umur (Tahun)</th>
<th>Status Retensi</th>
</tr>

<?php $no=1; while($r=mysqli_fetch_assoc($q)){ ?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($r['nomor_surat']) ?></td>
<td><?= htmlspecialchars($r['nama_kategori']) ?></td>
<td><?= $r['umur'] ?></td>
<td><?= $r['status_arsip'] ?></td>
</tr>
<?php } ?>
</table>

<br>
<a href="index.php">⬅ Kembali</a>

</div>
</body>
</html>
