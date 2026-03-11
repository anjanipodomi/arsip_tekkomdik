<?php
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

/* ==========================
   AMBIL DATA RETENSI
========================== */
$data = mysqli_query($conn, "
    SELECT 
        kategori.id_kategori,
        kategori.nama_kategori,
        retensi.masa_aktif,
        retensi.masa_inaktif
    FROM kategori
    LEFT JOIN retensi 
        ON kategori.id_kategori = retensi.id_kategori
    ORDER BY kategori.nama_kategori
");

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-archive me-2"></i> Manajemen Retensi Arsip
</h3>

<?php if (isset($_GET['success'])): ?>

<?php
$update = (int)($_GET['update'] ?? 0);
$musnah = (int)($_GET['musnah'] ?? 0);
?>

<div class="alert <?= ($update>0 || $musnah>0) ? 'alert-success' : 'alert-info' ?>">

    <strong>Proses retensi berhasil dijalankan.</strong><br>

    <?php if ($update > 0): ?>
        🔄 <?= $update ?> arsip diperbarui.<br>
    <?php endif; ?>

    <?php if ($musnah > 0): ?>
        🗑 <?= $musnah ?> arsip siap musnah telah dikirim ke pimpinan.<br>
    <?php endif; ?>

    <?php if ($update === 0 && $musnah === 0): ?>
        ℹ Tidak ada perubahan status arsip.
    <?php endif; ?>

</div>

<?php endif; ?>

<div class="card shadow-sm border-0">

<div class="card-header bg-white border-0">
<span class="fw-semibold">Daftar Pengaturan Retensi</span>
</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle text-center mb-0">

<thead class="table-light">
<tr>
<th>Kategori</th>
<th>Masa Aktif (Tahun)</th>
<th>Masa Inaktif (Tahun)</th>
<th>Status</th>
<th style="width:120px;">Aksi</th>
</tr>
</thead>

<tbody>

<?php while($r = mysqli_fetch_assoc($data)): ?>

<tr>

<td class="text-start">
<?= htmlspecialchars($r['nama_kategori']) ?>
</td>

<td>
<?= $r['masa_aktif'] ?? '-' ?>
</td>

<td>
<?= $r['masa_inaktif'] ?? '-' ?>
</td>

<td>
<?php if ($r['masa_aktif'] === null): ?>

<span class="badge bg-secondary">
Belum Diatur
</span>

<?php else: ?>

<span class="badge bg-success">
Aktif
</span>

<?php endif; ?>
</td>

<td>

<a href="<?= BASE_URL ?>views/atur_retensi.php?id=<?= $r['id_kategori'] ?>"
   class="btn btn-sm btn-outline-primary"
   data-bs-toggle="tooltip"
   title="Atur Retensi">

<i class="bi bi-gear"></i>

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>

</div>
</div>


<!-- BUTTON PROSES RETENSI -->
<div class="mt-3">

<a href="<?= BASE_URL ?>app/retensi/proses_retensi.php"
   onclick="return confirm('Jalankan proses retensi sekarang? Sistem akan menghitung ulang seluruh arsip.')"
   class="btn btn-danger btn-sm">

<i class="bi bi-arrow-repeat"></i>
Jalankan Proses Retensi

</a>

</div>


<!-- BUTTON KEMBALI -->
<div class="mt-3">

<a href="<?= BASE_URL ?>views/retensi.php"
class="btn btn-primary btn-sm">

<i class="bi bi-arrow-left"></i>
Kembali

</a>

</div>

</div>
</main>

<script>

document.addEventListener('DOMContentLoaded', function () {

const tooltipTriggerList = [].slice.call(
document.querySelectorAll('[data-bs-toggle="tooltip"]')
);

tooltipTriggerList.map(function (tooltipTriggerEl) {

return new bootstrap.Tooltip(tooltipTriggerEl);

});

});

</script>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>