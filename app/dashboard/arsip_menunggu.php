<?php
/**
 * Arsip Menunggu Persetujuan Pemusnahan
 * Role : Pimpinan
 */

session_start();
include __DIR__ . "/../config/database.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['role'] !== 'pimpinan') {
    exit("Akses ditolak");
}

/* ==========================
   AMBIL ARSIP SIAP MUSNAH
========================== */
$query = mysqli_query($conn, "
    SELECT
        arsip.id_arsip,
        arsip.asal_surat,
        arsip.nomor_surat,
        arsip.tanggal_surat,
        kategori.nama_kategori
    FROM arsip
    LEFT JOIN kategori
        ON arsip.id_kategori = kategori.id_kategori
    WHERE arsip.status_arsip = 'Siap Musnah'
    ORDER BY arsip.tanggal_surat ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Arsip Menunggu Pemusnahan</title>

<style>
body {
    font-family: Arial, sans-serif;
    background:#f4f6f8;
}
.container {
    width:95%;
    margin:30px auto;
}
h2 {
    margin-bottom:10px;
}
.info {
    background:#fff3cd;
    border-left:5px solid #f39c12;
    padding:12px;
    margin-bottom:15px;
}
table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
}
th, td {
    border:1px solid #ccc;
    padding:8px;
    text-align:center;
}
th {
    background:#eee;
}
.btn {
    padding:6px 10px;
    border-radius:4px;
    color:#fff;
    text-decoration:none;
    font-size:13px;
}
.detail { background:#3498db; }
.setujui { background:#27ae60; }
.tolak { background:#e74c3c; }
.back {
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="container">

<a href="../dashboard/pimpinan.php" class="back">⬅ Kembali ke Dashboard</a>

<h2>🗑️ Arsip Menunggu Persetujuan Pemusnahan</h2>

<div class="info">
⚠️ Arsip berikut telah melewati masa retensi dan menunggu keputusan pimpinan.
</div>

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
        <a href="../arsip/detail_arsip.php?id=<?= $row['id_arsip'] ?>"
           class="btn detail">
           Detail
        </a>

        <a href="../pemusnahan/proses_pemusnahan.php?id=<?= $row['id_arsip'] ?>&aksi=setuju"
        class="btn setujui"
        onclick="return confirm('Setujui pemusnahan arsip ini?')">
        Setujui
        </a>

        <a href="../pemusnahan/proses_pemusnahan.php?id=<?= $row['id_arsip'] ?>&aksi=tolak"
        class="btn tolak"
        onclick="return confirm('Tolak pemusnahan arsip ini?')">
        Tolak
        </a>

    </td>
</tr>
<?php
    }
}
?>
</table>

</div>

</body>
</html>
