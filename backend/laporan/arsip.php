<?php
session_start();
include __DIR__."/../config/database.php";

if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','operator','pimpinan'])) {
    die("Akses ditolak");
}

$q = mysqli_query($conn,"
    SELECT 
        arsip.nomor_surat,
        arsip.asal_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        box.kode_box
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    ORDER BY arsip.tanggal_surat DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Laporan Data Arsip</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:95%;margin:30px auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#eee}
.actions{margin-bottom:15px}
.btn{padding:8px 12px;background:#3498db;color:#fff;text-decoration:none;border-radius:4px}
</style>
</head>
<body>

<div class="container">

<h2>📂 Laporan Data Arsip</h2>

<div class="actions">
    <a href="cetak_pdf.php?jenis=arsip" target="_blank" class="btn">
        ⬇ Download PDF
    </a>
</div>


<table>
<tr>
<th>No</th>
<th>Nomor Surat</th>
<th>Asal Surat</th>
<th>Tanggal</th>
<th>Kategori</th>
<th>Box</th>
<th>Status</th>
</tr>

<?php $no=1; while($r=mysqli_fetch_assoc($q)){ ?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($r['nomor_surat']) ?></td>
<td><?= htmlspecialchars($r['asal_surat']) ?></td>
<td><?= $r['tanggal_surat'] ?></td>
<td><?= htmlspecialchars($r['nama_kategori']) ?></td>
<td><?= htmlspecialchars($r['kode_box']) ?></td>
<td><?= $r['status_arsip'] ?></td>
</tr>
<?php } ?>
</table>

<br>
<a href="index.php">⬅ Kembali</a>

</div>
</body>
</html>
