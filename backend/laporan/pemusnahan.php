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
        pemusnahan.tanggal_pemusnahan,
        users.nama_lengkap
    FROM pemusnahan
    JOIN arsip ON pemusnahan.id_arsip = arsip.id_arsip
    JOIN users ON pemusnahan.disetujui_oleh = users.id_user
    ORDER BY pemusnahan.tanggal_pemusnahan DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Laporan Pemusnahan Arsip</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:95%;margin:30px auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#eee}
.actions{margin-bottom:15px}
.btn{padding:8px 12px;background:#e74c3c;color:#fff;text-decoration:none;border-radius:4px}
</style>
</head>
<body>

<div class="container">

<h2>🔥 Laporan Pemusnahan Arsip</h2>

<a href="cetak_pdf.php?jenis=pemusnahan" target="_blank">
    ⬇ Download PDF
</a>


<table>
<tr>
<th>No</th>
<th>Nomor Surat</th>
<th>Asal Surat</th>
<th>Tanggal Pemusnahan</th>
<th>Disetujui Oleh</th>
</tr>

<?php $no=1; while($r=mysqli_fetch_assoc($q)){ ?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($r['nomor_surat']) ?></td>
<td><?= htmlspecialchars($r['asal_surat']) ?></td>
<td><?= $r['tanggal_pemusnahan'] ?></td>
<td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
</tr>
<?php } ?>
</table>

<br>
<a href="index.php">⬅ Kembali</a>

</div>
</body>
</html>
