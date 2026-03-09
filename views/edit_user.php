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
    header("Location: " . BASE_URL . "views/kelola_user.php"); 
    exit; 
}

$q = mysqli_query($conn,"SELECT * FROM users WHERE id_user='$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) { 
    header("Location: " . BASE_URL . "views/kelola_user.php"); 
    exit; 
}

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">✏️ Edit User</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<form method="POST" action="<?= BASE_URL ?>app/kelola_user/update_user.php">

<input type="hidden" name="id_user" value="<?= $data['id_user'] ?>">

<div class="mb-3">
<label class="form-label">Nama Lengkap</label>
<input type="text" name="nama_lengkap"
class="form-control"
value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label">Username</label>
<input type="text" name="username"
class="form-control"
value="<?= htmlspecialchars($data['username']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label">Password (Kosongkan jika tidak diganti)</label>
<input type="password" name="password" class="form-control">
</div>

<div class="mb-3">
<label class="form-label">Role</label>
<select name="role" class="form-select" required>
<option value="staff" <?= $data['role']=='staff'?'selected':'' ?>>Staff</option>
<option value="pimpinan" <?= $data['role']=='pimpinan'?'selected':'' ?>>Pimpinan</option>
<option value="admin" <?= $data['role']=='admin'?'selected':'' ?>>Admin</option>
</select>
</div>

<div class="d-flex gap-2">
<a href="<?= BASE_URL ?>views/kelola_user.php" class="btn btn-secondary">Batal</a>
<button type="submit" class="btn btn-primary">
<i class="bi bi-save"></i> Update User
</button>
</div>

</form>

</div>
</div>
</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>