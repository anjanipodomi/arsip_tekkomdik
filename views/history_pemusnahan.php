<?php
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user']) || 
    !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";

$role = $_SESSION['role'];

/* =========================
   SIDEBAR / NO SIDEBAR
========================= */
if ($role === 'admin') {
    require_once __DIR__ . "/../layout/sidebar.php";
} else {
    echo "<script>document.body.classList.add('no-sidebar');</script>";
}

/* =========================
   QUERY HISTORY
========================= */
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

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-journal-text me-2"></i> Riwayat Pemusnahan Arsip
</h3>

<div class="card shadow-sm border-0 rounded-4">
<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center mb-0">

<thead class="table-light">
<tr>
<th style="width:60px;">No</th>
<th>Nomor Surat</th>
<th>Asal Surat</th>
<th>Keputusan</th>
<th>Tanggal</th>
<th>Diproses Oleh</th>
</tr>
</thead>

<tbody>

<?php
$no = 1;

if (mysqli_num_rows($query) === 0) {
    echo "<tr>
            <td colspan='6'>Belum ada data pemusnahan</td>
          </tr>";
} else {
    while ($r = mysqli_fetch_assoc($query)) {
?>

<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($r['nomor_surat']) ?></td>
<td><?= htmlspecialchars($r['asal_surat']) ?></td>

<td>
<?php if($r['status_arsip'] == 'Dimusnahkan'): ?>
    <span class="badge bg-success">Disetujui</span>
<?php else: ?>
    <span class="badge bg-danger">Ditolak</span>
<?php endif; ?>
</td>

<<<<<<< HEAD
<td><?= date('d/m/Y', strtotime($r['tanggal_pemusnahan'])) ?></td>
=======
<td><?= date('d-m-Y', strtotime($r['tanggal_pemusnahan'])) ?></td>
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de

<td><?= htmlspecialchars($r['pimpinan']) ?></td>
</tr>

<?php
    }
}
?>

</tbody>
</table>
</div>

</div>
</div>

<?php if($role === 'pimpinan'): ?>
<div class="mt-4">
<a href="<?= BASE_URL ?>views/dashboard_pimpinan.php" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i> Kembali ke Dashboard
</a>
</div>
<?php endif; ?>

</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>