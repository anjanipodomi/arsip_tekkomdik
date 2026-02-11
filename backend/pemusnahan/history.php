<?php
/**
 * History Pemusnahan Arsip
 * Role : Admin & Pimpinan
 */

session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    exit("Akses ditolak");
}

$query = mysqli_query($conn, "
    SELECT
        arsip.id_arsip,
        arsip.nomor_surat,
        arsip.asal_surat,
        arsip.status_arsip,
        pemusnahan.tanggal_pemusnahan,
        users.username AS pimpinan
    FROM pemusnahan
    JOIN arsip ON pemusnahan.id_arsip = arsip.id_arsip
    JOIN users ON pemusnahan.disetujui_oleh = users.id_user
    ORDER BY pemusnahan.tanggal_pemusnahan DESC
");

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Pemusnahan Arsip</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:95%;margin:30px auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#eee}
.disetujui{color:green;font-weight:bold}
.ditolak{color:red;font-weight:bold}
</style>
</head>
<body>

<div class="container">

<h2>📜 Riwayat Pemusnahan Arsip</h2>

<table>
<tr>
    <th>No</th>
    <th>Nomor Surat</th>
    <th>Asal Surat</th>
    <th>Keputusan</th>
    <th>Tanggal</th>
    <th>Diproses Oleh</th>
</tr>

<?php
$no = 1;
if (mysqli_num_rows($query) === 0) {
    echo "<tr><td colspan='6'>Belum ada data pemusnahan</td></tr>";
}
while ($r = mysqli_fetch_assoc($query)) {
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($r['nomor_surat']) ?></td>
    <td><?= htmlspecialchars($r['asal_surat']) ?></td>
    <td class="<?= ($r['status_arsip']=='Dimusnahkan') ? 'disetujui' : 'ditolak' ?>">
        <?= ($r['status_arsip']=='Dimusnahkan') ? '✅ Disetujui' : '❌ Ditolak' ?>
    </td>
    <td><?= date('d-m-Y', strtotime($r['tanggal_pemusnahan'])) ?></td>
    <td><?= htmlspecialchars($r['pimpinan']) ?></td>
</tr>
<?php } ?>
</table>

<br>
<a href="../dashboard/<?= $_SESSION['role'] ?>.php">⬅ Kembali ke Dashboard</a>

</div>

</body>
</html>
