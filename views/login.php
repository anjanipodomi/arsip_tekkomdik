<?php
require_once __DIR__ . "/../app/config/config.php";

if (isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Sistem Arsip Inaktif Digital</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS LOGIN KHUSUS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/login.css">
</head>

<body>

<body>

<div class="login-container">

    <div class="login-card">

        <div class="brand-side">
            <img src="<?= BASE_URL ?>assets/img/Logo BTKP DIY_V2.svg" alt="Logo Instansi">

            <h1>ADISA</h1>
            <h2>Aplikasi Dokumen Inaktif Siap Akses</h2>

            <p>
                ADISA merupakan sistem informasi yang dirancang untuk mengelola dokumen arsip inaktif secara digital sehingga siap diakses setiap saat. Sistem ini memudahkan pencarian, pemanfaatan, dan pengelolaan arsip dengan aman, tertib, dan efisien.
                ADISA memastikan dokumen tidak aktif tetap bernilai sebagai sumber informasi, meskipun tidak lagi digunakan secara aktif. Sistem ini mendukung tata kelola arsip modern di Balai Tekkomdik DIY dan memudahkan pegawai dalam menelusuri arsip dengan cepat dan akurat.
            </p>
        </div>

        <div class="form-side">

            <h3>Masuk ke Sistem</h3>
            <p class="subtitle">Silakan login menggunakan akun Anda</p>

            <?php if ($error !== ''): ?>
                <div class="error-box">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>app/auth/login.php">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn-login">
                    Login
                </button>
            </form>

            <div class="login-info">
                Aplikasi Dokumen Inaktif Siap Akses (ADISA)<br>
                Versi 1.0
            </div>

        </div>

    </div>

    
    <div class="login-footer">
        © <?=date('Y')?> Balai Teknologi Komunikasi Pendidikan DIY  
        <br>Aplikasi Dokumen Inaktif Siap Akses
    </div>

</div>

</body>


</body>
</html>


