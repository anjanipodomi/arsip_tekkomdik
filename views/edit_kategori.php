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
    header("Location: " . BASE_URL . "views/kategori.php"); 
    exit; 
}

$q = mysqli_query($conn,"SELECT * FROM kategori WHERE id_kategori='$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) { 
    header("Location: " . BASE_URL . "views/kategori.php"); 
    exit; 
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">✏️ Edit Kategori</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<?php if($data['status']=='nonaktif'): ?>
<div class="alert alert-warning">
Kategori NONAKTIF tidak dapat diedit.
</div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>app/kategori/simpan_kategori.php">

<input type="hidden" name="id_kategori" value="<?= $data['id_kategori'] ?>">

<div class="mb-3">
<label class="form-label">Nama Kategori</label>
<input type="text" name="nama_kategori"
class="form-control"
value="<?= htmlspecialchars($data['nama_kategori']) ?>"
<?= $data['status']=='nonaktif'?'disabled':'' ?> required>
</div>

<div class="d-flex gap-2">
<a href="<?= BASE_URL ?>views/kategori.php" class="btn btn-secondary">Kembali</a>

<?php if($data['status']=='aktif'): ?>
<button type="submit" class="btn btn-primary">
<i class="bi bi-save"></i> Update
</button>
<?php endif; ?>

</div>

</form>

</div>
</div>
</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>