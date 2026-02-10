<?php
/**
 * Dashboard Staff Tekkomdik
 * Hak Akses:
 * - Cari arsip
 * - Lihat detail
 * - Download arsip
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

if ($_SESSION['role'] !== 'staff') {
    exit("Akses ditolak");
}

/* ==========================
   INPUT SEARCH
========================== */
$keyword = trim($_GET['keyword'] ?? '');

/* ==========================
   QUERY ARSIP
   - Staff hanya boleh lihat arsip
   - Tidak peduli status retensi
========================== */
$where = "";
if ($keyword !== '') {
    $keyword = mysqli_real_escape_string($conn, $keyword);
    $where = "
        WHERE arsip.asal_surat LIKE '%$keyword%'
           OR arsip.nomor_surat LIKE '%$keyword%'
           OR arsip.isi_ringkas LIKE '%$keyword%'
    ";
}

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
    $where
    ORDER BY arsip.tanggal_input DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Staff Tekkomdik</title>
<style>
body { font-family: Arial; background:#f4f6f8; }
.container { width:95%; margin:30px auto; }
.box { background:#fff; padding:15px; border-radius:6px; margin-bottom:15px; }
table { width:100%; border-collapse:collapse; background:#fff; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#ddd; }
.btn { padding:5px 10px; text-decoration:none; border-radius:4px; color:#fff; }
.detail { background:#3498db; }
.download { background:#2ecc71; }
.logout { float:right; color:red; text-decoration:none; font-weight:bold; }
input { padding:6px; width:250px; }
button { padding:6px 10px; cursor:pointer; }
</style>
</head>

<body>

<div class="container">

<h2>
📂 Dashboard Staff Tekkomdik
<a href="../auth/logout.php" class="logout">Logout</a>
</h2>

<!-- SEARCH -->
<div class="box">
<form method="GET">
    <strong>Cari Arsip:</strong>
    <input type="text"
           name="keyword"
           placeholder="Asal / Nomor / Isi"
           value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit">Cari</button>
</form>
</div>

<!-- TABEL ARSIP -->
<table>
<tr>
    <th>No</th>
    <th>Asal Surat</th>
    <th>Nomor Surat</th>
    <th>Tanggal</th>
    <th>Kategori</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;

if (mysqli_num_rows($query) === 0) {
    echo "<tr><td colspan='6'>Data tidak ditemukan</td></tr>";
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
        <a href="../arsip/detail_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn detail">
            Detail
        </a>

        <a href="../arsip/download_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn download">
            Download
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
