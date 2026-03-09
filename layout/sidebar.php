<?php
$page = basename($_SERVER['PHP_SELF']);

function activeMenu($current, $target){
    return $current == $target ? 'active bg-white text-dark fw-semibold shadow-sm' : '';
}
?>

<aside class="d-flex flex-column p-3 position-fixed"
       style="width:260px; height:100vh; background-color:#64bbe6; overflow-y:auto;">

    <a href="<?= BASE_URL ?>views/dashboard_admin.php"
       class="d-flex align-items-center mb-3 text-dark text-decoration-none">
        <span class="fs-5 fw-bold">ADISA Admin</span>
    </a>

    <hr class="border-light opacity-50">

    <ul class="nav nav-pills flex-column mb-auto">

        <li class="text-dark small fw-semibold mb-2">MENU UTAMA</li>

        <li>
            <a href="<?= BASE_URL ?>views/dashboard_admin.php"
               class="nav-link text-dark <?= strpos($page,'dashboard')!==false ? 'active bg-white text-dark fw-semibold shadow-sm' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <li class="text-dark small fw-semibold mt-3">MANAJEMEN ARSIP</li>

        <li>
            <a href="<?= BASE_URL ?>views/arsip.php"
               class="nav-link text-dark <?= activeMenu($page,'arsip.php') ?>">
                <i class="bi bi-folder me-2"></i> Manajemen Arsip
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>views/upload_arsip.php"
               class="nav-link text-dark <?= activeMenu($page,'upload_arsip.php') ?>">
                <i class="bi bi-plus-circle me-2"></i> Upload Arsip
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>views/kategori.php"
               class="nav-link text-dark <?= activeMenu($page,'kategori.php') ?>">
                <i class="bi bi-tags me-2"></i> Kategori Arsip
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>views/box_arsip.php"
               class="nav-link text-dark <?= activeMenu($page,'box_arsip.php') ?>">
                <i class="bi bi-archive me-2"></i> Box Arsip
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>views/retensi.php"
               class="nav-link text-dark <?= activeMenu($page,'retensi.php') ?>">
                <i class="bi bi-hourglass-split me-2"></i> Retensi Arsip
            </a>
        </li>

        <li class="text-dark small fw-semibold mt-3">ADMINISTRASI</li>

        <li>
            <a href="<?= BASE_URL ?>views/kelola_user.php"
               class="nav-link text-dark <?= activeMenu($page,'kelola_user.php') ?>">
                <i class="bi bi-people me-2"></i> Kelola User
            </a>
        </li>

        <li class="text-dark small fw-semibold mt-3">LAPORAN</li>

        <li>
            <a href="<?= BASE_URL ?>views/laporan_arsip.php"
               class="nav-link text-dark <?= activeMenu($page,'laporan_arsip.php') ?>">
                <i class="bi bi-file-earmark-text me-2"></i> Laporan Arsip
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>views/laporan_retensi.php"
               class="nav-link text-dark <?= activeMenu($page,'laporan_retensi.php') ?>">
                <i class="bi bi-hourglass me-2"></i> Laporan Retensi
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>views/laporan_pemusnahan.php"
               class="nav-link text-dark <?= activeMenu($page,'laporan_pemusnahan.php') ?>">
                <i class="bi bi-fire me-2"></i> Laporan Pemusnahan
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>views/laporan_log.php"
               class="nav-link text-dark <?= activeMenu($page,'laporan_log.php') ?>">
                <i class="bi bi-journal-text me-2"></i> Log Aktivitas
            </a>
        </li>

        <li class="text-dark small fw-semibold mt-3">SISTEM</li>

        <li>
            <a href="<?= BASE_URL ?>views/notifikasi.php"
               class="nav-link text-dark <?= activeMenu($page,'notifikasi.php') ?>">
                <i class="bi bi-bell me-2"></i> Notifikasi
                <?php if ($notif_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?= $notif_count ?></span>
                <?php endif; ?>
            </a>
        </li>

    </ul>
</aside>