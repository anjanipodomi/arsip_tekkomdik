<?php
/**
 * Dashboard Monitoring Retensi & Pemusnahan Arsip
 * Role: Admin & Pimpinan
 */

session_start();
include __DIR__ . "/config/database.php";

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['id_user'])) {
    header("Location: auth/login_form.php");
    exit;
}

$role = $_SESSION['role'];
if (!in_array($role, ['admin', 'pimpinan'])) {
    die("Akses ditolak");
}

// ==========================
// STATISTIK RETENSI
// ==========================
function countStatus($conn, $status) {
    $q = mysqli_query($conn, "
        SELECT COUNT(*) total 
        FROM arsip 
        WHERE status_arsip='$status'
    ");
    return mysqli_fetch_assoc($q)['total'];
}

$inaktif      = countStatus($conn, 'Inaktif');
$permanen     = countStatus($conn, 'Permanen');
$siap_musnah  = countStatus($conn, 'Siap Musnah');
$dimusnahkan  = countStatus($conn, 'Dimusnahkan');

// ==========================
// DATA MENUNGGU PERSETUJUAN
// ==========================
$menunggu = mysqli_query($conn, "
    SELECT id_arsip, nomor_surat, asal_surat, tanggal_surat
    FROM arsip
    WHERE status_arsip='Siap Musnah'
    ORDER BY tanggal_surat ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Retensi & Pemusnahan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }
        .container {
            width: 95%;
            margin: auto;
        }
        .stat {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .box {
            flex: 1;
            background: white;
            padding: 20px;
            text-align: center;
            border-radius: 6px;
        }
        .inaktif { border-top: 5px solid #3498db; }
        .permanen { border-top: 5px solid #2ecc71; }
        .siap { border-top: 5px solid #f39c12; }
        .musnah { border-top: 5px solid #e74c3c; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        table th {
            background: #ddd;
        }
        .btn {
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 4px;
            background: #9b59b6;
            color: white;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>📊 Dashboard Monitoring Retensi & Pemusnahan Arsip</h2>

    <!-- STATISTIK -->
    <div class="stat">
        <div class="box inaktif">
            <h3>Inaktif</h3>
            <h1><?= $inaktif ?></h1>
        </div>
        <div class="box permanen">
            <h3>Permanen</h3>
            <h1><?= $permanen ?></h1>
        </div>
        <div class="box siap">
            <h3>Siap Musnah</h3>
            <h1><?= $siap_musnah ?></h1>
        </div>
        <div class="box musnah">
            <h3>Dimusnahkan</h3>
            <h1><?= $dimusnahkan ?></h1>
        </div>
    </div>

    <!-- ADMIN: JALANKAN RETENSI -->
    <?php if ($role === 'admin') { ?>
        <p>
            <a href="retensi/proses_retensi.php" class="btn">
                🔁 Jalankan Proses Retensi
            </a>
        </p>
    <?php } ?>

    <!-- PIMPINAN: DAFTAR PERSETUJUAN -->
    <?php if ($role === 'pimpinan') { ?>
        <h3>🗑️ Arsip Menunggu Persetujuan Pemusnahan</h3>

        <table>
            <tr>
                <th>No</th>
                <th>Nomor Surat</th>
                <th>Asal Surat</th>
                <th>Tanggal Surat</th>
                <th>Aksi</th>
            </tr>

            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($menunggu)) {
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
                <td><?= htmlspecialchars($row['asal_surat']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_surat']) ?></td>
                <td>
                    <a href="pemusnahan/index.php" class="btn">
                        Lihat & Proses
                    </a>
                </td>
            </tr>
            <?php } ?>

            <?php if (mysqli_num_rows($menunggu) === 0) { ?>
            <tr>
                <td colspan="5">Tidak ada arsip menunggu persetujuan</td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>

</div>

</body>
</html>
