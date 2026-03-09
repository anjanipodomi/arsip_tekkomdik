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
if(!$id){ 
    header("Location: " . BASE_URL . "views/box_arsip.php"); 
    exit; 
}

$q = mysqli_query($conn,"SELECT * FROM box WHERE id_box='$id'");
$data = mysqli_fetch_assoc($q);

if(!$data){ 
    header("Location: " . BASE_URL . "views/box_arsip.php"); 
    exit; 
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">✏️ Edit Box Arsip</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<form action="<?= BASE_URL ?>app/box/update_box.php" method="POST">

<input type="hidden" name="id_box" value="<?= $data['id_box'] ?>">

<div class="mb-3">
<label class="form-label">Kode Box</label>
<input type="text" name="kode_box"
class="form-control"
value="<?= htmlspecialchars($data['kode_box']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label">Lokasi Fisik</label>
<input type="text" name="lokasi_fisik"
class="form-control"
value="<?= htmlspecialchars($data['lokasi_fisik']) ?>" required>
</div>

<div class="d-flex gap-2">
<a href="<?= BASE_URL ?>views/box_arsip.php" class="btn btn-secondary">Batal</a>
<button type="submit" class="btn btn-primary">
<i class="bi bi-save"></i> Update Box
</button>
</div>

</form>

</div>
</div>
</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>