<?php
$role = $_SESSION['role'] ?? '';
$notif_count = $notif_count ?? 0;
?>

<header class="topbar d-flex justify-content-between align-items-center px-4"
        style="height:70px; background-color:#64bbe6; position:fixed; top:0; left:0; right:0; z-index:1000;">

    <div class="d-flex align-items-center gap-3">

        <img src="<?= BASE_URL ?>assets/img/Logo BTKP DIY_V2.svg"
             alt="Logo"
             style="height:90px;">

        <div class="lh-sm text-dark">
            <div class="fw-bold">ADISA</div>
            <small style="font-size:13px;">
                Aplikasi Dokumen Inaktif Siap Akses
            </small>
        </div>

    </div>

    <div class="d-flex align-items-center gap-3">

        <?php if (in_array($role, ['admin','pimpinan'])): ?>
            <a href="<?= BASE_URL ?>views/notifikasi.php"
               class="position-relative text-dark text-decoration-none">
                <i class="bi bi-bell fs-5"></i>

                <?php if (!empty($notif_count) && $notif_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $notif_count ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>app/auth/logout.php"
           class="btn btn-danger btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>

    </div>

</header>