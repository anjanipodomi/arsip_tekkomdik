<?php
/**
 * Dashboard Pimpinan Tekkomdik
 * Hak Akses:
 * - Lihat semua arsip
 * - Cari arsip
 * - Lihat detail
 * - Download arsip
 * - Menyetujui / Menolak pemusnahan arsip
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
   INPUT SEARCH
========================== */
$keyword = trim($_GET['keyword'] ?? '');
$where   = "";

if ($keyword !== '') {
    $keyword = mysqli_real_escape_string($conn, $keyword);
    $where = "
        WHERE arsip.asal_surat LIKE '%$keyword%'
           OR arsip.nomor_surat LIKE '%$keyword%'
           OR arsip.isi_ringkas LIKE '%$keyword%'
    ";
}

/* ==========================
   QUERY SEMUA ARSIP
========================== */
$query_all = mysqli_query($conn, "
    SELECT
        arsip.id_arsip,
        arsip.asal_surat,
        arsip.nomor_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori
    FROM arsip
    LEFT JOIN kategori
        ON arsip.id_kategori = kategori.id_kategori
    $where
    ORDER BY arsip.tanggal_input DESC
");

/* ==========================
   QUERY ARSIP SIAP MUSNAH
========================== */
$query_musnah = mysqli_query($conn, "
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
<title>Dashboard Pimpinan Tekkomdik</title>

<style>
body { font-family: Arial; background:#f4f6f8; }
.container { width:95%; margin:30px auto; }
.box { background:#fff; padding:15px; border-radius:6px; margin-bottom:15px; }
table { width:100%; border-collapse:collapse; background:#fff; margin-bottom:30px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#ddd; }
.btn { padding:5px 10px; text-decoration:none; border-radius:4px; color:#fff; font-size:13px; }
.detail { background:#3498db; }
.download { background:#2ecc71; }
.setujui { background:#27ae60; }
.tolak { background:#e74c3c; }
.logout { float:right; color:red; text-decoration:none; font-weight:bold; }
input { padding:6px; width:250px; }
button { padding:6px 10px; cursor:pointer; }
.badge {
    padding:3px 7px;
    border-radius:4px;
    font-size:12px;
    color:#fff;
}
.aktif { background:#2980b9; }
.inaktif { background:#7f8c8d; }
.musnah { background:#c0392b; }
.info {
    background:#fff3cd;
    border-left:5px solid #f39c12;
    padding:12px;
    margin-bottom:15px;
}
</style>
</head>

<body>

<div class="container">

<h2>
📊 Dashboard Pimpinan Tekkomdik
<a href="../auth/logout.php" class="logout">Logout</a>
</h2>

<!-- ================= SEARCH ================= -->
<div class="box">
<form method="GET">
    <strong>Cari Arsip:</strong>
    <input type="text"
           name="keyword"
           placeholder="Asal / Nomor / Isi Ringkas"
           value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit">Cari</button>
</form>
</div>

<!-- ================= SEMUA ARSIP ================= -->
<h3>📁 Daftar Seluruh Arsip</h3>

<table>
<tr>
    <th>No</th>
    <th>Asal Surat</th>
    <th>Nomor Surat</th>
    <th>Tanggal</th>
    <th>Kategori</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;
if (mysqli_num_rows($query_all) === 0) {
    echo "<tr><td colspan='7'>Data tidak ditemukan</td></tr>";
} else {
    while ($row = mysqli_fetch_assoc($query_all)) {

        $statusClass = match ($row['status_arsip']) {
            'Aktif'        => 'aktif',
            'Inaktif'      => 'inaktif',
            'Siap Musnah'  => 'musnah',
            default        => 'inaktif'
        };
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['asal_surat']) ?></td>
    <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
    <td><?= $row['tanggal_surat'] ?></td>
    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
    <td><span class="badge <?= $statusClass ?>"><?= $row['status_arsip'] ?></span></td>
    <td>
        <a href="../arsip/detail_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn detail">Detail</a>
        <a href="../arsip/download_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn download">Download</a>
    </td>
</tr>
<?php
    }
}
?>
</table>

<!-- ================= ARSIP SIAP MUSNAH ================= -->
<h3>🗑️ Arsip Menunggu Persetujuan Pemusnahan</h3>

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
if (mysqli_num_rows($query_musnah) === 0) {
    echo "<tr><td colspan='6'>Tidak ada arsip menunggu persetujuan</td></tr>";
} else {
    while ($row = mysqli_fetch_assoc($query_musnah)) {
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['asal_surat']) ?></td>
    <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
    <td><?= $row['tanggal_surat'] ?></td>
    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
    <td>
        <a href="../arsip/detail_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn detail">Detail</a>

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
