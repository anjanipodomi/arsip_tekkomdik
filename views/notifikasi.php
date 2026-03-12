<?php
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";
require_once __DIR__ . "/../app/notifikasi/helper.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user']) || 
    !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* ==========================
   AUTO BERSIHKAN NOTIFIKASI LAMA
========================== */
bersihkan_notifikasi_lama($conn, $id_user);

/* ==========================
   AMBIL DATA NOTIFIKASI
========================== */
$query = mysqli_query($conn, "
    SELECT pesan, link, tanggal
    FROM notifikasi
    WHERE id_user = '$id_user'
    ORDER BY tanggal DESC
");

/* ==========================
   TANDAI NOTIFIKASI SEBAGAI DIBACA
========================== */
mysqli_query($conn, "
    UPDATE notifikasi
    SET status = 'dibaca'
    WHERE id_user = '$id_user'
    AND status = 'baru'
");

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";

$role = $_SESSION['role'];

if ($role === 'admin') {
    require_once __DIR__ . "/../layout/sidebar.php";
} else {
    echo "<script>document.body.classList.add('pimpinan');</script>";
}
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-bell me-2"></i> Notifikasi Sistem
</h3>

<div class="card shadow-sm border-0 rounded-4">

    <div class="card-header bg-white border-0">
        <span class="fw-semibold">Daftar Notifikasi</span>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th style="width:60px;" class="text-center">No</th>
                        <th class="text-start">Pesan</th>
                        <th style="width:180px;" class="text-center">Tanggal</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $no = 1;

                if (mysqli_num_rows($query) == 0) {
                    echo "<tr>
                            <td colspan='3' class='text-center text-muted'>
                                Tidak ada notifikasi
                            </td>
                          </tr>";
                }

                while ($row = mysqli_fetch_assoc($query)) :
                ?>

                <tr>
                    <!-- NO -->
                    <td class="text-center"><?= $no++ ?></td>

                    <!-- PESAN (RATA KIRI SEPERTI LOG AKTIVITAS) -->
                    <td class="text-start">
                        <?php if (!empty($row['link'])) : ?>
                            <a href="<?= htmlspecialchars($row['link']) ?>" 
                                class="text-decoration-none text-dark"> 
                                <?= htmlspecialchars($row['pesan']) ?>
                            </a>
                        <?php else : ?>
                            <?= htmlspecialchars($row['pesan']) ?>
                        <?php endif; ?>
                    </td>

                    <!-- TANGGAL -->
                    <td class="text-center">
                        <?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?>
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

<?php require_once __DIR__ . "/../layout/footer.php"; ?>