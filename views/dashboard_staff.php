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

$keyword = $_GET['keyword'] ?? '';
$where = "";

if($keyword != ''){
    $kw = mysqli_real_escape_string($conn,$keyword);
    $where = "WHERE arsip.asal_surat LIKE '%$kw%' 
              OR arsip.nomor_surat LIKE '%$kw%' 
              OR arsip.isi_ringkas LIKE '%$kw%'";
}

/* ================= PAGINATION ================= */

$limit = 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* HITUNG TOTAL DATA */
$countQuery = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    $where
");
$totalData  = mysqli_fetch_assoc($countQuery)['total'];
$totalPages = ceil($totalData / $limit);

/* QUERY DATA */
$query = mysqli_query($conn,"
    SELECT arsip.*, kategori.nama_kategori
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori=kategori.id_kategori
    $where
    ORDER BY tanggal_input DESC
    LIMIT $limit OFFSET $offset
");

include '../layout/init.php';
include '../layout/head.php';
include '../layout/header.php';

/* TANPA SIDEBAR */
echo "<script>document.body.classList.add('no-sidebar');</script>";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
<i class="bi bi-speedometer2 me-2"></i> Dashboard Staff</h3>

<div class="card shadow-sm border-0 rounded-4">
<div class="card-body">

<form method="GET" class="row g-3 mb-4">

<div class="col-md-10">
<input type="text"
       name="keyword"
       value="<?= htmlspecialchars($keyword) ?>"
       class="form-control"
       placeholder="Cari arsip...">
</div>

<div class="col-md-2 d-grid">
<button class="btn btn-primary">
<i class="bi bi-search"></i> Cari
</button>
</div>

</form>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center mb-0">

<thead class="table-light">
<tr>
<th style="width:60px;">No</th>
<th>Asal Surat</th>
<th>Nomor</th>
<th>Tanggal</th>
<th>Kategori</th>
<th style="width:120px;">Aksi</th>
</tr>
</thead>

<tbody>

<?php
$no = $offset + 1;

if(mysqli_num_rows($query)==0){
echo "<tr><td colspan='6'>Data tidak ditemukan</td></tr>";
}

while($r=mysqli_fetch_assoc($query)):
?>

<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($r['asal_surat']) ?></td>
<td><?= htmlspecialchars($r['nomor_surat']) ?></td>
<td><?= date('d-m-Y',strtotime($r['tanggal_surat'])) ?></td>
<td><?= htmlspecialchars($r['nama_kategori']) ?></td>

<td>
<div class="d-flex justify-content-center gap-2">

<!-- DETAIL -->
<a href="<?= BASE_URL ?>views/detail_arsip.php?id=<?= $r['id_arsip'] ?>"
class="btn btn-sm btn-outline-info"
data-bs-toggle="tooltip"
title="Detail">
<i class="bi bi-eye"></i>
</a>

<!-- VIEW FILE -->
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

<!-- ================= PAGINATION ================= -->
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
href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>">
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