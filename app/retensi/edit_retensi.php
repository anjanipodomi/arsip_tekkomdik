<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id = $_GET['id'] ?? '';

$q = mysqli_query($conn,"
    SELECT kategori.nama_kategori, retensi.masa_aktif, retensi.masa_inaktif
    FROM kategori
    LEFT JOIN retensi ON kategori.id_kategori = retensi.id_kategori
    WHERE kategori.id_kategori='$id'
");

$data = mysqli_fetch_assoc($q);
?>
<!DOCTYPE html>
<html>
<head><title>Atur Retensi</title></head>
<body>

<h3>Atur Retensi: <?= htmlspecialchars($data['nama_kategori']) ?></h3>

<form method="POST" action="simpan_retensi.php">
<input type="hidden" name="id_kategori" value="<?= $id ?>">

<label>Masa Aktif (tahun)</label><br>
<input type="number" name="masa_aktif" required value="<?= $data['masa_aktif'] ?>"><br><br>

<label>Masa Inaktif (tahun)</label><br>
<input type="number" name="masa_inaktif" required value="<?= $data['masa_inaktif'] ?>"><br><br>

<button type="submit">💾 Simpan</button>
</form>

</body>
</html>
