<?php
/**
 * Daftar Arsip Siap Dimusnahkan
 * Role: Pimpinan
 */

session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pimpinan') {
    die("Akses ditolak");
}

$query = mysqli_query($conn, "
    SELECT 
        arsip.id_arsip,
        arsip.asal_surat,
        arsip.nomor_surat,
        arsip.tanggal_surat,
        kategori.nama_kategori
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    WHERE arsip.status_arsip = 'Siap Musnah'
    ORDER BY arsip.tanggal_surat ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Persetujuan Pemusnahan Arsip</title>
<style>
body { font-family: Arial; background:#f4f6f8; }
.container { width:95%; margin:30px auto; }
table { width:100%; border-collapse:collapse; background:#fff; }
th,td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#ddd; }
.btn { padding:6px 10px; color:#fff; text-decoration:none; border-radius:4px; }
.setuju { background:#27ae60; }
.tolak { background:#e74c3c; }
.detail { background:#3498db; }
.logout { float:right; color:red; text-decoration:none; }
</style>
</head>

<body>
<div class="container">

<h2>
🗑️ Persetujuan Pemusnahan Arsip
<a href="../auth/logout.php" class="logout">Logout</a>
</h2>

<table>
<tr>
    <th>No</th>
    <th>Asal Surat</th>
    <th>Nomor Surat</th>
    <th>Tanggal Surat</th>
    <th>Kategori</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;
if (mysqli_num_rows($query) === 0) {
    echo "<tr><td colspan='6'>Tidak ada arsip menunggu persetujuan</td></tr>";
} else {
    while ($row = mysqli_fetch_assoc($query)) {
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['asal_surat']) ?></td>
    <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
    <td><?= $row['tanggal_surat'] ?></td>
    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
    <td>
        <a href="../arsip/detail_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn detail">Detail</a>

        <a href="proses_pemusnahan.php?id=<?= $row['id_arsip'] ?>&aksi=setuju"
           class="btn setuju"
           onclick="return confirm('Setujui pemusnahan arsip ini?')">
           Setujui
        </a>

        <a href="proses_pemusnahan.php?id=<?= $row['id_arsip'] ?>&aksi=tolak"
           class="btn tolak"
           onclick="return confirm('Tolak pemusnahan arsip ini?')">
           Tolak
        </a>
    </td>
</tr>
<?php }} ?>
</table>

</div>
</body>
</html>
