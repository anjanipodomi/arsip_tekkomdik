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

$id = $_GET['id'] ?? $_POST['id_arsip'] ?? '';

if (!$id) {
    header("Location: " . BASE_URL . "views/arsip.php");
    exit;
}

$q = mysqli_query($conn, "SELECT * FROM arsip WHERE id_arsip='$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    header("Location: " . BASE_URL . "views/arsip.php");
    exit;
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $asal  = trim($_POST['asal_surat']);
    $nomor = trim($_POST['nomor_surat']);
    $tgl   = $_POST['tanggal_surat'];
    $isi   = trim($_POST['isi_ringkas']);

/* ==========================
   VALIDASI EDIT ARSIP
========================== */

if ($asal=='' || $nomor=='' || $tgl=='' || $isi=='') {
    $pesan = "Semua field wajib diisi.";
}

elseif ($tgl > date('Y-m-d')) {
    $pesan = "Tanggal tidak boleh melebihi hari ini.";
}
    /* ==========================
    VALIDASI TIDAK ADA PERUBAHAN
    ========================== */
    elseif (
        $asal  === $data['asal_surat'] &&
        $nomor === $data['nomor_surat'] &&
        $tgl   === $data['tanggal_surat'] &&
        $isi   === $data['isi_ringkas']
    ){
        $pesan = "Tidak ada perubahan data.";
    }

    else {

        $asal_db  = mysqli_real_escape_string($conn,$asal);
        $nomor_db = mysqli_real_escape_string($conn,$nomor);
        $isi_db   = mysqli_real_escape_string($conn,$isi);

        mysqli_query($conn,"
            UPDATE arsip SET
                asal_surat='$asal_db',
                nomor_surat='$nomor_db',
                tanggal_surat='$tgl',
                isi_ringkas='$isi_db'
            WHERE id_arsip='$id'
        ");

        header("Location: " . BASE_URL . "views/detail_arsip.php?id=$id&updated=1");
        exit;
    }
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
<i class="bi bi-pencil-square"></i> Edit Arsip
</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<?php if($pesan): ?>
<div class="alert alert-danger">
<i class="bi bi-exclamation-triangle me-2"></i>
<?= $pesan ?>
</div>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="id_arsip" value="<?= $data['id_arsip'] ?>">

<div class="row g-3">

<div class="col-md-6">
<label class="form-label">Asal Surat</label>
<input type="text" name="asal_surat" class="form-control"
value="<?= htmlspecialchars($data['asal_surat']) ?>" required>
</div>

<div class="col-md-6">
<label class="form-label">Nomor Surat</label>
<input type="text" name="nomor_surat" class="form-control"
value="<?= htmlspecialchars($data['nomor_surat']) ?>" required>
</div>

<div class="col-md-4">
<label class="form-label">Tanggal Surat</label>
<input type="date" name="tanggal_surat" class="form-control"
value="<?= $data['tanggal_surat'] ?>" required>
</div>

<div class="col-md-12">
<label class="form-label">Isi Ringkas</label>
<textarea name="isi_ringkas" rows="4" class="form-control" required><?= htmlspecialchars($data['isi_ringkas']) ?></textarea>
</div>

</div>

<div class="mt-4 d-flex gap-2">
<a href="<?= BASE_URL ?>views/arsip.php" class="btn btn-secondary">Batal</a>
<button type="submit" class="btn btn-primary">
<i class="bi bi-save"></i> Update Arsip
</button>
</div>

</form>

</div>
</div>
</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>