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

$id = $_GET['id'] ?? '';

if (!$id) {
    header("Location: " . BASE_URL . "views/manajemen_retensi.php");
    exit;
}

$q = mysqli_query($conn, "
    SELECT kategori.nama_kategori, retensi.masa_aktif, retensi.masa_inaktif
    FROM kategori
    LEFT JOIN retensi 
    ON kategori.id_kategori = retensi.id_kategori
    WHERE kategori.id_kategori='$id'
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
    header("Location: " . BASE_URL . "views/manajemen_retensi.php");
    exit;
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
<i class="bi bi-clock-history me-2"></i> Atur Retensi: <?= htmlspecialchars($data['nama_kategori']) ?>
</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<form method="POST" action="<?= BASE_URL ?>app/retensi/simpan_retensi.php">

<input type="hidden" name="id_kategori" value="<?= $id ?>">

<div class="row g-3">

<div class="col-md-6">
<label class="form-label">Masa Aktif (Tahun)</label>
<input type="number"
       name="masa_aktif"
       class="form-control"
       min="0"
       required
       value="<?= $data['masa_aktif'] ?>">
</div>

<div class="col-md-6">
<label class="form-label">Masa Inaktif (Tahun)</label>
<input type="number"
       name="masa_inaktif"
       class="form-control"
       min="0"
       required
       value="<?= $data['masa_inaktif'] ?>">
</div>

</div>

<div class="mt-4 d-flex gap-2">
<a href="<?= BASE_URL ?>views/manajemen_retensi.php" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i> Kembali
</a>

<button type="submit" class="btn btn-primary">
<i class="bi bi-save"></i> Simpan
</button>
</div>

</form>

</div>
</div>

</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>