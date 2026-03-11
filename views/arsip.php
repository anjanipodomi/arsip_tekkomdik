<?php
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

/* ==========================
   CEK LOGIN
========================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

/* ==========================
   DATA MASTER (FILTER)
========================== */
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$box      = mysqli_query($conn, "SELECT * FROM box ORDER BY kode_box");

/* ==========================
   FILTER
========================== */
$keyword     = $_GET['keyword'] ?? '';
$id_kategori = $_GET['id_kategori'] ?? '';
$id_box      = $_GET['id_box'] ?? '';
$status      = $_GET['status'] ?? '';

$where = [];
$where = [];

if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where[] = "(arsip.asal_surat LIKE '%$kw%'
              OR arsip.nomor_surat LIKE '%$kw%'
              OR arsip.isi_ringkas LIKE '%$kw%')";
}

if ($id_kategori !== '') {
    $where[] = "arsip.id_kategori = '".mysqli_real_escape_string($conn,$id_kategori)."'";
}

if ($id_box !== '') {
    $where[] = "arsip.id_box = '".mysqli_real_escape_string($conn,$id_box)."'";
}

if ($status !== '') {
    $where[] = "arsip.status_arsip = '".mysqli_real_escape_string($conn,$status)."'";
}

if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
} else {
    $whereSQL = "";
}

/* ==========================
   PAGINATION
========================== */
$limit = 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* HITUNG TOTAL DATA */
$countQuery = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    $whereSQL
");
$totalData  = mysqli_fetch_assoc($countQuery)['total'];
$totalPages = ceil($totalData / $limit);

/* QUERY DATA */
$q = mysqli_query($conn, "
    SELECT 
        arsip.id_arsip,
        arsip.nomor_surat,
        arsip.asal_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        box.kode_box
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    $whereSQL
    ORDER BY arsip.tanggal_input DESC
    LIMIT $limit OFFSET $offset
");

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-folder me-2"></i> Manajemen Arsip Inaktif
</h3>

<div class="card shadow-sm border-0 rounded-4">

<!-- FILTER -->
<div class="card-body border-bottom">
<form method="GET">
<div class="row g-3">

<div class="col-md-3">
<input type="text" name="keyword"
       class="form-control"
       placeholder="Kata kunci"
       value="<?= htmlspecialchars($keyword) ?>">
</div>

<div class="col-md-2">
<select name="id_kategori" class="form-select">
<option value="">Semua Kategori</option>
<?php while($k=mysqli_fetch_assoc($kategori)): ?>
<option value="<?= $k['id_kategori'] ?>"
<?= ($id_kategori==$k['id_kategori'])?'selected':'' ?>>
<?= htmlspecialchars($k['nama_kategori']) ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-2">
<select name="id_box" class="form-select">
<option value="">Semua Box</option>
<?php while($b=mysqli_fetch_assoc($box)): ?>
<option value="<?= $b['id_box'] ?>"
<?= ($id_box==$b['id_box'])?'selected':'' ?>>
<?= htmlspecialchars($b['kode_box']) ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-2">
<select name="status" class="form-select">
<option value="">Semua Status</option>
<option value="Inaktif" <?= ($status=='Inaktif')?'selected':'' ?>>Inaktif</option>
<option value="Permanen" <?= ($status=='Permanen')?'selected':'' ?>>Permanen</option>
<option value="Siap Musnah" <?= ($status=='Siap Musnah')?'selected':'' ?>>Siap Musnah</option>
</select>
</div>

<div class="col-md-3 d-flex gap-2">
<button type="submit" class="btn btn-primary w-50">
<i class="bi bi-search"></i> Cari
</button>
<a href="<?= BASE_URL ?>views/arsip.php" class="btn btn-secondary w-50">
<i class="bi bi-arrow-clockwise"></i> Reset
</a>
</div>

</div>
</form>
</div>

<!-- TABLE -->
<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center mb-0">
<thead class="table-light">
<tr>
<th style="width:60px;">No</th>
<th>Asal Surat</th>
<th>Nomor</th>
<th>Tanggal</th>
<th>Kategori</th>
<th>Box</th>
<th>Status</th>
<th style="width:150px;">Aksi</th>
</tr>
</thead>
<tbody>

<?php
$no = $offset + 1;

if(mysqli_num_rows($q)==0){
echo "<tr><td colspan='8' class='text-muted'>Data tidak ditemukan</td></tr>";
}

while($r=mysqli_fetch_assoc($q)):
?>

<tr>
<td><?= $no++ ?></td>
<td class="text-start"><?= htmlspecialchars($r['asal_surat']) ?></td>
<td><?= htmlspecialchars($r['nomor_surat']) ?></td>
<td><?= date('d/m/Y', strtotime($r['tanggal_surat'])) ?></td>
<td><?= htmlspecialchars($r['nama_kategori']) ?></td>
<td><?= htmlspecialchars($r['kode_box']) ?></td>
<td>
<?php
switch($r['status_arsip']){
case 'Inaktif':
echo '<span class="badge bg-warning text-dark">Inaktif</span>';
break;
case 'Permanen':
echo '<span class="badge bg-success">Permanen</span>';
break;
case 'Siap Musnah':
echo '<span class="badge bg-danger">Siap Musnah</span>';
break;
case 'Dimusnahkan':
echo '<span class="badge bg-dark">Dimusnahkan</span>';
break;
}
?>
</td>

<td>
<div class="d-flex justify-content-center gap-2">

    <!-- DETAIL -->
    <a href="<?= BASE_URL ?>views/detail_arsip.php?id=<?= $r['id_arsip'] ?>"
       class="btn btn-sm btn-outline-info"
       data-bs-toggle="tooltip"
       title="Detail">
        <i class="bi bi-eye"></i>
    </a>

    <!-- VIEW (PREVIEW FILE) -->
    <a href="<?= BASE_URL ?>app/arsip/view_arsip.php?id=<?= $r['id_arsip'] ?>"
       class="btn btn-sm btn-outline-primary"
       target="_blank"
       data-bs-toggle="tooltip"
       title="View">
        <i class="bi bi-file-earmark-text"></i>
    </a>

    <!-- DOWNLOAD -->
    <a href="<?= BASE_URL ?>app/arsip/download_arsip.php?id=<?= $r['id_arsip'] ?>"
       class="btn btn-sm btn-outline-success"
       data-bs-toggle="tooltip"
       title="Download">
        <i class="bi bi-download"></i>
    </a>

</div>
</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>

<!-- PAGINATION -->
<?php if($totalPages > 1): ?>
<nav class="mt-4">
<ul class="pagination justify-content-center">

<?php if($page > 1): ?>
<li class="page-item">
<a class="page-link"
href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">
Previous
</a>
</li>
<?php endif; ?>

<?php for($i=1; $i <= $totalPages; $i++): ?>
<li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
<a class="page-link"
href="<?= BASE_URL ?>views/arsip.php?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>">
<?= $i ?>
</a>
</li>
<?php endfor; ?>

<?php if($page < $totalPages): ?>
<li class="page-item">
<a class="page-link"
href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">
Next
</a>
</li>
<?php endif; ?>

</ul>
</nav>
<?php endif; ?>

</div>
</div>

</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
return new bootstrap.Tooltip(tooltipTriggerEl);
});
});
</script>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>