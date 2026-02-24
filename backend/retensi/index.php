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
.alert{
    padding:12px;
    margin:15px 0;
    border-radius:6px;
    font-weight:bold;
}
.alert-success{
    background:#d4edda;
    color:#155724;
}
.alert-info{
    background:#d1ecf1;
    color:#0c5460;
}
</style>
</head>
<body>

<h2>⏳ Manajemen Retensi Arsip</h2>

<?php if (isset($_GET['success'])): ?>

<?php
$update = (int)($_GET['update'] ?? 0);
$musnah = (int)($_GET['musnah'] ?? 0);
?>

<div class="alert <?= ($update>0 || $musnah>0) ? 'alert-success' : 'alert-info' ?>">

    ✅ Proses retensi berhasil dijalankan.
    <br>

    <?php if ($update > 0): ?>
        🔄 <?= $update ?> arsip diperbarui.
        <br>
    <?php endif; ?>

    <?php if ($musnah > 0): ?>
        🗑 <?= $musnah ?> arsip siap musnah telah dikirim ke pimpinan.
        <br>
    <?php endif; ?>

    <?php if ($update === 0 && $musnah === 0): ?>
        ℹ : Tidak ada perubahan status arsip.
    <?php endif; ?>

</div>

<?php endif; ?>


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

<br><br>

<a href="proses_retensi.php"
   onclick="return confirm('Jalankan proses retensi sekarang? Sistem akan menghitung ulang seluruh arsip.')"
   class="btn"
   style="background:#8e44ad;color:#fff;border-radius:4px;padding:8px 14px;">
   🔁 Jalankan Proses Retensi
</a>

<br><br>

<a href="../dashboard/retensi.php">⬅ Kembali</a>

</body>
</html>