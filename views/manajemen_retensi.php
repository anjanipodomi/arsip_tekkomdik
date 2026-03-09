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

$data = mysqli_query($conn, "
    SELECT 
        kategori.id_kategori,
        kategori.nama_kategori,
        retensi.masa_aktif,
        retensi.masa_inaktif
    FROM kategori
    LEFT JOIN retensi 
    ON kategori.id_kategori = retensi.id_kategori
");
?>

<main class="content">
<div class="container-fluid">

    <h3 class="fw-bold mb-4">⏳ Manajemen Retensi Arsip</h3>

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

                    <?php while($r=mysqli_fetch_assoc($data)): ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($r['nama_kategori']) ?></td>

                            <td><?= $r['masa_aktif'] ?? '-' ?></td>
                            <td><?= $r['masa_inaktif'] ?? '-' ?></td>

                            <td>
                                <?php if($r['masa_aktif'] === null): ?>
                                    <span class="badge bg-secondary">Belum Diatur</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Aktif</span>
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

    <div class="mt-3">
        <a href="<?= BASE_URL ?>views/retensi.php" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
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