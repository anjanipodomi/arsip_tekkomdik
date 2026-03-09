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
   HITUNG TOTAL DATA
========================= */
$total_result = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM pemusnahan
");
$total_row  = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];

$total_page = ceil($total_data / $limit);

/* =========================
   QUERY DATA PER HALAMAN
========================= */
$q = mysqli_query($conn,"
    SELECT 
        arsip.nomor_surat,
        arsip.asal_surat,
        pemusnahan.tanggal_pemusnahan,
        users.nama_lengkap
    FROM pemusnahan
    JOIN arsip ON pemusnahan.id_arsip = arsip.id_arsip
    JOIN users ON pemusnahan.disetujui_oleh = users.id_user
    ORDER BY pemusnahan.tanggal_pemusnahan DESC
    LIMIT $offset, $limit
");

$no = $offset + 1;
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">🔥 Laporan Pemusnahan Arsip</h3>

<div class="card shadow-sm border-0 rounded-4">

    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Data Pemusnahan Arsip</span>

        <a href="<?= BASE_URL ?>app/laporan/cetak_pdf.php?jenis=pemusnahan&page=<?= $page ?>"
           target="_blank"
           class="btn btn-danger btn-sm">
           <i class="bi bi-file-earmark-pdf"></i> Download PDF (Halaman <?= $page ?>)
        </a>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nomor Surat</th>
                        <th>Asal Surat</th>
                        <th style="width:150px;">Tanggal Pemusnahan</th>
                        <th>Disetujui Oleh</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $no = $offset + 1;

                if(mysqli_num_rows($q) == 0){
                    echo "<tr>
                            <td colspan='5' class='text-muted'>
                                Belum ada data pemusnahan
                            </td>
                          </tr>";
                }

                while($r = mysqli_fetch_assoc($q)):
                ?>

                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($r['nomor_surat']) ?></td>
                    <td class="text-start"><?= htmlspecialchars($r['asal_surat']) ?></td>
                    <td><?= date('d-m-Y', strtotime($r['tanggal_pemusnahan'])) ?></td>
                    <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
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

                <?php if($page < $totalPages): ?>
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