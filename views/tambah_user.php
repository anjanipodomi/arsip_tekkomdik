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

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">➕ Tambah User</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<form method="POST"
    action="<?= BASE_URL ?>app/kelola_user/simpan_user.php">

<div class="row g-3">

<div class="col-md-6">
<label class="form-label">Nama Lengkap</label>
<input type="text" name="nama_lengkap"
class="form-control" required>
</div>

<div class="col-md-6">
<label class="form-label">Username</label>
<input type="text" name="username"
class="form-control" required>
</div>

<div class="col-md-6">
<label class="form-label">Password</label>
<input type="password" name="password"
class="form-control" required>
</div>

<div class="col-md-6">
<label class="form-label">Role</label>
<select name="role" class="form-select" required>
<option value="">-- Pilih Role --</option>
<option value="staff">Staff</option>
<option value="pimpinan">Pimpinan</option>
<option value="admin">Admin</option>
</select>
</div>

</div>

<div class="mt-4 d-flex gap-2">
<a href="<?= BASE_URL ?>views/kelola_user.php" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i> Batal
</a>

<button type="submit" class="btn btn-primary">
<i class="bi bi-save"></i> Simpan User
</button>
</div>

</form>

</div>
</div>

</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>