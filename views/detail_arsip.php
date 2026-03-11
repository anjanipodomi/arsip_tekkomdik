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

$id = $_GET['id'] ?? '';

/* VALIDASI ID */
if (!ctype_digit($id)) {
    header("Location: " . BASE_URL . "views/arsip.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT 
        arsip.*,
        kategori.nama_kategori,
        box.kode_box,
        box.lokasi_fisik
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    WHERE arsip.id_arsip = '$id'
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: " . BASE_URL . "views/arsip.php");
    exit;
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";

$role = $_SESSION['role'];

if ($role === 'admin') {
    require_once __DIR__ . "/../layout/sidebar.php";
} else {
    echo "<script>document.body.classList.add('staff');</script>";
}
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-file-earmark-text me-2"></i> Detail Arsip
</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered align-middle mb-0">

<tr><th width="220">Asal Surat</th><td><?= htmlspecialchars($data['asal_surat']) ?></td></tr>
<tr><th>Nomor Surat</th><td><?= htmlspecialchars($data['nomor_surat']) ?></td></tr>
<tr><th>Tanggal Surat</th><td><?= date('d-m-Y', strtotime($data['tanggal_surat'])) ?></td></tr>
<tr><th>Isi Ringkas</th><td><?= nl2br(htmlspecialchars($data['isi_ringkas'])) ?></td></tr>
<tr><th>Kategori</th><td><?= htmlspecialchars($data['nama_kategori']) ?></td></tr>

<tr>
<th>Box</th>
<td>
<?= htmlspecialchars($data['kode_box']) ?>
<?php if($data['lokasi_fisik']): ?>
(<?= htmlspecialchars($data['lokasi_fisik']) ?>)
<?php endif; ?>
</td>
</tr>

<tr><th>Jumlah Berkas</th><td><?= $data['jumlah_berkas'] ?></td></tr>
<tr><th>Tingkat Perkembangan</th><td><?= $data['tingkat_perkembangan'] ?></td></tr>
<tr><th>Klasifikasi</th><td><?= $data['klasifikasi_keamanan'] ?></td></tr>

<tr>
<th>Status</th>
<td>
<?php
switch($data['status_arsip']){
case 'Inaktif':
echo '<span class="badge bg-primary">Inaktif</span>';
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
</tr>

</table>
</div>

<?php
$arsip_dimusnahkan = ($data['status_arsip'] === 'Dimusnahkan');

if ($role === 'admin') {
    $back_url = BASE_URL . "views/arsip.php";
} elseif ($role === 'pimpinan') {
    $back_url = BASE_URL . "views/dashboard_pimpinan.php";
} else {
    $back_url = BASE_URL . "views/dashboard_staff.php";
}
?>

<div class="mt-4 d-flex gap-2 flex-wrap">

<a href="<?= $back_url ?>" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i> Kembali
</a>

<?php if(!$arsip_dimusnahkan): ?>
<a href="<?= BASE_URL ?>app/arsip/download_arsip.php?id=<?= $data['id_arsip'] ?>"
class="btn btn-primary">
<i class="bi bi-download"></i> Download
</a>
<?php endif; ?>

<?php if($role === 'admin' && !$arsip_dimusnahkan): ?>
<a href="<?= BASE_URL ?>views/edit_arsip.php?id=<?= $data['id_arsip'] ?>"
class="btn btn-warning">
<i class="bi bi-pencil"></i> Edit
</a>

<a href="<?= BASE_URL ?>app/arsip/hapus_arsip.php?id=<?= $data['id_arsip'] ?>"
class="btn btn-danger"
onclick="return confirm('Yakin ingin menghapus arsip ini?')">
<i class="bi bi-trash"></i> Hapus
</a>
<?php endif; ?>

</div>

</div>
</div>

</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>