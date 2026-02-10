<?php
session_start();
include __DIR__ . "/../config/database.php";

/* ==========================
   CEK LOGIN
========================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'];

/* ==========================
   LINK KEMBALI BERDASARKAN ROLE
========================== */
if ($role === 'admin') {
    $link_kembali = "../arsip/index.php";
    $teks_kembali = "⬅️ Kembali ke Manajemen Arsip";
} elseif ($role === 'staff') {
    $link_kembali = "../dashboard/staff.php";
    $teks_kembali = "⬅️ Kembali ke Dashboard Staff";
} elseif ($role === 'pimpinan') {
    $link_kembali = "../dashboard/pimpinan.php";
    $teks_kembali = "⬅️ Kembali ke Dashboard Pimpinan";
} else {
    $link_kembali = "../auth/logout.php";
    $teks_kembali = "⬅️ Kembali";
}


/* ==========================
   VALIDASI PARAMETER
========================== */
$id_arsip = $_GET['id'] ?? '';
if ($id_arsip === '') {
    die("ID arsip tidak valid");
}

/* ==========================
   QUERY DETAIL ARSIP
========================== */
$query = mysqli_query($conn, "
    SELECT 
        arsip.*,
        kategori.nama_kategori,
        box.kode_box,
        box.lokasi_fisik
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    WHERE arsip.id_arsip = '$id_arsip'
");

$data = mysqli_fetch_assoc($query);
if (!$data) {
    die("Data arsip tidak ditemukan");
}

/* ==========================
   PROTEKSI ARSIP DIMUSNAHKAN
========================== */
if ($data['status_arsip'] === 'Dimusnahkan') {
    // tetap boleh lihat metadata (untuk laporan),
    // tapi fungsi unduh & edit dibatasi
    $arsip_dimusnahkan = true;
} else {
    $arsip_dimusnahkan = false;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Arsip</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}
.container {
    width: 75%;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 6px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    vertical-align: top;
}
.label {
    width: 30%;
    font-weight: bold;
    background: #f0f0f0;
}
.btn {
    display: inline-block;
    padding: 8px 14px;
    margin-top: 12px;
    margin-right: 6px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    color: #fff;
}
.back { background:#7f8c8d; }
.download { background:#2ecc71; }
.edit { background:#3498db; }
.delete { background:#e74c3c; }

.status {
    font-weight: bold;
}
.status.inaktif { color:#f39c12; }
.status.permanen { color:#27ae60; }
.status.musnah { color:#e74c3c; }
</style>
</head>

<body>

<?php if (isset($_GET['updated'])) { ?>
    <p style="color:green;font-weight:bold;">
        ✅ Data arsip berhasil diperbarui
    </p>
<?php } ?>


<div class="container">

<h2>📄 Detail Arsip</h2>

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
    <td><?= htmlspecialchars($data['tanggal_surat']) ?></td>
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
    <td>
        <?= htmlspecialchars($data['kode_box']) ?>
        <?php if ($data['lokasi_fisik']) { ?>
            (<?= htmlspecialchars($data['lokasi_fisik']) ?>)
        <?php } ?>
    </td>
</tr>
<tr>
    <td class="label">Jumlah Berkas</td>
    <td><?= htmlspecialchars($data['jumlah_berkas']) ?></td>
</tr>
<tr>
    <td class="label">Tingkat Perkembangan</td>
    <td><?= htmlspecialchars($data['tingkat_perkembangan']) ?></td>
</tr>
<tr>
    <td class="label">Klasifikasi Keamanan</td>
    <td><?= htmlspecialchars($data['klasifikasi_keamanan']) ?></td>
</tr>
<tr>
    <td class="label">Status Arsip</td>
    <td class="status <?= $arsip_dimusnahkan ? 'musnah' : 'inaktif' ?>">
        <?= htmlspecialchars($data['status_arsip']) ?>
    </td>
</tr>
</table>

<!-- ==========================
     AKSI
========================== -->

<?php if (!$arsip_dimusnahkan) { ?>
    <a href="download_arsip.php?id=<?= $data['id_arsip'] ?>" class="btn download">
        ⬇️ Download Arsip
    </a>
<?php } else { ?>
    <p style="color:#e74c3c;font-weight:bold;margin-top:15px;">
        🚫 Arsip ini telah dimusnahkan dan tidak dapat diunduh
    </p>
<?php } ?>

<?php if ($role === 'admin' && !$arsip_dimusnahkan) { ?>
    <a href="edit_arsip.php?id=<?= $data['id_arsip'] ?>" class="btn edit">
        ✏️ Edit Arsip
    </a>

    <a href="hapus_arsip.php?id=<?= $data['id_arsip'] ?>"
       class="btn delete"
       onclick="return confirm('Yakin ingin menghapus arsip ini?')">
        🗑️ Hapus Arsip
    </a>
<?php } ?>

<br><br>

<a href="<?= $link_kembali ?>" class="btn back">
<?= $teks_kembali ?>
</a>


</div>

</body>
</html>
