<?php
session_start();
include __DIR__."/../config/database.php";

/* =========================
   CEK LOGIN & ROLE
========================= */
if (!isset($_SESSION['id_user']) || 
    !in_array($_SESSION['role'], ['admin','staff','pimpinan'])) {
    die("Akses ditolak");
}

/* =========================
   PAGINATION
========================= */
$limit = 50;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* =========================
   HITUNG TOTAL DATA
========================= */
$total_result = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM pemusnahan
");
$total_row  = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];

$total_page = ceil($total_data / $limit);

/* =========================
   QUERY DATA PER HALAMAN
========================= */
$q = mysqli_query($conn,"
    SELECT 
        arsip.nomor_surat,
        arsip.asal_surat,
        pemusnahan.tanggal_pemusnahan
    FROM pemusnahan
    JOIN arsip ON pemusnahan.id_arsip = arsip.id_arsip
    ORDER BY pemusnahan.tanggal_pemusnahan DESC
    LIMIT $offset, $limit
");

$no = $offset + 1;
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

.actions{
    margin-bottom:15px;
    text-align:left;
}

.btn{
    padding:8px 14px;
    background:#e74c3c;
    color:#fff;
    text-decoration:none;
    border-radius:5px;
    display:inline-block;
    font-weight:500;
}

.btn:hover{
    background:#c0392b;
}

.pagination{
    margin:15px 0 25px 0;
    text-align:center;
}

.pagination a{
    padding:6px 12px;
    margin:0 4px;
    border:1px solid #ccc;
    text-decoration:none;
    background:#fff;
    border-radius:4px;
    display:inline-block;
}

.pagination a:hover{
    background:#f0f0f0;
}

.pagination a.active{
    background:#e74c3c;
    color:#fff;
    font-weight:bold;
    border-color:#e74c3c;
}
</style>
</head>
<body>

<div class="container">

<h2>🔥 Laporan Pemusnahan Arsip</h2>

<div class="actions">
    <a href="cetak_pdf.php?jenis=pemusnahan&page=<?= $page ?>" 
       target="_blank" class="btn">
        ⬇ Download PDF (Halaman <?= $page ?>)
    </a>
</div>

<!-- PAGINATION -->
<div class="pagination">

<?php if ($page > 1): ?>
    <a href="?page=<?= $page-1 ?>">« Prev</a>
<?php endif; ?>

<?php for ($i=1; $i <= $total_page; $i++): ?>
    <a href="?page=<?= $i ?>" 
       class="<?= ($i==$page)?'active':'' ?>">
        <?= $i ?>
    </a>
<?php endfor; ?>

<?php if ($page < $total_page): ?>
    <a href="?page=<?= $page+1 ?>">Next »</a>
<?php endif; ?>

</div>

<table>
<tr>
<th>No</th>
<th>Nomor Surat</th>
<th>Asal Surat</th>
<th>Tanggal Pemusnahan</th>
<th>Disetujui Oleh</th>
</tr>

<?php while($r=mysqli_fetch_assoc($q)){ ?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($r['nomor_surat']) ?></td>
<td><?= htmlspecialchars($r['asal_surat']) ?></td>
<td><?= $r['tanggal_pemusnahan'] ?></td>
<td>Pimpinan</td>
</tr>
<?php } ?>

</table>

<br>
<a href="index.php">⬅ Kembali</a>

</div>
</body>
</html>