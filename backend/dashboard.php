<?php
session_start();
include __DIR__ . "/config/database.php";

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['id_user'])) {
    header("Location: auth/login_form.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$role    = $_SESSION['role'];

// ==========================
// CEK ROLE
// ==========================
if (!in_array($role, ['admin', 'pimpinan'])) {
    die("Akses ditolak");
}

// ==========================
// QUERY STATISTIK
// ==========================
$total_arsip = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM arsip")
)['total'];

$arsip_inaktif = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM arsip WHERE status_arsip='Inaktif'")
)['total'];

$arsip_permanen = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM arsip WHERE status_arsip='Permanen'")
)['total'];

$arsip_musnah = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM arsip WHERE status_arsip='Siap Musnah'")
)['total'];

$arsip_per_box = mysqli_query($conn, "
    SELECT box.kode_box, COUNT(arsip.id_arsip) jumlah
    FROM box
    LEFT JOIN arsip ON box.id_box = arsip.id_box
    GROUP BY box.id_box
");

$notif = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(*) total 
        FROM notifikasi 
        WHERE id_user='$id_user' AND status='baru'
    ")
)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Arsip Inaktif</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }
        .container {
            width: 90%;
            margin: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .menu {
            background: #fff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .menu a {
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }
        .card {
            background: #fff;
            padding: 20px;
            margin: 15px 0;
            border-radius: 6px;
        }
        .stat {
            display: flex;
            gap: 20px;
        }
        .box {
            flex: 1;
            background: #eaeaea;
            padding: 15px;
            text-align: center;
            border-radius: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        table th {
            background: #ddd;
        }
        a.logout {
            color: red;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>

<body>
<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2>📊 Dashboard Arsip Inaktif</h2>
        <a href="auth/logout.php" class="logout">Logout</a>
    </div>

    <!-- MENU UTAMA -->
    <div class="menu">
        <strong>Menu:</strong>

        <?php if ($role === 'admin') { ?>
            <a href="arsip/index.php">📂 Manajemen Arsip</a>
            <a href="arsip/tambah.php">➕ Upload Arsip</a>
            <a href="retensi/cek_retensi.php">⏳ Retensi Arsip</a>
            <a href="notifikasi/index.php">🔔 Notifikasi (<?= $notif ?>)</a>
            <a href="laporan/export_excel.php">📄 Laporan</a>
        <?php } ?>

        <?php if ($role === 'pimpinan') { ?>
            <a href="pemusnahan/index.php">🔥 Persetujuan Pemusnahan</a>
            <a href="laporan/export_pdf.php">📄 Laporan</a>
        <?php } ?>
    </div>

    <!-- STATISTIK -->
    <div class="card stat">
        <div class="box">
            <h3>Total Arsip</h3>
            <h1><?= $total_arsip ?></h1>
        </div>
        <div class="box">
            <h3>Inaktif</h3>
            <h1><?= $arsip_inaktif ?></h1>
        </div>
        <div class="box">
            <h3>Permanen</h3>
            <h1><?= $arsip_permanen ?></h1>
        </div>
        <div class="box">
            <h3>Siap Musnah</h3>
            <h1><?= $arsip_musnah ?></h1>
        </div>
    </div>

    <!-- ARSIP PER BOX -->
    <div class="card">
        <h3>📦 Arsip per Box</h3>
        <table>
            <tr>
                <th>Kode Box</th>
                <th>Jumlah Arsip</th>
            </tr>
            <?php while ($b = mysqli_fetch_assoc($arsip_per_box)) { ?>
            <tr>
                <td><?= htmlspecialchars($b['kode_box']) ?></td>
                <td><?= $b['jumlah'] ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>

</div>
</body>
</html>
