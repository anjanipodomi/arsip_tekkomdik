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

$query = mysqli_query($conn, "
    SELECT id_user, nama_lengkap, username, role, created_at
    FROM users
    ORDER BY created_at DESC
");

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

    <h3 class="fw-bold mb-4">
        <i class="bi bi-people me-2"></i> Kelola User
    </h3>

    <div class="card shadow-sm border-0">

        <!-- HEADER -->
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <a href="<?= BASE_URL ?>views/tambah_user.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah User
            </a>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th style="width:120px;">Role</th>
                            <th style="width:200px;">Tanggal Dibuat</th>
                            <th style="width:150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php 
                    $no=1; 
                    while($row=mysqli_fetch_assoc($query)) : 
                    ?>

                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="text-start"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>

                            <td>
                                <?php
                                switch($row['role']){
                                    case 'admin':
                                        echo '<span class="badge bg-danger">Admin</span>';
                                        break;
                                    case 'pimpinan':
                                        echo '<span class="badge bg-primary">Pimpinan</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">Staff</span>';
                                }
                                ?>
                            </td>

                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>

                            <td>
                                <div class="d-flex justify-content-center gap-2">

                                    <a href="<?= BASE_URL ?>views/edit_user.php?id=<?= $row['id_user'] ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip"
                                       title="Edit User">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <a href="<?= BASE_URL ?>app/kelola_user/hapus_user.php?id=<?= $row['id_user'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       data-bs-toggle="tooltip"
                                       title="Hapus User"
                                       onclick="return confirm('Hapus user ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>

                                </div>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>