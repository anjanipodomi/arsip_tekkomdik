<?php
/**
 * Daftar Arsip Inaktif + Search + Pagination
 */

session_start();
include __DIR__ . "/../config/database.php";

/* ==========================
   CEK LOGIN
========================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: /arsip_tekkomdik/backend/auth/login.php");
    exit;
}

$role = $_SESSION['role'];

/* ==========================
   DATA MASTER
========================== */
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$box      = mysqli_query($conn, "SELECT * FROM box ORDER BY kode_box");

/* ==========================
   INPUT SEARCH
========================== */
$keyword     = $_GET['keyword'] ?? '';
$id_kategori = $_GET['id_kategori'] ?? '';
$id_box      = $_GET['id_box'] ?? '';
$status      = $_GET['status'] ?? '';

/* ==========================
   PAGINATION
========================== */
$limit = 50;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

/* ==========================
   QUERY FILTER
========================== */
$where = [];

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

if ($status !== '') {
    $status_safe = mysqli_real_escape_string($conn, $status);
    $where[] = "arsip.status_arsip = '$status_safe'";
}

$whereSQL = "";
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}
/* ==========================
   HITUNG TOTAL DATA
========================== */
$total_result = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    $whereSQL
");

$total_row  = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_page = ceil($total_data / $limit);

/* ==========================
   QUERY DATA PER HALAMAN
========================== */
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
    LIMIT $offset, $limit
");

$no = $offset + 1;
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
.view{background:#f39c12}
.download{background:#2ecc71}
.tambah{background:#9b59b6}
.reset{background:#7f8c8d}

.pagination{
    margin:20px 0;
    text-align:center;
}

.pagination a{
    padding:6px 12px;
    margin:0 4px;
    border:1px solid #ccc;
    text-decoration:none;
    background:#fff;
    border-radius:4px;
}

.pagination a.active{
    background:#3498db;
    color:#fff;
    font-weight:bold;
    border-color:#3498db;
}

.logout{
    float:right;
    color:red;
    text-decoration:none;
    font-weight:bold;
}

.logout:hover{
    text-decoration:underline;
}
</style>
</head>

<body>
<div class="container">

<h2>
📂 Daftar Arsip

<?php if ($role === 'staff'): ?>
<a href="../auth/logout.php"
   class="logout"
   onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
   Logout
</a>
<?php endif; ?>

</h2>
<form method="GET">
<input type="text" name="keyword" placeholder="Kata kunci"
       value="<?= htmlspecialchars($keyword) ?>">

<select name="id_kategori">
<option value="">-- Semua Kategori --</option>
<?php while ($k=mysqli_fetch_assoc($kategori)) { ?>
<option value="<?= $k['id_kategori'] ?>"
<?= ($id_kategori==$k['id_kategori'])?'selected':'' ?>>
<?= htmlspecialchars($k['nama_kategori']) ?>
</option>
<?php } ?>
</select>

<select name="id_box">
<option value="">-- Semua Box --</option>
<?php while ($b=mysqli_fetch_assoc($box)) { ?>
<option value="<?= $b['id_box'] ?>"
<?= ($id_box==$b['id_box'])?'selected':'' ?>>
<?= htmlspecialchars($b['kode_box']) ?>
</option>
<?php } ?>
</select>

<select name="status">
<option value="">-- Semua Status --</option>
<option value="Inaktif" <?= ($status=='Inaktif')?'selected':'' ?>>Inaktif</option>
<option value="Permanen" <?= ($status=='Permanen')?'selected':'' ?>>Permanen</option>
<option value="Siap Musnah" <?= ($status=='Siap Musnah')?'selected':'' ?>>Siap Musnah</option>
<option value="Dimusnahkan" <?= ($status=='Dimusnahkan')?'selected':'' ?>>Dimusnahkan</option>
</select>

<button class="btn tambah">Cari</button>
<a href="index.php" class="btn reset">Reset</a>
</form>

<!-- PAGINATION ATAS -->
<div class="pagination">
<?php if ($page > 1): ?>
<a href="?<?= http_build_query($_GET + ['page'=>$page-1]) ?>">« Prev</a>
<?php endif; ?>

<?php for ($i=1; $i <= $total_page; $i++): ?>
<a href="?<?= http_build_query($_GET + ['page'=>$i]) ?>"
   class="<?= ($i==$page)?'active':'' ?>">
<?= $i ?>
</a>
<?php endfor; ?>

<?php if ($page < $total_page): ?>
<a href="?<?= http_build_query($_GET + ['page'=>$page+1]) ?>">Next »</a>
<?php endif; ?>
</div>

<table>
<tr>
<th>No</th>
<th>Asal Surat</th>
<th>Nomor</th>
<th>Tanggal</th>
<th>Kategori</th>
<th>Box</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php
if (mysqli_num_rows($query)==0) {
    echo "<tr><td colspan='8'>Data tidak ditemukan</td></tr>";
}
while($row=mysqli_fetch_assoc($query)){
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['asal_surat']) ?></td>
<td><?= htmlspecialchars($row['nomor_surat']) ?></td>
<td><?= date('d/m/Y', strtotime($row['tanggal_surat'])) ?></td>
<td><?= htmlspecialchars($row['nama_kategori']) ?></td>
<td><?= htmlspecialchars($row['kode_box']) ?></td>
<td><?= $row['status_arsip'] ?></td>
<td>
<a href="detail_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn detail">Detail</a>
<a href="view_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn view">View</a>
<a href="download_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn download">Download</a>
</td>
</tr>
<?php } ?>
</table>

</div>
</body>
</html>