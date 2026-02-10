<?php
/**
 * Daftar Arsip Inaktif + Search
 * Arsip Dimusnahkan DISAMARKAN
 */

session_start();
include __DIR__ . "/../config/database.php";

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['id_user'])) {
    header("Location: /arsip_tekkomdik/backend/auth/login.php");
    exit;
}

$role = $_SESSION['role'];

// ==========================
// DATA MASTER
// ==========================
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$box      = mysqli_query($conn, "SELECT * FROM box ORDER BY kode_box");

// ==========================
// INPUT SEARCH
// ==========================
$keyword     = $_GET['keyword'] ?? '';
$id_kategori = $_GET['id_kategori'] ?? '';
$id_box      = $_GET['id_box'] ?? '';
$status      = $_GET['status'] ?? '';

// ==========================
// QUERY FILTER
// ==========================
$where = [];

// ❗ SEMBUNYIKAN ARSIP DIMUSNAHKAN
$where[] = "arsip.status_arsip != 'Dimusnahkan'";

if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where[] = "(arsip.asal_surat LIKE '%$kw%'
              OR arsip.nomor_surat LIKE '%$kw%'
              OR arsip.isi_ringkas LIKE '%$kw%')";
}

if ($id_kategori !== '') {
    $where[] = "arsip.id_kategori = '$id_kategori'";
}

if ($id_box !== '') {
    $where[] = "arsip.id_box = '$id_box'";
}

if ($status !== '' && $status !== 'Dimusnahkan') {
    $where[] = "arsip.status_arsip = '$status'";
}

$whereSQL = "WHERE " . implode(" AND ", $where);

// ==========================
// QUERY ARSIP
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        arsip.id_arsip,
        arsip.asal_surat,
        arsip.nomor_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        box.kode_box
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    $whereSQL
    ORDER BY arsip.tanggal_input DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manajemen Arsip</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:95%;margin:auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#ddd}
.btn{padding:5px 8px;text-decoration:none;border-radius:4px;color:#fff}
.detail{background:#3498db}
.download{background:#2ecc71}
.tambah{background:#9b59b6}
.reset{background:#7f8c8d}
</style>
</head>

<body>
<div class="container">

<h2>📂 Manajemen Arsip Inaktif</h2>

<?php if ($role === 'admin') { ?>
<a href="tambah_arsip.php" class="btn tambah">➕ Tambah Arsip</a><br><br>
<?php } ?>

<form method="GET">
<input type="text" name="keyword" placeholder="Kata kunci" value="<?= htmlspecialchars($keyword) ?>">

<select name="id_kategori">
<option value="">-- Semua Kategori --</option>
<?php while ($k=mysqli_fetch_assoc($kategori)) { ?>
<option value="<?= $k['id_kategori'] ?>" <?= ($id_kategori==$k['id_kategori'])?'selected':'' ?>>
<?= htmlspecialchars($k['nama_kategori']) ?>
</option>
<?php } ?>
</select>

<select name="id_box">
<option value="">-- Semua Box --</option>
<?php while ($b=mysqli_fetch_assoc($box)) { ?>
<option value="<?= $b['id_box'] ?>" <?= ($id_box==$b['id_box'])?'selected':'' ?>>
<?= htmlspecialchars($b['kode_box']) ?>
</option>
<?php } ?>
</select>

<select name="status">
<option value="">-- Semua Status --</option>
<option value="Inaktif">Inaktif</option>
<option value="Permanen">Permanen</option>
<option value="Siap Musnah">Siap Musnah</option>
</select>

<button class="btn tambah">Cari</button>
<a href="index.php" class="btn reset">Reset</a>
</form>

<br>

<table>
<tr>
<th>No</th><th>Asal Surat</th><th>Nomor</th><th>Tanggal</th>
<th>Kategori</th><th>Box</th><th>Status</th><th>Aksi</th>
</tr>

<?php
$no=1;
if (mysqli_num_rows($query)==0) {
    echo "<tr><td colspan='8'>Data tidak ditemukan</td></tr>";
}
while($row=mysqli_fetch_assoc($query)){
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['asal_surat']) ?></td>
<td><?= htmlspecialchars($row['nomor_surat']) ?></td>
<td><?= $row['tanggal_surat'] ?></td>
<td><?= htmlspecialchars($row['nama_kategori']) ?></td>
<td><?= htmlspecialchars($row['kode_box']) ?></td>
<td><?= $row['status_arsip'] ?></td>
<td>
<a href="detail_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn detail">Detail</a>
<a href="download_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn download">Download</a>
</td>
</tr>
<?php } ?>
</table>

</div>
</body>
</html>
