<?php
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
// VALIDASI PARAMETER
// ==========================
$id_arsip = $_GET['id'] ?? '';
if ($id_arsip === '') {
    die("ID arsip tidak valid");
}

// ==========================
// QUERY DETAIL ARSIP
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        arsip.*,
        kategori.nama_kategori,
        box.kode_box
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    WHERE arsip.id_arsip = '$id_arsip'
");

$data = mysqli_fetch_assoc($query);
if (!$data) {
    die("Data arsip tidak ditemukan");
}

// ==========================
// AMBIL HASIL OCR
// ==========================
$teks_ocr = '';

if ($data['status_ocr'] === 'Sukses') {
    $qOCR = mysqli_query($conn, "
        SELECT teks_ocr 
        FROM hasil_ocr 
        WHERE id_arsip='$id_arsip'
        ORDER BY tanggal_ocr DESC
        LIMIT 1
    ");

    $ocr = mysqli_fetch_assoc($qOCR);
    if ($ocr) {
        $teks_ocr = $ocr['teks_ocr'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Arsip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }
        .container {
            width: 75%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .label {
            width: 30%;
            font-weight: bold;
            background: #f0f0f0;
        }
        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
        }
        .back { background: #7f8c8d; color: white; }
        .download { background: #2ecc71; color: white; }
        .ocr { background: #e67e22; color: white; }
        .status-ok { color: green; font-weight: bold; }
        .status-wait { color: orange; font-weight: bold; }
        .status-fail { color: red; font-weight: bold; }
        .ocr-box {
            background: #fafafa;
            padding: 12px;
            border: 1px dashed #ccc;
            margin-top: 10px;
            max-height: 250px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>📄 Detail Arsip Inaktif</h2>

    <table>
        <tr>
            <td class="label">Asal Surat</td>
            <td><?= htmlspecialchars($data['asal_surat']) ?></td>
        </tr>
        <tr>
            <td class="label">Nomor Surat</td>
            <td><?= htmlspecialchars($data['nomor_surat']) ?></td>
        </tr>
        <tr>
            <td class="label">Tanggal Surat</td>
            <td><?= $data['tanggal_surat'] ?></td>
        </tr>
        <tr>
            <td class="label">Isi Ringkas</td>
            <td><?= nl2br(htmlspecialchars($data['isi_ringkas'])) ?></td>
        </tr>
        <tr>
            <td class="label">Kategori Arsip</td>
            <td><?= htmlspecialchars($data['nama_kategori']) ?></td>
        </tr>
        <tr>
            <td class="label">Box Penyimpanan</td>
            <td><?= htmlspecialchars($data['kode_box']) ?></td>
        </tr>
        <tr>
            <td class="label">Status Arsip</td>
            <td><?= $data['status_arsip'] ?></td>
        </tr>
        <tr>
            <td class="label">Status OCR</td>
            <td>
                <?php
                if ($data['status_ocr'] === 'Sukses') {
                    echo '<span class="status-ok">Sukses</span>';
                } elseif ($data['status_ocr'] === 'Gagal') {
                    echo '<span class="status-fail">Gagal</span>';
                } else {
                    echo '<span class="status-wait">Belum Diproses</span>';
                }
                ?>
            </td>
        </tr>
    </table>

    <!-- TOMBOL OCR -->
    <?php if ($role === 'admin' && $data['status_ocr'] !== 'Sukses') { ?>
        <a href="../ocr/process_ocr.php?id=<?= $data['id_arsip'] ?>" class="btn ocr">
            🔍 Proses OCR
        </a>
    <?php } ?>

    <!-- HASIL OCR -->
    <?php if ($data['status_ocr'] === 'Sukses') { ?>
        <h3>📑 Hasil OCR (Isi Dokumen)</h3>
        <div class="ocr-box">
            <?= nl2br(htmlspecialchars($teks_ocr)) ?>
        </div>
    <?php } ?>

    <a href="download.php?id=<?= $data['id_arsip'] ?>" class="btn download">
        ⬇️ Download Arsip
    </a>

    <br><br>

    <a href="index.php" class="btn back">
        ⬅️ Kembali ke Daftar Arsip
    </a>

</div>

</body>
</html>
