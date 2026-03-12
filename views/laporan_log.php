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
include '../layout/init.php';
include '../layout/head.php';
include '../layout/header.php';
include '../layout/sidebar.php';

/* =========================
   PAGINATION
========================= */
$limit = 50;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* =========================
   HITUNG TOTAL DATA LOG
========================= */
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM log_aktivitas");
$total_row    = mysqli_fetch_assoc($total_result);
$total_data   = $total_row['total'];

$total_page = ceil($total_data / $limit);

/* =========================
   QUERY DATA PER HALAMAN
========================= */
$q = mysqli_query($conn, "
    SELECT 
        users.nama_lengkap,
        log_aktivitas.aktivitas,
        log_aktivitas.modul,
        log_aktivitas.tanggal
    FROM log_aktivitas
    LEFT JOIN users ON log_aktivitas.id_user = users.id_user
    ORDER BY log_aktivitas.tanggal DESC
    LIMIT $offset, $limit
");

$no = $offset + 1;
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-journal-text me-2"></i> Laporan Log Aktivitas Sistem
</h3>

<div class="card shadow-sm border-0 rounded-4">

    <!-- CARD HEADER -->
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Daftar Aktivitas Sistem</span>

        <a href="<?= BASE_URL ?>app/laporan/cetak_pdf.php?jenis=log&page=<?= $page ?>"
           target="_blank"
<<<<<<< HEAD
           class="btn btn-primary btn-sm">
=======
           class="btn btn-danger btn-sm">
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de
           <i class="bi bi-file-earmark-pdf"></i> Download PDF (Halaman <?= $page ?>)
        </a>
    </div>

    <!-- CARD BODY -->
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center mb-0">

                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama User</th>
                        <th>Aktivitas</th>
                        <th style="width:150px;">Modul</th>
                        <th style="width:180px;">Tanggal & Waktu</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $no = $offset + 1;

                if(mysqli_num_rows($q) == 0){
                    echo "<tr>
                            <td colspan='5'>Belum ada data log aktivitas</td>
                          </tr>";
                }

                while($r = mysqli_fetch_assoc($q)) :
                ?>

                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($r['nama_lengkap'] ?? '-') ?></td>
                        <td class="text-start">
                            <?= htmlspecialchars($r['aktivitas']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($r['modul'] ?? '-') ?>
                        </td>
                        <td>
                            <?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if($total_page > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">

                <?php if($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for($i=1; $i <= $total_page; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if($page < $total_page): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?>">Next</a>
                </li>
                <?php endif; ?>

            </ul>
        </nav>
        <?php endif; ?>

    </div>

</div>

</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>