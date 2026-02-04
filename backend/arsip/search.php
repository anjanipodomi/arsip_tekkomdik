<?php
/**
 * Pencarian Arsip Inaktif
 * Digunakan oleh:
 * - Admin / Operator Arsip
 * - Staff Tekkomdik
 * - Pimpinan
 *
 * Fungsi:
 * - Pencarian arsip berbasis metadata
 * - Dasar pencarian OCR di tahap lanjutan
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

// ==========================
// AMBIL DATA MASTER
// ==========================
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$box      = mysqli_query($conn, "SELECT * FROM box ORDER BY kode_box");

// ==========================
// AMBIL INPUT SEARCH
// ==========================
$keyword     = $_GET['keyword'] ?? '';
$id_kategori = $_GET['id_kategori'] ?? '';
$id_box      = $_GET['id_box'] ?? '';
$status      = $_GET['status'] ?? '';

// ==========================
// BANGUN QUERY DINAMIS
// ==========================
$where = [];

if ($keyword !== '') {
    $where[] = "(arsip.asal_surat LIKE '%$keyword%' 
                OR arsip.nomor_surat LIKE '%$keyword%' 
                OR arsip.isi_ringkas LIKE '%$keyword%'
                OR hasil_ocr.teks_ocr LIKE '%$keyword%')";
}


if ($id_kategori !== '') {
    $where[] = "arsip.id_kategori = '$id_kategori'";
}

if ($id_box !== '') {
    $where[] = "arsip.id_box = '$id_box'";
}

if ($status !== '') {
    $where[] = "arsip.status_arsip = '$status'";
}

$whereSQL = '';
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

// ==========================
// QUERY PENCARIAN
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        arsip.id_arsip,
        arsip.asal_surat,
        arsip.nomor_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        box.kode_box,
        hasil_ocr.teks_ocr
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    LEFT JOIN hasil_ocr ON arsip.id_arsip = hasil_ocr.id_arsip
    $whereSQL
    ORDER BY arsip.tanggal_input DESC
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Pencarian Arsip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }
        .container {
            width: 95%;
            margin: auto;
        }
        .search-box {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
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
        .search { background: #9b59b6; color: white; }
    </style>
</head>

<body>

<div class="container">

    <h2>🔍 Pencarian Arsip Inaktif</h2>

    <!-- FORM PENCARIAN -->
    <div class="search-box">
        <form method="GET">
            Kata Kunci:
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>">

            Kategori:
            <select name="id_kategori">
                <option value="">-- Semua --</option>
                <?php while ($k = mysqli_fetch_assoc($kategori)) { ?>
                    <option value="<?= $k['id_kategori'] ?>" <?= ($id_kategori == $k['id_kategori']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kategori']) ?>
                    </option>
                <?php } ?>
            </select>

            Box:
            <select name="id_box">
                <option value="">-- Semua --</option>
                <?php while ($b = mysqli_fetch_assoc($box)) { ?>
                    <option value="<?= $b['id_box'] ?>" <?= ($id_box == $b['id_box']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['kode_box']) ?>
                    </option>
                <?php } ?>
            </select>

            Status:
            <select name="status">
                <option value="">-- Semua --</option>
                <option value="Inaktif" <?= ($status == 'Inaktif') ? 'selected' : '' ?>>Inaktif</option>
                <option value="Permanen" <?= ($status == 'Permanen') ? 'selected' : '' ?>>Permanen</option>
                <option value="Siap Musnah" <?= ($status == 'Siap Musnah') ? 'selected' : '' ?>>Siap Musnah</option>
            </select>

            <button type="submit" class="btn search">Cari</button>
        </form>
    </div>

    <!-- HASIL PENCARIAN -->
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
