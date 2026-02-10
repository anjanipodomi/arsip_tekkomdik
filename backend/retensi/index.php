<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$data = mysqli_query($conn, "
    SELECT 
        kategori.id_kategori,
        kategori.nama_kategori,
        retensi.masa_aktif,
        retensi.masa_inaktif
    FROM kategori
    LEFT JOIN retensi ON kategori.id_kategori = retensi.id_kategori
    ORDER BY kategori.nama_kategori
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manajemen Retensi Arsip</title>
<style>
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#eee}
.warn{color:red;font-weight:bold}
.btn{padding:5px 10px;text-decoration:none}
</style>
</head>
<body>

<h2>⏳ Manajemen Retensi Arsip</h2>

<table>
<tr>
    <th>Kategori</th>
    <th>Masa Aktif (th)</th>
    <th>Masa Inaktif (th)</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php while($r=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= htmlspecialchars($r['nama_kategori']) ?></td>
<td><?= $r['masa_aktif'] ?? '-' ?></td>
<td><?= $r['masa_inaktif'] ?? '-' ?></td>
<td>
<?php if($r['masa_aktif']===null){ ?>
    <span class="warn">⚠ Belum ada retensi</span>
<?php } else { ?>
    ✔ Aktif
<?php } ?>
</td>
<td>
<a class="btn" href="edit_retensi.php?id=<?= $r['id_kategori'] ?>">Atur</a>
</td>
</tr>
<?php } ?>
</table>

<br>
<a href="../dashboard/retensi.php">⬅ Kembali</a>

</body>
</html>
