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
$status = $_GET['status'] ?? '';

if($status != ''){
    $total_result = mysqli_query($conn,"
        SELECT COUNT(*) as total
        FROM arsip
        WHERE status_arsip='".mysqli_real_escape_string($conn,$status)."'
    ");
}else{
    $total_result = mysqli_query($conn,"
        SELECT COUNT(*) as total
        FROM arsip
    ");
}

$total_row  = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];

$total_page = ceil($total_data / $limit);

/* =========================
   QUERY DATA PER HALAMAN
========================= */
$status = $_GET['status'] ?? '';
$q = mysqli_query($conn,"
    SELECT 
        arsip.nomor_surat,
        arsip.asal_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        box.kode_box
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    ".($status ? "WHERE arsip.status_arsip='".mysqli_real_escape_string($conn,$status)."'" : "")."
    ORDER BY arsip.tanggal_surat DESC
    LIMIT $offset, $limit
");

$no = $offset + 1;
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-file-earmark-text me-2"></i> Laporan Data Arsip
</h3>

<div class="card shadow-sm border-0 rounded-4">

    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Data Arsip</span>

        <form method="GET" class="d-flex gap-2">

    <select name="status" class="form-select form-select-sm">
    <option value="">Semua Status</option>
    <option value="Inaktif" <?= (($_GET['status'] ?? '')=='Inaktif')?'selected':'' ?>>Inaktif</option>
    <option value="Permanen" <?= (($_GET['status'] ?? '')=='Permanen')?'selected':'' ?>>Permanen</option>
    <option value="Siap Musnah" <?= (($_GET['status'] ?? '')=='Siap Musnah')?'selected':'' ?>>Siap Musnah</option>
    </select>

    <button type="submit" class="btn btn-primary btn-sm">
    Filter
    </button>

    </form>

    <a href="<?= BASE_URL ?>app/laporan/cetak_pdf.php?jenis=arsip&page=<?= $page ?>&status=<?= urlencode($_GET['status'] ?? '') ?>"
        target="_blank"
        class="btn btn-primary btn-sm">
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
                        <th style="width:120px;">Tanggal</th>
                        <th>Kategori</th>
                        <th>Box</th>
                        <th style="width:150px;">Status</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                if(mysqli_num_rows($q) == 0){
                    echo "<tr>
                            <td colspan='7'>Belum ada data arsip</td>
                         </tr>";
                } else {
                    while($r = mysqli_fetch_assoc($q)):
                ?>

                <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($r['nomor_surat']) ?></td>
                <td class="text-start"><?= htmlspecialchars($r['asal_surat']) ?></td>
                <td><?= date('d/m/Y', strtotime($r['tanggal_surat'])) ?></td>                <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
                <td><?= htmlspecialchars($r['kode_box']) ?></td>
                <td>
                    <?php
                    switch($r['status_arsip']){
                        case 'Inaktif':
                            echo '<span class="badge bg-warning text-dark">Inaktif</span>';
                            break;
                        case 'Permanen':
                            echo '<span class="badge bg-success">Permanen</span>';
                            break;
                        case 'Siap Musnah':
                            echo '<span class="badge bg-danger">Siap Musnah</span>';
                            break;
                        case 'Dimusnahkan':
                            echo '<span class="badge bg-secondary">Dimusnahkan</span>';
                            break;
                    }
                    ?>
                </td>
            </tr>

            <?php 
                endwhile;
            }
            ?>

            </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if($total_page > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">

                <?php if($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?>&status=<?= urlencode($_GET['status'] ?? '') ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for($i=1; $i <= $total_page; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($_GET['status'] ?? '') ?>">                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if($page < $total_page): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?>&status=<?= urlencode($_GET['status'] ?? '') ?>">Next</a>
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