<?php
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

if ($_SESSION['role'] !== 'pimpinan') {
    exit("Akses ditolak");
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";

echo "<script>document.body.classList.add('pimpinan');</script>";

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

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
🗑️ Arsip Menunggu Persetujuan Pemusnahan
</h3>

<div class="alert alert-warning">
⚠️ Arsip berikut telah melewati masa retensi dan menunggu keputusan pimpinan.
</div>

<div class="card shadow-sm border-0">
<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center">

<thead class="table-light">
<tr>
<th style="width:60px;">No</th>
<th>Asal Surat</th>
<th>Nomor Surat</th>
<th>Tanggal</th>
<th>Kategori</th>
<th style="width:220px;">Aksi</th>
</tr>
</thead>

<tbody>

<?php
$no = 1;

if (mysqli_num_rows($query) === 0) {
    echo "<tr>
            <td colspan='6' class='text-center'>
                Tidak ada arsip menunggu persetujuan
            </td>
          </tr>";
} else {
    while ($row = mysqli_fetch_assoc($query)) {
?>

<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['asal_surat']) ?></td>
<td><?= htmlspecialchars($row['nomor_surat']) ?></td>
<td><?= date('d-m-Y', strtotime($row['tanggal_surat'])) ?></td>
<td><?= htmlspecialchars($row['nama_kategori']) ?></td>

<td>
<div class="d-flex justify-content-center gap-2">

    <!-- VIEW (PREVIEW FILE) -->
    <a href="<?= BASE_URL ?>app/arsip/view_arsip.php?id=<?= $row['id_arsip'] ?>"
       class="btn btn-sm btn-outline-primary"
       target="_blank"
       data-bs-toggle="tooltip"
       title="View">
        <i class="bi bi-file-earmark-text"></i>
    </a>

    <!-- SETUJU -->
    <a href="<?= BASE_URL ?>app/pemusnahan/proses_pemusnahan.php?id=<?= $row['id_arsip'] ?>&aksi=setuju"
       class="btn btn-sm btn-success"
       onclick="return confirm('Setujui pemusnahan arsip ini?')"
       data-bs-toggle="tooltip"
       title="Setujui">
        <i class="bi bi-check-circle"></i>
    </a>

    <!-- TOLAK -->
    <a href="<?= BASE_URL ?>app/pemusnahan/proses_pemusnahan.php?id=<?= $row['id_arsip'] ?>&aksi=tolak"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Tolak pemusnahan arsip ini?')"
       data-bs-toggle="tooltip"
       title="Tolak">
        <i class="bi bi-x-circle"></i>
    </a>

</div>
</td>
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

<div class="mt-4">
<a href="<?= BASE_URL ?>views/dashboard_pimpinan.php"
class="btn btn-secondary">
<i class="bi bi-arrow-left"></i> Kembali ke Dashboard
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