<?php
/**
 * Daftar Arsip Inaktif
 * Digunakan oleh:
 * - Admin / Operator Arsip
 * - Staff Tekkomdik
 * - Pimpinan
 *
 * Fungsi:
 * - Menampilkan daftar arsip
 * - Akses ke detail & download
 */

session_start();
include __DIR__ . "/../config/database.php";

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login_form.php");
    exit;
}

$role = $_SESSION['role'];

// ==========================
// QUERY DATA ARSIP
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        arsip.id_arsip,
        arsip.asal_surat,
        arsip.nomor_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        box.kode_box
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    ORDER BY arsip.tanggal_input DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Arsip Inaktif</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }
        .container {
            width: 95%;
            margin: auto;
        }
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
            padding: 4px 8px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .detail { background: #3498db; color: white; }
        .download { background: #2ecc71; color: white; }
        .tambah { background: #9b59b6; color: white; }
    </style>
</head>

<body>

<div class="container">

    <h2>📂 Daftar Arsip Inaktif</h2>

    <!-- ADMIN SAJA: TAMBAH ARSIP -->
    <?php if ($role === 'admin') { ?>
        <p>
            <a href="tambah.php" class="btn tambah">➕ Tambah Arsip</a>
        </p>
    <?php } ?>

    <table>
        <tr>
            <th>No</th>
            <th>Asal Surat</th>
            <th>Nomor Surat</th>
            <th>Tanggal</th>
            <th>Kategori</th>
            <th>Box</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)) {
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['asal_surat']) ?></td>
            <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
            <td><?= $row['tanggal_surat'] ?></td>
            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
            <td><?= htmlspecialchars($row['kode_box']) ?></td>
            <td><?= $row['status_arsip'] ?></td>
            <td>
                <a href="detail.php?id=<?= $row['id_arsip'] ?>" class="btn detail">Detail</a>
                <a href="download.php?id=<?= $row['id_arsip'] ?>" class="btn download">Download</a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

</body>
</html>
